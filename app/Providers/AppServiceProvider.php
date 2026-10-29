<?php

namespace App\Providers;

use App\Models\CompanySetting;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Identidad corporativa: se consulta UNA vez por petición y se reutiliza (scoped)
        $this->app->scoped('branding', function () {
            try {
                return CompanySetting::query()->first() ?? new CompanySetting(CompanySetting::DEFAULTS);
            } catch (QueryException) {
                // La tabla aún no existe (antes de migrar): se usan los valores iniciales
                return new CompanySetting(CompanySetting::DEFAULTS);
            }
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Verificador de permisos del ERP. Reemplaza al de Spatie
        // (config/permission.php → 'register_permission_check_method' => false)
        // para respetar el ROL ACTIVO + los permisos DIRECTOS del usuario.
        // Lo usan: $user->can(), @can, el middleware 'permission:' y Gate::allows().
        Gate::before(function ($user, string $ability) {
            if (! $user instanceof User) {
                return null;
            }

            // true = autorizado. null = «no opino»: Laravel sigue con Policies/Gates y, si no hay, niega.
            return $user->hasPermissionInActiveRole($ability) ? true : null;
        });

        // $branding disponible en TODAS las vistas (menú, inicio de sesión, PDF...)
        View::composer('*', function ($view) {
            $view->with('branding', CompanySetting::current());
        });
    }
}
