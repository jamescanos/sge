<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    // Etiquetas para mostrar el concepto en pantalla
    public const CONCEPTS = [
        'inventario_inicial' => 'Inventario inicial',
        'compra' => 'Compra (recepción)',
        'venta' => 'Venta',
        'anulacion_venta' => 'Anulación de venta',
        'ajuste' => 'Ajuste de inventario',
    ];

    protected $fillable = [
        'product_id', 'type', 'concept', 'quantity', 'balance',
        'unit_cost', 'reference_type', 'reference_id', 'user_id', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'balance' => 'integer',
            'unit_cost' => 'decimal:2',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Documento que originó el movimiento: una venta, una compra o nada (ajuste/inventario inicial).
    // withTrashed(): una venta anulada queda en la papelera, pero su número debe seguir en el kardex
    public function reference()
    {
        return $this->morphTo()->withTrashed();
    }

    public function conceptLabel(): string
    {
        return self::CONCEPTS[$this->concept] ?? $this->concept;
    }

    // Texto corto del documento de referencia: FV-00001, OC-00001...
    public function referenceLabel(): ?string
    {
        return $this->reference?->invoice_number ?? $this->reference?->purchase_number;
    }
}
