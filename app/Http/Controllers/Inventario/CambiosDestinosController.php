<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\IngresoLenguaLocal;
use App\Support\DespachoCodigoBarras;
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

        $filtrosActivos = $idProducto !== '' || $propietario !== '';

        $rows = collect();

        if ($filtrosActivos) {
            $op = $this->likeOperator();

            $patProp = '%'.$propietario.'%';

            $rows = IngresoLenguaLocal::query()
                ->sinDespachar()
                ->when($idProducto !== '', fn ($q) => $this->applyIdProductoFlexibleFilter($q, $op, $idProducto))
                ->when($propietario !== '', fn ($q) => $q->where('propietario', $op, $patProp))
                ->orderByDesc('imported_at')
                ->orderByDesc('id')
                ->limit(self::LISTADO_LIMIT)
                ->get();
        }

        return view('cambios-destinos', [
            'rows' => $rows,
            'filters' => [
                'id_producto' => $idProducto,
                'propietario' => $propietario,
            ],
            'filtrosActivos' => $filtrosActivos,
            'listadoLimit' => self::LISTADO_LIMIT,
        ]);
    }

    public function update(Request $request, IngresoLenguaLocal $ingreso): RedirectResponse
    {
        if ($ingreso->despachado_at !== null) {
            return redirect()
                ->route('cambios.destinos', $request->only(['id_producto', 'propietario']))
                ->with('cambios_destinos_error', 'Esa lengua ya fue despachada; no puede modificarse el destino desde aquí.');
        }

        $validated = $request->validate([
            'destino' => ['nullable', 'string', 'max:60000'],
            'id_producto' => ['nullable', 'string', 'max:80'],
            'propietario' => ['nullable', 'string', 'max:200'],
        ]);

        $destino = $validated['destino'] ?? null;
        if (is_string($destino)) {
            $destino = trim($destino);
            $destino = $destino === '' ? null : $destino;
        }

        $ingreso->update(['destino' => $destino]);

        return redirect()
            ->route('cambios.destinos', [
                'id_producto' => $validated['id_producto'] ?? '',
                'propietario' => $validated['propietario'] ?? '',
            ])
            ->with('cambios_destinos_ok', 'Destino actualizado correctamente.');
    }

    /**
     * Misma idea que inventario ({@see InventarioLenguasController::ejecutarConsultaInventario}):
     * solo `ILIKE` / `LIKE` con comodines, sin raw SQL frágil.
     * Se añaden variantes OR (sin guiones, solo dígitos, guiones unicode) para formatos distintos en la columna.
     *
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
}
