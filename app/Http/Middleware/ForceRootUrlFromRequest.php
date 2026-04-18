<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * Alinea route() y url() al host y puerto de la petición actual (p. ej. Nginx en 192.168.x.x:8007).
 * Evita que APP_URL fijo (localhost) rompa fetch, formularios y cookies entre orígenes distintos.
 */
class ForceRootUrlFromRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        $root = $request->getSchemeAndHttpHost();
        if ($root !== '') {
            URL::forceRootUrl($root);
        }

        return $next($request);
    }
}
