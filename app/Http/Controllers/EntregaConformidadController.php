<?php

namespace App\Http\Controllers;

use App\Models\Despacho;
use App\Models\IngresoLenguaLocal;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\View\View;

class EntregaConformidadController extends Controller
{
    public function index(): View
    {
        $despachos = Despacho::query()
            ->with('user')
            ->withCount('ingresos as lenguas_count')
            ->orderByDesc('realizado_at')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('entrega-conformidad.index', [
            'despachos' => $despachos,
        ]);
    }

    public function pdf(Despacho $despacho): Response
    {
        $despacho->load('user');

        $lineas = IngresoLenguaLocal::query()
            ->where('despacho_id', $despacho->id)
            ->orderBy('id')
            ->get(['id_producto', 'destino', 'fecha_registro', 'hora_registro']);

        $expedicionAt = now()->timezone(config('app.timezone'));

        $pdf = Pdf::loadView('entrega-conformidad.pdf-documento', [
            'despacho' => $despacho,
            'lineas' => $lineas,
            'expedicionAt' => $expedicionAt,
            'logoDataUri' => $this->logoInstitucionalDataUri(),
        ]);

        $pdf->setPaper('a4', 'portrait');

        $nombre = 'documento-conformidad-despacho-'.$despacho->id.'.pdf';

        return $pdf->stream($nombre);
    }

    private function logoInstitucionalDataUri(): ?string
    {
        foreach (['images/fond.png', 'images/lengua.png'] as $rel) {
            $path = public_path($rel);
            if (! is_readable($path)) {
                continue;
            }
            $raw = @file_get_contents($path);
            if ($raw === false || $raw === '') {
                continue;
            }

            return 'data:image/png;base64,'.base64_encode($raw);
        }

        return null;
    }
}
