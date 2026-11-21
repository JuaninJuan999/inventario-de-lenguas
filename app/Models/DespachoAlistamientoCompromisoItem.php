<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DespachoAlistamientoCompromisoItem extends Model
{
    protected $table = 'despacho_alistamiento_compromiso_items';

    protected $fillable = [
        'compromiso_id',
        'id_producto',
    ];

    public function compromiso(): BelongsTo
    {
        return $this->belongsTo(DespachoAlistamientoCompromiso::class, 'compromiso_id');
    }
}
