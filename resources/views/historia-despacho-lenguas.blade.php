<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @include('partials.favicon')
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Historial de despacho de lenguas — {{ config('app.name') }}</title>
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
                max-width: 18rem;
                white-space: normal;
                word-break: break-word;
                overflow-wrap: anywhere;
            }
            .btn-detalle {
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
            }
            .btn-detalle:hover {
                filter: brightness(1.06);
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
            .hist-backdrop {
                position: fixed;
                inset: 0;
                z-index: 1000;
                display: none;
                align-items: center;
                justify-content: center;
                padding: 1rem;
                background: rgba(0, 0, 0, 0.65);
                backdrop-filter: blur(4px);
            }
            .hist-backdrop.is-open {
                display: flex;
            }
            .hist-dialog {
                width: 100%;
                max-width: 36rem;
                max-height: min(85vh, 28rem);
                display: flex;
                flex-direction: column;
                border-radius: 1rem;
                border: 1px solid color-mix(in srgb, var(--brand-green) 40%, transparent);
                background: rgba(6, 18, 14, 0.96);
                box-shadow: 0 24px 48px rgba(0, 0, 0, 0.45);
            }
            .hist-dialog__head {
                padding: 0.85rem 1rem;
                border-bottom: 1px solid color-mix(in srgb, var(--brand-green) 25%, transparent);
            }
            .hist-dialog__head h2 {
                margin: 0;
                font-size: 0.95rem;
                font-weight: 700;
            }
            .hist-dialog__body {
                flex: 1;
                overflow: auto;
                padding: 0.75rem 1rem 1rem;
                font-size: 0.85rem;
                line-height: 1.5;
            }
            .hist-dialog__body .hist-detalle-table {
                width: 100%;
                border-collapse: collapse;
                font-size: 0.78rem;
            }
            .hist-dialog__body .hist-detalle-table th,
            .hist-dialog__body .hist-detalle-table td {
                border: 1px solid color-mix(in srgb, var(--brand-green) 28%, rgba(0, 0, 0, 0.5));
                padding: 0.35rem 0.45rem;
                text-align: left;
                vertical-align: top;
                white-space: normal;
                word-break: break-word;
                overflow-wrap: anywhere;
            }
            .hist-dialog__body .hist-detalle-table th {
                background: color-mix(in srgb, var(--brand-green) 18%, transparent);
                font-weight: 800;
                text-transform: uppercase;
                letter-spacing: 0.03em;
                font-size: 0.62rem;
            }
            .hist-dialog__body .hist-detalle-table th:first-child,
            .hist-dialog__body .hist-detalle-table td:first-child {
                width: 0.01%;
                white-space: nowrap;
                vertical-align: middle;
                word-break: normal;
                overflow-wrap: normal;
            }
            .hist-dialog__body ul {
                margin: 0.5rem 0 0;
                padding-left: 1.15rem;
            }
            .hist-dialog__foot {
                padding: 0.65rem 1rem;
                border-top: 1px solid color-mix(in srgb, var(--brand-green) 25%, transparent);
                display: flex;
                justify-content: flex-end;
            }
            .btn-close-hist {
                padding: 0.45rem 1rem;
                border-radius: 9999px;
                border: 1px solid color-mix(in srgb, var(--brand-rose) 45%, transparent);
                background: color-mix(in srgb, var(--brand-rose) 10%, transparent);
                color: var(--brand-rose);
                font-weight: 600;
                font-size: 0.85rem;
                cursor: pointer;
                font-family: inherit;
            }
            .hist-msg-err {
                color: #ffb4b4;
                font-weight: 600;
            }
        </style>
    </head>
    <body>
        @include('partials.logo-institucional')
        <header class="page-bar">
            <div>
                <h1>Historial de despacho de lenguas</h1>
                <p class="sub">
                    Registro de despachos finalizados: fecha, empresa, conductor, placa, cantidad de lenguas y usuario.
                    Use <strong>Ver detalle</strong> para ver id de producto, propietario y destino de cada lengua del
                    despacho.
                </p>
            </div>
            <a class="link-menu" href="{{ route('menu') }}">← Menú</a>
        </header>

        <section class="sheet">
            @if ($despachos->isEmpty())
                <p class="muted">
                    Aún no hay despachos registrados en el historial. Los registros aparecen al usar
                    <strong>Terminar despacho</strong> en el módulo de despacho (desde la última actualización del
                    sistema).
                </p>
            @else
                <p class="muted">
                    Se muestran <strong>{{ $despachos->total() }}</strong> despacho(s) en total. Página
                    {{ $despachos->currentPage() }} de {{ $despachos->lastPage() }}.
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
                                        <button
                                            type="button"
                                            class="btn-detalle"
                                            data-detalle-url="{{ route('historia.despacho.lenguas.detalle', $d) }}"
                                        >
                                            Ver detalle
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($despachos->hasPages())
                    <nav class="pagination-wrap" aria-label="Paginación del historial">
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

        <div class="hist-backdrop" id="hist-backdrop" role="presentation" aria-hidden="true" hidden>
            <div class="hist-dialog" role="dialog" aria-modal="true" aria-labelledby="hist-dialog-title" id="hist-dialog">
                <div class="hist-dialog__head">
                    <h2 id="hist-dialog-title">Detalle del despacho</h2>
                </div>
                <div class="hist-dialog__body" id="hist-dialog-body">
                    <p class="muted" style="margin: 0">Cargando…</p>
                </div>
                <div class="hist-dialog__foot">
                    <button type="button" class="btn-close-hist" id="hist-dialog-close">Cerrar</button>
                </div>
            </div>
        </div>

        <script>
            (function () {
                var backdrop = document.getElementById('hist-backdrop');
                var bodyEl = document.getElementById('hist-dialog-body');
                var closeBtn = document.getElementById('hist-dialog-close');
                var dialog = document.getElementById('hist-dialog');

                function openModal() {
                    backdrop.hidden = false;
                    backdrop.removeAttribute('hidden');
                    backdrop.classList.add('is-open');
                    backdrop.setAttribute('aria-hidden', 'false');
                    document.body.style.overflow = 'hidden';
                }

                function closeModal() {
                    backdrop.classList.remove('is-open');
                    backdrop.hidden = true;
                    backdrop.setAttribute('hidden', 'hidden');
                    backdrop.setAttribute('aria-hidden', 'true');
                    document.body.style.overflow = '';
                }

                function esc(s) {
                    var d = document.createElement('div');
                    d.textContent = s == null ? '' : String(s);
                    return d.innerHTML;
                }

                document.querySelectorAll('.btn-detalle').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        var url = btn.getAttribute('data-detalle-url');
                        if (!url || !bodyEl) return;
                        bodyEl.innerHTML = '<p class="muted" style="margin:0">Cargando…</p>';
                        openModal();
                        fetch(url, {
                            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                            credentials: 'same-origin',
                        })
                            .then(function (r) {
                                return r.json();
                            })
                            .then(function (json) {
                                if (!json.ok) {
                                    bodyEl.innerHTML =
                                        '<p class="hist-msg-err">' + esc(json.message || 'Error al cargar.') + '</p>';
                                    return;
                                }
                                var lineas = json.lineas || [];
                                if (!lineas.length) {
                                    bodyEl.innerHTML =
                                        '<p class="muted" style="margin:0">Sin líneas asociadas a este despacho.</p>';
                                    return;
                                }
                                function celdaMostrar(v) {
                                    if (v == null) return '—';
                                    var t = String(v).trim();
                                    return t === '' ? '—' : t;
                                }
                                var wrap = document.createElement('div');
                                wrap.style.overflowX = 'auto';
                                var table = document.createElement('table');
                                table.className = 'hist-detalle-table';
                                table.setAttribute('aria-label', 'Líneas del despacho');
                                var thead = document.createElement('thead');
                                var thr = document.createElement('tr');
                                ['Id producto', 'Propietario', 'Destino'].forEach(function (label) {
                                    var th = document.createElement('th');
                                    th.scope = 'col';
                                    th.textContent = label;
                                    thr.appendChild(th);
                                });
                                thead.appendChild(thr);
                                var tbody = document.createElement('tbody');
                                lineas.forEach(function (row) {
                                    var tr = document.createElement('tr');
                                    ['id_producto', 'propietario', 'destino'].forEach(function (key) {
                                        var td = document.createElement('td');
                                        td.textContent = celdaMostrar(row[key]);
                                        tr.appendChild(td);
                                    });
                                    tbody.appendChild(tr);
                                });
                                table.appendChild(thead);
                                table.appendChild(tbody);
                                wrap.appendChild(table);
                                bodyEl.innerHTML = '';
                                var intro = document.createElement('p');
                                intro.className = 'muted';
                                intro.style.margin = '0 0 0.5rem';
                                intro.textContent = lineas.length + ' lengua(s) en este despacho.';
                                bodyEl.appendChild(intro);
                                bodyEl.appendChild(wrap);
                            })
                            .catch(function () {
                                bodyEl.innerHTML =
                                    '<p class="hist-msg-err">No se pudo cargar el detalle. Revise la sesión o la red.</p>';
                            });
                    });
                });

                if (closeBtn) closeBtn.addEventListener('click', closeModal);
                if (backdrop) {
                    backdrop.addEventListener('click', function (e) {
                        if (e.target === backdrop) closeModal();
                    });
                }
                if (dialog) {
                    dialog.addEventListener('click', function (e) {
                        e.stopPropagation();
                    });
                }
                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape' && backdrop && backdrop.classList.contains('is-open')) {
                        closeModal();
                    }
                });
            })();
        </script>
    </body>
</html>
