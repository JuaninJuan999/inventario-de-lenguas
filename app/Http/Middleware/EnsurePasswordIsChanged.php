<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsChanged
{
    /**
     * Si el admin restableció la clave, obliga a definir una nueva antes de usar el sistema.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user === null || ! $user->must_change_password) {
            return $next($request);
        }

        if ($request->routeIs('password.change', 'password.change.update', 'logout')) {
            return $next($request);
        }

        return redirect()
            ->route('password.change')
            ->with('status', 'Debe definir una nueva contraseña segura antes de continuar.');
    }
}
