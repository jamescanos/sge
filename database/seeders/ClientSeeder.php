<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $clients = [
            ['document_type' => 'CC',  'document_number' => '1088123456', 'name' => 'Juan Pérez',         'email' => 'juan@test.com',      'phone' => '3001234567', 'address' => 'Calle 10 #20-30'],
            ['document_type' => 'NIT', 'document_number' => '900123456',  'name' => 'TecnoSolutions SAS', 'email' => 'contacto@tecno.com', 'phone' => '3109876543', 'address' => 'Carrera 5 #45-10'],
            ['document_type' => 'CC',  'document_number' => '1098765432', 'name' => 'María Gómez',        'email' => 'maria@test.com',     'phone' => '3201112233', 'address' => 'Av. Principal #12-34'],
            ['document_type' => 'CE',  'document_number' => 'E1234567',   'name' => 'Carlos Rodríguez',   'email' => 'carlos@test.com',    'phone' => '3154445566', 'address' => 'Transversal 8 #90-12'],
            ['document_type' => 'CC',  'document_number' => '1055443322', 'name' => 'Ana Martínez',       'email' => 'ana@test.com',       'phone' => '3187778899', 'address' => 'Diagonal 15 #40-50'],
        ];

        // firstOrCreate: si el documento ya existe no lo duplica (se puede ejecutar varias veces)
        foreach ($clients as $client) {
            Client::firstOrCreate(['document_number' => $client['document_number']], $client);
        }
    }
}
