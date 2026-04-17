<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @include('partials.favicon')
        <title>Entrega de Conformidad — {{ config('app.name') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet" />
        <style>
            :root {
                --brand-rose: #f9dff8;
                --brand-green: #7ce8ad;
                --welcome-base: #050807;
                --welcome-surface: rgba(6, 18, 14, 0.78);
                --green-deep: #1a5c40;
                --text: #f4fffa;
            }
            * {
                box-sizing: border-box;
            }
            body {
                margin: 0;
                min-height: 100vh;
                min-height: 100dvh;
                font-family: "Instrument Sans", ui-sans-serif, system-ui, sans-serif;
                background-color: var(--welcome-base);
                background-image:
                    radial-gradient(ellipse 130% 100% at 0% -15%, rgba(124, 232, 173, 0.22), transparent 52%),
                    radial-gradient(ellipse 110% 90% at 100% 15%, rgba(249, 223, 248, 0.18), transparent 48%);
                color: var(--text);
                padding: 1rem 1.25rem 2rem;
            }
            .page-bar {
                max-width: 90rem;
                margin: 0 auto 1rem;
                display: flex;
                flex-wrap: wrap;
                align-items: flex-start;
                justify-content: space-between;
                gap: 0.75rem;
            }
            .page-bar h1 {
                margin: 0;
                font-size: 1.15rem;
                font-weight: 700;
            }
            .page-bar .sub {
                margin: 0.35rem 0 0;
                font-size: 0.82rem;
                line-height: 1.45;
                opacity: 0.88;
                max-width: 48rem;
            }
            .link-menu {
                font-size: 0.875rem;
                font-weight: 600;
                color: var(--brand-rose);
                text-decoration: none;
                padding: 0.4rem 0.85rem;
                border-radius: 9999px;
                border: 1px solid color-mix(in srgb, var(--brand-rose) 45%, transparent);
                background: color-mix(in srgb, var(--brand-rose) 8%, transparent);
            }
            .link-menu:hover {
                background: color-mix(in srgb, var(--brand-rose) 16%, transparent);
            }
            .sheet {
                max-width: 90rem;
                margin: 0 auto;
                background: var(--welcome-surface);
                padding: 1.25rem 1.35rem 1.5rem;
                border-radius: 1.25rem;
                border: 1px solid color-mix(in srgb, var(--brand-green) 35%, transparent);
            }
            .muted {
                margin: 0 0 1rem;
                font-size: 0.85rem;
                color: color-mix(in srgb, var(--text) 68%, transparent);
            }
            .table-wrap {
                overflow-x: auto;
                border-radius: 0.65rem;
                border: 1px solid color-mix(in srgb, var(--brand-green) 28%, rgba(0, 0, 0, 0.5));
            }
            table.data {
                width: 100%;
                border-collapse: collapse;
                font-size: 0.8rem;
            }
            table.data th,
            table.data td {
                border: 1px solid color-mix(in srgb, var(--brand-green) 28%, rgba(0, 0, 0, 0.5));
                padding: 0.45rem 0.5rem;
                text-align: left;
                vertical-align: top;
            }
            table.data th {
                background: linear-gradient(
                    180deg,
                    color-mix(in srgb, var(--brand-green) 24%, transparent),
                    color-mix(in srgb, var(--brand-green) 10%, transparent)
                );
                font-weight: 800;
                text-transform: uppercase;
                letter-spacing: 0.04em;
                font-size: 0.65rem;
            }
            table.data tbody tr:nth-child(even) {
                background: rgba(0, 0, 0, 0.2);
            }
            table.data th.col-empresa,
            table.data td.col-empresa {
                min-width: 8rem;
                max-width: 16rem;
                white-space: normal;
                word-break: break-word;
                overflow-wrap: anywhere;
            }
            .btn-pdf {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0.35rem 0.75rem;
                font-size: 0.72rem;
                font-weight: 700;
                letter-spacing: 0.04em;
                text-transform: uppercase;
                color: #04261c;
                border: 1px solid color-mix(in srgb, var(--brand-green) 50%, #fff);
                border-radius: 6px;
                cursor: pointer;
                font-family: inherit;
                text-decoration: none;
                background: linear-gradient(145deg, var(--brand-green), color-mix(in srgb, var(--brand-green) 72%, var(--green-deep)));
            }
            .btn-pdf:hover {
                filter: brightness(1.06);
            }
            .btn-pdf[disabled],
            .btn-pdf.is-disabled {
                opacity: 0.45;
                pointer-events: none;
                cursor: not-allowed;
            }
            .pagination-wrap {
                margin-top: 1rem;
                font-size: 0.85rem;
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 0.65rem 1rem;
            }
            .pagination-wrap a {
                color: var(--brand-rose);
                font-weight: 600;
            }
            .pagination-wrap a:hover {
                text-decoration: underline;
            }
            .pagination-wrap .page-now {
                color: color-mix(in srgb, var(--text) 75%, transparent);
            }
        </style>
    </head>
    <body>
        @include('partials.logo-institucional')
        <header class="page-bar">
            <div>
                <h1>Entrega de Conformidad</h1>
                <p class="sub">
                    Solo para movimientos tipo <strong>Despacho</strong> (módulo Despacho de lenguas). Genere el PDF con
                    el acto de conformidad: resumen del transporte, nota legal y detalle por lengua (código, descripción,
                    fecha de beneficio del ingreso y destino).
                </p>
            </div>
            <a class="link-menu" href="{{ route('menu') }}">← Menú</a>
        </header>

        <section class="sheet">
            @if ($despachos->isEmpty())
                <p class="muted">
                    No hay despachos registrados. El documento se habilita cuando exista al menos un despacho finalizado
                    con lenguas asociadas.
                </p>
            @else
                <p class="muted">
                    <strong>{{ $despachos->total() }}</strong> despacho(s). Página {{ $despachos->currentPage() }} de
                    {{ $despachos->lastPage() }}.
                </p>
                <div class="table-wrap">
                    <table class="data">
                        <thead>
                            <tr>
                                <th>Fecha despacho</th>
                                <th class="col-empresa">Empresa</th>
                                <th>Conductor</th>
                                <th>Placa</th>
                                <th>Lenguas</th>
                                <th>Usuario</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($despachos as $d)
                                <tr>
                                    <td>{{ $d->realizado_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') ?? '—' }}</td>
                                    <td class="col-empresa">{{ $d->empresa !== null && $d->empresa !== '' ? $d->empresa : '—' }}</td>
                                    <td>{{ $d->conductor !== null && $d->conductor !== '' ? $d->conductor : '—' }}</td>
                                    <td>{{ $d->placa !== null && $d->placa !== '' ? $d->placa : '—' }}</td>
                                    <td>{{ (int) ($d->lenguas_count ?? 0) }}</td>
                                    <td>{{ $d->user?->name ?? '—' }}</td>
                                    <td>
                                        @if ((int) ($d->lenguas_count ?? 0) > 0)
                                            <a
                                                class="btn-pdf"
                                                href="{{ route('entrega.conformidad.pdf', $d) }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                            >
                                                Generar documento de despacho
                                            </a>
                                        @else
                                            <span class="btn-pdf is-disabled" aria-disabled="true">Sin lenguas</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($despachos->hasPages())
                    <nav class="pagination-wrap" aria-label="Paginación">
                        @if ($despachos->onFirstPage())
                            <span class="page-now" style="opacity: 0.45">← Anterior</span>
                        @else
                            <a href="{{ $despachos->previousPageUrl() }}">← Anterior</a>
                        @endif
                        <span class="page-now">Página {{ $despachos->currentPage() }} de {{ $despachos->lastPage() }}</span>
                        @if ($despachos->hasMorePages())
                            <a href="{{ $despachos->nextPageUrl() }}">Siguiente →</a>
                        @else
                            <span class="page-now" style="opacity: 0.45">Siguiente →</span>
                        @endif
                    </nav>
                @endif
            @endif
        </section>
    </body>
</html>
