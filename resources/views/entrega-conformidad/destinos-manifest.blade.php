<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Destinos despacho {{ $despacho->id }} — {{ config('app.name') }}</title>
        <style>
            * {
                box-sizing: border-box;
            }
            body {
                margin: 0;
                padding: 1.25rem 1.5rem 2rem;
                font-family: Arial, Helvetica, sans-serif;
                font-size: 11pt;
                color: #111;
                background: #fff;
            }
            .meta {
                display: flex;
                flex-wrap: wrap;
                justify-content: space-between;
                align-items: flex-start;
                gap: 0.75rem 1.5rem;
                margin-bottom: 1rem;
            }
            .meta dl {
                margin: 0;
            }
            .meta dt {
                font-weight: 700;
                display: inline;
            }
            .meta dd {
                display: inline;
                margin: 0;
            }
            .meta-right {
                text-align: right;
            }
            table.manifest {
                width: 100%;
                border-collapse: collapse;
                border: 1px solid #000;
            }
            table.manifest th,
            table.manifest td {
                border: 1px solid #000;
                padding: 0.4rem 0.5rem;
                vertical-align: top;
            }
            table.manifest thead th {
                background: #1a5c40;
                color: #fff;
                font-weight: 700;
                text-align: center;
                text-transform: uppercase;
                letter-spacing: 0.02em;
            }
            table.manifest tbody tr:nth-child(even) {
                background: #f0f0f0;
            }
            table.manifest tbody tr:nth-child(odd) {
                background: #fff;
            }
            .col-destino {
                width: 22%;
                text-align: center;
                font-weight: 700;
            }
            .col-direccion {
                text-align: left;
                font-weight: 400;
            }
            .captura-root {
                width: 820px;
                max-width: 100%;
                margin: 0 auto;
                padding: 1.25rem 1.5rem 1.5rem;
                background: #fff;
            }
            .hint {
                margin-top: 1rem;
                font-size: 9pt;
                color: #444;
                text-align: center;
            }
            @media print {
                body {
                    padding: 0.5rem;
                }
                .hint {
                    display: none;
                }
            }
        </style>
    </head>
    <body>
        <div id="captura-manifiesto" class="captura-root">
            <div class="meta">
                <dl>
                    <div>
                        <dt>Conductor:</dt>
                        <dd>{{ $despacho->conductor !== null && $despacho->conductor !== '' ? $despacho->conductor : '—' }}</dd>
                    </div>
                    <div>
                        <dt>Vehículo:</dt>
                        <dd>{{ $despacho->placa !== null && $despacho->placa !== '' ? $despacho->placa : '—' }}</dd>
                    </div>
                </dl>
                <div class="meta-right">
                    <strong>N° Destinos:</strong>
                    {{ $numDestinos }}
                </div>
            </div>

            <table class="manifest" role="table" aria-label="Destinos del despacho">
                <thead>
                    <tr>
                        <th scope="col">Destino</th>
                        <th scope="col">Dirección</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($filas as $fila)
                        <tr>
                            <td class="col-destino">{{ $fila['codigo'] }}</td>
                            <td class="col-direccion">{{ $fila['direccion'] !== '' ? $fila['direccion'] : '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td class="col-destino">—</td>
                            <td class="col-direccion">—</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <p class="hint">
            Vista para imprimir (Ctrl+P). Despacho #{{ $despacho->id }} —
            {{ $despacho->realizado_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') ?? '—' }}.
        </p>
    </body>
</html>
