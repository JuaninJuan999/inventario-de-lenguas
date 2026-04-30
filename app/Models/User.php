<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;

#[Fillable(['name', 'username', 'email', 'password', 'role', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Solo super administrador: gestión de usuarios y asignación de roles.
     */
    public function canManageUsers(): bool
    {
        return $this->role === 'super_admin' && $this->is_active;
    }

    /**
     * Tarjeta del menú (incluye módulos que solo aplican con {@see self::canManageUsers()}).
     */
    public function canSeeMenuModule(Modulo $modulo): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($modulo->requires_manage_users) {
            return $this->canManageUsers();
        }

        return $this->canAccessModule($modulo->clave);
    }

    /**
     * Permiso para rutas con middleware `module:*`. Primero intenta la matriz en base local
     * (`role_modulo` + `modulos`); si no hay datos aún, usa config/modulos_por_rol.php.
     *
     * @param  string  $module  Clave del módulo (p. ej. inventario, ingresos).
     */
    public function canAccessModule(string $module): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if (! Schema::hasTable('role_modulo') || ! Schema::hasTable('modulos')) {
            return $this->canAccessModuleFromConfig($module);
        }

        $role = $this->role ?? '';

        $hayMatrizParaRol = RoleModulo::query()->where('role', $role)->exists();

        if (! $hayMatrizParaRol) {
            return $this->canAccessModuleFromConfig($module);
        }

        return RoleModulo::query()
            ->where('role', $role)
            ->whereHas('modulo', static function ($q) use ($module): void {
                $q->where('clave', $module)->where('activo', true);
            })
            ->exists();
    }

    /** @param  string  $module  Clave en config/modulos_por_rol.php (roles.*) */
    private function canAccessModuleFromConfig(string $module): bool
    {
        $role = $this->role ?? '';
        /** @var list<string>|null $modules */
        $modules = config('modulos_por_rol.roles.'.$role);

        return is_array($modules) && in_array($module, $modules, true);
    }

    /** Único registro con rol super_admin en la base (no se puede eliminar). */
    public function isSoleSuperAdmin(): bool
    {
        if ($this->role !== 'super_admin') {
            return false;
        }

        return self::query()->where('role', 'super_admin')->count() === 1;
    }

    /** Único super_admin activo: no se puede inactivar (bloqueo de acceso total). */
    public function isSoleActiveSuperAdmin(): bool
    {
        if ($this->role !== 'super_admin' || ! $this->is_active) {
            return false;
        }

        return self::query()->where('role', 'super_admin')->where('is_active', true)->count() === 1;
    }
}
