<?php

return [

    /*
    | Conexión Laravel a SIRT (misma que Colbeef / POSTGRES_* en database.php).
    */
    'sirt_connection' => env('DECOMISOS_SIRT_CONNECTION', 'colbeef'),

    /*
    | Filtros equivalentes a su consulta en pgAdmin (id_parte_producto = 24, id_dictamen = 3).
    */
    'id_parte_producto' => (int) env('DECOMISOS_ID_PARTE_PRODUCTO', 24),

    'id_dictamen' => (int) env('DECOMISOS_ID_DICTAMEN', 3),

];
