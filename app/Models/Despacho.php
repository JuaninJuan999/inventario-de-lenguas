<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Despacho extends Model
{
    protected $fillable = [
        'user_id',
        'id_vehiculo_asignado',
        'empresa',
        'conductor',
        'placa',
        'realizado_at',
    ];

    protected function casts(): array
    {
        return [
            'realizado_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Filas de inventario marcadas en este despacho. */
    public function ingresos(): HasMany
    {
        return $this->hasMany(IngresoLenguaLocal::class, 'despacho_id');
    }
}
