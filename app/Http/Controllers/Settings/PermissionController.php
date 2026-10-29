<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StorePermissionRequest;
use App\Models\User;
use App\Services\AccessControlService;
use App\Support\PermissionCatalog;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:ver-permisos', only: ['index']),
            new Middleware('permission:editar-roles', only: ['updateMatrix']),
            new Middleware('permission:crear-permisos', only: ['store']),
            new Middleware('permission:eliminar-permisos', only: ['destroy']),
        ];
    }

    /**
     * Matriz de permisos: filas = permisos agrupados por módulo, columnas = roles.
     * Filtros: módulo y «ver como usuario» (permisos efectivos de un usuario y su origen).
     */
    public function index(Request $request)
    {
        $allGroups = PermissionCatalog::grouped(Permission::with('users')->orderBy('name')->get());
        $module = $request->input('module');
        $groups = $module && isset($allGroups[$module]) ? [$module => $allGroups[$module]] : $allGroups;

        $roles = Role::with('permissions:id,name')->orderBy('name')->get();
        $rolePermissions = $roles->mapWithKeys(fn ($r) => [$r->id => $r->permissions->pluck('name')->all()]);

        // «Ver como usuario»: origen de cada permiso efectivo
        $inspected = $request->filled('user') ? User::with('roles.permissions', 'permissions')->find($request->input('user')) : null;
        $origins = [];
        if ($inspected) {
            foreach ($inspected->permissions as $p) {
                $origins[$p->name][] = 'directo';
            }
            foreach ($inspected->roles as $r) {
                foreach ($r->permissions as $p) {
                    $origins[$p->name][] = "rol: {$r->name}";
                }
            }
        }

        return view('settings.permissions.index', [
            'groups' => $groups,
            'modules' => collect($allGroups)->map(fn ($g) => $g['label']),
            'module' => $module,
            'roles' => $roles,
            'rolePermissions' => $rolePermissions,
            'superRole' => PermissionCatalog::superRole(),
            'users' => User::orderBy('name')->get(['id', 'name', 'email']),
            'inspected' => $inspected,
            'origins' => $origins,
        ]);
    }

    /**
     * Guarda la matriz. Solo se tocan los permisos VISIBLES (si hay filtro por módulo, los demás se conservan).
     */
    public function updateMatrix(Request $request, AccessControlService $service)
    {
        $data = $request->validate([
            'visible' => ['required', 'array'],
            'visible.*' => ['string', 'exists:permissions,name'],
            'matrix' => ['array'],
            'matrix.*' => ['array'],
            'matrix.*.*' => ['string', 'exists:permissions,name'],
        ]);

        DB::transaction(function () use ($data, $service) {
            foreach (Role::with('permissions')->get() as $role) {
                $current = $role->permissions->pluck('name')->all();
                $checked = $data['matrix'][$role->id] ?? [];

                // (lo que tenía y no se veía) + (lo marcado ahora)
                $final = array_unique(array_merge(array_diff($current, $data['visible']), $checked));
                $service->syncRolePermissions($role, $final);
            }
        });

        return back()->with('success', 'Matriz de permisos actualizada.');
    }

    public function store(StorePermissionRequest $request, AccessControlService $service)
    {
        $permission = $service->createPermission($request->validated('action'), $request->validated('module'));

        return redirect()->route('settings.permissions.index', ['module' => $request->validated('module')])
            ->with('success', "Permiso «{$permission->name}» creado y asignado al rol ".PermissionCatalog::superRole().'.');
    }

    public function destroy(Permission $permission, AccessControlService $service)
    {
        try {
            $service->deletePermission($permission);
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        return back()->with('success', "Permiso «{$permission->name}» eliminado.");
    }
}
