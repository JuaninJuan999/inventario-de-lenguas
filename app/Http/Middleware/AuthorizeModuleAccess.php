<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthorizeModuleAccess
{
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $user = $request->user();
        if ($user === null || ! $user->canAccessModule($module)) {
            return redirect()
                ->route('menu')
                ->with('module_denied', 'No tiene permiso para acceder a ese módulo.');
        }

        return $next($request);
    }
}
