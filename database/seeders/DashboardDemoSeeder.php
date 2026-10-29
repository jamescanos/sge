<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Product;
use App\Models\Provider;
use App\Models\Sale;
use App\Models\User;
use App\Services\PurchaseService;
use App\Services\SaleService;
use Illuminate\Database\Seeder;

/**
 * OPCIONAL: compras y ventas de los últimos 6 meses para ver los gráficos del Dashboard con datos.
 * Usa los mismos servicios del ERP, así que el stock y el kardex quedan coherentes.
 * No se incluye en DatabaseSeeder: se ejecuta a mano y una sola vez.
 */
class DashboardDemoSeeder extends Seeder
{
    private const NOTE = 'Datos de demostración (dashboard)';

    public function run(PurchaseService $purchases, SaleService $sales): void
    {
        if (Sale::where('notes', self::NOTE)->exists()) {
            $this->command?->warn('Los datos de demostración ya existen. No se agregó nada.');

            return;
        }

        $user = User::role('admin')->first() ?? User::first();
        $products = Product::where('active', true)->get();
        $providers = Provider::where('active', true)->pluck('id');
        $clients = Client::where('active', true)->pluck('id');

        if (! $user || $products->isEmpty() || $providers->isEmpty() || $clients->isEmpty()) {
            $this->command?->error('Se necesitan usuarios, productos, proveedores y clientes activos.');

            return;
        }

        mt_srand(2026); // siempre los mismos datos

        foreach (range(5, 0) as $monthsAgo) {
            $month = now()->startOfMonth()->subMonths($monthsAgo);
            $lastDay = $monthsAgo === 0 ? now()->day : $month->daysInMonth;

            // 1. Una compra al inicio del mes, recibida completa (aumenta el stock)
            $items = $products->shuffle()->take(2)->map(fn ($p) => [
                'product_id' => $p->id,
                'quantity' => mt_rand(3, 6),
                'unit_cost' => round($p->price * 0.7, 2),
            ])->values()->all();

            $purchase = $purchases->register([
                'provider_id' => $providers->random(),
                'purchase_date' => $month->copy()->addDays(1)->toDateString(),
                'notes' => self::NOTE,
                'items' => $items,
            ], $user->id);
            $purchases->receive($purchase, $purchase->details->pluck('quantity', 'id')->all(), 'FP-DEMO-'.$purchase->id);

            // 2. Entre 6 y 10 ventas en días distintos del mes (solo con el stock disponible)
            foreach (range(1, mt_rand(6, 10)) as $i) {
                $product = Product::whereKey($products->random()->id)->first();
                $quantity = min(mt_rand(1, 3), $product->stock);

                if ($quantity < 1) {
                    continue;
                }

                $sales->register([
                    'client_id' => $clients->random(),
                    'sale_date' => $month->copy()->addDays(mt_rand(1, $lastDay) - 1)->toDateString(),
                    'notes' => self::NOTE,
                    'items' => [['product_id' => $product->id, 'quantity' => $quantity]],
                ], $user->id);
            }
        }

        $this->command?->info('Datos de demostración creados: 6 compras recibidas y sus ventas.');
    }
}
