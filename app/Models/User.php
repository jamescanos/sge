<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, LogsActivity, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'active',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    // Valor por defecto en memoria (coincide con el default de la columna)
    protected $attributes = [
        'active' => true,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'active' => 'boolean',
        ];
    }

    // Auditoría (Clase 12): solo estos campos. NUNCA se audita la contraseña.
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'active'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('usuarios')
            ->setDescriptionForEvent(fn (string $event) => match ($event) {
                'created' => 'creado',
                'updated' => 'actualizado',
                'deleted' => 'eliminado',
                default => $event,
            });
    }

    /**
     * Rol con el que el usuario está trabajando en esta sesión.
     * - Con un solo rol: ese rol.
     * - Con varios roles: el que eligió al iniciar sesión (session('active_role')).
     * - Sin roles o sin elección (consola, pruebas): null.
     */
    public function activeRole(): ?Role
    {
        $roles = $this->roles;

        if ($roles->count() === 1) {
            return $roles->first();
        }

        return $roles->firstWhere('name', session('active_role'));
    }

    // ¿El usuario debe elegir con qué rol trabajar?
    public function hasMultipleRoles(): bool
    {
        return $this->roles->count() > 1;
    }

    /**
     * Verificación central de permisos del ERP (la usa Gate::before en AppServiceProvider):
     *   permiso efectivo = permisos DIRECTOS del usuario + permisos del ROL ACTIVO.
     * Si el usuario tiene varios roles y aún no eligió uno, se usa la unión de todos (solo ocurre
     * fuera del navegador, porque el middleware EnsureActiveRole obliga a elegir).
     */
    public function hasPermissionInActiveRole(string $permission): bool
    {
        try {
            if ($this->hasDirectPermission($permission)) {
                return true;
            }

            $role = $this->activeRole();

            return $role
                ? $role->hasPermissionTo($permission)
                : $this->hasPermissionTo($permission);
        } catch (PermissionDoesNotExist) {
            // La habilidad consultada no es un permiso registrado (ej.: una Policy)
            return false;
        }
    }
}
