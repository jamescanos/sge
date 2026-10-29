<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_number', 20)->unique();                   // OC-00001
            $table->foreignId('provider_id')->constrained()->onDelete('restrict');
            $table->foreignId('user_id')->constrained()->onDelete('restrict'); // Quién registró la orden
            $table->date('purchase_date');
            $table->string('provider_invoice', 50)->nullable();                 // Factura del proveedor
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('tax', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->enum('status', ['pendiente', 'parcial', 'recibida', 'anulada'])->default('pendiente');
            $table->timestamp('received_at')->nullable();                       // Cuándo se completó la recepción
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
