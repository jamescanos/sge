<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            $table->enum('document_type', ['NIT', 'CC', 'CE', 'PP'])->default('NIT');
            $table->string('document_number', 30)->unique();
            $table->string('name', 150);                     // Razón social
            $table->string('contact_name', 150)->nullable(); // Asesor comercial del proveedor
            $table->string('email', 150)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('address', 200)->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('providers');
    }
};
