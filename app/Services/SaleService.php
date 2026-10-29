<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleService
{
    // Clase 12: el stock lo mueve el InventoryService (kardex)
    public function __construct(private InventoryService $inventory) {}

    /**
     * Registra una venta completa dentro de una transacción.
     *
     * @param  array  $data  ['client_id' => int, 'sale_date' => 'Y-m-d', 'notes' => string, 'items' => [['product_id' => int, 'quantity' => int], ...]]
     */
    public function register(array $data, int $userId): Sale
    {
        return DB::transaction(function () use ($data, $userId) {
            // 0. Agrupar cantidades por producto (si el mismo producto se agregó en dos filas)
            $quantities = [];
            foreach ($data['items'] as $item) {
                $quantities[$item['product_id']] = ($quantities[$item['product_id']] ?? 0) + (int) $item['quantity'];
            }

            // 1. Validar stock y calcular subtotales
            $items = [];
            $subtotal = 0;

            foreach ($quantities as $productId => $quantity) {
                // lockForUpdate(): bloquea la fila hasta terminar la transacción (evita vender el mismo stock dos veces)
                $product = Product::lockForUpdate()->findOrFail($productId);

                if (! $product->active) {
                    throw ValidationException::withMessages([
                        'items' => "El producto «{$product->name}» está inactivo y no se puede vender.",
                    ]);
                }

                if ($product->stock < $quantity) {
                    throw ValidationException::withMessages([
                        'items' => "Stock insuficiente para «{$product->name}». Disponible: {$product->stock}, solicitado: {$quantity}.",
                    ]);
                }

                $lineSubtotal = $product->price * $quantity;
                $subtotal += $lineSubtotal;

                $items[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_price' => $product->price,
                    'subtotal' => $lineSubtotal,
                ];
            }

            $tax = round($subtotal * 0.19, 2);
            $total = round($subtotal + $tax, 2);

            // 2. Crear la cabecera de la venta
            $sale = Sale::create([
                'invoice_number' => $this->nextInvoiceNumber(),
                'client_id' => $data['client_id'],
                'user_id' => $userId,
                'sale_date' => $data['sale_date'],
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
                'status' => 'pagada',
                'notes' => $data['notes'] ?? null,
            ]);

            // 3. Crear los detalles y descontar stock
            foreach ($items as $item) {
                $sale->details()->create([
                    'product_id' => $item['product']->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                ]);

                // Descuento de stock + movimiento en el kardex (Clase 12)
                $this->inventory->exit($item['product'], $item['quantity'], 'venta', $sale, (float) $item['unit_price']);
            }

            return $sale;
        });
    }

    /**
     * Genera el siguiente número de factura: FV-00001, FV-00002, ...
     */
    private function nextInvoiceNumber(): string
    {
        $last = Sale::withTrashed()->orderByDesc('id')->first();
        $next = $last ? $last->id + 1 : 1;

        return 'FV-'.str_pad($next, 5, '0', STR_PAD_LEFT);
    }
}
