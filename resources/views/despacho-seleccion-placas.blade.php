<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        @include('partials.favicon')
        <title>Despacho — Seleccionar placa — {{ config('app.name') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet" />
        <style>
            :root {
                --brand-rose: #f9dff8;
                --brand-green: #7ce8ad;
                --welcome-base: #050807;
                --welcome-surface: rgba(6, 18, 14, 0.78);
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
                padding: max(0.75rem, env(safe-area-inset-top)) max(0.85rem, env(safe-area-inset-right))
                    max(1.25rem, env(safe-area-inset-bottom)) max(0.85rem, env(safe-area-inset-left));
            }
            .page-bar {
                max-width: 56rem;
                margin: 0 auto 1rem;
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
                gap: 0.75rem;
            }
            .page-bar__actions {
                display: flex;
                flex-wrap: wrap;
                gap: 0.5rem;
                justify-content: flex-end;
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
                max-width: 42rem;
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
                -webkit-tap-highlight-color: color-mix(in srgb, var(--brand-green) 35%, transparent);
                touch-action: manipulation;
            }
            .link-menu:focus-visible {
                outline: 2px solid var(--brand-green);
                outline-offset: 2px;
            }
            .sheet {
                max-width: 56rem;
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
                overflow-wrap: anywhere;
                word-break: break-word;
            }
            .muted code {
                overflow-wrap: anywhere;
                word-break: break-word;
            }
            .alert {
                margin: 0 0 1rem;
                padding: 0.65rem 0.85rem;
                border-radius: 8px;
                font-size: 0.85rem;
                font-weight: 600;
                border: 1px solid color-mix(in srgb, #ff6b6b 55%, transparent);
                background: color-mix(in srgb, #ff6b6b 12%, transparent);
                color: #ffe0e0;
            }
            .table-wrap {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                border-radius: 0.65rem;
                border: 1px solid color-mix(in srgb, var(--brand-green) 28%, rgba(0, 0, 0, 0.5));
            }
            table.data {
                width: 100%;
                border-collapse: collapse;
                font-size: 0.85rem;
            }
            table.data th,
            table.data td {
                border: 1px solid color-mix(in srgb, var(--brand-green) 28%, rgba(0, 0, 0, 0.5));
                padding: 0.45rem 0.55rem;
                text-align: left;
                vertical-align: top;
            }
            table.data th {
                background: linear-gradient(
                    180deg,
                    color-mix(in srgb, var(--brand-green) 22%, transparent),
                    color-mix(in srgb, var(--brand-green) 10%, transparent)
                );
                font-weight: 800;
                text-transform: uppercase;
                letter-spacing: 0.04em;
                font-size: 0.65rem;
            }
            table.data tbody tr:nth-child(even) {
                background: rgba(0, 0, 0, 0.18);
            }
            .btn-abrir {
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
                background: linear-gradient(145deg, var(--brand-green), color-mix(in srgb, var(--brand-green) 72%, #1a5c40));
                text-decoration: none;
                white-space: nowrap;
            }
            .btn-abrir:hover {
                filter: brightness(1.06);
            }
            .btn-abrir:active {
                filter: brightness(0.96);
                transform: translateY(1px);
            }
            .btn-abrir:focus-visible {
                outline: 2px solid var(--brand-green);
                outline-offset: 2px;
            }
            .empty-state {
                margin: 0.35rem 0 0;
                padding: 1.15rem 1.2rem 1.25rem;
                border-radius: 0.85rem;
                border: 1px solid color-mix(in srgb, var(--brand-green) 40%, transparent);
                background: color-mix(in srgb, var(--brand-green) 8%, rgba(0, 0, 0, 0.28));
                text-align: center;
            }
            .empty-state__title {
                margin: 0 0 0.45rem;
                font-size: 1.05rem;
                font-weight: 800;
                color: var(--brand-green);
            }
            .empty-state__text {
                margin: 0;
                font-size: 0.9rem;
                line-height: 1.5;
                color: color-mix(in srgb, var(--text) 88%, transparent);
                font-weight: 500;
            }
            .empty-state__actions {
                margin-top: 0.9rem;
                display: flex;
                flex-wrap: wrap;
                gap: 0.5rem;
                justify-content: center;
            }
            .empty-state__actions a {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0.45rem 0.95rem;
                border-radius: 9999px;
                font-size: 0.82rem;
                font-weight: 700;
                text-decoration: none;
                border: 1px solid color-mix(in srgb, var(--brand-green) 50%, transparent);
                background: color-mix(in srgb, var(--brand-green) 16%, transparent);
                color: var(--brand-green);
            }
            .empty-state__actions a:hover {
                background: color-mix(in srgb, var(--brand-green) 26%, transparent);
            }
            @media (max-width: 640px) {
                .page-bar {
                    flex-direction: column;
                    align-items: stretch;
                    gap: 0.65rem;
                }
                .page-bar h1 {
                    font-size: 1.05rem;
                    line-height: 1.25;
                }
                .page-bar__actions {
                    justify-content: stretch;
                }
                .link-menu {
                    flex: 1 1 calc(50% - 0.25rem);
                    justify-content: center;
                    min-height: 44px;
                    display: inline-flex;
                    align-items: center;
                    text-align: center;
                    padding: 0.55rem 0.85rem;
                    box-sizing: border-box;
                }
                .sheet {
                    padding: 1rem 0.85rem 1.25rem;
                    border-radius: 1rem;
                }
                .muted {
                    font-size: 0.8rem;
                }
                .alert {
                    font-size: 0.8rem;
                    padding: 0.55rem 0.75rem;
                }
                .empty-state {
                    padding: 1rem 0.85rem 1.1rem;
                }
                .empty-state__title {
                    font-size: 0.98rem;
                }
                .empty-state__text {
                    font-size: 0.84rem;
                }
                .empty-state__actions a {
                    min-height: 44px;
                    padding: 0.55rem 1rem;
                }
            }
            /* Tarjetas por fila: mejor lectura y pulsación en pantallas estrechas */
            @media (max-width: 560px) {
                .table-wrap {
                    overflow-x: visible;
                    margin-left: -0.15rem;
                    margin-right: -0.15rem;
                    padding-left: 0.15rem;
                    padding-right: 0.15rem;
                }
                table.data thead {
                    position: absolute;
                    width: 1px;
                    height: 1px;
                    padding: 0;
                    margin: -1px;
                    overflow: hidden;
                    clip: rect(0, 0, 0, 0);
                    white-space: nowrap;
                    border: 0;
                }
                table.data tbody tr {
                    display: block;
                    margin-bottom: 0.85rem;
                    border-radius: 0.75rem;
                    border: 1px solid color-mix(in srgb, var(--brand-green) 35%, rgba(0, 0, 0, 0.5));
                    overflow: hidden;
                    background: rgba(0, 0, 0, 0.22);
                }
                table.data tbody td {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    gap: 0.75rem;
                    padding: 0.65rem 0.85rem;
                    border: none;
                    border-bottom: 1px solid color-mix(in srgb, var(--brand-green) 22%, rgba(0, 0, 0, 0.45));
                    font-size: 0.92rem;
                }
                table.data tbody td:last-child {
                    border-bottom: none;
                    flex-direction: column;
                    align-items: stretch;
                    padding-top: 0.75rem;
                    padding-bottom: 0.75rem;
                }
                table.data tbody td::before {
                    content: attr(data-label);
                    font-size: 0.62rem;
                    font-weight: 800;
                    letter-spacing: 0.06em;
                    text-transform: uppercase;
                    color: var(--brand-rose);
                    flex-shrink: 0;
                    max-width: 42%;
                }
                table.data tbody td:last-child::before {
                    display: none;
                }
                table.data .btn-abrir {
                    width: 100%;
                    justify-content: center;
                    min-height: 46px;
                    font-size: 0.78rem;
                    padding: 0.55rem 1rem;
                }
            }
        </style>
    </head>
    <body>
        @include('partials.logo-institucional')
        <header class="page-bar">
            <div>
                <h1>Despacho de lenguas — alistamiento (informativo)</h1>
            </div>
            <div class="page-bar__actions">
                <a class="link-menu" href="{{ route('despacho.lenguas.manual') }}">Despacho manual</a>
                <a class="link-menu" href="{{ route('menu') }}">← Menú</a>
            </div>
        </header>

        <section class="sheet">
            @if (session('despacho_flash_error'))
                <p class="alert" role="alert">{{ session('despacho_flash_error') }}</p>
            @endif
            @if ($error !== null)
                <p class="alert" role="alert">{{ $error }}</p>
            @endif

            @if (config('despacho.ppv_modo_solo_consulta_colbeef'))
                <p class="muted">
                    En modo solo consulta no se aplica filtro <code>va.user_name</code> en SQL (se listan todos los usuarios
                    de vehículo asignado para esa parte).
                </p>
            @elseif ($filtraPorUserName ?? false)
                <p class="muted">
                    Filtro por usuario Colbeef activo: <strong>{{ $filtroUserName }}</strong>
                    (<code>DESPACHO_VEHICULO_USER_NAME</code> o usuario Laravel si <code>DESPACHO_USE_AUTH_USERNAME=true</code>).
                    Para un alistamiento solo por placa/producto/fecha, use <code>DESPACHO_PPV_FILTRAR_USER_NAME=false</code>.
                </p>
            @endif

            @if (($error === null) && $placas === [])
                <div class="empty-state" role="status">
                    <p class="empty-state__title">Alistamiento vacío</p>
                    <p class="empty-state__text">
                        No hay placas con códigos pendientes para despachar en este momento.
                        Puede usar <strong>Despacho manual</strong> o revisar el inventario si espera ver vehículos asignados hoy.
                    </p>
                    <div class="empty-state__actions">
                        <a href="{{ route('despacho.lenguas.manual') }}">Ir a despacho manual</a>
                        <a href="/inventario-de-lenguas">Abrir inventario</a>
                    </div>
                </div>
            @elseif ($placas !== [])
                <div class="table-wrap">
                    <table class="data">
                        <thead>
                            <tr>
                                <th>Placa</th>
                                <th>Pendientes</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($placas as $p)
                                <tr>
                                    <td data-label="Placa">{{ $p->placa_vehiculo ?? '—' }}</td>
                                    <td data-label="Pendientes">{{ (int) ($p->pendientes ?? 0) }}</td>
                                    <td data-label="">
                                        <a
                                            class="btn-abrir"
                                            href="{{ route('despacho.lenguas.checklist', ['id_vehiculo_asignado' => $p->id_vehiculo_asignado]) }}"
                                        >
                                            Abrir checklist
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </body>
</html>
