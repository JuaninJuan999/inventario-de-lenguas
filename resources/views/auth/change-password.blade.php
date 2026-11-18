<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @include('partials.favicon')
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Nueva contraseña — {{ config('app.name') }}</title>
        @include('gestion-usuarios.partials.head-assets')
        <style>
            body {
                display: flex;
                flex-direction: column;
                align-items: stretch;
            }
            .sheet {
                max-width: 28rem;
            }
        </style>
    </head>
    <body>
        @include('partials.logo-institucional')
        <header class="page-bar" style="max-width: 28rem">
            <div>
                <h1>Defina una nueva contraseña</h1>
                <p class="sub">
                    Hola, <strong>{{ $user->name }}</strong>. Su acceso temporal usa el nombre de usuario;
                    debe crear una contraseña segura para continuar.
                </p>
            </div>
            <form method="post" action="{{ route('logout') }}">
                @csrf
                <button class="link-menu" type="submit" style="cursor: pointer; font-family: inherit">Cerrar sesión</button>
            </form>
        </header>

        <section class="sheet" aria-labelledby="pwd-title">
            <h2 id="pwd-title" class="section-title" style="margin-bottom: 0.65rem">Cambio obligatorio</h2>

            @if (session('status'))
                <p class="alert alert--ok" role="status">{{ session('status') }}</p>
            @endif
            @if ($errors->any())
                <p class="alert alert--err" role="alert">Revise la contraseña indicada.</p>
            @endif

            <form class="form-panel" method="post" action="{{ route('password.change.update') }}" novalidate>
                @csrf
                <div class="form-grid">
                    <div class="field field--full">
                        <label for="gu-password">Nueva contraseña</label>
                        <div class="password-wrap">
                            <input
                                id="gu-password"
                                type="password"
                                name="password"
                                required
                                autocomplete="new-password"
                                aria-describedby="gu-password-tips"
                            />
                            <button
                                type="button"
                                class="toggle-password"
                                id="gu-toggle-password"
                                aria-controls="gu-password"
                                aria-pressed="false"
                                aria-label="Mostrar contraseña"
                            >
                                Ver
                            </button>
                        </div>
                        <div id="gu-password-tips" class="pwd-tips">
                            <p class="pwd-tips__title">Recomendaciones para una contraseña segura</p>
                            <ul class="pwd-tips__list" aria-live="polite">
                                <li data-rule="len">Al menos 8 caracteres</li>
                                <li data-rule="lower">Una letra minúscula</li>
                                <li data-rule="upper">Una letra mayúscula</li>
                                <li data-rule="num">Un número</li>
                                <li data-rule="sym">Un símbolo (!@#$…)</li>
                                <li data-rule="diff">Distinta del nombre de usuario</li>
                            </ul>
                        </div>
                        @error('password')
                            <p class="err">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="field field--full">
                        <label for="gu-password-confirmation">Confirmar contraseña</label>
                        <div class="password-wrap">
                            <input
                                id="gu-password-confirmation"
                                type="password"
                                name="password_confirmation"
                                required
                                autocomplete="new-password"
                            />
                            <button
                                type="button"
                                class="toggle-password"
                                id="gu-toggle-password-confirmation"
                                aria-controls="gu-password-confirmation"
                                aria-pressed="false"
                                aria-label="Mostrar confirmación de contraseña"
                            >
                                Ver
                            </button>
                        </div>
                    </div>
                </div>
                <div class="actions">
                    <button class="btn-submit" type="submit">Guardar y continuar</button>
                </div>
            </form>
        </section>
        <script>
            (function () {
                var username = @json(strtolower((string) ($user->username ?? '')));

                function bindToggle(inputId, buttonId, confirmField) {
                    var input = document.getElementById(inputId);
                    var btn = document.getElementById(buttonId);
                    if (!input || !btn) return;
                    btn.addEventListener('click', function () {
                        var show = input.type === 'password';
                        input.type = show ? 'text' : 'password';
                        btn.setAttribute('aria-pressed', show ? 'true' : 'false');
                        var noun = confirmField ? 'confirmación de contraseña' : 'contraseña';
                        btn.setAttribute('aria-label', show ? 'Ocultar ' + noun : 'Mostrar ' + noun);
                        btn.textContent = show ? 'Ocultar' : 'Ver';
                    });
                }
                bindToggle('gu-password', 'gu-toggle-password', false);
                bindToggle('gu-password-confirmation', 'gu-toggle-password-confirmation', true);

                var pwdEl = document.getElementById('gu-password');
                var tipsEl = document.getElementById('gu-password-tips');
                if (!pwdEl || !tipsEl) return;

                function refreshPwdTips() {
                    var v = String(pwdEl.value || '');
                    var checks = {
                        len: v.length >= 8,
                        lower: /[a-z]/.test(v),
                        upper: /[A-Z]/.test(v),
                        num: /[0-9]/.test(v),
                        sym: /[^A-Za-z0-9]/.test(v),
                        diff: v.length > 0 && v.toLowerCase() !== username,
                    };
                    tipsEl.querySelectorAll('[data-rule]').forEach(function (li) {
                        var ok = !!checks[li.getAttribute('data-rule')];
                        li.classList.toggle('is-ok', ok);
                    });
                }

                pwdEl.addEventListener('input', refreshPwdTips);
                pwdEl.addEventListener('focus', refreshPwdTips);
                refreshPwdTips();
            })();
        </script>
    </body>
</html>
