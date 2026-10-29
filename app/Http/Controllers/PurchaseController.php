<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReceivePurchaseRequest;
use App\Http\Requests\StorePurchaseRequest;
use App\Models\Product;
use App\Models\Provider;
use App\Models\Purchase;
use App\Services\PurchaseService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\ValidationException;

class PurchaseController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:ver-compras', only: ['index', 'show', 'pdf']),
            new Middleware('permission:crear-compras', only: ['create', 'store']),
            new Middleware('permission:recibir-compras', only: ['receive']),
            new Middleware('permission:eliminar-compras', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $purchases = Purchase::query()
            ->with(['provider', 'user'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = '%'.$request->string('search').'%';
                $q->where(function ($q) use ($search) {
                    $q->where('purchase_number', 'like', $search)
                        ->orWhere('provider_invoice', 'like', $search)
                        ->orWhereHas('provider', fn ($q) => $q->where('name', 'like', $search));
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('purchase_date', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('purchase_date', '<=', $request->input('to')))
            ->orderByDesc('purchase_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('purchases.index', [
            'purchases' => $purchases,
            'totalMonth' => Purchase::whereMonth('purchase_date', now()->month)
                ->whereYear('purchase_date', now()->year)
                ->sum('total'),
            'pendingCount' => Purchase::whereIn('status', ['pendiente', 'parcial'])->count(),
        ]);
    }

    public function create()
    {
        return view('purchases.create', [
            'providers' => Provider::where('active', true)->orderBy('name')->get(['id', 'name', 'document_number']),
            // A diferencia de la venta, aquí SÍ se listan productos con stock 0 (son los que más se necesita comprar)
            'products' => Product::orderBy('name')->get(['id', 'name', 'price', 'stock']),
        ]);
    }

    public function store(StorePurchaseRequest $request, PurchaseService $service)
    {
        $purchase = $service->register($request->validated(), $request->user()->id);

        return redirect()->route('purchases.show', $purchase)
            ->with('success', "Orden de compra «{$purchase->purchase_number}» registrada. Pendiente de recepción.");
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['provider', 'user', 'details.product']);

        return view('purchases.show', ['purchase' => $purchase]);
    }

    public function receive(ReceivePurchaseRequest $request, Purchase $purchase, PurchaseService $service)
    {
        try {
            $purchase = $service->receive(
                $purchase,
                $request->validated('items'),
                $request->validated('provider_invoice')
            );
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        $message = $purchase->status === 'recibida'
            ? "Orden «{$purchase->purchase_number}» recibida completamente. Inventario actualizado."
            : "Recepción parcial registrada para «{$purchase->purchase_number}». Inventario actualizado.";

        return redirect()->route('purchases.show', $purchase)->with('success', $message);
    }

    public function destroy(Purchase $purchase, PurchaseService $service)
    {
        try {
            $service->cancel($purchase);
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        return redirect()->route('purchases.index')
            ->with('success', "Orden «{$purchase->purchase_number}» anulada.");
    }

    public function pdf(Purchase $purchase)
    {
        $purchase->load(['provider', 'user', 'details.product']);

        $pdf = Pdf::loadView('pdf.purchase', ['purchase' => $purchase])
            ->setPaper('letter');

        // stream(): abre el PDF en el navegador. download(): lo descarga directamente.
        return $pdf->stream("{$purchase->purchase_number}.pdf");
    }
}
