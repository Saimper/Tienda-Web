<?php

namespace App\Filament\Resources;

use Illuminate\Support\Str;
use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationGroup = 'Inventario';
    protected static ?string $modelLabel = 'Producto';
    protected static ?string $navigationLabel = 'Productos';
    protected static ?int $navigationSort = 1;
    protected static int $lowStockThreshold = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Básica')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Nombre del Producto')
                            ->placeholder('Ej: Camiseta Premium')
                            ->columnSpan(2),
                            
                        Forms\Components\TextInput::make('sku')
                            ->required()
                            ->maxLength(255)
                            ->label('Código SKU')
                            ->placeholder('Ej: PROD-2024-001')
                            ->unique(ignoreRecord: true)
                            ->columnSpan(1),
                            
                        Forms\Components\TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->label('Precio Unitario')
                            ->placeholder('0.00')
                            ->columnSpan(1),
                            
                        Forms\Components\TextInput::make('stock')
                            ->required()
                            ->numeric()
                            ->label('Existencia')
                            ->placeholder('0')
                            ->columnSpan(1)
                            ->suffixIcon(fn ($state) => $state <= static::$lowStockThreshold ? 'heroicon-o-exclamation-triangle' : null)
                            ->suffixIconColor(fn ($state) => $state <= static::$lowStockThreshold ? 'danger' : null)
                            ->helperText(fn ($state) => $state <= static::$lowStockThreshold ? "¡Stock bajo! Nivel actual: {$state}" : null),
                    ])
                    ->columns(4),
                    
                Forms\Components\Section::make('Detalles Adicionales')
                    ->schema([
                        Forms\Components\Select::make('category_id')
                            ->relationship('category', 'name')
                            ->label('Categoría')
                            ->required()
                            ->preload()
                            ->searchable()
                            ->native(false)
                            ->columnSpan(1),
                            
                        Forms\Components\Textarea::make('description')
                            ->label('Descripción Completa')
                            ->placeholder('Descripción detallada del producto...')
                            ->columnSpan(2)
                            ->rows(3),
                            
                        Forms\Components\FileUpload::make('image')
                            ->label('Imagen del Producto')
                            ->disk('public')
                            ->directory('products')
                            ->visibility('public')
                            ->image()
                            ->imageEditor()
                            ->preserveFilenames()
                            ->maxSize(10240)
                            ->columnSpan(2)
                            ->helperText('Formatos: JPG, PNG, WEBP | Máx: 10MB')
                            ->getUploadedFileNameForStorageUsing(
                                fn ($file): string => 'products/'.Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)).'-'.uniqid().'.'.$file->getClientOriginalExtension()
                            ),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Imagen')
                    ->getStateUsing(fn ($record) => $record->image ? asset('storage/'.$record->image) : null)
                    ->size(60)
                    ->square()
                    ->extraImgAttributes([
                        'class' => 'rounded-lg shadow',
                        'style' => 'object-fit: cover'
                    ]),
                    
                Tables\Columns\TextColumn::make('name')
                    ->label('Producto')
                    ->sortable()
                    ->searchable()
                    ->description(fn (Product $record) => substr($record->description, 0, 40).'...')
                    ->wrap()
                    ->weight('medium'),
                    
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->sortable()
                    ->searchable()
                    ->copyable()
                    ->copyMessage('SKU copiado')
                    ->copyMessageDuration(1500),
                    
                Tables\Columns\TextColumn::make('price')
                    ->label('Precio')
                    ->sortable()
                    ->money('COP')
                    ->color('success')
                    ->alignEnd(),
                    
                Tables\Columns\TextColumn::make('stock')
                    ->label('Stock')
                    ->sortable()
                    ->color(fn (Product $record): string => match (true) {
                        $record->stock <= 0 => 'danger',
                        $record->stock <= static::$lowStockThreshold => 'warning',
                        default => 'success',
                    })
                    ->icon(fn (Product $record): ?string => match (true) {
                        $record->stock <= 0 => 'heroicon-o-x-circle',
                        $record->stock <= static::$lowStockThreshold => 'heroicon-o-exclamation-triangle',
                        default => null,
                    })
                    ->alignCenter(),
                    
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoría')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('gray'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->label('Filtrar por Categoría')
                    ->indicator('Categoría')
                    ->multiple()
                    ->searchable(),
                    
                Tables\Filters\TernaryFilter::make('stock')
                    ->label('Estado de Inventario')
                    ->placeholder('Todos')
                    ->trueLabel('Con stock')
                    ->falseLabel('Sin stock')
                    ->queries(
                        true: fn (Builder $query) => $query->where('stock', '>', 0),
                        false: fn (Builder $query) => $query->where('stock', '<=', 0),
                    ),
                    
                Tables\Filters\Filter::make('low_stock')
                    ->label('Stock bajo')
                    ->query(fn (Builder $query): Builder => $query->where('stock', '<=', static::$lowStockThreshold))
                    ->indicator('Stock bajo')
                    ->default(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->icon('heroicon-o-eye')
                    ->color('info'),
                    
                Tables\Actions\EditAction::make()
                    ->icon('heroicon-o-pencil')
                    ->color('primary')
                    ->after(function (Product $record) {
                        if ($record->stock <= static::$lowStockThreshold) {
                            Notification::make()
                                ->title('¡Stock bajo!')
                                ->body("El producto {$record->name} tiene stock bajo ({$record->stock} unidades)")
                                ->warning()
                                ->persistent()
                                ->send();
                        }
                    }),
                    
                Tables\Actions\DeleteAction::make()
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->successNotificationTitle('Producto eliminado exitosamente'),
                    
                Tables\Actions\Action::make('StockBajo')
                    ->icon('heroicon-o-bell-alert')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (Product $record): bool => $record->stock <= static::$lowStockThreshold)
                    ->action(function (Product $record) {
                        Notification::make()
                            ->title('¡Stock bajo notificado!')
                            ->body("Se ha enviado una alerta sobre el stock bajo del producto {$record->name}")
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Eliminar seleccionados')
                        ->deselectRecordsAfterCompletion(),
                        
                    Tables\Actions\BulkAction::make('updateStock')
                        ->label('Actualizar stock')
                        ->icon('heroicon-o-arrow-path')
                        ->form([
                            Forms\Components\TextInput::make('stock')
                                ->label('Nuevo valor de stock')
                                ->numeric()
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $records->each->update(['stock' => $data['stock']]);
                            
                            $lowStockProducts = $records->filter(fn ($record) => $record->stock <= static::$lowStockThreshold);
                            
                            if ($lowStockProducts->isNotEmpty()) {
                                Notification::make()
                                    ->title('¡Atención!')
                                    ->body(count($lowStockProducts).' productos quedaron con stock bajo')
                                    ->warning()
                                    ->send();
                            }
                        }),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Nuevo Producto')
                    ->icon('heroicon-o-plus'),
            ])
            ->emptyStateHeading('Aún no hay productos')
            ->emptyStateDescription('Comienza creando tu primer producto')
            ->emptyStateIcon('heroicon-o-shopping-cart')
            ->deferLoading();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'), 
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $lowStockCount = static::getModel()::where('stock', '<=', static::$lowStockThreshold)->count();
        return $lowStockCount > 0 ? (string)$lowStockCount : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole(['admin', 'logistica']);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole(['admin', 'logistica']);
    }
}