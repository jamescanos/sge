<?php

namespace App\Http\Requests\Settings;

use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends StoreUserRequest
{
    // Al editar, la contraseña es opcional: si se deja vacía, se conserva la actual
    protected function passwordRules(): array
    {
        return ['nullable', 'confirmed', Password::defaults()];
    }
}
