# Decomisos — alta manual, sugerencias y fecha de beneficio

Documentación para el **server de producción** `http://192.168.20.205:8007/` (no confundir con el PC de desarrollo `.11`).

Guía unificada tras pull: `docs/inventario/PULL-EN-SERVER-205.md`

## Tras actualizar código (en el 205)

```bash
cd /ruta/del/proyecto
git pull origin main
php artisan route:clear
php artisan view:clear
php artisan config:clear
```

`.env` del server: `APP_URL=http://192.168.20.205:8007`

Si Nginx sirve el sitio: corregir `try_files` → `docs/inventario/nginx-query-string.md`

Rutas nuevas que deben existir:

| Método | Ruta | Nombre |
|--------|------|--------|
| GET | `/decomisos-de-lenguas/sugerencias-producto` | `decomisos.lenguas.sugerencias_producto` |

Comprobar: `php artisan route:list --name=decomisos`

## Qué incluye esta versión

### 1. Sugerencias al escribir Id producto

- Al escribir ≥ 2 caracteres en **Decomiso manual → Id producto**, el navegador llama a  
  `GET /decomisos-de-lenguas/sugerencias-producto?_=1&busca=...`  
  (acepta también `q`; el `_` / `busca` evitan el bug Nginx `??q=`)
- Solo lista lenguas **disponibles** en inventario local (`sinDespachar`).
- Solo roles con permiso de **escritura** en decomisos (p. ej. calidad, admin). Lectura → 403 / lista vacía.
- Flechas / Enter para elegir; muestra código + fecha de beneficio + propietario.

### Filtros de búsqueda (Aplicar / Limpiar / Excel)

El formulario usa `action="/decomisos-de-lenguas"` (GET nativo, sin `route()` absoluto ni JS que bloquee el submit).

**Importante:** la **Búsqueda libre** filtra decomisos **ya registrados** (SIRT/manual), no el inventario.  
Para localizar un código disponible y decomisarlo, escriba en **Id producto** del cuadro “Decomiso manual” (sugerencias).

Si en el server **Aplicar** no filtra (o salta a `127.0.0.1`):

1. `git pull origin main`
2. `php artisan view:clear && php artisan route:clear`
3. Tras Aplicar, la URL debe ser `http://192.168.20.205:8007/decomisos-de-lenguas?q=...` (o con fechas)
4. `.env` del server: `APP_URL=http://192.168.20.205:8007`

Si no aparecen sugerencias / sale “Sin coincidencias” o “No está en el inventario local”:

1. **Nginx LAN (`:8007`)**: si `try_files` tiene `/index.php?$is_args$args` (con `?` de más), las sugerencias/filtros fallan. Ver `docs/inventario/nginx-query-string.md` y dejar `/index.php$is_args$args` + `nginx -s reload`
2. `git pull` + `route:clear` + `view:clear`
3. Sesión con rol que pueda registrar manual (calidad/admin, no solo lectura)
4. El código debe existir en **Inventario de Lenguas del mismo host** y **sin despachar**
5. Si el mensaje dice **ya no está disponible**, ese id ya tiene `despachado_at`
6. Network → `sugerencias-producto?_=1&busca=...` → **200** JSON con `items`

### 2. Dictamen por defecto

- El select **Nombre dictamen** inicia en **Decomisado** (y esa opción siempre está en el catálogo).

### 3. Columnas **Fecha de beneficio** y **Propietario**

| Columna | Origen | Fuente |
|---------|--------|--------|
| Fecha de beneficio | **SIRT** | `trazabilidad_proceso.insensibilizacion.fecha_registro` (Animal Insensibilizado) |
| Fecha de beneficio | **Manual** | Inventario local: `fecha_turno_referencia` o `fecha_registro` del ingreso |
| Propietario | **Manual** | Inventario local (`ingreso_lenguas_locales.propietario`) |
| Propietario | **SIRT** | Inventario local si existe; si no, SIRT `producto_empresa` → `organizaciones.empresa` |

Si una fila SIRT muestra propietario «—», ese código no está (o no estuvo) en la réplica local **y** no hubo empresa en `producto_empresa` para ese id.

Ambas van también en el **Excel** de decomisos.

**Fecha registro / Hora registro** siguen siendo las del decomiso (inspección SIRT o momento del alta manual), no la de beneficio.

Si en filas SIRT la fecha de beneficio sale `—`:

- Revisar conectividad SIRT / esquema `trazabilidad_proceso`
- Que el `id_producto` exista en `insensibilizacion`
- El listado de decomisos SIRT **no se cae** si falla esa consulta auxiliar (solo queda sin fecha)

## Validación rápida en el server

1. `http://192.168.20.205:8007/decomisos-de-lenguas`
2. Dictamen = **Decomisado** sin tocar el select
3. Escribir `2611` en Id producto → lista de sugerencias
4. Tabla: columna **Fecha de beneficio** con valor en filas Manual y SIRT
5. Excel: misma columna

## Código relevante

- `app/Http/Controllers/Decomisos/InspeccionSirtController.php`  
  (`sugerenciasProducto`, `adjuntarFechaBeneficioDesdeInsensibilizacion`, `enriquecerFechaBeneficio`)
- `resources/views/decomisos-lenguas.blade.php`
- `routes/web.php` (grupo `module:decomisos`)

## Relacionado

- Roles escritura/lectura: `docs/roles-y-modulos.md`
- Deploy server: `docs/inventario/deploy-server-205.md`
- Checklist: `docs/inventario/checklist-server.md`
