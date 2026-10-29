<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveRole
{
    /**
     * 1. Si el usuario fue desactivado mientras tenía la sesión abierta, lo saca del sistema.
     * 2. Si el usuario tiene varios roles y no ha elegido uno válido, lo envía a elegirlo.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if (! $user->active) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'Su usuario está inactivo. Contacte al administrador.']);
        }

        // Rutas que se pueden usar sin rol elegido (la propia selección y el cierre de sesión)
        if ($request->routeIs('active-role.*', 'logout')) {
            return $next($request);
        }

        if ($user->hasMultipleRoles() && ! $user->roles->contains('name', session('active_role'))) {
            return redirect()->route('active-role.edit');
        }

        return $next($request);
    }
}
