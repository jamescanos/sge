<?php

namespace App\Http\Requests;

class UpdateProductRequest extends StoreProductRequest
{
    /**
     * Clase 12: al editar NO se recibe el stock. Solo cambia por movimientos
     * (compras, ventas, anulaciones o ajustes con motivo), que quedan en el kardex.
     */
    public function rules(): array
    {
        return collect(parent::rules())->except('stock')->all();
    }
}
