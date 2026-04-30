<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @include('partials.favicon')
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Cambios de destinos — {{ config('app.name') }}</title>
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
            .filtros-actions {
                display: flex;
                flex-wrap: wrap;
                gap: 0.5rem 0.65rem;
                align-items: center;
                margin-top: 0.85rem;
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
            .col-prop,
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
            .btn-guardar:hover {
                filter: brightness(1.06);
            }
        </style>
    </head>
    <body>
        @include('partials.logo-institucional')
        <header class="page-bar">
            <div>
                <h1>Cambios de destinos</h1>
                <p class="sub">
                    Busque filas del <strong>inventario disponible</strong> (sin despachar) por <strong>ID producto</strong>
                    y/o <strong>propietario</strong>; edite el <strong>destino</strong> y guarde cada registro.
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

            <form class="filtros-form" method="get" action="{{ route('cambios.destinos') }}" aria-labelledby="filtros-cambios-heading">
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
                </div>
                <div class="filtros-actions">
                    <button type="submit" class="btn-filtrar">Buscar en inventario</button>
                    @if ($filtrosActivos)
                        <a class="link-limpiar" href="{{ route('cambios.destinos') }}">Limpiar</a>
                    @endif
                </div>
            </form>

            @if (! $filtrosActivos)
                <p class="muted">
                    Indique al menos un criterio (<strong>ID producto</strong> o <strong>propietario</strong>) y pulse
                    <strong>Buscar en inventario</strong>. Solo se listan lenguas aún disponibles (no despachadas). Máximo
                    {{ $listadoLimit }} filas por consulta.
                </p>
            @elseif ($rows->isEmpty())
                <p class="muted">
                    No hay registros que coincidan. Este módulo usa la misma base de datos local que
                    <strong>Inventario de lenguas</strong> y solo muestra filas <strong>disponibles</strong> (sin
                    despachar). Si ese ID ya salió en un despacho, no se listará aquí tampoco.
                </p>
            @else
                <p class="muted">
                    <strong>{{ $rows->count() }}</strong> registro(s) encontrado(s). Edite el destino y pulse
                    <strong>Guardar</strong> en la fila correspondiente.
                </p>
                <div class="table-wrap">
                    <table class="data">
                        <thead>
                            <tr>
                                <th class="col-producto">ID producto</th>
                                <th class="col-prop">Propietario</th>
                                <th>Ref. turno</th>
                                <th class="col-destino">Destino</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $row)
                                <tr>
                                    <td class="col-producto">{{ $row->id_producto }}</td>
                                    <td class="col-prop">{{ $row->propietario !== null && $row->propietario !== '' ? $row->propietario : '—' }}</td>
                                    <td>
                                        {{ $row->fecha_turno_referencia?->format('d/m/Y') ?? '—' }}
                                    </td>
                                    <td class="col-destino">
                                        <form method="post" action="{{ route('cambios.destinos.update', $row) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="id_producto" value="{{ $filters['id_producto'] }}" />
                                            <input type="hidden" name="propietario" value="{{ $filters['propietario'] }}" />
                                            <input type="hidden" name="context_row_id" value="{{ $row->id }}" />
                                            <label class="sr-only" for="destino-{{ $row->id }}">Destino para {{ $row->id_producto }}</label>
                                            @php
                                                $failedRowId = old('context_row_id');
                                                $destinoValor =
                                                    $failedRowId !== null && (int) $failedRowId === $row->id
                                                        ? old('destino', '')
                                                        : ($row->destino ?? '');
                                            @endphp
                                            <textarea
                                                id="destino-{{ $row->id }}"
                                                class="destino-input"
                                                name="destino"
                                                rows="2"
                                                maxlength="60000"
                                            >{{ $destinoValor }}</textarea>
                                            @if ($failedRowId !== null && (int) $failedRowId === $row->id && $errors->has('destino'))
                                                <p style="margin: 0.35rem 0 0; font-size: 0.72rem; color: #ffb4b4; font-weight: 600">{{ $errors->first('destino') }}</p>
                                            @endif
                                            <div style="margin-top: 0.4rem">
                                                <button type="submit" class="btn-guardar">Guardar destino</button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
        <style>
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
    </body>
</html>
