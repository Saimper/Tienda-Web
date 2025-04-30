<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Tipos de documento permitidos
     */
    const DOCUMENT_TYPES = [
        'CC' => 'Documento Nacional de Identidad',
        'RUT' => 'Registro Único Tributario',
        'PAS' => 'Pasaporte',
        'DNI' => 'Cédula extranjera',
        'OTRO' => 'Otro'
    ];

    /**
     * Atributos asignables masivamente
     * @var array
     */
    protected $fillable = [
        'document_type',
        'document_number',
        'name',
        'email',
        'phone',
        'address',
        'birthdate',
        'is_active'
    ];

    /**
     * Atributos que deberían ser casteados
     * @var array
     */
    protected $casts = [
        'birthdate' => 'date',
        'is_active' => 'boolean'
    ];

    /**
     * Atributos ocultos en arrays/JSON
     * @var array
     */
    protected $hidden = [
        'deleted_at'
    ];

    /**
     * Relación con las ventas del cliente
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class, 'customer_document_number', 'document_number');
    }

    /**
     * Accesor para el tipo de documento legible
     */
    public function getDocumentTypeNameAttribute(): string
    {
        return self::DOCUMENT_TYPES[$this->document_type] ?? $this->document_type;
    }

    /**
     * Accesor para el nombre completo con documento
     */
    public function getFullNameWithDocumentAttribute(): string
    {
        return "{$this->name} ({$this->document_type}: {$this->document_number})";
    }

    /**
     * Scope para clientes activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para búsqueda por documento o nombre
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where('document_number', 'like', "%{$search}%")
            ->orWhere('name', 'like', "%{$search}%");
    }

    /**
     * Validar formato de documento según tipo
     */
    public static function validateDocument(string $type, string $number): bool
    {
        return match($type) {
            'DNI' => strlen($number) === 8 && ctype_digit($number),
            'RUC' => strlen($number) === 11 && ctype_digit($number),
            default => true,
        };
    }
}