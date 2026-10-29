<?php

namespace Database\Seeders;

use App\Models\Provider;
use Illuminate\Database\Seeder;

class ProviderSeeder extends Seeder
{
    public function run(): void
    {
        $providers = [
            ['document_type' => 'NIT', 'document_number' => '900111222', 'name' => 'Mayorista Digital SAS',     'contact_name' => 'Laura Ríos',    'email' => 'ventas@mayoristadigital.com', 'phone' => '6012345678', 'address' => 'Bogotá, Calle 80 #45-12'],
            ['document_type' => 'NIT', 'document_number' => '900333444', 'name' => 'Importadora Tech Andina',   'contact_name' => 'Pedro Salazar', 'email' => 'pedidos@techandina.com',      'phone' => '6044567890', 'address' => 'Medellín, Carrera 43A #1-50'],
            ['document_type' => 'NIT', 'document_number' => '900555666', 'name' => 'Accesorios del Pacífico',   'contact_name' => 'Diana Mejía',   'email' => 'comercial@accpacifico.com',   'phone' => '6027654321', 'address' => 'Cali, Av. 6N #23-45'],
            ['document_type' => 'CC',  'document_number' => '1094555666', 'name' => 'Andrés Cardona (independiente)', 'contact_name' => null, 'email' => 'andres.cardona@test.com', 'phone' => '3135556677', 'address' => 'Cartago, Calle 12 #5-30'],
        ];

        // firstOrCreate: si el documento ya existe no lo duplica (se puede ejecutar varias veces)
        foreach ($providers as $provider) {
            Provider::firstOrCreate(['document_number' => $provider['document_number']], $provider);
        }
    }
}
