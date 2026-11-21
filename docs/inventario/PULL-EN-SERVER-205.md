# Tras `git pull` en el server de producción

## Host correcto

| Entorno | URL | Notas |
|---------|-----|--------|
| **Producción (server)** | `http://192.168.20.205:8007/` | **Este es el server.** `APP_URL` y Nginx del server apuntan aquí. |
| PC desarrollo (LAN) | `http://192.168.20.11:8007/` | Laragon en el PC de desarrollo; **no** es el server. |
| Artisan local | `http://127.0.0.1:8000/` | Solo en el PC de desarrollo. |

No configures el `.env` del **205** con la IP `.11` ni con `127.0.0.1`.

---

## Comandos en el server (205)

```bash
cd /ruta/del/proyecto   # inventario-de-lenguas en 192.168.20.205
git pull origin main
php artisan config:clear
php artisan view:clear
php artisan route:clear
php artisan modulos:sync-matriz
```

`.env` del server:

```env
APP_URL=http://192.168.20.205:8007
```

---

## Nginx en el 205 (obligatorio si sirve por `:8007`)

Si al escribir un código en **Decomiso manual** no salen sugerencias, o los filtros `?q=` fallan en la IP pero sí en localhost:

1. Abrir el conf de Nginx del sitio (Laragon: `sites-enabled\inventario-de-lenguas.conf` o el que escuche `8007` / `192.168.20.205`).
2. Dejar **exactamente**:

```nginx
location / {
    try_files $uri $uri/ /index.php$is_args$args;
}
```

**Incorrecto** (rompe el primer parámetro de la query, p. ej. `q`):

```nginx
try_files $uri $uri/ /index.php?$is_args$args;
```

3. Recargar Nginx (Laragon → Nginx → Reload).

Detalle técnico: `docs/inventario/nginx-query-string.md`

---

## Cambios que trae el código (saber qué validar)

### Inventario

- Filtros **Aplicar** / chips / Excel / sync con rutas same-origin (no dependen de `APP_URL` mal puesto).
- Doc: `docs/inventario/filtro-id-producto-server.md`

### Decomisos

- Sugerencias al escribir Id producto → `GET /decomisos-de-lenguas/sugerencias-producto?_=1&busca=...`
- Dictamen por defecto **Decomisado**
- Columna **Fecha de beneficio** (SIRT = Animal Insensibilizado; manual = ref. turno local)
- Filtros de listado con `action="/decomisos-de-lenguas"` (GET nativo)
- Doc: `docs/decomisos-manual.md`

### Otros

- Checklist operativo completo: `docs/inventario/checklist-server.md`
- Deploy resumido: `docs/inventario/deploy-server-205.md`

---

## Validación mínima en producción (205)

1. Entrar a `http://192.168.20.205:8007/`
2. **Inventario** → filtrar un Id producto → **Aplicar** → URL con `?id_producto=...` en **205**
3. **Decomisos** → escribir `2611` en Id producto → lista de sugerencias
4. Tabla decomisos muestra **Fecha de beneficio**
5. `php artisan route:list --name=decomisos` incluye `sugerencias_producto`

Si algo falla, revisar en este orden: Nginx `try_files` → `APP_URL=http://192.168.20.205:8007` → `view:clear` / `route:clear` → docs enlazados arriba.
