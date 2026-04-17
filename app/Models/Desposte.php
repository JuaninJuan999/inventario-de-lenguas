<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Desposte extends Model
{
    protected $fillable = [
        'user_id',
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

    /** Filas de inventario marcadas en este movimiento a desposte. */
    public function ingresos(): HasMany
    {
        return $this->hasMany(IngresoLenguaLocal::class, 'desposte_id');
    }
}
