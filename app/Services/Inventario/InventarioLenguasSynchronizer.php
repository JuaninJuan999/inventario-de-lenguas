<?php

namespace App\Services\Inventario;

use App\Models\IngresoLenguaLocal;
use App\Services\IngresosLenguas\IngresosLenguasConsultaSirt;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Alimenta la réplica local (ingreso_lenguas_locales) desde SIRT/Colbeef.
 *
 * Usado por:
 * - POST inventario.lenguas.importar_hoy (polling de la vista)
 * - php artisan inventario:sync-lenguas (CLI / scheduler)
 *
 * Sin rango explícito importa desde (hoy − INVENTARIO_LENGUAS_SYNC_DIAS_ATRAS)
 * hasta el día de operación (default: desde ayer).
 *
 * Guarda meta de la última sync en caché (hit_limit, watermark de ids) para
 * alertas en UI y pases incrementales / catch-up cuando se alcanza el tope
 * INGRESOS_LENGUAS_CONSULTA_LIMIT.
 *
 * Relación con Ingresos: misma consulta SIRT; Ingresos no escribe en local.
 * Documentación operativa: docs/sincronizacion-inventario.md
 */
class InventarioLenguasSynchronizer
{
    /** Clave de caché con last_sync_at, hit_limit, max/min insensibilizacion_id, etc. */
    public const CACHE_META_KEY = 'inventario_lenguas.sync.meta';

    public function __construct(
        protected IngresosLenguasConsultaSirt $consultaSirt
    ) {}

