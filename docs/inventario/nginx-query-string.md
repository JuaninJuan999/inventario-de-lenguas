# Nginx: filtros y sugerencias sin `q` (producción LAN)

## Síntoma

En `http://192.168.20.11:8007/` (o el server LAN):

- Decomiso manual: al escribir un código **no salen sugerencias** (o “Sin coincidencias”).
- Filtros GET con `?q=...` como **primer** parámetro fallan; con `?_=1&q=...` sí funcionan.

En `http://127.0.0.1:8000` (artisan serve) suele verse bien.

## Causa

En el `server` de Nginx (Laragon) estaba:

```nginx
try_files $uri $uri/ /index.php?$is_args$args;
```

`$is_args` ya incluye `?` cuando hay query string. El `?` literal extra produce:

```text
/index.php??q=2611
```

PHP no interpreta bien el parámetro `q` → la app lo ve vacío.

## Arreglo (obligatorio en cada PC/server con Nginx)

Archivo típico Laragon:

`C:\laragon\etc\nginx\sites-enabled\inventario-de-lenguas.conf`

Dejar:

```nginx
location / {
    try_files $uri $uri/ /index.php$is_args$args;
}
```

Luego recargar Nginx (Laragon → Nginx → Reload, o):

```bash
nginx -s reload
```

En el server `192.168.20.205` aplicar el **mismo** cambio si usa Nginx.

## Mitigación en código

Las sugerencias de decomiso envían `?_=1&busca=...` (no dependen de `q` como primer param). Tras corregir Nginx, filtros con `q` también quedan bien.

## Validar

1. Abrir decomisos en la IP de producción (`:8007`).
2. Escribir `2611` en Id producto → deben listarse códigos.
3. Network → `sugerencias-producto?_=1&busca=2611` → JSON con `items` no vacío.
