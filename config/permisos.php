<?php

/*
|--------------------------------------------------------------------------
| Catálogo de permisos del ERP
|--------------------------------------------------------------------------
| Los permisos siguen la convención «accion-modulo» (ver-productos, recibir-compras).
| Aquí solo se definen las etiquetas y el orden en que se muestran en pantalla.
| Si aparece un módulo o una acción que no está en esta lista, se muestra con su
| nombre técnico, así que agregar permisos nuevos nunca rompe la vista.
*/

return [

    // Módulos (categorías de permisos) en el orden en que aparecen en la matriz
    'modules' => [
        'productos' => 'Productos',
        'categorias' => 'Categorías',
        'clientes' => 'Clientes',
        'ventas' => 'Ventas',
        'proveedores' => 'Proveedores',
        'compras' => 'Compras',
        'inventario' => 'Inventario (kardex)',
        'reportes' => 'Reportes',
        'auditoria' => 'Auditoría',
        'usuarios' => 'Usuarios',
        'roles' => 'Roles',
        'permisos' => 'Permisos',
        'branding' => 'Identidad corporativa',
    ],

    // Acciones (columnas de la matriz)
    'actions' => [
        'ver' => 'Ver',
        'crear' => 'Crear',
        'editar' => 'Editar',
        'eliminar' => 'Eliminar',
        'recibir' => 'Recibir',
        'ajustar' => 'Ajustar',
        'exportar' => 'Exportar',
        'aprobar' => 'Aprobar',
    ],

    // Rol que siempre tiene TODOS los permisos (no se puede editar ni eliminar)
    'super_role' => 'admin',
];
