<?php

/**
 * Matriz por rol → claves de módulo (middleware `module:*` y pivot `role_modulo`).
 *
 * El menú principal lee textos y rutas desde las tablas locales `modulos` y `role_modulo`
 * (sembradas desde esta lista). Tras editar roles aquí: ejecute `php artisan modulos:sync-matriz`.
 *
 * Claves: dashboard, gestion_usuarios, observatorio, inventario, cambios_destinos, ingresos, despacho,
 *          desposte, historial, entrega, decomisos
 *
 * Decomisos en solo lectura (ver / Excel, sin alta manual): ver decomisos_solo_lectura.
 */
return [
    'roles' => [
        'super_admin' => [
            'dashboard',
            'gestion_usuarios',
            'observatorio',
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
        'logistico' => [
            'dashboard',
            'inventario',
            'despacho',
            'desposte',
        ],
        'auxiliar' => [
            'dashboard',
            'inventario',
            'historial',
            'entrega',
            'decomisos',
        ],
        'planillaje' => [
            'dashboard',
            'inventario',
            'decomisos',
        ],
        'calidad' => [
            'dashboard',
            'inventario',
            'decomisos',
        ],
        /**
         * Alias de compatibilidad (usuarios antiguos).
         * operador ≈ logistico; user ≈ auxiliar.
         */
        'operador' => [
            'dashboard',
            'inventario',
            'despacho',
            'desposte',
        ],
        'user' => [
            'dashboard',
            'inventario',
            'historial',
            'entrega',
            'decomisos',
        ],
    ],

    /**
     * Roles con acceso a decomisos pero sin registrar decomiso manual.
     * (Imagen: «sí, solo visualizar».)
     */
    'decomisos_solo_lectura' => [
        'gerencia',
        'auxiliar',
        'planillaje',
        'user',
    ],

    /** Roles que se pueden asignar al crear un usuario (desde gestión). */
    'asignables' => [
        'super_admin' => 'Super administrador (control total)',
        'admin' => 'Administrador',
        'gerencia' => 'Gerencia',
        'logistico' => 'Logístico',
        'auxiliar' => 'Auxiliar',
        'planillaje' => 'Planillaje',
        'calidad' => 'Calidad',
    ],
];
