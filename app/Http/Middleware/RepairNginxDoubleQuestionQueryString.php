<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mitiga Nginx mal configurado:
 *   try_files $uri $uri/ /index.php?$is_args$args;
 * (con "?" literal de más) → QUERY_STRING llega como "?por_vencer=1" y $_GET queda vacío.
 *
 * Correcto en Nginx: /index.php$is_args$args  (sin "?" antes de $is_args).
 * Ver docs/inventario/nginx-query-string.md
 */
class RepairNginxDoubleQuestionQueryString
{
    public function handle(Request $request, Closure $next): Response
    {
        $qs = $request->server->get('QUERY_STRING');
        if (! is_string($qs) || $qs === '' || ! str_starts_with($qs, '?')) {
            return $next($request);
        }

        $qs = ltrim($qs, '?');
        $request->server->set('QUERY_STRING', $qs);

        $params = [];
        parse_str($qs, $params);
        if ($params === []) {
            return $next($request);
        }

        $request->query->replace(array_merge($request->query->all(), $params));

        foreach ($params as $key => $value) {
            $_GET[$key] = $value;
        }

        return $next($request);
    }
}
