<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Product;
use App\Models\Provider;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        $modules = collect([
            [
                'title' => 'Productos',
                'description' => $user->can('crear-productos') ? 'Administra el inventario.' : 'Consulta el inventario.',
                'route' => 'products.index',
                'permission' => 'ver-productos',
                'icon' => 'cube',
            ],
            [
                'title' => 'Categorías',
                'description' => $user->can('crear-categorias') ? 'Organiza los productos.' : 'Consulta las categorías.',
                'route' => 'categories.index',
                'permission' => 'ver-categorias',
                'icon' => 'tag',
            ],
            [
                'title' => 'Clientes',
                'description' => $user->can('crear-clientes') ? 'Administra los clientes.' : 'Consulta los clientes.',
                'route' => 'clients.index',
                'permission' => 'ver-clientes',
                'icon' => 'users',
            ],
            [
                'title' => 'Ventas',
                'description' => $user->can('crear-ventas') ? 'Registra y consulta ventas.' : 'Consulta las ventas.',
                'route' => 'sales.index',
                'permission' => 'ver-ventas',
                'icon' => 'cart',
            ],
            [
                'title' => 'Proveedores',
                'description' => $user->can('crear-proveedores') ? 'Administra los proveedores.' : 'Consulta los proveedores.',
                'route' => 'providers.index',
                'permission' => 'ver-proveedores',
                'icon' => 'building',
            ],
            [
                'title' => 'Compras',
                'description' => $user->can('crear-compras') ? 'Registra órdenes y recibe mercancía.' : 'Consulta las compras.',
                'route' => 'purchases.index',
                'permission' => 'ver-compras',
                'icon' => 'truck',
            ],
            [
                'title' => 'Configuración',
                'description' => 'Usuarios, roles y permisos.',
                'route' => 'settings.index',
                'permission' => 'ver-usuarios',
                'icon' => 'settings',
            ],
        ])->filter(fn ($m) => $user->can($m['permission']))->values();

        // KPIs: cada tarjeta lleva un ícono y un tono de color (ver dashboard.blade.php)
        $stats = collect();

        if ($user->can('ver-productos')) {
            $stats->push(['label' => 'Productos activos', 'value' => Product::where('active', true)->count(), 'icon' => 'cube', 'tone' => 'indigo']);
            $stats->push(['label' => 'Stock bajo (≤ 5)', 'value' => Product::where('active', true)->where('stock', '<=', 5)->count(), 'icon' => 'alert', 'tone' => 'amber']);
            $stats->push([
                'label' => 'Valor del inventario',
                'value' => '$'.number_format((float) Product::where('active', true)->sum(DB::raw('price * stock')), 0),
                'icon' => 'banknotes',
                'tone' => 'emerald',
            ]);
        }

        if ($user->can('ver-clientes')) {
            $stats->push(['label' => 'Clientes activos', 'value' => Client::where('active', true)->count(), 'icon' => 'users', 'tone' => 'sky']);
        }

        $salesMonthTotal = 0;
        if ($user->can('ver-ventas')) {
            $salesThisMonth = Sale::whereMonth('sale_date', now()->month)
                ->whereYear('sale_date', now()->year);
            $salesMonthTotal = (float) $salesThisMonth->sum('total');
            $stats->push([
                'label' => 'Ventas del mes',
                'value' => '$'.number_format($salesMonthTotal, 0),
                'hint' => $salesThisMonth->count().' factura(s)',
                'icon' => 'cart',
                'tone' => 'brand',
            ]);
        }

        // --- KPIs de compras (Clase 10) ---
        $purchasesMonthTotal = 0;
        if ($user->can('ver-compras')) {
            $purchasesThisMonth = Purchase::whereMonth('purchase_date', now()->month)
                ->whereYear('purchase_date', now()->year);
            $purchasesMonthTotal = (float) $purchasesThisMonth->sum('total');
            $stats->push([
                'label' => 'Compras del mes',
                'value' => '$'.number_format($purchasesMonthTotal, 0),
                'hint' => $purchasesThisMonth->count().' orden(es)',
                'icon' => 'truck',
                'tone' => 'violet',
            ]);
            $stats->push([
                'label' => 'Órdenes por recibir',
                'value' => Purchase::whereIn('status', ['pendiente', 'parcial'])->count(),
                'icon' => 'clock',
                'tone' => 'orange',
            ]);
        }

        if ($user->can('ver-proveedores')) {
            $stats->push(['label' => 'Proveedores activos', 'value' => Provider::where('active', true)->count(), 'icon' => 'building', 'tone' => 'slate']);
        }

        // Indicador financiero simple: lo que entró por ventas menos lo que salió por compras en el mes
        if ($user->can('ver-reportes')) {
            $balance = $salesMonthTotal - $purchasesMonthTotal;
            $stats->push([
                'label' => 'Ventas − Compras del mes',
                'value' => ($balance < 0 ? '-$' : '$').number_format(abs($balance), 0),
                'icon' => 'trending',
                'tone' => $balance < 0 ? 'rose' : 'emerald',
            ]);
        }

        // Top 5 productos más vendidos del mes
        $topProducts = collect();
        if ($user->can('ver-reportes') || $user->can('ver-ventas')) {
            $topProducts = SaleDetail::query()
                ->select('product_id', DB::raw('SUM(quantity) as total_sold'))
                ->whereHas('sale', function ($q) {
                    $q->whereMonth('sale_date', now()->month)
                        ->whereYear('sale_date', now()->year);
                })
                ->with('product:id,name')
                ->groupBy('product_id')
                ->orderByDesc('total_sold')
                ->limit(5)
                ->get();
        }

        // Sugerencia de reabastecimiento: productos activos con stock bajo (Clase 10)
        $lowStock = collect();
        if ($user->can('crear-compras')) {
            $lowStock = Product::where('active', true)
                ->where('stock', '<=', 5)
                ->orderBy('stock')
                ->limit(10)
                ->get(['id', 'name', 'stock']);
        }

        return view('dashboard', [
            'user' => $user,
            'roles' => $user->getRoleNames(),
            'activeRole' => $user->activeRole()?->name,
            'modules' => $modules,
            'stats' => $stats,
            'topProducts' => $topProducts,
            'lowStock' => $lowStock,
            'charts' => $this->charts($user, $topProducts),
        ]);
    }

    /**
     * Datos de los gráficos del Dashboard (configuración visual).
     * Cada gráfico: tipo de Chart.js, etiquetas y series. Solo se arma si el usuario tiene el permiso.
     */
    private function charts($user, $topProducts): array
    {
        $charts = [];

        // 1. Barras: ventas y compras de los últimos 6 meses
        if ($user->can('ver-ventas') || $user->can('ver-compras')) {
            $months = collect(range(5, 0))->map(fn ($i) => now()->startOfMonth()->subMonths($i));
            $from = $months->first()->copy();

            $datasets = [];
            if ($user->can('ver-ventas')) {
                $sales = Sale::where('sale_date', '>=', $from)
                    ->selectRaw("DATE_FORMAT(sale_date, '%Y-%m') AS ym, SUM(total) AS total")
                    ->groupBy('ym')->pluck('total', 'ym');
                $datasets[] = ['label' => 'Ventas', 'data' => $months->map(fn ($m) => (float) ($sales[$m->format('Y-m')] ?? 0))->all()];
            }
            if ($user->can('ver-compras')) {
                $purchases = Purchase::where('purchase_date', '>=', $from)->where('status', '!=', 'anulada')
                    ->selectRaw("DATE_FORMAT(purchase_date, '%Y-%m') AS ym, SUM(total) AS total")
                    ->groupBy('ym')->pluck('total', 'ym');
                $datasets[] = ['label' => 'Compras', 'data' => $months->map(fn ($m) => (float) ($purchases[$m->format('Y-m')] ?? 0))->all()];
            }

            $charts[] = [
                'id' => 'sales-purchases',
                'title' => 'Ventas vs. compras',
                'subtitle' => 'Últimos 6 meses (con IVA)',
                'type' => 'bar',
                'money' => true,
                'wide' => true,
                'labels' => $months->map(fn ($m) => Str::ucfirst($m->locale('es')->isoFormat('MMM YY')))->all(),
                'datasets' => $datasets,
            ];
        }

        // 2. Línea: ventas diarias de los últimos 30 días
        if ($user->can('ver-ventas')) {
            $days = collect(range(29, 0))->map(fn ($i) => now()->startOfDay()->subDays($i));
            $daily = Sale::where('sale_date', '>=', $days->first())
                ->selectRaw('DATE(sale_date) AS day, SUM(total) AS total')
                ->groupBy('day')->pluck('total', 'day');

            $charts[] = [
                'id' => 'daily-sales',
                'title' => 'Ventas diarias',
                'subtitle' => 'Últimos 30 días',
                'type' => 'line',
                'money' => true,
                'labels' => $days->map(fn (Carbon $d) => $d->format('d/m'))->all(),
                'datasets' => [['label' => 'Ventas', 'data' => $days->map(fn ($d) => (float) ($daily[$d->toDateString()] ?? 0))->all()]],
            ];
        }

        // 3. Donut: unidades en inventario por categoría
        if ($user->can('ver-productos')) {
            $byCategory = Product::where('active', true)
                ->selectRaw('category_id, SUM(stock) AS units')
                ->groupBy('category_id')
                ->with('category:id,name')
                ->get();

            $charts[] = [
                'id' => 'stock-category',
                'title' => 'Inventario por categoría',
                'subtitle' => 'Unidades disponibles',
                'type' => 'doughnut',
                'labels' => $byCategory->map(fn ($row) => $row->category?->name ?? 'Sin categoría')->all(),
                'datasets' => [['label' => 'Unidades', 'data' => $byCategory->map(fn ($row) => (int) $row->units)->all()]],
            ];
        }

        // 4. Donut: órdenes de compra por estado (incluye las anuladas, que están en la papelera)
        if ($user->can('ver-compras')) {
            $byStatus = Purchase::withTrashed()
                ->selectRaw('status, COUNT(*) AS total')
                ->groupBy('status')->pluck('total', 'status');
            $statuses = ['pendiente' => 'Pendiente', 'parcial' => 'Parcial', 'recibida' => 'Recibida', 'anulada' => 'Anulada'];

            $charts[] = [
                'id' => 'purchase-status',
                'title' => 'Órdenes de compra',
                'subtitle' => 'Por estado',
                'type' => 'doughnut',
                'labels' => array_values($statuses),
                'datasets' => [['label' => 'Órdenes', 'data' => collect($statuses)->keys()->map(fn ($s) => (int) ($byStatus[$s] ?? 0))->all()]],
            ];
        }

        // 5. Barras horizontales: top 5 productos más vendidos del mes
        if ($topProducts->isNotEmpty() || $user->can('ver-ventas')) {
            $charts[] = [
                'id' => 'top-products',
                'title' => 'Top 5 productos del mes',
                'subtitle' => 'Unidades vendidas',
                'type' => 'bar',
                'horizontal' => true,
                'labels' => $topProducts->map(fn ($item) => $item->product?->name)->all(),
                'datasets' => [['label' => 'Unidades', 'data' => $topProducts->map(fn ($item) => (int) $item->total_sold)->all()]],
            ];
        }

        // Un gráfico sin ningún dato se muestra como «Sin datos» en lugar de un lienzo vacío
        return collect($charts)->map(function ($chart) {
            $chart['empty'] = collect($chart['datasets'])->flatMap(fn ($ds) => $ds['data'])->sum() == 0;

            return $chart;
        })->all();
    }
}
