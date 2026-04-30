<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoleModulo extends Model
{
    protected $table = 'role_modulo';

    public $timestamps = false;

    protected $fillable = [
        'role',
        'modulo_id',
    ];

    /** @return BelongsTo<Modulo, $this> */
    public function modulo(): BelongsTo
    {
        return $this->belongsTo(Modulo::class, 'modulo_id');
    }
}
