<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @include('partials.favicon')
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Editar usuario — {{ config('app.name') }}</title>
        @include('gestion-usuarios.partials.head-assets')
    </head>
    <body>
        @include('partials.logo-institucional')
        <header class="page-bar">
            <div>
                <h1>Editar usuario</h1>
                <p class="sub">
                    Actualice los datos de <strong>{{ $usuario->name }}</strong>
                    ({{ $usuario->username }}). Desde aquí también puede restablecer la contraseña.
                </p>
            </div>
            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center">
                <a class="link-menu" href="{{ route('gestion.usuarios.index') }}">← Lista de usuarios</a>
                <a class="link-menu" href="{{ route('menu') }}">← Menú</a>
            </div>
        </header>

        <section class="sheet" aria-labelledby="form-editar">
            <h2 id="form-editar" class="section-title" style="margin-bottom: 0.65rem">Datos de la cuenta</h2>

            @if (session('status'))
                <p class="alert alert--ok" role="status">{{ session('status') }}</p>
            @endif
            @if (session('gestion_error'))
                <p class="alert alert--err" role="alert">{{ session('gestion_error') }}</p>
            @endif
            @if ($errors->any())
                <p class="alert alert--err" role="alert">Revise los datos del formulario.</p>
            @endif

            <form class="form-panel" method="post" action="{{ route('gestion.usuarios.update', $usuario) }}" novalidate>
                @csrf
                @method('PUT')
                <div class="form-grid">
                    <div class="field field--full">
                        <label for="gu-name">Nombre completo</label>
                        <input id="gu-name" type="text" name="name" value="{{ old('name', $usuario->name) }}" maxlength="255" required autocomplete="name" />
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
                            value="{{ old('username', $usuario->username) }}"
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
                        <label for="gu-email">Correo electrónico</label>
                        <input id="gu-email" type="email" name="email" value="{{ old('email', $usuario->email) }}" maxlength="255" required autocomplete="email" />
                        @error('email')
                            <p class="err">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="field field--full">
                        <label for="gu-role">Rol en el sistema</label>
                        <select id="gu-role" name="role" required>
                            @foreach ($rolesAsignables as $valor => $etiqueta)
                                <option value="{{ $valor }}" @selected(old('role', $usuario->role) === $valor)>{{ $etiqueta }}</option>
                            @endforeach
                            @if (! array_key_exists($usuario->role, $rolesAsignables))
                                <option value="{{ $usuario->role }}" @selected(old('role', $usuario->role) === $usuario->role)>
                                    {{ $usuario->role }} (legado)
                                </option>
                            @endif
                        </select>
                        @error('role')
                            <p class="err">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="actions">
                    <button class="btn-submit" type="submit">Guardar cambios</button>
                    <a class="link-menu" href="{{ route('gestion.usuarios.index') }}" style="margin-left: 0.25rem">Cancelar</a>
                </div>
            </form>
        </section>

        <section class="sheet" style="margin-top: 1rem" aria-labelledby="reset-pwd">
            <h2 id="reset-pwd" class="section-title" style="margin-bottom: 0.65rem">Restablecer contraseña</h2>
            <p class="muted" style="margin-top: 0">
                La contraseña temporal será el <strong>nombre de usuario</strong>
                (<code>{{ $usuario->username }}</code>). En el próximo ingreso el sistema pedirá una contraseña nueva y segura.
            </p>
            @if ($usuario->must_change_password)
                <p class="alert alert--ok" role="status" style="margin-top: 0.75rem">
                    Ya está marcado para cambio obligatorio en el próximo acceso.
                </p>
            @endif
            <form
                method="post"
                action="{{ route('gestion.usuarios.reset_password', $usuario) }}"
                style="margin-top: 0.85rem"
                onsubmit="return confirm('¿Restablecer la contraseña de {{ $usuario->username }}? Quedará igual al nombre de usuario hasta que la cambie.');"
            >
                @csrf
                <button class="btn-inline" type="submit">Restablecer contraseña</button>
            </form>
        </section>
    </body>
</html>
