<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Si la cuenta fue inactivada con sesión abierta, cierra sesión y envía al login.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user !== null && ! $user->is_active) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->with('account_inactive', 'Su cuenta fue inactivada. Si cree que es un error, contacte al administrador.');
        }

        return $next($request);
    }
}
