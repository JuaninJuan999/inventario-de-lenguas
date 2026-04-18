<?php

namespace App\Http\Controllers\Despacho;

use App\Http\Controllers\Controller;
use App\Models\DespachoOperadorPlaca;
use Illuminate\View\View;

class DespachoLenguasController extends Controller
{
    public function __invoke(): View
    {
        $filas = DespachoOperadorPlaca::filasParaDespacho();
        if ($filas === []) {
            $filas = config('despacho_operadores_placas.filas', []);
        }

        return view('despacho-lenguas', [
            'operadorPlacaFilas' => $filas,
        ]);
    }
}
