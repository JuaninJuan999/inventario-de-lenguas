<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @include('partials.favicon')
        <title>Decomisos de Lenguas — {{ config('app.name') }}</title>
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
                max-width: 72rem;
                margin: 0 auto 1rem;
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
                gap: 0.75rem;
            }
            .page-bar h1 {
                margin: 0;
                font-size: 1.15rem;
                font-weight: 700;
                text-shadow:
                    0 0 28px color-mix(in srgb, var(--brand-green) 35%, transparent),
                    0 2px 8px rgba(0, 0, 0, 0.45);
            }
            .page-bar .sub {
                margin: 0.35rem 0 0;
                font-size: 0.82rem;
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
            }
            .link-menu:hover {
                background: color-mix(in srgb, var(--brand-rose) 16%, transparent);
            }
            .sheet {
                max-width: 72rem;
                margin: 0 auto;
                background: var(--welcome-surface);
                padding: 1.25rem 1.35rem 1.5rem;
                border-radius: 1.25rem;
                border: 1px solid color-mix(in srgb, var(--brand-green) 35%, transparent);
                box-shadow:
                    0 0 0 1px color-mix(in srgb, var(--brand-rose) 12%, transparent) inset,
                    0 24px 56px rgba(0, 0, 0, 0.45);
                backdrop-filter: blur(16px);
                -webkit-backdrop-filter: blur(16px);
            }
            .meta {
                margin: 0 0 1rem;
                font-size: 0.78rem;
                font-weight: 600;
                letter-spacing: 0.04em;
                text-transform: uppercase;
                color: color-mix(in srgb, var(--brand-rose) 92%, #fff);
            }
            .alert {
                margin: 0 0 1rem;
                padding: 0.75rem 0.9rem;
                border-radius: 0.65rem;
                font-size: 0.88rem;
                font-weight: 600;
                border: 1px solid color-mix(in srgb, #ffb4b4 55%, transparent);
                background: color-mix(in srgb, #5c1a1a 55%, rgba(0, 0, 0, 0.35));
                color: #ffe4e4;
            }
            .alert--ok {
                border: 1px solid color-mix(in srgb, var(--brand-green) 45%, transparent);
                background: color-mix(in srgb, var(--brand-green) 12%, rgba(0, 0, 0, 0.35));
                color: var(--text);
            }
            .table-wrap {
                overflow-x: auto;
                border-radius: 0.65rem;
                border: 1px solid color-mix(in srgb, var(--brand-green) 28%, rgba(0, 0, 0, 0.5));
            }
            table.data {
                width: 100%;
                border-collapse: collapse;
                font-size: 0.84rem;
            }
            table.data th,
            table.data td {
                border: 1px solid color-mix(in srgb, var(--brand-green) 28%, rgba(0, 0, 0, 0.5));
                padding: 0.5rem 0.6rem;
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
                font-size: 0.68rem;
                color: var(--text);
                white-space: nowrap;
            }
            table.data tbody tr:nth-child(even) {
                background: rgba(0, 0, 0, 0.2);
            }
            .muted {
                margin: 0;
                font-size: 0.85rem;
                color: color-mix(in srgb, var(--text) 65%, transparent);
            }
            .filters {
                margin-bottom: 1.25rem;
                padding-bottom: 1.25rem;
                border-bottom: 1px solid color-mix(in srgb, var(--brand-green) 22%, transparent);
            }
            .filters-row {
                display: flex;
                flex-wrap: nowrap;
                align-items: flex-end;
                gap: 0.65rem 0.85rem;
                overflow-x: auto;
                padding-bottom: 0.1rem;
                -webkit-overflow-scrolling: touch;
            }
            .filter-field--grow {
                flex: 1 1 10rem;
                min-width: 8rem;
            }
            .filter-field--date {
                flex: 0 0 auto;
            }
            .filter-field--date input[type="date"] {
                width: auto;
                min-width: 10.75rem;
            }
            .filter-actions {
                display: flex;
                flex-wrap: nowrap;
                align-items: center;
                gap: 0.5rem;
                flex-shrink: 0;
                margin: 0;
                padding-bottom: 0.05rem;
            }
            .filter-field label {
                display: block;
                font-size: 0.68rem;
                font-weight: 800;
                letter-spacing: 0.06em;
                text-transform: uppercase;
                color: color-mix(in srgb, var(--brand-rose) 90%, #fff);
                margin-bottom: 0.35rem;
            }
            .filter-field input {
                width: 100%;
                padding: 0.5rem 0.65rem;
                border-radius: 6px;
                border: 1px solid color-mix(in srgb, var(--brand-green) 40%, #1a3d2e);
                background: rgba(0, 0, 0, 0.35);
                color: var(--text);
                font-size: 0.9rem;
                font-family: inherit;
            }
            .filter-field input:focus {
                outline: 2px solid var(--brand-green);
                outline-offset: 1px;
            }
            .filter-field .err {
                margin: 0.3rem 0 0;
                font-size: 0.75rem;
                font-weight: 600;
                color: #ffb4b4;
                white-space: nowrap;
            }
            .btn-apply {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0.5rem 1.15rem;
                font-size: 0.75rem;
                font-weight: 800;
                letter-spacing: 0.05em;
                text-transform: uppercase;
                color: #04261c;
                border: 1px solid color-mix(in srgb, var(--brand-green) 50%, #fff);
                border-radius: 6px;
                cursor: pointer;
                font-family: inherit;
                background: linear-gradient(145deg, var(--brand-green), color-mix(in srgb, var(--brand-green) 72%, var(--green-deep)));
            }
            .btn-apply:hover {
                filter: brightness(1.06);
            }
            .btn-clear {
                font-size: 0.82rem;
                font-weight: 600;
                color: var(--brand-rose);
                text-decoration: none;
                padding: 0.45rem 0.75rem;
                border-radius: 9999px;
                border: 1px solid color-mix(in srgb, var(--brand-rose) 45%, transparent);
            }
            .btn-clear:hover {
                background: color-mix(in srgb, var(--brand-rose) 12%, transparent);
            }
        </style>
    </head>
    <body>
        @include('partials.logo-institucional')
        <header class="page-bar">
            <div>
                <h1>Decomisos de Lenguas</h1>
                <p class="sub">
                    Listado desde <strong>SIRT</strong>. Sin búsqueda libre, el listado usa por defecto la
                    <strong>fecha de registro de hoy</strong> (puede cambiar desde/hasta). Si escribe en
                    <strong>búsqueda libre</strong> (p. ej. id de producto), la consulta ignora las fechas y busca en
                    todo el historial disponible (hasta 500 filas, las más recientes). Tras cada consulta exitosa,
                    los id listados se sincronizan con el inventario local (misma baja que al despachar).
                </p>
            </div>
            <a class="link-menu" href="{{ route('menu') }}">← Menú</a>
        </header>

        <section class="sheet" aria-labelledby="tabla-decomisos">
            <p id="tabla-decomisos" class="meta">
                Columnas: id producto · fecha registro · hora registro · dictamen · enfermedad
            </p>

            @if (! empty($error))
                <p class="alert" role="alert">{{ $error }}</p>
            @endif
            @if (! empty($invSyncDecomisoError))
                <p class="alert" role="alert">{{ $invSyncDecomisoError }}</p>
            @elseif (($invRetiradosDecomiso ?? 0) > 0)
                <p class="alert alert--ok" role="status">
                    Inventario local: se retiraron automáticamente
                    <strong>{{ (int) $invRetiradosDecomiso }}</strong>
                    lengua(s) cuyo id de producto aparece en esta consulta de decomisos SIRT.
                </p>
            @endif

            <form class="filters" method="get" action="{{ route('decomisos.lenguas') }}" aria-label="Filtros de búsqueda">
                <div class="filters-row">
                    <div class="filter-field filter-field--grow">
                        <label for="f-q">Búsqueda libre</label>
                        <input
                            id="f-q"
                            type="search"
                            name="q"
                            value="{{ $filters['q'] ?? '' }}"
                            maxlength="200"
                            placeholder="Id, dictamen o enfermedad…"
                            autocomplete="off"
                        />
                        @error('q')
                            <p class="err">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="filter-field filter-field--date">
                        <label for="f-fecha-desde">Desde</label>
                        <input
                            id="f-fecha-desde"
                            type="date"
                            name="fecha_desde"
                            value="{{ $filters['fecha_desde'] ?? '' }}"
                        />
                        @error('fecha_desde')
                            <p class="err">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="filter-field filter-field--date">
                        <label for="f-fecha-hasta">Hasta</label>
                        <input
                            id="f-fecha-hasta"
                            type="date"
                            name="fecha_hasta"
                            value="{{ $filters['fecha_hasta'] ?? '' }}"
                        />
                        @error('fecha_hasta')
                            <p class="err">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="filter-actions">
                        <button class="btn-apply" type="submit">Aplicar</button>
                        <a class="btn-clear" href="{{ route('decomisos.lenguas') }}">Limpiar</a>
                    </div>
                </div>
            </form>

            @if ($errors->any() && empty($error))
                <p class="alert" role="alert">Revise los filtros marcados abajo.</p>
            @endif

            @if (empty($error) && count($rows) === 0)
                <p class="muted">No hay filas para los filtros indicados (máx. 500 por consulta).</p>
            @elseif (empty($error))
                <p class="muted" style="margin: 0 0 0.75rem">{{ count($rows) }} registro(s).</p>
                <div class="table-wrap">
                    <table class="data">
                        <thead>
                            <tr>
                                <th>Id producto</th>
                                <th>Fecha registro</th>
                                <th>Hora registro</th>
                                <th>Nombre dictamen</th>
                                <th>Nombre enfermedad</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $row)
                                @php
                                    $r = (array) $row;
                                    $idProd = isset($r['id_producto']) ? (string) $r['id_producto'] : (isset($r['ID_PRODUCTO']) ? (string) $r['ID_PRODUCTO'] : '');
                                    $hora = $r['hora_registro'] ?? null;
                                    if ($hora instanceof \DateTimeInterface) {
                                        $horaTxt = $hora->format('H:i:s');
                                    } else {
                                        $horaTxt = $hora !== null && $hora !== '' ? (string) $hora : '—';
                                    }
                                    $fecha = $r['fecha_registro'] ?? null;
                                    if ($fecha instanceof \DateTimeInterface) {
                                        $fechaTxt = $fecha->format('Y-m-d');
                                    } else {
                                        $fechaTxt = $fecha !== null && $fecha !== '' ? (string) $fecha : '—';
                                    }
                                @endphp
                                <tr>
                                    <td>{{ $idProd !== '' ? $idProd : '—' }}</td>
                                    <td>{{ $fechaTxt }}</td>
                                    <td>{{ $horaTxt }}</td>
                                    <td>{{ $r['nombre_dictamen'] ?? '—' }}</td>
                                    <td>{{ $r['nombre_enfermedad'] ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </body>
</html>
