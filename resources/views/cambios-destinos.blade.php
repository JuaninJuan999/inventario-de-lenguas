<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @include('partials.favicon')
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Destinos y etiquetas — {{ config('app.name') }}</title>
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
            .alert {
                margin: 0 0 1rem;
                padding: 0.65rem 0.85rem;
                border-radius: 8px;
                font-size: 0.85rem;
                font-weight: 600;
            }
            .alert--ok {
                border: 1px solid color-mix(in srgb, var(--brand-green) 50%, transparent);
                background: color-mix(in srgb, var(--brand-green) 12%, transparent);
                color: var(--text);
            }
            .alert--err {
                border: 1px solid color-mix(in srgb, #ff6b6b 55%, transparent);
                background: color-mix(in srgb, #ff6b6b 12%, transparent);
                color: #ffe0e0;
            }
            .filtros-form {
                margin: 0 0 1.15rem;
                padding: 1rem 1rem 1.05rem;
                border-radius: 0.75rem;
                border: 1px solid color-mix(in srgb, var(--brand-green) 28%, rgba(0, 0, 0, 0.45));
                background: rgba(0, 0, 0, 0.22);
            }
            .filtros-form__title {
                margin: 0 0 0.65rem;
                font-size: 0.72rem;
                font-weight: 800;
                letter-spacing: 0.06em;
                text-transform: uppercase;
                color: color-mix(in srgb, var(--brand-green) 85%, var(--text));
            }
            .filtros-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(14rem, 1fr));
                gap: 0.65rem 1rem;
                align-items: end;
            }
            .filtros-field label {
                display: block;
                font-size: 0.68rem;
                font-weight: 700;
                letter-spacing: 0.04em;
                text-transform: uppercase;
                margin-bottom: 0.28rem;
                opacity: 0.92;
            }
            .filtros-field input[type='text'] {
                width: 100%;
                padding: 0.42rem 0.45rem;
                font-size: 0.82rem;
                font-family: inherit;
                color: var(--text);
                border-radius: 6px;
                border: 1px solid color-mix(in srgb, var(--brand-green) 35%, rgba(0, 0, 0, 0.55));
                background: rgba(4, 14, 11, 0.92);
            }
            .filtros-field--check {
                display: flex;
                align-items: flex-end;
                padding-bottom: 0.15rem;
            }
            .check-adicionales {
                display: inline-flex;
                align-items: center;
                gap: 0.45rem;
                font-size: 0.8rem;
                font-weight: 600;
                cursor: pointer;
                user-select: none;
                position: relative;
                max-width: 100%;
            }
            .check-adicionales input {
                width: 1rem;
                height: 1rem;
                accent-color: var(--brand-green);
                cursor: pointer;
                flex-shrink: 0;
            }
            .check-adicionales__tip {
                position: relative;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 1.15rem;
                height: 1.15rem;
                margin-left: 0.1rem;
                flex-shrink: 0;
                border-radius: 999px;
                border: 1px solid color-mix(in srgb, var(--brand-green) 55%, rgba(255, 255, 255, 0.25));
                background: color-mix(in srgb, var(--brand-green) 22%, rgba(4, 14, 11, 0.9));
                color: #e8f6ef;
                font-size: 0.72rem;
                font-weight: 800;
                line-height: 1;
                cursor: help;
            }
            .check-adicionales__tip:focus {
                outline: 2px solid color-mix(in srgb, var(--brand-green) 70%, white);
                outline-offset: 2px;
            }
            .check-adicionales__bubble {
                position: absolute;
                left: 0;
                bottom: calc(100% + 0.45rem);
                z-index: 20;
                width: min(18.5rem, 78vw);
                padding: 0.55rem 0.7rem;
                border-radius: 8px;
                border: 1px solid color-mix(in srgb, var(--brand-green) 40%, rgba(0, 0, 0, 0.45));
                background: rgba(4, 14, 11, 0.97);
                color: #dceee5;
                font-size: 0.72rem;
                font-weight: 500;
                line-height: 1.35;
                letter-spacing: 0.01em;
                text-transform: none;
                box-shadow: 0 8px 22px rgba(0, 0, 0, 0.35);
                opacity: 0;
                visibility: hidden;
                pointer-events: none;
                transform: translateY(4px);
                transition: opacity 0.15s ease, transform 0.15s ease, visibility 0.15s;
            }
            .check-adicionales__bubble::after {
                content: '';
                position: absolute;
                top: 100%;
                left: 1.1rem;
                border: 6px solid transparent;
                border-top-color: color-mix(in srgb, var(--brand-green) 40%, rgba(0, 0, 0, 0.45));
            }
            .check-adicionales:hover .check-adicionales__bubble,
            .check-adicionales:focus-within .check-adicionales__bubble,
            .check-adicionales__tip:hover .check-adicionales__bubble,
            .check-adicionales__tip:focus .check-adicionales__bubble,
            .check-adicionales__tip.is-open .check-adicionales__bubble {
                opacity: 1;
                visibility: visible;
                transform: translateY(0);
            }
            .filtros-actions {
                display: flex;
                flex-wrap: wrap;
                gap: 0.5rem 0.65rem;
                align-items: center;
                margin-top: 0.85rem;
            }
            .filtros-actions-bar {
                display: flex;
                flex-wrap: wrap;
                gap: 0.45rem 0.55rem;
                align-items: center;
                width: 100%;
                margin-top: 0.75rem;
                padding-top: 0.75rem;
                border-top: 1px solid color-mix(in srgb, var(--brand-green) 22%, transparent);
            }
            .filtros-actions-bar__label {
                margin: 0 0.15rem 0 0;
                font-size: 0.65rem;
                font-weight: 800;
                letter-spacing: 0.06em;
                text-transform: uppercase;
                color: color-mix(in srgb, var(--brand-green) 80%, var(--text));
            }
            .btn-por-vencer {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0.42rem 1rem;
                font-size: 0.78rem;
                font-weight: 700;
                letter-spacing: 0.04em;
                text-transform: uppercase;
                text-decoration: none;
                color: #3a2a08;
                border: 1px solid color-mix(in srgb, #f5d78e 55%, #fff);
                border-radius: 6px;
                cursor: pointer;
                font-family: inherit;
                background: linear-gradient(145deg, #f5d78e, color-mix(in srgb, #f5d78e 70%, #c4a04a));
            }
            .btn-por-vencer:hover {
                filter: brightness(1.05);
            }
            .btn-por-vencer.is-active {
                box-shadow: 0 0 0 2px color-mix(in srgb, #f5d78e 45%, transparent);
            }
            .btn-marcar-adicional {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0.42rem 0.9rem;
                font-size: 0.75rem;
                font-weight: 700;
                letter-spacing: 0.04em;
                text-transform: uppercase;
                color: #2a1024;
                border: 1px solid color-mix(in srgb, var(--brand-rose) 55%, #fff);
                border-radius: 6px;
                cursor: pointer;
                font-family: inherit;
                background: linear-gradient(145deg, var(--brand-rose), color-mix(in srgb, var(--brand-rose) 70%, #c48bb8));
            }
            .btn-marcar-adicional:hover {
                filter: brightness(1.05);
            }
            .btn-desmarcar-adicional {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0.42rem 0.75rem;
                font-size: 0.72rem;
                font-weight: 700;
                letter-spacing: 0.03em;
                text-transform: uppercase;
                color: var(--text);
                border: 1px solid color-mix(in srgb, var(--text) 28%, transparent);
                border-radius: 6px;
                cursor: pointer;
                font-family: inherit;
                background: rgba(0, 0, 0, 0.28);
            }
            .btn-desmarcar-adicional:hover {
                border-color: color-mix(in srgb, var(--brand-rose) 50%, transparent);
            }
            .chip-adicional,
            .chip-salida {
                display: inline-block;
                margin-left: 0.35rem;
                padding: 0.12rem 0.4rem;
                font-size: 0.62rem;
                font-weight: 800;
                letter-spacing: 0.05em;
                text-transform: uppercase;
                border-radius: 9999px;
                vertical-align: middle;
            }
            .chip-adicional {
                color: #2a1024;
                background: color-mix(in srgb, var(--brand-rose) 82%, #fff);
            }
            .chip-salida {
                color: #04261c;
                background: color-mix(in srgb, var(--brand-green) 78%, #fff);
            }
            tr.row-adicional > td {
                background: color-mix(in srgb, var(--brand-rose) 8%, transparent);
            }
            tr.row-salida > td {
                box-shadow: inset 3px 0 0 color-mix(in srgb, var(--brand-green) 70%, transparent);
            }
            .col-check {
                width: 2.6rem;
                text-align: center;
                vertical-align: middle;
            }
            .col-check .col-check-label {
                display: block;
                margin: 0 0 0.2rem;
                font-size: 0.62rem;
                font-weight: 800;
                letter-spacing: 0.06em;
                text-transform: uppercase;
                line-height: 1.1;
                color: color-mix(in srgb, var(--text) 72%, transparent);
            }
            .col-check input {
                width: 1rem;
                height: 1rem;
                accent-color: var(--brand-green);
                cursor: pointer;
            }
            .sel-count {
                margin: 0;
                font-size: 0.78rem;
                color: color-mix(in srgb, var(--text) 68%, transparent);
            }
            .btn-marcar-salida {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0.42rem 0.9rem;
                font-size: 0.75rem;
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
            .btn-marcar-salida:hover {
                filter: brightness(1.05);
            }
            .btn-desmarcar-salida {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0.42rem 0.9rem;
                font-size: 0.75rem;
                font-weight: 700;
                letter-spacing: 0.04em;
                text-transform: uppercase;
                color: #dceee5;
                border: 1px solid color-mix(in srgb, var(--brand-green) 35%, rgba(0, 0, 0, 0.45));
                border-radius: 6px;
                cursor: pointer;
                font-family: inherit;
                background: rgba(4, 14, 11, 0.75);
            }
            .btn-desmarcar-salida:hover {
                border-color: color-mix(in srgb, var(--brand-green) 55%, #fff);
            }
            .btn-marcar-adicional:disabled,
            .btn-desmarcar-adicional:disabled,
            .btn-marcar-salida:disabled,
            .btn-desmarcar-salida:disabled {
                opacity: 0.45;
                cursor: not-allowed;
                filter: none;
            }
            .btn-filtrar {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0.42rem 1rem;
                font-size: 0.78rem;
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
            .btn-filtrar:hover {
                filter: brightness(1.06);
            }
            .link-limpiar {
                font-size: 0.82rem;
                font-weight: 600;
                color: var(--brand-rose);
                text-decoration: none;
                padding: 0.38rem 0.65rem;
                border-radius: 9999px;
                border: 1px solid color-mix(in srgb, var(--brand-rose) 38%, transparent);
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
            .col-producto {
                white-space: nowrap;
            }
            .col-prop {
                width: 1%;
                white-space: nowrap;
                vertical-align: middle;
            }
            .col-destino {
                max-width: 16rem;
                word-break: break-word;
                overflow-wrap: anywhere;
            }
            .destino-input {
                width: 100%;
                min-width: 12rem;
                min-height: 3.25rem;
                padding: 0.4rem 0.45rem;
                font-size: 0.8rem;
                font-family: inherit;
                color: var(--text);
                border-radius: 6px;
                border: 1px solid color-mix(in srgb, var(--brand-green) 35%, rgba(0, 0, 0, 0.55));
                background: rgba(4, 14, 11, 0.92);
                resize: vertical;
            }
            .destino-input.is-dirty {
                border-color: color-mix(in srgb, var(--brand-rose) 65%, transparent);
                box-shadow: 0 0 0 1px color-mix(in srgb, var(--brand-rose) 35%, transparent);
            }
            tr.row-pendiente > td {
                background: color-mix(in srgb, var(--brand-rose) 10%, transparent);
            }
            tr.row-pendiente .col-producto {
                font-weight: 800;
                text-decoration: underline;
                text-underline-offset: 3px;
                color: #ffe8f7;
            }
            .btn-guardar {
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
                white-space: nowrap;
            }
            .btn-guardar:hover:not(:disabled) {
                filter: brightness(1.06);
            }
            .btn-guardar:disabled {
                opacity: 0.45;
                cursor: not-allowed;
                filter: none;
            }
            .btn-guardar--masivo {
                padding: 0.42rem 1rem;
                font-size: 0.78rem;
            }
            .bulk-actions-bar {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
                gap: 0.65rem;
                margin: 0.85rem 0 0;
            }
            .pendientes-hint {
                margin: 0;
                font-size: 0.78rem;
                font-weight: 600;
                color: color-mix(in srgb, var(--brand-rose) 88%, #fff);
            }
            .pendientes-hint[hidden] {
                display: none;
            }
            .listado-filtro-rapido {
                display: flex;
                flex-wrap: wrap;
                align-items: flex-end;
                gap: 0.5rem 0.85rem;
                margin: 0 0 0.85rem;
                padding: 0.75rem 0.85rem;
                border-radius: 0.65rem;
                border: 1px solid color-mix(in srgb, var(--brand-green) 24%, transparent);
                background: rgba(0, 0, 0, 0.18);
            }
            .listado-filtro-rapido label {
                display: block;
                font-size: 0.68rem;
                font-weight: 700;
                letter-spacing: 0.04em;
                text-transform: uppercase;
                margin-bottom: 0.28rem;
                opacity: 0.92;
            }
            .listado-filtro-rapido input[type='search'] {
                min-width: min(100%, 18rem);
                width: 18rem;
                max-width: 100%;
                padding: 0.42rem 0.5rem;
                font-size: 0.85rem;
                font-family: inherit;
                color: var(--text);
                border-radius: 6px;
                border: 1px solid color-mix(in srgb, var(--brand-green) 40%, rgba(0, 0, 0, 0.55));
                background: rgba(4, 14, 11, 0.92);
            }
            .listado-filtro-rapido input[type='search']:focus {
                outline: 2px solid var(--brand-green);
                outline-offset: 1px;
            }
            .listado-filtro-rapido .filtro-rapido-meta {
                margin: 0;
                font-size: 0.78rem;
                color: color-mix(in srgb, var(--text) 65%, transparent);
            }
            tr.row-filtrada-oculto {
                display: none;
            }
            .check-adicional-row.is-flag-dirty,
            .check-salida-row.is-flag-dirty {
                outline: 2px solid color-mix(in srgb, var(--brand-rose) 80%, #fff);
                outline-offset: 2px;
            }
            .cambios-backdrop {
                position: fixed;
                inset: 0;
                z-index: 80;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 1rem;
                background: rgba(2, 8, 6, 0.72);
                backdrop-filter: blur(2px);
            }
            .cambios-backdrop[hidden] {
                display: none !important;
            }
            .cambios-dialog {
                width: min(100%, 26rem);
                border-radius: 0.9rem;
                border: 1px solid color-mix(in srgb, var(--brand-green) 40%, transparent);
                background: linear-gradient(165deg, rgba(10, 28, 22, 0.98), rgba(6, 16, 13, 0.98));
                box-shadow: 0 18px 48px rgba(0, 0, 0, 0.45);
                color: var(--text);
            }
            .cambios-dialog__head {
                padding: 1rem 1.1rem 0.35rem;
            }
            .cambios-dialog__head h2 {
                margin: 0;
                font-size: 1.05rem;
                font-weight: 750;
            }
            .cambios-dialog__body {
                padding: 0.35rem 1.1rem 0.85rem;
            }
            .cambios-dialog__lead {
                margin: 0 0 0.55rem;
                font-size: 0.88rem;
                opacity: 0.9;
            }
            .cambios-dialog__list {
                margin: 0;
                padding: 0.55rem 0.85rem 0.55rem 1.35rem;
                border-radius: 0.55rem;
                background: rgba(0, 0, 0, 0.28);
                border: 1px solid color-mix(in srgb, var(--brand-green) 22%, transparent);
                font-size: 0.88rem;
                line-height: 1.45;
            }
            .cambios-dialog__list li + li {
                margin-top: 0.28rem;
            }
            .cambios-dialog__foot {
                display: flex;
                flex-wrap: wrap;
                justify-content: flex-end;
                gap: 0.55rem;
                padding: 0 1.1rem 1.05rem;
            }
            .btn-cambios-cancel {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0.42rem 0.95rem;
                font-size: 0.78rem;
                font-weight: 700;
                letter-spacing: 0.03em;
                text-transform: uppercase;
                font-family: inherit;
                color: var(--text);
                border-radius: 6px;
                cursor: pointer;
                border: 1px solid color-mix(in srgb, var(--text) 28%, transparent);
                background: rgba(255, 255, 255, 0.06);
            }
            .btn-cambios-cancel:hover {
                background: rgba(255, 255, 255, 0.12);
            }
            .sr-only {
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
        </style>
    </head>
    <body>
        @include('partials.logo-institucional')
        <header class="page-bar">
            <div>
                <h1>Destinos y etiquetas</h1>
                <p class="sub">
                    Busque lenguas del <strong>inventario disponible</strong> (sin despachar) por <strong>ID producto</strong>,
                    <strong>propietario</strong>, <strong>solo adicionales</strong> o <strong>solo salidas</strong>;
                    edite el <strong>destino</strong> y active/desactive <strong>ADI.</strong> o
                    <strong>SAL.</strong>; un solo <strong>Guardar cambios</strong> aplica todo.
                </p>
            </div>
            <a class="link-menu" href="{{ route('menu') }}">← Menú</a>
        </header>

        <section class="sheet">
            @if (session('cambios_destinos_ok'))
                <p class="alert alert--ok" role="status">{{ session('cambios_destinos_ok') }}</p>
            @endif
            @if (session('cambios_destinos_error'))
                <p class="alert alert--err" role="alert">{{ session('cambios_destinos_error') }}</p>
            @endif

            <form class="filtros-form" id="filtros-cambios" method="get" action="" aria-labelledby="filtros-cambios-heading">
                <h2 id="filtros-cambios-heading" class="filtros-form__title">Filtros de búsqueda</h2>
                <div class="filtros-grid">
                    <div class="filtros-field">
                        <label for="filtro-id-producto">ID producto</label>
                        <input
                            id="filtro-id-producto"
                            type="text"
                            name="id_producto"
                            value="{{ $filters['id_producto'] }}"
                            maxlength="80"
                            autocomplete="off"
                            placeholder="Parcial o completo"
                        />
                    </div>
                    <div class="filtros-field">
                        <label for="filtro-propietario">Propietario</label>
                        <input
                            id="filtro-propietario"
                            type="text"
                            name="propietario"
                            value="{{ $filters['propietario'] }}"
                            maxlength="200"
                            autocomplete="off"
                            placeholder="Parcial o completo"
                        />
                    </div>
                    <div class="filtros-field filtros-field--check">
                        <label
                            class="check-adicionales"
                            for="filtro-solo-adicionales"
                            title="Filtra la búsqueda: solo lenguas ya marcadas como Adicional."
                        >
                            <input
                                id="filtro-solo-adicionales"
                                type="checkbox"
                                name="solo_adicionales"
                                value="1"
                                @checked(! empty($filters['solo_adicionales']))
                                aria-describedby="hint-solo-adicionales"
                            />
                            Solo etiquetados como adicionales
                            <span
                                class="check-adicionales__tip"
                                tabindex="0"
                                role="button"
                                aria-label="Qué hace esta opción"
                                aria-describedby="hint-solo-adicionales"
                            >
                                ?
                                <span class="check-adicionales__bubble" id="hint-solo-adicionales" role="tooltip">
                                    Al activarlo, la búsqueda lista <strong>solo</strong> las lenguas que ya tienen
                                    la etiqueta <strong>Adicional</strong>. Desmárquelo para ver también el resto del
                                    inventario disponible (según código o propietario).
                                </span>
                            </span>
                        </label>
                    </div>
                    <div class="filtros-field filtros-field--check">
                        <label
                            class="check-adicionales"
                            for="filtro-solo-salidas"
                            title="Filtra la búsqueda: solo lenguas ya marcadas como Salida."
                        >
                            <input
                                id="filtro-solo-salidas"
                                type="checkbox"
                                name="solo_salidas"
                                value="1"
                                @checked(! empty($filters['solo_salidas']))
                                aria-describedby="hint-solo-salidas"
                            />
                            Solo etiquetados como salidas
                            <span
                                class="check-adicionales__tip"
                                tabindex="0"
                                role="button"
                                aria-label="Qué hace esta opción"
                                aria-describedby="hint-solo-salidas"
                            >
                                ?
                                <span class="check-adicionales__bubble" id="hint-solo-salidas" role="tooltip">
                                    Al activarlo, la búsqueda lista <strong>solo</strong> las lenguas que ya tienen
                                    la etiqueta <strong>Salida</strong>. Desmárquelo para ver también el resto del
                                    inventario disponible (según código o propietario).
                                </span>
                            </span>
                        </label>
                    </div>
                    @if (! empty($filters['por_vencer']))
                        <input type="hidden" name="por_vencer" value="1" />
                    @endif
                </div>
                <div class="filtros-actions">
                    <button type="submit" class="btn-filtrar">Buscar en inventario</button>
                    <a
                        class="btn-por-vencer{{ ! empty($filters['por_vencer']) ? ' is-active' : '' }}"
                        href="/cambios-de-destinos?_=1&por_vencer=1"
                        title="Lista lenguas disponibles que vencen hoy o mañana (vida útil 4 días)"
                    >
                        Próximas a vencer
                    </a>
                    @if ($filtrosActivos)
                        <a class="link-limpiar" href="/cambios-de-destinos">Limpiar</a>
                    @endif
                </div>
                @if ($filtrosActivos && $rows->isNotEmpty())
                    <div class="filtros-actions-bar" role="toolbar" aria-label="Acciones del listado">
                        <span class="filtros-actions-bar__label">Acciones</span>
                        <button
                            type="button"
                            class="btn-guardar btn-guardar--masivo"
                            id="btn-guardar-masivo-top"
                            disabled
                        >
                            Guardar cambios
                        </button>
                        <p class="pendientes-hint" id="pendientes-hint-top" hidden>
                            <span class="js-pendientes-resumen">Sin cambios</span>
                        </p>
                    </div>
                @endif
            </form>

            @if (! $filtrosActivos)
                <p class="muted">
                    Indique un criterio (<strong>ID producto</strong>, <strong>propietario</strong>,
                    <strong>solo adicionales</strong>, <strong>solo salidas</strong>) o pulse
                    <strong>Próximas a vencer</strong>. Solo se listan lenguas aún disponibles (no despachadas).
                    En búsquedas normales, máximo {{ number_format($listadoLimit, 0, ',', '.') }} filas;
                    <strong>Próximas a vencer</strong> lista todas.
                </p>
            @elseif ($rows->isEmpty())
                <p class="muted">
                    @if (! empty($filters['por_vencer']))
                        No hay lenguas <strong>próximas a vencer</strong> (hoy o mañana) disponibles en inventario
                        con los demás filtros aplicados.
                    @else
                        No hay registros que coincidan. Este módulo usa la misma base de datos local que
                        <strong>Inventario de lenguas</strong> y solo muestra filas <strong>disponibles</strong> (sin
                        despachar). Si ese ID ya salió en un despacho, no se listará aquí tampoco.
                    @endif
                </p>
            @else
                <p class="muted">
                    <strong>{{ number_format($totalCoincidencias, 0, ',', '.') }}</strong> registro(s) encontrado(s)
                    @if (! empty($filters['por_vencer']))
                        (próximas a vencer: hoy o mañana)
                    @endif
                    @if (! empty($filters['solo_adicionales']) && ! empty($filters['solo_salidas']))
                        · solo adicionales y salidas
                    @elseif (! empty($filters['solo_adicionales']))
                        · solo adicionales
                    @elseif (! empty($filters['solo_salidas']))
                        · solo salidas
                    @endif
                    @if (! empty($listadoLimitado))
                        · listando las primeras <strong>{{ number_format($rows->count(), 0, ',', '.') }}</strong>
                        (máx. {{ number_format($listadoLimit, 0, ',', '.') }})
                    @endif
                    . Edite destinos o active/desactive <strong>ADI.</strong> / <strong>SAL.</strong> en cada fila;
                    luego pulse <strong>Guardar cambios</strong> (arriba o abajo) para confirmar todo junto.
                </p>

                <div class="listado-filtro-rapido">
                    <div>
                        <label for="filtro-rapido-id">Filtrar ID producto (en este listado)</label>
                        <input
                            id="filtro-rapido-id"
                            type="search"
                            inputmode="text"
                            autocomplete="off"
                            spellcheck="false"
                            placeholder="Escriba el código…"
                            aria-controls="tabla-destinos-body"
                        />
                    </div>
                    <p class="filtro-rapido-meta" id="filtro-rapido-meta" aria-live="polite">
                        Mostrando <strong id="filtro-rapido-visibles">{{ $rows->count() }}</strong> de
                        <strong>{{ $rows->count() }}</strong> en pantalla
                        @if (($totalCoincidencias ?? 0) > $rows->count())
                            · total coincidencias: <strong>{{ number_format($totalCoincidencias, 0, ',', '.') }}</strong>
                        @endif
                        · Pendientes: <strong id="filtro-rapido-editadas">0</strong>
                        · ADI.: <strong>{{ $rows->where('es_adicional', true)->count() }}</strong>
                        · SAL.: <strong>{{ $rows->where('es_salida', true)->count() }}</strong>
                    </p>
                </div>

                <form
                    id="form-destinos-masivo"
                    method="post"
                    action="{{ route('cambios.destinos.guardar') }}"
                    data-dirty-tracker="1"
                >
                    @csrf
                    <input type="hidden" name="id_producto" value="{{ $filters['id_producto'] }}" />
                    <input type="hidden" name="propietario" value="{{ $filters['propietario'] }}" />
                    @if (! empty($filters['solo_adicionales']))
                        <input type="hidden" name="solo_adicionales" value="1" />
                    @endif
                    @if (! empty($filters['solo_salidas']))
                        <input type="hidden" name="solo_salidas" value="1" />
                    @endif
                    @if (! empty($filters['por_vencer']))
                        <input type="hidden" name="por_vencer" value="1" />
                    @endif

                    <div class="table-wrap">
                        <table class="data">
                            <thead>
                                <tr>
                                    <th class="col-check" scope="col" title="Adicional — estado deseado (visibles)">
                                        <span class="col-check-label" aria-hidden="true">ADI.</span>
                                        <label class="sr-only" for="check-todos-adicionales">Marcar ADI. en visibles</label>
                                        <input
                                            type="checkbox"
                                            id="check-todos-adicionales"
                                            title="ADI. — activar/desactivar en filas visibles"
                                        />
                                    </th>
                                    <th class="col-producto">ID producto</th>
                                    <th class="col-prop">Propietario</th>
                                    <th>Ref. turno</th>
                                    <th class="col-destino">Destino</th>
                                    <th class="col-check col-check--salida" scope="col" title="Salida — estado deseado (visibles)">
                                        <span class="col-check-label" aria-hidden="true">SAL.</span>
                                        <label class="sr-only" for="check-todos-salidas">Marcar SAL. en visibles</label>
                                        <input
                                            type="checkbox"
                                            id="check-todos-salidas"
                                            title="SAL. — activar/desactivar en filas visibles"
                                        />
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="tabla-destinos-body">
                                @foreach ($rows as $row)
                                    @php
                                        $destinoValor = (string) ($row->destino ?? '');
                                        $esAdicional = (bool) $row->es_adicional;
                                        $esSalida = (bool) $row->es_salida;
                                    @endphp
                                    <tr
                                        data-row-id="{{ $row->id }}"
                                        data-id-producto="{{ strtolower($row->id_producto) }}"
                                        @class(['row-adicional' => $esAdicional, 'row-salida' => $esSalida])
                                    >
                                        <td class="col-check">
                                            <input
                                                type="checkbox"
                                                class="check-adicional-row"
                                                value="{{ $row->id }}"
                                                data-original="{{ $esAdicional ? '1' : '0' }}"
                                                @checked($esAdicional)
                                                aria-label="ADI. {{ $row->id_producto }}"
                                                title="Marcar o quitar Adicional (se guarda con Guardar cambios)"
                                            />
                                        </td>
                                        <td class="col-producto">
                                            {{ $row->id_producto }}
                                            @if ($esAdicional)
                                                <span class="chip-adicional">Adicional</span>
                                            @endif
                                            @if ($esSalida)
                                                <span class="chip-salida">Salida</span>
                                            @endif
                                        </td>
                                        <td class="col-prop">{{ $row->propietario !== null && $row->propietario !== '' ? $row->propietario : '—' }}</td>
                                        <td>
                                            {{ $row->fecha_turno_referencia?->format('d/m/Y') ?? '—' }}
                                        </td>
                                        <td class="col-destino">
                                            <label class="sr-only" for="destino-{{ $row->id }}">Destino para {{ $row->id_producto }}</label>
                                            <textarea
                                                id="destino-{{ $row->id }}"
                                                class="destino-input"
                                                name="destinos[{{ $row->id }}]"
                                                rows="2"
                                                maxlength="60000"
                                                data-original="{{ $destinoValor }}"
                                            >{{ $destinoValor }}</textarea>
                                        </td>
                                        <td class="col-check col-check--salida">
                                            <input
                                                type="checkbox"
                                                class="check-salida-row"
                                                value="{{ $row->id }}"
                                                data-original="{{ $esSalida ? '1' : '0' }}"
                                                @checked($esSalida)
                                                aria-label="SAL. {{ $row->id_producto }}"
                                                title="Marcar o quitar Salida (se guarda con Guardar cambios)"
                                            />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="bulk-actions-bar">
                        <p class="pendientes-hint" id="pendientes-hint-bottom" hidden>
                            <span class="js-pendientes-resumen">Sin cambios</span>
                        </p>
                        <button
                            type="button"
                            class="btn-guardar btn-guardar--masivo"
                            id="btn-guardar-masivo-bottom"
                            disabled
                        >
                            Guardar cambios
                        </button>
                    </div>
                </form>
            @endif
        </section>

        <div class="cambios-backdrop" id="cambios-confirm-backdrop" role="presentation" aria-hidden="true" hidden>
            <div
                class="cambios-dialog"
                role="dialog"
                aria-modal="true"
                aria-labelledby="cambios-confirm-title"
                id="cambios-confirm-dialog"
            >
                <div class="cambios-dialog__head">
                    <h2 id="cambios-confirm-title">¿Guardar cambios?</h2>
                </div>
                <div class="cambios-dialog__body">
                    <p class="cambios-dialog__lead">Se aplicarán al inventario disponible:</p>
                    <ul class="cambios-dialog__list" id="cambios-confirm-list"></ul>
                </div>
                <div class="cambios-dialog__foot">
                    <button type="button" class="btn-cambios-cancel" id="cambios-confirm-cancel">Cancelar</button>
                    <button type="button" class="btn-guardar btn-guardar--masivo" id="cambios-confirm-ok">
                        Confirmar y guardar
                    </button>
                </div>
            </div>
        </div>
        <script>
            (function () {
                var tips = document.querySelectorAll('.check-adicionales__tip');
                if (!tips.length) {
                    return;
                }
                tips.forEach(function (tip) {
                    tip.addEventListener('click', function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        tips.forEach(function (other) {
                            if (other !== tip) {
                                other.classList.remove('is-open');
                            }
                        });
                        tip.classList.toggle('is-open');
                        tip.focus();
                    });
                    tip.addEventListener('keydown', function (e) {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            tip.classList.toggle('is-open');
                        }
                        if (e.key === 'Escape') {
                            tip.classList.remove('is-open');
                        }
                    });
                });
                document.addEventListener('click', function (e) {
                    tips.forEach(function (tip) {
                        if (!tip.contains(e.target)) {
                            tip.classList.remove('is-open');
                        }
                    });
                });
            })();
        </script>
        @if ($filtrosActivos && $rows->isNotEmpty())
            <script>
                (function () {
                    var form = document.getElementById('form-destinos-masivo');
                    if (!form) {
                        return;
                    }

                    var buttons = [
                        document.getElementById('btn-guardar-masivo-top'),
                        document.getElementById('btn-guardar-masivo-bottom'),
                    ].filter(Boolean);
                    var hints = document.querySelectorAll('.pendientes-hint');
                    var resumenEls = document.querySelectorAll('.js-pendientes-resumen');
                    var editadasEl = document.getElementById('filtro-rapido-editadas');
                    var tbody = document.getElementById('tabla-destinos-body');
                    var checkTodos = document.getElementById('check-todos-adicionales');
                    var checkTodosSalidas = document.getElementById('check-todos-salidas');
                    var filtroRapido = document.getElementById('filtro-rapido-id');
                    var visiblesEl = document.getElementById('filtro-rapido-visibles');
                    var backdrop = document.getElementById('cambios-confirm-backdrop');
                    var listEl = document.getElementById('cambios-confirm-list');
                    var btnCancel = document.getElementById('cambios-confirm-cancel');
                    var btnOk = document.getElementById('cambios-confirm-ok');
                    var submitting = false;

                    function norm(v) {
                        return String(v == null ? '' : v).replace(/\r\n/g, '\n').trim();
                    }

                    function flagDirty(cb) {
                        var original = cb.getAttribute('data-original') === '1';
                        return cb.checked !== original;
                    }

                    function filasVisibles() {
                        if (!tbody) {
                            return [];
                        }
                        return Array.prototype.slice.call(
                            tbody.querySelectorAll('tr[data-id-producto]:not(.row-filtrada-oculto)'),
                        );
                    }

                    function collectChanges() {
                        var destinos = 0;
                        var adiMarcar = 0;
                        var adiQuitar = 0;
                        var salMarcar = 0;
                        var salQuitar = 0;

                        form.querySelectorAll('textarea.destino-input').forEach(function (ta) {
                            if (norm(ta.value) !== norm(ta.getAttribute('data-original'))) {
                                destinos++;
                            }
                        });

                        form.querySelectorAll('.check-adicional-row').forEach(function (cb) {
                            if (!flagDirty(cb)) {
                                return;
                            }
                            if (cb.checked) {
                                adiMarcar++;
                            } else {
                                adiQuitar++;
                            }
                        });

                        form.querySelectorAll('.check-salida-row').forEach(function (cb) {
                            if (!flagDirty(cb)) {
                                return;
                            }
                            if (cb.checked) {
                                salMarcar++;
                            } else {
                                salQuitar++;
                            }
                        });

                        return {
                            destinos: destinos,
                            adiMarcar: adiMarcar,
                            adiQuitar: adiQuitar,
                            salMarcar: salMarcar,
                            salQuitar: salQuitar,
                            total: destinos + adiMarcar + adiQuitar + salMarcar + salQuitar,
                        };
                    }

                    function resumenTexto(c) {
                        if (c.total === 0) {
                            return 'Sin cambios';
                        }
                        var partes = [];
                        if (c.destinos > 0) {
                            partes.push(
                                c.destinos === 1 ? '1 destino' : c.destinos + ' destinos',
                            );
                        }
                        if (c.adiMarcar > 0) {
                            partes.push(
                                c.adiMarcar === 1
                                    ? '1 ADI. a marcar'
                                    : c.adiMarcar + ' ADI. a marcar',
                            );
                        }
                        if (c.adiQuitar > 0) {
                            partes.push(
                                c.adiQuitar === 1
                                    ? '1 ADI. a quitar'
                                    : c.adiQuitar + ' ADI. a quitar',
                            );
                        }
                        if (c.salMarcar > 0) {
                            partes.push(
                                c.salMarcar === 1
                                    ? '1 SAL. a marcar'
                                    : c.salMarcar + ' SAL. a marcar',
                            );
                        }
                        if (c.salQuitar > 0) {
                            partes.push(
                                c.salQuitar === 1
                                    ? '1 SAL. a quitar'
                                    : c.salQuitar + ' SAL. a quitar',
                            );
                        }
                        return partes.join(' · ');
                    }

                    function syncRowState(tr) {
                        if (!tr) {
                            return;
                        }
                        var ta = tr.querySelector('textarea.destino-input');
                        var cbAdi = tr.querySelector('.check-adicional-row');
                        var cbSal = tr.querySelector('.check-salida-row');
                        var dirtyDest =
                            ta &&
                            norm(ta.value) !== norm(ta.getAttribute('data-original'));
                        var dirtyAdi = cbAdi ? flagDirty(cbAdi) : false;
                        var dirtySal = cbSal ? flagDirty(cbSal) : false;

                        if (ta) {
                            ta.classList.toggle('is-dirty', dirtyDest);
                            var rowId = tr.getAttribute('data-row-id') || '';
                            if (dirtyDest && rowId) {
                                ta.setAttribute('name', 'destinos[' + rowId + ']');
                            } else {
                                ta.removeAttribute('name');
                            }
                        }
                        if (cbAdi) {
                            cbAdi.classList.toggle('is-flag-dirty', dirtyAdi);
                        }
                        if (cbSal) {
                            cbSal.classList.toggle('is-flag-dirty', dirtySal);
                        }
                        tr.classList.toggle('row-pendiente', dirtyDest || dirtyAdi || dirtySal);
                    }

                    function syncHeaderCheck(rowClass, checkTodosEl) {
                        if (!checkTodosEl) {
                            return;
                        }
                        var checks = filasVisibles()
                            .map(function (tr) {
                                return tr.querySelector('.' + rowClass);
                            })
                            .filter(Boolean);
                        var checked = checks.filter(function (c) {
                            return c.checked;
                        }).length;
                        checkTodosEl.checked = checks.length > 0 && checked === checks.length;
                        checkTodosEl.indeterminate = checked > 0 && checked < checks.length;
                    }

                    function clearFlagHiddens() {
                        form.querySelectorAll('input.js-flag-payload').forEach(function (el) {
                            el.remove();
                        });
                    }

                    function injectFlagHiddens() {
                        clearFlagHiddens();
                        form.querySelectorAll('.check-adicional-row').forEach(function (cb) {
                            if (!flagDirty(cb)) {
                                return;
                            }
                            var input = document.createElement('input');
                            input.type = 'hidden';
                            input.className = 'js-flag-payload';
                            input.name = 'adicionales[' + cb.value + ']';
                            input.value = cb.checked ? '1' : '0';
                            form.appendChild(input);
                        });
                        form.querySelectorAll('.check-salida-row').forEach(function (cb) {
                            if (!flagDirty(cb)) {
                                return;
                            }
                            var input = document.createElement('input');
                            input.type = 'hidden';
                            input.className = 'js-flag-payload';
                            input.name = 'salidas[' + cb.value + ']';
                            input.value = cb.checked ? '1' : '0';
                            form.appendChild(input);
                        });
                    }

                    function refresh() {
                        if (tbody) {
                            tbody.querySelectorAll('tr[data-row-id]').forEach(syncRowState);
                        }
                        var c = collectChanges();
                        var texto = resumenTexto(c);
                        buttons.forEach(function (btn) {
                            btn.disabled = c.total === 0;
                        });
                        hints.forEach(function (h) {
                            h.hidden = c.total === 0;
                        });
                        resumenEls.forEach(function (el) {
                            el.textContent = texto;
                        });
                        if (editadasEl) {
                            editadasEl.textContent = String(c.total);
                        }
                        syncHeaderCheck('check-adicional-row', checkTodos);
                        syncHeaderCheck('check-salida-row', checkTodosSalidas);
                    }

                    function openModal(c) {
                        if (!backdrop || !listEl) {
                            return;
                        }
                        var items = [];
                        if (c.destinos > 0) {
                            items.push(
                                c.destinos === 1
                                    ? '1 destino modificado'
                                    : c.destinos + ' destinos modificados',
                            );
                        }
                        if (c.adiMarcar > 0) {
                            items.push(
                                c.adiMarcar === 1
                                    ? '1 código a marcar como ADI.'
                                    : c.adiMarcar + ' códigos a marcar como ADI.',
                            );
                        }
                        if (c.adiQuitar > 0) {
                            items.push(
                                c.adiQuitar === 1
                                    ? '1 código a quitar ADI.'
                                    : c.adiQuitar + ' códigos a quitar ADI.',
                            );
                        }
                        if (c.salMarcar > 0) {
                            items.push(
                                c.salMarcar === 1
                                    ? '1 código a marcar como SAL.'
                                    : c.salMarcar + ' códigos a marcar como SAL.',
                            );
                        }
                        if (c.salQuitar > 0) {
                            items.push(
                                c.salQuitar === 1
                                    ? '1 código a quitar SAL.'
                                    : c.salQuitar + ' códigos a quitar SAL.',
                            );
                        }
                        listEl.innerHTML = items
                            .map(function (t) {
                                return '<li>' + t + '</li>';
                            })
                            .join('');
                        backdrop.hidden = false;
                        backdrop.removeAttribute('hidden');
                        backdrop.setAttribute('aria-hidden', 'false');
                        document.body.style.overflow = 'hidden';
                        if (btnOk) {
                            btnOk.focus();
                        }
                    }

                    function closeModal() {
                        if (!backdrop) {
                            return;
                        }
                        backdrop.hidden = true;
                        backdrop.setAttribute('hidden', 'hidden');
                        backdrop.setAttribute('aria-hidden', 'true');
                        document.body.style.overflow = '';
                    }

                    function pedirGuardar() {
                        refresh();
                        var c = collectChanges();
                        if (c.total === 0) {
                            alert('No hay cambios para guardar.');
                            return;
                        }
                        openModal(c);
                    }

                    function confirmarGuardar() {
                        if (submitting) {
                            return;
                        }
                        refresh();
                        var c = collectChanges();
                        if (c.total === 0) {
                            closeModal();
                            alert('No hay cambios para guardar.');
                            return;
                        }
                        injectFlagHiddens();
                        submitting = true;
                        closeModal();
                        form.submit();
                    }

                    form.querySelectorAll('textarea.destino-input').forEach(function (ta) {
                        ta.removeAttribute('name');
                        ta.addEventListener('input', refresh);
                        ta.addEventListener('change', refresh);
                        ta.addEventListener('blur', refresh);
                    });

                    form.addEventListener('submit', function (e) {
                        if (submitting) {
                            return;
                        }
                        e.preventDefault();
                        pedirGuardar();
                    });

                    buttons.forEach(function (btn) {
                        btn.addEventListener('click', pedirGuardar);
                    });

                    if (btnCancel) {
                        btnCancel.addEventListener('click', closeModal);
                    }
                    if (btnOk) {
                        btnOk.addEventListener('click', confirmarGuardar);
                    }
                    if (backdrop) {
                        backdrop.addEventListener('click', function (e) {
                            if (e.target === backdrop) {
                                closeModal();
                            }
                        });
                    }
                    document.addEventListener('keydown', function (e) {
                        if (e.key === 'Escape' && backdrop && !backdrop.hidden) {
                            closeModal();
                        }
                    });

                    if (filtroRapido && tbody) {
                        function aplicarFiltroRapido() {
                            var q = String(filtroRapido.value || '')
                                .trim()
                                .toLowerCase()
                                .replace(/[\s]+/g, '');
                            var visibles = 0;
                            tbody.querySelectorAll('tr[data-id-producto]').forEach(function (tr) {
                                var id = String(tr.getAttribute('data-id-producto') || '').replace(
                                    /[\s]+/g,
                                    '',
                                );
                                var ok = q === '' || id.indexOf(q) !== -1;
                                tr.classList.toggle('row-filtrada-oculto', !ok);
                                if (ok) {
                                    visibles++;
                                }
                            });
                            if (visiblesEl) {
                                visiblesEl.textContent = String(visibles);
                            }
                            refresh();
                        }
                        filtroRapido.addEventListener('input', aplicarFiltroRapido);
                        filtroRapido.addEventListener('search', aplicarFiltroRapido);
                    }

                    if (tbody) {
                        tbody.addEventListener('change', function (e) {
                            if (!e.target) {
                                return;
                            }
                            if (
                                e.target.classList.contains('check-adicional-row') ||
                                e.target.classList.contains('check-salida-row')
                            ) {
                                refresh();
                            }
                        });
                    }

                    if (checkTodos) {
                        checkTodos.addEventListener('change', function () {
                            var on = checkTodos.checked;
                            filasVisibles().forEach(function (tr) {
                                var cb = tr.querySelector('.check-adicional-row');
                                if (cb) {
                                    cb.checked = on;
                                }
                            });
                            refresh();
                        });
                    }

                    if (checkTodosSalidas) {
                        checkTodosSalidas.addEventListener('change', function () {
                            var on = checkTodosSalidas.checked;
                            filasVisibles().forEach(function (tr) {
                                var cb = tr.querySelector('.check-salida-row');
                                if (cb) {
                                    cb.checked = on;
                                }
                            });
                            refresh();
                        });
                    }

                    refresh();
                })();
            </script>
        @endif
    </body>
</html>
