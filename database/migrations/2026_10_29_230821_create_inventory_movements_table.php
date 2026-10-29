<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('restrict');
            $table->enum('type', ['entrada', 'salida']);
            // Motivo del movimiento: inventario_inicial, compra, venta, anulacion_venta, ajuste
            $table->string('concept', 30);
            $table->unsignedInteger('quantity');            // siempre positiva; el tipo indica el sentido
            $table->integer('balance');                     // stock del producto DESPUÉS del movimiento
            $table->decimal('unit_cost', 12, 2)->nullable(); // costo o precio unitario de referencia
            $table->nullableMorphs('reference');            // documento que lo originó (Sale, Purchase...)
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('notes', 255)->nullable();       // motivo obligatorio en los ajustes
            $table->timestamps();

            $table->index(['product_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
