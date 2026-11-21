<?php

namespace App\Http\Controllers\Despacho;

use App\Http\Controllers\Controller;
use App\Models\Despacho;
use App\Models\Desposte;
use App\Models\IngresoLenguaLocal;
use App\Support\DespachoInventarioMatch;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HistoriaDespachoLenguasController extends Controller
{
    public function index(Request $request): View
    {
        $operadoresLista = Despacho::query()
            ->whereNotNull('empresa')
            ->where('empresa', '!=', '')
            ->distinct()
            ->orderBy('empresa')
            ->pluck('empresa')
            ->all();

        $tipoRaw = $request->query('tipo', '');
        $tipo = is_string($tipoRaw) && in_array($tipoRaw, ['despacho', 'desposte'], true)
            ? $tipoRaw
            : '';

        $operadorRaw = $request->query('operador', '');
        $operador = '';
        if (is_string($operadorRaw) && $operadorRaw !== '' && in_array($operadorRaw, $operadoresLista, true)) {
            $operador = $operadorRaw;
        }

        $desde = self::parseFechaFiltro($request->query('desde'));
        $hasta = self::parseFechaFiltro($request->query('hasta'));
        if ($desde !== null && $hasta !== null && $desde > $hasta) {
            [$desde, $hasta] = [$hasta, $desde];
        }

        $codigoRaw = $request->query('codigo', '');
        $codigo = is_string($codigoRaw) ? trim($codigoRaw) : '';
        $codigo = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $codigo) ?? '';
        $codigo = DespachoInventarioMatch::normalizarGuionesIdProducto(substr($codigo, 0, 80));

        $operadorFiltro = $operador !== '' ? $operador : null;

        $idsPorCodigo = $codigo !== ''
            ? $this->idsMovimientosPorCodigo($codigo)
            : null;

        $despachoQuery = Despacho::query()
            ->with('user')
            ->withCount('ingresos as lenguas_count');

        if ($desde !== null) {
            $despachoQuery->whereDate('realizado_at', '>=', $desde);
        }
        if ($hasta !== null) {
            $despachoQuery->whereDate('realizado_at', '<=', $hasta);
        }
        if ($operadorFiltro !== null) {
            $despachoQuery->where('empresa', $operadorFiltro);
        }
        if ($idsPorCodigo !== null) {
            $despachoIds = $idsPorCodigo['despacho_ids'];
            if ($despachoIds === []) {
                $despachoQuery->whereRaw('0 = 1');
            } else {
                $despachoQuery->whereIn('id', $despachoIds);
            }
        }

        $desposteQuery = Desposte::query()
            ->with('user')
            ->withCount('ingresos as lenguas_count');

        if ($desde !== null) {
            $desposteQuery->whereDate('realizado_at', '>=', $desde);
        }
        if ($hasta !== null) {
            $desposteQuery->whereDate('realizado_at', '<=', $hasta);
        }
        if ($idsPorCodigo !== null) {
            $desposteIds = $idsPorCodigo['desposte_ids'];
            if ($desposteIds === []) {
                $desposteQuery->whereRaw('0 = 1');
            } else {
                $desposteQuery->whereIn('id', $desposteIds);
            }
        }

        $incluirDespacho = $tipo === '' || $tipo === 'despacho';
        $incluirDesposte = ($tipo === '' || $tipo === 'desposte') && $operadorFiltro === null;

        $despachos = $incluirDespacho
            ? $despachoQuery->get()
            : collect();

        $despostes = $incluirDesposte
            ? $desposteQuery->get()
            : collect();

        $filas = Collection::make();

        foreach ($despachos as $d) {
            $ts = $d->realizado_at?->format('Y-m-d H:i:s') ?? '1970-01-01 00:00:00';
            $filas->push((object) [
                'movimiento_tipo' => 'despacho',
                'realizado_at' => $d->realizado_at,
                'empresa' => $d->empresa,
                'conductor' => $d->conductor,
                'placa' => $d->placa,
                'lenguas_count' => (int) ($d->lenguas_count ?? 0),
                'user' => $d->user,
                'detalle_url' => route('historia.despacho.lenguas.detalle', $d),
                'dialog_title' => 'Detalle del despacho',
                '_sort' => $ts.' A '.str_pad((string) $d->id, 10, '0', STR_PAD_LEFT),
            ]);
        }

        foreach ($despostes as $x) {
            $ts = $x->realizado_at?->format('Y-m-d H:i:s') ?? '1970-01-01 00:00:00';
            $filas->push((object) [
                'movimiento_tipo' => 'desposte',
                'realizado_at' => $x->realizado_at,
                'empresa' => null,
                'conductor' => null,
                'placa' => null,
                'lenguas_count' => (int) ($x->lenguas_count ?? 0),
                'user' => $x->user,
                'detalle_url' => route('historia.desposte.lenguas.detalle', $x),
                'dialog_title' => 'Detalle — Planta de Desposte',
                '_sort' => $ts.' B '.str_pad((string) $x->id, 10, '0', STR_PAD_LEFT),
            ]);
        }

        $ordenados = $filas->sortByDesc(static fn (object $r): string => $r->_sort)->values();

        $porPagina = 30;
        $pagina = LengthAwarePaginator::resolveCurrentPage();
        $items = $ordenados->forPage($pagina, $porPagina)->map(static function (object $r): object {
            unset($r->_sort);

            return $r;
        })->values();

        $movimientos = new LengthAwarePaginator(
            $items,
            $ordenados->count(),
            $porPagina,
            $pagina,
            [
                // Path root-relativo: no depender de APP_URL (mismo problema que filtros Inventario).
                'path' => '/historia-despacho-lenguas',
                'pageName' => 'page',
            ],
        );
        $movimientos->withQueryString();

        $filtrosActivos = $desde !== null
            || $hasta !== null
            || $tipo !== ''
            || $operadorFiltro !== null
            || $codigo !== '';

        $codigoSinHistorialHint = null;
        if ($codigo !== '' && $movimientos->total() === 0) {
            $codigoSinHistorialHint = $this->resolverCodigoSinHistorialHint($codigo);
        }

        return view('historia-despacho-lenguas', [
            'movimientos' => $movimientos,
            'operadoresLista' => $operadoresLista,
            'filtrosActivos' => $filtrosActivos,
            'codigoSinHistorialHint' => $codigoSinHistorialHint,
            'filtros' => [
                'desde' => $desde,
                'hasta' => $hasta,
                'tipo' => $tipo,
                'operador' => $operador,
                'codigo' => $codigo,
            ],
        ]);
    }

    /**
     * Cuando el filtro por código no lista movimientos: distingue inexistente /
     * aún en inventario / filtrado por fecha|tipo|operador.
     *
     * @return 'inexistente'|'disponible_en_inventario'|'con_otros_filtros'
     */
    private function resolverCodigoSinHistorialHint(string $codigo): string
    {
        $base = $this->queryIngresosPorCodigo($codigo);

        $existeEnInventario = (clone $base)->exists();
        if (! $existeEnInventario) {
            return 'inexistente';
        }

        $tieneMovimientos = (clone $base)
            ->where(function ($q): void {
                $q->whereNotNull('despacho_id')->orWhereNotNull('desposte_id');
            })
            ->exists();

        if (! $tieneMovimientos) {
            return 'disponible_en_inventario';
        }

        return 'con_otros_filtros';
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<IngresoLenguaLocal>
     */
    private function queryIngresosPorCodigo(string $codigo)
    {
        $op = DB::getDriverName() === 'pgsql' ? 'ilike' : 'like';
        $needle = '%'.addcslashes($codigo, '%_\\').'%';
        $keys = DespachoInventarioMatch::matchKeysForProductId($codigo);

        return IngresoLenguaLocal::query()
            ->where(function ($q) use ($op, $needle, $keys): void {
                $q->where('id_producto', $op, $needle);
                if ($keys !== []) {
                    $q->orWhereIn('id_producto', $keys);
                }
            });
    }

    /**
     * Localiza despachos / despostes que contienen el código de producto (parcial o exacto).
     *
     * @return array{despacho_ids: list<int>, desposte_ids: list<int>}
     */
    private function idsMovimientosPorCodigo(string $codigo): array
    {
        $rows = $this->queryIngresosPorCodigo($codigo)
            ->where(function ($q): void {
                $q->whereNotNull('despacho_id')->orWhereNotNull('desposte_id');
            })
            ->get(['despacho_id', 'desposte_id']);

        $despachoIds = [];
        $desposteIds = [];
        foreach ($rows as $row) {
            if ($row->despacho_id !== null) {
                $despachoIds[] = (int) $row->despacho_id;
            }
            if ($row->desposte_id !== null) {
                $desposteIds[] = (int) $row->desposte_id;
            }
        }

        return [
            'despacho_ids' => array_values(array_unique($despachoIds)),
            'desposte_ids' => array_values(array_unique($desposteIds)),
        ];
    }

    private static function parseFechaFiltro(mixed $valor): ?string
    {
        if (! is_string($valor) || trim($valor) === '') {
            return null;
        }

        try {
            return Carbon::parse($valor)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    public function detalle(Despacho $despacho): JsonResponse
    {
        $lineas = IngresoLenguaLocal::query()
            ->where('despacho_id', $despacho->id)
            ->orderBy('id')
            ->get(['id_producto', 'propietario', 'destino', 'es_adicional'])
            ->map(static fn (IngresoLenguaLocal $r): array => [
                'id_producto' => (string) $r->id_producto,
                'propietario' => $r->propietario !== null && $r->propietario !== '' ? (string) $r->propietario : '',
                'destino' => $r->destino !== null && $r->destino !== '' ? (string) $r->destino : '',
                'es_adicional' => (bool) $r->es_adicional,
            ])
            ->values()
            ->all();

        return response()->json([
            'ok' => true,
            'despacho_id' => $despacho->id,
            'realizado_at' => $despacho->realizado_at?->toIso8601String(),
            'lineas' => $lineas,
        ]);
    }

    public function detalleDesposte(Desposte $desposte): JsonResponse
    {
        $lineas = IngresoLenguaLocal::query()
            ->where('desposte_id', $desposte->id)
            ->orderBy('id')
            ->get(['id_producto', 'propietario', 'destino', 'es_adicional'])
            ->map(static fn (IngresoLenguaLocal $r): array => [
                'id_producto' => (string) $r->id_producto,
                'propietario' => $r->propietario !== null && $r->propietario !== '' ? (string) $r->propietario : '',
                'destino' => $r->destino !== null && $r->destino !== '' ? (string) $r->destino : '',
                'es_adicional' => (bool) $r->es_adicional,
            ])
            ->values()
            ->all();

        return response()->json([
            'ok' => true,
            'desposte_id' => $desposte->id,
            'realizado_at' => $desposte->realizado_at?->toIso8601String(),
            'lineas' => $lineas,
        ]);
    }
}
