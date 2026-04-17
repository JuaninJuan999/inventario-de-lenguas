<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @include('partials.favicon')
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Lenguas Desposte — {{ config('app.name') }}</title>
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
                align-items: flex-start;
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
            }
            .link-menu:hover {
                background: color-mix(in srgb, var(--brand-rose) 16%, transparent);
            }
            .sheet {
                max-width: 72rem;
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
            .field-card__text .hint-quitar {
                color: #ffb4b4;
                font-weight: 800;
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
                font-size: 0.82rem;
            }
            .dispatch-table-wrap {
                overflow-x: auto;
                max-width: 100%;
            }
            .dispatch-table th,
            .dispatch-table td {
                border: 1px solid color-mix(in srgb, var(--brand-green) 28%, rgba(0, 0, 0, 0.5));
                padding: 0.4rem 0.5rem;
                text-align: left;
                vertical-align: top;
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
                font-size: 0.65rem;
                color: var(--text);
            }
            .dispatch-cell {
                margin: 0;
                font-size: inherit;
                line-height: 1.45;
                color: var(--text);
                white-space: normal;
                word-break: break-word;
                overflow-wrap: anywhere;
                min-width: 0;
            }
            .dispatch-cell--empty {
                color: color-mix(in srgb, var(--text) 52%, transparent);
                font-style: italic;
            }
            .dispatch-table th.col-id-producto,
            .dispatch-table td.col-id-producto {
                white-space: nowrap;
                vertical-align: middle;
            }
            .dispatch-cell--id-producto {
                white-space: nowrap;
                word-break: normal;
                overflow-wrap: normal;
            }
            .dispatch-table th.col-fecha-mov,
            .dispatch-table td.col-fecha-mov {
                white-space: nowrap;
                vertical-align: middle;
            }
            .dispatch-table tbody tr:nth-child(even) {
                background: rgba(0, 0, 0, 0.18);
            }
            .dispatch-table th.col-quitar,
            .dispatch-table td.col-quitar {
                width: 3.5rem;
                text-align: center;
                vertical-align: middle;
            }
            .dispatch-table td input.inp-codigo-desposte[type="hidden"] {
                display: none;
            }
            .btn-retirar-codigo {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 2rem;
                height: 2rem;
                margin: 0 auto;
                padding: 0;
                border: 1px solid color-mix(in srgb, #ff6b6b 75%, transparent);
                border-radius: 6px;
                background: color-mix(in srgb, #c92a2a 42%, rgba(0, 0, 0, 0.35));
                color: #ffc9c9;
                font-size: 1.4rem;
                font-weight: 700;
                line-height: 1;
                cursor: pointer;
                font-family: inherit;
            }
            .btn-retirar-codigo:hover {
                background: color-mix(in srgb, #e03131 55%, rgba(0, 0, 0, 0.25));
                color: #fff;
            }
            .btn-retirar-codigo:focus-visible {
                outline: 2px solid color-mix(in srgb, #ffb4b4 90%, #fff);
                outline-offset: 2px;
            }
            .footer-actions {
                display: flex;
                justify-content: flex-end;
                margin-top: 0.5rem;
            }
        </style>
    </head>
    <body>
        @include('partials.logo-institucional')
        <div class="page-bar">
            <div>
                <h1>Lenguas Desposte</h1>
                <p class="sub">Lenguas que pasan al proceso de la Planta de Desposte.</p>
            </div>
            <a class="link-menu" href="{{ route('menu') }}">← Volver al menú</a>
        </div>

        <div class="sheet">
            <form id="form-desposte" action="#" method="post" onsubmit="return false;">
                @csrf

                <div class="field-card">
                    <h2 class="field-card__title">Id de producto (lectura de código de barras)</h2>
                    <p class="field-card__text">
                        Escanee o escriba el valor y pulse <strong>Enter</strong>. Si agregó una fila
                        por error, use la <strong class="hint-quitar">×</strong>.
                    </p>
                    <input
                        class="field-card__input"
                        type="text"
                        id="codigo-lengua"
                        autocomplete="off"
                        placeholder="Escanee o escriba el código"
                    />
                </div>

                <div class="field-card table-block">
                    <div class="table-head-row">
                        <h2 class="field-card__title">Detalle</h2>
                        <div class="total-display">
                            <span class="total-label">Total</span>
                            <span class="total-num" id="total-lenguas" aria-live="polite">0</span>
                        </div>
                    </div>

                    <div class="dispatch-table-wrap">
                        <table class="dispatch-table" aria-label="Lenguas a desposte">
                            <thead>
                                <tr>
                                    <th scope="col" class="col-id-producto">Id producto</th>
                                    <th scope="col">Propietario</th>
                                    <th scope="col">Destino</th>
                                    <th scope="col" class="col-fecha-mov">Fecha de movimiento</th>
                                    <th scope="col" class="col-quitar">Quitar</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-desposte"></tbody>
                        </table>
                    </div>
                </div>

                <div class="footer-actions">
                    <button type="button" class="btn-3d btn-3d--finish" id="btn-terminar">
                        Registrar movimiento a Planta de Desposte
                    </button>
                </div>
            </form>
        </div>

        <script>
            window.desposteLenguaDestinoUrl = @json(route('despacho.lookup.lengua_destino'));
            window.desposteFinalizarInventarioUrl = @json(route('desposte.finalizar.inventario'));
        </script>
        <script>
            (function () {
                var formDesposte = document.getElementById('form-desposte');
                var inputCodigo = document.getElementById('codigo-lengua');
                var tbody = document.getElementById('tbody-desposte');
                var totalEl = document.getElementById('total-lenguas');
                var btnTerminar = document.getElementById('btn-terminar');
                var codes = new Set();
                var agregarEnCurso = false;
                var finalizarEnCurso = false;

                function pad(n) {
                    return String(n).padStart(2, '0');
                }

                function fechaMovimientoAhora() {
                    var d = new Date();
                    return (
                        pad(d.getDate()) +
                        '/' +
                        pad(d.getMonth() + 1) +
                        '/' +
                        d.getFullYear() +
                        ' ' +
                        pad(d.getHours()) +
                        ':' +
                        pad(d.getMinutes())
                    );
                }

                function actualizarTotal() {
                    totalEl.textContent = String(tbody.querySelectorAll('tr').length);
                }

                function dispatchCell(text, emptyLabel) {
                    var el = document.createElement('div');
                    el.className = 'dispatch-cell' + (text ? '' : ' dispatch-cell--empty');
                    el.textContent = text || emptyLabel;
                    return el;
                }

                async function agregarFila() {
                    if (agregarEnCurso) {
                        return;
                    }
                    var raw = (inputCodigo.value || '').trim();
                    if (!raw) {
                        alert('Ingrese el código o id de producto.');
                        inputCodigo.focus();
                        return;
                    }
                    var urlBase = window.desposteLenguaDestinoUrl || '';
                    if (!urlBase) {
                        alert('No está configurada la ruta de consulta de inventario.');
                        return;
                    }
                    agregarEnCurso = true;
                    inputCodigo.disabled = true;
                    var idNorm = raw;
                    var propietarioTxt = '';
                    var destinoTxt = '';
                    var encontrado = false;
                    try {
                        var res = await fetch(urlBase + '?codigo=' + encodeURIComponent(raw), {
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
                            alert(json.message || 'No se pudo validar el código.');
                            inputCodigo.focus();
                            return;
                        }
                        idNorm = (json.id_producto || raw).trim();
                        encontrado = !!json.encontrado;
                        propietarioTxt =
                            json.propietario != null && String(json.propietario).trim() !== ''
                                ? String(json.propietario).trim()
                                : '';
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
                    var tdId = document.createElement('td');
                    tdId.className = 'col-id-producto';
                    var tdProp = document.createElement('td');
                    var tdDest = document.createElement('td');
                    var tdFecha = document.createElement('td');
                    tdFecha.className = 'col-fecha-mov';
                    var tdQuitar = document.createElement('td');
                    tdQuitar.className = 'col-quitar';

                    var hiddenCod = document.createElement('input');
                    hiddenCod.type = 'hidden';
                    hiddenCod.name = 'codigos[]';
                    hiddenCod.className = 'inp-codigo-desposte';
                    hiddenCod.value = idNorm;

                    var divId = document.createElement('div');
                    divId.className = 'dispatch-cell dispatch-cell--id-producto';
                    divId.textContent = idNorm;
                    if (raw !== idNorm) {
                        divId.title =
                            'Lectura (código de barras): ' +
                            raw +
                            ' — Se usa el id de producto normalizado (sin sufijo final) para inventario y al registrar el movimiento.';
                    } else {
                        divId.title = 'Id de producto coincidente con la lectura.';
                    }

                    var divProp = dispatchCell(
                        propietarioTxt,
                        encontrado ? 'Sin propietario en inventario' : 'Sin fila en inventario',
                    );
                    var divDest = dispatchCell(
                        destinoTxt,
                        encontrado ? 'Sin destino registrado' : 'Sin fila en inventario',
                    );
                    var divFecha = document.createElement('div');
                    divFecha.className = 'dispatch-cell';
                    divFecha.textContent = fechaMovimientoAhora();

                    var btnRetirar = document.createElement('button');
                    btnRetirar.type = 'button';
                    btnRetirar.className = 'btn-retirar-codigo';
                    btnRetirar.setAttribute('aria-label', 'Quitar ' + idNorm + ' de la lista');
                    btnRetirar.title = 'Quitar de la lista (no se registra hasta confirmar el movimiento)';
                    btnRetirar.textContent = '\u00D7';
                    btnRetirar.addEventListener('click', function () {
                        var h = tr.querySelector('input.inp-codigo-desposte');
                        var k = (h && h.value ? h.value : '').trim().toLowerCase();
                        if (k) {
                            codes.delete(k);
                        }
                        tr.remove();
                        actualizarTotal();
                        inputCodigo.focus();
                    });

                    tdId.appendChild(hiddenCod);
                    tdId.appendChild(divId);
                    tdProp.appendChild(divProp);
                    tdDest.appendChild(divDest);
                    tdFecha.appendChild(divFecha);
                    tdQuitar.appendChild(btnRetirar);
                    tr.appendChild(tdId);
                    tr.appendChild(tdProp);
                    tr.appendChild(tdDest);
                    tr.appendChild(tdFecha);
                    tr.appendChild(tdQuitar);
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
                    void terminarDesposte();
                });

                async function terminarDesposte() {
                    if (finalizarEnCurso) {
                        return;
                    }
                    var n = tbody.querySelectorAll('tr').length;
                    if (n === 0) {
                        alert('No hay lenguas en la lista.');
                        return;
                    }
                    if (
                        !confirm(
                            '¿Confirma registrar el movimiento a Planta de Desposte con ' +
                                n +
                                ' lengua(s)?\n\nSe darán de baja del inventario local los registros cuyo id de producto coincida con el id normalizado mostrado en la tabla.',
                        )
                    ) {
                        return;
                    }
                    var urlFin = window.desposteFinalizarInventarioUrl || '';
                    if (!urlFin || !formDesposte) {
                        alert('No está configurada la ruta para finalizar.');
                        return;
                    }
                    var tokenEl = formDesposte.querySelector('[name="_token"]');
                    if (!tokenEl || !tokenEl.value) {
                        alert('Falta el token de seguridad; recargue la página.');
                        return;
                    }
                    var fd = new FormData();
                    fd.append('_token', tokenEl.value);
                    tbody.querySelectorAll('input.inp-codigo-desposte').forEach(function (inp) {
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
                            var errMsg = json.message || 'No se pudo actualizar el inventario.';
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
                        alert(json.message || 'Movimiento registrado.');
                        tbody.innerHTML = '';
                        codes.clear();
                        actualizarTotal();
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
