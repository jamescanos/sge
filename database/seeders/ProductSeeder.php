<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(InventoryService $inventory): void
    {
        $computacion = Category::firstOrCreate(['name' => 'Computación'], ['description' => 'Equipos de cómputo']);
        $accesorios = Category::firstOrCreate(['name' => 'Accesorios'], ['description' => 'Accesorios tecnológicos']);

        $products = [
            ['name' => 'Laptop Lenovo',    'price' => 4500000, 'stock' => 10, 'category_id' => $computacion->id],
            ['name' => 'Mouse Logitech',   'price' => 120000,  'stock' => 30, 'category_id' => $accesorios->id],
            ['name' => 'Teclado Mecánico', 'price' => 250000,  'stock' => 15, 'category_id' => $accesorios->id],
            ['name' => 'Monitor Samsung',  'price' => 850000,  'stock' => 4,  'category_id' => $computacion->id],
            ['name' => 'Diadema HyperX',   'price' => 320000,  'stock' => 8,  'category_id' => $accesorios->id],
        ];

        foreach ($products as $product) {
            $model = Product::firstOrCreate(['name' => $product['name']], $product + ['active' => true]);

            // Clase 12: saldo inicial en el kardex (si el producto aún no tiene movimientos)
            $inventory->opening($model);
        }
    }
}
