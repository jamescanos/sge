<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use Auditable, HasFactory, SoftDeletes;

    // Auditoría (Clase 12)
    protected string $auditLog = 'productos';

    // El stock NO se audita aquí: sus cambios quedan en el kardex (inventory_movements)
    protected array $auditExcept = ['stock'];

    protected $fillable = [
        'name', 'description', 'price', 'stock', 'category_id', 'active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock' => 'integer',
            'active' => 'boolean',
        ];
    }

    /**
     * Relación: Product pertenece a Category (incluida si está en la papelera).
     *
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class)->withTrashed();
    }

    // Relación: un producto aparece en muchos detalles de venta
    public function saleDetails()
    {
        return $this->hasMany(SaleDetail::class);
    }

    // Relación: un producto aparece en muchos detalles de compra (Clase 10)
    public function purchaseDetails()
    {
        return $this->hasMany(PurchaseDetail::class);
    }

    // Relación: movimientos del kardex (Clase 12)
    public function movements()
    {
        return $this->hasMany(InventoryMovement::class);
    }
}
