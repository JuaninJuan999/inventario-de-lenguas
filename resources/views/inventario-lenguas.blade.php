<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @include('partials.favicon')
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
                display: flex;
                flex-wrap: nowrap;
                align-items: baseline;
                gap: 0.45rem 0.65rem;
                width: 100%;
                max-width: 100%;
                overflow-x: auto;
                text-align: left;
            }
            .sync-status p {
                margin: 0;
                font-size: 0.78rem;
                line-height: 1.35;
                color: color-mix(in srgb, var(--text) 72%, transparent);
                font-weight: 500;
                text-transform: none;
                letter-spacing: normal;
                white-space: nowrap;
            }
            .sync-status p + p {
                margin-top: 0;
                font-size: 0.78rem;
                color: color-mix(in srgb, var(--text) 62%, transparent);
            }
            .sync-status p + p::before {
                content: "·";
                margin-right: 0.65rem;
                color: color-mix(in srgb, var(--brand-green) 70%, transparent);
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
            table.data td.cel-vencida,
            table.data td.cel-vencida.col-id-producto {
                color: #ff8f8f;
                font-weight: 800;
            }
            table.data tr.row-vencida > td {
                background: color-mix(in srgb, #ff4d4d 12%, transparent);
            }
            table.data td.cel-por-vencer,
            table.data td.cel-por-vencer.col-id-producto {
                color: #ffc56e;
                font-weight: 800;
            }
            table.data tr.row-por-vencer > td {
                background: color-mix(in srgb, #ffb020 10%, transparent);
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
            .filters-grid.filters-grid--single-row {
                display: flex;
                flex-wrap: nowrap;
                align-items: flex-end;
                gap: 0.65rem 0.75rem;
                overflow-x: auto;
                overflow-y: visible;
                padding-bottom: 0.15rem;
                scrollbar-color: color-mix(in srgb, var(--brand-green) 55%, transparent) rgba(0, 0, 0, 0.25);
            }
            .filters-grid.filters-grid--single-row .filter-field {
                flex: 1 1 0;
                min-width: 7.5rem;
            }
            .filters-grid.filters-grid--single-row .filter-field--date {
                flex: 0 0 10.75rem;
                min-width: 10.25rem;
            }
            .filters-advanced {
                margin-top: 0.55rem;
                width: fit-content;
                max-width: 100%;
                border: 1px solid color-mix(in srgb, var(--brand-green) 22%, transparent);
                border-radius: 0.45rem;
                padding: 0.15rem 0.55rem;
                background: color-mix(in srgb, var(--brand-green) 5%, rgba(0, 0, 0, 0.18));
            }
            .filters-advanced[open] {
                width: 100%;
                padding-bottom: 0.55rem;
            }
            .filters-advanced > summary {
                display: inline-flex;
                align-items: center;
                gap: 0.4rem;
                cursor: pointer;
                font-size: 0.68rem;
                font-weight: 700;
                letter-spacing: 0.03em;
                text-transform: uppercase;
                opacity: 0.88;
                padding: 0.2rem 0;
                list-style: none;
                user-select: none;
            }
            .filters-advanced > summary::-webkit-details-marker {
                display: none;
            }
            .filters-advanced > summary::after {
                content: "";
                width: 0.45rem;
                height: 0.45rem;
                border-right: 1.5px solid currentColor;
                border-bottom: 1.5px solid currentColor;
                transform: rotate(45deg);
                margin-top: -0.15rem;
                opacity: 0.85;
                flex-shrink: 0;
                transition: transform 0.15s ease;
            }
            .filters-advanced[open] > summary {
                margin-bottom: 0.4rem;
            }
            .filters-advanced[open] > summary::after {
                transform: rotate(225deg);
                margin-top: 0.1rem;
            }
            .chip-adicional,
            .chip-salida {
                display: inline-block;
                margin-left: 0.35rem;
                padding: 0.1rem 0.4rem;
                border-radius: 9999px;
                font-size: 0.62rem;
                font-weight: 800;
                letter-spacing: 0.04em;
                text-transform: uppercase;
                vertical-align: middle;
            }
            .chip-adicional {
                background: color-mix(in srgb, var(--brand-rose) 22%, transparent);
                color: #ffe9fc;
            }
            .chip-salida {
                background: color-mix(in srgb, var(--brand-green) 28%, transparent);
                color: #e8f6ef;
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
            .filter-field input,
            .filter-field select {
                width: 100%;
                padding: 0.5rem 0.65rem;
                border-radius: 6px;
                border: 1px solid color-mix(in srgb, var(--brand-green) 40%, #1a3d2e);
                background: rgba(0, 0, 0, 0.35);
                color: var(--text);
                font-size: 0.9rem;
                font-family: inherit;
            }
            .filter-field select {
                cursor: pointer;
            }
            .filter-field select option {
                background: #0a1612;
                color: var(--text);
            }
            .filter-field input:focus,
            .filter-field select:focus {
                outline: 2px solid var(--brand-green);
                outline-offset: 1px;
            }
            /* Icono calendario siempre claro: Chromium a veces ignora filter en el pictograma nativo. */
            .filter-field input[type="date"]::-webkit-calendar-picker-indicator {
                cursor: pointer;
                width: 1.45rem;
                height: 1.35rem;
                min-width: 1.45rem;
                min-height: 1.35rem;
                padding: 0;
                margin-inline-start: 0.2rem;
                opacity: 1;
                filter: none;
                background: transparent center / 1.12rem 1.12rem no-repeat
                    url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23f4fffa' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='4' width='18' height='18' rx='2' ry='2'/%3E%3Cline x1='16' y1='2' x2='16' y2='6'/%3E%3Cline x1='8' y1='2' x2='8' y2='6'/%3E%3Cline x1='3' y1='10' x2='21' y2='10'/%3E%3C/svg%3E");
            }
            .filter-field input[type="date"]::-webkit-datetime-edit-fields-wrapper,
            .filter-field input[type="date"]::-webkit-datetime-edit-text {
                color: var(--text);
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
            .filter-actions .btn-excel {
                margin-left: auto;
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
                position: relative;
                display: inline-flex;
                align-items: center;
                gap: 0.35rem;
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
            .btn-clear.is-active {
                text-decoration: underline;
                text-underline-offset: 3px;
                background: color-mix(in srgb, var(--brand-rose) 16%, transparent);
                border-color: color-mix(in srgb, var(--brand-rose) 70%, transparent);
                color: #fff;
            }
            .btn-clear__badge {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-width: 1.15rem;
                height: 1.15rem;
                padding: 0 0.28rem;
                border-radius: 9999px;
                font-size: 0.62rem;
                font-weight: 800;
                line-height: 1;
                letter-spacing: 0;
                text-decoration: none;
                color: #fff;
                background: #e11d48;
                box-shadow: 0 0 0 1.5px color-mix(in srgb, var(--welcome-surface, #0b1a14) 80%, transparent);
            }
            .btn-clear__badge--zero {
                background: color-mix(in srgb, var(--text) 28%, transparent);
                color: color-mix(in srgb, var(--text) 85%, #fff);
            }
            .btn-clear.is-active .btn-clear__badge {
                background: #fff;
                color: #9f1239;
            }
            .btn-clear.is-active .btn-clear__badge--zero {
                background: color-mix(in srgb, #fff 35%, transparent);
                color: #fff;
            }
            .btn-clear--salidas .btn-clear__badge:not(.btn-clear__badge--zero) {
                background: color-mix(in srgb, var(--brand-green) 78%, #0a3d2a);
            }
            .btn-clear--salidas.is-active .btn-clear__badge:not(.btn-clear__badge--zero) {
                background: #fff;
                color: #0a3d2a;
            }
            .btn-excel {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0.5rem 1.15rem;
                font-size: 0.75rem;
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
        </style>
    </head>
    <body>
        @include('partials.logo-institucional')
        <header class="page-bar">
            <div>
                <h1>Inventario de Lenguas</h1>
                <p>
                    Muestra el total de <strong>Lenguas</strong> disponibles en el inventario.
                    </p>
            </div>
            <a class="link-menu" href="{{ route('menu') }}">← Menú</a>
        </header>

        <section
            id="inv-sheet"
            class="sheet"
            aria-label="Inventario de lenguas"
            data-import-url="{{ route('inventario.lenguas.importar_hoy', [], false) }}"
            data-sync-interval="{{ (int) $inventarioSyncIntervalSeconds }}"
        >
            <div class="toolbar">
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

            @php
                $viendoPorVencer = ! empty($filters['por_vencer']);
                $viendoVencidas = ! empty($filters['vencidas']);
                $viendoAdicionales = ($filters['adicionales'] ?? '') === 'si';
                $viendoSalidas = ! empty($filters['salidas']);
                $tieneFechasAvanzadas = ($filters['fecha_desde'] ?? '') !== '' || ($filters['fecha_hasta'] ?? '') !== '';
                $chipQueryBase = array_filter([
                    'id_producto' => $filters['id_producto'] ?? '',
                    'propietario' => $filters['propietario'] ?? '',
                    'destino' => $filters['destino'] ?? '',
                ], static fn ($v) => $v !== null && $v !== '');
            @endphp
            <form
                id="inv-filters"
                class="filters"
                method="get"
                action="{{ route('inventario.lenguas') }}"
                aria-label="Criterios de consulta del inventario de lenguas"
                @if ($viendoPorVencer) data-por-vencer="1" @endif
                @if ($viendoVencidas) data-vencidas="1" @endif
                @if ($viendoAdicionales) data-adicionales="1" @endif
                @if ($viendoSalidas) data-salidas="1" @endif
            >
                @if ($viendoPorVencer)
                    <input type="hidden" name="por_vencer" value="1" id="inv-por-vencer-flag" />
                @endif
                @if ($viendoVencidas)
                    <input type="hidden" name="vencidas" value="1" id="inv-vencidas-flag" />
                @endif
                @if ($viendoAdicionales)
                    <input type="hidden" name="adicionales" value="si" id="inv-adicionales-flag" />
                @endif
                @if ($viendoSalidas)
                    <input type="hidden" name="salidas" value="1" id="inv-salidas-flag" />
                @endif
                <div class="filters-grid filters-grid--single-row">
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
                    <div class="filter-field">
                        <label for="inv-destino">Destino</label>
                        <input
                            id="inv-destino"
                            type="text"
                            name="destino"
                            value="{{ $filters['destino'] ?? '' }}"
                            maxlength="200"
                            placeholder="Parcial o completo"
                            autocomplete="off"
                        />
                        @error('destino')
                            <p class="err">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <details class="filters-advanced" @if ($tieneFechasAvanzadas) open @endif>
                    <summary>Avanzado · filtro por fecha de ref. turno (opcional)</summary>
                    <div class="filters-grid filters-grid--single-row">
                        <div class="filter-field filter-field--date">
                            <label for="inv-fecha-desde">Fecha desde</label>
                            <input
                                id="inv-fecha-desde"
                                type="date"
                                name="fecha_desde"
                                value="{{ $filters['fecha_desde'] ?? '' }}"
                                autocomplete="off"
                            />
                            @error('fecha_desde')
                                <p class="err">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="filter-field filter-field--date">
                            <label for="inv-fecha-hasta">Fecha hasta</label>
                            <input
                                id="inv-fecha-hasta"
                                type="date"
                                name="fecha_hasta"
                                value="{{ $filters['fecha_hasta'] ?? '' }}"
                                autocomplete="off"
                            />
                            @error('fecha_hasta')
                                <p class="err">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <p class="muted" style="margin: 0.45rem 0 0; font-size: 0.76rem">
                        Por defecto se lista todo el stock disponible. Si indica solo una fecha, se usa ese mismo día como rango.
                    </p>
                </details>

                <div class="filter-actions">
                    <button class="btn-apply" type="submit">Aplicar</button>
                    <button
                        class="btn-excel"
                        type="submit"
                        formaction="{{ route('inventario.lenguas.excel') }}"
                        formmethod="get"
                    >Excel</button>
                    <a class="btn-clear" href="{{ route('inventario.lenguas') }}" title="Quitar filtros y ver todo el stock">Limpiar</a>
                    @php
                        $nAdi = (int) ($conteoAdicionales ?? 0);
                        $nSal = (int) ($conteoSalidas ?? 0);
                    @endphp
                    <a
                        id="inv-chip-adicionales"
                        class="btn-clear {{ $viendoAdicionales ? 'is-active' : '' }}"
                        href="{{ route('inventario.lenguas', $chipQueryBase + ['adicionales' => 'si']) }}"
                        title="Solo lenguas etiquetadas como Adicional (ADI.): {{ $nAdi }} disponible(s)"
                        aria-current="{{ $viendoAdicionales ? 'page' : 'false' }}"
                        aria-label="Adicionales, {{ $nAdi }} disponible{{ $nAdi === 1 ? '' : 's' }}"
                        data-badge-kind="adicionales"
                    >
                        Adicionales
                        <span
                            id="inv-badge-adicionales"
                            @class(['btn-clear__badge', 'btn-clear__badge--zero' => $nAdi === 0])
                        >{{ $nAdi > 99 ? '99+' : $nAdi }}</span>
                    </a>
                    <a
                        id="inv-chip-salidas"
                        class="btn-clear btn-clear--salidas {{ $viendoSalidas ? 'is-active' : '' }}"
                        href="{{ route('inventario.lenguas', $chipQueryBase + ['salidas' => 1]) }}"
                        title="Solo lenguas etiquetadas como Salida (SAL.): {{ $nSal }} disponible(s)"
                        aria-current="{{ $viendoSalidas ? 'page' : 'false' }}"
                        aria-label="Salidas, {{ $nSal }} disponible{{ $nSal === 1 ? '' : 's' }}"
                        data-badge-kind="salidas"
                    >
                        Salidas
                        <span
                            id="inv-badge-salidas"
                            @class(['btn-clear__badge', 'btn-clear__badge--zero' => $nSal === 0])
                        >{{ $nSal > 99 ? '99+' : $nSal }}</span>
                    </a>
                    <a
                        class="btn-clear {{ $viendoPorVencer ? 'is-active' : '' }}"
                        href="{{ route('inventario.lenguas', $chipQueryBase + ['por_vencer' => 1]) }}"
                        title="Lenguas que vencen hoy o mañana (vida útil 4 días desde fecha de registro)"
                        aria-current="{{ $viendoPorVencer ? 'page' : 'false' }}"
                    >Por vencer</a>
                    <a
                        class="btn-clear {{ $viendoVencidas ? 'is-active' : '' }}"
                        href="{{ route('inventario.lenguas', $chipQueryBase + ['vencidas' => 1]) }}"
                        title="Lenguas que ya superaron la fecha de vencimiento en stock"
                        aria-current="{{ $viendoVencidas ? 'page' : 'false' }}"
                    >Vencidas</a>
                    <a
                        class="btn-clear {{ ! $viendoPorVencer && ! $viendoVencidas && ! $viendoAdicionales && ! $viendoSalidas && ($filters['fecha_desde'] ?? '') === $hoyOperacion && ($filters['fecha_hasta'] ?? '') === $hoyOperacion ? 'is-active' : '' }}"
                        href="{{ route('inventario.lenguas', $chipQueryBase + ['fecha_desde' => $hoyOperacion, 'fecha_hasta' => $hoyOperacion]) }}"
                        title="Acotar por ref. turno del día de operación"
                    >Solo hoy</a>
                </div>
            </form>

            @if ($errors->any())
                <p class="alert alert--err" role="alert">
                    Los criterios de consulta no cumplen las reglas de validación; corrija los campos señalados.
                </p>
            @endif

            @if (! empty($inventarioSyncMeta['hit_limit']))
                <p class="alert alert--err" id="inv-sync-limit-alert" role="status">
                    La última sincronización alcanzó el tope de
                    <strong>{{ (int) ($inventarioSyncMeta['limite'] ?? 2000) }}</strong>
                    filas por consulta a SIRT. Pueden faltar lenguas; aumente
                    <code>INGRESOS_LENGUAS_CONSULTA_LIMIT</code> o acote el rango. El scheduler seguirá recuperando lotes antiguos en corridas siguientes.
                </p>
            @endif

            <div id="inv-listado">
                @include('inventario-lenguas._listado', [
                    'rows' => $rows,
                    'filters' => $filters,
                    'importOk' => session()->has('importados_sync'),
                    'importadosSync' => session('importados_sync'),
                    'importHitLimit' => session('import_hit_limit'),
                    'importLimite' => session('import_limite'),
                    'importError' => session('import_error'),
                    'filtrosActivos' => $filtrosActivos,
                    'listadoEmptyHint' => $listadoEmptyHint,
                    'totalCoincidentes' => $totalCoincidentes ?? $rows->count(),
                    'listadoLimit' => $listadoLimit ?? 2000,
                    'inventarioSyncMeta' => $inventarioSyncMeta ?? null,
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

                function formatBadgeCount(n) {
                    n = parseInt(n, 10);
                    if (!isFinite(n) || n < 0) {
                        n = 0;
                    }
                    return n > 99 ? '99+' : String(n);
                }

                function updateEtiquetaBadge(chipId, badgeId, count, label) {
                    const chip = document.getElementById(chipId);
                    const badge = document.getElementById(badgeId);
                    if (!badge) {
                        return;
                    }
                    const n = parseInt(count, 10);
                    const safe = !isFinite(n) || n < 0 ? 0 : n;
                    badge.textContent = formatBadgeCount(safe);
                    badge.classList.toggle('btn-clear__badge--zero', safe === 0);
                    if (chip) {
                        const plural = safe === 1 ? '' : 's';
                        chip.title =
                            'Solo lenguas etiquetadas como ' +
                            label +
                            ': ' +
                            safe +
                            ' disponible(s)';
                        chip.setAttribute(
                            'aria-label',
                            label.split(' ')[0] + ', ' + safe + ' disponible' + plural,
                        );
                    }
                }

                function updateEtiquetaBadges(data) {
                    if (!data || typeof data !== 'object') {
                        return;
                    }
                    if (typeof data.conteo_adicionales !== 'undefined') {
                        updateEtiquetaBadge(
                            'inv-chip-adicionales',
                            'inv-badge-adicionales',
                            data.conteo_adicionales,
                            'Adicional (ADI.)',
                        );
                    }
                    if (typeof data.conteo_salidas !== 'undefined') {
                        updateEtiquetaBadge(
                            'inv-chip-salidas',
                            'inv-badge-salidas',
                            data.conteo_salidas,
                            'Salida (SAL.)',
                        );
                    }
                }

                async function runSync() {
                    if (!importUrl || syncInFlight) {
                        return;
                    }
                    syncInFlight = true;
                    setLine1('Ejecutando actualización de la réplica local…');
                    target.querySelector('.alert--err.js-ajax-error')?.remove();
                    const filterForm = document.getElementById('inv-filters');
                    const syncUrl = new URL(importUrl, window.location.href);
                    const filterNames = [
                        'fecha_desde',
                        'fecha_hasta',
                        'id_producto',
                        'propietario',
                        'destino',
                        'adicionales',
                        'salidas',
                        'por_vencer',
                        'vencidas',
                    ];
                    filterNames.forEach(function (name) {
                        const el = filterForm ? filterForm.querySelector('[name="' + name + '"]') : null;
                        if (!el || el.value === '') {
                            if (
                                name === 'por_vencer' ||
                                name === 'vencidas' ||
                                name === 'adicionales' ||
                                name === 'salidas'
                            ) {
                                return;
                            }
                            syncUrl.searchParams.set(name, '');
                            return;
                        }
                        syncUrl.searchParams.set(name, el.value);
                    });
                    const pagePorVencer =
                        filterForm && filterForm.getAttribute('data-por-vencer') === '1'
                            ? '1'
                            : new URLSearchParams(window.location.search).get('por_vencer');
                    const pageVencidas =
                        filterForm && filterForm.getAttribute('data-vencidas') === '1'
                            ? '1'
                            : new URLSearchParams(window.location.search).get('vencidas');
                    const pageAdicionales =
                        filterForm && filterForm.getAttribute('data-adicionales') === '1'
                            ? '1'
                            : new URLSearchParams(window.location.search).get('adicionales');
                    const pageSalidas =
                        filterForm && filterForm.getAttribute('data-salidas') === '1'
                            ? '1'
                            : new URLSearchParams(window.location.search).get('salidas');
                    if (pageVencidas === '1') {
                        syncUrl.searchParams.set('vencidas', '1');
                        syncUrl.searchParams.delete('por_vencer');
                    } else if (pagePorVencer === '1') {
                        syncUrl.searchParams.set('por_vencer', '1');
                        syncUrl.searchParams.delete('vencidas');
                    }
                    if (pageAdicionales === '1' || pageAdicionales === 'si') {
                        syncUrl.searchParams.set('adicionales', 'si');
                        syncUrl.searchParams.delete('salidas');
                    } else if (pageSalidas === '1') {
                        syncUrl.searchParams.set('salidas', '1');
                        syncUrl.searchParams.delete('adicionales');
                    }
                    const body = new FormData();
                    body.append('_token', csrfToken());
                    filterNames.forEach(function (name) {
                        const el = filterForm ? filterForm.querySelector('[name="' + name + '"]') : null;
                        if (name === 'vencidas') {
                            if (pageVencidas === '1' || (el && el.value === '1')) {
                                body.append('vencidas', '1');
                            }
                            return;
                        }
                        if (name === 'por_vencer') {
                            if (pageVencidas !== '1' && (pagePorVencer === '1' || (el && el.value === '1'))) {
                                body.append('por_vencer', '1');
                            }
                            return;
                        }
                        if (name === 'adicionales') {
                            if (
                                pageSalidas !== '1' &&
                                (pageAdicionales === '1' ||
                                    pageAdicionales === 'si' ||
                                    (el && el.value === 'si'))
                            ) {
                                body.append('adicionales', 'si');
                            }
                            return;
                        }
                        if (name === 'salidas') {
                            if (
                                pageAdicionales !== '1' &&
                                pageAdicionales !== 'si' &&
                                (pageSalidas === '1' || (el && el.value === '1'))
                            ) {
                                body.append('salidas', '1');
                            }
                            return;
                        }
                        body.append(name, el ? el.value : '');
                    });
                    try {
                        const res = await fetch(syncUrl.toString(), {
                            method: 'POST',
                            headers: {
                                Accept: 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': csrfToken(),
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
                        updateEtiquetaBadges(data);
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
