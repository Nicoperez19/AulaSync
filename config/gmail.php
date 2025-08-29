<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Gmail Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración específica para Gmail SMTP
    |
    */

    'host' => env('MAIL_HOST', 'smtp.gmail.com'),
    'port' => env('MAIL_PORT', 587),
    'encryption' => env('MAIL_ENCRYPTION', 'tls'),
    'username' => env('MAIL_USERNAME'),
    'password' => env('MAIL_PASSWORD'),
    'from_address' => env('MAIL_FROM_ADDRESS', 'soporteaulasync@gmail.com'),
    'from_name' => env('MAIL_FROM_NAME', 'AulaSync'),
    
    /*
    |--------------------------------------------------------------------------
    | Gmail App Password
    |--------------------------------------------------------------------------
    |
    | Para Gmail, necesitas usar una contraseña de aplicación
    | No uses tu contraseña normal de Gmail
    |
    */
    
    'app_password' => env('MAIL_PASSWORD'),
    
    /*
    |--------------------------------------------------------------------------
    | Gmail Settings
    |--------------------------------------------------------------------------
    |
    | Configuraciones adicionales para Gmail
    |
    */
    
    'timeout' => 60,
    'verify_peer' => false,
    'verify_peer_name' => false,
    'allow_self_signed' => true,
];
