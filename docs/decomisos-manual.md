# Decomisos — alta manual, sugerencias y fecha de beneficio

Documentación para que el server (`192.168.20.205:8007`) quede igual que local tras `git pull`.

## Tras actualizar código

```bash
cd /ruta/del/proyecto
git pull origin main
php artisan route:clear
php artisan view:clear
php artisan config:clear
```

Rutas nuevas que deben existir:

| Método | Ruta | Nombre |
|--------|------|--------|
| GET | `/decomisos-de-lenguas/sugerencias-producto` | `decomisos.lenguas.sugerencias_producto` |

Comprobar: `php artisan route:list --name=decomisos`

## Qué incluye esta versión

### 1. Sugerencias al escribir Id producto

- Al escribir ≥ 2 caracteres en **Decomiso manual → Id producto**, el navegador llama a  
  `GET /decomisos-de-lenguas/sugerencias-producto?q=...`
- Solo lista lenguas **disponibles** en inventario local (`sinDespachar`).
- Solo roles con permiso de **escritura** en decomisos (p. ej. calidad, admin). Lectura → 403 / lista vacía.
- Flechas / Enter para elegir; muestra código + fecha de beneficio + propietario.

Si no aparecen sugerencias en el server:

1. `git pull` + `route:clear` + `view:clear`
2. Sesión con rol que pueda registrar manual
3. Inventario local con stock (códigos disponibles)
4. Consola del navegador: la URL debe ser same-origin (`192.168.20.205:8007/...`), no `127.0.0.1`

### 2. Dictamen por defecto

- El select **Nombre dictamen** inicia en **Decomisado** (y esa opción siempre está en el catálogo).

### 3. Columna **Fecha de beneficio**

| Origen | Fuente |
|--------|--------|
| **SIRT** | `trazabilidad_proceso.insensibilizacion.fecha_registro` (Animal Insensibilizado), por `id_producto` |
| **Manual** | Inventario local: `fecha_turno_referencia` o, si falta, `fecha_registro` del ingreso ligado |
| Fallback | Misma lógica local si SIRT no devolvió fecha |

También va en el **Excel** de decomisos.

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
