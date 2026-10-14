<?php

use App\Models\AreaAcademica;
use App\Models\Asignatura;
use App\Models\Asistencia;
use App\Models\AsistenteAcademico;
use App\Models\Carrera;
use App\Models\ClaseNoRealizada;
use App\Models\Espacio;
use App\Models\Facultad;
use App\Models\Horario;
use App\Models\JefeCarrera;
use App\Models\LicenciaProfesor;
use App\Models\LlaveNoDevuelta;
use App\Models\Mapa;
use App\Models\Modulo;
use App\Models\Notificacion;
use App\Models\Piso;
use App\Models\Planificacion_Asignatura;
use App\Models\PlanificacionProfesorColaborador;
use App\Models\Profesor;
use App\Models\ProfesorAtraso;
use App\Models\ProfesorColaborador;
use App\Models\RecuperacionClase;
use App\Models\Reserva;
use App\Models\Solicitante;
use App\Models\VetoSalaEstudio;
use App\Models\Visitante;

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

    /*
     * Disable Spatie's default queue tenant-awareness listener since AulaSync
     * uses its own custom App\Models\Tenant implementation on the 'tenant' connection.
     */
    'queues_are_tenant_aware_by_default' => false,

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
        Espacio::class,
        Mapa::class,
        Piso::class,
        Planificacion_Asignatura::class,
        Profesor::class,
        Asignatura::class,
        Reserva::class,
        Horario::class,
        Modulo::class,
        ClaseNoRealizada::class,
        LicenciaProfesor::class,
        RecuperacionClase::class,
        Asistencia::class,
        ProfesorAtraso::class,
        ProfesorColaborador::class,
        PlanificacionProfesorColaborador::class,
        AsistenteAcademico::class,
        JefeCarrera::class,
        VetoSalaEstudio::class,
        Visitante::class,
        Carrera::class,
        Facultad::class,
        AreaAcademica::class,
        Solicitante::class,
        Notificacion::class,
        LlaveNoDevuelta::class,
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
