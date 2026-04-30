<?php

/**
 * Matriz por rol → claves de módulo (middleware `module:*` y pivot `role_modulo`).
 *
 * El menú principal lee textos y rutas desde las tablas locales `modulos` y `role_modulo`
 * (sembradas desde esta lista). Tras editar roles aquí: ejecute `php artisan modulos:sync-matriz`.
 *
 * Claves: dashboard, gestion_usuarios, inventario, cambios_destinos, ingresos, despacho, desposte,
 *          historial, entrega, decomisos
 */
return [
    'roles' => [
        'super_admin' => [
            'dashboard',
            'gestion_usuarios',
            'inventario',
            'cambios_destinos',
            'ingresos',
            'despacho',
            'desposte',
            'historial',
            'entrega',
            'decomisos',
        ],
        'admin' => [
            'dashboard',
            'inventario',
            'cambios_destinos',
            'ingresos',
            'despacho',
            'desposte',
            'historial',
            'entrega',
            'decomisos',
        ],
        'gerencia' => [
            'dashboard',
            'inventario',
            'cambios_destinos',
            'ingresos',
            'historial',
            'decomisos',
        ],
        'operador' => [
            'inventario',
            'cambios_destinos',
            'despacho',
            'desposte',
            'historial',
        ],
        'auxiliar' => [
            'dashboard',
            'inventario',
            'cambios_destinos',
            'historial',
            'entrega',
            'decomisos',
        ],
        /** Usuarios creados antes de la matriz: mismo acceso que auxiliar hasta reasignación. */
        'user' => [
            'dashboard',
            'inventario',
            'cambios_destinos',
            'historial',
            'entrega',
            'decomisos',
        ],
    ],

    /** Roles que se pueden asignar al crear un usuario (desde gestión). */
    'asignables' => [
        'super_admin' => 'Super administrador (control total)',
        'admin' => 'Administrador',
        'gerencia' => 'Gerencia',
        'operador' => 'Operador',
        'auxiliar' => 'Auxiliar',
    ],
];
