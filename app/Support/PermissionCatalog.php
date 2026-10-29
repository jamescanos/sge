<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;

/**
 * Organiza los permisos de Spatie en una matriz Módulo × Acción
 * a partir de la convención de nombres «accion-modulo».
 */
class PermissionCatalog
{
    /**
     * Separa el nombre de un permiso: 'recibir-compras' => ['recibir', 'compras'].
     */
    public static function split(string $name): array
    {
        $parts = explode('-', $name, 2);

        return count($parts) === 2 ? $parts : [$name, 'otros'];
    }

    public static function moduleLabel(string $module): string
    {
        return config("permisos.modules.$module", ucfirst(str_replace('-', ' ', $module)));
    }

    public static function actionLabel(string $action): string
    {
        return config("permisos.actions.$action", ucfirst($action));
    }

    /**
     * Devuelve los permisos agrupados por módulo:
     * ['productos' => ['label' => 'Productos', 'permissions' => ['ver' => Permission, 'crear' => Permission, ...]], ...]
     */
    public static function grouped(?Collection $permissions = null): array
    {
        $permissions ??= Permission::orderBy('name')->get();
        $order = array_keys(config('permisos.modules', []));

        $groups = [];
        foreach ($permissions as $permission) {
            [$action, $module] = self::split($permission->name);
            $groups[$module]['label'] = self::moduleLabel($module);
            $groups[$module]['permissions'][$action] = $permission;
        }

        // Ordenar: primero los módulos del catálogo, luego los demás alfabéticamente
        uksort($groups, function ($a, $b) use ($order) {
            $ia = array_search($a, $order);
            $ib = array_search($b, $order);
            $ia = $ia === false ? PHP_INT_MAX : $ia;
            $ib = $ib === false ? PHP_INT_MAX : $ib;

            return $ia === $ib ? strcmp($a, $b) : $ia <=> $ib;
        });

        return $groups;
    }

    /**
     * Acciones presentes en los grupos (columnas de la matriz), en el orden del catálogo.
     */
    public static function actions(array $groups): array
    {
        $present = collect($groups)->flatMap(fn ($g) => array_keys($g['permissions']))->unique()->all();
        $order = array_keys(config('permisos.actions', []));

        $known = array_values(array_intersect($order, $present));
        $others = array_values(array_diff($present, $order));
        sort($others);

        return array_merge($known, $others);
    }

    public static function superRole(): string
    {
        return config('permisos.super_role', 'admin');
    }
}
