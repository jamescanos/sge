<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseService
{
    // Clase 12: el stock lo mueve el InventoryService (kardex)
    public function __construct(private InventoryService $inventory) {}

    /**
     * Registra una ORDEN DE COMPRA (estado "pendiente"). Todavía NO mueve el inventario:
     * el stock solo aumenta cuando la mercancía llega físicamente (método receive).
     *
     * @param  array  $data  ['provider_id' => int, 'purchase_date' => 'Y-m-d', 'provider_invoice' => ?string, 'notes' => ?string,
     *                       'items' => [['product_id' => int, 'quantity' => int, 'unit_cost' => float], ...]]
     */
    public function register(array $data, int $userId): Purchase
    {
        return DB::transaction(function () use ($data, $userId) {
            // 1. Calcular subtotales de cada línea
            $items = [];
            $subtotal = 0;

            foreach ($data['items'] as $item) {
                $quantity = (int) $item['quantity'];
                $unitCost = round((float) $item['unit_cost'], 2);
                $lineSubtotal = round($quantity * $unitCost, 2);

                $items[] = [
                    'product_id' => $item['product_id'],
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'subtotal' => $lineSubtotal,
                ];

                $subtotal += $lineSubtotal;
            }

            $tax = round($subtotal * 0.19, 2);
            $total = round($subtotal + $tax, 2);

            // 2. Crear la cabecera de la orden de compra
            $purchase = Purchase::create([
                'purchase_number' => $this->nextPurchaseNumber(),
                'provider_id' => $data['provider_id'],
                'user_id' => $userId,
                'purchase_date' => $data['purchase_date'],
                'provider_invoice' => $data['provider_invoice'] ?? null,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
                'status' => 'pendiente',
                'notes' => $data['notes'] ?? null,
            ]);

            // 3. Crear los detalles (received_quantity arranca en 0)
            $purchase->details()->createMany($items);

            return $purchase;
        });
    }

    /**
     * Registra la RECEPCIÓN (total o parcial) de la mercancía y AUMENTA el stock.
     *
     * @param  array  $quantities  [id_del_detalle => cantidad_que_llega, ...]
     */
    public function receive(Purchase $purchase, array $quantities, ?string $providerInvoice = null): Purchase
    {
        return DB::transaction(function () use ($purchase, $quantities, $providerInvoice) {
            // lockForUpdate(): dos almacenistas no pueden recibir la misma orden al mismo tiempo
            $purchase = Purchase::lockForUpdate()->findOrFail($purchase->id);
            $purchase->load('details.product');

            // Regla 1: solo se recibe mercancía de órdenes pendientes o parciales
            if (! $purchase->canBeReceived()) {
                throw ValidationException::withMessages([
                    'items' => "La orden {$purchase->purchase_number} está «{$purchase->status}» y no admite recepciones.",
                ]);
            }

            // Regla 2: los detalles enviados deben pertenecer a esta orden
            $unknown = array_diff(array_keys($quantities), $purchase->details->pluck('id')->all());
            if (! empty($unknown)) {
                throw ValidationException::withMessages([
                    'items' => 'Se enviaron líneas que no pertenecen a esta orden de compra.',
                ]);
            }

            $receivedSomething = false;

            foreach ($purchase->details as $detail) {
                $quantity = (int) ($quantities[$detail->id] ?? 0);

                if ($quantity === 0) {
                    continue;
                }

                // Regla 3: no se puede recibir más de lo pedido
                if ($quantity > $detail->pendingQuantity()) {
                    throw ValidationException::withMessages([
                        'items' => "No se puede recibir más de lo pedido para «{$detail->product?->name}». "
                            ."Pendiente: {$detail->pendingQuantity()}, recibido ahora: {$quantity}.",
                    ]);
                }

                // Actualizar la línea y AUMENTAR el stock del producto
                $detail->increment('received_quantity', $quantity);

                $this->inventory->entry(
                    Product::withTrashed()->findOrFail($detail->product_id),
                    $quantity,
                    'compra',
                    $purchase,
                    (float) $detail->unit_cost
                );

                $receivedSomething = true;
            }

            // Regla 4: una recepción debe traer al menos una unidad
            if (! $receivedSomething) {
                throw ValidationException::withMessages([
                    'items' => 'Debe ingresar al menos una cantidad mayor que cero para registrar la recepción.',
                ]);
            }

            // 4. Recalcular el estado de la orden
            $purchase->load('details');
            $complete = $purchase->details->every(fn ($d) => $d->received_quantity >= $d->quantity);

            $purchase->update([
                'status' => $complete ? 'recibida' : 'parcial',
                'received_at' => $complete ? now() : null,
                'provider_invoice' => $providerInvoice ?: $purchase->provider_invoice,
            ]);

            return $purchase;
        });
    }

    /**
     * Anula una orden de compra. Regla: solo si NO ha llegado mercancía (estado "pendiente"),
     * por lo tanto no hay stock que devolver.
     */
    public function cancel(Purchase $purchase): void
    {
        if (! $purchase->canBeCancelled()) {
            throw ValidationException::withMessages([
                'purchase' => "La orden {$purchase->purchase_number} ya tiene mercancía recibida y no se puede anular.",
            ]);
        }

        DB::transaction(function () use ($purchase) {
            $purchase->update(['status' => 'anulada']);
            $purchase->delete();
        });
    }

    /**
     * Genera el siguiente número de orden de compra: OC-00001, OC-00002, ...
     */
    private function nextPurchaseNumber(): string
    {
        $last = Purchase::withTrashed()->orderByDesc('id')->first();
        $next = $last ? $last->id + 1 : 1;

        return 'OC-'.str_pad($next, 5, '0', STR_PAD_LEFT);
    }
}
