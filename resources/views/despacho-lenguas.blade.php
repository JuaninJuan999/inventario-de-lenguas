<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Despacho de Lenguas — {{ config('app.name') }}</title>
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
                max-width: 56rem;
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
                color: var(--text);
                text-shadow:
                    0 0 28px color-mix(in srgb, var(--brand-green) 35%, transparent),
                    0 2px 8px rgba(0, 0, 0, 0.45);
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
                max-width: 56rem;
                margin: 0 auto;
                position: relative;
                background: var(--welcome-surface);
                padding: 1.5rem 1.5rem 2rem;
                border-radius: 1.25rem;
                border: 1px solid color-mix(in srgb, var(--brand-green) 35%, transparent);
                box-shadow:
                    0 0 0 1px color-mix(in srgb, var(--brand-rose) 12%, transparent) inset,
                    0 24px 56px rgba(0, 0, 0, 0.45),
                    0 0 60px color-mix(in srgb, var(--brand-green) 12%, transparent);
                backdrop-filter: blur(16px);
                -webkit-backdrop-filter: blur(16px);
            }
            /* Botón 3D con verde institucional */
            .btn-3d {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0.45rem 0.9rem;
                min-height: 2.25rem;
                font-size: 0.72rem;
                font-weight: 800;
                letter-spacing: 0.04em;
                text-transform: uppercase;
                color: #04261c;
                border: 1px solid color-mix(in srgb, var(--brand-green) 50%, #fff);
                border-radius: 6px;
                cursor: pointer;
                background: linear-gradient(145deg, var(--brand-green), color-mix(in srgb, var(--brand-green) 72%, var(--green-deep)));
                box-shadow:
                    inset 1px 1px 0 rgba(255, 255, 255, 0.45),
                    inset -1px -1px 0 rgba(0, 0, 0, 0.18),
                    0 3px 0 color-mix(in srgb, var(--green-deep) 85%, #000),
                    0 6px 20px color-mix(in srgb, var(--brand-green) 35%, transparent);
                white-space: nowrap;
            }
            .btn-3d:hover {
                filter: brightness(1.06);
            }
            .btn-3d:active {
                transform: translateY(2px);
                box-shadow:
                    inset 1px 1px 0 rgba(255, 255, 255, 0.35),
                    inset -1px -1px 0 rgba(0, 0, 0, 0.2),
                    0 1px 0 color-mix(in srgb, var(--green-deep) 85%, #000);
            }
            .btn-3d--finish {
                padding: 0.75rem 1.75rem;
                font-size: 0.85rem;
                margin-top: 0.25rem;
            }
            .label-fixed {
                width: 11.5rem;
                flex-shrink: 0;
            }
            .row-field {
                display: flex;
                align-items: stretch;
                gap: 0.65rem;
                margin-bottom: 0.75rem;
            }
            .row-field input[type="text"] {
                flex: 1;
                min-width: 0;
                padding: 0.45rem 0.65rem;
                border: 1px solid color-mix(in srgb, var(--brand-green) 40%, #1a3d2e);
                border-radius: 6px;
                background: rgba(0, 0, 0, 0.35);
                color: var(--text);
                font-size: 0.95rem;
            }
            .row-field input::placeholder {
                color: color-mix(in srgb, var(--text) 42%, transparent);
            }
            .row-field input:focus {
                outline: 2px solid var(--brand-green);
                outline-offset: 1px;
            }
            /* Tarjetas (todo lo que no es botón 3D) */
            .field-card {
                margin-bottom: 1rem;
                padding: 1rem 1.15rem 1.15rem;
                border-radius: 0.9rem;
                border: 1px solid color-mix(in srgb, var(--brand-green) 28%, transparent);
                background: rgba(0, 0, 0, 0.22);
                box-shadow: 0 0 0 1px color-mix(in srgb, var(--brand-rose) 8%, transparent) inset;
            }
            .field-card__title {
                margin: 0 0 0.65rem;
                font-size: 0.68rem;
                font-weight: 800;
                letter-spacing: 0.08em;
                text-transform: uppercase;
                color: var(--brand-rose);
            }
            .field-card__text {
                margin: 0 0 0.65rem;
                font-size: 0.88rem;
                line-height: 1.45;
                color: color-mix(in srgb, var(--text) 88%, transparent);
            }
            .field-card__text code {
                font-size: 0.86em;
                padding: 0.1rem 0.35rem;
                border-radius: 4px;
                background: rgba(0, 0, 0, 0.35);
                border: 1px solid color-mix(in srgb, var(--brand-green) 35%, transparent);
            }
            .field-card input[type="text"].field-card__input {
                width: 100%;
                padding: 0.5rem 0.7rem;
                border: 1px solid color-mix(in srgb, var(--brand-green) 40%, #1a3d2e);
                border-radius: 6px;
                background: rgba(0, 0, 0, 0.35);
                color: var(--text);
                font-size: 0.95rem;
            }
            .field-card input.field-card__input::placeholder {
                color: color-mix(in srgb, var(--text) 42%, transparent);
            }
            .field-card input.field-card__input:focus {
                outline: 2px solid var(--brand-green);
                outline-offset: 1px;
            }
            .table-block {
                margin-top: 0;
            }
            .table-head-row {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 0.75rem;
                margin-bottom: 0.65rem;
                flex-wrap: wrap;
            }
            .table-head-row .field-card__title {
                margin-bottom: 0;
            }
            .total-display {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
            }
            .total-label {
                font-size: 0.72rem;
                font-weight: 800;
                letter-spacing: 0.05em;
                text-transform: uppercase;
                color: color-mix(in srgb, var(--text) 85%, transparent);
            }
            .total-num {
                min-width: 2.5rem;
                padding: 0.35rem 0.6rem;
                border: 1px solid color-mix(in srgb, var(--brand-green) 45%, transparent);
                border-radius: 6px;
                background: rgba(0, 0, 0, 0.4);
                color: var(--brand-green);
                font-weight: 700;
                text-align: center;
                font-size: 0.95rem;
            }
            .dispatch-table {
                width: 100%;
                border-collapse: collapse;
                font-size: 0.88rem;
            }
            .dispatch-table th,
            .dispatch-table td {
                border: 1px solid color-mix(in srgb, var(--brand-green) 28%, rgba(0, 0, 0, 0.5));
                padding: 0.4rem 0.5rem;
                text-align: left;
            }
            .dispatch-table th {
                background: linear-gradient(
                    180deg,
                    color-mix(in srgb, var(--brand-green) 22%, transparent),
                    color-mix(in srgb, var(--brand-green) 10%, transparent)
                );
                font-weight: 800;
                text-transform: uppercase;
                letter-spacing: 0.03em;
                font-size: 0.7rem;
                color: var(--text);
            }
            .dispatch-table td input {
                width: 100%;
                border: none;
                background: transparent;
                padding: 0.2rem;
                font-size: inherit;
                color: var(--text);
            }
            .dispatch-table td input::placeholder {
                color: color-mix(in srgb, var(--text) 38%, transparent);
            }
            .dispatch-table td input:focus {
                outline: 1px solid var(--brand-green);
                outline-offset: 1px;
                background: color-mix(in srgb, var(--brand-green) 8%, transparent);
                border-radius: 4px;
            }
            .dispatch-table tbody tr:nth-child(even) {
                background: rgba(0, 0, 0, 0.18);
            }
            .footer-actions {
                display: flex;
                justify-content: flex-end;
                margin-top: 0.5rem;
            }
            button.btn-3d.label-fixed {
                font-family: inherit;
                cursor: pointer;
            }
            .lookup-backdrop {
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
            .lookup-backdrop.is-open {
                display: flex;
            }
            .lookup-dialog {
                width: 100%;
                max-width: 42rem;
                max-height: min(85vh, 36rem);
                display: flex;
                flex-direction: column;
                border-radius: 1rem;
                border: 1px solid color-mix(in srgb, var(--brand-green) 40%, transparent);
                background: var(--welcome-surface);
                box-shadow:
                    0 0 0 1px color-mix(in srgb, var(--brand-rose) 10%, transparent) inset,
                    0 24px 64px rgba(0, 0, 0, 0.55);
            }
            .lookup-dialog__head {
                padding: 1rem 1.15rem;
                border-bottom: 1px solid color-mix(in srgb, var(--brand-green) 25%, transparent);
            }
            .lookup-dialog__head h2 {
                margin: 0 0 0.5rem;
                font-size: 0.95rem;
                font-weight: 700;
                color: var(--text);
            }
            .lookup-dialog__search {
                width: 100%;
                padding: 0.5rem 0.65rem;
                border-radius: 6px;
                border: 1px solid color-mix(in srgb, var(--brand-green) 40%, #1a3d2e);
                background: rgba(0, 0, 0, 0.35);
                color: var(--text);
                font-size: 0.9rem;
            }
            .lookup-dialog__search:focus {
                outline: 2px solid var(--brand-green);
                outline-offset: 1px;
            }
            .lookup-dialog__body {
                flex: 1;
                overflow: auto;
                padding: 0.5rem 0.75rem 1rem;
            }
            .lookup-msg {
                margin: 0.75rem 0;
                font-size: 0.85rem;
                color: color-mix(in srgb, var(--brand-rose) 90%, #fff);
            }
            .lookup-msg--muted {
                color: color-mix(in srgb, var(--text) 65%, transparent);
            }
            .lookup-table {
                width: 100%;
                border-collapse: collapse;
                font-size: 0.78rem;
            }
            .lookup-table th,
            .lookup-table td {
                border: 1px solid color-mix(in srgb, var(--brand-green) 22%, transparent);
                padding: 0.35rem 0.45rem;
                text-align: left;
            }
            .lookup-table th {
                position: sticky;
                top: 0;
                background: rgba(6, 18, 14, 0.95);
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.03em;
                font-size: 0.65rem;
                color: var(--brand-green);
            }
            .lookup-table tbody tr {
                cursor: pointer;
            }
            .lookup-table tbody tr:hover {
                background: color-mix(in srgb, var(--brand-green) 14%, transparent);
            }
            .lookup-dialog__foot {
                padding: 0.65rem 1rem;
                border-top: 1px solid color-mix(in srgb, var(--brand-green) 25%, transparent);
                display: flex;
                justify-content: flex-end;
                gap: 0.5rem;
            }
            .btn-close-modal {
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
            .btn-close-modal:hover {
                background: color-mix(in srgb, var(--brand-rose) 18%, transparent);
            }
            @media (max-width: 640px) {
                .label-fixed {
                    width: 100%;
                }
                .row-field {
                    flex-direction: column;
                    align-items: stretch;
                }
            }
        </style>
    </head>
    <body>
        <div class="page-bar">
            <h1>Despacho de Lenguas</h1>
            <a class="link-menu" href="{{ route('menu') }}">← Volver al menú</a>
        </div>

        <div class="sheet">
            <form id="form-despacho" action="#" method="post" onsubmit="return false;">
                @csrf

                <div class="row-field">
                    <button type="button" class="btn-3d label-fixed" data-open-lookup="empresa" aria-haspopup="dialog">
                        Empresa
                    </button>
                    <input
                        type="text"
                        name="empresa"
                        id="empresa"
                        autocomplete="organization"
                        placeholder="Nombre o razón social"
                    >
                </div>

                <div class="row-field">
                    <button type="button" class="btn-3d label-fixed" data-open-lookup="placa" aria-haspopup="dialog">
                        Placa vehiculo
                    </button>
                    <input type="text" name="placa" id="placa" autocomplete="off" placeholder="">
                </div>
                <div class="row-field">
                    <button type="button" class="btn-3d label-fixed" data-open-lookup="conductor" aria-haspopup="dialog">
                        Conductor
                    </button>
                    <input type="text" name="conductor" id="conductor" autocomplete="name" placeholder="">
                </div>

                <div class="field-card">
                    <h2 class="field-card__title">Código de lengua</h2>
                    <p class="field-card__text">
                        Escanee o escriba el código y pulse <strong>Enter</strong>.
                    </p>
                    <input
                        class="field-card__input"
                        type="text"
                        id="codigo-lengua"
                        autocomplete="off"
                        placeholder="Escanee o escriba el código"
                    >
                </div>

                <div class="field-card table-block">
                    <div class="table-head-row">
                        <h2 class="field-card__title">Detalle del despacho</h2>
                        <div class="total-display">
                            <span class="total-label">Total lenguas</span>
                            <span class="total-num" id="total-lenguas" aria-live="polite">0</span>
                        </div>
                    </div>

                    <table class="dispatch-table" aria-label="Lenguas en despacho">
                        <thead>
                            <tr>
                                <th scope="col">Código</th>
                                <th scope="col">Destino</th>
                                <th scope="col">Fecha despacho</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-despacho"></tbody>
                    </table>
                </div>

                <div class="footer-actions">
                    <button type="button" class="btn-3d btn-3d--finish" id="btn-terminar">Terminar despacho</button>
                </div>
            </form>
        </div>

        <div class="lookup-backdrop" id="lookup-backdrop" role="presentation" aria-hidden="true" hidden>
            <div
                class="lookup-dialog"
                role="dialog"
                aria-modal="true"
                aria-labelledby="lookup-dialog-title"
                id="lookup-dialog"
            >
                <div class="lookup-dialog__head">
                    <h2 id="lookup-dialog-title">Buscar registro</h2>
                    <input
                        type="search"
                        class="lookup-dialog__search"
                        id="lookup-search"
                        placeholder="Filtrar por placa, conductor o empresa…"
                        autocomplete="off"
                    >
                </div>
                <div class="lookup-dialog__body">
                    <p class="lookup-msg lookup-msg--muted" id="lookup-status">Escriba para filtrar o espere la carga.</p>
                    <div style="overflow-x: auto;">
                        <table class="lookup-table" id="lookup-table" aria-label="Resultados">
                            <thead id="lookup-thead"></thead>
                            <tbody id="lookup-tbody"></tbody>
                        </table>
                    </div>
                </div>
                <div class="lookup-dialog__foot">
                    <button type="button" class="btn-close-modal" id="lookup-close">Cerrar</button>
                </div>
            </div>
        </div>

        <script>
            window.despachoVehiculosUrl = @json(route('despacho.lookup.vehiculos'));
            window.despachoLenguaDestinoUrl = @json(route('despacho.lookup.lengua_destino'));
            window.despachoFinalizarInventarioUrl = @json(route('despacho.finalizar.inventario'));
        </script>
        <script>
            (function () {
                var lookupBackdrop = document.getElementById('lookup-backdrop');
                var lookupDialog = document.getElementById('lookup-dialog');
                var lookupTitle = document.getElementById('lookup-dialog-title');
                var lookupSearch = document.getElementById('lookup-search');
                var lookupStatus = document.getElementById('lookup-status');
                var lookupTbody = document.getElementById('lookup-tbody');
                var lookupClose = document.getElementById('lookup-close');
                var lookupTargetField = null;
                var debounceTimer = null;
                var lastController = null;
                var lastFiltroUserName = '';

                function esc(s) {
                    if (s == null || s === '') return '—';
                    var d = document.createElement('div');
                    d.textContent = String(s);
                    return d.innerHTML;
                }

                function setLookupThead(field) {
                    var thead = document.getElementById('lookup-thead');
                    if (!thead) return;
                    var labels = {
                        empresa: 'Empresa',
                        placa: 'Placa vehículo',
                        conductor: 'Conductor',
                    };
                    var th = labels[field] || 'Valor';
                    thead.innerHTML = '<tr><th>' + th + '</th></tr>';
                }

                function openLookup(field) {
                    lookupTargetField = field;
                    var titles = {
                        empresa: 'Buscar empresa',
                        placa: 'Buscar placa de vehículo',
                        conductor: 'Buscar conductor',
                    };
                    var placeholders = {
                        empresa: 'Escriba para filtrar por empresa…',
                        placa: 'Escriba para filtrar por placa…',
                        conductor: 'Escriba para filtrar por nombre de conductor…',
                    };
                    lookupTitle.textContent = titles[field] || 'Buscar';
                    lookupSearch.placeholder = placeholders[field] || '';
                    lookupSearch.value = '';
                    lookupTbody.innerHTML = '';
                    setLookupThead(field);
                    lookupStatus.textContent =
                        'Listado según la columna seleccionada. Escriba para acotar la búsqueda.';
                    lookupStatus.className = 'lookup-msg lookup-msg--muted';
                    lookupBackdrop.hidden = false;
                    lookupBackdrop.removeAttribute('hidden');
                    lookupBackdrop.setAttribute('aria-hidden', 'false');
                    lookupBackdrop.classList.add('is-open');
                    document.body.style.overflow = 'hidden';
                    setTimeout(function () {
                        lookupSearch.focus();
                    }, 50);
                    runLookup('');
                }

                function closeLookup() {
                    lookupBackdrop.classList.remove('is-open');
                    lookupBackdrop.hidden = true;
                    lookupBackdrop.setAttribute('hidden', 'hidden');
                    lookupBackdrop.setAttribute('aria-hidden', 'true');
                    document.body.style.overflow = '';
                    lookupTargetField = null;
                    if (lastController) {
                        lastController.abort();
                        lastController = null;
                    }
                }

                function applySelection(row) {
                    if (!lookupTargetField) return;
                    var inputId =
                        lookupTargetField === 'empresa'
                            ? 'empresa'
                            : lookupTargetField === 'placa'
                              ? 'placa'
                              : 'conductor';
                    var el = document.getElementById(inputId);
                    if (!el) return;
                    var val = '';
                    if (lookupTargetField === 'empresa') val = row.empresa || '';
                    if (lookupTargetField === 'placa') val = row.placa_vehiculo || '';
                    if (lookupTargetField === 'conductor') val = row.nombre_conductor || '';
                    el.value = val;
                    el.focus();
                    closeLookup();
                }

                function cellValueForFocus(row, field) {
                    if (field === 'empresa') return row.empresa;
                    if (field === 'placa') return row.placa_vehiculo;
                    return row.nombre_conductor;
                }

                function renderRows(rows) {
                    var field = lookupTargetField;
                    lookupTbody.innerHTML = '';
                    if (!field) return;
                    if (!rows.length) {
                        lookupStatus.textContent =
                            'Sin resultados. La consulta solo incluye filas con user_name = "' +
                            (lastFiltroUserName || '—') +
                            '" en vehiculo_asignado (como en pgAdmin). Si en su SQL usa otro valor, defina DESPACHO_VEHICULO_USER_NAME en .env.';
                        lookupStatus.className = 'lookup-msg';
                        return;
                    }
                    lookupStatus.textContent =
                        rows.length +
                        ' resultado(s). Pulse una fila para copiar ese valor al campo del formulario.';
                    lookupStatus.className = 'lookup-msg lookup-msg--muted';
                    rows.forEach(function (row) {
                        var tr = document.createElement('tr');
                        tr.setAttribute('role', 'button');
                        tr.tabIndex = 0;
                        tr.innerHTML = '<td>' + esc(cellValueForFocus(row, field)) + '</td>';
                        tr.addEventListener('click', function () {
                            applySelection(row);
                        });
                        tr.addEventListener('keydown', function (e) {
                            if (e.key === 'Enter' || e.key === ' ') {
                                e.preventDefault();
                                applySelection(row);
                            }
                        });
                        lookupTbody.appendChild(tr);
                    });
                }

                function runLookup(q) {
                    var focus = lookupTargetField;
                    if (!focus) return;
                    var url =
                        window.despachoVehiculosUrl +
                        '?focus=' +
                        encodeURIComponent(focus) +
                        '&search=' +
                        encodeURIComponent(q);
                    if (lastController) lastController.abort();
                    lastController = new AbortController();
                    lookupStatus.textContent = 'Cargando…';
                    fetch(url, {
                        signal: lastController.signal,
                        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin',
                    })
                        .then(function (r) {
                            return r.json();
                        })
                        .then(function (json) {
                            if (!json.ok) {
                                lookupStatus.textContent = json.message || 'Error al consultar.';
                                lookupStatus.className = 'lookup-msg';
                                lookupTbody.innerHTML = '';
                                return;
                            }
                            lastFiltroUserName = json.filtro_user_name || '';
                            renderRows(json.data || []);
                        })
                        .catch(function (err) {
                            if (err.name === 'AbortError') return;
                            lookupStatus.textContent = 'Error de red o del servidor.';
                            lookupStatus.className = 'lookup-msg';
                            lookupTbody.innerHTML = '';
                        });
                }

                document.querySelectorAll('[data-open-lookup]').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        openLookup(btn.getAttribute('data-open-lookup'));
                    });
                });

                lookupSearch.addEventListener('input', function () {
                    var q = lookupSearch.value.trim();
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(function () {
                        if (!lookupTargetField) return;
                        runLookup(q);
                    }, 280);
                });

                lookupClose.addEventListener('click', closeLookup);
                lookupBackdrop.addEventListener('click', function (e) {
                    if (e.target === lookupBackdrop) closeLookup();
                });
                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape' && lookupBackdrop.classList.contains('is-open')) {
                        closeLookup();
                    }
                });
                if (lookupDialog) {
                    lookupDialog.addEventListener('click', function (e) {
                        e.stopPropagation();
                    });
                }
            })();
        </script>
        <script>
            (function () {
                var formDespacho = document.getElementById('form-despacho');
                var inputCodigo = document.getElementById('codigo-lengua');
                var tbody = document.getElementById('tbody-despacho');
                var totalEl = document.getElementById('total-lenguas');
                var btnTerminar = document.getElementById('btn-terminar');
                var codes = new Set();
                var agregarEnCurso = false;
                var finalizarEnCurso = false;

                function hoyTexto() {
                    var d = new Date();
                    var dd = String(d.getDate()).padStart(2, '0');
                    var mm = String(d.getMonth() + 1).padStart(2, '0');
                    var yyyy = d.getFullYear();
                    return dd + '/' + mm + '/' + yyyy;
                }

                function actualizarTotal() {
                    totalEl.textContent = String(tbody.querySelectorAll('tr').length);
                }

                async function agregarFila() {
                    if (agregarEnCurso) {
                        return;
                    }
                    var raw = (inputCodigo.value || '').trim();
                    if (!raw) {
                        alert('Ingrese el código de la lengua.');
                        inputCodigo.focus();
                        return;
                    }
                    var urlBase = window.despachoLenguaDestinoUrl || '';
                    if (!urlBase) {
                        alert('No está configurada la ruta de consulta de inventario.');
                        return;
                    }
                    agregarEnCurso = true;
                    inputCodigo.disabled = true;
                    var idNorm = raw;
                    var destinoTxt = '';
                    var encontrado = false;
                    try {
                        var res = await fetch(
                            urlBase + '?codigo=' + encodeURIComponent(raw),
                            {
                                headers: {
                                    Accept: 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                credentials: 'same-origin',
                            },
                        );
                        var ct = res.headers.get('content-type') || '';
                        if (!ct.includes('application/json')) {
                            throw new Error('Respuesta no JSON');
                        }
                        var json = await res.json();
                        if (!json.ok) {
                            alert(json.message || 'No se pudo validar el código.');
                            inputCodigo.focus();
                            return;
                        }
                        idNorm = (json.id_producto || raw).trim();
                        encontrado = !!json.encontrado;
                        destinoTxt =
                            json.destino != null && String(json.destino).trim() !== ''
                                ? String(json.destino).trim()
                                : '';
                    } catch (err) {
                        alert('No se pudo consultar el inventario local. Revise la sesión o la red.');
                        inputCodigo.focus();
                        return;
                    } finally {
                        inputCodigo.disabled = false;
                        agregarEnCurso = false;
                    }

                    if (!idNorm) {
                        inputCodigo.focus();
                        return;
                    }
                    if (codes.has(idNorm.toLowerCase())) {
                        alert('Ese id de producto ya está en la lista.');
                        inputCodigo.value = '';
                        inputCodigo.focus();
                        return;
                    }
                    codes.add(idNorm.toLowerCase());

                    var tr = document.createElement('tr');
                    var td1 = document.createElement('td');
                    var td2 = document.createElement('td');
                    var td3 = document.createElement('td');
                    var inCod = document.createElement('input');
                    inCod.type = 'text';
                    inCod.name = 'codigos[]';
                    inCod.className = 'inp-codigo-despacho';
                    inCod.value = idNorm;
                    inCod.readOnly = true;
                    if (raw !== idNorm) {
                        inCod.title = 'Leído: ' + raw;
                    }
                    var inDest = document.createElement('input');
                    inDest.type = 'text';
                    inDest.name = 'destinos[]';
                    inDest.placeholder = encontrado
                        ? destinoTxt
                            ? 'Destino'
                            : 'Sin destino registrado (editable)'
                        : 'Sin fila en inventario para este id (editable)';
                    if (destinoTxt) {
                        inDest.value = destinoTxt;
                    }
                    var inFecha = document.createElement('input');
                    inFecha.type = 'text';
                    inFecha.name = 'fechas[]';
                    inFecha.value = hoyTexto();
                    inFecha.readOnly = true;
                    td1.appendChild(inCod);
                    td2.appendChild(inDest);
                    td3.appendChild(inFecha);
                    tr.appendChild(td1);
                    tr.appendChild(td2);
                    tr.appendChild(td3);
                    tbody.appendChild(tr);
                    inputCodigo.value = '';
                    inputCodigo.focus();
                    actualizarTotal();
                }

                inputCodigo.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        void agregarFila();
                    }
                });

                btnTerminar.addEventListener('click', function () {
                    void terminarDespacho();
                });

                async function terminarDespacho() {
                    if (finalizarEnCurso) {
                        return;
                    }
                    var n = tbody.querySelectorAll('tr').length;
                    if (n === 0) {
                        alert('No hay lenguas en el despacho.');
                        return;
                    }
                    if (
                        !confirm(
                            '¿Confirma terminar el despacho con ' +
                                n +
                                ' lengua(s)?\n\nSe darán de baja del inventario local los registros cuyo id de producto coincida con cada código de la lista.',
                        )
                    ) {
                        return;
                    }
                    var urlFin = window.despachoFinalizarInventarioUrl || '';
                    if (!urlFin || !formDespacho) {
                        alert('No está configurada la ruta para finalizar el despacho.');
                        return;
                    }
                    var tokenEl = formDespacho.querySelector('[name="_token"]');
                    if (!tokenEl || !tokenEl.value) {
                        alert('Falta el token de seguridad; recargue la página.');
                        return;
                    }
                    var fd = new FormData();
                    fd.append('_token', tokenEl.value);
                    tbody.querySelectorAll('input.inp-codigo-despacho').forEach(function (inp) {
                        fd.append('codigos[]', (inp.value || '').trim());
                    });
                    finalizarEnCurso = true;
                    btnTerminar.disabled = true;
                    try {
                        var res = await fetch(urlFin, {
                            method: 'POST',
                            body: fd,
                            headers: {
                                Accept: 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            credentials: 'same-origin',
                        });
                        var ct = res.headers.get('content-type') || '';
                        if (!ct.includes('application/json')) {
                            throw new Error('Respuesta no JSON');
                        }
                        var json = await res.json();
                        if (!json.ok) {
                            var errMsg = json.message || 'No se pudo dar de baja el inventario.';
                            if (json.errors && typeof json.errors === 'object') {
                                var parts = [];
                                Object.keys(json.errors).forEach(function (k) {
                                    var arr = json.errors[k];
                                    if (Array.isArray(arr)) {
                                        parts = parts.concat(arr);
                                    }
                                });
                                if (parts.length) {
                                    errMsg = parts.join(' ');
                                }
                            }
                            alert(errMsg);
                            return;
                        }
                        alert(json.message || 'Despacho finalizado.');
                        tbody.innerHTML = '';
                        codes.clear();
                        actualizarTotal();
                        document.getElementById('empresa').value = '';
                        document.getElementById('placa').value = '';
                        document.getElementById('conductor').value = '';
                        if (inputCodigo) {
                            inputCodigo.focus();
                        }
                    } catch (err) {
                        alert('Error de red o del servidor al actualizar el inventario.');
                    } finally {
                        btnTerminar.disabled = false;
                        finalizarEnCurso = false;
                    }
                }
            })();
        </script>
    </body>
</html>
