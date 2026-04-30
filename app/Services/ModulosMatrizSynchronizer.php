<?php

namespace App\Services;

use App\Models\Modulo;
use App\Models\RoleModulo;
use Illuminate\Support\Facades\DB;

/**
 * Catálogo de módulos del menú y matriz rol ↔ módulo en base local (PostgreSQL por defecto).
 * La fuente de roles permitidos sigue siendo {@see config('modulos_por_rol.roles')}.
 */
class ModulosMatrizSynchronizer
{
    /**
     * @return array{pivot_assignments: int}
     */
    public function sync(): array
    {
        foreach (self::definitions() as $def) {
            Modulo::query()->updateOrCreate(
                ['clave' => $def['clave']],
                [
                    'nombre' => $def['nombre'],
                    'descripcion' => $def['descripcion'],
                    'route_name' => $def['route_name'],
                    'orden' => $def['orden'],
                    'activo' => true,
                    'requires_manage_users' => $def['requires_manage_users'],
                ]
            );
        }

        $pivotAssignments = 0;

        DB::transaction(function () use (&$pivotAssignments): void {
            RoleModulo::query()->delete();

            foreach (config('modulos_por_rol.roles') as $role => $claves) {
                foreach ($claves as $clave) {
                    $modulo = Modulo::query()->where('clave', $clave)->first();
                    if ($modulo === null || $modulo->requires_manage_users) {
                        continue;
                    }
                    RoleModulo::query()->create([
                        'role' => $role,
                        'modulo_id' => $modulo->id,
                    ]);
                    $pivotAssignments++;
                }
            }
        });

        return ['pivot_assignments' => $pivotAssignments];
    }

    /**
     * Definiciones del menú (textos mostrados al usuario).
     *
     * @return list<array{clave: string, nombre: string, descripcion: string|null, route_name: string, orden: int, requires_manage_users: bool}>
     */
    public static function definitions(): array
    {
        return [
            [
                'clave' => 'dashboard',
                'nombre' => 'Dashboard',
                'descripcion' => 'Panel principal: resumen, indicadores y accesos rápidos del inventario.',
                'route_name' => 'dashboard',
                'orden' => 10,
                'requires_manage_users' => false,
            ],
            [
                'clave' => 'gestion_usuarios',
                'nombre' => 'Gestión de usuarios',
                'descripcion' => 'Lista de usuarios, alta en página aparte, roles y correo compartido entre cuentas si aplica.',
                'route_name' => 'gestion.usuarios.index',
                'orden' => 15,
                'requires_manage_users' => true,
            ],
            [
                'clave' => 'inventario',
                'nombre' => 'Inventario de Lenguas',
                'descripcion' => 'Stock disponible en la base de datos local (réplica); puede sincronizarse desde SIRT cuando está configurado.',
                'route_name' => 'inventario.lenguas',
                'orden' => 20,
                'requires_manage_users' => false,
            ],
            [
                'clave' => 'cambios_destinos',
                'nombre' => 'Cambios de destinos',
                'descripcion' => 'Buscar por ID producto y propietario y actualizar el destino logístico en inventario disponible.',
                'route_name' => 'cambios.destinos',
                'orden' => 25,
                'requires_manage_users' => false,
            ],
            [
                'clave' => 'ingresos',
                'nombre' => 'Ingresos de Lenguas',
                'descripcion' => 'Consulta en tiempo real contra la base corporativa externa (no guarda filas en la base local).',
                'route_name' => 'ingresos.lenguas',
                'orden' => 30,
                'requires_manage_users' => false,
            ],
            [
                'clave' => 'despacho',
                'nombre' => 'Despacho de Lenguas',
                'descripcion' => 'Gestiona el despacho de lenguas asegurando el control del stock en inventario.',
                'route_name' => 'despacho.lenguas',
                'orden' => 35,
                'requires_manage_users' => false,
            ],
            [
                'clave' => 'desposte',
                'nombre' => 'Lenguas Desposte',
                'descripcion' => 'Lenguas que pasan al proceso de la Planta de Desposte.',
                'route_name' => 'desposte.lenguas',
                'orden' => 40,
                'requires_manage_users' => false,
            ],
            [
                'clave' => 'historial',
                'nombre' => 'Historial de despacho de lenguas',
                'descripcion' => 'Consulta de despachos y de movimientos a Planta de Desposte.',
                'route_name' => 'historia.despacho.lenguas',
                'orden' => 45,
                'requires_manage_users' => false,
            ],
            [
                'clave' => 'entrega',
                'nombre' => 'Entrega de Conformidad',
                'descripcion' => 'Generación e impresión de la guía de despacho de lenguas.',
                'route_name' => 'entrega.conformidad',
                'orden' => 50,
                'requires_manage_users' => false,
            ],
            [
                'clave' => 'decomisos',
                'nombre' => 'Decomisos de Lenguas',
                'descripcion' => 'Registro de lenguas decomisadas o retiradas.',
                'route_name' => 'decomisos.lenguas',
                'orden' => 55,
                'requires_manage_users' => false,
            ],
        ];
    }
}
