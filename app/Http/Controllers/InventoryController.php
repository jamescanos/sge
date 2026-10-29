<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdjustmentRequest;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Services\InventoryService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\ValidationException;

class InventoryController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:ver-inventario', only: ['kardex', 'pdf']),
            new Middleware('permission:ajustar-inventario', only: ['adjust', 'storeAdjustment']),
        ];
    }

    /**
     * Kardex: movimientos de un producto con entradas, salidas y saldo.
     */
    public function kardex(Request $request)
    {
        $products = Product::withTrashed()->orderBy('name')->get(['id', 'name', 'stock', 'deleted_at']);
        $product = $request->filled('product') ? $products->firstWhere('id', (int) $request->input('product')) : null;

        $movements = $product
            ? $this->movementsQuery($request, $product)->paginate(20)->withQueryString()
            : null;

        return view('inventory.kardex', [
            'products' => $products,
            'product' => $product,
            'movements' => $movements,
            'totals' => $product ? $this->totals($request, $product) : null,
            'concepts' => InventoryMovement::CONCEPTS,
        ]);
    }

    public function pdf(Request $request, Product $product)
    {
        return Pdf::loadView('pdf.kardex', [
            'product' => $product,
            'movements' => $this->movementsQuery($request, $product)->get(),
            'totals' => $this->totals($request, $product),
            'from' => $request->input('from'),
            'to' => $request->input('to'),
        ])->setPaper('letter', 'landscape')->stream("kardex-{$product->id}.pdf");
    }

    public function adjust(Product $product)
    {
        return view('inventory.adjust', [
            'product' => $product,
            'lastMovements' => $product->movements()->latest('id')->limit(5)->get(),
        ]);
    }

    public function storeAdjustment(StoreAdjustmentRequest $request, Product $product, InventoryService $inventory)
    {
        try {
            $movement = $inventory->adjust($product, $request->integer('counted_stock'), $request->validated('reason'));
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        if (! $movement) {
            return back()->with('error', 'El conteo coincide con el stock del sistema: no se registró ningún ajuste.');
        }

        return redirect()->route('inventory.kardex', ['product' => $product->id])
            ->with('success', "Ajuste registrado: {$movement->type} de {$movement->quantity} unidades. Nuevo stock: {$movement->balance}.");
    }

    private function movementsQuery(Request $request, Product $product)
    {
        return $product->movements()
            ->with(['user:id,name', 'reference'])
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->input('to')))
            ->when($request->filled('concept'), fn ($q) => $q->where('concept', $request->input('concept')))
            ->orderBy('id');
    }

    private function totals(Request $request, Product $product): array
    {
        $query = $this->movementsQuery($request, $product)->reorder();

        return [
            'entradas' => (int) (clone $query)->where('type', 'entrada')->sum('quantity'),
            'salidas' => (int) (clone $query)->where('type', 'salida')->sum('quantity'),
            // Regla de control: el último saldo del kardex debe coincidir con el stock del producto
            'cuadra' => (int) ($product->movements()->latest('id')->value('balance') ?? 0) === (int) $product->stock,
        ];
    }
}
