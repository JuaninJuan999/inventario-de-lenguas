# Nginx: query string roto (`??q=`) — server producción `.205`

## Hosts

| Rol | URL |
|-----|-----|
| **Server producción** | `http://192.168.20.205:8007/` |
| PC desarrollo (Laragon LAN) | `http://192.168.20.11:8007/` (mismo bug si el conf está mal) |
| Artisan | `http://127.0.0.1:8000/` (no usa este conf; suele verse bien) |

El server correcto de planta/producción es **`192.168.20.205`**.

## Síntoma

En la IP LAN (`:8007`):

- Decomiso manual: al escribir un código **no salen sugerencias** (o “Sin coincidencias” / búsqueda vacía).
- Filtros GET con `?q=...` como **primer** parámetro fallan; con `?_=1&q=...` sí funcionan.

## Causa

Conf Nginx con:

```nginx
try_files $uri $uri/ /index.php?$is_args$args;
```

`$is_args` ya trae `?` si hay query. El `?` literal extra produce:

```text
/index.php??q=2611
```

PHP no recibe el parámetro `q` → la app lo ve vacío.

## Arreglo en el server 205 (y en el PC .11 si aplica)

Archivo típico Laragon:

`C:\laragon\etc\nginx\sites-enabled\inventario-de-lenguas.conf`

(en el 205: el conf que haga `listen 8007` / `server_name 192.168.20.205`)

```nginx
location / {
    try_files $uri $uri/ /index.php$is_args$args;
}
```

Recargar Nginx (Laragon → Nginx → Reload).

Este archivo de Nginx **no** viene en el `git pull`; hay que editarlo **en cada máquina** que sirva la app por Nginx.

## Mitigación en el código (sí viene con `git pull`)

Las sugerencias de decomiso llaman:

`/decomisos-de-lenguas/sugerencias-producto?_=1&busca=...`

Así no dependen de `q` como primer parámetro. Aun así **hay que corregir Nginx** para filtros y otras pantallas que usen `?q=`.

## Validar en el 205

1. `http://192.168.20.205:8007/decomisos-de-lenguas`
2. Escribir `2611` en Id producto → listado de códigos
3. Network → `sugerencias-producto?_=1&busca=2611` → JSON con `items` no vacío
4. Opcional: `sugerencias-producto?q=2611` también debe devolver items tras el fix de Nginx

Guía de pull: `docs/inventario/PULL-EN-SERVER-205.md`
