<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    use HasFactory;

    /**
     * Atributos asignables masivamente
     * @var array
     */
    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_price'
    ];

    /**
     * Conversión de tipos de atributos
     * @var array
     */
    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2'
    ];

    /**
     * Relación con la venta (Sale)
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Relación con el producto
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Calcula el precio total automáticamente
     */
    public function calculateTotalPrice(): void
    {
        $this->total_price = $this->quantity * $this->unit_price;
        $this->save();
    }

    /**
     * Accesor para precio unitario formateado
     */
    public function getFormattedUnitPriceAttribute(): string
    {
        return '$' . number_format($this->unit_price, 2);
    }

    /**
     * Accesor para precio total formateado
     */
    public function getFormattedTotalPriceAttribute(): string
    {
        return '$' . number_format($this->total_price, 2);
    }

    /**
     * Eventos del modelo
     */
    protected static function booted()
    {
        // Actualizar stock del producto cuando se crea/elimina un ítem
        static::created(function ($item) {
            $product = $item->product;
            $product->stock -= $item->quantity;
            $product->save();
        });

        static::deleted(function ($item) {
            $product = $item->product;
            $product->stock += $item->quantity;
            $product->save();
        });
    }
}