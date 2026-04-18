<?php

/**
 * Matriz de acceso: cada rol → claves de módulo permitidas (alineado a rutas / menú).
 *
 * Claves: dashboard, gestion_usuarios, inventario, ingresos, despacho, desposte,
 *          historial, entrega, decomisos
 */
return [
    'roles' => [
        'super_admin' => [
            'dashboard',
            'gestion_usuarios',
            'inventario',
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
            'ingresos',
            'historial',
            'decomisos',
        ],
        'operador' => [
            'inventario',
            'despacho',
            'desposte',
            'historial',
        ],
        'auxiliar' => [
            'dashboard',
            'inventario',
            'historial',
            'entrega',
            'decomisos',
        ],
        /** Usuarios creados antes de la matriz: mismo acceso que auxiliar hasta reasignación. */
        'user' => [
            'dashboard',
            'inventario',
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
