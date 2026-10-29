<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        Category::firstOrCreate(['name' => 'Electrónicos'], ['description' => 'Equipos electrónicos']);
        Category::firstOrCreate(['name' => 'Computación'], ['description' => 'Equipos de cómputo']);
        Category::firstOrCreate(['name' => 'Accesorios'], ['description' => 'Accesorios tecnológicos']);
    }
}
