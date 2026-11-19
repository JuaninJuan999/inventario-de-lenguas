<?php

return [

    /*
    | Zona horaria para calcular "hoy" en los filtros (Volver a hoy y fechas por defecto).
    | Si APP_TIMEZONE=UTC, sin esto el "día" puede adelantarse respecto a su hora local.
    | Ej.: America/Bogota, America/Caracas, America/Santiago.
    */
    'fecha_operacion_timezone' => env('INGRESOS_LENGUAS_FECHA_OPERACION_TIMEZONE', 'America/Bogota'),

    /*
    | Conexión Laravel (p. ej. colbeef / POSTGRES_* en database.php).
    */
    'connection' => env('INGRESOS_LENGUAS_CONNECTION', 'colbeef'),

    /*
    | Filtro de la consulta de destino (parte_producto_empresa).
    */
    'id_parte_producto' => (int) env('INGRESOS_LENGUAS_ID_PARTE_PRODUCTO', 4),

    /*
    | Máximo de insensibilizaciones por consulta a SIRT (CTE ins_filtrada).
    | Debe cubrir picos de un turno; el visor local lista hasta 2.000.
    */
    'consulta_insensibilizacion_limit' => (int) env('INGRESOS_LENGUAS_CONSULTA_LIMIT', 2000),

    /*
    | Origen de la fecha del filtro "desde/hasta", siempre con producto
    | vinculado a un turno en plan_faena_turno:
    | - plan_faena: fecha en la tabla plan_faena (caso habitual si turno no tiene fecha).
    | - plan_faena_turno: fecha en la propia plan_faena_turno.
    | - turno: join a trazabilidad_proceso.turno (pft_f.id_turno = tur_f.id).
    */
    'turno_fecha_bind' => env('INGRESOS_LENGUAS_TURNO_FECHA_BIND', 'plan_faena'),

    /*
    | Columna de fecha en plan_faena (cuando turno_fecha_bind = plan_faena).
    */
    'plan_faena_fecha_column' => env('INGRESOS_LENGUAS_PLAN_FAENA_FECHA_COLUMN', 'fecha_plan'),

    /*
    | Columna de fecha en plan_faena_turno (cuando turno_fecha_bind = plan_faena_turno).
    */
    'plan_faena_turno_fecha_column' => env('INGRESOS_LENGUAS_PLAN_FAENA_TURNO_FECHA_COLUMN', 'fecha_registro'),

    /*
    | Columna de fecha en turno (cuando turno_fecha_bind = turno).
    */
    'turno_tabla_fecha_column' => env('INGRESOS_LENGUAS_TURNO_TABLA_FECHA_COLUMN', 'fecha'),

    /*
    |--------------------------------------------------------------------------
    | Sacrificio de emergencia (sin plan de faena)
    |--------------------------------------------------------------------------
    |
    | Las reses de emergencia suelen existir en insensibilizacion pero sin fila
    | en plan_faena_producto, por eso el EXISTS de turno las excluía.
    | Se incluyen además por id_proceso y/o id_puesto_trabajo (SIRT Colbeef):
    |   proceso 40 = Insensibilizacion Sacrificio de Emergencia
    |   puesto 7  = Sacrificio De Emergencia
    | Con rango de fechas se filtra por ins.fecha_registro.
    | fecha_turno_referencia cae en fecha_registro si no hay plan.
    */
    'incluir_sacrificio_emergencia' => filter_var(
        env('INGRESOS_LENGUAS_INCLUIR_EMERGENCIA', true),
        FILTER_VALIDATE_BOOLEAN
    ),
    'emergencia_id_proceso' => (int) env('INGRESOS_LENGUAS_EMERGENCIA_ID_PROCESO', 40),
    'emergencia_id_puesto_trabajo' => (int) env('INGRESOS_LENGUAS_EMERGENCIA_ID_PUESTO', 7),

    /*
    |--------------------------------------------------------------------------
    | Ventana de sync del inventario (días hacia atrás)
    |--------------------------------------------------------------------------
    |
    | Cuando sync / inventario:sync-lenguas / importar-hoy no reciben rango
    | explícito, se importa desde (hoy_operación − N) hasta hoy_operación.
    | Default 1 = desde ayer (incluye turno de emergencia y faena del día previo).
    | 0 = solo el día de operación.
    */
    'inventario_sync_dias_atras' => max(0, (int) env('INVENTARIO_LENGUAS_SYNC_DIAS_ATRAS', 1)),

    /*
    |--------------------------------------------------------------------------
    | Sync con la vista abierta (polling del navegador)
    |--------------------------------------------------------------------------
    |
    | Segundos entre cada POST inventario.lenguas.importar_hoy mientras la
    | página de Inventario está abierta. Mínimo efectivo 30. Use 0 para solo
    | sincronizar al cargar, sin repetir.
    |
    | Esto NO reemplaza el scheduler de segundo plano (ver abajo). Si nadie
    | tiene la vista abierta, esta vía no actualiza la réplica.
    |
    | Guía servidor: docs/sincronizacion-inventario.md
    */
    'inventario_sync_interval_seconds' => (int) env('INVENTARIO_LENGUAS_SYNC_INTERVAL', 120),

    /*
    |--------------------------------------------------------------------------
    | Sync en segundo plano (scheduler Laravel)
    |--------------------------------------------------------------------------
    |
    | Minutos entre cada `php artisan inventario:sync-lenguas` registrado en
    | routes/console.php. 0 = no programar la tarea (solo sync manual / vista).
    |
    | IMPORTANTE — “opcional” pero necesario para sync sin navegador:
    | Registrar la tarea no basta. En el servidor debe haber un despertador:
    |   - Linux producción: cron cada minuto → php artisan schedule:run
    |   - Windows / Laragon: scripts/start-schedule.bat → schedule:work
    |
    | Comprobar: php artisan schedule:list
    | Probar a mano: php artisan inventario:sync-lenguas
    |
    | Guía completa: docs/sincronizacion-inventario.md
    */
    'inventario_schedule_minutes' => (int) env('INVENTARIO_LENGUAS_SCHEDULE_MINUTES', 10),

];
