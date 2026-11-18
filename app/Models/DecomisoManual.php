<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DecomisoManual extends Model
{
    protected $table = 'decomisos_manuales';

    protected $fillable = [
        'id_producto',
        'fecha_registro',
        'hora_registro',
        'nombre_dictamen',
        'nombre_enfermedad',
        'ingreso_lengua_local_id',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha_registro' => 'date',
        ];
    }

    public function ingreso(): BelongsTo
    {
        return $this->belongsTo(IngresoLenguaLocal::class, 'ingreso_lengua_local_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
