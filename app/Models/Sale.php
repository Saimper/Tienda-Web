<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array
     */
    protected $fillable = [
        'invoice_number',
        'customer_document_number',
        'customer_document_type',
        'customer_name',
        'user_id',
        'sale_date',
        'subtotal',
        'tax',
        'discount',
        'total',
        'payment_method',
        'status',
        'notes'
    ];

    /**
     * Los atributos que deberían ser casteados.
     *
     * @var array
     */
    protected $casts = [
        'sale_date' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2'
    ];

    /**
     * Relación con el cliente (usando documento como clave)
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_document_number', 'document_number');
    }

    /**
     * Relación con el usuario (vendedor)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con los items de la venta
     */
    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Generar número de factura automáticamente al crear
     */
    protected static function booted()
    {
        static::creating(function ($sale) {
            $sale->invoice_number = 'FAC-'.date('Ymd').'-'.strtoupper(uniqid());
        });
    }

    /**
     * Calcular total de la venta
     */
    public function calculateTotal(): void
    {
        $this->total = $this->subtotal + $this->tax - $this->discount;
    }

    /**
     * Obtener el estado como texto legible
     */
    public function getStatusTextAttribute(): string
    {
        return [
            'pending' => 'Pendiente',
            'completed' => 'Completada',
            'cancelled' => 'Cancelada'
        ][$this->status] ?? $this->status;
    }
}