<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Inventario de Lenguas — {{ config('app.name') }}</title>
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
                align-items: center;
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
                max-width: 52rem;
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
            .sheet {
                max-width: 90rem;
                margin: 0 auto;
                background: var(--welcome-surface);
                padding: 1.25rem 1.35rem 1.5rem;
                border-radius: 1.25rem;
                border: 1px solid color-mix(in srgb, var(--brand-green) 35%, transparent);
            }
            .toolbar {
                display: flex;
                flex-wrap: wrap;
                align-items: flex-start;
                justify-content: space-between;
                gap: 0.75rem 1rem;
                margin-bottom: 1rem;
                padding-bottom: 1rem;
                border-bottom: 1px solid color-mix(in srgb, var(--brand-green) 22%, transparent);
            }
            .toolbar p {
                margin: 0;
                font-size: 0.78rem;
                font-weight: 600;
                letter-spacing: 0.04em;
                text-transform: uppercase;
                color: color-mix(in srgb, var(--brand-rose) 92%, #fff);
            }
            .sync-status {
                text-align: right;
                max-width: min(100%, 26rem);
            }
            .sync-status p {
                margin: 0;
                font-size: 0.78rem;
                line-height: 1.45;
                color: color-mix(in srgb, var(--text) 72%, transparent);
                font-weight: 500;
                text-transform: none;
                letter-spacing: normal;
            }
            .sync-status p + p {
                margin-top: 0.25rem;
                font-size: 0.72rem;
                color: color-mix(in srgb, var(--text) 58%, transparent);
            }
            .alert {
                margin: 0 0 1rem;
                padding: 0.75rem 0.9rem;
                border-radius: 0.65rem;
                font-size: 0.88rem;
                font-weight: 600;
            }
            .alert--ok {
                border: 1px solid color-mix(in srgb, var(--brand-green) 45%, transparent);
                background: color-mix(in srgb, var(--brand-green) 12%, rgba(0, 0, 0, 0.35));
                color: var(--text);
            }
            .alert--err {
                border: 1px solid color-mix(in srgb, #ffb4b4 55%, transparent);
                background: color-mix(in srgb, #5c1a1a 55%, rgba(0, 0, 0, 0.35));
                color: #ffe4e4;
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
            table.data col.col-id-producto-col {
                width: 0;
            }
            table.data th.col-id-producto,
            table.data td.col-id-producto {
                white-space: nowrap;
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
            .filters-grid {
                display: grid;
                gap: 0.75rem 1rem;
                grid-template-columns: 1fr;
            }
            @media (min-width: 640px) {
                .filters-grid {
                    grid-template-columns: repeat(2, 1fr);
                }
            }
            @media (min-width: 960px) {
                .filters-grid {
                    grid-template-columns: repeat(4, 1fr);
                }
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
            }
            .filter-actions {
                display: flex;
                flex-wrap: wrap;
                gap: 0.65rem;
                align-items: center;
                margin-top: 1rem;
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
        <header class="page-bar">
            <div>
                <h1>Inventario de Lenguas</h1>
                <p class="sub">
                    <strong>Propósito:</strong> disponer de una réplica institucional del <strong>registro de ingresos de
                    lenguas</strong> a planta, alineada a la trazabilidad corporativa y a los mismos criterios de faena
                    que el módulo de ingresos (turno y fecha de plan). <strong>Alcance:</strong> las consultas y filtros
                    operan exclusivamente sobre la base local; la integración con el origen (SIRT) se ejecuta de forma
                    programada mientras esta vista permanezca abierta. <strong>Presentación:</strong> hasta 2.000
                    registros, con indicadores de vigencia operativa (fecha de vencimiento y proximidad respecto del
                    día de registro).
                </p>
            </div>
            <a class="link-menu" href="{{ route('menu') }}">← Menú</a>
        </header>

        <section
            id="inv-sheet"
            class="sheet"
            aria-labelledby="inv-tabla"
            data-import-url="{{ route('inventario.lenguas.importar_hoy') }}"
            data-sync-interval="{{ (int) $inventarioSyncIntervalSeconds }}"
        >
            <div class="toolbar">
                <p id="inv-tabla">Réplica local — visor (máx. 2.000 registros)</p>
                <div class="sync-status" id="inv-sync-status" aria-live="polite">
                    <p id="inv-sync-line1">Actualizando la réplica conforme al origen corporativo…</p>
                    <p id="inv-sync-line2">
                        @if ($inventarioSyncIntervalSeconds === 0)
                            La importación del día de operación se ejecuta al cargar o al refrescar esta página
                            (intervalo de repetición desactivado en configuración).
                        @else
                            @php
                                $sec = (int) $inventarioSyncIntervalSeconds;
                                $cada =
                                    $sec < 60
                                        ? $sec . ' s'
                                        : ($sec % 60 === 0
                                            ? ((int) ($sec / 60)) . ' min'
                                            : $sec . ' s');
                            @endphp
                            Ciclo de actualización automática cada {{ $cada }} con sesión activa en esta vista
                            (parámetro de entorno).
                        @endif
                    </p>
                </div>
            </div>

            <form
                id="inv-filters"
                class="filters"
                method="get"
                action="{{ route('inventario.lenguas') }}"
                aria-label="Criterios de consulta del inventario de lenguas"
            >
                <div class="filters-grid">
                    <div class="filter-field">
                        <label for="inv-fecha-desde">Fecha desde (ref. turno)</label>
                        <input
                            id="inv-fecha-desde"
                            type="date"
                            name="fecha_desde"
                            value="{{ $filters['fecha_desde'] ?? '' }}"
                        />
                        @error('fecha_desde')
                            <p class="err">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="filter-field">
                        <label for="inv-fecha-hasta">Fecha hasta (ref. turno)</label>
                        <input
                            id="inv-fecha-hasta"
                            type="date"
                            name="fecha_hasta"
                            value="{{ $filters['fecha_hasta'] ?? '' }}"
                        />
                        @error('fecha_hasta')
                            <p class="err">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="filter-field">
                        <label for="inv-id-producto">Id producto</label>
                        <input
                            id="inv-id-producto"
                            type="text"
                            name="id_producto"
                            value="{{ $filters['id_producto'] ?? '' }}"
                            maxlength="80"
                            placeholder="Código"
                            autocomplete="off"
                        />
                        @error('id_producto')
                            <p class="err">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="filter-field">
                        <label for="inv-propietario">Propietario</label>
                        <input
                            id="inv-propietario"
                            type="text"
                            name="propietario"
                            value="{{ $filters['propietario'] ?? '' }}"
                            maxlength="200"
                            placeholder="Nombre"
                            autocomplete="off"
                        />
                        @error('propietario')
                            <p class="err">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="filter-actions">
                    <button class="btn-apply" type="submit">Aplicar</button>
                    @php
                        $soloFechasInv = array_filter(
                            [
                                'fecha_desde' => $filters['fecha_desde'] ?? '',
                                'fecha_hasta' => $filters['fecha_hasta'] ?? '',
                            ],
                            static fn ($v) => $v !== null && $v !== '',
                        );
                    @endphp
                    <a class="btn-clear" href="{{ route('inventario.lenguas', $soloFechasInv) }}">Limpiar</a>
                    <a
                        class="btn-clear"
                        href="{{ route('inventario.lenguas', ['fecha_desde' => $hoyOperacion, 'fecha_hasta' => $hoyOperacion]) }}"
                    >Solo hoy (ref. turno)</a>
                </div>
            </form>

            @if ($errors->any())
                <p class="alert alert--err" role="alert">
                    Los criterios de consulta no cumplen las reglas de validación; corrija los campos señalados.
                </p>
            @endif

            <div id="inv-listado">
                @include('inventario-lenguas._listado', [
                    'rows' => $rows,
                    'importMessage' => session('import_message'),
                    'importError' => session('import_error'),
                    'filtrosActivos' => $filtrosActivos,
                    'listadoEmptyHint' => $listadoEmptyHint,
                ])
            </div>
        </section>
        <script>
            (function () {
                const sheet = document.getElementById('inv-sheet');
                const target = document.getElementById('inv-listado');
                const line1 = document.getElementById('inv-sync-line1');
                if (!sheet || !target || typeof window.fetch !== 'function') {
                    if (line1) {
                        line1.textContent =
                            'Para la actualización automática de la réplica se requiere JavaScript activo; en caso contrario, recargue la página tras completar una importación manual.';
                    }
                    return;
                }

                const importUrl = sheet.getAttribute('data-import-url') || '';
                const intervalSec = parseInt(sheet.getAttribute('data-sync-interval') || '0', 10);
                const intervalMs = intervalSec > 0 ? intervalSec * 1000 : 0;

                let syncInFlight = false;

                function csrfToken() {
                    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                }

                function formatClock(d) {
                    const pad = (n) => (n < 10 ? '0' + n : '' + n);
                    return pad(d.getHours()) + ':' + pad(d.getMinutes()) + ':' + pad(d.getSeconds());
                }

                function setLine1(text) {
                    if (line1) {
                        line1.textContent = text;
                    }
                }

                async function runSync() {
                    if (!importUrl || syncInFlight) {
                        return;
                    }
                    syncInFlight = true;
                    setLine1('Ejecutando actualización de la réplica local…');
                    target.querySelector('.alert--err.js-ajax-error')?.remove();
                    const body = new FormData();
                    body.append('_token', csrfToken());
                    const filterForm = document.getElementById('inv-filters');
                    if (filterForm) {
                        ['fecha_desde', 'fecha_hasta', 'id_producto', 'propietario'].forEach(function (name) {
                            const el = filterForm.querySelector('[name="' + name + '"]');
                            body.append(name, el ? el.value : '');
                        });
                    }
                    try {
                        const res = await fetch(importUrl, {
                            method: 'POST',
                            headers: {
                                Accept: 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body,
                            credentials: 'same-origin',
                        });
                        const ct = res.headers.get('content-type') || '';
                        if (!ct.includes('application/json')) {
                            throw new Error('Respuesta no JSON');
                        }
                        const data = await res.json();
                        if (typeof data.html === 'string') {
                            target.innerHTML = data.html;
                        }
                        const t = formatClock(new Date());
                        if (data.ok) {
                            setLine1('Última actualización de réplica correcta (' + t + ').');
                        } else {
                            setLine1(
                                'Último ciclo de actualización (' +
                                    t +
                                    '): no conforme; revise el mensaje en pantalla o la conectividad con el origen.',
                            );
                        }
                    } catch (err) {
                        const p = document.createElement('p');
                        p.className = 'alert alert--err js-ajax-error';
                        p.setAttribute('role', 'alert');
                        p.textContent =
                            'No fue posible completar la operación (comunicación con el servidor o sesión caducada). Recargue la página y, si persiste, contacte a soporte.';
                        target.insertBefore(p, target.firstChild);
                        setLine1('Interrupción del servicio o sesión (' + formatClock(new Date()) + ').');
                    } finally {
                        syncInFlight = false;
                    }
                }

                runSync();
                if (intervalMs > 0) {
                    setInterval(function () {
                        if (document.visibilityState === 'visible') {
                            runSync();
                        }
                    }, intervalMs);
                }
            })();
        </script>
    </body>
</html>
