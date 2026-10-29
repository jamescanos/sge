<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'provider_id' => ['required', Rule::exists('providers', 'id')->whereNull('deleted_at')->where('active', true)],
            'purchase_date' => ['required', 'date', 'before_or_equal:today'],
            'provider_invoice' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:500'],

            // Detalle de la orden de compra
            'items' => ['required', 'array', 'min:1'],
            // distinct: el mismo producto no puede aparecer en dos filas
            'items.*.product_id' => ['required', 'distinct', Rule::exists('products', 'id')->whereNull('deleted_at')],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    public function attributes(): array
    {
        return [
            'provider_id' => 'proveedor',
            'purchase_date' => 'fecha de compra',
            'provider_invoice' => 'factura del proveedor',
            'items' => 'productos',
            'items.*.product_id' => 'producto',
            'items.*.quantity' => 'cantidad',
            'items.*.unit_cost' => 'costo unitario',
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Debe agregar al menos un producto a la orden de compra.',
            'items.min' => 'Debe agregar al menos un producto a la orden de compra.',
            'items.*.product_id.distinct' => 'Hay productos repetidos: se debe usar una sola fila por producto.',
        ];
    }
}
