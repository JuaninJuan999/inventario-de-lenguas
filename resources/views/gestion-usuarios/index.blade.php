<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @include('partials.favicon')
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Gestión de usuarios — {{ config('app.name') }}</title>
        @include('gestion-usuarios.partials.head-assets')
    </head>
    <body>
        @include('partials.logo-institucional')
        <header class="page-bar">
            <div>
                <h1>Gestión de usuarios</h1>
                <p class="sub">
                    Listado de cuentas del sistema. El acceso es por <strong>nombre de usuario</strong> (no por correo).
                    Use <strong>Crear usuario</strong> para el formulario de alta. Solo el super administrador puede inactivar o eliminar cuentas.
                </p>
            </div>
            <a class="link-menu" href="{{ route('menu') }}">← Menú</a>
        </header>

        <section class="sheet" aria-labelledby="lista-usuarios">
            @if (session('status'))
                <p class="alert alert--ok" role="status">{{ session('status') }}</p>
            @endif

            @if (session('gestion_error'))
                <p class="alert alert--err" role="alert">{{ session('gestion_error') }}</p>
            @endif

            <div class="list-toolbar">
                <h2 id="lista-usuarios" class="section-title">Usuarios registrados</h2>
                <a class="btn-submit" href="{{ route('gestion.usuarios.create') }}">Crear usuario</a>
            </div>
            <p class="muted">Listado alfabético por nombre ({{ $usuarios->total() }} en total).</p>

            <div class="table-wrap">
                <table class="data">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Usuario</th>
                            <th>Correo</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Alta</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($usuarios as $u)
                            <tr>
                                <td>{{ $u->name }}</td>
                                <td>{{ $u->username }}</td>
                                <td>{{ $u->email }}</td>
                                <td>
                                    <span class="pill">{{ $rolesAsignables[$u->role] ?? ($u->role === 'user' ? 'Usuario (legado)' : $u->role) }}</span>
                                </td>
                                <td>
                                    @if ($u->is_active)
                                        <span class="pill" style="background: color-mix(in srgb, var(--brand-green) 22%, transparent)">Activo</span>
                                    @else
                                        <span class="pill" style="opacity: 0.85">Inactivo</span>
                                    @endif
                                </td>
                                <td>{{ $u->created_at?->timezone(config('app.timezone'))->format('Y-m-d H:i') ?? '—' }}</td>
                                <td>
                                    <div class="row-actions">
                                        <a
                                            class="btn-icon"
                                            href="{{ route('gestion.usuarios.edit', $u) }}"
                                            title="Editar"
                                            aria-label="Editar usuario {{ $u->username }}"
                                        >
                                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                                <path d="M12 20h9" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                            </svg>
                                        </a>
                                        @if (! $u->is(auth()->user()))
                                            @if ($u->is_active && ! $u->isSoleActiveSuperAdmin())
                                                <form
                                                    method="post"
                                                    action="{{ route('gestion.usuarios.inactivar', $u) }}"
                                                    onsubmit="return confirm('¿Inactivar este usuario? No podrá iniciar sesión hasta que lo reactive.');"
                                                >
                                                    @csrf
                                                    <button class="btn-icon" type="submit" title="Inactivar" aria-label="Inactivar usuario {{ $u->username }}">
                                                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                                            <circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/>
                                                            <path d="M9 12h6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                        </svg>
                                                    </button>
                                                </form>
                                            @elseif (! $u->is_active)
                                                <form method="post" action="{{ route('gestion.usuarios.activar', $u) }}">
                                                    @csrf
                                                    <button class="btn-icon btn-icon--ok" type="submit" title="Reactivar" aria-label="Reactivar usuario {{ $u->username }}">
                                                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                                            <circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/>
                                                            <path d="M8 12l2.5 2.5L16 9" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                            @if (! $u->isSoleSuperAdmin())
                                                <form
                                                    method="post"
                                                    action="{{ route('gestion.usuarios.destroy', $u) }}"
                                                    onsubmit="return confirm('¿Eliminar permanentemente este usuario? Esta acción no se puede deshacer.');"
                                                >
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn-icon btn-icon--danger" type="submit" title="Eliminar" aria-label="Eliminar usuario {{ $u->username }}">
                                                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                                            <path d="M3 6h18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                            <path d="M8 6V4h8v2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                            <path d="M19 6l-1 14H6L5 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                                            <path d="M10 11v6M14 11v6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                        @else
                                            <span class="muted-cell">(su cuenta)</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">No hay usuarios en el sistema.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pagination">
                {{ $usuarios->links() }}
            </div>
        </section>
    </body>
</html>
