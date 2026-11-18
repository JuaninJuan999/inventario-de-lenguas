<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @include('partials.favicon')
        <title>{{ config('app.name') }}</title>
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
            .user-menu {
                position: relative;
            }
            .user-menu__toggle {
                display: inline-flex;
                align-items: center;
                gap: 0.55rem;
                padding: 0.28rem 0.55rem 0.28rem 0.28rem;
                border-radius: 9999px;
                border: 1px solid color-mix(in srgb, var(--brand-green) 45%, transparent);
                background: color-mix(in srgb, var(--brand-green) 12%, rgba(0, 0, 0, 0.35));
                color: #f4fffa;
                cursor: pointer;
                font-family: inherit;
            }
            .user-menu__toggle:focus-visible {
                outline: 2px solid var(--brand-green);
                outline-offset: 2px;
            }
            .user-menu__avatar,
            .user-menu__avatar-fallback {
                width: 2rem;
                height: 2rem;
                border-radius: 9999px;
                flex-shrink: 0;
            }
            .user-menu__avatar {
                object-fit: cover;
                border: 1px solid color-mix(in srgb, var(--brand-green) 40%, transparent);
            }
            .user-menu__avatar-fallback {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: 0.72rem;
                font-weight: 800;
                background: color-mix(in srgb, var(--brand-green) 22%, transparent);
            }
            .user-menu__burger {
                display: inline-flex;
                flex-direction: column;
                justify-content: center;
                gap: 0.22rem;
                width: 1.15rem;
                margin-right: 0.2rem;
            }
            .user-menu__burger span {
                display: block;
                height: 2px;
                width: 100%;
                border-radius: 9999px;
                background: currentColor;
            }
            .user-menu__panel {
                position: absolute;
                right: 0;
                top: calc(100% + 0.45rem);
                min-width: 12.5rem;
                padding: 0.4rem;
                border-radius: 0.85rem;
                border: 1px solid color-mix(in srgb, var(--brand-green) 40%, transparent);
                background: rgba(6, 18, 14, 0.96);
                box-shadow: 0 16px 40px rgba(0, 0, 0, 0.45);
                z-index: 40;
            }
            .user-menu__panel[hidden] {
                display: none;
            }
            .user-menu__item {
                display: flex;
                width: 100%;
                align-items: center;
                gap: 0.55rem;
                padding: 0.65rem 0.75rem;
                border-radius: 0.55rem;
                border: none;
                background: transparent;
                color: #f4fffa;
                font: inherit;
                font-size: 0.88rem;
                font-weight: 600;
                text-decoration: none;
                cursor: pointer;
                text-align: left;
            }
            .user-menu__item:hover,
            .user-menu__item:focus-visible {
                background: color-mix(in srgb, var(--brand-green) 16%, transparent);
                outline: none;
            }
            .user-menu__item--danger {
                color: #ffd4d4;
            }
            .user-menu__item svg {
                width: 1rem;
                height: 1rem;
                flex-shrink: 0;
            }
            .alert-ok {
                margin: 0 0 1rem;
                padding: 0.75rem 1rem;
                border-radius: 0.65rem;
                font-size: 0.88rem;
                font-weight: 600;
                border: 1px solid color-mix(in srgb, var(--brand-green) 45%, transparent);
                background: color-mix(in srgb, var(--brand-green) 14%, rgba(0, 0, 0, 0.35));
                color: #eafff4;
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
            .alert-menu {
                margin: 0 0 1rem;
                padding: 0.75rem 1rem;
                border-radius: 0.65rem;
                font-size: 0.88rem;
                font-weight: 600;
                border: 1px solid color-mix(in srgb, #ffb4b4 55%, transparent);
                background: color-mix(in srgb, #5c1a1a 55%, rgba(0, 0, 0, 0.35));
                color: #ffe4e4;
            }
            .visually-hidden {
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
        <header class="top">
            <div>
                <h1>Menú</h1>
                <p class="sub">Hola, {{ auth()->user()->name }}. Aquí irán los módulos de la aplicación.</p>
            </div>
            <div class="actions">
                <a class="btn btn--ghost" href="{{ url('/') }}">Inicio</a>
                <div class="user-menu" data-user-menu>
                    <button
                        type="button"
                        class="user-menu__toggle"
                        id="user-menu-toggle"
                        aria-haspopup="menu"
                        aria-expanded="false"
                        aria-controls="user-menu-panel"
                        title="Cuenta"
                    >
                        @if (auth()->user()->avatarUrl())
                            <img
                                class="user-menu__avatar"
                                src="{{ auth()->user()->avatarUrl() }}"
                                alt=""
                            />
                        @else
                            <span class="user-menu__avatar-fallback" aria-hidden="true">{{ auth()->user()->avatarInitials() }}</span>
                        @endif
                        <span class="user-menu__burger" aria-hidden="true">
                            <span></span><span></span><span></span>
                        </span>
                        <span class="visually-hidden">Abrir menú de cuenta</span>
                    </button>
                    <div class="user-menu__panel" id="user-menu-panel" role="menu" aria-labelledby="user-menu-toggle" hidden>
                        <a class="user-menu__item" role="menuitem" href="{{ route('perfil.edit') }}">
                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <circle cx="12" cy="8" r="3.5" fill="none" stroke="currentColor" stroke-width="2"/>
                                <path d="M5 19.5c1.6-3.2 4-4.8 7-4.8s5.4 1.6 7 4.8" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            Mi perfil
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="user-menu__item user-menu__item--danger" role="menuitem" type="submit">
                                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                    <path d="M10 4H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    <path d="M15 16l4-4-4-4M9 12h10" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Cerrar sesión
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <section class="modules" aria-labelledby="modules-heading">
            <h2 id="modules-heading">Módulos</h2>
            @if (session('status'))
                <p class="alert-ok" role="status">{{ session('status') }}</p>
            @endif
            @if (session('module_denied'))
                <p class="alert-menu" role="alert">{{ session('module_denied') }}</p>
            @endif
            <div class="module-grid">
                @forelse ($menuModulos as $modulo)
                    <a class="module-card" href="{{ route($modulo->route_name) }}">
                        <h3>{{ $modulo->nombre }}</h3>
                        @if ($modulo->descripcion !== null && $modulo->descripcion !== '')
                            <p>{{ $modulo->descripcion }}</p>
                        @endif
                        <span class="arrow">Abrir →</span>
                    </a>
                @empty
                    <p class="alert-menu" role="status">
                        No hay módulos configurados para su usuario. Ejecute primero las migraciones y luego el seeder
                        ModulosMatrizSeeder o el comando Artisan modulos:sync-matriz para cargar las tablas locales del menú.
                    </p>
                @endforelse
            </div>
        </section>
        <script>
            (function () {
                var root = document.querySelector('[data-user-menu]');
                if (!root) return;
                var toggle = root.querySelector('.user-menu__toggle');
                var panel = root.querySelector('.user-menu__panel');
                if (!toggle || !panel) return;

                function setOpen(open) {
                    panel.hidden = !open;
                    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                }

                toggle.addEventListener('click', function (e) {
                    e.stopPropagation();
                    setOpen(panel.hidden);
                });

                document.addEventListener('click', function (e) {
                    if (!root.contains(e.target)) setOpen(false);
                });

                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape') setOpen(false);
                });
            })();
        </script>
    </body>
</html>
