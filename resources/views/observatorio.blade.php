<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @include('partials.favicon')
        <title>Observatorio — {{ config('app.name') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800|fraunces:600,700" rel="stylesheet" />
        <style>
            :root {
                --brand-rose: #f9dff8;
                --brand-green: #7ce8ad;
                --bg: #050807;
                --surface: rgba(6, 18, 14, 0.78);
                --text: #f4fffa;
                --ink: #0a1f18;
            }
            * { box-sizing: border-box; }
            body {
                margin: 0;
                min-height: 100vh;
                font-family: "Instrument Sans", ui-sans-serif, system-ui, sans-serif;
                background-color: var(--bg);
                background-image:
                    radial-gradient(ellipse 120% 80% at 8% -20%, rgba(124, 232, 173, 0.22), transparent 52%),
                    radial-gradient(ellipse 90% 70% at 100% 0%, rgba(249, 223, 248, 0.16), transparent 48%),
                    linear-gradient(180deg, rgba(0, 0, 0, 0.2), transparent 28%);
                color: var(--text);
                padding: 1.25rem 1.25rem 2.5rem;
            }
            .top {
                max-width: 72rem;
                margin: 0 auto 1.35rem;
                display: flex;
                flex-wrap: wrap;
                align-items: flex-end;
                justify-content: space-between;
                gap: 1rem;
            }
            .brand {
                margin: 0;
                font-family: Fraunces, Georgia, serif;
                font-size: clamp(1.85rem, 3.2vw, 2.45rem);
                font-weight: 700;
                letter-spacing: -0.02em;
                line-height: 1.05;
            }
            .brand span {
                color: color-mix(in srgb, var(--brand-green) 80%, #fff);
            }
            .sub {
                margin: 0.4rem 0 0;
                font-size: 0.88rem;
                opacity: 0.82;
                max-width: 36rem;
                line-height: 1.45;
            }
            .live-clock {
                margin: 0.4rem 0 0;
                font-size: 0.72rem;
                font-weight: 700;
                letter-spacing: 0.06em;
                text-transform: uppercase;
                color: color-mix(in srgb, var(--brand-rose) 85%, #fff);
            }
            .btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0.45rem 0.95rem;
                border-radius: 9999px;
                font-size: 0.85rem;
                font-weight: 600;
                text-decoration: none;
                border: 1px solid color-mix(in srgb, var(--brand-rose) 45%, transparent);
                color: var(--brand-rose);
                background: color-mix(in srgb, var(--brand-rose) 10%, transparent);
            }
            .periodos {
                display: inline-flex;
                flex-wrap: wrap;
                gap: 0.35rem;
                padding: 0.25rem;
                border-radius: 9999px;
                border: 1px solid color-mix(in srgb, var(--brand-green) 28%, transparent);
                background: rgba(0, 0, 0, 0.28);
            }
            .periodos a {
                text-decoration: none;
                color: color-mix(in srgb, var(--text) 75%, transparent);
                font-size: 0.75rem;
                font-weight: 700;
                letter-spacing: 0.04em;
                text-transform: uppercase;
                padding: 0.38rem 0.75rem;
                border-radius: 9999px;
            }
            .periodos a.is-on {
                color: var(--ink);
                background: linear-gradient(145deg, var(--brand-green), color-mix(in srgb, var(--brand-green) 70%, #1a5c40));
            }
            .shell {
                max-width: 72rem;
                margin: 0 auto;
                display: grid;
                gap: 0.9rem;
                grid-template-columns: repeat(12, minmax(0, 1fr));
            }
            .panel {
                background: var(--surface);
                border: 1px solid color-mix(in srgb, var(--brand-green) 30%, transparent);
                border-radius: 1.1rem;
                padding: 1rem 1.1rem 1.15rem;
                box-shadow: 0 0 0 1px color-mix(in srgb, var(--brand-rose) 6%, transparent) inset;
            }
            .pulse {
                grid-column: 1 / -1;
                display: grid;
                gap: 0.75rem;
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
            .kpi {
                padding: 0.95rem 1rem;
                border-radius: 0.9rem;
                border: 1px solid color-mix(in srgb, var(--brand-green) 26%, transparent);
                background: rgba(0, 0, 0, 0.22);
                min-height: 7.2rem;
            }
            .kpi .num {
                margin: 0;
                font-size: 1.75rem;
                font-weight: 800;
                letter-spacing: -0.02em;
                font-variant-numeric: tabular-nums;
            }
            .kpi .label {
                margin: 0.35rem 0 0;
                font-size: 0.68rem;
                font-weight: 800;
                letter-spacing: 0.07em;
                text-transform: uppercase;
                color: color-mix(in srgb, var(--brand-green) 80%, #fff);
            }
            .kpi .hint {
                margin: 0.4rem 0 0;
                font-size: 0.78rem;
                line-height: 1.4;
                opacity: 0.72;
            }
            .span-7 { grid-column: span 7; }
            .span-5 { grid-column: span 5; }
            .span-12 { grid-column: 1 / -1; }
            h2 {
                margin: 0 0 0.85rem;
                font-size: 0.72rem;
                font-weight: 800;
                letter-spacing: 0.08em;
                text-transform: uppercase;
                color: color-mix(in srgb, var(--brand-rose) 88%, #fff);
            }
            .heat-list { display: flex; flex-direction: column; gap: 0.55rem; }
            .heat-row {
                display: grid;
                grid-template-columns: minmax(7rem, 11rem) 1fr auto;
                gap: 0.65rem;
                align-items: center;
                width: 100%;
                text-align: left;
                font: inherit;
                color: inherit;
                background: transparent;
                border: 0;
                padding: 0.2rem 0.15rem;
                border-radius: 0.45rem;
                cursor: pointer;
                text-decoration: none;
            }
            .heat-row:hover,
            .heat-row.is-on {
                background: color-mix(in srgb, var(--brand-green) 10%, transparent);
            }
            .heat-row span.name {
                font-size: 0.82rem;
                font-weight: 600;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            .heat-track {
                height: 0.55rem;
                border-radius: 9999px;
                background: rgba(255, 255, 255, 0.08);
                overflow: hidden;
            }
            .heat-fill {
                height: 100%;
                width: 0;
                border-radius: inherit;
                background: linear-gradient(90deg, var(--brand-green), color-mix(in srgb, var(--brand-rose) 55%, var(--brand-green)));
                transition: width 0.7s cubic-bezier(0.22, 1, 0.36, 1);
            }
            .heat-row strong {
                font-size: 0.82rem;
                font-variant-numeric: tabular-nums;
                min-width: 2rem;
                text-align: right;
            }
            .rank-list { display: flex; flex-direction: column; gap: 0.65rem; }
            .rank-item {
                display: grid;
                grid-template-columns: 1.6rem 1fr auto;
                gap: 0.55rem;
                align-items: center;
            }
            .rank-item .pos {
                font-size: 0.72rem;
                font-weight: 800;
                opacity: 0.55;
            }
            .rank-item .who strong {
                display: block;
                font-size: 0.88rem;
            }
            .rank-item .who span {
                font-size: 0.72rem;
                opacity: 0.65;
            }
            .rank-bar {
                grid-column: 2 / -1;
                height: 0.35rem;
                border-radius: 9999px;
                background: rgba(255, 255, 255, 0.08);
                overflow: hidden;
            }
            .rank-bar > i {
                display: block;
                height: 100%;
                width: 0;
                background: color-mix(in srgb, var(--brand-rose) 70%, #fff);
                transition: width 0.7s cubic-bezier(0.22, 1, 0.36, 1);
            }
            .feed {
                list-style: none;
                margin: 0;
                padding: 0;
                display: flex;
                flex-direction: column;
                gap: 0.55rem;
                max-height: 28rem;
                overflow: auto;
            }
            .feed li {
                display: grid;
                grid-template-columns: auto 1fr auto;
                gap: 0.7rem;
                align-items: start;
                padding: 0.65rem 0.7rem;
                border-radius: 0.7rem;
                border: 1px solid color-mix(in srgb, var(--brand-green) 18%, transparent);
                background: rgba(0, 0, 0, 0.2);
            }
            .dot {
                width: 0.55rem;
                height: 0.55rem;
                margin-top: 0.35rem;
                border-radius: 50%;
                background: var(--brand-green);
                box-shadow: 0 0 0 4px color-mix(in srgb, var(--brand-green) 18%, transparent);
            }
            .feed .t {
                margin: 0;
                font-size: 0.86rem;
                font-weight: 700;
            }
            .feed .d {
                margin: 0.2rem 0 0;
                font-size: 0.76rem;
                opacity: 0.7;
                line-height: 1.4;
            }
            .feed time {
                font-size: 0.72rem;
                font-weight: 700;
                opacity: 0.65;
                white-space: nowrap;
            }
            .empty {
                margin: 0;
                font-size: 0.86rem;
                opacity: 0.7;
            }
            .clear-mod {
                display: inline-flex;
                margin: 0 0 0.75rem;
                font-size: 0.75rem;
                font-weight: 700;
                color: var(--brand-rose);
                text-decoration: none;
            }
            .charts-row {
                grid-column: 1 / -1;
                display: grid;
                gap: 0.9rem;
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .chart-panel h2 { margin-bottom: 0.35rem; }
            .chart-meta {
                margin: 0 0 0.85rem;
                font-size: 0.76rem;
                opacity: 0.7;
            }
            .time-chart {
                display: flex;
                align-items: flex-end;
                gap: 0.28rem;
                min-height: 11rem;
                height: 11rem;
                padding: 0.15rem 0.1rem 0;
                overflow-x: auto;
            }
            .time-col {
                flex: 1 1 0;
                min-width: 1.35rem;
                max-width: 2.75rem;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: flex-end;
                gap: 0.28rem;
                height: 100%;
            }
            .time-val {
                font-size: 0.62rem;
                font-weight: 700;
                font-variant-numeric: tabular-nums;
                opacity: 0.75;
                min-height: 0.85rem;
            }
            .time-bar-wrap {
                flex: 1 1 auto;
                width: 100%;
                display: flex;
                align-items: flex-end;
                justify-content: center;
            }
            .time-bar {
                width: 72%;
                max-width: 1.35rem;
                height: 0;
                border-radius: 0.35rem 0.35rem 0.15rem 0.15rem;
                background: linear-gradient(180deg, var(--brand-green), color-mix(in srgb, var(--brand-green) 45%, #0a2e20));
                transition: height 0.7s cubic-bezier(0.22, 1, 0.36, 1);
            }
            .time-bar--users {
                background: linear-gradient(180deg, var(--brand-rose), color-mix(in srgb, var(--brand-rose) 45%, #5a2a4e));
            }
            .time-label {
                font-size: 0.58rem;
                font-weight: 700;
                letter-spacing: 0.02em;
                opacity: 0.62;
                white-space: nowrap;
                transform: rotate(-35deg);
                transform-origin: center top;
                margin-top: 0.15rem;
                height: 1.5rem;
            }
            .time-col.is-peak .time-bar {
                box-shadow: 0 0 0 1px color-mix(in srgb, #fff 35%, transparent);
            }
            .time-col.is-peak .time-val {
                color: var(--brand-green);
                opacity: 1;
            }
            @media (max-width: 980px) {
                .pulse { grid-template-columns: repeat(2, minmax(0, 1fr)); }
                .span-7, .span-5 { grid-column: 1 / -1; }
                .charts-row { grid-template-columns: 1fr; }
            }
            @media (max-width: 560px) {
                .pulse { grid-template-columns: 1fr; }
                .heat-row { grid-template-columns: 1fr auto; }
                .heat-track { grid-column: 1 / -1; }
                .time-col { min-width: 1.1rem; }
            }
            @media (prefers-reduced-motion: reduce) {
                .heat-fill, .rank-bar > i, .time-bar { transition: none; }
            }
        </style>
    </head>
    <body>
        @include('partials.logo-institucional')
        <header class="top">
            <div>
                <h1 class="brand">Observa<span>torio</span></h1>
                <p class="sub">
                    Usabilidad del sistema · {{ $periodoEtiqueta }}. Quién entra, a qué módulo y con qué frecuencia.
                </p>
                <p class="live-clock" id="obs-clock" aria-live="polite"></p>
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:0.65rem;align-items:center">
                <nav class="periodos" aria-label="Periodo">
                    <a href="{{ route('observatorio', array_filter(['periodo' => 'hoy', 'modulo' => $filtroModulo ?: null])) }}" @class(['is-on' => $periodo === 'hoy'])>Hoy</a>
                    <a href="{{ route('observatorio', array_filter(['periodo' => '7d', 'modulo' => $filtroModulo ?: null])) }}" @class(['is-on' => $periodo === '7d'])>7 días</a>
                    <a href="{{ route('observatorio', array_filter(['periodo' => 'mes', 'modulo' => $filtroModulo ?: null])) }}" @class(['is-on' => $periodo === 'mes'])>Mes</a>
                </nav>
                <a class="btn" href="{{ route('menu') }}">← Menú</a>
            </div>
        </header>

        <div class="shell">
            <div class="pulse">
                <article class="kpi">
                    <p class="num">{{ number_format($activosRecientes) }}</p>
                    <p class="label">Activos · 30 min</p>
                    <p class="hint">Usuarios con al menos una entrada reciente.</p>
                </article>
                <article class="kpi">
                    <p class="num">{{ number_format($modulosTocados) }}</p>
                    <p class="label">Módulos tocados</p>
                    <p class="hint">Distintos módulos visitados en el periodo.</p>
                </article>
                <article class="kpi">
                    <p class="num">{{ number_format($usuariosDistintos) }}</p>
                    <p class="label">Usuarios distintos</p>
                    <p class="hint">{{ number_format($totalEntradas) }} entrada(s) registradas.</p>
                </article>
                <article class="kpi">
                    <p class="num">{{ number_format($topModuloTotal) }}</p>
                    <p class="label">Más frecuente</p>
                    <p class="hint">{{ $topModuloNombre }}.</p>
                </article>
            </div>

            <div class="charts-row">
                <article class="panel chart-panel">
                    <h2>Usabilidad en el tiempo · entradas</h2>
                    <p class="chart-meta">
                        @if ($serieGranularidad === 'hora')
                            Accesos por hora · pico {{ $picoEtiqueta }} ({{ number_format($picoEntradas) }})
                        @else
                            Accesos por día · pico {{ $picoEtiqueta }} ({{ number_format($picoEntradas) }})
                        @endif
                        @if ($filtroModulo !== '')
                            · {{ $nombresModulo[$filtroModulo] ?? $filtroModulo }}
                        @endif
                    </p>
                    @if ($picoEntradas === 0)
                        <p class="empty">Sin actividad en este periodo para graficar.</p>
                    @else
                        <div class="time-chart" role="img" aria-label="Gráfico de entradas en el tiempo">
                            @foreach ($serieTemporal as $punto)
                                @php
                                    $h = (int) round(($punto['entradas'] / $maxSerieEntradas) * 100);
                                    $esPico = $punto['etiqueta'] === $picoEtiqueta && $punto['entradas'] === $picoEntradas && $picoEntradas > 0;
                                @endphp
                                <div class="time-col {{ $esPico ? 'is-peak' : '' }}" title="{{ $punto['etiqueta'] }}: {{ $punto['entradas'] }} entrada(s)">
                                    <span class="time-val">{{ $punto['entradas'] > 0 ? $punto['entradas'] : '' }}</span>
                                    <div class="time-bar-wrap">
                                        <div class="time-bar js-bar-h" data-height="{{ max($punto['entradas'] > 0 ? 8 : 2, $h) }}"></div>
                                    </div>
                                    <span class="time-label">{{ $punto['etiqueta'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </article>
                <article class="panel chart-panel">
                    <h2>Usabilidad en el tiempo · usuarios</h2>
                    <p class="chart-meta">
                        Usuarios distintos
                        {{ $serieGranularidad === 'hora' ? 'por hora' : 'por día' }}
                        en el mismo periodo.
                    </p>
                    @if (collect($serieTemporal)->sum('usuarios') === 0)
                        <p class="empty">Sin usuarios activos en la serie temporal.</p>
                    @else
                        <div class="time-chart" role="img" aria-label="Gráfico de usuarios distintos en el tiempo">
                            @foreach ($serieTemporal as $punto)
                                @php
                                    $h = (int) round(($punto['usuarios'] / $maxSerieUsuarios) * 100);
                                @endphp
                                <div class="time-col" title="{{ $punto['etiqueta'] }}: {{ $punto['usuarios'] }} usuario(s)">
                                    <span class="time-val">{{ $punto['usuarios'] > 0 ? $punto['usuarios'] : '' }}</span>
                                    <div class="time-bar-wrap">
                                        <div class="time-bar time-bar--users js-bar-h" data-height="{{ max($punto['usuarios'] > 0 ? 8 : 2, $h) }}"></div>
                                    </div>
                                    <span class="time-label">{{ $punto['etiqueta'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </article>
            </div>

            <article class="panel span-7">
                <h2>Mapa de calor · módulos</h2>
                @if ($filtroModulo !== '')
                    <a class="clear-mod" href="{{ route('observatorio', ['periodo' => $periodo]) }}">Quitar filtro · {{ $nombresModulo[$filtroModulo] ?? $filtroModulo }}</a>
                @endif
                @if ($heatmap === [])
                    <p class="empty">Aún no hay accesos en este periodo. Navegue por el sistema para generar pulso.</p>
                @else
                    <div class="heat-list">
                        @foreach ($heatmap as $item)
                            <a
                                class="heat-row {{ $filtroModulo === $item['clave'] ? 'is-on' : '' }}"
                                href="{{ route('observatorio', ['periodo' => $periodo, 'modulo' => $item['clave']]) }}"
                                title="Filtrar actividad: {{ $item['nombre'] }}"
                            >
                                <span class="name">{{ $item['nombre'] }}</span>
                                <div class="heat-track">
                                    <div
                                        class="heat-fill js-width"
                                        data-width="{{ (int) round(($item['total'] / $maxHeat) * 100) }}"
                                    ></div>
                                </div>
                                <strong>{{ number_format($item['total']) }}</strong>
                            </a>
                        @endforeach
                    </div>
                @endif
            </article>

            <article class="panel span-5">
                <h2>Top usuarios</h2>
                @if ($ranking === [])
                    <p class="empty">Sin ranking todavía.</p>
                @else
                    <div class="rank-list">
                        @foreach ($ranking as $i => $u)
                            <div class="rank-item">
                                <span class="pos">{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</span>
                                <div class="who">
                                    <strong>{{ $u['nombre'] }}</strong>
                                    <span>{{ $u['role_label'] }} · {{ number_format($u['total']) }} acceso(s)</span>
                                </div>
                                <div class="rank-bar" aria-hidden="true">
                                    <i class="js-width" data-width="{{ (int) round(($u['total'] / $maxRank) * 100) }}"></i>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </article>

            <article class="panel span-12">
                <h2>
                    Actividad reciente
                    @if ($filtroModulo !== '')
                        · {{ $nombresModulo[$filtroModulo] ?? $filtroModulo }}
                    @endif
                </h2>
                @if ($feed->isEmpty())
                    <p class="empty">No hay entradas para mostrar.</p>
                @else
                    <ul class="feed">
                        @foreach ($feed as $row)
                            <li>
                                <span class="dot" aria-hidden="true"></span>
                                <div>
                                    <p class="t">{{ $row['usuario'] }} · {{ $row['modulo'] }}</p>
                                    <p class="d">
                                        {{ $row['role_label'] }}
                                        @if (! empty($row['username']))
                                            · {{ '@'.$row['username'] }}
                                        @endif
                                        @if (! empty($row['ruta']))
                                            · {{ $row['ruta'] }}
                                        @endif
                                    </p>
                                </div>
                                <time datetime="{{ $row['fecha_key'] }}">{{ $row['hora'] }}</time>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </article>
        </div>

        @include('partials.credito-desarrollador')

        <script>
            (function () {
                var clock = document.getElementById('obs-clock');
                function tick() {
                    if (!clock) return;
                    var d = new Date();
                    var pad = function (n) { return n < 10 ? '0' + n : '' + n; };
                    clock.textContent =
                        'En vivo · ' +
                        pad(d.getDate()) + '/' + pad(d.getMonth() + 1) + '/' + d.getFullYear() +
                        ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes()) + ':' + pad(d.getSeconds());
                }
                tick();
                setInterval(tick, 1000);

                var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                setTimeout(function () {
                    document.querySelectorAll('.js-width').forEach(function (el) {
                        el.style.width = (el.getAttribute('data-width') || '0') + '%';
                    });
                    document.querySelectorAll('.js-bar-h').forEach(function (el) {
                        el.style.height = (el.getAttribute('data-height') || '2') + '%';
                    });
                }, reduce ? 0 : 40);
            })();
        </script>
    </body>
</html>
