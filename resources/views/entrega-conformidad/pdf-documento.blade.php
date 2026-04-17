<!DOCTYPE html>
<html lang="es">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Despacho de lenguas — Conformidad</title>
        <style>
            * {
                box-sizing: border-box;
            }
            body {
                font-family: DejaVu Sans, sans-serif;
                font-size: 9.5pt;
                color: #1a1a1a;
                margin: 0;
                padding: 18px 22px 28px;
                line-height: 1.35;
            }
            .doc-header {
                text-align: center;
                margin-bottom: 8px;
            }
            .doc-logo {
                max-height: 46px;
                width: auto;
                margin-bottom: 4px;
            }
            .doc-title {
                margin: 0 auto 2px;
                padding: 0 8px 5px;
                font-size: 15pt;
                font-weight: bold;
                letter-spacing: 0.14em;
                text-transform: uppercase;
                color: #0d3d2a;
                border-bottom: 2.5px solid #2d8a5c;
                display: inline-block;
            }
            table.resumen {
                width: 100%;
                margin: 0 auto 5px;
                border-collapse: collapse;
                font-size: 8.5pt;
                table-layout: fixed;
            }
            table.resumen th,
            table.resumen td {
                border: 1px solid #b8c9c0;
                padding: 3px 5px;
                text-align: center;
                vertical-align: middle;
                width: 25%;
            }
            table.resumen thead th {
                background: #e8f4ee;
                font-weight: bold;
                color: #0d3d2a;
                font-size: 7.8pt;
                line-height: 1.15;
            }
            table.resumen tbody td {
                background: #fff;
                font-size: 9pt;
            }
            .nota {
                margin: 4px 0 8px;
                padding: 5px 8px;
                border: 1px solid #c5d9ce;
                background: #f6faf8;
                font-size: 8.5pt;
                line-height: 1.3;
                text-align: justify;
                font-style: italic;
                color: #222;
            }
            table.detalle {
                width: 100%;
                border-collapse: collapse;
                font-size: 8.5pt;
            }
            table.detalle th,
            table.detalle td {
                border: 1px solid #9aaf9f;
                padding: 5px 6px;
                text-align: left;
                vertical-align: top;
            }
            table.detalle th {
                background: #1a5c40;
                color: #fff;
                font-weight: bold;
                text-transform: uppercase;
                letter-spacing: 0.04em;
                font-size: 7.5pt;
            }
            table.detalle tr:nth-child(even) td {
                background: #f3f8f5;
            }
            .codigo-cell {
                white-space: nowrap;
                font-family: DejaVu Sans Mono, DejaVu Sans, monospace;
                font-size: 8.2pt;
            }
            .pie {
                margin-top: 16px;
                font-size: 7.5pt;
                color: #555;
                text-align: center;
            }
            .firmas-wrap {
                margin-top: 32px;
                padding-top: 16px;
                border-top: 1px solid #c5d9ce;
            }
            table.firmas {
                width: 100%;
                border-collapse: collapse;
                font-size: 10pt;
            }
            table.firmas td {
                width: 50%;
                vertical-align: bottom;
                padding: 22px 14px 0 0;
            }
            table.firmas td:last-child {
                padding-right: 0;
                padding-left: 14px;
            }
            .firma-texto {
                white-space: nowrap;
            }
            .firma-linea {
                display: inline-block;
                min-width: 200px;
                margin-left: 6px;
                border-bottom: 1px solid #1a1a1a;
                height: 16px;
                vertical-align: bottom;
            }
        </style>
    </head>
    <body>
        <div class="doc-header">
            @if (! empty($logoDataUri))
                <img class="doc-logo" src="{{ $logoDataUri }}" alt="" />
            @endif
            <div class="doc-title">Despacho de lenguas</div>
        </div>

        <table class="resumen" aria-label="Resumen del despacho">
            <thead>
                <tr>
                    <th scope="col">Conductor</th>
                    <th scope="col">Placa / vehículo</th>
                    <th scope="col">Fecha de expedición</th>
                    <th scope="col">Total lenguas</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $despacho->conductor !== null && $despacho->conductor !== '' ? $despacho->conductor : '—' }}</td>
                    <td>{{ $despacho->placa !== null && $despacho->placa !== '' ? $despacho->placa : '—' }}</td>
                    <td>{{ $expedicionAt->format('d/m/Y H:i') }}</td>
                    <td>{{ $lineas->count() }}</td>
                </tr>
            </tbody>
        </table>

        <p class="nota">
            <strong>Nota:</strong> Los productos relacionados a continuación, se despachan a conformidad, aptos para consumo
            humano, no presentan cambios en sus características organolépticas.
        </p>

        <table class="detalle" aria-label="Detalle de lenguas despachadas">
            <thead>
                <tr>
                    <th scope="col">Código</th>
                    <th scope="col">Descripción</th>
                    <th scope="col">Fecha beneficio</th>
                    <th scope="col">Destino</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($lineas as $row)
                    @php
                        $fb = $row->fecha_registro !== null ? $row->fecha_registro->format('d/m/Y') : '—';
                        $dest = $row->destino !== null && $row->destino !== '' ? $row->destino : '—';
                    @endphp
                    <tr>
                        <td class="codigo-cell">{{ $row->id_producto }}</td>
                        <td>LENGUA</td>
                        <td>{{ $fb }}</td>
                        <td>{{ $dest }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; font-style: italic">No hay líneas asociadas a este despacho.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="firmas-wrap">
            <table class="firmas" aria-label="Firmas">
                <tr>
                    <td>
                        <span class="firma-texto">Entrega:</span><span class="firma-linea"></span>
                    </td>
                    <td>
                        <span class="firma-texto">Recibe:</span><span class="firma-linea"></span>
                    </td>
                </tr>
            </table>
        </div>
    </body>
</html>
