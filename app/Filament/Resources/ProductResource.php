<?php

namespace App\Filament\Resources;

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

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationGroup = 'Inventario';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->label('Nombre')
                ->placeholder('Introduce el nombre del producto'),

            Forms\Components\TextInput::make('sku')
                ->required()
                ->maxLength(255)
                ->label('SKU')
                ->placeholder('Introduce el SKU del producto'),

            Forms\Components\TextInput::make('price')
                ->required()
                ->numeric()
                ->label('Precio')
                ->placeholder('Introduce el precio del producto'),
            Forms\Components\TextInput::make('stock')
                ->required()
                ->numeric()
                ->label('Stock')
                ->placeholder('Introduce la cantidad disponible'),

            Forms\Components\Textarea::make('description')
                ->label('Descripción')
                ->placeholder('Introduce una descripción del producto'),

            Forms\Components\Select::make('category_id')
                ->relationship('category', 'name')
                ->label('Categoría')
                ->required(),

            Forms\Components\FileUpload::make('image')
                ->label('Imágenes')
                ->multiple()
                ->disk('public')
                ->directory('products')
                ->visibility('public')
                ->image()
                ->maxSize(10240)
                ->columnSpan(2),
        
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
            Tables\Columns\TextColumn::make('name')
                ->sortable()
                ->searchable(),
            Tables\Columns\TextColumn::make('sku')
                ->sortable()
                ->searchable(),
            Tables\Columns\TextColumn::make('price')
                ->sortable()
                ->money('cop', false),
            Tables\Columns\TextColumn::make('category.name')
                ->sortable()
                ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('delete')
                ->action(function (Product $record) {
                    $record->delete();
                })
                ->requiresConfirmation()
                ->color('danger')
                ->icon('heroicon-o-trash')
                ->label('Eliminar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
