<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SettingsController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            // Basta con UNO de los permisos (el | significa «o»)
            new Middleware('permission:ver-usuarios|ver-roles|ver-permisos|ver-branding'),
        ];
    }

    public function __invoke()
    {
        return view('settings.index', [
            'usersCount' => User::count(),
            'inactiveCount' => User::where('active', false)->count(),
            'multiRoleCount' => User::has('roles', '>', 1)->count(),
            'rolesCount' => Role::count(),
            'permissionsCount' => Permission::count(),
        ]);
    }
}
