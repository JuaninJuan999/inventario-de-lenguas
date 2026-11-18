<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @include('partials.favicon')
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Mi perfil — {{ config('app.name') }}</title>
        @include('gestion-usuarios.partials.head-assets')
        <style>
            .avatar-row {
                display: flex;
                flex-wrap: wrap;
                gap: 1rem;
                align-items: center;
            }
            .avatar-preview {
                width: 5.5rem;
                height: 5.5rem;
                border-radius: 9999px;
                object-fit: cover;
                border: 2px solid color-mix(in srgb, var(--brand-green) 45%, transparent);
                background: color-mix(in srgb, var(--brand-green) 12%, rgba(0, 0, 0, 0.35));
            }
            .avatar-fallback {
                width: 5.5rem;
                height: 5.5rem;
                border-radius: 9999px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: 1.35rem;
                font-weight: 800;
                letter-spacing: 0.04em;
                border: 2px solid color-mix(in srgb, var(--brand-green) 45%, transparent);
                background: color-mix(in srgb, var(--brand-green) 14%, rgba(0, 0, 0, 0.35));
                color: var(--text);
            }
            .avatar-controls {
                display: grid;
                gap: 0.45rem;
                min-width: min(100%, 16rem);
            }
            .check-remove {
                display: flex;
                align-items: center;
                gap: 0.45rem;
                font-size: 0.82rem;
                opacity: 0.9;
            }
            .check-remove input {
                width: auto;
                margin: 0;
            }
            .role-pill {
                display: inline-block;
                margin-top: 0.25rem;
                padding: 0.2rem 0.55rem;
                border-radius: 9999px;
                font-size: 0.72rem;
                font-weight: 700;
                background: color-mix(in srgb, var(--brand-rose) 18%, transparent);
            }
        </style>
    </head>
    <body>
        @include('partials.logo-institucional')
        <header class="page-bar">
            <div>
                <h1>Mi perfil</h1>
                <p class="sub">
                    Actualice sus datos, contraseña y foto de perfil.
                    <span class="role-pill">{{ $user->role }}</span>
                </p>
            </div>
            <a class="link-menu" href="{{ route('menu') }}">← Menú</a>
        </header>

        <section class="sheet" aria-labelledby="perfil-form">
            <h2 id="perfil-form" class="section-title" style="margin-bottom: 0.65rem">Datos personales</h2>

            @if (session('status'))
                <p class="alert alert--ok" role="status">{{ session('status') }}</p>
            @endif
            @if ($errors->any())
                <p class="alert alert--err" role="alert">Revise los datos del formulario.</p>
            @endif

            <form
                class="form-panel"
                method="post"
                action="{{ route('perfil.update') }}"
                enctype="multipart/form-data"
                novalidate
            >
                @csrf
                @method('PUT')

                <div class="form-grid">
                    <div class="field field--full">
                        <label>Foto de perfil</label>
                        <div class="avatar-row">
                            @if ($user->avatarUrl())
                                <img class="avatar-preview" src="{{ $user->avatarUrl() }}" alt="Foto de {{ $user->name }}" />
                            @else
                                <span class="avatar-fallback" aria-hidden="true">{{ $user->avatarInitials() }}</span>
                            @endif
                            <div class="avatar-controls">
                                <input id="perfil-avatar" type="file" name="avatar" accept="image/jpeg,image/png,image/webp" />
                                <p class="muted" style="margin: 0; font-size: 0.75rem">JPG, PNG o WEBP · máx. 2 MB</p>
                                @if ($user->avatar_path)
                                    <label class="check-remove">
                                        <input type="checkbox" name="remove_avatar" value="1" />
                                        Quitar foto actual
                                    </label>
                                @endif
                                @error('avatar')
                                    <p class="err">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="field field--full">
                        <label for="perfil-name">Nombre completo</label>
                        <input id="perfil-name" type="text" name="name" value="{{ old('name', $user->name) }}" maxlength="255" required autocomplete="name" />
                        @error('name')
                            <p class="err">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="field field--full">
                        <label for="perfil-username">Nombre de usuario (login)</label>
                        <input
                            id="perfil-username"
                            type="text"
                            name="username"
                            value="{{ old('username', $user->username) }}"
                            maxlength="191"
                            required
                            autocomplete="username"
                            spellcheck="false"
                            autocapitalize="none"
                        />
                        @error('username')
                            <p class="err">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="field field--full">
                        <label for="perfil-email">Correo electrónico</label>
                        <input id="perfil-email" type="email" name="email" value="{{ old('email', $user->email) }}" maxlength="255" required autocomplete="email" />
                        @error('email')
                            <p class="err">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="field field--full">
                        <label for="gu-password">Nueva contraseña (opcional)</label>
                        <div class="password-wrap">
                            <input
                                id="gu-password"
                                type="password"
                                name="password"
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
                        <div id="gu-password-tips" class="pwd-tips" hidden>
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
                        <label for="gu-password-confirmation">Confirmar nueva contraseña</label>
                        <div class="password-wrap">
                            <input
                                id="gu-password-confirmation"
                                type="password"
                                name="password_confirmation"
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
                    <button class="btn-submit" type="submit">Guardar perfil</button>
                    <a class="link-menu" href="{{ route('menu') }}" style="margin-left: 0.25rem">Cancelar</a>
                </div>
            </form>
        </section>

        <script>
            (function () {
                var usernameEl = document.getElementById('perfil-username');
                function currentUsername() {
                    return String(usernameEl && usernameEl.value ? usernameEl.value : '').toLowerCase().trim();
                }

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
                    tipsEl.hidden = v.length === 0 && document.activeElement !== pwdEl;
                    var checks = {
                        len: v.length >= 8,
                        lower: /[a-z]/.test(v),
                        upper: /[A-Z]/.test(v),
                        num: /[0-9]/.test(v),
                        sym: /[^A-Za-z0-9]/.test(v),
                        diff: v.length > 0 && v.toLowerCase() !== currentUsername(),
                    };
                    tipsEl.querySelectorAll('[data-rule]').forEach(function (li) {
                        li.classList.toggle('is-ok', !!checks[li.getAttribute('data-rule')]);
                    });
                }

                pwdEl.addEventListener('focus', function () {
                    tipsEl.hidden = false;
                    refreshPwdTips();
                });
                pwdEl.addEventListener('blur', function () {
                    if (!String(pwdEl.value || '').length) tipsEl.hidden = true;
                });
                pwdEl.addEventListener('input', refreshPwdTips);
                if (usernameEl) usernameEl.addEventListener('input', refreshPwdTips);
            })();
        </script>
    </body>
</html>
