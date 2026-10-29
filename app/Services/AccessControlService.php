<?php

namespace App\Services;

use App\Models\User;
use App\Support\PermissionCatalog;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Reglas de negocio de la seguridad del ERP (usuarios, roles y permisos).
 */
class AccessControlService
{
    /**
     * Crea o actualiza un usuario con sus roles y permisos directos, dentro de una transacción.
     *
     * @param  array  $data  ['name', 'email', 'password'?, 'active', 'roles' => [...], 'permissions' => [...]]
     */
    public function saveUser(User $user, array $data, User $actor): User
    {
        $roles = $data['roles'] ?? [];
        $active = (bool) ($data['active'] ?? false);

        // Regla 1: nadie se puede desactivar a sí mismo
        if ($user->exists && $user->is($actor) && ! $active) {
            throw ValidationException::withMessages(['active' => 'No puede desactivar su propio usuario.']);
        }

        // Regla 2: siempre debe quedar al menos un administrador activo
        $this->ensureAnAdminRemains($user, $roles, $active);

        return DB::transaction(function () use ($user, $data, $roles, $active) {
            $user->fill([
                'name' => $data['name'],
                'email' => $data['email'],
                'active' => $active,
            ]);

            if (! empty($data['password'])) {
                $user->password = $data['password']; // el cast 'hashed' la encripta
            }

            if (! $user->exists) {
                $user->email_verified_at = now(); // creado por un administrador: ya verificado
            }

            $user->save();

            $before = ['roles' => $user->getRoleNames()->sort()->values()->all(),
                'permisos_directos' => $user->getDirectPermissions()->pluck('name')->sort()->values()->all()];

            $user->syncRoles($roles);
            $user->syncPermissions($data['permissions'] ?? []); // permisos DIRECTOS

            // Clase 12: los cambios de seguridad quedan en la bitácora
            $after = ['roles' => collect($roles)->sort()->values()->all(),
                'permisos_directos' => collect($data['permissions'] ?? [])->sort()->values()->all()];
            if ($before !== $after) {
                $this->logSecurity($user, 'roles y permisos del usuario modificados', $before, $after);
            }

            return $user;
        });
    }

    public function toggleActive(User $user, User $actor): User
    {
        if ($user->is($actor)) {
            throw ValidationException::withMessages(['active' => 'No puede desactivar su propio usuario.']);
        }

        if ($user->active) {
            $this->ensureAnAdminRemains($user, $user->getRoleNames()->all(), false);
        }

        $user->update(['active' => ! $user->active]);

        return $user;
    }

    /**
     * Regla: al guardar este usuario debe seguir existiendo al menos un administrador activo.
     */
    public function ensureAnAdminRemains(User $user, array $newRoles, bool $active): void
    {
        $admin = PermissionCatalog::superRole();

        $wasAdmin = $user->exists && $user->hasRole($admin) && $user->active;
        $willBeAdmin = in_array($admin, $newRoles, true) && $active;

        if (! $wasAdmin || $willBeAdmin) {
            return;
        }

        $otherAdmins = User::role($admin)
            ->where('active', true)
            ->whereKeyNot($user->id)
            ->count();

        if ($otherAdmins === 0) {
            throw ValidationException::withMessages([
                'roles' => "Debe existir al menos un usuario activo con el rol «{$admin}».",
            ]);
        }
    }

    /**
     * Guarda los permisos de un rol. El rol administrador siempre conserva todos los permisos.
     */
    public function syncRolePermissions(Role $role, array $permissions): void
    {
        $before = $role->permissions()->pluck('name')->sort()->values()->all();

        if ($role->name === PermissionCatalog::superRole()) {
            $role->syncPermissions(Permission::all());
        } else {
            $role->syncPermissions($permissions);
        }

        $after = $role->permissions()->pluck('name')->sort()->values()->all();
        if ($before !== $after) {
            $this->logSecurity($role, "permisos del rol «{$role->name}» modificados", ['permisos' => $before], ['permisos' => $after]);
        }
    }

    /**
     * Clase 12: Spatie no audita roles ni permisos por sí solo; se registra manualmente
     * con el mismo formato (old / attributes) que usan los modelos auditados.
     */
    private function logSecurity($subject, string $description, array $old, array $new): void
    {
        activity('seguridad')
            ->performedOn($subject)
            ->causedBy(auth()->user())
            ->event('updated')
            ->withChanges(['old' => $old, 'attributes' => $new])
            ->log($description);
    }

    /**
     * Crea un permiso nuevo «accion-modulo» y se lo entrega al rol administrador.
     */
    public function createPermission(string $action, string $module): Permission
    {
        $permission = Permission::create(['name' => "{$action}-{$module}", 'guard_name' => 'web']);

        Role::findByName(PermissionCatalog::superRole())->givePermissionTo($permission);

        return $permission;
    }

    /**
     * Regla: solo se elimina un permiso que no esté en uso (fuera del rol administrador).
     */
    public function deletePermission(Permission $permission): void
    {
        $rolesUsing = $permission->roles()->where('name', '!=', PermissionCatalog::superRole())->pluck('name');
        $usersUsing = $permission->users()->count();

        if ($rolesUsing->isNotEmpty() || $usersUsing > 0) {
            throw ValidationException::withMessages([
                'permission' => "El permiso «{$permission->name}» está en uso (roles: "
                    .($rolesUsing->join(', ') ?: 'ninguno').", usuarios con permiso directo: {$usersUsing}).",
            ]);
        }

        $permission->delete();
    }
}
