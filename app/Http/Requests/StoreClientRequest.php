<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClientRequest extends FormRequest
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
            'document_type' => ['required', Rule::in(['CC', 'NIT', 'CE', 'PP'])],
            'document_number' => [
                'required', 'string', 'max:30',
                // Sin withoutTrashed(): la columna es UNIQUE en la BD, también cuenta a los clientes en la papelera
                Rule::unique('clients', 'document_number')->ignore($this->route('client')),
            ],
            'name' => ['required', 'string', 'max:150'],
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
            'name' => 'nombre',
            'email' => 'correo',
            'phone' => 'teléfono',
            'address' => 'dirección',
        ];
    }
}