    /**
     * @param  array{id_producto?: string, propietario?: string}  $filters
     * @return array{
     *     success: bool,
     *     message: string,
     *     importados: int,
     *     hit_limit: bool,
     *     limite: int,
     *     max_insensibilizacion_id: int|null,
     *     min_insensibilizacion_id: int|null
     * }
     */
    public function sync(
        ?string $fechaDesde = null,
        ?string $fechaHasta = null,
        array $filters = [],
        ?int $userId = null,
        bool $incrementalCatchUp = true,
    ): array {
        if (! $this->consultaSirt->externalPostgresEnabled()) {
            return $this->fail('Configure la conexión SIRT/Colbeef en .env para importar.');
        }

        $connection = (string) config('ingresos_lenguas.connection');
        if ($connection === '') {
            return $this->fail('Conexión no configurada (ingresos_lenguas.connection).');
        }

        $hoy = $this->consultaSirt->fechaHoyOperacion();
        $fechaDesdeTrim = trim((string) ($fechaDesde ?? ''));
        $fechaHastaTrim = trim((string) ($fechaHasta ?? ''));

        // Sin rango explícito: ventana configurable (default desde ayer hasta hoy).
        if ($fechaDesdeTrim === '' && $fechaHastaTrim === '') {
            $diasAtras = max(0, (int) config('ingresos_lenguas.inventario_sync_dias_atras', 1));
            $fechaHastaTrim = $hoy;
            try {
                $tz = trim((string) config('ingresos_lenguas.fecha_operacion_timezone', '')) ?: null;
                $fechaDesdeTrim = Carbon::parse($hoy, $tz)->subDays($diasAtras)->toDateString();
            } catch (Throwable $e) {
                report($e);
                $fechaDesdeTrim = Carbon::parse($hoy)->subDays($diasAtras)->toDateString();
            }
        }

        if ($fechaDesdeTrim !== '' && $fechaHastaTrim === '') {
            $fechaHastaTrim = $fechaDesdeTrim;
        }
        if ($fechaHastaTrim !== '' && $fechaDesdeTrim === '') {
            $fechaDesdeTrim = $fechaHastaTrim;
        }

        $useRangoTurno = false;
        if ($fechaDesdeTrim !== '' && $fechaHastaTrim !== '') {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaDesdeTrim) === 1
                && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaHastaTrim) === 1
                && $fechaDesdeTrim <= $fechaHastaTrim) {
                $useRangoTurno = true;
            }
        }

        // Con ventana por defecto ya no usamos “solo hoy” (primeraCarga).
        $primeraCarga = false;
        $fechaSqlDesde = $useRangoTurno ? $fechaDesdeTrim : $hoy;
        $fechaSqlHasta = $useRangoTurno ? $fechaHastaTrim : $hoy;
        $fechaTurnoFallback = $useRangoTurno ? $fechaHastaTrim : $hoy;

        $filterPayload = [
            'id_producto' => substr(trim((string) ($filters['id_producto'] ?? '')), 0, 80),
            'propietario' => substr(trim((string) ($filters['propietario'] ?? '')), 0, 200),
        ];

        $limiteConsulta = max(100, min(10000, (int) config('ingresos_lenguas.consulta_insensibilizacion_limit', 2000)));

        $passPrincipal = $this->ejecutarPass(
            $connection,
            $hoy,
            $primeraCarga,
            $fechaSqlDesde,
            $fechaSqlHasta,
            $filterPayload,
            $userId,
            $fechaTurnoFallback,
        );

        if (! $passPrincipal['success']) {
            return $passPrincipal;
        }

        $importados = $passPrincipal['importados'];
        $maxId = $passPrincipal['max_insensibilizacion_id'];
        $minId = $passPrincipal['min_insensibilizacion_id'];
        $hitLimit = $importados >= $limiteConsulta;

        // Si se alcanzó el tope, un pase ASC bajo el mínimo ayuda a recuperar lotes más antiguos en corridas sucesivas.
        if ($incrementalCatchUp && $hitLimit && $minId !== null && $minId > 1) {
            $passCatchUp = $this->ejecutarPass(
                $connection,
                $hoy,
                $primeraCarga,
                $fechaSqlDesde,
                $fechaSqlHasta,
                $filterPayload,
                $userId,
                $fechaTurnoFallback,
                null,
                $minId,
                true,
            );

            if ($passCatchUp['success']) {
                $importados += $passCatchUp['importados'];
                if ($passCatchUp['max_insensibilizacion_id'] !== null) {
                    $maxId = max($maxId ?? 0, $passCatchUp['max_insensibilizacion_id']);
                }
                if ($passCatchUp['min_insensibilizacion_id'] !== null) {
                    $minId = min($minId ?? PHP_INT_MAX, $passCatchUp['min_insensibilizacion_id']);
                }
                if ($passCatchUp['importados'] >= $limiteConsulta) {
                    $hitLimit = true;
                }
            }
        }

        // Pase incremental: solo ids nuevos por encima del watermark del mismo rango.
        $metaPrevia = $this->meta();
        $rangoKey = $useRangoTurno ? "{$fechaDesdeTrim}|{$fechaHastaTrim}" : "hoy:{$hoy}";
        $watermark = (int) ($metaPrevia['max_insensibilizacion_id'] ?? 0);
        if ($incrementalCatchUp && ($metaPrevia['rango_key'] ?? null) === $rangoKey && $watermark > 0) {
            $passInc = $this->ejecutarPass(
                $connection,
                $hoy,
                $primeraCarga,
                $fechaSqlDesde,
                $fechaSqlHasta,
                $filterPayload,
                $userId,
                $fechaTurnoFallback,
                $watermark,
                null,
                true,
            );
            if ($passInc['success'] && $passInc['importados'] > 0) {
                $importados += $passInc['importados'];
                if ($passInc['max_insensibilizacion_id'] !== null) {
                    $maxId = max($maxId ?? 0, $passInc['max_insensibilizacion_id']);
                }
            }
        }

        $msg = $useRangoTurno
            ? "Réplica actualizada desde SIRT ({$fechaDesdeTrim} → {$fechaHastaTrim})."
            : "Réplica actualizada desde SIRT (día {$hoy}).";
        if ($hitLimit) {
            $msg .= " Tope de consulta SIRT ({$limiteConsulta}): pueden faltar filas; suba INGRESOS_LENGUAS_CONSULTA_LIMIT o acote filtros.";
        }

        $this->guardarMeta([
            'last_sync_at' => now()->toIso8601String(),
            'last_message' => $msg,
            'importados' => $importados,
            'hit_limit' => $hitLimit,
            'limite' => $limiteConsulta,
            'max_insensibilizacion_id' => $maxId,
            'min_insensibilizacion_id' => $minId,
            'rango_key' => $rangoKey,
            'success' => true,
        ]);

        return [
            'success' => true,
            'message' => $msg,
            'importados' => $importados,
            'hit_limit' => $hitLimit,
            'limite' => $limiteConsulta,
            'max_insensibilizacion_id' => $maxId,
            'min_insensibilizacion_id' => $minId,
        ];
    }

    /**
     * @return array{
     *     last_sync_at: ?string,
     *     last_message: ?string,
     *     importados: int,
     *     hit_limit: bool,
     *     limite: int|null,
     *     max_insensibilizacion_id: int|null,
     *     min_insensibilizacion_id: int|null,
     *     rango_key: ?string,
     *     success: bool|null
     * }
     */
    public function meta(): array
    {
        $raw = Cache::get(self::CACHE_META_KEY, []);
        if (! is_array($raw)) {
            $raw = [];
        }

        return [
            'last_sync_at' => isset($raw['last_sync_at']) ? (string) $raw['last_sync_at'] : null,
            'last_message' => isset($raw['last_message']) ? (string) $raw['last_message'] : null,
            'importados' => (int) ($raw['importados'] ?? 0),
            'hit_limit' => (bool) ($raw['hit_limit'] ?? false),
            'limite' => isset($raw['limite']) ? (int) $raw['limite'] : null,
            'max_insensibilizacion_id' => isset($raw['max_insensibilizacion_id']) ? (int) $raw['max_insensibilizacion_id'] : null,
            'min_insensibilizacion_id' => isset($raw['min_insensibilizacion_id']) ? (int) $raw['min_insensibilizacion_id'] : null,
            'rango_key' => isset($raw['rango_key']) ? (string) $raw['rango_key'] : null,
            'success' => array_key_exists('success', $raw) ? (bool) $raw['success'] : null,
        ];
    }

    /**
     * @param  array{id_producto: string, propietario: string}  $filters
     * @return array{
     *     success: bool,
     *     message: string,
     *     importados: int,
     *     hit_limit: bool,
     *     limite: int,
     *     max_insensibilizacion_id: int|null,
     *     min_insensibilizacion_id: int|null
     * }
     */
    private function ejecutarPass(
        string $connection,
        string $hoy,
        bool $primeraCarga,
        string $fechaSqlDesde,
        string $fechaSqlHasta,
        array $filters,
        ?int $userId,
        string $fechaTurnoFallback,
        ?int $despuesDeInsensibilizacionId = null,
        ?int $antesDeInsensibilizacionId = null,
        bool $ordenAscendente = false,
    ): array {
        $result = $this->consultaSirt->consultar(
            $connection,
            $hoy,
            $primeraCarga,
            $fechaSqlDesde,
            $fechaSqlHasta,
            $filters,
            true,
            $despuesDeInsensibilizacionId,
            $antesDeInsensibilizacionId,
            $ordenAscendente,
        );

        if ($result['exception'] instanceof Throwable) {
            report($result['exception']);
            $message = 'No se pudo leer desde SIRT. Revise conexión y permisos.';
            if (config('app.debug')) {
                $message .= ' Detalle: '.$result['exception']->getMessage();
            }

            $this->guardarMeta([
                'last_sync_at' => now()->toIso8601String(),
                'last_message' => $message,
                'success' => false,
                'hit_limit' => false,
                'importados' => 0,
            ]);

            return $this->fail($message);
        }

        $importados = 0;
        $maxId = null;
        $minId = null;

        try {
            DB::transaction(function () use ($result, $userId, &$importados, &$maxId, &$minId, $fechaTurnoFallback): void {
                foreach ($result['rows'] as $row) {
                    $r = (array) $row;
                    $insId = (int) ($r['insensibilizacion_id'] ?? 0);
                    if ($insId <= 0) {
                        continue;
                    }

                    $fechaReg = $r['fecha_registro'] ?? null;
                    if ($fechaReg instanceof \DateTimeInterface) {
                        $fechaReg = $fechaReg->format('Y-m-d');
                    }

                    $hora = $r['hora_registro'] ?? null;
                    if ($hora instanceof \DateTimeInterface) {
                        $hora = $hora->format('H:i:s');
                    }

                    $fechaTurnoRef = $r['fecha_turno_referencia'] ?? null;
                    if ($fechaTurnoRef instanceof \DateTimeInterface) {
                        $fechaTurnoRef = $fechaTurnoRef->format('Y-m-d');
                    } elseif (is_string($fechaTurnoRef)) {
                        $fechaTurnoRef = trim($fechaTurnoRef);
                        $fechaTurnoRef = $fechaTurnoRef !== '' ? $fechaTurnoRef : null;
                    } else {
                        $fechaTurnoRef = null;
                    }

                    IngresoLenguaLocal::query()->updateOrCreate(
                        ['insensibilizacion_id' => $insId],
                        [
                            'id_producto' => (string) ($r['id_producto'] ?? ''),
                            'fecha_registro' => $fechaReg,
                            'hora_registro' => $hora !== null && $hora !== '' ? (string) $hora : null,
                            'propietario' => isset($r['propietario']) ? (string) $r['propietario'] : null,
                            'destino' => isset($r['destino']) ? (string) $r['destino'] : null,
                            'peso' => isset($r['peso']) && $r['peso'] !== '' ? $r['peso'] : null,
                            'fecha_turno_referencia' => $fechaTurnoRef ?? $fechaTurnoFallback,
                            'imported_at' => now(),
                            'user_id' => $userId,
                        ],
                    );
                    $importados++;
                    $maxId = $maxId === null ? $insId : max($maxId, $insId);
                    $minId = $minId === null ? $insId : min($minId, $insId);
                }
            });
        } catch (Throwable $e) {
            report($e);

            return $this->fail('Error al guardar en la base local.');
        }

        $limite = max(100, min(10000, (int) config('ingresos_lenguas.consulta_insensibilizacion_limit', 2000)));

        return [
            'success' => true,
            'message' => '',
            'importados' => $importados,
            'hit_limit' => $importados >= $limite,
            'limite' => $limite,
            'max_insensibilizacion_id' => $maxId,
            'min_insensibilizacion_id' => $minId,
        ];
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    private function guardarMeta(array $meta): void
    {
        $prev = $this->meta();
        Cache::forever(self::CACHE_META_KEY, array_merge($prev, $meta));
    }

    /**
     * @return array{
     *     success: bool,
     *     message: string,
     *     importados: int,
     *     hit_limit: bool,
     *     limite: int,
     *     max_insensibilizacion_id: int|null,
     *     min_insensibilizacion_id: int|null
     * }
     */
    private function fail(string $message): array
    {
        return [
            'success' => false,
            'message' => $message,
            'importados' => 0,
            'hit_limit' => false,
            'limite' => max(100, min(10000, (int) config('ingresos_lenguas.consulta_insensibilizacion_limit', 2000))),
            'max_insensibilizacion_id' => null,
            'min_insensibilizacion_id' => null,
        ];
    }
}
