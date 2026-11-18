<?php

namespace App\Http\Controllers;

use App\Models\Modulo;
use App\Models\ModuloAcceso;
use App\Services\ModulosMatrizSynchronizer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ObservatorioController extends Controller
{
    public function __invoke(Request $request): View
    {
        $periodo = $request->string('periodo')->toString();
        if (! in_array($periodo, ['hoy', '7d', 'mes'], true)) {
            $periodo = 'hoy';
        }

        $tz = (string) config('app.timezone', 'America/Bogota');
        $ahora = Carbon::now($tz);
        [$inicio, $fin, $periodoEtiqueta] = $this->rangoPeriodo($periodo, $ahora);

        $base = ModuloAcceso::query()
            ->where('created_at', '>=', $inicio)
            ->where('created_at', '<=', $fin);

        $totalEntradas = (clone $base)->count();
        $usuariosDistintos = (int) (clone $base)->selectRaw('COUNT(DISTINCT user_id) as c')->value('c');
        $modulosTocados = (int) (clone $base)->selectRaw('COUNT(DISTINCT modulo_clave) as c')->value('c');

        $recientesDesde = $ahora->copy()->subMinutes(30);
        $activosRecientes = (int) ModuloAcceso::query()
            ->where('created_at', '>=', $recientesDesde)
            ->selectRaw('COUNT(DISTINCT user_id) as c')
            ->value('c');

        $topModulo = (clone $base)
            ->select('modulo_clave', DB::raw('COUNT(*) as total'))
            ->groupBy('modulo_clave')
            ->orderByDesc('total')
            ->orderBy('modulo_clave')
            ->first();

        $nombresModulo = $this->mapaNombresModulo();

        $heatmap = (clone $base)
            ->select('modulo_clave', DB::raw('COUNT(*) as total'))
            ->groupBy('modulo_clave')
            ->orderByDesc('total')
            ->orderBy('modulo_clave')
            ->get()
            ->map(function ($row) use ($nombresModulo): array {
                $clave = (string) $row->modulo_clave;

                return [
                    'clave' => $clave,
                    'nombre' => $nombresModulo[$clave] ?? $clave,
                    'total' => (int) $row->total,
                ];
            })
            ->values()
            ->all();

        $maxHeat = max(1, collect($heatmap)->max('total') ?: 1);

        $filtroModulo = substr($request->string('modulo')->trim()->toString(), 0, 64);
        $feedQuery = ModuloAcceso::query()
            ->with('user:id,name,username,role')
            ->where('created_at', '>=', $inicio)
            ->where('created_at', '<=', $fin)
            ->when($filtroModulo !== '', fn ($q) => $q->where('modulo_clave', $filtroModulo))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(40);

        $feed = $feedQuery->get()->map(function (ModuloAcceso $a) use ($nombresModulo, $tz): array {
            $clave = (string) $a->modulo_clave;

            return [
                'id' => $a->id,
                'usuario' => $a->user?->name ?: ($a->user?->username ?: 'Usuario #'.$a->user_id),
                'username' => $a->user?->username,
                'role' => $a->role ?: ($a->user?->role ?: '—'),
                'role_label' => $this->etiquetaRol($a->role ?: ($a->user?->role ?: '')),
                'modulo_clave' => $clave,
                'modulo' => $nombresModulo[$clave] ?? $clave,
                'ruta' => $a->ruta,
                'hora' => $a->created_at?->timezone($tz)->format('d/m H:i:s') ?? '—',
                'fecha_key' => $a->created_at?->timezone($tz)->format('Y-m-d') ?? '',
            ];
        });

        $topUsuarios = (clone $base)
            ->select('user_id', DB::raw('COUNT(*) as total'))
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $userIds = $topUsuarios->pluck('user_id')->all();
        $users = \App\Models\User::query()
            ->whereIn('id', $userIds)
            ->get(['id', 'name', 'username', 'role'])
            ->keyBy('id');

        $ranking = $topUsuarios->map(function ($row) use ($users): array {
            $u = $users->get((int) $row->user_id);

            return [
                'nombre' => $u?->name ?: ($u?->username ?: 'Usuario #'.$row->user_id),
                'username' => $u?->username,
                'role_label' => $this->etiquetaRol($u?->role ?: ''),
                'total' => (int) $row->total,
            ];
        })->values()->all();

        $maxRank = max(1, collect($ranking)->max('total') ?: 1);

        return view('observatorio', [
            'periodo' => $periodo,
            'periodoEtiqueta' => $periodoEtiqueta,
            'filtroModulo' => $filtroModulo,
            'activosRecientes' => $activosRecientes,
            'modulosTocados' => $modulosTocados,
            'usuariosDistintos' => $usuariosDistintos,
            'totalEntradas' => $totalEntradas,
            'topModuloNombre' => $topModulo
                ? ($nombresModulo[(string) $topModulo->modulo_clave] ?? (string) $topModulo->modulo_clave)
                : '—',
            'topModuloTotal' => $topModulo ? (int) $topModulo->total : 0,
            'heatmap' => $heatmap,
            'maxHeat' => $maxHeat,
            'feed' => $feed,
            'ranking' => $ranking,
            'maxRank' => $maxRank,
            'nombresModulo' => $nombresModulo,
        ]);
    }

    /**
     * @return array{0: Carbon, 1: Carbon, 2: string}
     */
    private function rangoPeriodo(string $periodo, Carbon $ahora): array
    {
        return match ($periodo) {
            '7d' => [
                $ahora->copy()->subDays(6)->startOfDay(),
                $ahora->copy()->endOfDay(),
                'Últimos 7 días',
            ],
            'mes' => [
                $ahora->copy()->startOfMonth()->startOfDay(),
                $ahora->copy()->endOfMonth()->endOfDay(),
                $ahora->copy()->locale('es')->translatedFormat('F Y'),
            ],
            default => [
                $ahora->copy()->startOfDay(),
                $ahora->copy()->endOfDay(),
                'Hoy · '.$ahora->format('d/m/Y'),
            ],
        };
    }

    /**
     * @return array<string, string>
     */
    private function mapaNombresModulo(): array
    {
        $fromDb = Modulo::query()->pluck('nombre', 'clave')->all();
        if ($fromDb !== []) {
            return array_map(static fn ($n): string => (string) $n, $fromDb);
        }

        $map = [];
        foreach (ModulosMatrizSynchronizer::definitions() as $def) {
            $map[$def['clave']] = $def['nombre'];
        }

        return $map;
    }

    private function etiquetaRol(string $role): string
    {
        if ($role === '') {
            return '—';
        }

        /** @var array<string, string>|null $asignables */
        $asignables = config('modulos_por_rol.asignables');
        if (is_array($asignables) && isset($asignables[$role])) {
            return (string) $asignables[$role];
        }

        return $role;
    }
}
