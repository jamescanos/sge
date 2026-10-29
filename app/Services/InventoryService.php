<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * ÚNICO punto del ERP que modifica el stock. Cada cambio deja un movimiento en el kardex
 * con el saldo resultante, quién lo hizo y el documento que lo originó.
 */
class InventoryService
{
    /**
     * Entrada de mercancía (aumenta el stock).
     */
    public function entry(Product $product, int $quantity, string $concept, ?Model $reference = null,
        ?float $unitCost = null, ?string $notes = null): InventoryMovement
    {
        return $this->move($product, 'entrada', $quantity, $concept, $reference, $unitCost, $notes);
    }

    /**
     * Salida de mercancía (disminuye el stock). Nunca deja el stock en negativo.
     */
    public function exit(Product $product, int $quantity, string $concept, ?Model $reference = null,
        ?float $unitCost = null, ?string $notes = null): InventoryMovement
    {
        return $this->move($product, 'salida', $quantity, $concept, $reference, $unitCost, $notes);
    }

    /**
     * Ajuste por conteo físico: lleva el stock al valor contado y registra la diferencia.
     */
    public function adjust(Product $product, int $countedStock, string $reason): ?InventoryMovement
    {
        return DB::transaction(function () use ($product, $countedStock, $reason) {
            $product = Product::withTrashed()->lockForUpdate()->findOrFail($product->id);
            $difference = $countedStock - $product->stock;

            if ($difference === 0) {
                return null; // el conteo coincide: no hay nada que ajustar
            }

            return $difference > 0
                ? $this->move($product, 'entrada', $difference, 'ajuste', null, null, $reason)
                : $this->move($product, 'salida', -$difference, 'ajuste', null, null, $reason);
        });
    }

    /**
     * Registra el stock existente como «inventario inicial» (productos nuevos o anteriores al kardex).
     */
    public function opening(Product $product): ?InventoryMovement
    {
        if ($product->stock <= 0 || $product->movements()->exists()) {
            return null;
        }

        return InventoryMovement::create([
            'product_id' => $product->id,
            'type' => 'entrada',
            'concept' => 'inventario_inicial',
            'quantity' => $product->stock,
            'balance' => $product->stock,
            'user_id' => auth()->id(),
            'notes' => 'Saldo inicial del producto',
        ]);
    }

    private function move(Product $product, string $type, int $quantity, string $concept, ?Model $reference,
        ?float $unitCost, ?string $notes): InventoryMovement
    {
        if ($quantity <= 0) {
            throw ValidationException::withMessages(['quantity' => 'La cantidad del movimiento debe ser mayor que cero.']);
        }

        return DB::transaction(function () use ($product, $type, $quantity, $concept, $reference, $unitCost, $notes) {
            // Se vuelve a leer y bloquear la fila: el saldo siempre parte del valor real en la BD
            $product = Product::withTrashed()->lockForUpdate()->findOrFail($product->id);

            $balance = $type === 'entrada' ? $product->stock + $quantity : $product->stock - $quantity;

            if ($balance < 0) {
                throw ValidationException::withMessages([
                    'items' => "Stock insuficiente para «{$product->name}». Disponible: {$product->stock}, solicitado: {$quantity}.",
                ]);
            }

            $product->update(['stock' => $balance]);

            return InventoryMovement::create([
                'product_id' => $product->id,
                'type' => $type,
                'concept' => $concept,
                'quantity' => $quantity,
                'balance' => $balance,
                'unit_cost' => $unitCost,
                'reference_type' => $reference?->getMorphClass(),
                'reference_id' => $reference?->getKey(),
                'user_id' => auth()->id(),
                'notes' => $notes,
            ]);
        });
    }
}
