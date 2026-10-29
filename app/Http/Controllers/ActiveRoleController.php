<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ActiveRoleController extends Controller
{
    // Pantalla «¿Con qué rol desea trabajar?»
    public function edit(Request $request)
    {
        $user = $request->user();

        if (! $user->hasMultipleRoles()) {
            return redirect()->route('dashboard');
        }

        return view('active-role.select', [
            'roles' => $user->roles()->withCount('permissions')->orderBy('name')->get(),
            'current' => session('active_role'),
            'directCount' => $user->getDirectPermissions()->count(),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'role' => ['required', Rule::in($user->getRoleNames()->all())],
        ], [
            'role.required' => 'Debe seleccionar un rol.',
            'role.in' => 'El rol seleccionado no está asignado a su usuario.',
        ]);

        $request->session()->put('active_role', $data['role']);

        return redirect()->intended(route('dashboard'))
            ->with('success', "Está trabajando con el rol «{$data['role']}».");
    }
}
