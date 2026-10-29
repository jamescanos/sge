<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProviderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['active' => $this->boolean('active')]);
    }

    public function rules(): array
    {
        return [
            'document_type' => ['required', Rule::in(['NIT', 'CC', 'CE', 'PP'])],
            'document_number' => [
                'required', 'string', 'max:30',
                // La columna es UNIQUE en la BD: también cuenta a los proveedores en la papelera
                Rule::unique('providers', 'document_number')->ignore($this->route('provider')),
            ],
            'name' => ['required', 'string', 'max:150'],
            'contact_name' => ['nullable', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:200'],
            'active' => ['boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'document_type' => 'tipo de documento',
            'document_number' => 'número de documento',
            'name' => 'razón social',
            'contact_name' => 'persona de contacto',
            'email' => 'correo',
            'phone' => 'teléfono',
            'address' => 'dirección',
        ];
    }
}
