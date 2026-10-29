<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            // «Jefe de Bodega» => «jefe-de-bodega»
            'name' => Str::slug((string) $this->input('name')),
            'permissions' => $this->input('permissions', []),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50', Rule::unique('roles', 'name')->ignore($this->route('role'))],
            'permissions' => ['array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nombre del rol',
            'permissions.*' => 'permiso',
        ];
    }
}
