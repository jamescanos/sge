<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use Auditable, SoftDeletes;

    // Auditoría (Clase 12)
    protected string $auditLog = 'compras';

    protected $fillable = [
        'purchase_number',
        'provider_id',
        'user_id',
        'purchase_date',
        'provider_invoice',
        'subtotal',
        'tax',
        'total',
        'status',
        'received_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'received_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class)->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(PurchaseDetail::class);
    }

    // ¿Todavía se puede recibir mercancía de esta orden?
    public function canBeReceived(): bool
    {
        return in_array($this->status, ['pendiente', 'parcial']);
    }

    // Regla de negocio: solo se anula una orden a la que no ha llegado nada
    public function canBeCancelled(): bool
    {
        return $this->status === 'pendiente';
    }
}
