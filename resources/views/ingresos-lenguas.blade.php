<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @include('partials.favicon')
        <title>Ingresos de Lenguas — {{ config('app.name') }}</title>
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
                text-shadow:
                    0 0 28px color-mix(in srgb, var(--brand-green) 35%, transparent),
                    0 2px 8px rgba(0, 0, 0, 0.45);
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
            .link-menu:hover {
                background: color-mix(in srgb, var(--brand-rose) 16%, transparent);
            }
            .sheet {
                max-width: 90rem;
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
                color: var(--text);
                white-space: nowrap;
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
        @include('partials.logo-institucional')
        <header class="page-bar">
            <div>
                <h1>Ingresos de Lenguas</h1>
                <p class="sub">
                    Consulta operativa de <strong>ingresos de lenguas</strong> sobre la base de trazabilidad: se
                    muestran identificador de producto, fecha y hora de insensibilización, titular del producto,
                    destino logístico asociado y peso registrado. Los filtros permiten acotar por periodo de faena,
                    identificador y nombre de titular; al abrir el módulo se prioriza la jornada en curso y la
                    consulta devuelve hasta {{ max(100, min(10000, (int) config('ingresos_lenguas.consulta_insensibilizacion_limit', 2000))) }} registros por búsqueda.
                </p>
            </div>
            <a class="link-menu" href="{{ route('menu') }}">← Menú</a>
        </header>

        <section class="sheet" aria-labelledby="tabla-ingresos">
            <p id="tabla-ingresos" class="meta">
                Criterios de búsqueda y listado de resultados
            </p>

            @if (! empty($error))
                <p class="alert" role="alert">{{ $error }}</p>
            @endif

            <form class="filters" method="get" action="{{ route('ingresos.lenguas') }}" aria-label="Filtros de ingresos">
                <input type="hidden" name="_enviado" value="1" />
                <div class="filters-grid">
                    <div class="filter-field">
                        <label for="ing-fecha-desde">Fecha desde (turno)</label>
                        <input
                            id="ing-fecha-desde"
                            type="date"
                            name="fecha_desde"
                            value="{{ $filters['fecha_desde'] ?? '' }}"
                        />
                        @error('fecha_desde')
                            <p class="err">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="filter-field">
                        <label for="ing-fecha-hasta">Fecha hasta (turno)</label>
                        <input
                            id="ing-fecha-hasta"
                            type="date"
                            name="fecha_hasta"
                            value="{{ $filters['fecha_hasta'] ?? '' }}"
                        />
                        @error('fecha_hasta')
                            <p class="err">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="filter-field">
                        <label for="ing-id-producto">Id producto</label>
                        <input
                            id="ing-id-producto"
                            type="text"
                            name="id_producto"
                            value="{{ $filters['id_producto'] ?? '' }}"
                            maxlength="80"
                            placeholder="Codigo Producto"
                            autocomplete="off"
                        />
                        @error('id_producto')
                            <p class="err">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="filter-field">
                        <label for="ing-propietario">Propietario</label>
                        <input
                            id="ing-propietario"
                            type="text"
                            name="propietario"
                            value="{{ $filters['propietario'] ?? '' }}"
                            maxlength="200"
                            placeholder="Nombre Propietario"
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
                        $soloFechasIngresos = [
                            '_enviado' => '1',
                            'fecha_desde' => $filters['fecha_desde'] ?? '',
                            'fecha_hasta' => $filters['fecha_hasta'] ?? '',
                        ];
                    @endphp
                    <a
                        class="btn-clear"
                        href="{{ route('ingresos.lenguas', $soloFechasIngresos) }}"
                    >Limpiar</a>
                    <a class="btn-clear" href="{{ route('ingresos.lenguas') }}">Volver a hoy</a>
                </div>
                <p class="muted" style="margin: 0.85rem 0 0; font-size: 0.82rem">
                    Si indica solo una fecha de turno, se usa ese mismo día como rango (desde y hasta). Puede buscar solo por
                    id de producto o solo por propietario sin fechas; si además indica fechas, el rango se cruza con la
                    referencia de turno en trazabilidad.
                </p>
            </form>

            @if ($errors->any() && empty($error))
                <p class="alert" role="alert">Revise los filtros indicados.</p>
            @endif

            @php
                $limiteConsultaIngresos = (int) ($consulta_insensibilizacion_limit ?? max(
                    100,
                    min(10000, (int) config('ingresos_lenguas.consulta_insensibilizacion_limit', 2000)),
                ));
            @endphp
            @if (empty($error) && ! $errors->any() && count($rows) === 0)
                <p class="muted">
                    No hay filas para los filtros indicados (coincidentes según turno y criterios:
                    {{ (int) ($total_coincidentes ?? 0) }}; tope de presentación {{ $limiteConsultaIngresos }} por consulta).
                </p>
            @elseif (empty($error) && ! $errors->any())
                @php
                    $fmtHoraIngresos = static function ($v) {
                        if ($v instanceof \DateTimeInterface) {
                            return $v->format('H:i:s');
                        }

                        return $v !== null && $v !== '' ? (string) $v : '—';
                    };
                    $fmtFechaIngresos = static function ($v) {
                        if ($v instanceof \DateTimeInterface) {
                            return $v->format('Y-m-d');
                        }

                        return $v !== null && $v !== '' ? (string) $v : '—';
                    };
                @endphp
                @php
                    $totalRefTurno = (int) ($total_coincidentes ?? 0);
                    $nMostrados = count($rows);
                @endphp
                <p class="muted" style="margin: 0 0 0.75rem">
                    Total coincidente con referencia a turno (y filtros aplicados): <strong>{{ $totalRefTurno }}</strong>.
                    @if ($totalRefTurno > $nMostrados)
                        Se muestran <strong>{{ $nMostrados }}</strong> filas (tope {{ $limiteConsultaIngresos }} por consulta, las más recientes).
                    @else
                        Se listan <strong>{{ $nMostrados }}</strong> fila(s).
                    @endif
                </p>
                <div class="table-wrap">
                    <table class="data">
                        <colgroup>
                            <col class="col-id-producto-col" />
                            <col span="5" />
                        </colgroup>
                        <thead>
                            <tr>
                                <th class="col-id-producto">Id producto</th>
                                <th>Fecha registro</th>
                                <th>Hora registro</th>
                                <th>Propietario</th>
                                <th>Destino</th>
                                <th>Peso</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $row)
                                @php
                                    $r = (array) $row;
                                    $fechaTxt = $fmtFechaIngresos($r['fecha_registro'] ?? null);
                                    $horaTxt = $fmtHoraIngresos($r['hora_registro'] ?? null);
                                @endphp
                                <tr>
                                    <td class="col-id-producto">{{ $r['id_producto'] ?? '—' }}</td>
                                    <td>{{ $fechaTxt }}</td>
                                    <td>{{ $horaTxt }}</td>
                                    <td>{{ $r['propietario'] ?? '—' }}</td>
                                    <td>{{ $r['destino'] !== null && $r['destino'] !== '' ? $r['destino'] : '—' }}</td>
                                    <td>{{ \App\Support\PesoFormatter::mostrar($r['peso'] ?? null) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </body>
</html>
