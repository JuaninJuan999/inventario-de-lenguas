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
    | Inventario de Lenguas: segundos entre cada sincronización automática con SIRT
    | mientras la página del inventario está abierta (polling). Mínimo efectivo 30.
    | Use 0 para solo sincronizar al abrir la página, sin repetir.
    */
    'inventario_sync_interval_seconds' => (int) env('INVENTARIO_LENGUAS_SYNC_INTERVAL', 120),

];
