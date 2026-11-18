<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuloAcceso extends Model
{
    public $timestamps = false;

    protected $table = 'modulo_accesos';

    protected $fillable = [
        'user_id',
        'role',
        'modulo_clave',
        'ruta',
        'method',
        'ip',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
