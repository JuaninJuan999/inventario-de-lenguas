<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Inventario de Lenguas — {{ config('app.name') }}</title>
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
            .btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0.5rem 1rem;
                border-radius: 9999px;
                font-size: 0.875rem;
                font-weight: 600;
                text-decoration: none;
                border: 1px solid color-mix(in srgb, var(--brand-rose) 45%, transparent);
                color: var(--brand-rose);
                background: color-mix(in srgb, var(--brand-rose) 10%, transparent);
            }
            .btn:hover {
                background: color-mix(in srgb, var(--brand-rose) 16%, transparent);
            }
            .content {
                max-width: 48rem;
                margin: 0 auto;
                padding: 1.5rem 1.25rem;
                border-radius: 1rem;
                border: 1px solid color-mix(in srgb, var(--brand-green) 35%, transparent);
                background: rgba(6, 18, 14, 0.55);
                font-size: 0.95rem;
                line-height: 1.55;
                opacity: 0.92;
            }
        </style>
    </head>
    <body>
        <header class="top">
            <div>
                <h1>Inventario de Lenguas</h1>
                <p class="sub">Gestión del inventario de <strong>lenguas</strong> (producto cárnico).</p>
            </div>
            <a class="btn" href="{{ route('menu') }}">← Volver al menú</a>
        </header>

        <div class="content">
            <p style="margin: 0;">
                Aquí podrás registrar, consultar y administrar las <strong>lenguas</strong> (stock, calidades, movimientos, etc.) cuando implementes las pantallas y la lógica de negocio.
            </p>
        </div>
    </body>
</html>
