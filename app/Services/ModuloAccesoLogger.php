<?php

namespace App\Services;

use App\Models\ModuloAcceso;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Throwable;

/** Registra entradas a módulos con throttle para no inundar la BD. */
class ModuloAccesoLogger
{
    public const THROTTLE_SECONDS = 60;

    public function logIfAllowed(Request $request, User $user, string $moduloClave): void
    {
        $moduloClave = trim($moduloClave);
        if ($moduloClave === '') {
            return;
        }

        $cacheKey = 'modulo_acceso:'.$user->id.':'.$moduloClave;
        if (Cache::has($cacheKey)) {
            return;
        }

        Cache::put($cacheKey, 1, self::THROTTLE_SECONDS);

        $ua = (string) $request->userAgent();
        if (strlen($ua) > 255) {
            $ua = substr($ua, 0, 255);
        }

        try {
            ModuloAcceso::query()->create([
                'user_id' => $user->id,
                'role' => $user->role !== null && $user->role !== '' ? (string) $user->role : null,
                'modulo_clave' => $moduloClave,
                'ruta' => substr('/'.$request->path(), 0, 255),
                'method' => substr(strtoupper($request->method()), 0, 16),
                'ip' => $request->ip(),
                'user_agent' => $ua !== '' ? $ua : null,
                'created_at' => now(),
            ]);
        } catch (Throwable $e) {
            report($e);
        }
    }
}
