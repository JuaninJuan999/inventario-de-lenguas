<?php

namespace App\Http\Controllers\Usuarios;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GestionUsuariosController extends Controller
{
    public function index(): View
    {
        $usuarios = User::query()
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('gestion-usuarios.index', [
            'usuarios' => $usuarios,
            'rolesAsignables' => config('modulos_por_rol.asignables', []),
        ]);
    }

    public function create(): View
    {
        return view('gestion-usuarios.create', [
            'rolesAsignables' => config('modulos_por_rol.asignables', []),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $rolesValidos = array_keys(config('modulos_por_rol.asignables', []));

        $name = trim($request->string('name')->toString());
        $usernameNorm = Str::lower(trim($request->string('username')->toString()));
        if ($usernameNorm === '') {
            $usernameNorm = self::usernameDesdeNombre($name);
        }
        $usernameNorm = self::asegurarUsernameUnico($usernameNorm);
        $request->merge([
            'name' => $name,
            'username' => $usernameNorm,
        ]);

        $validated = $request->validate(
            [
                'name' => ['required', 'string', 'max:255'],
                'username' => [
                    'required',
                    'string',
                    'max:191',
                    'regex:/^[a-z0-9._-]+$/',
                    Rule::unique('users', 'username'),
                ],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
                'role' => ['required', 'string', Rule::in($rolesValidos)],
            ],
            [
                'name.required' => 'Indique el nombre completo.',
                'username.required' => 'Indique el nombre de usuario.',
                'username.regex' => 'El usuario solo puede tener letras minúsculas, números, punto, guion y guion bajo.',
                'username.unique' => 'Ese nombre de usuario ya está en uso.',
                'email.required' => 'Indique el correo electrónico.',
                'email.email' => 'El correo electrónico no es válido.',
                'password.required' => 'Indique la contraseña.',
                'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
                'password.confirmed' => 'La confirmación de la contraseña no coincide.',
                'role.required' => 'Seleccione un rol.',
                'role.in' => 'El rol seleccionado no es válido.',
            ],
        );

        User::query()->create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $validated['role'],
            'is_active' => true,
            'email_verified_at' => null,
        ]);

        return redirect()
            ->route('gestion.usuarios.index')
            ->with(
                'status',
                'Usuario creado correctamente. Login: '.$validated['username'].'.'
            );
    }

    public function edit(User $user): View
    {
        return view('gestion-usuarios.edit', [
            'usuario' => $user,
            'rolesAsignables' => config('modulos_por_rol.asignables', []),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $rolesValidos = array_keys(config('modulos_por_rol.asignables', []));
        if (is_string($user->role) && $user->role !== '' && ! in_array($user->role, $rolesValidos, true)) {
            $rolesValidos[] = $user->role;
        }

        $name = trim($request->string('name')->toString());
        $usernameNorm = Str::lower(trim($request->string('username')->toString()));
        if ($usernameNorm === '') {
            $usernameNorm = self::usernameDesdeNombre($name);
        }
        if ($usernameNorm !== Str::lower((string) $user->username)) {
            $usernameNorm = self::asegurarUsernameUnicoExcepto($usernameNorm, $user->id);
        }
        $request->merge([
            'name' => $name,
            'username' => $usernameNorm,
        ]);

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
                'role' => ['required', 'string', Rule::in($rolesValidos)],
            ],
            [
                'name.required' => 'Indique el nombre completo.',
                'username.required' => 'Indique el nombre de usuario.',
                'username.regex' => 'El usuario solo puede tener letras minúsculas, números, punto, guion y guion bajo.',
                'username.unique' => 'Ese nombre de usuario ya está en uso.',
                'email.required' => 'Indique el correo electrónico.',
                'email.email' => 'El correo electrónico no es válido.',
                'role.required' => 'Seleccione un rol.',
                'role.in' => 'El rol seleccionado no es válido.',
            ],
        );

        if (
            $user->role === 'super_admin'
            && $validated['role'] !== 'super_admin'
            && $user->isSoleActiveSuperAdmin()
        ) {
            return redirect()
                ->route('gestion.usuarios.edit', $user)
                ->with('gestion_error', 'Debe existir al menos un super administrador activo.');
        }

        $user->fill([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ]);
        $user->save();

        return redirect()
            ->route('gestion.usuarios.index')
            ->with('status', 'Usuario actualizado: '.$user->username.'.');
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $username = Str::lower(trim((string) $user->username));
        if ($username === '') {
            return redirect()
                ->route('gestion.usuarios.edit', $user)
                ->with('gestion_error', 'El usuario no tiene nombre de usuario; no se puede restablecer la clave.');
        }

        $user->password = $username;
        $user->must_change_password = true;
        $user->save();

        return redirect()
            ->route('gestion.usuarios.edit', $user)
            ->with(
                'status',
                'Contraseña restablecida. Temporal: el mismo nombre de usuario ('.$username.'). Al ingresar deberá definir una nueva.'
            );
    }

