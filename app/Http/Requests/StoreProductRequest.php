<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    /**
     * La autorización se resuelve con el middleware de permisos del controlador.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normaliza el checkbox "active" (si no se envía, queda en false).
     */
    protected function prepareForValidation(): void
    {
        $this->merge(['active' => $this->boolean('active')]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'stock' => ['required', 'integer', 'min:0'],
            'category_id' => ['required', Rule::exists('categories', 'id')->whereNull('deleted_at')],
            'active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'description' => 'descripción',
            'price' => 'precio',
            'stock' => 'stock',
            'category_id' => 'categoría',
            'active' => 'activo',
        ];
    }
}
