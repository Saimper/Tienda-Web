<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nombre del producto
            $table->string('sku')->unique(); // SKU único para el producto
            $table->decimal('price', 10, 2); // Precio del producto
            $table->integer('stock'); // Cantidad disponible en inventario
            $table->text('description')->nullable(); // Descripción del producto
            $table->string('image')->nullable(); // Ruta o nombre de la imagen (opcional)
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade'); // Relación con la categoría
         
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
