# Sincronización de Inventario de Lenguas (guía para servidor)

Documento operativo para el equipo que despliega o mantiene la aplicación.
Complementa los comentarios en `config/ingresos_lenguas.php`, `routes/console.php` y
`app/Console/Commands/SyncInventarioLenguasCommand.php`.

---

## 1. Cómo se alimenta el inventario (resumen)

| Pieza | Rol |
|-------|-----|
| **Ingresos de Lenguas** | Solo **consulta en vivo** a SIRT/Colbeef. No escribe en la BD local. |
| **Inventario de Lenguas** | **Réplica local** en la tabla `ingreso_lenguas_locales`. Es la que usan despacho, desposte, cambios de destino y decomisos. |
| **Misma fuente SIRT** | Ambos usan `App\Services\IngresosLenguas\IngresosLenguasConsultaSirt`. |
| **Bajas** | Despacho / desposte / decomisos marcan `despachado_at` **solo en local**. No modifican SIRT ni la pantalla de Ingresos. |

Flujo de importación:

1. Se consulta SIRT (día de operación o rango de ref. turno).
2. Por cada fila con `insensibilizacion_id` se hace `updateOrCreate` en `ingreso_lenguas_locales`.
3. El listado de inventario muestra filas con `despachado_at` nulo (disponibles).

Servicio central de sync: `App\Services\Inventario\InventarioLenguasSynchronizer`.

---

## 2. Dos formas de sincronizar (no se excluyen)

### A) Con la vista abierta (polling del navegador)

- Ruta: `POST /inventario-de-lenguas/importar-hoy` (`inventario.lenguas.importar_hoy`).
- Intervalo: `INVENTARIO_LENGUAS_SYNC_INTERVAL` (segundos; mínimo efectivo 30; `0` = solo al cargar).
- **Limitación:** si nadie tiene abierta la pantalla de Inventario, esta vía **no** actualiza la réplica.

### B) En segundo plano (recomendado en servidor)

- Comando Artisan: `php artisan inventario:sync-lenguas`
- Registrado en el **scheduler** de Laravel (`routes/console.php`) cada N minutos.
- Variable: `INVENTARIO_LENGUAS_SCHEDULE_MINUTES` (default `10`; `0` = no programar la tarea).

**Importante:** registrar la tarea en el schedule **no basta**. Tiene que haber un proceso del sistema que ejecute el calendario de Laravel (ver sección 3).

---

## 3. Cómo “despertar” el schedule de Laravel

Laravel no corre tareas solo. Hace falta uno de estos mecanismos:

| Comando | Uso |
|---------|-----|
| `php artisan schedule:work` | Proceso **persistente** que cada minuto llama internamente a `schedule:run`. Cómodo en Laragon / Windows / un servicio. |
| `php artisan schedule:run` | Ejecuta **una vez** lo que toque en ese minuto. En Linux de producción se pone en **cron cada minuto**. |

### Opción recomendada en producción (Linux)

Cron del usuario del pool PHP (o root, según política):

```cron
* * * * * cd /ruta/al/proyecto && php artisan schedule:run >> /dev/null 2>&1
```

Con eso, cada 10 minutos (según `.env`) correrá `inventario:sync-lenguas`.

### Opción Laragon / Windows (desarrollo o servidor Windows)

Desde la raíz del proyecto:

```bat
php artisan schedule:work
```

O usar el script incluido:

```bat
scripts\start-schedule.bat
```

Dejar esa ventana/servicio **siempre abierto** mientras se quiera sync en segundo plano.
No lanzar `schedule:work` desde una petición HTTP (Apache/php-fpm): el proceso muere con el request.

### Verificar que el schedule está registrado

```bat
php artisan schedule:list
```

Debería listar algo como:

```text
*/10 * * * *  php artisan inventario:sync-lenguas
```

### Probar la sync a mano (sin esperar al reloj)

