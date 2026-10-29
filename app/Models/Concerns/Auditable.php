<?php

namespace App\Models\Concerns;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Auditoría estándar del ERP (spatie/laravel-activitylog v5).
 * Cada modelo que use este trait registra automáticamente quién lo creó, modificó,
 * eliminó o restauró, y qué valores cambiaron (antes → después).
 *
 * En el modelo se puede definir:
 *   protected string $auditLog = 'productos';   // nombre del módulo en la bitácora
 *   protected array $auditExcept = ['stock'];   // campos que NO se auditan
 */
trait Auditable
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()                // audita los campos de $fillable...
            ->logExcept($this->auditExcept ?? [])  // ...menos los excluidos
            ->logOnlyDirty()               // solo lo que realmente cambió
            ->dontLogEmptyChanges()        // si no cambió nada, no registra
            ->useLogName($this->auditLog ?? $this->getTable())
            ->setDescriptionForEvent(fn (string $event) => match ($event) {
                'created' => 'creado',
                'updated' => 'actualizado',
                'deleted' => 'eliminado',
                'restored' => 'restaurado',
                default => $event,
            });
    }
}
