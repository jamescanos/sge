<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StorePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $action = Str::slug((string) $this->input('action'));
        $module = Str::slug((string) $this->input('module'));

        $this->merge([
            'action' => $action,
            'module' => $module,
            'name' => "{$action}-{$module}",
        ]);
    }

    public function rules(): array
    {
        return [
            // La acción es una sola palabra (ver, crear, aprobar...) para poder separar «accion-modulo»
            'action' => ['required', 'string', 'max:30', 'regex:/^[a-z]+$/'],
            'module' => ['required', 'string', 'max:50', 'regex:/^[a-z0-9-]+$/'],
            'name' => [Rule::unique('permissions', 'name')],
        ];
    }

    public function attributes(): array
    {
        return [
            'action' => 'acción',
            'module' => 'módulo',
            'name' => 'permiso',
        ];
    }

    public function messages(): array
    {
        return [
            'action.regex' => 'La acción debe ser una sola palabra en minúsculas y sin tildes (ej.: aprobar).',
            'name.unique' => 'El permiso «:input» ya existe.',
        ];
    }
}
