# Sincronización de Inventario de Lenguas (guía para servidor)

Documento operativo para el equipo que despliega o mantiene la aplicación.
Complementa los comentarios en `config/ingresos_lenguas.php`, `routes/console.php` y
`app/Console/Commands/SyncInventarioLenguasCommand.php`.

Roles y módulos (menú / permisos): ver `docs/roles-y-modulos.md`.

Reinicio / baseline desde Excel de códigos: ver `docs/inventario/reiniciar-desde-excel.md`
(`php artisan inventario:reiniciar-desde-excel …`).

Campos locales que la sync **no** debe pisar si ya fueron editados en planta:

- `es_adicional` / `es_salida` (etiquetas en **Destinos y etiquetas**)
- `destino` cuando `destino_local = true` (destino editado en **Destinos y etiquetas**)

El módulo **Ingresos** consulta SIRT en vivo: allí el destino corporativo no cambia por ediciones locales.

---

## 1. Cómo se alimenta el inventario (resumen)

| Pieza | Rol |
|-------|-----|
| **Ingresos de Lenguas** | Solo **consulta en vivo** a SIRT/Colbeef. No escribe en la BD local. |
| **Inventario de Lenguas** | **Réplica local** en la tabla `ingreso_lenguas_locales`. Es la que usan despacho, desposte, cambios de destino y decomisos. |
| **Misma fuente SIRT** | Ambos usan `App\Services\IngresosLenguas\IngresosLenguasConsultaSirt`. |
| **Bajas** | Despacho / desposte / decomisos marcan `despachado_at` **solo en local**. No modifican SIRT ni la pantalla de Ingresos. |

Flujo de importación:

1. Se consulta SIRT (rango de ref. turno). Sin fechas explícitas, la ventana
   por defecto es **desde ayer hasta hoy** (`INVENTARIO_LENGUAS_SYNC_DIAS_ATRAS=1`).
2. Por cada fila con `insensibilizacion_id` se hace `updateOrCreate` en `ingreso_lenguas_locales`.
3. El listado de inventario muestra filas con `despachado_at` nulo (disponibles).

**Sacrificio de emergencia:** la consulta SIRT también incluye filas de
`insensibilizacion` con `id_proceso = 40` y/o `id_puesto_trabajo = 7` aunque **no**
tengan `plan_faena_producto`. En ese caso `fecha_turno_referencia` usa
`fecha_registro`. Config: `INGRESOS_LENGUAS_INCLUIR_EMERGENCIA` (default `true`) en
`config/ingresos_lenguas.php`. Tras desplegar: `php artisan config:clear` y
`php artisan inventario:sync-lenguas`.

Servicio central de sync: `App\Services\Inventario\InventarioLenguasSynchronizer`.
Consulta: `App\Services\IngresosLenguas\IngresosLenguasConsultaSirt`.

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
| `INVENTARIO_LENGUAS_SYNC_DIAS_ATRAS` | Días hacia atrás en la sync sin rango explícito. **Default `1` = desde ayer hasta hoy.** `0` = solo el día de operación. |
| `POSTGRES_*` / `COLBEEF_DB_*` | Credenciales de la conexión PostgreSQL SIRT. |

También hace falta que la app pueda conectar a SIRT (`POSTGRES_HOST` o flags `POSTGRES_ENABLED` / `COLBEEF_DB_ENABLED` según `IngresosLenguasConsultaSirt::externalPostgresEnabled()`).

En el `.env` del servidor conviene dejar explícito (aunque el default ya es 1):

```env
INVENTARIO_LENGUAS_SYNC_DIAS_ATRAS=1
```

Luego: `php artisan config:clear`.

---

## 5. Comportamiento del inventario en pantalla

- Al abrir **Inventario de Lenguas** sin fechas, el listado muestra **todo el stock disponible** en la réplica local (sin acotar “ayer + hoy” en la UI).
- Chips útiles: **Por vencer**, **Vencidas**, **Solo hoy** (ref. turno = día de operación). Fechas opcionales en Avanzado.
- Tras una sync automática/manual, el aviso muestra solo el **total disponible** (sin conteos técnicos de SIRT).
- Si la última sync tocó el tope de filas, aparece una alerta operativa (meta en caché: `inventario_lenguas.sync.meta`).

