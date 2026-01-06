<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Multi-Tenancy Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for the multi-tenancy system.
    |
    */

    /**
     * Enable or disable multi-tenancy
     */
    'enabled' => env('MULTITENANCY_ENABLED', true),

    /**
     * Tenant identification method
     * Options: 'subdomain', 'domain', 'header', 'session'
     */
    'identification' => 'session',

    /**
     * Use separate databases for each tenant
     * If false, all tenants will share the same database
     * but data will be filtered by tenant scope
     */
    'separate_databases' => env('MULTITENANCY_SEPARATE_DATABASES', true),

    /**
     * Database name pattern for tenant databases
     * Available placeholders: {domain}, {id}
     */
    'database_name_pattern' => 'aulasync_{domain}',

    /**
     * Tenant-aware models
     * These models will automatically be scoped by tenant
     */
    'tenant_models' => [
        \App\Models\Espacio::class,
        \App\Models\Mapa::class,
        \App\Models\Piso::class,
        \App\Models\Planificacion_Asignatura::class,
        \App\Models\Profesor::class,
        \App\Models\Asignatura::class,
        \App\Models\Reserva::class,
        \App\Models\Horario::class,
    ],

    /**
     * Tables that should be created in each tenant database
     * when using separate databases
     */
    'tenant_tables' => [
        'espacios',
        'mapas',
        'pisos',
        'planificacion_asignaturas',
        'profesors',
        'reservas',
        'horarios',
        'asignaturas',
        'modulos',
        'bloques',
        'clases_no_realizadas',
        'licencias_profesores',
        'recuperacion_clases',
        'asistencias',
        'profesor_atrasos',
        'profesores_colaboradores',
        'planificaciones_profesores_colaboradores',
    ],
];