```bat
php artisan inventario:sync-lenguas
php artisan inventario:sync-lenguas --desde=2026-11-01 --hasta=2026-11-17
```

---

## 4. Variables de entorno relevantes

Definidas en `.env` / documentadas en `.env.example` y `config/ingresos_lenguas.php`:

| Variable | Efecto |
|----------|--------|
| `INGRESOS_LENGUAS_CONNECTION` | Conexión Laravel a SIRT (p. ej. `colbeef`). |
| `INGRESOS_LENGUAS_FECHA_OPERACION_TIMEZONE` | Zona del “día de operación” (p. ej. `America/Bogota`). |
| `INGRESOS_LENGUAS_CONSULTA_LIMIT` | Tope de filas por consulta SIRT (default 2000). Si se alcanza, la UI puede mostrar alerta y el sync intenta catch-up incremental. |
| `INGRESOS_LENGUAS_ID_PARTE_PRODUCTO` | Filtro de parte producto en destino (default 4). |
| `INVENTARIO_LENGUAS_SYNC_INTERVAL` | Polling del navegador en segundos (`0` = solo al abrir). |
| `INVENTARIO_LENGUAS_SCHEDULE_MINUTES` | Minutos entre sync de fondo (`0` = no programar). |
| `POSTGRES_*` / `COLBEEF_DB_*` | Credenciales de la conexión PostgreSQL SIRT. |

También hace falta que la app pueda conectar a SIRT (`POSTGRES_HOST` o flags `POSTGRES_ENABLED` / `COLBEEF_DB_ENABLED` según `IngresosLenguasConsultaSirt::externalPostgresEnabled()`).

---

## 5. Comportamiento del inventario en pantalla

- Al abrir **Inventario de Lenguas** sin fechas, el listado usa por defecto el **día de operación** (alineado con la sync del día).
- Botón **Ver todos** (`?todos=1`): lista disponibles sin forzar ese default de fechas.
- Botón **Solo hoy (ref. turno)**: fuerza el día de operación.
- Si la última sync tocó el tope SIRT, aparece una alerta en la vista (meta en caché: `inventario_lenguas.sync.meta`).

---

## 6. Checklist de puesta en marcha (servidor)

1. Copiar `.env` desde `.env.example` y completar `POSTGRES_*` / conexión Colbeef.
2. `php artisan migrate`
3. Confirmar `INVENTARIO_LENGUAS_SCHEDULE_MINUTES=10` (o el valor acordado).
4. Activar el despertador del schedule:
   - Linux: cron `* * * * * … schedule:run`
   - Windows/Laragon: `scripts\start-schedule.bat` o servicio equivalente con `schedule:work`
5. `php artisan schedule:list` → debe verse `inventario:sync-lenguas`
6. `php artisan inventario:sync-lenguas` → debe importar sin error
7. Abrir Inventario en el navegador y verificar listado / mensaje de sync

---

## 7. Archivos de código a revisar

| Archivo | Contenido |
|---------|-----------|
| `config/ingresos_lenguas.php` | Defaults y comentarios de ENV |
| `routes/console.php` | Registro `Schedule::command('inventario:sync-lenguas')` |
| `app/Console/Commands/SyncInventarioLenguasCommand.php` | CLI `inventario:sync-lenguas` |
| `app/Services/Inventario/InventarioLenguasSynchronizer.php` | Upsert local, watermark, catch-up, meta |
| `app/Services/IngresosLenguas/IngresosLenguasConsultaSirt.php` | SQL a SIRT |
| `scripts/start-schedule.bat` | Arranque de `schedule:work` en Windows |
| Este documento | `docs/sincronizacion-inventario.md` |

---

## 8. Qué no hace este diseño (a propósito)

- No escribe decomisos/despachos de vuelta a SIRT.
- No sustituye la sync al abrir la vista: ambas pueden coexistir.
- No debe ejecutarse `schedule:work` “dentro” de Apache como si fuera un endpoint web.
