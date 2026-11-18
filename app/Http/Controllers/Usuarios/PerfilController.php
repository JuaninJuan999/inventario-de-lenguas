<?php

namespace App\Http\Controllers\Usuarios;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Usuarios\GestionUsuariosController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class PerfilController extends Controller
{
    public function edit(Request $request): View
    {
        return view('perfil.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        $name = trim($request->string('name')->toString());
        $usernameNorm = Str::lower(trim($request->string('username')->toString()));
        if ($usernameNorm === '') {
            $usernameNorm = GestionUsuariosController::usernameDesdeNombre($name);
        }
        if ($usernameNorm !== Str::lower((string) $user->username)) {
            $usernameNorm = GestionUsuariosController::asegurarUsernameUnicoExcepto($usernameNorm, $user->id);
        }
        $request->merge([
            'name' => $name,
            'username' => $usernameNorm,
        ]);

        $username = Str::lower(trim((string) ($user->username ?? '')));

        $validated = $request->validate(
            [
                'name' => ['required', 'string', 'max:255'],
                'username' => [
                    'required',
                    'string',
                    'max:191',
                    'regex:/^[a-z0-9._-]+$/',
                    Rule::unique('users', 'username')->ignore($user->id),
                ],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
                'password' => [
                    'nullable',
                    'string',
                    'confirmed',
                    Password::min(8)->letters()->mixedCase()->numbers()->symbols(),
                    function (string $attribute, mixed $value, \Closure $fail) use ($username): void {
                        if ($value === null || $value === '') {
                            return;
                        }
                        if ($username !== '' && strcasecmp((string) $value, $username) === 0) {
                            $fail('La nueva contraseña no puede ser igual al nombre de usuario.');
                        }
                    },
                ],
                'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'remove_avatar' => ['nullable', 'boolean'],
            ],
            [
                'name.required' => 'Indique su nombre completo.',
                'username.required' => 'Indique el nombre de usuario.',
                'username.regex' => 'El usuario solo puede tener letras minúsculas, números, punto, guion y guion bajo.',
                'username.unique' => 'Ese nombre de usuario ya está en uso.',
                'email.required' => 'Indique el correo electrónico.',
                'email.email' => 'El correo electrónico no es válido.',
                'password.confirmed' => 'La confirmación de la contraseña no coincide.',
                'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
                'password.letters' => 'La contraseña debe incluir al menos una letra.',
                'password.mixed' => 'La contraseña debe incluir mayúsculas y minúsculas.',
                'password.numbers' => 'La contraseña debe incluir al menos un número.',
                'password.symbols' => 'La contraseña debe incluir al menos un símbolo (!@#$…).',
                'avatar.image' => 'La foto debe ser una imagen.',
                'avatar.mimes' => 'Use JPG, PNG o WEBP.',
                'avatar.max' => 'La foto no debe superar 2 MB.',
            ],
        );

        $user->name = $validated['name'];
        $user->username = $validated['username'];
        $user->email = $validated['email'];

        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
            $user->must_change_password = false;
        }

        if ($request->boolean('remove_avatar') && $user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
            $user->avatar_path = null;
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar_path = $path;
        }

        $user->save();

        return redirect()
            ->route('perfil.edit')
            ->with('status', 'Perfil actualizado correctamente.');
    }
}
