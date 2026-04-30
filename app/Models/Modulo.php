<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Modulo extends Model
{
    protected $fillable = [
        'clave',
        'nombre',
        'descripcion',
        'route_name',
        'orden',
        'activo',
        'requires_manage_users',
    ];

    protected function casts(): array
    {
        return [
            'orden' => 'integer',
            'activo' => 'boolean',
            'requires_manage_users' => 'boolean',
        ];
    }

    /** @return HasMany<RoleModulo, $this> */
    public function roleModulos(): HasMany
    {
        return $this->hasMany(RoleModulo::class, 'modulo_id');
    }
}
