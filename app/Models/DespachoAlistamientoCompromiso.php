<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DespachoAlistamientoCompromiso extends Model
{
    protected $table = 'despacho_alistamiento_compromisos';

    protected $fillable = [
        'dia_operacion',
        'id_vehiculo_asignado',
        'placa',
        'first_seen_at',
        'last_seen_at',
    ];

    protected function casts(): array
    {
        return [
            'dia_operacion' => 'date',
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'id_vehiculo_asignado' => 'integer',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(DespachoAlistamientoCompromisoItem::class, 'compromiso_id');
    }
}
