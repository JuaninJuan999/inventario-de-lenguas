<?php

/**
 * Matriz por rol → claves de módulo (middleware `module:*` y pivot `role_modulo`).
 *
 * El menú principal lee textos y rutas desde las tablas locales `modulos` y `role_modulo`
 * (sembradas desde esta lista). Tras editar roles aquí: ejecute `php artisan modulos:sync-matriz`.
 *
 * En servidor, un `git pull` sin ese comando deja permisos viejos en BD (p. ej. gerencia aún ve
 * Destinos y etiquetas aunque aquí ya no figure). Ver README → Despliegue y docs/roles-y-modulos.md.
 *
 * Claves: dashboard, gestion_usuarios, observatorio, inventario, cambios_destinos, ingresos, despacho,
 *          desposte, historial, entrega, decomisos
 *
 * Nota: la clave `cambios_destinos` corresponde al módulo de menú «Destinos y etiquetas»
 * (destino logístico + marcas Adicional / Salida). No renombrar la clave sin migrar pivots.
 *
 * Decomisos en solo lectura (ver / Excel, sin alta manual): ver decomisos_solo_lectura.
 *
 * Documentación: docs/roles-y-modulos.md
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
        /**
         * Responsable de turno / planta: operación de piso sin Destinos y etiquetas,
         * sin gestión de usuarios ni observatorio. Decomisos en solo lectura
         * (ver decomisos_solo_lectura). Detalle: docs/roles-y-modulos.md
         */
        'supervisor' => [
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
        'supervisor',
        'auxiliar',
        'planillaje',
        'user',
    ],

    /** Roles que se pueden asignar al crear un usuario (desde gestión). */
    'asignables' => [
        'super_admin' => 'Super administrador (control total)',
        'admin' => 'Administrador',
        'supervisor' => 'Supervisor (turno / planta)',
        'gerencia' => 'Gerencia',
        'logistico' => 'Logístico',
        'auxiliar' => 'Auxiliar',
        'planillaje' => 'Planillaje',
        'calidad' => 'Calidad',
    ],
];
