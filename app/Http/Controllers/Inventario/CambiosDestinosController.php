<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\IngresoLenguaLocal;
use App\Support\DespachoCodigoBarras;
use App\Support\DespachoInventarioMatch;
use App\Support\VencimientoLengua;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CambiosDestinosController extends Controller
{
    private const LISTADO_LIMIT = 500;

    public function index(Request $request): View
    {
        $idProducto = substr($request->string('id_producto')->trim()->toString(), 0, 80);
        $idProducto = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $idProducto) ?? '';
        $idProducto = str_replace(['–', '—', '−'], '-', $idProducto);
        $propietario = substr($request->string('propietario')->trim()->toString(), 0, 200);
        $soloAdicionales = $request->boolean('solo_adicionales');
        $soloSalidas = $request->boolean('solo_salidas');
        $porVencer = $request->boolean('por_vencer');

        $filtrosActivos = $idProducto !== ''
            || $propietario !== ''
            || $soloAdicionales
            || $soloSalidas
            || $porVencer;

        $rows = collect();
        $totalCoincidencias = 0;
        $listadoLimitado = false;

        if ($filtrosActivos) {
            $op = $this->likeOperator();

            $patProp = '%'.$propietario.'%';
            $hoyYmd = Carbon::now(VencimientoLengua::zona())->startOfDay()->format('Y-m-d');
            $diasExpr = $this->sqlDiasHastaVencimientoColumn();

            $query = IngresoLenguaLocal::query()
                ->sinDespachar()
                ->when($idProducto !== '', fn ($q) => $this->applyIdProductoFlexibleFilter($q, $op, $idProducto))
                ->when($propietario !== '', fn ($q) => $q->where('propietario', $op, $patProp))
                ->when($soloAdicionales, fn ($q) => $q->where('es_adicional', true))
                ->when($soloSalidas, fn ($q) => $q->where('es_salida', true))
                ->when($porVencer, function ($q) use ($diasExpr, $hoyYmd): void {
                    $q->whereNotNull('fecha_registro')
                        ->whereRaw("({$diasExpr}) BETWEEN 0 AND 1", [$hoyYmd]);
                });

            $totalCoincidencias = (clone $query)->count();

            $query
                ->orderByDesc('es_adicional')
                ->orderByDesc('es_salida')
                ->orderByDesc('imported_at')
                ->orderByDesc('id');

            // Próximas a vencer: listar todas (conteo real). Otros filtros: tope de seguridad.
            if (! $porVencer) {
                $listadoLimitado = $totalCoincidencias > self::LISTADO_LIMIT;
                $query->limit(self::LISTADO_LIMIT);
            }

            $rows = $query->get();
        }

        return view('cambios-destinos', [
            'rows' => $rows,
            'filters' => [
                'id_producto' => $idProducto,
                'propietario' => $propietario,
                'solo_adicionales' => $soloAdicionales,
                'solo_salidas' => $soloSalidas,
                'por_vencer' => $porVencer,
            ],
            'filtrosActivos' => $filtrosActivos,
            'listadoLimit' => self::LISTADO_LIMIT,
            'totalCoincidencias' => $totalCoincidencias,
            'listadoLimitado' => $listadoLimitado,
        ]);
    }

    public function update(Request $request, IngresoLenguaLocal $ingreso): RedirectResponse
    {
        if ($ingreso->despachado_at !== null) {
            return redirect()
                ->route('cambios.destinos', $this->redirectQuery($request))
                ->with('cambios_destinos_error', 'Esa lengua ya fue despachada; no puede modificarse el destino desde aquí.');
        }

        $validated = $request->validate([
            'destino' => ['nullable', 'string', 'max:60000'],
            'id_producto' => ['nullable', 'string', 'max:80'],
            'propietario' => ['nullable', 'string', 'max:200'],
            'solo_adicionales' => ['nullable', 'boolean'],
            'solo_salidas' => ['nullable', 'boolean'],
            'por_vencer' => ['nullable', 'boolean'],
        ]);

        $destino = $validated['destino'] ?? null;
        if (is_string($destino)) {
            $destino = trim($destino);
            $destino = $destino === '' ? null : $destino;
        }

        $ingreso->update([
            'destino' => $destino,
            'destino_local' => true,
        ]);

        return redirect()
            ->route('cambios.destinos', $this->redirectQueryFromValidated($validated))
            ->with('cambios_destinos_ok', 'Destino actualizado correctamente.');
    }

    public function bulkUpdate(Request $request): RedirectResponse
    {
        return $this->guardarCambios($request);
    }

    /**
     * Guarda en un solo paso: destinos editados + cambios de ADI. + cambios de SAL.
     */
    public function guardarCambios(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'destinos' => ['nullable', 'array', 'max:10000'],
            'destinos.*' => ['nullable', 'string', 'max:60000'],
            'adicionales' => ['nullable', 'array', 'max:10000'],
            'adicionales.*' => ['nullable', 'in:0,1'],
            'salidas' => ['nullable', 'array', 'max:10000'],
            'salidas.*' => ['nullable', 'in:0,1'],
            'id_producto' => ['nullable', 'string', 'max:80'],
            'propietario' => ['nullable', 'string', 'max:200'],
            'solo_adicionales' => ['nullable', 'boolean'],
            'solo_salidas' => ['nullable', 'boolean'],
            'por_vencer' => ['nullable', 'boolean'],
        ]);

        $destinos = is_array($validated['destinos'] ?? null) ? $validated['destinos'] : [];
        $adicionales = is_array($validated['adicionales'] ?? null) ? $validated['adicionales'] : [];
        $salidas = is_array($validated['salidas'] ?? null) ? $validated['salidas'] : [];

        if ($destinos === [] && $adicionales === [] && $salidas === []) {
            return redirect()
                ->route('cambios.destinos', $this->redirectQueryFromValidated($validated))
                ->with('cambios_destinos_error', 'No hay cambios para guardar.');
        }

        $destinosOk = 0;
        $adiMarcar = 0;
        $adiQuitar = 0;
        $salMarcar = 0;
        $salQuitar = 0;
        $omitidos = 0;

        DB::transaction(function () use (
            $destinos,
            $adicionales,
            $salidas,
            &$destinosOk,
            &$adiMarcar,
            &$adiQuitar,
            &$salMarcar,
            &$salQuitar,
            &$omitidos,
        ): void {
            foreach ($destinos as $id => $destinoRaw) {
                $id = (int) $id;
                if ($id <= 0) {
                    continue;
                }
                $destino = is_string($destinoRaw) ? trim($destinoRaw) : '';
                $destino = $destino === '' ? null : $destino;

                $ingreso = IngresoLenguaLocal::query()->sinDespachar()->whereKey($id)->first();
                if ($ingreso === null) {
                    $omitidos++;

                    continue;
                }

                $actual = $ingreso->destino !== null ? trim((string) $ingreso->destino) : null;
                $actual = $actual === '' ? null : $actual;
                if ($actual === $destino) {
                    continue;
                }

                $ingreso->update([
                    'destino' => $destino,
                    'destino_local' => true,
                ]);
                $destinosOk++;
            }

            foreach ($adicionales as $id => $valor) {
                $id = (int) $id;
                if ($id <= 0) {
                    continue;
                }
                $marcar = (string) $valor === '1';
                $ingreso = IngresoLenguaLocal::query()->sinDespachar()->whereKey($id)->first();
                if ($ingreso === null) {
                    $omitidos++;

                    continue;
                }
                if ((bool) $ingreso->es_adicional === $marcar) {
                    continue;
                }
                $ingreso->update(['es_adicional' => $marcar]);
                if ($marcar) {
                    $adiMarcar++;
                } else {
                    $adiQuitar++;
                }
            }

            foreach ($salidas as $id => $valor) {
                $id = (int) $id;
                if ($id <= 0) {
                    continue;
                }
                $marcar = (string) $valor === '1';
                $ingreso = IngresoLenguaLocal::query()->sinDespachar()->whereKey($id)->first();
                if ($ingreso === null) {
                    $omitidos++;

                    continue;
                }
                if ((bool) $ingreso->es_salida === $marcar) {
                    continue;
                }
                $ingreso->update(['es_salida' => $marcar]);
                if ($marcar) {
                    $salMarcar++;
                } else {
                    $salQuitar++;
                }
            }
        });

        $query = $this->redirectQueryFromValidated($validated);
        $total = $destinosOk + $adiMarcar + $adiQuitar + $salMarcar + $salQuitar;

        if ($total === 0) {
            $msg = 'No había cambios distintos a los ya guardados.';
            if ($omitidos > 0) {
                $msg .= " ({$omitidos} fila(s) no disponibles o ya despachadas.)";
            }

            return redirect()
                ->route('cambios.destinos', $query)
                ->with('cambios_destinos_error', $msg);
        }

        $partes = [];
        if ($destinosOk > 0) {
            $partes[] = $destinosOk === 1 ? '1 destino' : "{$destinosOk} destinos";
        }
        if ($adiMarcar > 0) {
            $partes[] = $adiMarcar === 1 ? '1 ADI. marcada' : "{$adiMarcar} ADI. marcadas";
        }
        if ($adiQuitar > 0) {
            $partes[] = $adiQuitar === 1 ? '1 ADI. quitada' : "{$adiQuitar} ADI. quitadas";
        }
        if ($salMarcar > 0) {
            $partes[] = $salMarcar === 1 ? '1 SAL. marcada' : "{$salMarcar} SAL. marcadas";
        }
        if ($salQuitar > 0) {
            $partes[] = $salQuitar === 1 ? '1 SAL. quitada' : "{$salQuitar} SAL. quitadas";
        }

        $msg = 'Cambios aplicados: '.implode(', ', $partes).'.';
        if ($omitidos > 0) {
            $msg .= " Se omitieron {$omitidos} fila(s) no disponibles.";
        }

        return redirect()
            ->route('cambios.destinos', $query)
            ->with('cambios_destinos_ok', $msg);
    }

    /**
     * Escanea / escribe un código y lo marca (o desmarca) como adicional en inventario disponible.
     */
    public function marcarAdicional(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'codigo' => ['required', 'string', 'max:120'],
            'accion' => ['nullable', 'in:marcar,desmarcar'],
            'id_producto' => ['nullable', 'string', 'max:80'],
            'propietario' => ['nullable', 'string', 'max:200'],
            'solo_adicionales' => ['nullable', 'boolean'],
            'solo_salidas' => ['nullable', 'boolean'],
            'por_vencer' => ['nullable', 'boolean'],
        ]);

        $accion = $validated['accion'] ?? 'marcar';
        $marcar = $accion !== 'desmarcar';
        $codigo = trim((string) $validated['codigo']);
        $codigo = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $codigo) ?? '';
        $codigo = DespachoInventarioMatch::normalizarGuionesIdProducto($codigo);

        $query = $this->redirectQueryFromValidated($validated);

        if ($codigo === '') {
            return redirect()
                ->route('cambios.destinos', $query)
                ->with('cambios_destinos_error', 'Indique un código de producto para marcar como adicional.');
        }

        $ingreso = DespachoInventarioMatch::findAvailableRow($codigo);

        if ($ingreso === null) {
            return redirect()
                ->route('cambios.destinos', $query + ['id_producto' => $codigo])
                ->with(
                    'cambios_destinos_error',
                    "No hay inventario disponible (sin despachar) para el código «{$codigo}»."
                );
        }

        if ((bool) $ingreso->es_adicional === $marcar) {
            $msg = $marcar
                ? "«{$ingreso->id_producto}» ya estaba marcado como adicional."
                : "«{$ingreso->id_producto}» no tenía marca de adicional.";

            return redirect()
                ->route('cambios.destinos', $query + ['id_producto' => (string) $ingreso->id_producto])
                ->with('cambios_destinos_ok', $msg);
        }

        $ingreso->update(['es_adicional' => $marcar]);

        $msg = $marcar
            ? "Código «{$ingreso->id_producto}» marcado como adicional."
            : "Se quitó la marca de adicional a «{$ingreso->id_producto}».";

        return redirect()
            ->route('cambios.destinos', $query + ['id_producto' => (string) $ingreso->id_producto])
            ->with('cambios_destinos_ok', $msg);
    }

    /**
     * Marca o desmarca varios IDs del listado como adicionales.
     */
    public function bulkAdicionales(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:500'],
            'ids.*' => ['integer', 'min:1'],
            'accion' => ['required', 'in:marcar,desmarcar'],
            'id_producto' => ['nullable', 'string', 'max:80'],
            'propietario' => ['nullable', 'string', 'max:200'],
            'solo_adicionales' => ['nullable', 'boolean'],
            'solo_salidas' => ['nullable', 'boolean'],
            'por_vencer' => ['nullable', 'boolean'],
        ]);

        $marcar = $validated['accion'] === 'marcar';
        $ids = array_values(array_unique(array_map('intval', $validated['ids'])));
        $query = $this->redirectQueryFromValidated($validated);

        $updated = IngresoLenguaLocal::query()
            ->sinDespachar()
            ->whereIn('id', $ids)
            ->where('es_adicional', '!=', $marcar)
            ->update(['es_adicional' => $marcar]);

        if ($updated === 0) {
            return redirect()
                ->route('cambios.destinos', $query)
                ->with(
                    'cambios_destinos_error',
                    $marcar
                        ? 'Ninguna fila seleccionada cambió (ya estaban como adicionales o no están disponibles).'
                        : 'Ninguna fila seleccionada tenía marca de adicional (o no están disponibles).'
                );
        }

        $msg = $marcar
            ? ($updated === 1
                ? 'Se marcó 1 código como adicional.'
                : "Se marcaron {$updated} códigos como adicionales.")
            : ($updated === 1
                ? 'Se quitó la marca de adicional a 1 código.'
                : "Se quitó la marca de adicional a {$updated} códigos.");

        return redirect()
            ->route('cambios.destinos', $query)
            ->with('cambios_destinos_ok', $msg);
    }

    /**
     * Marca o desmarca varios IDs del listado como salida.
     */
    public function bulkSalidas(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:500'],
            'ids.*' => ['integer', 'min:1'],
            'accion' => ['required', 'in:marcar,desmarcar'],
            'id_producto' => ['nullable', 'string', 'max:80'],
            'propietario' => ['nullable', 'string', 'max:200'],
            'solo_adicionales' => ['nullable', 'boolean'],
            'solo_salidas' => ['nullable', 'boolean'],
            'por_vencer' => ['nullable', 'boolean'],
        ]);

        $marcar = $validated['accion'] === 'marcar';
        $ids = array_values(array_unique(array_map('intval', $validated['ids'])));
        $query = $this->redirectQueryFromValidated($validated);

        $updated = IngresoLenguaLocal::query()
            ->sinDespachar()
            ->whereIn('id', $ids)
            ->where('es_salida', '!=', $marcar)
            ->update(['es_salida' => $marcar]);

        if ($updated === 0) {
            return redirect()
                ->route('cambios.destinos', $query)
                ->with(
                    'cambios_destinos_error',
                    $marcar
                        ? 'Ninguna fila seleccionada cambió (ya estaban como salida o no están disponibles).'
                        : 'Ninguna fila seleccionada tenía marca de salida (o no están disponibles).'
                );
        }

        $msg = $marcar
            ? ($updated === 1
                ? 'Se marcó 1 código como salida.'
                : "Se marcaron {$updated} códigos como salida.")
            : ($updated === 1
                ? 'Se quitó la marca de salida a 1 código.'
                : "Se quitó la marca de salida a {$updated} códigos.");

        return redirect()
            ->route('cambios.destinos', $query)
            ->with('cambios_destinos_ok', $msg);
    }

    /**
     * @param  Builder<IngresoLenguaLocal>  $query
     */
    private function applyIdProductoFlexibleFilter(Builder $query, string $op, string $idProducto): void
    {
        $patterns = [];

        $push = function (string $literal) use (&$patterns): void {
            $literal = trim($literal);
            if ($literal === '') {
                return;
            }
            $p = '%'.$literal.'%';
            if (! in_array($p, $patterns, true)) {
                $patterns[] = $p;
            }
        };

        $push($idProducto);

        foreach (['–', '—', '−'] as $dash) {
            if (str_contains($idProducto, '-')) {
                $push(str_replace('-', $dash, $idProducto));
            }
        }

        $compact = preg_replace('/[\s\-\/_.]+/', '', $idProducto) ?? '';
        if ($compact !== '' && $compact !== $idProducto) {
            $push($compact);
        }

        $digits = preg_replace('/\D/', '', $idProducto) ?? '';
        if ($digits !== '' && strlen($digits) >= 3) {
            $push($digits);
        }

        $norm = DespachoCodigoBarras::normalizarIdProducto($idProducto);
        if ($norm !== '' && $norm !== $idProducto) {
            $push($norm);
        }
        $normCompact = preg_replace('/[\s\-\/_.]+/', '', $norm) ?? '';
        if ($normCompact !== '' && $normCompact !== $norm) {
            $push($normCompact);
        }

        $query->where(function (Builder $w) use ($op, $patterns): void {
            foreach ($patterns as $i => $pat) {
                if ($i === 0) {
                    $w->where('id_producto', $op, $pat);
                } else {
                    $w->orWhere('id_producto', $op, $pat);
                }
            }
        });
    }

    private function likeOperator(): string
    {
        return DB::getDriverName() === 'pgsql' ? 'ilike' : 'like';
    }

    /**
     * Días hasta vencimiento (fecha registro + 4), mismo criterio que Inventario / Dashboard.
     */
    private function sqlDiasHastaVencimientoColumn(): string
    {
        return match (DB::getDriverName()) {
            'pgsql' => '(CASE WHEN fecha_registro IS NULL THEN NULL ELSE ((fecha_registro + INTERVAL \'4 days\')::date - CAST(? AS date))::integer END)',
            'sqlite' => '(CASE WHEN fecha_registro IS NULL THEN NULL ELSE CAST(ROUND(julianday(date(fecha_registro, \'+4 days\')) - julianday(?)) AS INTEGER) END)',
            default => '(CASE WHEN fecha_registro IS NULL THEN NULL ELSE DATEDIFF(DATE_ADD(fecha_registro, INTERVAL 4 DAY), ?) END)',
        };
    }

    /**
     * @return array{id_producto?: string, propietario?: string, solo_adicionales?: int, solo_salidas?: int, por_vencer?: int}
     */
    private function redirectQuery(Request $request): array
    {
        return $this->redirectQueryFromValidated([
            'id_producto' => $request->input('id_producto'),
            'propietario' => $request->input('propietario'),
            'solo_adicionales' => $request->boolean('solo_adicionales'),
            'solo_salidas' => $request->boolean('solo_salidas'),
            'por_vencer' => $request->boolean('por_vencer'),
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array{id_producto?: string, propietario?: string, solo_adicionales?: int, solo_salidas?: int, por_vencer?: int}
     */
    private function redirectQueryFromValidated(array $validated): array
    {
        $query = [
            'id_producto' => (string) ($validated['id_producto'] ?? ''),
            'propietario' => (string) ($validated['propietario'] ?? ''),
        ];

        if (! empty($validated['solo_adicionales'])) {
            $query['solo_adicionales'] = 1;
        }
        if (! empty($validated['solo_salidas'])) {
            $query['solo_salidas'] = 1;
        }
        if (! empty($validated['por_vencer'])) {
            $query['por_vencer'] = 1;
        }

        return $query;
    }
}