    /** Convierte "Juan Carreño" → "juan.carreno" (login). */
    public static function usernameDesdeNombre(string $name): string
    {
        $ascii = Str::ascii(trim($name));
        $slug = Str::lower($ascii);
        $slug = preg_replace('/[^a-z0-9]+/', '.', $slug) ?? '';
        $slug = trim($slug, '.');
        $slug = preg_replace('/\.{2,}/', '.', $slug) ?? $slug;

        if ($slug === '') {
            $slug = 'usuario';
        }

        return substr($slug, 0, 180);
    }

    public static function asegurarUsernameUnico(string $base, ?int $exceptUserId = null): string
    {
        return self::asegurarUsernameUnicoExcepto($base, $exceptUserId);
    }

    public static function asegurarUsernameUnicoExcepto(string $base, ?int $exceptUserId = null): string
    {
        $base = Str::lower(trim($base));
        $base = preg_replace('/[^a-z0-9._-]/', '', $base) ?? '';
        $base = trim($base, '.-_');
        if ($base === '') {
            $base = 'usuario';
        }
        $base = substr($base, 0, 180);

        $candidato = $base;
        $n = 2;
        while (
            User::query()
                ->where('username', $candidato)
                ->when($exceptUserId !== null, static fn ($q) => $q->where('id', '!=', $exceptUserId))
                ->exists()
        ) {
            $sufijo = (string) $n;
            $candidato = substr($base, 0, max(1, 191 - 1 - strlen($sufijo))).'.'.$sufijo;
            $n++;
            if ($n > 9999) {
                $candidato = $base.'.'.Str::lower(Str::random(4));
                break;
            }
        }

        return $candidato;
    }

    public function inactivar(Request $request, User $user): RedirectResponse
    {
        $actor = $request->user();
        if ($user->is($actor)) {
            return $this->gestionError('No puede inactivar su propia cuenta.');
        }

        if ($user->isSoleActiveSuperAdmin()) {
            return $this->gestionError('Debe existir al menos un super administrador activo.');
        }

        if (! $user->is_active) {
            return redirect()->route('gestion.usuarios.index')->with('status', 'El usuario ya estaba inactivo.');
        }

        $user->is_active = false;
        $user->save();

        return redirect()
            ->route('gestion.usuarios.index')
            ->with('status', 'Usuario inactivado. Ya no podrá iniciar sesión.');
    }

    public function activar(Request $request, User $user): RedirectResponse
    {
        if ($user->is_active) {
            return redirect()->route('gestion.usuarios.index')->with('status', 'El usuario ya estaba activo.');
        }

        $user->is_active = true;
        $user->save();

        return redirect()
            ->route('gestion.usuarios.index')
            ->with('status', 'Usuario reactivado. Ya puede iniciar sesión.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return $this->gestionError('No puede eliminar su propia cuenta.');
        }

        if ($user->isSoleSuperAdmin()) {
            return $this->gestionError('No se puede eliminar el único super administrador del sistema.');
        }

        $user->delete();

        return redirect()
            ->route('gestion.usuarios.index')
            ->with('status', 'Usuario eliminado de forma permanente.');
    }

    private function gestionError(string $message): RedirectResponse
    {
        return redirect()->route('gestion.usuarios.index')->with('gestion_error', $message);
    }
}
