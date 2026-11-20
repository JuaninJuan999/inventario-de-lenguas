<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @include('partials.favicon')
        <title>Dashboard — {{ config('app.name') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet" />
        <style>
            :root {
                --brand-rose: #f9dff8;
                --brand-green: #7ce8ad;
                --bg: #050807;
                --surface: rgba(6, 18, 14, 0.72);
                --text: #f4fffa;
                --warn: #ffd28a;
                --danger: #ff8f8f;
            }
            * { box-sizing: border-box; }
            body {
                margin: 0;
                min-height: 100vh;
                font-family: "Instrument Sans", ui-sans-serif, system-ui, sans-serif;
                background-color: var(--bg);
                background-image:
                    radial-gradient(ellipse 120% 90% at 0% -10%, rgba(124, 232, 173, 0.2), transparent 50%),
                    radial-gradient(ellipse 100% 80% at 100% 0%, rgba(249, 223, 248, 0.14), transparent 46%);
                color: var(--text);
                padding: 1.25rem 1.25rem 2.5rem;
            }
            .top {
                max-width: 72rem;
                margin: 0 auto 1.25rem;
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
                gap: 0.85rem;
            }
            h1 {
                margin: 0;
                font-size: 1.25rem;
                font-weight: 800;
            }
            .sub {
                margin: 0.3rem 0 0;
                font-size: 0.86rem;
                opacity: 0.85;
            }
            .live-clock {
                margin: 0.35rem 0 0;
                font-size: 0.75rem;
                font-weight: 600;
                letter-spacing: 0.04em;
                color: color-mix(in srgb, var(--brand-green) 75%, #fff);
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
                transition: background 0.18s ease, transform 0.18s ease;
            }
            .btn:hover {
                background: color-mix(in srgb, var(--brand-rose) 18%, transparent);
                transform: translateY(-1px);
            }
            .dash {
                max-width: 72rem;
                margin: 0 auto;
                display: grid;
                gap: 0.85rem;
                grid-template-columns: repeat(12, 1fr);
            }
            .panel {
                border-radius: 1rem;
                border: 1px solid color-mix(in srgb, var(--brand-green) 32%, transparent);
                background: var(--surface);
                box-shadow: 0 0 0 1px color-mix(in srgb, var(--brand-rose) 8%, transparent) inset;
                padding: 1rem 1.05rem 1.1rem;
                min-width: 0;
                opacity: 0;
                transform: translateY(10px);
                animation: dash-in 0.45s ease forwards;
            }
            .panel:nth-child(1) { animation-delay: 0.02s; }
            .panel:nth-child(2) { animation-delay: 0.06s; }
            .panel:nth-child(3) { animation-delay: 0.1s; }
            .panel:nth-child(4) { animation-delay: 0.14s; }
            .panel:nth-child(5) { animation-delay: 0.18s; }
            .panel:nth-child(6) { animation-delay: 0.22s; }
            .panel:nth-child(7) { animation-delay: 0.26s; }
            .panel:nth-child(8) { animation-delay: 0.3s; }
            .panel:nth-child(9) { animation-delay: 0.34s; }
            .panel:nth-child(10) { animation-delay: 0.38s; }
            @keyframes dash-in {
                to { opacity: 1; transform: translateY(0); }
            }
            .panel h2 {
                margin: 0 0 0.75rem;
                font-size: 0.68rem;
                font-weight: 800;
                letter-spacing: 0.08em;
                text-transform: uppercase;
                color: color-mix(in srgb, var(--brand-green) 80%, #fff);
            }
            .kpi-grid {
                grid-column: 1 / -1;
                display: grid;
                gap: 0.85rem;
                grid-template-columns: repeat(5, minmax(0, 1fr));
            }
            a.kpi {
                display: flex;
                gap: 0.85rem;
                align-items: center;
                text-decoration: none;
                color: inherit;
                cursor: pointer;
                transition: transform 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
            }
            a.kpi:hover,
            a.kpi:focus-visible {
                transform: translateY(-3px);
                border-color: color-mix(in srgb, var(--brand-green) 65%, transparent);
                box-shadow:
                    0 0 0 1px color-mix(in srgb, var(--brand-rose) 14%, transparent) inset,
                    0 14px 28px rgba(0, 0, 0, 0.35);
                outline: none;
            }
            a.kpi .cta {
                margin-top: 0.35rem;
                font-size: 0.7rem;
                font-weight: 700;
                color: var(--brand-green);
                opacity: 0;
                transform: translateX(-4px);
                transition: opacity 0.18s ease, transform 0.18s ease;
            }
            a.kpi:hover .cta,
            a.kpi:focus-visible .cta {
                opacity: 1;
                transform: translateX(0);
            }
            .ring {
                --p: 0;
                --ring: var(--brand-green);
                width: 3.6rem;
                height: 3.6rem;
                border-radius: 50%;
                background:
                    radial-gradient(closest-side, rgba(5, 8, 7, 0.95) 68%, transparent 70% 100%),
                    conic-gradient(var(--ring) calc(var(--p) * 1%), color-mix(in srgb, #fff 12%, transparent) 0);
                flex-shrink: 0;
                display: grid;
                place-items: center;
                font-size: 0.72rem;
                font-weight: 800;
                transition: transform 0.25s ease;
            }
            a.kpi:hover .ring {
                transform: scale(1.06);
            }
            .kpi .num {
                margin: 0;
                font-size: 1.55rem;
                font-weight: 800;
                line-height: 1.1;
                font-variant-numeric: tabular-nums;
            }
            .kpi .label {
                margin: 0.2rem 0 0;
                font-size: 0.72rem;
                font-weight: 700;
                letter-spacing: 0.05em;
                text-transform: uppercase;
                opacity: 0.8;
            }
            .kpi .hint {
                margin: 0.25rem 0 0;
                font-size: 0.75rem;
                opacity: 0.7;
                line-height: 1.35;
            }
            .span-5 { grid-column: span 5; }
            .span-4 { grid-column: span 4; }
            .span-7 { grid-column: span 7; }
            .span-8 { grid-column: span 8; }
            .pct-big {
                margin: 0;
                font-size: 2.4rem;
                font-weight: 800;
                letter-spacing: -0.03em;
                font-variant-numeric: tabular-nums;
            }
            .bar-track {
                margin: 0.65rem 0 0.5rem;
                height: 0.55rem;
                border-radius: 9999px;
                background: rgba(0, 0, 0, 0.35);
                overflow: hidden;
            }
            .bar-fill {
                height: 100%;
                width: 0;
                border-radius: inherit;
                background: linear-gradient(90deg, var(--brand-green), color-mix(in srgb, var(--brand-green) 55%, #1a5c40));
                transition: width 0.9s cubic-bezier(0.22, 1, 0.36, 1);
            }
            .stack {
                display: grid;
                gap: 0.55rem;
            }
            .stack-row {
                display: grid;
                grid-template-columns: 6.5rem 1fr auto;
                gap: 0.5rem;
                align-items: center;
                font-size: 0.78rem;
                padding: 0.35rem 0.4rem;
                border-radius: 0.55rem;
                cursor: pointer;
                border: 1px solid transparent;
                transition: background 0.15s ease, border-color 0.15s ease;
            }
            .stack-row:hover,
            .stack-row.is-active {
                background: rgba(0, 0, 0, 0.28);
                border-color: color-mix(in srgb, var(--brand-green) 30%, transparent);
            }
            .stack-track {
                height: 0.7rem;
                border-radius: 9999px;
                background: rgba(0, 0, 0, 0.35);
                overflow: hidden;
            }
            .stack-fill {
                height: 100%;
                width: 0;
                border-radius: inherit;
                transition: width 0.85s cubic-bezier(0.22, 1, 0.36, 1);
            }
            .stack-fill--ok { background: var(--brand-green); }
            .stack-fill--warn { background: var(--warn); }
            .stack-fill--danger { background: var(--danger); }
            .chart {
                display: flex;
                align-items: flex-end;
                gap: 0.45rem;
                height: 9.5rem;
                padding-top: 0.35rem;
            }
            .chart-col {
                flex: 1;
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 0.35rem;
                min-width: 0;
                height: 100%;
                justify-content: flex-end;
                cursor: pointer;
                border-radius: 0.5rem;
                padding: 0.15rem;
                transition: background 0.15s ease;
            }
            .chart-col:hover,
            .chart-col.is-active {
                background: color-mix(in srgb, var(--brand-green) 12%, transparent);
            }
            .chart-bar-wrap {
                flex: 1;
                width: 100%;
                display: flex;
                align-items: flex-end;
                justify-content: center;
            }
            .chart-bar {
                width: 70%;
                max-width: 2rem;
                height: 0;
                border-radius: 6px 6px 2px 2px;
                background: linear-gradient(180deg, var(--brand-green), color-mix(in srgb, var(--brand-rose) 45%, #1a5c40));
                min-height: 2px;
                transition: height 0.8s cubic-bezier(0.22, 1, 0.36, 1), filter 0.15s ease;
            }
            .chart-col:hover .chart-bar,
            .chart-col.is-active .chart-bar {
                filter: brightness(1.12);
            }
            .chart-label {
                font-size: 0.62rem;
                opacity: 0.7;
                text-align: center;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                width: 100%;
            }
            .chart-val {
                font-size: 0.68rem;
                font-weight: 700;
                opacity: 0.85;
            }
            .chart-hint {
                margin: 0.55rem 0 0;
                font-size: 0.75rem;
                opacity: 0.75;
                min-height: 1.1rem;
            }
            .ingresos-head {
                display: flex;
                align-items: baseline;
                justify-content: space-between;
                gap: 0.75rem;
                margin: 0 0 0.55rem;
            }
            .ingresos-head h2 {
                margin: 0;
            }
            .ingresos-total {
                font-size: 1.35rem;
                font-weight: 800;
                letter-spacing: -0.02em;
                color: var(--brand-green);
            }
            .ingresos-total span {
                display: block;
                font-size: 0.68rem;
                font-weight: 600;
                opacity: 0.72;
                letter-spacing: 0;
                color: var(--text);
            }
            .chart--ingresos {
                height: 8.2rem;
                gap: 0.35rem;
            }
            .chart--ingresos .chart-bar {
                background: linear-gradient(
                    180deg,
                    color-mix(in srgb, var(--brand-rose) 55%, var(--brand-green)),
                    color-mix(in srgb, var(--brand-green) 70%, #0a2e20)
                );
            }
            .chart--ingresos a.chart-col {
                text-decoration: none;
                color: inherit;
            }
            .filtros-act {
                display: flex;
                flex-wrap: wrap;
                gap: 0.4rem;
                margin: 0 0 0.75rem;
            }
            .chip {
                appearance: none;
                border: 1px solid color-mix(in srgb, var(--brand-green) 35%, transparent);
                background: rgba(0, 0, 0, 0.25);
                color: var(--text);
                border-radius: 9999px;
                padding: 0.28rem 0.7rem;
                font-size: 0.72rem;
                font-weight: 700;
                font-family: inherit;
                cursor: pointer;
                transition: background 0.15s ease, border-color 0.15s ease;
            }
            .chip:hover {
                border-color: color-mix(in srgb, var(--brand-green) 65%, transparent);
            }
            .chip.is-active {
                background: color-mix(in srgb, var(--brand-green) 22%, transparent);
                border-color: color-mix(in srgb, var(--brand-green) 70%, transparent);
                color: #04261c;
            }
            .actividad {
                list-style: none;
                margin: 0;
                padding: 0;
                display: grid;
                gap: 0.55rem;
            }
            .actividad li {
                display: grid;
                grid-template-columns: auto 1fr auto;
                gap: 0.55rem;
                align-items: start;
                padding: 0.55rem 0.6rem;
                border-radius: 0.65rem;
                background: rgba(0, 0, 0, 0.22);
                border: 1px solid color-mix(in srgb, var(--brand-green) 18%, transparent);
                transition: transform 0.15s ease, border-color 0.15s ease, background 0.15s ease;
            }
            .actividad li:hover {
                transform: translateX(3px);
                border-color: color-mix(in srgb, var(--brand-green) 45%, transparent);
                background: rgba(0, 0, 0, 0.32);
            }
            .actividad li.is-hidden {
                display: none;
            }
            .actividad .dot {
                width: 0.55rem;
                height: 0.55rem;
                margin-top: 0.35rem;
                border-radius: 50%;
                background: var(--brand-green);
            }
            .actividad .t {
                margin: 0;
                font-size: 0.82rem;
                font-weight: 700;
            }
            .actividad .d {
                margin: 0.15rem 0 0;
                font-size: 0.72rem;
                opacity: 0.72;
            }
            .actividad a {
                font-size: 0.72rem;
                font-weight: 700;
                color: var(--brand-rose);
                text-decoration: none;
                white-space: nowrap;
                padding: 0.2rem 0.45rem;
                border-radius: 9999px;
                border: 1px solid color-mix(in srgb, var(--brand-rose) 40%, transparent);
                transition: background 0.15s ease;
            }
            .actividad a:hover {
                background: color-mix(in srgb, var(--brand-rose) 16%, transparent);
            }
            .accesos {
                display: grid;
                gap: 0.55rem;
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .acceso {
                display: block;
                text-decoration: none;
                color: inherit;
                padding: 0.7rem 0.75rem;
                border-radius: 0.75rem;
                border: 1px solid color-mix(in srgb, var(--brand-green) 28%, transparent);
                background: rgba(0, 0, 0, 0.22);
                transition: border-color 0.15s ease, transform 0.15s ease, background 0.15s ease;
            }
            .acceso:hover {
                transform: translateY(-2px);
                border-color: color-mix(in srgb, var(--brand-green) 60%, transparent);
                background: color-mix(in srgb, var(--brand-green) 10%, rgba(0, 0, 0, 0.22));
            }
            .acceso strong {
                display: block;
                font-size: 0.88rem;
            }
            .acceso span {
                display: block;
                margin-top: 0.2rem;
                font-size: 0.72rem;
                opacity: 0.72;
            }
            .sync-line {
                margin: 0;
                font-size: 0.78rem;
                line-height: 1.45;
                opacity: 0.82;
            }
            .empty {
                margin: 0;
                font-size: 0.82rem;
                opacity: 0.7;
            }
            .toast {
                position: fixed;
                right: 1rem;
                bottom: 3.5rem;
                z-index: 50;
                max-width: min(22rem, calc(100vw - 2rem));
                padding: 0.7rem 0.9rem;
                border-radius: 0.75rem;
                border: 1px solid color-mix(in srgb, var(--brand-green) 45%, transparent);
                background: rgba(6, 18, 14, 0.95);
                color: var(--text);
                font-size: 0.8rem;
                font-weight: 600;
                box-shadow: 0 16px 40px rgba(0, 0, 0, 0.45);
                opacity: 0;
                transform: translateY(8px);
                pointer-events: none;
                transition: opacity 0.2s ease, transform 0.2s ease;
            }
            .toast.is-on {
                opacity: 1;
                transform: translateY(0);
            }
            @media (max-width: 1100px) {
                .kpi-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            }
            @media (max-width: 980px) {
                .kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
                .span-5, .span-4, .span-7, .span-8 { grid-column: 1 / -1; }
            }
            @media (max-width: 560px) {
                .kpi-grid { grid-template-columns: 1fr; }
                .accesos { grid-template-columns: 1fr; }
            }
            @media (prefers-reduced-motion: reduce) {
                .panel { animation: none; opacity: 1; transform: none; }
                .bar-fill, .stack-fill, .chart-bar { transition: none; }
            }
        </style>
    </head>
    <body>
        @include('partials.logo-institucional')
        <header class="top">
            <div>
                <h1>Dashboard</h1>
                <p class="sub">Resumen operativo · {{ $mesEtiqueta }} · inventario, salidas y decomisos.</p>
                <p class="live-clock" id="dash-clock" aria-live="polite"></p>
            </div>
            <a class="btn" href="{{ route('menu') }}">← Volver al menú</a>
        </header>

        @php
            $inv = max(1, (int) $inventarioTotal);
            $pOk = (int) round(($disponiblesOk / $inv) * 100);
            $pWarn = (int) round(($porVencer / $inv) * 100);
            $pDanger = (int) round(($vencidas / $inv) * 100);
            $maxSerie = max(1, collect($serieDespachos)->max('total'));
            $hoyYmd = now()->timezone(config('app.timezone'))->format('Y-m-d');
            $mesPref = now()->timezone(config('app.timezone'))->format('Y-m');
        @endphp

        <div class="dash" id="dash-root">
            <div class="kpi-grid">
                <a class="panel kpi" href="{{ route('inventario.lenguas') }}">
                    <div class="ring js-ring" data-target="{{ $inventarioTotal > 0 ? 100 : 0 }}" style="--ring: var(--brand-green)"><span class="js-ring-label">0</span></div>
                    <div>
                        <p class="num js-count" data-target="{{ (int) $inventarioTotal }}">0</p>
                        <p class="label">En inventario</p>
                        <p class="hint">Lenguas disponibles (sin despachar / decomisar).</p>
                        <p class="cta">Abrir inventario →</p>
                    </div>
                </a>
                <a class="panel kpi" href="{{ route('inventario.lenguas', ['por_vencer' => 1]) }}">
                    <div class="ring js-ring" data-target="{{ min(100, $pWarn) }}" style="--ring: var(--warn)"><span class="js-ring-label">0%</span></div>
                    <div>
                        <p class="num js-count" data-target="{{ (int) $porVencer }}">0</p>
                        <p class="label">Por vencer</p>
                        <p class="hint">Vencen hoy o mañana (vida útil 4 días).</p>
                        <p class="cta">Revisar stock →</p>
                    </div>
                </a>
                <a class="panel kpi" href="{{ route('inventario.lenguas', ['vencidas' => 1]) }}">
                    <div class="ring js-ring" data-target="{{ min(100, $pDanger) }}" style="--ring: var(--danger)"><span class="js-ring-label">0%</span></div>
                    <div>
                        <p class="num js-count" data-target="{{ (int) $vencidas }}">0</p>
                        <p class="label">Vencidas</p>
                        <p class="hint">Ya superaron la fecha de vencimiento en stock.</p>
                        <p class="cta">Ver vencidas →</p>
                    </div>
                </a>
                <a class="panel kpi" href="{{ route('entrega.conformidad') }}">
                    <div class="ring js-ring" data-target="{{ $despachosMes > 0 ? 100 : 0 }}" style="--ring: var(--brand-rose)"><span class="js-ring-label">MES</span></div>
                    <div>
                        <p class="num js-count" data-target="{{ (int) $despachosMes }}">0</p>
                        <p class="label">Despachos del mes</p>
                        <p class="hint">{{ number_format($lenguasDespachadasMes) }} lenguas · {{ number_format($despostesMes) }} desposte(s).</p>
                        <p class="cta">Ir a conformidad →</p>
                    </div>
                </a>
                <a class="panel kpi" href="{{ route('decomisos.lenguas') }}">
                    <div class="ring js-ring" data-target="{{ $decomisosMes > 0 ? min(100, max(12, (int) round(($decomisosHoy / max(1, $decomisosMes)) * 100))) : 0 }}" style="--ring: var(--danger)"><span class="js-ring-label">MES</span></div>
                    <div>
                        <p class="num js-count" data-target="{{ (int) $decomisosMes }}">0</p>
                        <p class="label">Decomisos del mes</p>
                        <p class="hint">
                            {{ number_format($decomisosSirtMes) }} SIRT · {{ number_format($decomisosManualesMes) }} manual(es)
                            · hoy {{ number_format($decomisosHoy) }}{{ $decomisosSirtOk ? '' : ' · SIRT no consultado' }}.
                        </p>
                        <p class="cta">Abrir decomisos →</p>
                    </div>
                </a>
            </div>

            <article class="panel span-5">
                <h2>Salud del stock</h2>
                <p class="pct-big"><span class="js-count" data-target="{{ (int) $pctOk }}">0</span>%</p>
                <div class="bar-track" aria-hidden="true">
                    <div class="bar-fill js-width" data-width="{{ $pctOk }}"></div>
                </div>
                <p class="hint" style="margin:0;font-size:0.8rem;opacity:0.78;line-height:1.45">
                    Proporción de lenguas en inventario sin alerta de vencimiento inmediato.
                    Los decomisos del mes están en el KPI dedicado (arriba).
                </p>
            </article>

            <article class="panel span-7">
                <h2>Composición del inventario · clic para resaltar</h2>
                <div class="stack" id="stack-composicion">
                    <button type="button" class="stack-row" data-focus="ok" style="width:100%;text-align:left;background:transparent;color:inherit;font:inherit">
                        <span>Disponible OK</span>
                        <div class="stack-track"><div class="stack-fill stack-fill--ok js-width" data-width="{{ $pOk }}"></div></div>
                        <strong>{{ number_format($disponiblesOk) }}</strong>
                    </button>
                    <button type="button" class="stack-row" data-focus="warn" style="width:100%;text-align:left;background:transparent;color:inherit;font:inherit">
                        <span>Por vencer</span>
                        <div class="stack-track"><div class="stack-fill stack-fill--warn js-width" data-width="{{ $pWarn }}"></div></div>
                        <strong>{{ number_format($porVencer) }}</strong>
                    </button>
                    <button type="button" class="stack-row" data-focus="danger" style="width:100%;text-align:left;background:transparent;color:inherit;font:inherit">
                        <span>Vencidas</span>
                        <div class="stack-track"><div class="stack-fill stack-fill--danger js-width" data-width="{{ $pDanger }}"></div></div>
                        <strong>{{ number_format($vencidas) }}</strong>
                    </button>
                </div>
            </article>

            <article class="panel span-8">
                <h2>Despachos · últimos 7 días · clic en un día</h2>
                <div class="chart" id="chart-despachos" role="list" aria-label="Barras de despachos por día">
                    @foreach ($serieDespachos as $punto)
                        @php
                            $h = (int) round(($punto['total'] / $maxSerie) * 100);
                        @endphp
                        <button
                            type="button"
                            class="chart-col"
                            role="listitem"
                            data-fecha="{{ $punto['fecha'] }}"
                            data-total="{{ $punto['total'] }}"
                            data-etiqueta="{{ $punto['etiqueta'] }}"
                            data-height="{{ max(2, $h) }}"
                            title="{{ $punto['etiqueta'] }}: {{ $punto['total'] }} despacho(s)"
                        >
                            <span class="chart-val">{{ $punto['total'] }}</span>
                            <div class="chart-bar-wrap">
                                <div class="chart-bar js-bar-h"></div>
                            </div>
                            <span class="chart-label">{{ $punto['etiqueta'] }}</span>
                        </button>
                    @endforeach
                </div>
                <p class="chart-hint" id="chart-hint">Pase el cursor o pulse un día para filtrar la actividad.</p>
            </article>

            <article class="panel span-4">
                @php
                    $maxIngresos = max(1, collect($serieIngresos)->max('total'));
                @endphp
                <div class="ingresos-head">
                    <h2>Ingresos · 5 días</h2>
                    <p class="ingresos-total">
                        <span class="js-count" data-target="{{ (int) $ingresosCincoDiasTotal }}">0</span>
                        <span>lenguas registradas</span>
                    </p>
                </div>
                <div class="chart chart--ingresos" role="list" aria-label="Ingresos de lenguas por día">
                    @foreach ($serieIngresos as $punto)
                        @php
                            $hIng = (int) round(($punto['total'] / $maxIngresos) * 100);
                        @endphp
                        <a
                            class="chart-col"
                            role="listitem"
                            href="{{ route('inventario.lenguas', ['fecha_desde' => $punto['fecha'], 'fecha_hasta' => $punto['fecha']]) }}"
                            data-height="{{ max(2, $hIng) }}"
                            title="{{ $punto['etiqueta'] }}: {{ $punto['total'] }} ingreso(s)"
                        >
                            <span class="chart-val">{{ $punto['total'] }}</span>
                            <div class="chart-bar-wrap">
                                <div class="chart-bar js-bar-h"></div>
                            </div>
                            <span class="chart-label">{{ $punto['etiqueta'] }}</span>
                        </a>
                    @endforeach
                </div>
                <p class="chart-hint">Por fecha de registro. Clic en un día para ver el detalle.</p>
                <p style="margin:0.65rem 0 0">
                    <a class="btn" href="{{ route('ingresos.lenguas') }}">Ver ingresos →</a>
                </p>
            </article>

            <article class="panel span-7">
                <h2>Actividad reciente · despachos</h2>
                <div class="filtros-act" role="group" aria-label="Filtrar actividad">
                    <button type="button" class="chip is-active" data-filtro="todos">Todos</button>
                    <button type="button" class="chip" data-filtro="hoy">Hoy</button>
                    <button type="button" class="chip" data-filtro="mes">Este mes</button>
                </div>
                @if ($ultimosDespachos->isEmpty())
                    <p class="empty">Sin despachos registrados todavía.</p>
                @else
                    <ul class="actividad" id="lista-actividad">
                        @foreach ($ultimosDespachos as $d)
                            @php
                                $fechaKey = $d->realizado_at?->timezone(config('app.timezone'))->format('Y-m-d') ?? '';
                                $mesKey = $d->realizado_at?->timezone(config('app.timezone'))->format('Y-m') ?? '';
                            @endphp
                            <li data-fecha="{{ $fechaKey }}" data-mes="{{ $mesKey }}">
                                <span class="dot" aria-hidden="true"></span>
                                <div>
                                    <p class="t">
                                        {{ $d->realizado_at?->timezone(config('app.timezone'))->format('d/m H:i') ?? '—' }}
                                        · {{ $d->placa ?: 'Sin placa' }}
                                    </p>
                                    <p class="d">
                                        {{ $d->empresa ?: 'Operador —' }} · {{ (int) ($d->lenguas_count ?? 0) }} lengua(s)
                                        · {{ $d->user?->name ?? 'Usuario —' }}
                                    </p>
                                </div>
                                @if ((int) ($d->lenguas_count ?? 0) > 0)
                                    <a href="{{ route('entrega.conformidad.pdf', $d) }}" target="_blank" rel="noopener">PDF</a>
                                @else
                                    <span class="d">—</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                    <p class="empty" id="actividad-vacia" hidden>No hay despachos para ese filtro.</p>
                @endif
                <p style="margin:0.75rem 0 0">
                    <a class="btn" href="{{ route('entrega.conformidad') }}">Ver conformidad</a>
                </p>
            </article>

            <article class="panel span-5">
                <h2>Accesos rápidos</h2>
                <div class="accesos">
                    @foreach ($accesos as $a)
                        <a class="acceso" href="{{ route($a['route']) }}">
                            <strong>{{ $a['titulo'] }}</strong>
                            <span>{{ $a['desc'] }}</span>
                        </a>
                    @endforeach
                </div>
            </article>
        </div>

        <div class="toast" id="dash-toast" role="status" aria-live="polite"></div>

        <script>
            (function () {
                var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                var toastEl = document.getElementById('dash-toast');
                var toastTimer = null;

                function toast(msg) {
                    if (!toastEl) return;
                    toastEl.textContent = msg;
                    toastEl.classList.add('is-on');
                    clearTimeout(toastTimer);
                    toastTimer = setTimeout(function () {
                        toastEl.classList.remove('is-on');
                    }, 2200);
                }

                function animateCount(el, target, suffix) {
                    suffix = suffix || '';
                    if (reduce) {
                        el.textContent = Number(target).toLocaleString('es-CO') + suffix;
                        return;
                    }
                    var start = 0;
                    var end = Number(target) || 0;
                    var t0 = null;
                    var dur = 700;
                    function frame(ts) {
                        if (!t0) t0 = ts;
                        var p = Math.min(1, (ts - t0) / dur);
                        var eased = 1 - Math.pow(1 - p, 3);
                        var val = Math.round(start + (end - start) * eased);
                        el.textContent = val.toLocaleString('es-CO') + suffix;
                        if (p < 1) requestAnimationFrame(frame);
                    }
                    requestAnimationFrame(frame);
                }

                document.querySelectorAll('.js-count').forEach(function (el) {
                    animateCount(el, el.getAttribute('data-target') || '0', '');
                });

                document.querySelectorAll('.js-ring').forEach(function (ring) {
                    var target = Number(ring.getAttribute('data-target') || 0);
                    var label = ring.querySelector('.js-ring-label');
                    var isPct = label && String(label.textContent).indexOf('%') !== -1;
                    var isMes = label && String(label.textContent).trim() === 'MES';
                    if (reduce) {
                        ring.style.setProperty('--p', String(target));
                        if (label && !isMes) {
                            label.textContent = isPct ? target + '%' : (target >= 100 ? 'OK' : '—');
                        }
                        return;
                    }
                    ring.style.setProperty('--p', '0');
                    var t0 = null;
                    function frame(ts) {
                        if (!t0) t0 = ts;
                        var p = Math.min(1, (ts - t0) / 850);
                        var eased = 1 - Math.pow(1 - p, 3);
                        var cur = Math.round(target * eased);
                        ring.style.setProperty('--p', String(cur));
                        if (label && !isMes) {
                            if (isPct) label.textContent = cur + '%';
                            else label.textContent = cur >= 100 ? 'OK' : (cur === 0 ? '—' : cur + '%');
                        }
                        if (p < 1) requestAnimationFrame(frame);
                    }
                    requestAnimationFrame(frame);
                });

                setTimeout(function () {
                    document.querySelectorAll('.js-width').forEach(function (el) {
                        el.style.width = (el.getAttribute('data-width') || '0') + '%';
                    });
                    document.querySelectorAll('.js-bar-h').forEach(function (el) {
                        var col = el.closest('[data-height]');
                        el.style.height = ((col && col.getAttribute('data-height')) || '2') + '%';
                    });
                }, reduce ? 0 : 40);

                var clock = document.getElementById('dash-clock');
                function tickClock() {
                    if (!clock) return;
                    var d = new Date();
                    var pad = function (n) { return n < 10 ? '0' + n : '' + n; };
                    clock.textContent =
                        'En vivo · ' +
                        pad(d.getDate()) +
                        '/' +
                        pad(d.getMonth() + 1) +
                        '/' +
                        d.getFullYear() +
                        ' ' +
                        pad(d.getHours()) +
                        ':' +
                        pad(d.getMinutes()) +
                        ':' +
                        pad(d.getSeconds());
                }
                tickClock();
                setInterval(tickClock, 1000);

                var lista = document.getElementById('lista-actividad');
                var vacia = document.getElementById('actividad-vacia');
                var hoy = @json($hoyYmd);
                var mes = @json($mesPref);
                var filtroActual = 'todos';
                var fechaChart = null;

                function aplicarFiltroActividad() {
                    if (!lista) return;
                    var visibles = 0;
                    lista.querySelectorAll('li').forEach(function (li) {
                        var f = li.getAttribute('data-fecha') || '';
                        var m = li.getAttribute('data-mes') || '';
                        var ok = true;
                        if (fechaChart) {
                            ok = f === fechaChart;
                        } else if (filtroActual === 'hoy') {
                            ok = f === hoy;
                        } else if (filtroActual === 'mes') {
                            ok = m === mes;
                        }
                        li.classList.toggle('is-hidden', !ok);
                        if (ok) visibles++;
                    });
                    if (vacia) vacia.hidden = visibles > 0;
                }

                document.querySelectorAll('.filtros-act .chip').forEach(function (chip) {
                    chip.addEventListener('click', function () {
                        fechaChart = null;
                        document.querySelectorAll('.chart-col').forEach(function (c) {
                            c.classList.remove('is-active');
                        });
                        document.querySelectorAll('.filtros-act .chip').forEach(function (c) {
                            c.classList.toggle('is-active', c === chip);
                        });
                        filtroActual = chip.getAttribute('data-filtro') || 'todos';
                        aplicarFiltroActividad();
                        toast('Actividad: ' + chip.textContent.trim());
                    });
                });

                var hint = document.getElementById('chart-hint');
                document.querySelectorAll('#chart-despachos .chart-col').forEach(function (col) {
                    col.addEventListener('mouseenter', function () {
                        if (!hint) return;
                        hint.textContent =
                            col.getAttribute('data-etiqueta') +
                            ': ' +
                            col.getAttribute('data-total') +
                            ' despacho(s). Clic para filtrar actividad.';
                    });
                    col.addEventListener('click', function () {
                        var fecha = col.getAttribute('data-fecha');
                        var active = col.classList.contains('is-active');
                        document.querySelectorAll('.chart-col').forEach(function (c) {
                            c.classList.remove('is-active');
                        });
                        document.querySelectorAll('.filtros-act .chip').forEach(function (c) {
                            c.classList.remove('is-active');
                        });
                        if (active) {
                            fechaChart = null;
                            filtroActual = 'todos';
                            var todos = document.querySelector('.filtros-act .chip[data-filtro="todos"]');
                            if (todos) todos.classList.add('is-active');
                            if (hint) hint.textContent = 'Filtro de día quitado.';
                            toast('Mostrando toda la actividad reciente');
                        } else {
                            col.classList.add('is-active');
                            fechaChart = fecha;
                            if (hint) {
                                hint.textContent =
                                    'Filtrando actividad del ' +
                                    col.getAttribute('data-etiqueta') +
                                    ' (' +
                                    col.getAttribute('data-total') +
                                    ' despacho(s)).';
                            }
                            toast('Actividad filtrada: ' + col.getAttribute('data-etiqueta'));
                        }
                        aplicarFiltroActividad();
                    });
                });

                document.querySelectorAll('#stack-composicion .stack-row').forEach(function (row) {
                    row.addEventListener('click', function () {
                        var focus = row.getAttribute('data-focus');
                        document.querySelectorAll('#stack-composicion .stack-row').forEach(function (r) {
                            r.classList.toggle('is-active', r === row);
                        });
                        var msgs = {
                            ok: 'Stock disponible sin alerta inmediata',
                            warn: 'Priorice lenguas por vencer hoy/mañana',
                            danger: 'Revise lenguas ya vencidas en inventario',
                        };
                        toast(msgs[focus] || 'Composición seleccionada');
                    });
                });
            })();
        </script>
    </body>
</html>
