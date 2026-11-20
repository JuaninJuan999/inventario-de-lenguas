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
                color: #ffffff;
                background: rgba(255, 255, 255, 0.12);
                border: 1px solid color-mix(in srgb, #ffffff 55%, var(--brand-green));
                color-scheme: dark;
            }
            .filter-field--date input[type="date"]::-webkit-calendar-picker-indicator {
                cursor: pointer;
                opacity: 1;
                filter: invert(1) brightness(1.15);
            }
            .filter-field--date input[type="date"]::-webkit-datetime-edit,
            .filter-field--date input[type="date"]::-webkit-datetime-edit-fields-wrapper,
            .filter-field--date input[type="date"]::-webkit-datetime-edit-text,
            .filter-field--date input[type="date"]::-webkit-datetime-edit-month-field,
            .filter-field--date input[type="date"]::-webkit-datetime-edit-day-field,
            .filter-field--date input[type="date"]::-webkit-datetime-edit-year-field {
                color: #ffffff;
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
            .btn-excel {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0.4rem 0.95rem;
                font-size: 0.72rem;
                font-weight: 800;
                letter-spacing: 0.05em;
                text-transform: uppercase;
                color: #04261c;
                text-decoration: none;
                border: 1px solid color-mix(in srgb, #9be7a8 55%, #fff);
                border-radius: 6px;
                cursor: pointer;
                font-family: inherit;
                background: linear-gradient(145deg, #c8f7d4, color-mix(in srgb, var(--brand-green) 55%, #1f6b48));
            }
            .btn-excel:hover {
                filter: brightness(1.06);
            }
            .listado-meta {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
                gap: 0.65rem;
                margin: 0 0 0.75rem;
            }
            .listado-meta .muted {
                margin: 0;
            }
            .manual-box {
                margin-bottom: 1.25rem;
                padding: 1rem 1.1rem 1.15rem;
                border-radius: 0.85rem;
                border: 1px solid color-mix(in srgb, var(--brand-rose) 28%, transparent);
                background: color-mix(in srgb, var(--brand-rose) 6%, rgba(0, 0, 0, 0.28));
            }
            .manual-box h2 {
                margin: 0 0 0.85rem;
                font-size: 0.78rem;
                font-weight: 800;
                letter-spacing: 0.06em;
                text-transform: uppercase;
                color: color-mix(in srgb, var(--brand-rose) 92%, #fff);
            }
            .manual-grid {
                display: grid;
                grid-template-columns: minmax(8rem, 1.1fr) minmax(9rem, 1.2fr) minmax(9rem, 1.2fr) auto;
                gap: 0.75rem 0.85rem;
                align-items: end;
            }
            .manual-grid .filter-field select {
                width: 100%;
                padding: 0.5rem 0.65rem;
                border-radius: 6px;
                border: 1px solid color-mix(in srgb, var(--brand-green) 40%, #1a3d2e);
                background: rgba(0, 0, 0, 0.35);
                color: var(--text);
                font-size: 0.9rem;
                font-family: inherit;
            }
            .suggest-wrap {
                position: relative;
            }
            .suggest-list {
                display: none;
                position: absolute;
                z-index: 40;
                left: 0;
                right: 0;
                top: calc(100% + 0.25rem);
                margin: 0;
                padding: 0.25rem 0;
                list-style: none;
                max-height: 14rem;
                overflow: auto;
                border-radius: 0.55rem;
                border: 1px solid color-mix(in srgb, var(--brand-green) 45%, transparent);
                background: rgba(6, 18, 14, 0.96);
                box-shadow: 0 12px 28px rgba(0, 0, 0, 0.45);
            }
            .suggest-list.is-open {
                display: block;
            }
            .suggest-list li {
                margin: 0;
            }
            .suggest-list button {
                display: block;
                width: 100%;
                text-align: left;
                border: 0;
                background: transparent;
                color: var(--text);
                font: inherit;
                padding: 0.45rem 0.7rem;
                cursor: pointer;
            }
            .suggest-list button:hover,
            .suggest-list button.is-active {
                background: color-mix(in srgb, var(--brand-green) 18%, transparent);
            }
            .suggest-list .suggest-code {
                font-weight: 700;
                letter-spacing: 0.02em;
            }
            .suggest-list .suggest-meta {
                display: block;
                margin-top: 0.15rem;
                font-size: 0.72rem;
                opacity: 0.78;
            }
            .manual-grid .filter-field select:focus {
                outline: 2px solid var(--brand-green);
                outline-offset: 1px;
            }
            .badge-origen {
                display: inline-block;
                margin-left: 0.35rem;
                padding: 0.12rem 0.45rem;
                border-radius: 9999px;
                font-size: 0.62rem;
                font-weight: 800;
                letter-spacing: 0.04em;
                text-transform: uppercase;
                vertical-align: middle;
            }
            .badge-origen--manual {
                color: #04261c;
                background: color-mix(in srgb, var(--brand-rose) 75%, #fff);
            }
            .badge-origen--sirt {
                color: #04261c;
                background: color-mix(in srgb, var(--brand-green) 70%, #fff);
            }
            @media (max-width: 860px) {
                .manual-grid {
                    grid-template-columns: 1fr;
                }
            }
        </style>
    </head>
    <body>
        @include('partials.logo-institucional')
        <header class="page-bar">
            <div>
                <h1>Decomisos de Lenguas</h1>
            </div>
            <a class="link-menu" href="{{ route('menu') }}">← Menú</a>
        </header>

        <section class="sheet" aria-labelledby="tabla-decomisos">
            <p id="tabla-decomisos" class="meta">
                Columnas: id producto · fecha de beneficio · fecha registro · hora registro · dictamen · enfermedad
            </p>

            @if (session('decomiso_manual_ok'))
                <p class="alert alert--ok" role="status">{{ session('decomiso_manual_ok') }}</p>
            @endif
            @if (session('decomiso_manual_error'))
                <p class="alert alert--err" role="alert">{{ session('decomiso_manual_error') }}</p>
            @endif

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

            @if (! empty($puedeRegistrarDecomisoManual))
            <div class="manual-box">
                <h2>Decomiso manual</h2>
                <form method="post" action="/decomisos-de-lenguas/manual" aria-label="Registrar decomiso manual">
                    @csrf
                    <div class="manual-grid">
                        <div class="filter-field">
                            <label for="m-id-producto">Id producto</label>
                            <div class="suggest-wrap">
                                <input
                                    id="m-id-producto"
                                    type="text"
                                    name="id_producto"
                                    value="{{ old('id_producto') }}"
                                    maxlength="80"
                                    required
                                    placeholder="Código en inventario…"
                                    autocomplete="off"
                                    aria-autocomplete="list"
                                    aria-controls="m-id-producto-suggest"
                                    data-suggest-url="/decomisos-de-lenguas/sugerencias-producto"
                                />
                                <ul
                                    id="m-id-producto-suggest"
                                    class="suggest-list"
                                    role="listbox"
                                    aria-hidden="true"
                                ></ul>
                            </div>
                            @error('id_producto')
                                <p class="err">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="filter-field">
                            <label for="m-dictamen">Nombre dictamen</label>
                            @php
                                $dictamenSeleccionado = old('nombre_dictamen', 'Decomisado');
                            @endphp
                            <select id="m-dictamen" name="nombre_dictamen" required>
                                <option value="">Seleccione…</option>
                                @foreach ($opcionesDictamen ?? [] as $opt)
                                    <option value="{{ $opt }}" @selected($dictamenSeleccionado === $opt)>{{ $opt }}</option>
                                @endforeach
                            </select>
                            @error('nombre_dictamen')
                                <p class="err">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="filter-field">
                            <label for="m-enfermedad">Nombre enfermedad</label>
                            <select id="m-enfermedad" name="nombre_enfermedad">
                                <option value="">— Sin enfermedad —</option>
                                @foreach ($opcionesEnfermedad ?? [] as $opt)
                                    <option value="{{ $opt }}" @selected(old('nombre_enfermedad') === $opt)>{{ $opt }}</option>
                                @endforeach
                            </select>
                            @error('nombre_enfermedad')
                                <p class="err">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="filter-actions">
                            <button class="btn-apply" type="submit">Registrar y retirar</button>
                        </div>
                    </div>
                </form>
            </div>
            @else
                <p class="muted" style="margin: 0 0 1rem">
                    Su rol tiene acceso de <strong>solo visualización</strong> a decomisos (consulta y Excel). No puede registrar altas manuales.
                </p>
            @endif

            <form
                id="filtros-decomisos"
                class="filters"
                method="get"
                action="/decomisos-de-lenguas"
                aria-label="Filtros de búsqueda"
            >
                <div class="filters-row">
                    <div class="filter-field filter-field--grow">
                        <label for="f-q">Búsqueda libre</label>
                        <input
                            id="f-q"
                            type="search"
                            name="q"
                            value="{{ $filters['q'] ?? '' }}"
                            maxlength="200"
                            placeholder="Id, dictamen o enfermedad… (decomisos ya registrados)"
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
                        <a class="btn-clear" href="/decomisos-de-lenguas">Limpiar</a>
                    </div>
                </div>
            </form>

            @if (empty($error) && ($errors->has('q') || $errors->has('fecha_desde') || $errors->has('fecha_hasta')))
                <p class="alert" role="alert">Revise los filtros marcados abajo.</p>
            @endif

            @if (count($rows) === 0)
                @php
                    $qBusqueda = trim((string) ($filters['q'] ?? ''));
                @endphp
                @if ($qBusqueda !== '')
                    <p class="muted">
                        Ningún decomiso coincide con «{{ $qBusqueda }}».
                        La búsqueda libre filtra decomisos ya registrados (SIRT/manual), no el inventario.
                        Para decomisar un código disponible, úselo en <strong>Id producto</strong> arriba (sugerencias al escribir).
                    </p>
                @else
                    <p class="muted">No hay filas para los filtros indicados (máx. 500 por consulta).</p>
                @endif
            @else
                <div class="listado-meta">
                    <p class="muted">{{ count($rows) }} registro(s).</p>
                    <button
                        class="btn-excel"
                        type="submit"
                        form="filtros-decomisos"
                        formaction="/decomisos-de-lenguas/excel"
                        formmethod="get"
                    >Descargar Excel</button>
                </div>
                <div class="table-wrap">
                    <table class="data">
                        <thead>
                            <tr>
                                <th>Id producto</th>
                                <th title="Fecha de beneficio (Animal Insensibilizado / ref. turno)">Fecha de beneficio</th>
                                <th>Fecha registro</th>
                                <th>Hora registro</th>
                                <th>Nombre dictamen</th>
                                <th>Nombre enfermedad</th>
                                <th>Origen</th>
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
                                    $fechaBeneficio = $r['fecha_beneficio'] ?? $r['fecha_ingreso'] ?? null;
                                    if ($fechaBeneficio instanceof \DateTimeInterface) {
                                        $fechaBeneficioTxt = $fechaBeneficio->format('Y-m-d');
                                    } else {
                                        $fechaBeneficioTxt = $fechaBeneficio !== null && $fechaBeneficio !== '' ? (string) $fechaBeneficio : '—';
                                    }
                                    $origen = strtolower((string) ($r['origen'] ?? 'sirt'));
                                    $esManual = $origen === 'manual';
                                @endphp
                                <tr>
                                    <td>{{ $idProd !== '' ? $idProd : '—' }}</td>
                                    <td>{{ $fechaBeneficioTxt }}</td>
                                    <td>{{ $fechaTxt }}</td>
                                    <td>{{ $horaTxt }}</td>
                                    <td>{{ $r['nombre_dictamen'] ?? '—' }}</td>
                                    <td>{{ $r['nombre_enfermedad'] ?? '—' }}</td>
                                    <td>
                                        <span class="badge-origen {{ $esManual ? 'badge-origen--manual' : 'badge-origen--sirt' }}">
                                            {{ $esManual ? 'Manual' : 'SIRT' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
        @if (! empty($puedeRegistrarDecomisoManual))
        <script>
            (function () {
                const input = document.getElementById('m-id-producto');
                const list = document.getElementById('m-id-producto-suggest');
                if (!input || !list || typeof window.fetch !== 'function') {
                    return;
                }
                // Siempre same-origin (evita APP_URL=127.0.0.1 u otra IP en el server).
                const suggestUrl = new URL(
                    '/decomisos-de-lenguas/sugerencias-producto',
                    window.location.origin,
                ).pathname;
                let timer = null;
                let activeIdx = -1;
                let items = [];
                let reqSeq = 0;

                function closeList() {
                    list.classList.remove('is-open');
                    list.setAttribute('aria-hidden', 'true');
                    list.innerHTML = '';
                    activeIdx = -1;
                    items = [];
                    input.setAttribute('aria-expanded', 'false');
                }

                function openList() {
                    if (!list.children.length) {
                        return;
                    }
                    list.classList.add('is-open');
                    list.setAttribute('aria-hidden', 'false');
                    input.setAttribute('aria-expanded', 'true');
                }

                function renderEmpty(msg) {
                    list.innerHTML = '';
                    const li = document.createElement('li');
                    const span = document.createElement('span');
                    span.className = 'suggest-meta';
                    span.style.display = 'block';
                    span.style.padding = '0.5rem 0.7rem';
                    span.textContent = msg;
                    li.appendChild(span);
                    list.appendChild(li);
                    openList();
                    activeIdx = -1;
                }

                function render() {
                    list.innerHTML = '';
                    if (!items.length) {
                        renderEmpty('Sin coincidencias en inventario disponible.');
                        return;
                    }
                    items.forEach(function (item, idx) {
                        const li = document.createElement('li');
                        li.setAttribute('role', 'option');
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.id = 'm-sug-' + idx;
                        const code = document.createElement('span');
                        code.className = 'suggest-code';
                        code.textContent = item.id_producto || '';
                        btn.appendChild(code);
                        const metaBits = [];
                        if (item.fecha_beneficio) {
                            metaBits.push('Beneficio ' + item.fecha_beneficio);
                        }
                        if (item.propietario) {
                            metaBits.push(item.propietario);
                        }
                        if (item.destino) {
                            metaBits.push(item.destino);
                        }
                        if (metaBits.length) {
                            const meta = document.createElement('span');
                            meta.className = 'suggest-meta';
                            meta.textContent = metaBits.join(' · ');
                            btn.appendChild(meta);
                        }
                        btn.addEventListener('mousedown', function (ev) {
                            ev.preventDefault();
                            pick(item);
                        });
                        li.appendChild(btn);
                        list.appendChild(li);
                    });
                    activeIdx = 0;
                    highlight();
                    openList();
                }

                function highlight() {
                    const buttons = list.querySelectorAll('button');
                    buttons.forEach(function (btn, i) {
                        btn.classList.toggle('is-active', i === activeIdx);
                    });
                }

                function pick(item) {
                    input.value = item.id_producto || '';
                    closeList();
                    input.focus();
                }

                function fetchSuggestions(q) {
                    const seq = ++reqSeq;
                    const url = new URL(suggestUrl, window.location.origin);
                    url.searchParams.set('q', q);
                    fetch(url.toString(), {
                        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin',
                    })
                        .then(function (res) {
                            if (!res.ok) {
                                throw new Error('suggest');
                            }
                            return res.json();
                        })
                        .then(function (data) {
                            if (seq !== reqSeq) {
                                return;
                            }
                            items = Array.isArray(data.items) ? data.items : [];
                            render();
                        })
                        .catch(function () {
                            if (seq === reqSeq) {
                                items = [];
                                renderEmpty(
                                    'No se pudo buscar (sesión o ruta). En el server: git pull + php artisan route:clear && view:clear.',
                                );
                            }
                        });
                }

                input.addEventListener('input', function () {
                    const q = String(input.value || '').trim();
                    if (timer) {
                        clearTimeout(timer);
                    }
                    if (q.length < 2) {
                        closeList();
                        return;
                    }
                    timer = setTimeout(function () {
                        fetchSuggestions(q);
                    }, 220);
                });

                input.addEventListener('keydown', function (ev) {
                    if (!list.classList.contains('is-open') || !items.length) {
                        return;
                    }
                    if (ev.key === 'ArrowDown') {
                        ev.preventDefault();
                        activeIdx = (activeIdx + 1) % items.length;
                        highlight();
                    } else if (ev.key === 'ArrowUp') {
                        ev.preventDefault();
                        activeIdx = (activeIdx - 1 + items.length) % items.length;
                        highlight();
                    } else if (ev.key === 'Enter' && activeIdx >= 0 && activeIdx < items.length) {
                        ev.preventDefault();
                        pick(items[activeIdx]);
                    } else if (ev.key === 'Escape') {
                        closeList();
                    }
                });

                input.addEventListener('blur', function () {
                    setTimeout(closeList, 140);
                });
            })();
        </script>
        @endif
    </body>
</html>
