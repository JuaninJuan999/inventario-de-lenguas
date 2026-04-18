<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:191'],
            'password' => ['required', 'string'],
        ]);

        $credentials['username'] = Str::lower(trim($credentials['username']));
        $usernameNorm = $credentials['username'];
        $cuenta = User::query()->where('username', $usernameNorm)->first();
        if ($cuenta !== null && ! $cuenta->is_active && Hash::check($credentials['password'], $cuenta->password)) {
            throw ValidationException::withMessages([
                'username' => 'Su cuenta está inactiva. Contacte al administrador.',
            ]);
        }

        if (! Auth::attempt(array_merge($credentials, ['is_active' => true]), $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'username' => __('auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('menu'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
