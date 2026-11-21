# Despliegue rápido — server producción `192.168.20.205:8007`

**Producción = `192.168.20.205`.**  
No usar `192.168.20.11` (PC de desarrollo) como `APP_URL` del server.

Guía post-pull ampliada: `docs/inventario/PULL-EN-SERVER-205.md`

## 1. Traer código

En el **server 205**, dentro del proyecto:

```bash
cd /ruta/del/proyecto   # carpeta inventario-de-lenguas en 192.168.20.205
git pull origin main
php artisan config:clear
php artisan view:clear
php artisan route:clear
php artisan modulos:sync-matriz
```

## 1b. Nginx en el 205 (si sirve en `:8007`)

Si las sugerencias de decomiso o los filtros `?q=` fallan en `192.168.20.205:8007` pero funcionan en artisan/`127.0.0.1`, corregir `try_files` **en el 205**:

→ `docs/inventario/nginx-query-string.md`

```nginx
try_files $uri $uri/ /index.php$is_args$args;
```

(no usar `/index.php?$is_args$args` — ese `?` de más rompe el query string)

## 2. Configurar URL pública

Editar `.env` del **server**:

```env
APP_URL=http://192.168.20.205:8007
```

```bash
php artisan config:clear
php artisan view:clear
```

Entrar siempre por: `http://192.168.20.205:8007/`

## 3. Qué incluye el código reciente (main)

| Tema | Doc / comando |
|------|----------------|
| Filtros Inventario (rutas relativas) | Este ajuste + `APP_URL` correcto |
| Dashboard: ingresos últimos 5 días | Panel en dashboard (reemplaza “Importados sync”) |
| Decomisos: sugerencias, Decomisado default, fecha beneficio | `docs/decomisos-manual.md` |
| Nginx `try_files` (sugerencias/filtros en :8007) | `docs/inventario/nginx-query-string.md` |
| Checklist post-pull producción | `docs/inventario/PULL-EN-SERVER-205.md` |
| Baseline Excel / v2 / vencidos / aviso auxiliar | `docs/inventario/checklist-server.md` |

## 4. Validar filtros

1. `http://192.168.20.205:8007/inventario-de-lenguas`
2. ID producto → **Aplicar**
3. La URL debe ser `http://192.168.20.205:8007/inventario-de-lenguas?id_producto=...`
4. Si solo “recarga” y no filtra (o salta a `127.0.0.1`), ver `docs/inventario/filtro-id-producto-server.md`

## 5. Validar decomisos (tras pull)

1. `http://192.168.20.205:8007/decomisos-de-lenguas`
2. Dictamen en **Decomisado**; escribir código → sugerencias
3. Tabla con **Fecha de beneficio** (SIRT desde Animal Insensibilizado)
4. Detalle: `docs/decomisos-manual.md`

## Relacionado

- Post-pull 205: `docs/inventario/PULL-EN-SERVER-205.md`
- Checklist: `docs/inventario/checklist-server.md`
- Filtro Id producto: `docs/inventario/filtro-id-producto-server.md`
- Nginx query string: `docs/inventario/nginx-query-string.md`
- Decomisos manual: `docs/decomisos-manual.md`
