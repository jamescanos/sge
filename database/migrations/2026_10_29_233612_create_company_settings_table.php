<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Identidad corporativa del ERP: una sola fila con los datos de la organización
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->string('trade_name', 80);              // nombre comercial (el que se ve en el menú)
            $table->string('legal_name', 150);             // razón social (documentos PDF)
            $table->string('nit', 20);
            $table->string('slogan', 120)->nullable();
            $table->string('address', 150)->nullable();
            $table->string('city', 80)->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('email', 120)->nullable();
            $table->string('website', 150)->nullable();
            $table->string('primary_color', 7)->default('#0f766e'); // color de marca en formato #RRGGBB
            $table->string('logo_path')->nullable();       // ruta del logo dentro del disco «public»
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};
