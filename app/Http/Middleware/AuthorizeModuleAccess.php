<?php

namespace App\Http\Middleware;

use App\Services\ModuloAccesoLogger;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthorizeModuleAccess
{
    public function __construct(
        private readonly ModuloAccesoLogger $accesoLogger,
    ) {}

    public function handle(Request $request, Closure $next, string $module): Response
    {
        $user = $request->user();
        if ($user === null || ! $user->canAccessModule($module)) {
            return redirect()
                ->route('menu')
                ->with('module_denied', 'No tiene permiso para acceder a ese módulo.');
        }

        $this->accesoLogger->logIfAllowed($request, $user, $module);

        return $next($request);
    }
}
