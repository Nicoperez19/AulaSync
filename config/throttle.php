<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the rate limiting options for your application.
    | These settings control how many requests a user can make in a given
    | time period before being throttled.
    |
    */

    'api' => [
        'requests_per_minute' => env('API_RATE_LIMIT', 120), // 120 requests por minuto
        'decay_minutes' => 1,
    ],

    'web' => [
        'requests_per_minute' => env('WEB_RATE_LIMIT', 60), // 60 requests por minuto
        'decay_minutes' => 1,
    ],

    'auth' => [
        'requests_per_minute' => env('AUTH_RATE_LIMIT', 5), // 5 requests por minuto
        'decay_minutes' => 1,
    ],

    /*
    |--------------------------------------------------------------------------
    | Throttle Exceptions
    |--------------------------------------------------------------------------
    |
    | You may specify which routes should be excluded from rate limiting.
    | These routes will not be throttled regardless of the rate limit settings.
    |
    */

    'exclude' => [
        // Rutas que no deben tener rate limiting
        'api/espacios/estados',
        'api/verificar-usuario/*',
    ],

];
