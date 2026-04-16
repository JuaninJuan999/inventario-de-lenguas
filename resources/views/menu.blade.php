<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Menú — {{ config('app.name') }}</title>
        <style>
            :root {
                --brand-rose: #f9dff8;
                --brand-green: #7ce8ad;
                --bg: #050807;
            }
            * {
                box-sizing: border-box;
            }
            body {
                margin: 0;
                min-height: 100vh;
                min-height: 100dvh;
                font-family: ui-sans-serif, system-ui, sans-serif;
                background: var(--bg);
                color: #f4fffa;
                padding: 1.75rem 1.5rem 2rem;
            }
            .top {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                max-width: 48rem;
                margin: 0 auto 2rem;
            }
            h1 {
                font-size: 1.35rem;
                font-weight: 700;
                margin: 0;
            }
            .sub {
                margin: 0.35rem 0 0;
                font-size: 0.9rem;
                opacity: 0.85;
            }
            .actions {
                display: flex;
                flex-wrap: wrap;
                gap: 0.5rem;
                align-items: center;
            }
            .btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0.5rem 1rem;
                border-radius: 9999px;
                font-size: 0.875rem;
                font-weight: 600;
                text-decoration: none;
                border: none;
                cursor: pointer;
                font-family: inherit;
            }
            .btn--ghost {
                color: var(--brand-rose);
                background: color-mix(in srgb, var(--brand-rose) 10%, transparent);
                border: 1px solid color-mix(in srgb, var(--brand-rose) 45%, transparent);
            }
            .btn--primary {
                color: #04261c;
                background: var(--brand-green);
                border: 1px solid color-mix(in srgb, var(--brand-green) 50%, #fff);
            }
            .modules {
                max-width: 48rem;
                margin: 0 auto;
            }
            .modules h2 {
                font-size: 0.75rem;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.08em;
                opacity: 0.65;
                margin: 0 0 0.75rem;
            }
            .module-grid {
                display: grid;
                gap: 1rem;
                grid-template-columns: repeat(auto-fill, minmax(15rem, 1fr));
            }
            .module-card {
                display: block;
                padding: 1.25rem 1.35rem;
                border-radius: 1rem;
                text-decoration: none;
                color: inherit;
                border: 1px solid color-mix(in srgb, var(--brand-green) 40%, transparent);
                background: rgba(6, 18, 14, 0.65);
                box-shadow: 0 0 0 1px color-mix(in srgb, var(--brand-rose) 8%, transparent) inset;
                transition:
                    transform 0.15s ease,
                    border-color 0.15s ease,
                    box-shadow 0.15s ease;
            }
            .module-card:hover {
                transform: translateY(-2px);
                border-color: color-mix(in srgb, var(--brand-green) 65%, transparent);
                box-shadow:
                    0 0 0 1px color-mix(in srgb, var(--brand-rose) 12%, transparent) inset,
                    0 12px 32px rgba(0, 0, 0, 0.35);
            }
            .module-card:focus-visible {
                outline: 2px solid var(--brand-green);
                outline-offset: 3px;
            }
            .module-card h3 {
                margin: 0 0 0.4rem;
                font-size: 1.05rem;
                font-weight: 700;
                color: #f4fffa;
            }
            .module-card p {
                margin: 0;
                font-size: 0.85rem;
                opacity: 0.8;
                line-height: 1.45;
            }
            .module-card .arrow {
                margin-top: 0.85rem;
                font-size: 0.8rem;
                font-weight: 600;
                color: var(--brand-green);
            }
        </style>
    </head>
    <body>
        <header class="top">
            <div>
                <h1>Menú</h1>
                <p class="sub">Hola, {{ auth()->user()->name }}. Aquí irán los módulos de la aplicación.</p>
            </div>
            <div class="actions">
                <a class="btn btn--ghost" href="{{ url('/') }}">Inicio</a>
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button class="btn btn--primary" type="submit">Cerrar sesión</button>
                </form>
            </div>
        </header>

        <section class="modules" aria-labelledby="modules-heading">
            <h2 id="modules-heading">Módulos</h2>
            <div class="module-grid">
                <a class="module-card" href="{{ route('dashboard') }}">
                    <h3>Dashboard</h3>
                    <p>Panel principal: resumen, indicadores y accesos rápidos del inventario.</p>
                    <span class="arrow">Abrir →</span>
                </a>
                <a class="module-card" href="{{ route('inventario.lenguas') }}">
                    <h3>Inventario de Lenguas</h3>
                    <p>
                        Réplica local del ingreso de <strong>lenguas</strong> con trazabilidad corporativa, consultas
                        filtradas e indicadores de vigencia operativa para el control de existencias a planta.
                    </p>
                    <span class="arrow">Abrir →</span>
                </a>
                <a class="module-card" href="{{ route('ingresos.lenguas') }}">
                    <h3>Ingresos de Lenguas</h3>
                    <p>Listado de <strong>ingresos</strong> desde trazabilidad (insensibilización y datos asociados).</p>
                    <span class="arrow">Abrir →</span>
                </a>
                <a class="module-card" href="{{ route('despacho.lenguas') }}">
                    <h3>Despacho de Lenguas</h3>
                    <p>Salida, pedidos y distribución de <strong>lenguas</strong>.</p>
                    <span class="arrow">Abrir →</span>
                </a>
                <a class="module-card" href="{{ route('entrega.conformidad') }}">
                    <h3>Entrega de Conformidad</h3>
                    <p>Actas de entrega conforme y recepción de <strong>lenguas</strong>.</p>
                    <span class="arrow">Abrir →</span>
                </a>
                <a class="module-card" href="{{ route('decomisos.lenguas') }}">
                    <h3>Decomisos de Lenguas</h3>
                    <p>Registro de <strong>lenguas</strong> decomisadas o retiradas.</p>
                    <span class="arrow">Abrir →</span>
                </a>
            </div>
        </section>
    </body>
</html>
