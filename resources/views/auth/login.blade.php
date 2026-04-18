<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @include('partials.favicon')
        <title>Iniciar sesión — {{ config('app.name') }}</title>
        <style>
            :root {
                --brand-rose: #f9dff8;
                --brand-green: #7ce8ad;
                --bg: #050807;
                --surface: rgba(6, 18, 14, 0.88);
            }
            * {
                box-sizing: border-box;
            }
            body {
                margin: 0;
                min-height: 100vh;
                min-height: 100dvh;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 1rem;
                padding: 1.5rem;
                font-family: ui-sans-serif, system-ui, sans-serif;
                background: var(--bg);
                color: #f4fffa;
            }
            .card {
                width: 100%;
                max-width: 22rem;
                padding: 1.75rem 1.5rem;
                border-radius: 1.25rem;
                background: var(--surface);
                border: 1px solid color-mix(in srgb, var(--brand-green) 35%, transparent);
                box-shadow:
                    0 0 0 1px color-mix(in srgb, var(--brand-rose) 12%, transparent) inset,
                    0 24px 56px rgba(0, 0, 0, 0.55);
            }
            h1 {
                font-size: 1.25rem;
                font-weight: 700;
                margin: 0 0 1.25rem;
                text-align: center;
            }
            label {
                display: block;
                font-size: 0.8rem;
                font-weight: 600;
                margin-bottom: 0.35rem;
                color: color-mix(in srgb, #f4fffa 88%, transparent);
            }
            .field {
                margin-bottom: 1rem;
            }
            input[type="text"],
            input[type="password"] {
                width: 100%;
                padding: 0.6rem 0.75rem;
                border-radius: 0.5rem;
                border: 1px solid color-mix(in srgb, var(--brand-green) 40%, #1a3d2e);
                background: rgba(0, 0, 0, 0.35);
                color: #f4fffa;
                font-size: 0.95rem;
            }
            .password-wrap input {
                padding-right: 2.85rem;
            }
            input:focus {
                outline: 2px solid var(--brand-green);
                outline-offset: 2px;
            }
            .password-wrap {
                position: relative;
            }
            .toggle-password {
                position: absolute;
                right: 0.35rem;
                top: 50%;
                transform: translateY(-50%);
                margin: 0;
                padding: 0.35rem 0.5rem;
                border: none;
                border-radius: 0.35rem;
                background: color-mix(in srgb, var(--brand-green) 18%, transparent);
                color: var(--brand-green);
                font-size: 0.75rem;
                font-weight: 600;
                cursor: pointer;
                line-height: 1.2;
            }
            .toggle-password:hover {
                background: color-mix(in srgb, var(--brand-green) 28%, transparent);
            }
            .toggle-password:focus-visible {
                outline: 2px solid var(--brand-green);
                outline-offset: 2px;
            }
            .remember {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                margin-bottom: 1.25rem;
                font-size: 0.875rem;
            }
            .remember input {
                width: auto;
            }
            .error {
                color: #fca5a5;
                font-size: 0.8rem;
                margin-top: 0.35rem;
            }
            .flash-info {
                margin: 0 0 1rem;
                padding: 0.65rem 0.75rem;
                border-radius: 0.5rem;
                font-size: 0.82rem;
                line-height: 1.45;
                color: #ffe4e4;
                border: 1px solid color-mix(in srgb, #ffb4b4 45%, transparent);
                background: color-mix(in srgb, #5c1a1a 45%, rgba(0, 0, 0, 0.35));
            }
            button[type="submit"] {
                width: 100%;
                cursor: pointer;
                padding: 0.65rem 1rem;
                border-radius: 9999px;
                border: 1px solid color-mix(in srgb, var(--brand-green) 50%, #fff);
                background: linear-gradient(145deg, var(--brand-green), color-mix(in srgb, var(--brand-green) 78%, #1a5c40));
                color: #04261c;
                font-weight: 600;
                font-size: 0.95rem;
            }
            button[type="submit"]:hover {
                filter: brightness(1.05);
            }
            .back {
                display: block;
                text-align: center;
                margin-top: 1.25rem;
                font-size: 0.875rem;
                color: var(--brand-rose);
            }
        </style>
    </head>
    <body>
        @include('partials.logo-institucional')
        <div class="card">
            <h1>Iniciar sesión</h1>

            @if (session('account_inactive'))
                <p class="flash-info" role="alert">{{ session('account_inactive') }}</p>
            @endif

            <form method="POST" action="{{ route('login') }}" novalidate>
                @csrf

                <div class="field">
                    <label for="username">Usuario</label>
                    <input
                        id="username"
                        type="text"
                        name="username"
                        value="{{ old('username') }}"
                        required
                        autocomplete="username"
                        autofocus
                        spellcheck="false"
                        autocapitalize="none"
                    >
                    @error('username')
                        <p class="error" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div class="field">
                    <label for="password">Contraseña</label>
                    <div class="password-wrap">
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                        >
                        <button
                            type="button"
                            class="toggle-password"
                            id="toggle-password"
                            aria-controls="password"
                            aria-pressed="false"
                            aria-label="Mostrar contraseña"
                        >
                            Ver
                        </button>
                    </div>
                    @error('password')
                        <p class="error" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <label class="remember">
                    <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                    Recordarme en este equipo
                </label>

                <button type="submit">Entrar</button>
            </form>

            <a class="back" href="{{ url('/') }}">Volver al inicio</a>
        </div>

        <script>
            (function () {
                var input = document.getElementById('password');
                var btn = document.getElementById('toggle-password');
                if (!input || !btn) return;

                btn.addEventListener('click', function () {
                    var show = input.type === 'password';
                    input.type = show ? 'text' : 'password';
                    btn.setAttribute('aria-pressed', show ? 'true' : 'false');
                    btn.setAttribute('aria-label', show ? 'Ocultar contraseña' : 'Mostrar contraseña');
                    btn.textContent = show ? 'Ocultar' : 'Ver';
                });
            })();
        </script>
    </body>
</html>