**Nota:** la ventana “desde ayer” aplica a la **importación** desde SIRT, no a un filtro forzado del listado. Si la réplica local todavía tiene histórico antiguo, hay que limpiarla (sección 6).

---

## 6. Alinear el servidor “desde ayer en adelante” (como en desarrollo)

Cuando el servidor tiene histórico viejo en `ingreso_lenguas_locales` (o datos de prueba en despacho/desposte/decomisos) y se quiere dejar la BD local igual que el entorno limpio de desarrollo:

### Qué borra / qué no toca

| Borra (local) | No toca |
|---------------|---------|
| `ingreso_lenguas_locales` (réplica) | Consultas / BD SIRT |
| `despachos`, `despostes`, `decomisos_manuales` | `users` |
| `despacho_operador_placas`, `modulo_accesos` | `modulos`, `role_modulo` |

### Comando recomendado (una sola vez tras el pull)

```bash
php artisan config:clear
php artisan inventario:limpiar-operativo --force
```

Eso:

1. Vacía los datos operativos locales listados arriba.
2. Limpia la meta de sync en caché.
3. Reimporta con la ventana por defecto (**ayer → hoy**, salvo que pases `--desde` / `--hasta`).

Variantes:

```bash
# Solo vaciar réplica e importar; conservar despachos/despostes/decomisos
php artisan inventario:limpiar-operativo --solo-inventario --force

# Rango explícito
php artisan inventario:limpiar-operativo --desde=2026-11-17 --hasta=2026-11-18 --force

# Solo reimportar sin borrar (histórico local se mantiene; upsert)
php artisan inventario:sync-lenguas
```

> `inventario:sync-lenguas` **no elimina** filas antiguas: solo crea/actualiza. Para “empezar desde ayer” hace falta `inventario:limpiar-operativo` (o borrar a mano lo anterior a ayer).

### Checklist rápido post-`git pull`

```bash
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan modulos:sync-matriz
php artisan config:clear
php artisan view:clear
# Opcional, si quieren la BD local limpia desde ayer:
php artisan inventario:limpiar-operativo --force
# Si no limpian, al menos forzar sync:
# php artisan inventario:sync-lenguas
```

---

## 7. Checklist de puesta en marcha (servidor nuevo)

1. Copiar `.env` desde `.env.example` y completar `POSTGRES_*` / conexión Colbeef.
2. Confirmar `INVENTARIO_LENGUAS_SYNC_DIAS_ATRAS=1`.
3. `php artisan migrate`
4. Confirmar `INVENTARIO_LENGUAS_SCHEDULE_MINUTES=10` (o el valor acordado).
5. Activar el despertador del schedule:
   - Linux: cron `* * * * * … schedule:run`
   - Windows/Laragon: `scripts\start-schedule.bat` o servicio equivalente con `schedule:work`
6. `php artisan schedule:list` → debe verse `inventario:sync-lenguas`
7. `php artisan inventario:limpiar-operativo --force` (o `inventario:sync-lenguas` si la BD ya está vacía)
8. Abrir Inventario en el navegador y verificar el total disponible

---

## 8. Archivos de código a revisar

| Archivo | Contenido |
|---------|-----------|
| `config/ingresos_lenguas.php` | Defaults y comentarios de ENV (`inventario_sync_dias_atras`) |
| `routes/console.php` | Registro `Schedule::command('inventario:sync-lenguas')` |
| `app/Console/Commands/SyncInventarioLenguasCommand.php` | CLI `inventario:sync-lenguas` |
| `app/Console/Commands/LimpiarOperativoInventarioCommand.php` | CLI `inventario:limpiar-operativo` |
| `app/Services/Inventario/InventarioLenguasSynchronizer.php` | Upsert local, watermark, catch-up, meta |
| `app/Services/IngresosLenguas/IngresosLenguasConsultaSirt.php` | SQL a SIRT |
| `scripts/start-schedule.bat` | Arranque de `schedule:work` en Windows |
| Este documento | `docs/sincronizacion-inventario.md` |

---

## 9. Qué no hace este diseño (a propósito)

- No escribe decomisos/despachos de vuelta a SIRT.
- No sustituye la sync al abrir la vista: ambas pueden coexistir.
- No debe ejecutarse `schedule:work` “dentro” de Apache como si fuera un endpoint web.
