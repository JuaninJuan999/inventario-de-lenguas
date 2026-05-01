<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Conexión Laravel al servidor PostgreSQL (Colbeef / trazabilidad)
    |--------------------------------------------------------------------------
    |
    | Debe coincidir con una entrada en config/database.php (p. ej. "colbeef").
    | Las credenciales suelen definirse con POSTGRES_* en .env (ver database.php).
    |
    */

    'colbeef_connection' => env('COLBEEF_DB_CONNECTION', 'colbeef'),

    /*
    |--------------------------------------------------------------------------
    | Filtro user_name en vehiculo_asignado (WHERE va.user_name = ?)
    |--------------------------------------------------------------------------
    |
    | Debe coincidir con el valor en la columna user_name de vehiculo_asignado
    | (en pgAdmin suele ser porteria52). Si inicia sesión otro usuario de Laravel,
    | sin esta variable no verá esos registros.
    |
    | DESPACHO_USE_AUTH_USERNAME=true usa el username del usuario logueado en Laravel.
    |
    */

    'vehiculo_registra_username' => env('DESPACHO_VEHICULO_USER_NAME', 'porteria52'),

    'vehiculo_registra_use_auth_username' => filter_var(
        env('DESPACHO_USE_AUTH_USERNAME', false),
        FILTER_VALIDATE_BOOLEAN
    ),

    /*
    |--------------------------------------------------------------------------
    | Código de barras en despacho (coincidencia con inventario local)
    |--------------------------------------------------------------------------
    |
    | Si el lector envía p. ej. 2604-02375-6000 y en base local el id es 2604-02375,
    | el último segmento tras "-" debe ser solo dígitos y de esta longitud (p. ej. 4 para 6000).
    | Con menos de tres segmentos no se recorta (p. ej. 2604-02375 se deja igual).
    |
    */

    'codigo_barras_suffix_digitos' => max(1, min(12, (int) env('DESPACHO_CODIGO_BARRAS_SUFIJO_DIGITOS', 4))),

    /*
    |--------------------------------------------------------------------------
    | Checklist físico vs Colbeef (parte_producto_vehiculo)
    |--------------------------------------------------------------------------
    |
    | id_parte_producto habitualmente coincide con INGRESOS_LENGUAS_ID_PARTE_PRODUCTO (p. ej. 4).
    | Por defecto false: listado tipo alistamiento (placa + producto + fecha + producto_entregado) sin acotar por
    | user_name. Ponga DESPACHO_PPV_FILTRAR_USER_NAME=true y DESPACHO_VEHICULO_USER_NAME (p. ej. porteria52) si
    | debe coincidir solo con quien registró el vehículo en Colbeef.
    |
    */

    'id_parte_producto_vehiculo' => (int) env('DESPACHO_ID_PARTE_PRODUCTO', 4),

    'filtrar_ppv_por_user_name_vehiculo' => filter_var(
        env('DESPACHO_PPV_FILTRAR_USER_NAME', false),
        FILTER_VALIDATE_BOOLEAN
    ),

    /*
    |--------------------------------------------------------------------------
    | ¿En qué columna filtrar user_name cuando el filtro anterior está activo?
    |--------------------------------------------------------------------------
    |
    | Valores: vehiculo (= va.user_name en vehiculo_asignado) o parte_producto (= ppv.user_name).
    | La consulta exploratoria en pgAdmin suele listar ppv.user_name; si solo ve placas incorrectas,
    | pruebe DESPACHO_PPV_USERNAME_EN=parte_producto y DESPACHO_VEHICULO_USER_NAME con el usuario de ppv.
    |
    */

    'ppv_filtrar_username_columna' => env('DESPACHO_PPV_USERNAME_EN', 'vehiculo'),

    /*
    |--------------------------------------------------------------------------
    | Modo “solo consulta” Colbeef (listado crudo para diagnóstico)
    |--------------------------------------------------------------------------
    |
    | DESPACHO_PPV_SOLO_CONSULTA_COLBEEF=true ignora fecha “hoy”, catálogo local de placas,
    | inventario local y el filtro user_name en SQL: solo parte producto + producto_entregado (ver abajo).
    | Desactivar en producción cuando ya no necesite depurar la lista de placas.
    |
    */

    'ppv_modo_solo_consulta_colbeef' => filter_var(
        env('DESPACHO_PPV_SOLO_CONSULTA_COLBEEF', false),
        FILTER_VALIDATE_BOOLEAN
    ),

    /*
    |--------------------------------------------------------------------------
    | Solo registros del día actual (fecha_registro en parte_producto_vehiculo)
    |--------------------------------------------------------------------------
    |
    | DESPACHO_PPV_SOLO_FECHA_HOY=false lista placas/líneas históricas pendientes.
    | La fecha “hoy” usa la zona horaria de config/app.php (timezone).
    |
    */

    'ppv_filtrar_fecha_registro_hoy' => filter_var(
        env('DESPACHO_PPV_SOLO_FECHA_HOY', true),
        FILTER_VALIDATE_BOOLEAN
    ),

    /*
    |--------------------------------------------------------------------------
    | Solo productos que existen en inventario local sin despachar
    |--------------------------------------------------------------------------
    |
    | Afecta la lista de placas (solo vehículos con al menos una línea despachable)
    | y el detalle del checklist / validación al finalizar.
    |
    */

    'ppv_solo_con_inventario_local_disponible' => filter_var(
        env('DESPACHO_PPV_SOLO_INVENTARIO_LOCAL', true),
        FILTER_VALIDATE_BOOLEAN
    ),

    /*
    |--------------------------------------------------------------------------
    | Solo placas registradas en catálogo local (despacho_operador_placas)
    |--------------------------------------------------------------------------
    |
    | Cruza va.placa_vehiculo (Colbeef) con la tabla local para mantener el mismo
    | universo de placas que ya configuró en esta instalación.
    | La coincidencia ignora espacios y mayúsculas (p. ej. LPL051 = LPL 051).
    | Si la tabla está vacía, el filtro no se aplica (compatibilidad hasta cargar datos).
    |
    */

    'ppv_filtrar_placa_catalogo_local' => filter_var(
        env('DESPACHO_PPV_FILTRAR_PLACA_CATALOGO_LOCAL', true),
        FILTER_VALIDATE_BOOLEAN
    ),

    /*
    |--------------------------------------------------------------------------
    | Valor de ppv.producto_entregado en las consultas Colbeef de despacho
    |--------------------------------------------------------------------------
    |
    | true = solo filas con producto_entregado true (p. ej. alineado a su SELECT en pgAdmin).
    | false = solo filas con producto_entregado false (listado histórico de “pendientes”).
    | El listado de placas y el checklist deduplican por (vehículo, id_producto): una fila,
    | la de ppv.id más alto entre las que cumplen el filtro.
    |
    */

    'ppv_producto_entregado_valor' => filter_var(
        env('DESPACHO_PPV_PRODUCTO_ENTREGADO', true),
        FILTER_VALIDATE_BOOLEAN
    ),

];
