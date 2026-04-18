<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DespachoOperadorPlaca extends Model
{
    protected $table = 'despacho_operador_placas';

    protected $fillable = [
        'placa',
        'operador_logistico',
    ];

    /**
     * @return list<array{placa: string, operador: string}>
     */
    public static function filasParaDespacho(): array
    {
        return static::query()
            ->orderBy('operador_logistico')
            ->orderBy('placa')
            ->get()
            ->map(static fn (self $r): array => [
                'placa' => (string) $r->placa,
                'operador' => (string) $r->operador_logistico,
            ])
            ->values()
            ->all();
    }
}
