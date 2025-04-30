<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabla de ventas (sales)
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            
            // Relación con clientes (usando document_number como referencia)
            $table->string('customer_document_number')->nullable();
            $table->foreign('customer_document_number')
                  ->references('document_number')
                  ->on('customers')
                  ->nullOnDelete();
                  
            // Campos de respaldo de información del cliente
            $table->string('customer_document_type')->nullable();
            $table->string('customer_name')->nullable();
            
            $table->foreignId('user_id')->constrained(); // Vendedor
            $table->dateTime('sale_date');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->enum('payment_method', ['efectivo', 'tarjeta', 'transferencia', 'otro']);
            $table->enum('status', ['pendiente', 'completada', 'cancelada'])->default('pendiente');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Tabla de items de venta (sale_items)
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
    }
};