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
        $usernameNorm = Str::lower(trim($request->string('username')->toString()));
        $request->merge(['username' => $usernameNorm]);

        $rolesValidos = array_keys(config('modulos_por_rol.asignables', []));

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'max:191',
                'regex:/^[a-zA-Z0-9._-]+$/',
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
            ->with('status', 'Usuario creado correctamente. Podrá iniciar sesión con el nombre de usuario indicado.');
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
