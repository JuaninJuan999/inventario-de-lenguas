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

];
