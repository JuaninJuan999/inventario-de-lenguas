<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ForcedPasswordChangeController extends Controller
{
    public function edit(Request $request): View
    {
        return view('auth.change-password', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        $username = Str::lower(trim((string) ($user->username ?? '')));

        $validated = $request->validate(
            [
                'password' => [
                    'required',
                    'string',
                    'confirmed',
                    Password::min(8)->letters()->mixedCase()->numbers()->symbols(),
                    function (string $attribute, mixed $value, \Closure $fail) use ($username): void {
                        if ($username !== '' && strcasecmp((string) $value, $username) === 0) {
                            $fail('La nueva contraseña no puede ser igual al nombre de usuario.');
                        }
                    },
                ],
            ],
            [
                'password.required' => 'Indique la nueva contraseña.',
                'password.confirmed' => 'La confirmación de la contraseña no coincide.',
                'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
                'password.letters' => 'La contraseña debe incluir al menos una letra.',
                'password.mixed' => 'La contraseña debe incluir mayúsculas y minúsculas.',
                'password.numbers' => 'La contraseña debe incluir al menos un número.',
                'password.symbols' => 'La contraseña debe incluir al menos un símbolo (!@#$…).',
            ],
        );

        $user->password = $validated['password'];
        $user->must_change_password = false;
        $user->save();

        return redirect()
            ->route('menu')
            ->with('status', 'Contraseña actualizada. Ya puede usar el sistema.');
    }
}
