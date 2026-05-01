<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @include('partials.favicon')
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
            .dispatch-table-wrap {
                overflow-x: auto;
                max-width: 100%;
                -webkit-overflow-scrolling: touch;
                overscroll-behavior-x: contain;
            }
            .dispatch-table-wrap:focus-visible {
                outline: 2px solid color-mix(in srgb, var(--brand-green) 85%, transparent);
                outline-offset: 3px;
                border-radius: 0.35rem;
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
                font-size: 0.7rem;
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
            .dispatch-table th.col-codigo,
            .dispatch-table td.col-codigo {
                width: 0.01%;
                white-space: nowrap;
                vertical-align: middle;
            }
            .dispatch-cell.dispatch-cell--codigo {
                white-space: nowrap;
                word-break: normal;
                overflow-wrap: normal;
                max-width: none;
            }
            .dispatch-table tbody tr:nth-child(even) {
                background: rgba(0, 0, 0, 0.18);
            }
            .row-checklist-alistado > td {
                background: linear-gradient(
                    180deg,
                    color-mix(in srgb, var(--brand-green) 38%, transparent),
                    color-mix(in srgb, var(--brand-green) 14%, transparent)
                ) !important;
                box-shadow: inset 0 -2px 0 color-mix(in srgb, var(--brand-green) 85%, #04261c);
            }
            .row-checklist-alistado .dispatch-cell--codigo {
                text-decoration: underline;
                text-underline-offset: 3px;
            }
            .row-checklist-sin-inv > td {
                opacity: 0.82;
            }
            .estado-chip {
                display: inline-block;
                margin-left: 0.35rem;
                padding: 0.08rem 0.35rem;
                border-radius: 4px;
                font-size: 0.62rem;
                font-weight: 800;
                letter-spacing: 0.05em;
                text-transform: uppercase;
                vertical-align: middle;
                border: 1px solid color-mix(in srgb, var(--brand-green) 35%, transparent);
                background: rgba(0, 0, 0, 0.35);
                color: color-mix(in srgb, var(--text) 88%, transparent);
            }
            .estado-chip--pendiente {
                border-color: color-mix(in srgb, var(--brand-rose) 55%, transparent);
                color: var(--brand-rose);
            }
            .estado-chip--sin-inv {
                border-color: color-mix(in srgb, #ff6b6b 65%, transparent);
                color: #ffb4b4;
            }
            .estado-chip--ok {
                border-color: color-mix(in srgb, var(--brand-green) 65%, transparent);
                color: #04261c;
                background: color-mix(in srgb, var(--brand-green) 42%, transparent);
            }
            .btn-3d.is-static-readonly {
                cursor: default;
                opacity: 0.92;
            }
            .btn-3d.is-static-readonly:active {
                transform: none;
            }
            .dispatch-table th.col-quitar,
            .dispatch-table td.col-quitar {
                width: 3.5rem;
                text-align: center;
                vertical-align: middle;
            }
            .dispatch-table td input.inp-codigo-despacho[type="hidden"] {
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
                max-height: min(92dvh, 36rem);
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
                .page-bar {
                    flex-direction: column;
                    align-items: stretch;
                    gap: 0.65rem;
                }
                .page-bar h1 {
                    font-size: 1.05rem;
                    line-height: 1.25;
                }
                .page-bar .page-bar__actions {
                    justify-content: stretch;
                }
                .page-bar .link-menu {
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
                    padding: 1rem 0.85rem 1.35rem;
                    border-radius: 1rem;
                }
                .label-fixed {
                    width: 100%;
                }
                .row-field {
                    flex-direction: column;
                    align-items: stretch;
                    gap: 0.45rem;
                    margin-bottom: 0.85rem;
                }
                .btn-3d.label-fixed {
                    width: 100%;
                    min-height: 46px;
                    justify-content: center;
                    white-space: normal;
                    text-align: center;
                    line-height: 1.25;
                    padding: 0.55rem 0.75rem;
                }
                .row-field input[type='text'],
                .field-card input[type='text'].field-card__input {
                    font-size: 16px;
                    min-height: 46px;
                    padding: 0.55rem 0.7rem;
                }
                .table-head-row {
                    flex-direction: column;
                    align-items: stretch;
                    gap: 0.5rem;
                }
                .total-display {
                    flex-wrap: wrap;
                    justify-content: flex-start;
                }
                .footer-actions {
                    justify-content: stretch;
                }
                .btn-3d--finish {
                    width: 100%;
                    justify-content: center;
                    min-height: 50px;
                    margin-top: 0.35rem;
                }
                .lookup-backdrop {
                    padding: max(0.75rem, env(safe-area-inset-top)) max(0.65rem, env(safe-area-inset-right))
                        max(0.85rem, env(safe-area-inset-bottom)) max(0.65rem, env(safe-area-inset-left));
                    align-items: flex-end;
                }
                .lookup-dialog {
                    max-height: min(92dvh, 100%);
                    border-bottom-left-radius: 0;
                    border-bottom-right-radius: 0;
                }
                .btn-retirar-codigo {
                    width: 46px;
                    height: 46px;
                    font-size: 1.5rem;
                }
            }
            /* Tabla detalle: en móvil se desliza horizontalmente (dedos / barra inferior). */
            @media (max-width: 560px) {
                .dispatch-table-wrap {
                    overflow-x: auto;
                    overflow-y: visible;
                    margin-left: -0.25rem;
                    margin-right: -0.25rem;
                    padding-left: 0.25rem;
                    padding-right: 0.25rem;
                    padding-bottom: 0.35rem;
                    touch-action: pan-x pan-y;
                    scrollbar-width: thin;
                }
                .dispatch-table-wrap::-webkit-scrollbar {
                    height: 6px;
                }
                .dispatch-table-wrap::-webkit-scrollbar-thumb {
                    background: color-mix(in srgb, var(--brand-green) 55%, transparent);
                    border-radius: 999px;
                }
                .dispatch-table {
                    width: max-content;
                    min-width: 720px;
                    max-width: none;
                    font-size: 0.8rem;
                }
                .dispatch-table th,
                .dispatch-table td {
                    padding: 0.45rem 0.55rem;
                }
                .dispatch-table th:nth-child(2),
                .dispatch-table td:nth-child(2) {
                    min-width: 9rem;
                    max-width: 12rem;
                }
                .dispatch-table th:nth-child(3),
                .dispatch-table td:nth-child(3) {
                    min-width: 16rem;
                    max-width: 24rem;
                }
                .dispatch-table th:nth-child(4),
                .dispatch-table td:nth-child(4) {
                    min-width: 8.5rem;
                    white-space: nowrap;
                }
                .dispatch-table th.col-quitar,
                .dispatch-table td.col-quitar {
                    width: auto;
                    min-width: 3.75rem;
                }
                .dispatch-table th.col-codigo,
                .dispatch-table td.col-codigo {
                    white-space: nowrap;
                }
                .dispatch-cell.dispatch-cell--codigo {
                    white-space: nowrap;
                    word-break: normal;
                    overflow-wrap: normal;
                }
            }
        </style>
    </head>
    <body>
        @include('partials.logo-institucional')
        <div class="page-bar">
            <h1>Despacho de Lenguas @if (! empty($checklistMode)) — checklist @endif</h1>
            <div class="page-bar__actions">
                @if (! empty($checklistMode))
                    <a class="link-menu" href="{{ route('despacho.lenguas') }}">← Placas pendientes</a>
                @else
                    <a class="link-menu" href="{{ route('despacho.lenguas') }}">Lista checklist</a>
                @endif
                <a class="link-menu" href="{{ route('menu') }}">← Volver al menú</a>
            </div>
        </div>

        <div class="sheet">
            <form id="form-despacho" action="#" method="post" onsubmit="return false;">
                @csrf

                <div class="row-field">
                    <button type="button" class="btn-3d label-fixed" data-open-lookup="empresa" aria-haspopup="dialog">
                        Operador logístico
                    </button>
                    <input
                        type="text"
                        name="empresa"
                        id="empresa"
                        autocomplete="organization"
                        placeholder="{{ ! empty($checklistMode) ? 'Elija del listado o escriba el operador' : 'Código operador (listado COLBEEF)' }}"
                        value="{{ old('empresa', $vehiculoEmpresaChecklist ?? '') }}"
                    >
                </div>

                <div class="row-field">
                    <button type="button" class="btn-3d label-fixed" data-open-lookup="placa" aria-haspopup="dialog">
                        Placa vehículo
                    </button>
                    <input
                        type="text"
                        name="placa"
                        id="placa"
                        autocomplete="off"
                        placeholder="{{ ! empty($checklistMode) ? 'Elija del listado o escriba la placa' : 'Placa asignada al operador' }}"
                        value="{{ old('placa', $vehiculoPlacaChecklist ?? '') }}"
                    >
                </div>
                <div class="row-field">
                    <button type="button" class="btn-3d label-fixed" data-open-lookup="conductor" aria-haspopup="dialog">
                        Conductor
                    </button>
                    <input
                        type="text"
                        name="conductor"
                        id="conductor"
                        autocomplete="name"
                        placeholder="{{ ! empty($checklistMode) ? 'Elija del listado o escriba el conductor' : '' }}"
                        value="{{ old('conductor', $vehiculoConductorChecklist ?? '') }}"
                    >
                </div>

                <div class="field-card">
                    <h2 class="field-card__title">Código de lengua</h2>
                    <p class="field-card__text">
                        @if (! empty($checklistMode))
                            Escanee o escriba el código y pulse <strong>Enter</strong>. Las filas del listado Colbeef
                            pasan a <strong>verde (alistado)</strong> al leer un código que coincida y tenga inventario
                            local. Si necesita <strong>añadir</strong> una lengua que no estaba en la lista, al pulsar
                            <strong>Enter</strong> se agregará igual (inventario local). Use
                            <strong class="hint-quitar">×</strong> para desmarcar. Antes de
                            <strong>Terminar despacho</strong> elija usted <strong>operador, placa y conductor</strong> con
                            los botones o a mano.
                        @else
                            Escanee o escriba el código y pulse <strong>Enter</strong>. Si agregó uno por error, use la
                            <strong class="hint-quitar">×</strong> roja en la tabla para retirarlo de este despacho (sigue en
                            inventario hasta que pulse <strong>Terminar despacho</strong> con la lista definitiva).
                        @endif
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
                    @if (! empty($checklistMode) && ($checklistSinInventarioCount ?? 0) > 0)
                        <p class="field-card__text" style="margin-top: 0">
                            Hay <strong>{{ (int) $checklistSinInventarioCount }}</strong> código(s) en Colbeef sin fila disponible en inventario local:
                            no podrá cerrar el despacho hasta incorporarlos al inventario o corregir la asignación.
                        </p>
                    @endif
                    <div class="table-head-row">
                        <h2 class="field-card__title">Detalle del despacho</h2>
                        <div class="total-display">
                            @if (! empty($checklistMode))
                                <span class="total-label">Validadas</span>
                                <span class="total-num" id="total-validadas-checklist" aria-live="polite">0</span>
                                <span class="total-label">de</span>
                                <span class="total-num" id="total-requeridas-checklist">{{ (int) ($checklistTotalConInventario ?? 0) }}</span>
                                <span class="total-label">con inventario</span>
                            @else
                                <span class="total-label">Total lenguas</span>
                                <span class="total-num" id="total-lenguas" aria-live="polite">0</span>
                            @endif
                        </div>
                    </div>

                    <div
                        class="dispatch-table-wrap"
                        role="region"
                        aria-label="Detalle del despacho: deslice horizontalmente para ver todas las columnas"
                        tabindex="0"
                    >
                        <table class="dispatch-table" aria-label="Lenguas en despacho">
                            <thead>
                                <tr>
                                    <th scope="col" class="col-codigo">Código</th>
                                    <th scope="col">Propietario</th>
                                    <th scope="col">Destino</th>
                                    <th scope="col">Fecha despacho</th>
                                    <th scope="col" class="col-quitar">
                                        @if (! empty($checklistMode))
                                            Desmarcar
                                        @else
                                            Quitar
                                        @endif
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="tbody-despacho">
                                @if (! empty($checklistMode))
                                    @foreach ($checklistLines ?? [] as $line)
                                        <tr
                                            data-checklist-row="1"
                                            data-en-inv="{{ $line['en_inventario'] ? '1' : '0' }}"
                                            data-alistado="0"
                                            data-canonical="{{ $line['canonical_id_producto'] }}"
                                            data-match-keys="{{ htmlspecialchars(json_encode($line['match_keys'], JSON_THROW_ON_ERROR), ENT_QUOTES | ENT_HTML5, 'UTF-8') }}"
                                            @class([
                                                'row-checklist-pendiente' => $line['en_inventario'],
                                                'row-checklist-sin-inv' => ! $line['en_inventario'],
                                            ])
                                        >
                                            <td class="col-codigo" data-label="Código">
                                                <input
                                                    type="hidden"
                                                    name="codigos[]"
                                                    class="inp-codigo-despacho"
                                                    value="{{ $line['canonical_id_producto'] }}"
                                                    disabled
                                                >
                                                <div class="dispatch-cell dispatch-cell--codigo">{{ $line['id_producto_colbeef'] }}</div>
                                            </td>
                                            <td data-label="Propietario">
                                                @if (! $line['en_inventario'])
                                                    <div class="dispatch-cell dispatch-cell--empty">—</div>
                                                @elseif (trim((string) ($line['propietario'] ?? '')) !== '')
                                                    <div class="dispatch-cell">{{ $line['propietario'] }}</div>
                                                @else
                                                    <div class="dispatch-cell dispatch-cell--empty">Sin propietario en inventario</div>
                                                @endif
                                            </td>
                                            <td data-label="Destino">
                                                @if (! $line['en_inventario'])
                                                    <div class="dispatch-cell dispatch-cell--empty">Sin inventario local</div>
                                                @elseif (trim((string) ($line['destino'] ?? '')) !== '')
                                                    <div class="dispatch-cell">{{ $line['destino'] }}</div>
                                                @else
                                                    <div class="dispatch-cell dispatch-cell--empty">Sin destino registrado</div>
                                                @endif
                                            </td>
                                            <td data-label="Estado">
                                                <div class="dispatch-cell">
                                                    <span class="fecha-despacho-cel">—</span>
                                                    @if (! $line['en_inventario'])
                                                        <span class="estado-chip estado-chip--sin-inv">Sin inventario</span>
                                                    @else
                                                        <span class="estado-chip estado-chip--pendiente row-estado-chip">Pendiente</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="col-quitar">
                                                @if ($line['en_inventario'])
                                                    <button
                                                        type="button"
                                                        class="btn-retirar-codigo"
                                                        aria-label="Desmarcar código {{ $line['id_producto_colbeef'] }}"
                                                        title="Desmarcar validación física"
                                                    >
                                                        ×
                                                    </button>
                                                @else
                                                    <span class="dispatch-cell dispatch-cell--empty">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="footer-actions">
                    <button
                        type="button"
                        class="btn-3d btn-3d--finish"
                        id="btn-terminar"
                        @if (! empty($checklistMode)) disabled @endif
                    >
                        Terminar despacho
                    </button>
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
                        placeholder="Filtrar resultados del cuadro…"
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
            window.despachoVehiculosUrl = @json(route('despacho.lookup.vehiculos', [], false));
            window.despachoLenguaDestinoUrl = @json(route('despacho.lookup.lengua_destino', [], false));
            window.despachoFinalizarInventarioUrl = @json(route('despacho.finalizar.inventario', [], false));
            window.despachoOperadorPlacaFilas = @json($operadorPlacaFilas ?? []);
            window.despachoSeleccionUrl = @json(route('despacho.lenguas'));
            window.despachoCodigoBarrasSuffixDigitos = {{ (int) config('despacho.codigo_barras_suffix_digitos', 4) }};
            window.despachoChecklistVehicleId = @json($checklistVehicleId ?? null);
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
                    if (field === 'empresa' || field === 'placa') {
                        thead.innerHTML =
                            '<tr><th>Operador logístico</th><th>Placa vehículo</th></tr>';
                        return;
                    }
                    var labels = {
                        conductor: 'Conductor',
                    };
                    var th = labels[field] || 'Valor';
                    thead.innerHTML = '<tr><th>' + th + '</th></tr>';
                }

                function openLookup(field) {
                    lookupTargetField = field;
                    var titles = {
                        empresa: 'Operador logístico y placa (COLBEEF)',
                        placa: 'Operador logístico y placa (COLBEEF)',
                        conductor: 'Buscar conductor (SIRT)',
                    };
                    var placeholders = {
                        empresa: 'Filtrar por operador o placa…',
                        placa: 'Filtrar por operador o placa…',
                        conductor: 'Escriba para filtrar por nombre de conductor…',
                    };
                    lookupTitle.textContent = titles[field] || 'Buscar';
                    lookupSearch.placeholder = placeholders[field] || '';
                    lookupSearch.value = '';
                    lookupTbody.innerHTML = '';
                    setLookupThead(field);
                    if (field === 'empresa' || field === 'placa') {
                        lookupStatus.textContent =
                            'Listado estándar COLBEEF. Pulse una fila para rellenar operador y placa.';
                    } else {
                        lookupStatus.textContent =
                            'Consulta SIRT (vehículo asignado). Escriba para acotar la búsqueda.';
                    }
                    lookupStatus.className = 'lookup-msg lookup-msg--muted';
                    lookupBackdrop.hidden = false;
                    lookupBackdrop.removeAttribute('hidden');
                    lookupBackdrop.setAttribute('aria-hidden', 'false');
                    lookupBackdrop.classList.add('is-open');
                    document.body.style.overflow = 'hidden';
                    setTimeout(function () {
                        lookupSearch.focus();
                    }, 50);
                    if (field === 'empresa' || field === 'placa') {
                        runLocalOperadorCatalog('');
                    } else {
                        runLookup('');
                    }
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
                    if (lookupTargetField === 'empresa' || lookupTargetField === 'placa') {
                        var op = document.getElementById('empresa');
                        var pl = document.getElementById('placa');
                        if (op) {
                            op.value = row.operador != null ? String(row.operador) : '';
                        }
                        if (pl) {
                            pl.value = row.placa != null ? String(row.placa) : '';
                        }
                        if (lookupTargetField === 'empresa' && op) {
                            op.focus();
                        } else if (pl) {
                            pl.focus();
                        }
                        closeLookup();
                        return;
                    }
                    var el = document.getElementById('conductor');
                    if (!el) return;
                    el.value = row.nombre_conductor || '';
                    el.focus();
                    closeLookup();
                }

                function cellValueForFocus(row, field) {
                    return row.nombre_conductor;
                }

                function filaOperadorCoincide(row, q) {
                    if (q == null || String(q).trim() === '') {
                        return true;
                    }
                    var qn = String(q).trim().toLowerCase();
                    var qCompact = qn.replace(/\s+/g, '');
                    var op = String(row.operador != null ? row.operador : '').toLowerCase();
                    var pl = String(row.placa != null ? row.placa : '').toLowerCase();
                    var plCompact = pl.replace(/\s+/g, '');
                    return (
                        op.indexOf(qn) !== -1 ||
                        pl.indexOf(qn) !== -1 ||
                        plCompact.indexOf(qCompact) !== -1
                    );
                }

                function runLocalOperadorCatalog(q) {
                    var focus = lookupTargetField;
                    if (focus !== 'empresa' && focus !== 'placa') {
                        return;
                    }
                    var todas = window.despachoOperadorPlacaFilas;
                    if (!Array.isArray(todas) || todas.length === 0) {
                        lookupStatus.textContent =
                            'No hay filas configuradas (despacho_operadores_placas).';
                        lookupStatus.className = 'lookup-msg';
                        lookupTbody.innerHTML = '';
                        return;
                    }
                    var qq = q == null ? '' : String(q).trim();
                    var filtradas = todas.filter(function (row) {
                        return filaOperadorCoincide(row, qq);
                    });
                    renderLocalOperadorRows(filtradas);
                }

                function renderLocalOperadorRows(rows) {
                    lookupTbody.innerHTML = '';
                    if (!rows.length) {
                        lookupStatus.textContent =
                            'Sin coincidencias en el listado COLBEEF. Puede escribir el operador o la placa a mano en el formulario.';
                        lookupStatus.className = 'lookup-msg';
                        return;
                    }
                    lookupStatus.textContent =
                        rows.length +
                        ' fila(s). Pulse una fila para rellenar operador logístico y placa.';
                    lookupStatus.className = 'lookup-msg lookup-msg--muted';
                    rows.forEach(function (row) {
                        var tr = document.createElement('tr');
                        tr.setAttribute('role', 'button');
                        tr.tabIndex = 0;
                        tr.innerHTML =
                            '<td>' +
                            esc(row.operador) +
                            '</td><td>' +
                            esc(row.placa) +
                            '</td>';
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
                    if (!focus || focus === 'empresa' || focus === 'placa') {
                        return;
                    }
                    var base = window.despachoVehiculosUrl;
                    if (!base) {
                        lookupStatus.textContent = 'No está configurada la ruta de consulta de vehículos.';
                        lookupStatus.className = 'lookup-msg';
                        lookupTbody.innerHTML = '';
                        return;
                    }
                    var url;
                    try {
                        var u = new URL(String(base), window.location.href);
                        u.searchParams.set('focus', focus);
                        u.searchParams.set('search', q == null ? '' : String(q));
                        url = u.toString();
                    } catch (e) {
                        lookupStatus.textContent = 'Ruta de consulta inválida. Recargue la página.';
                        lookupStatus.className = 'lookup-msg';
                        lookupTbody.innerHTML = '';
                        return;
                    }
                    if (lastController) lastController.abort();
                    lastController = new AbortController();
                    lookupStatus.textContent = 'Cargando…';
                    fetch(url, {
                        signal: lastController.signal,
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-Despacho-Lookup-Focus': focus,
                        },
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
                        if (lookupTargetField === 'empresa' || lookupTargetField === 'placa') {
                            runLocalOperadorCatalog(q);
                        } else {
                            runLookup(q);
                        }
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
                function despachoNormalizarUnicodeHyphens(s) {
                    return String(s || '')
                        .replace(/[\u2010\u2011\u2012\u2013\u2014\u2015\u2212\uFF0D]/g, '-')
                        .trim();
                }

                function despachoNormalizarIdProducto(leido) {
                    var s = despachoNormalizarUnicodeHyphens(String(leido || ''));
                    if (!s) {
                        return '';
                    }
                    var digitos = Number(window.despachoCodigoBarrasSuffixDigitos || 4);
                    if (!digitos || digitos < 1) {
                        return s;
                    }
                    var parts = s.split('-');
                    if (parts.length < 3) {
                        return s;
                    }
                    var last = parts[parts.length - 1];
                    if (last.length === digitos && /^\d+$/.test(last)) {
                        parts.pop();
                        return parts.join('-');
                    }
                    return s;
                }

                function scannerVariantSet(leido) {
                    var out = new Set();
                    var t = despachoNormalizarUnicodeHyphens(String(leido || ''));
                    if (t) {
                        out.add(t);
                    }
                    var norm = despachoNormalizarIdProducto(t);
                    if (norm && norm !== t) {
                        out.add(norm);
                    }
                    var compact = t.replace(/[\s\-\/_.]+/g, '');
                    if (compact) {
                        out.add(compact);
                    }
                    var digits = t.replace(/\D/g, '');
                    if (digits.length >= 3) {
                        out.add(digits);
                    }
                    return out;
                }

                function variantSetsIntersect(va, vb) {
                    for (var v of va) {
                        if (vb.has(v)) {
                            return true;
                        }
                    }
                    return false;
                }

                /** Coincide lectura con checklist aunque Colbeef e inventario usen formato distinto (compacto, sufijo, etc.). */
                function rowMatchesScan(leido, keysArr) {
                    var L = despachoNormalizarUnicodeHyphens(String(leido || ''));
                    if (!L) {
                        return false;
                    }
                    var V = scannerVariantSet(L);
                    var base = Array.isArray(keysArr) ? keysArr : [];
                    if (!base.length) {
                        return false;
                    }
                    for (var i = 0; i < base.length; i++) {
                        var k = despachoNormalizarUnicodeHyphens(String(base[i] == null ? '' : base[i]));
                        if (!k) {
                            continue;
                        }
                        if (V.has(k)) {
                            return true;
                        }
                        var nk = despachoNormalizarIdProducto(k);
                        if (nk && V.has(nk)) {
                            return true;
                        }
                        var K = scannerVariantSet(k);
                        if (variantSetsIntersect(V, K)) {
                            return true;
                        }
                    }
                    return false;
                }

                function checklistMergeRowMatchKeys(tr) {
                    var keys = [];
                    try {
                        var parsed = JSON.parse(tr.getAttribute('data-match-keys') || '[]');
                        if (Array.isArray(parsed)) {
                            keys = parsed.slice();
                        }
                    } catch (eIgn) {
                        keys = [];
                    }
                    var canonical = despachoNormalizarUnicodeHyphens(tr.getAttribute('data-canonical') || '');
                    if (canonical && keys.indexOf(canonical) === -1) {
                        keys.push(canonical);
                    }
                    var cell = tr.querySelector('.dispatch-cell--codigo');
                    var vis = cell ? despachoNormalizarUnicodeHyphens(String(cell.textContent || '')) : '';
                    if (vis && keys.indexOf(vis) === -1) {
                        keys.push(vis);
                    }
                    return keys;
                }

                var checklistVidRaw = window.despachoChecklistVehicleId;
                var esChecklist =
                    checklistVidRaw != null &&
                    checklistVidRaw !== '' &&
                    !Number.isNaN(Number(checklistVidRaw)) &&
                    Number(checklistVidRaw) > 0;

                var formDespacho = document.getElementById('form-despacho');
                var inputCodigo = document.getElementById('codigo-lengua');
                var tbody = document.getElementById('tbody-despacho');
                var totalEl = document.getElementById('total-lenguas');
                var totalValidadasEl = document.getElementById('total-validadas-checklist');
                var btnTerminar = document.getElementById('btn-terminar');
                var codes = new Set();
                var agregarEnCurso = false;
                var finalizarEnCurso = false;

                /** Campo código siempre editable; foco estable tras Enter/async en móvil (evita disabled + focus). */
                function scheduleFocusCodigo() {
                    if (!inputCodigo) {
                        return;
                    }
                    inputCodigo.disabled = false;
                    inputCodigo.removeAttribute('disabled');
                    window.setTimeout(function () {
                        try {
                            inputCodigo.focus({ preventScroll: true });
                        } catch (eIgn) {
                            try {
                                inputCodigo.focus();
                            } catch (e2) {}
                        }
                    }, 10);
                }

                function hoyTexto() {
                    var d = new Date();
                    var dd = String(d.getDate()).padStart(2, '0');
                    var mm = String(d.getMonth() + 1).padStart(2, '0');
                    var yyyy = d.getFullYear();
                    return dd + '/' + mm + '/' + yyyy;
                }

                function actualizarTotal() {
                    if (totalEl) {
                        totalEl.textContent = String(tbody.querySelectorAll('tr').length);
                    }
                }

                function checklistMarcarTotal() {
                    if (!totalValidadasEl || !tbody) {
                        return;
                    }
                    var n = tbody.querySelectorAll(
                        'tr[data-checklist-row="1"][data-en-inv="1"][data-alistado="1"]',
                    ).length;
                    totalValidadasEl.textContent = String(n);
                }

                function checklistActualizarBotonTerminar() {
                    if (!btnTerminar || !esChecklist || !tbody) {
                        return;
                    }
                    var sinInv = tbody.querySelectorAll('tr[data-checklist-row="1"][data-en-inv="0"]').length > 0;
                    var pend =
                        tbody.querySelectorAll('tr[data-checklist-row="1"][data-en-inv="1"][data-alistado="0"]').length >
                        0;
                    btnTerminar.disabled = sinInv || pend || finalizarEnCurso;
                    btnTerminar.title = sinInv
                        ? 'Hay códigos sin inventario local en este checklist.'
                        : pend
                          ? 'Debe escanear y validar todas las lenguas con inventario antes de cerrar.'
                          : '';
                }

                function checklistActualizarCelda(tr, tdIndex, text, emptyLabel, forceEmptyClass) {
                    var tds = tr.querySelectorAll('td');
                    var td = tds[tdIndex];
                    if (!td) {
                        return;
                    }
                    var div = td.querySelector('.dispatch-cell');
                    if (!div) {
                        return;
                    }
                    var v = text != null ? String(text).trim() : '';
                    var empty = forceEmptyClass || !v;
                    div.textContent = empty ? emptyLabel : v;
                    div.className = 'dispatch-cell' + (empty ? ' dispatch-cell--empty' : '');
                }

                function checklistLimpiarFila(tr) {
                    tr.setAttribute('data-alistado', '0');
                    tr.classList.remove('row-checklist-alistado');
                    tr.classList.add('row-checklist-pendiente');
                    var hid = tr.querySelector('input.inp-codigo-despacho');
                    if (hid && hid.value) {
                        codes.delete(String(hid.value).trim().toLowerCase());
                    }
                    if (hid) {
                        hid.disabled = true;
                    }
                    var fechaSpan = tr.querySelector('.fecha-despacho-cel');
                    if (fechaSpan) {
                        fechaSpan.textContent = '—';
                    }
                    var chip = tr.querySelector('.row-estado-chip');
                    if (chip) {
                        chip.textContent = 'Pendiente';
                        chip.className = 'estado-chip estado-chip--pendiente row-estado-chip';
                    }
                    checklistMarcarTotal();
                    checklistActualizarBotonTerminar();
                    scheduleFocusCodigo();
                }

                async function checklistOnScanEnter() {
                    if (agregarEnCurso || !tbody) {
                        return;
                    }
                    var raw = (inputCodigo.value || '').trim();
                    if (!raw) {
                        alert('Ingrese el código de la lengua.');
                        scheduleFocusCodigo();
                        return;
                    }
                    var candidatos = [];
                    tbody.querySelectorAll('tr[data-checklist-row="1"][data-en-inv="1"][data-alistado="0"]').forEach(
                        function (tr) {
                            var keys = checklistMergeRowMatchKeys(tr);
                            if (rowMatchesScan(raw, keys)) {
                                candidatos.push(tr);
                            }
                        },
                    );
                    if (candidatos.length === 0) {
                        var coincideConAlgunaFilaChecklist = false;
                        tbody.querySelectorAll('tr[data-checklist-row="1"]').forEach(function (trR) {
                            if (rowMatchesScan(raw, checklistMergeRowMatchKeys(trR))) {
                                coincideConAlgunaFilaChecklist = true;
                            }
                        });
                        if (coincideConAlgunaFilaChecklist) {
                            alert(
                                'Ese código ya está en el checklist (pendiente de leer o ya alistado). Use “Desmarcar” en la fila si debe volver a validarla, o revise el código leído.',
                            );
                            scheduleFocusCodigo();
                            return;
                        }
                        await agregarFila();
                        scheduleFocusCodigo();
                        return;
                    }
                    if (candidatos.length > 1) {
                        alert(
                            'Hay más de una fila coincidente en el checklist (duplicados). Revise los datos en Colbeef.',
                        );
                        scheduleFocusCodigo();
                        return;
                    }
                    var tr = candidatos[0];
                    var canonicalEsperado = (tr.getAttribute('data-canonical') || '').trim();
                    var urlBase = window.despachoLenguaDestinoUrl || '';
                    if (!urlBase) {
                        alert('No está configurada la ruta de consulta de inventario.');
                        scheduleFocusCodigo();
                        return;
                    }
                    agregarEnCurso = true;
                    try {
                        var u = new URL(String(urlBase), window.location.href);
                        u.searchParams.set('codigo', raw);
                        var res = await fetch(u.toString(), {
                            headers: {
                                Accept: 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-Despacho-Codigo-Lengua': raw,
                            },
                            credentials: 'same-origin',
                        });
                        var ct = res.headers.get('content-type') || '';
                        if (!ct.includes('application/json')) {
                            throw new Error('Respuesta no JSON');
                        }
                        var json = await res.json();
                        if (!json.ok) {
                            alert(json.message || 'No se pudo validar el código en inventario.');
                            return;
                        }
                        if (!json.encontrado) {
                            alert('El código no tiene una fila disponible en inventario local (sin despachar).');
                            return;
                        }
                        var invId = String(json.id_producto || '').trim();
                        var keysFila = checklistMergeRowMatchKeys(tr);
                        if (
                            !invId ||
                            (invId !== canonicalEsperado && !rowMatchesScan(invId, keysFila))
                        ) {
                            alert(
                                'El inventario local no coincide con la fila esperada del checklist (' +
                                    canonicalEsperado +
                                    ').',
                            );
                            return;
                        }
                        tr.setAttribute('data-alistado', '1');
                        tr.classList.remove('row-checklist-pendiente');
                        tr.classList.add('row-checklist-alistado');
                        var hid = tr.querySelector('input.inp-codigo-despacho');
                        if (hid) {
                            hid.disabled = false;
                            hid.value = invId;
                        }
                        var fechaSpan = tr.querySelector('.fecha-despacho-cel');
                        if (fechaSpan) {
                            fechaSpan.textContent = hoyTexto();
                        }
                        var chip = tr.querySelector('.row-estado-chip');
                        if (chip) {
                            chip.textContent = 'Alistado';
                            chip.className = 'estado-chip estado-chip--ok row-estado-chip';
                        }
                        var propTxt =
                            json.propietario != null && String(json.propietario).trim() !== ''
                                ? String(json.propietario).trim()
                                : '';
                        var destTxt =
                            json.destino != null && String(json.destino).trim() !== ''
                                ? String(json.destino).trim()
                                : '';
                        checklistActualizarCelda(tr, 1, propTxt, 'Sin propietario en inventario', !propTxt);
                        checklistActualizarCelda(tr, 2, destTxt, 'Sin destino registrado', !destTxt);
                        codes.add(invId.toLowerCase());
                        inputCodigo.value = '';
                        checklistMarcarTotal();
                        checklistActualizarBotonTerminar();
                    } catch (err) {
                        alert('No se pudo consultar el inventario local. Revise la sesión o la red.');
                    } finally {
                        agregarEnCurso = false;
                        scheduleFocusCodigo();
                    }
                }

                function checklistBindFilas() {
                    if (!tbody) {
                        return;
                    }
                    tbody.querySelectorAll('tr[data-checklist-row="1"]').forEach(function (tr) {
                        var btn = tr.querySelector('.btn-retirar-codigo');
                        if (!btn) {
                            return;
                        }
                        btn.addEventListener('click', function () {
                            checklistLimpiarFila(tr);
                        });
                    });
                    checklistMarcarTotal();
                    checklistActualizarBotonTerminar();
                }

                function dispatchCell(text, emptyLabel) {
                    var d = document.createElement('div');
                    d.className = 'dispatch-cell' + (text ? '' : ' dispatch-cell--empty');
                    d.textContent = text || emptyLabel;
                    return d;
                }

                async function agregarFila() {
                    if (agregarEnCurso) {
                        return;
                    }
                    var raw = (inputCodigo.value || '').trim();
                    if (!raw) {
                        alert('Ingrese el código de la lengua.');
                        scheduleFocusCodigo();
                        return;
                    }
                    var urlBase = window.despachoLenguaDestinoUrl || '';
                    if (!urlBase) {
                        alert('No está configurada la ruta de consulta de inventario.');
                        scheduleFocusCodigo();
                        return;
                    }
                    agregarEnCurso = true;
                    var idNorm = raw;
                    var propietarioTxt = '';
                    var destinoTxt = '';
                    var encontrado = false;
                    try {
                        var u;
                        try {
                            u = new URL(String(urlBase), window.location.href);
                            u.searchParams.set('codigo', raw);
                        } catch (e1) {
                            alert('Ruta de inventario inválida. Recargue la página (Ctrl+F5).');
                            return;
                        }
                        var res = await fetch(u.toString(), {
                            headers: {
                                Accept: 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-Despacho-Codigo-Lengua': raw,
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
                        return;
                    } finally {
                        agregarEnCurso = false;
                        scheduleFocusCodigo();
                    }

                    if (!idNorm) {
                        return;
                    }
                    if (codes.has(idNorm.toLowerCase())) {
                        alert('Ese id de producto ya está en la lista.');
                        inputCodigo.value = '';
                        scheduleFocusCodigo();
                        return;
                    }
                    codes.add(idNorm.toLowerCase());

                    var tr = document.createElement('tr');
                    var td1 = document.createElement('td');
                    td1.className = 'col-codigo';
                    td1.setAttribute('data-label', 'Código');
                    var td2 = document.createElement('td');
                    td2.setAttribute('data-label', 'Propietario');
                    var td3 = document.createElement('td');
                    td3.setAttribute('data-label', 'Destino');
                    var td4 = document.createElement('td');
                    td4.setAttribute('data-label', 'Fecha despacho');
                    var hiddenCod = document.createElement('input');
                    hiddenCod.type = 'hidden';
                    hiddenCod.name = 'codigos[]';
                    hiddenCod.className = 'inp-codigo-despacho';
                    hiddenCod.value = idNorm;
                    var divCod = document.createElement('div');
                    divCod.className = 'dispatch-cell dispatch-cell--codigo';
                    divCod.textContent = idNorm;
                    if (raw !== idNorm) {
                        divCod.title = 'Leído (código): ' + raw;
                    }
                    var divProp = dispatchCell(
                        propietarioTxt,
                        encontrado ? 'Sin propietario en inventario' : 'Sin fila en inventario',
                    );
                    var divDest = dispatchCell(
                        destinoTxt,
                        encontrado ? 'Sin destino registrado' : 'Sin fila en inventario',
                    );
                    var divFecha = dispatchCell(hoyTexto(), '');
                    var td5 = document.createElement('td');
                    td5.className = 'col-quitar';
                    var btnRetirar = document.createElement('button');
                    btnRetirar.type = 'button';
                    btnRetirar.className = 'btn-retirar-codigo';
                    btnRetirar.setAttribute('aria-label', 'Retirar código ' + idNorm + ' de este despacho');
                    btnRetirar.title = 'Retirar de la lista (no se dará de baja hasta terminar el despacho)';
                    btnRetirar.textContent = '\u00D7';
                    btnRetirar.addEventListener('click', function () {
                        var h = tr.querySelector('input.inp-codigo-despacho');
                        var k = (h && h.value ? h.value : '').trim().toLowerCase();
                        if (k) {
                            codes.delete(k);
                        }
                        tr.remove();
                        actualizarTotal();
                        scheduleFocusCodigo();
                    });
                    td5.appendChild(btnRetirar);
                    td1.appendChild(hiddenCod);
                    td1.appendChild(divCod);
                    td2.appendChild(divProp);
                    td3.appendChild(divDest);
                    td4.appendChild(divFecha);
                    tr.appendChild(td1);
                    tr.appendChild(td2);
                    tr.appendChild(td3);
                    tr.appendChild(td4);
                    tr.appendChild(td5);
                    tbody.appendChild(tr);
                    inputCodigo.value = '';
                    actualizarTotal();
                }

                if (esChecklist) {
                    checklistBindFilas();
                    inputCodigo.addEventListener('keydown', function (e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            void checklistOnScanEnter();
                        }
                    });
                } else {
                    inputCodigo.addEventListener('keydown', function (e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            void agregarFila();
                        }
                    });
                }

                btnTerminar.addEventListener('click', function () {
                    void terminarDespacho();
                });

                async function terminarDespacho() {
                    if (finalizarEnCurso) {
                        return;
                    }
                    function valCampoForm(id) {
                        var el = document.getElementById(id);
                        return el ? String(el.value || '').trim() : '';
                    }
                    var nCodigos = esChecklist
                        ? tbody.querySelectorAll('input.inp-codigo-despacho:not([disabled])').length
                        : tbody.querySelectorAll('tr').length;
                    if (nCodigos === 0) {
                        alert(esChecklist ? 'No hay códigos validados para despachar.' : 'No hay lenguas en el despacho.');
                        return;
                    }
                    if (esChecklist && tbody) {
                        var sinInvMsg = tbody.querySelectorAll('tr[data-checklist-row="1"][data-en-inv="0"]').length > 0;
                        var pendMsg =
                            tbody.querySelectorAll('tr[data-checklist-row="1"][data-en-inv="1"][data-alistado="0"]')
                                .length > 0;
                        if (sinInvMsg) {
                            alert(
                                'No puede cerrar el despacho: hay productos sin inventario local en este checklist.',
                            );
                            return;
                        }
                        if (pendMsg) {
                            alert('Debe escanear todas las lenguas pendientes antes de terminar el despacho.');
                            return;
                        }
                        if (!valCampoForm('empresa') || !valCampoForm('placa') || !valCampoForm('conductor')) {
                            alert(
                                'Indique operador logístico, placa y conductor (con el botón de listado o escribiendo) antes de terminar el despacho.',
                            );
                            return;
                        }
                    }
                    var msgConfirm =
                        '¿Confirma terminar el despacho con ' +
                        nCodigos +
                        ' lengua(s)?\n\nSe darán de baja del inventario local los registros cuyo id de producto coincida con cada código de la lista.';
                    if (esChecklist) {
                        msgConfirm +=
                            '\n\nEl servidor exige incluir al menos todos los códigos del checklist Colbeef de este vehículo; puede haber códigos adicionales.';
                    }
                    if (!confirm(msgConfirm)) {
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
                        if (inp.disabled) {
                            return;
                        }
                        fd.append('codigos[]', (inp.value || '').trim());
                    });
                    fd.append('empresa', valCampoForm('empresa'));
                    fd.append('conductor', valCampoForm('conductor'));
                    fd.append('placa', valCampoForm('placa'));
                    if (esChecklist) {
                        fd.append('id_vehiculo_asignado', String(Number(checklistVidRaw)));
                    }
                    finalizarEnCurso = true;
                    if (esChecklist) {
                        checklistActualizarBotonTerminar();
                    } else if (btnTerminar) {
                        btnTerminar.disabled = true;
                    }
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
                        if (json.redirect_url) {
                            window.location.href = json.redirect_url;
                            return;
                        }
                        tbody.innerHTML = '';
                        codes.clear();
                        actualizarTotal();
                        document.getElementById('empresa').value = '';
                        document.getElementById('placa').value = '';
                        document.getElementById('conductor').value = '';
                        scheduleFocusCodigo();
                    } catch (err) {
                        alert('Error de red o del servidor al actualizar el inventario.');
                    } finally {
                        finalizarEnCurso = false;
                        if (esChecklist) {
                            checklistActualizarBotonTerminar();
                        } else if (btnTerminar) {
                            btnTerminar.disabled = false;
                        }
                    }
                }
            })();
        </script>
    </body>
</html>
