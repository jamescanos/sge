<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['required', Rule::exists('clients', 'id')->whereNull('deleted_at')->where('active', true)],
            'sale_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],

            // Detalle de la venta
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', Rule::exists('products', 'id')->whereNull('deleted_at')->where('active', true)],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function attributes(): array
    {
        return [
            'client_id' => 'cliente',
            'sale_date' => 'fecha de venta',
            'items' => 'productos',
            'items.*.product_id' => 'producto',
            'items.*.quantity' => 'cantidad',
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Debe agregar al menos un producto a la venta.',
            'items.min' => 'Debe agregar al menos un producto a la venta.',
        ];
    }
}
