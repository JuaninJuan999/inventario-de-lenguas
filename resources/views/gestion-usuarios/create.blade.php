<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @include('partials.favicon')
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Crear usuario — {{ config('app.name') }}</title>
        @include('gestion-usuarios.partials.head-assets')
    </head>
    <body>
        @include('partials.logo-institucional')
        <header class="page-bar">
            <div>
                <h1>Crear usuario</h1>
                <p class="sub">
                    Complete los datos y guarde. El <strong>nombre de usuario</strong> se propone solo a partir del
                    nombre completo (puede editarlo). Se guarda en minúsculas y es el login del sistema.
                </p>
            </div>
            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center">
                <a class="link-menu" href="{{ route('gestion.usuarios.index') }}">← Lista de usuarios</a>
                <a class="link-menu" href="{{ route('menu') }}">← Menú</a>
            </div>
        </header>

        <section class="sheet" aria-labelledby="form-alta">
            <h2 id="form-alta" class="section-title" style="margin-bottom: 0.65rem">Formulario de registro</h2>

            @if ($errors->any())
                <p class="alert alert--err" role="alert">Revise los datos del formulario.</p>
            @endif

            <p class="muted">
                Letras, números, punto, guion y guion bajo en el usuario. La contraseña debe tener al menos 8 caracteres.
            </p>

            <form class="form-panel" method="post" action="{{ route('gestion.usuarios.store') }}" novalidate>
                @csrf
                <div class="form-grid">
                    <div class="field field--full">
                        <label for="gu-name">Nombre completo</label>
                        <input id="gu-name" type="text" name="name" value="{{ old('name') }}" maxlength="255" required autocomplete="name" />
                        @error('name')
                            <p class="err">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="field field--full">
                        <label for="gu-username">Nombre de usuario (login)</label>
                        <input
                            id="gu-username"
                            type="text"
                            name="username"
                            value="{{ old('username') }}"
                            maxlength="191"
                            autocomplete="username"
                            spellcheck="false"
                            autocapitalize="none"
                            placeholder="Se genera al escribir el nombre…"
                            data-auto="1"
                        />
                        <p class="muted" style="margin: 0.35rem 0 0; font-size: 0.78rem">
                            Ejemplo: Juan Carreño → <code>juan.carreno</code>. Si ya existe, el servidor añade un sufijo.
                        </p>
                        @error('username')
                            <p class="err">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="field field--full">
                        <label for="gu-email">Correo electrónico</label>
                        <input id="gu-email" type="email" name="email" value="{{ old('email') }}" maxlength="255" required autocomplete="email" />
                        @error('email')
                            <p class="err">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="field field--full">
                        <label for="gu-role">Rol en el sistema</label>
                        <select id="gu-role" name="role" required>
                            @foreach ($rolesAsignables as $valor => $etiqueta)
                                <option value="{{ $valor }}" @selected(old('role', 'auxiliar') === $valor)>{{ $etiqueta }}</option>
                            @endforeach
                        </select>
                        @error('role')
                            <p class="err">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="field field--full">
                        <label for="gu-password">Contraseña</label>
                        <div class="password-wrap">
                            <input id="gu-password" type="password" name="password" required autocomplete="new-password" />
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
                    <button class="btn-submit" type="submit">Guardar usuario</button>
                    <a class="link-menu" href="{{ route('gestion.usuarios.index') }}" style="margin-left: 0.25rem">Cancelar</a>
                </div>
            </form>
        </section>
        <script>
            (function () {
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

                var nameEl = document.getElementById('gu-name');
                var userEl = document.getElementById('gu-username');
                if (!nameEl || !userEl) return;

                var auto = userEl.getAttribute('data-auto') === '1' && !String(userEl.value || '').trim();

                function slugUsername(raw) {
                    var map = {
                        á: 'a', à: 'a', ä: 'a', â: 'a', ã: 'a',
                        é: 'e', è: 'e', ë: 'e', ê: 'e',
                        í: 'i', ì: 'i', ï: 'i', î: 'i',
                        ó: 'o', ò: 'o', ö: 'o', ô: 'o', õ: 'o',
                        ú: 'u', ù: 'u', ü: 'u', û: 'u',
                        ñ: 'n', ç: 'c',
                    };
                    var s = String(raw || '').toLowerCase();
                    s = s.replace(/[áàäâãéèëêíìïîóòöôõúùüûñç]/g, function (c) {
                        return map[c] || c;
                    });
                    s = s.replace(/[^a-z0-9]+/g, '.').replace(/^\.+|\.+$/g, '').replace(/\.{2,}/g, '.');
                    return s.slice(0, 180);
                }

                nameEl.addEventListener('input', function () {
                    if (!auto) return;
                    userEl.value = slugUsername(nameEl.value);
                });

                userEl.addEventListener('input', function () {
                    auto = false;
                    userEl.setAttribute('data-auto', '0');
                });

                if (auto && nameEl.value) {
                    userEl.value = slugUsername(nameEl.value);
                }
            })();
        </script>
    </body>
</html>
