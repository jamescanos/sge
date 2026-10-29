<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StoreUserRequest;
use App\Http\Requests\Settings\UpdateUserRequest;
use App\Models\User;
use App\Services\AccessControlService;
use App\Support\PermissionCatalog;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class UserController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:ver-usuarios', only: ['index']),
            new Middleware('permission:crear-usuarios', only: ['create', 'store']),
            new Middleware('permission:editar-usuarios', only: ['edit', 'update', 'toggle']),
        ];
    }

    public function index(Request $request)
    {
        $users = User::query()
            ->with('roles')
            ->withCount('permissions') // permisos DIRECTOS
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = '%'.$request->string('search').'%';
                $q->where(fn ($q) => $q->where('name', 'like', $search)->orWhere('email', 'like', $search));
            })
            ->when($request->filled('role'), fn ($q) => $q->role($request->input('role')))
            ->when($request->filled('status'), fn ($q) => $q->where('active', $request->input('status') === 'active'))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('settings.users.index', [
            'users' => $users,
            'roles' => Role::orderBy('name')->pluck('name'),
        ]);
    }

    public function create()
    {
        return view('settings.users.create', $this->formData(new User(['active' => true])));
    }

    public function store(StoreUserRequest $request, AccessControlService $service)
    {
        $user = $service->saveUser(new User, $request->validated(), $request->user());

        return redirect()->route('settings.users.index')
            ->with('success', "Usuario «{$user->name}» creado.");
    }

    public function edit(User $user)
    {
        return view('settings.users.edit', $this->formData($user));
    }

    public function update(UpdateUserRequest $request, User $user, AccessControlService $service)
    {
        $service->saveUser($user, $request->validated(), $request->user());

        return redirect()->route('settings.users.index')
            ->with('success', "Usuario «{$user->name}» actualizado.");
    }

    public function toggle(Request $request, User $user, AccessControlService $service)
    {
        try {
            $service->toggleActive($user, $request->user());
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        return back()->with('success', "Usuario «{$user->name}» ".($user->active ? 'activado.' : 'desactivado.'));
    }

    /**
     * Datos comunes del formulario: roles disponibles, matriz de permisos y de dónde viene cada permiso.
     */
    private function formData(User $user): array
    {
        $roles = Role::with('permissions')->orderBy('name')->get();
        $groups = PermissionCatalog::grouped();

        // Permisos heredados por rol: ['ver-productos' => ['admin', 'vendedor'], ...]
        $inherited = [];
        foreach ($user->exists ? $user->roles()->with('permissions')->get() : [] as $role) {
            foreach ($role->permissions as $permission) {
                $inherited[$permission->name][] = $role->name;
            }
        }

        return [
            'user' => $user,
            'roles' => $roles,
            'groups' => $groups,
            'actions' => PermissionCatalog::actions($groups),
            'userRoles' => old('roles', $user->exists ? $user->getRoleNames()->all() : []),
            'directPermissions' => old('permissions', $user->exists ? $user->getDirectPermissions()->pluck('name')->all() : []),
            'inherited' => $inherited,
        ];
    }
}
