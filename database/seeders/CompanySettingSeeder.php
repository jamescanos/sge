<?php

namespace Database\Seeders;

use App\Models\CompanySetting;
use Illuminate\Database\Seeder;

class CompanySettingSeeder extends Seeder
{
    /**
     * Crea la identidad corporativa inicial (solo si todavía no existe).
     */
    public function run(): void
    {
        if (! CompanySetting::query()->exists()) {
            CompanySetting::create(CompanySetting::DEFAULTS);
        }
    }
}
