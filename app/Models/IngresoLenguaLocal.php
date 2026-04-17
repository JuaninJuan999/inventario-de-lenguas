<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IngresoLenguaLocal extends Model
{
    protected $table = 'ingreso_lenguas_locales';

    protected $fillable = [
        'insensibilizacion_id',
        'id_producto',
        'fecha_registro',
        'hora_registro',
        'propietario',
        'destino',
        'peso',
        'fecha_turno_referencia',
        'imported_at',
        'user_id',
        'despacho_id',
        'despachado_at',
    ];

    protected function casts(): array
    {
        return [
            'fecha_registro' => 'date',
            'fecha_turno_referencia' => 'date',
            'peso' => 'float',
            'imported_at' => 'datetime',
            'despachado_at' => 'datetime',
        ];
    }

    /** Registros aún disponibles en inventario (no finalizados en despacho). */
    public function scopeSinDespachar(Builder $query): Builder
    {
        return $query->whereNull('despachado_at');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function despacho(): BelongsTo
    {
        return $this->belongsTo(Despacho::class, 'despacho_id');
    }
}
