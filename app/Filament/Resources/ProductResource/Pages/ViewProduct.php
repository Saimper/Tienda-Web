<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components;
use Filament\Infolists\Infolist;

class ViewProduct extends ViewRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Editar Producto')
                ->icon('heroicon-m-pencil-square')
                ->color('primary')
                ->size('md')
                ->tooltip('Modificar este producto'),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Grid::make()
                    ->schema([
                        // Sección de Imagen (Diseño Galería)
                        Components\Card::make()
                            ->schema([
                                Components\ImageEntry::make('image')
                                    ->hiddenLabel()
                                    ->getStateUsing(fn ($record) => asset('storage/'.$record->image))
                                    ->height(450)
                                    ->extraImgAttributes([
                                        'class' => 'rounded-xl object-contain w-full h-full shadow-lg transition-all duration-300 hover:scale-[1.02]',
                                        'alt' => 'Imagen del producto',
                                        'onerror' => "this.onerror=null;this.src='".url('/images/placeholder.png')."'"
                                    ])
                            ])
                            ->columnSpan(['lg' => 1])
                            ->extraAttributes([
                                'class' => 'bg-white p-4 rounded-xl border border-gray-100'
                            ]),

                        // Sección de Detalles (Diseño Premium)
                        Components\Card::make()
                            ->schema([
                                Components\TextEntry::make('name')
                                    ->label('')
                                    ->size(Components\TextEntry\TextEntrySize::Large)
                                    ->weight('bold')
                                    ->color('primary')
                                    ->columnSpanFull()
                                    ->extraAttributes([
                                        'class' => 'text-2xl font-bold mb-4'
                                    ]),
                                
                                Components\Grid::make()
                                    ->schema([
                                        // Columna de Información
                                        Components\Group::make([
                                            Components\TextEntry::make('sku')
                                                ->label('REFERENCIA')
                                                ->badge()
                                                ->color('gray')
                                                ->icon('heroicon-m-qr-code')
                                                ->extraAttributes([
                                                    'class' => 'font-mono text-sm'
                                                ]),
                                            
                                            Components\TextEntry::make('category.name')
                                                ->label('CATEGORÍA')
                                                ->badge()
                                                ->color('primary')
                                                ->icon('heroicon-m-tag'),
                                            
                                            Components\TextEntry::make('created_at')
                                                ->label('AGREGADO')
                                                ->dateTime('d M Y')
                                                ->icon('heroicon-m-calendar')
                                                ->color('gray'),
                                        ])
                                        ->columnSpan(['lg' => 1])
                                        ->extraAttributes([
                                            'class' => 'space-y-3'
                                        ]),

                                        // Columna de Precio/Stock
                                        Components\Group::make([
                                            Components\TextEntry::make('price')
                                                ->label('PRECIO')
                                                ->money('COP')
                                                ->size(Components\TextEntry\TextEntrySize::Large)
                                                ->weight('bold')
                                                ->color('success')
                                                ->icon('heroicon-m-currency-dollar')
                                                ->extraAttributes([
                                                    'class' => 'text-xl'
                                                ]),
                                            
                                            Components\TextEntry::make('stock')
                                                ->label('STOCK')
                                                ->badge()
                                                ->size(Components\TextEntry\TextEntrySize::Large)
                                                ->formatStateUsing(fn ($state) => $state > 0 ? "$state unidades" : 'AGOTADO')
                                                ->color(fn (int $state): string => match (true) {
                                                    $state > 20 => 'success',
                                                    $state > 0 => 'warning',
                                                    default => 'danger',
                                                })
                                                ->icon('heroicon-m-archive-box'),
                                        ])
                                        ->columnSpan(['lg' => 1])
                                        ->extraAttributes([
                                            'class' => 'space-y-3'
                                        ]),
                                    ])
                                    ->columns(2)
                                    ->columnSpanFull(),

                                // Descripción Elegante
                                Components\Section::make('DESCRIPCIÓN')
                                    ->schema([
                                        Components\TextEntry::make('description')
                                            ->hiddenLabel()
                                            ->prose()
                                            ->markdown()
                                            ->extraAttributes([
                                                'class' => 'text-gray-700 leading-relaxed mt-4'
                                            ])
                                    ])
                                    ->collapsible()
                                    ->columnSpanFull(),
                            ])
                            ->columnSpan(['lg' => 1])
                            ->extraAttributes([
                                'class' => 'bg-white p-6 rounded-xl border border-gray-100'
                            ]),
                    ])
                    ->columns(2)
                    ->extraAttributes([
                        'class' => 'gap-6'
                    ]),
            ]);
    }
}