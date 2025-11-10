<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Rutas de API
    |--------------------------------------------------------------------------
    |
    | Puedes definir una o más rutas que deben tener habilitado CORS.
    | Puedes usar comodines, como 'api/*'.
    */
    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
    ],

    /*
    |--------------------------------------------------------------------------
    | Métodos Permitidos
    |--------------------------------------------------------------------------
    |
    | Especifica qué métodos HTTP están permitidos. ' * ' permite todos.
    */
    'allowed_methods' => ['*'],

    /*
    |--------------------------------------------------------------------------
    | Orígenes Permitidos
    |--------------------------------------------------------------------------
    |
    | Especifica qué orígenes pueden hacer peticiones. '*' lo permite todo.
    | Para producción, deberías cambiar esto a tu dominio real:
    | ['http://localhost:8000', 'https://tu-dominio.com']
    */
    'allowed_origins' => ['*'],

    /*
    |--------------------------------------------------------------------------
    | Patrones de Orígenes Permitidos
    |--------------------------------------------------------------------------
    |
    | Puedes usar expresiones regulares para orígenes dinámicos.
    */
    'allowed_origins_patterns' => [],

    /*
    |--------------------------------------------------------------------------
    | Encabezados Permitidos
    |--------------------------------------------------------------------------
    |
    | Encabezados que se permiten en la petición. '*' permite todos.
    */
    'allowed_headers' => ['*'],

    /*
    |--------------------------------------------------------------------------
    | Encabezados Expuestos
    |--------------------------------------------------------------------------
    |
    | Encabezados que se exponen al script del frontend.
    */
    'exposed_headers' => [],

    /*
    |--------------------------------------------------------------------------
    | Edad Máxima (Max Age)
    |--------------------------------------------------------------------------
    |
    | Controla el caché de 'Access-Control-Max-Age'.
    */
    'max_age' => 0,

    /*
    |--------------------------------------------------------------------------
    | Soporte de Credenciales
    |--------------------------------------------------------------------------
    |
    | ¡ESTA ES LA CLAVE MÁS IMPORTANTE!
    | Permite que el frontend (Service Worker) envíe la cookie de sesión.
    */
    'supports_credentials' => true, // <-- ¡YA LO PUSE EN 'true' PARA TI!

];