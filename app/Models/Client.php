<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use Auditable, SoftDeletes;

    // Auditoría (Clase 12)
    protected string $auditLog = 'clientes';

    protected $fillable = [
        'document_type',
        'document_number',
        'name',
        'email',
        'phone',
        'address',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    // Relación: un cliente tiene muchas ventas
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}
