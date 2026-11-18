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

        $validated = $request->validate([
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
        ]);

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

    public static function asegurarUsernameUnico(string $base): string
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
        while (User::query()->where('username', $candidato)->exists()) {
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
