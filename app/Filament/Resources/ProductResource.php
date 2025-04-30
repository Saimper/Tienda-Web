<?php

namespace App\Filament\Resources;

use Illuminate\Support\Str;
use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationGroup = 'Inventario';
    protected static ?string $modelLabel = 'Producto';
    protected static ?string $navigationLabel = 'Productos';
    protected static ?int $navigationSort = 1;

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
                            ->columnSpan(1),
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
                            ->loadingIndicatorPosition('right')
                            ->panelLayout('integrated')
                            ->downloadable()
                            ->openable()
                            ->getUploadedFileNameForStorageUsing(
                                function ($file): string {
                                    $extension = $file->getClientOriginalExtension();
                                    $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                                    return 'products/'.Str::slug($name).'-'.uniqid().'.'.$extension;
                                }
                            )
                            ->dehydrated(true) // Mantiene el valor existente si no se sube nueva imagen
                            ->default(null), // Evita conflictos con imágenes existentes
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
                    ->getStateUsing(function ($record) {
                        if (!$record->image) {
                            return null;
                        }
                        return asset('storage/'.$record->image);
                    })
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
                    ->color(fn (Product $record) => $record->stock > 20 ? 'success' : ($record->stock > 0 ? 'warning' : 'danger'))
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
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->icon('heroicon-o-eye')
                    ->color('info'),
                    
                Tables\Actions\EditAction::make()
                    ->icon('heroicon-o-pencil')
                    ->color('warning'),
                    
                Tables\Actions\DeleteAction::make()
                    ->icon('heroicon-o-trash')
                    ->successNotificationTitle('Producto eliminado exitosamente'),
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
                        })
                        ->after(fn () => Notification::make()
                            ->title('Stock actualizado')
                            ->success()
                            ->send()),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
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

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole(['admin', 'logistica']);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole(['admin', 'logistica']);
    }
}