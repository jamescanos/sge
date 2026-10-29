<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StoreRoleRequest;
use App\Http\Requests\Settings\UpdateRoleRequest;
use App\Services\AccessControlService;
use App\Support\PermissionCatalog;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class RoleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:ver-roles', only: ['index']),
            new Middleware('permission:crear-roles', only: ['create', 'store']),
            new Middleware('permission:editar-roles', only: ['edit', 'update']),
            new Middleware('permission:eliminar-roles', only: ['destroy']),
        ];
    }

    public function index()
    {
        return view('settings.roles.index', [
            'roles' => Role::withCount(['users', 'permissions'])->orderBy('name')->get(),
            'superRole' => PermissionCatalog::superRole(),
        ]);
    }

    public function create()
    {
        return view('settings.roles.create', $this->formData(new Role));
    }

    public function store(StoreRoleRequest $request, AccessControlService $service)
    {
        $role = DB::transaction(function () use ($request, $service) {
            $role = Role::create(['name' => $request->validated('name'), 'guard_name' => 'web']);
            $service->syncRolePermissions($role, $request->validated('permissions'));

            return $role;
        });

        return redirect()->route('settings.roles.index')
            ->with('success', "Rol «{$role->name}» creado con {$role->permissions()->count()} permisos.");
    }

    public function edit(Role $role)
    {
        return view('settings.roles.edit', $this->formData($role));
    }

    public function update(UpdateRoleRequest $request, Role $role, AccessControlService $service)
    {
        DB::transaction(function () use ($request, $role, $service) {
            // El rol administrador no se renombra (el sistema lo busca por su nombre)
            if ($role->name !== PermissionCatalog::superRole()) {
                $role->update(['name' => $request->validated('name')]);
            }

            $service->syncRolePermissions($role, $request->validated('permissions'));
        });

        return redirect()->route('settings.roles.index')
            ->with('success', "Rol «{$role->name}» actualizado.");
    }

    public function destroy(Role $role)
    {
        if ($role->name === PermissionCatalog::superRole()) {
            return back()->with('error', 'El rol administrador no se puede eliminar.');
        }

        if ($role->users()->exists()) {
            return back()->with('error', "No se puede eliminar «{$role->name}»: tiene usuarios asignados.");
        }

        $role->delete();

        return redirect()->route('settings.roles.index')
            ->with('success', "Rol «{$role->name}» eliminado.");
    }

    private function formData(Role $role): array
    {
        $groups = PermissionCatalog::grouped();

        return [
            'role' => $role,
            'groups' => $groups,
            'actions' => PermissionCatalog::actions($groups),
            'selected' => old('permissions', $role->exists ? $role->permissions->pluck('name')->all() : []),
            'isSuperRole' => $role->name === PermissionCatalog::superRole(),
        ];
    }
}
