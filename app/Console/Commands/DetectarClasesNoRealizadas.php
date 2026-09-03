<?php

namespace App\Console\Commands;

use App\Helpers\SemesterHelper;
use App\Models\ClaseNoRealizada;
use App\Models\Notificacion;
use App\Models\Planificacion_Asignatura;
use App\Models\Reserva;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class DetectarClasesNoRealizadas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clases:detectar-no-realizadas 
                            {--force : Forzar detección ignorando el tiempo de gracia}
                            {--dry-run : Solo mostrar qué se detectaría sin registrar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Detecta automáticamente las clases no realizadas (15 min después del inicio del módulo)';

    /**
     * Tiempo de gracia en minutos antes de considerar una clase como no realizada
     */
    const TIEMPO_GRACIA_MINUTOS = 15;

    /**
     * Horarios de módulos
     */
    private $horariosModulos = [
        'lunes' => [
            1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'],
            2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'],
            3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'],
            4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'],
            5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'],
            6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'],
            7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'],
            8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'],
            9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'],
            10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'],
            11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'],
            12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'],
            13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'],
            14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'],
            15 => ['inicio' => '22:10:00', 'fin' => '23:00:00'],
        ],
        'martes' => [
            1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'],
            2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'],
            3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'],
            4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'],
            5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'],
            6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'],
            7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'],
            8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'],
            9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'],
            10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'],
            11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'],
            12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'],
            13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'],
            14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'],
            15 => ['inicio' => '22:10:00', 'fin' => '23:00:00'],
        ],
        'miercoles' => [
            1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'],
            2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'],
            3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'],
            4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'],
            5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'],
            6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'],
            7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'],
            8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'],
            9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'],
            10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'],
            11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'],
            12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'],
            13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'],
            14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'],
            15 => ['inicio' => '22:10:00', 'fin' => '23:00:00'],
        ],
        'jueves' => [
            1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'],
            2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'],
            3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'],
            4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'],
            5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'],
            6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'],
            7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'],
            8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'],
            9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'],
            10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'],
            11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'],
            12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'],
            13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'],
            14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'],
            15 => ['inicio' => '22:10:00', 'fin' => '23:00:00'],
        ],
        'viernes' => [
            1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'],
            2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'],
            3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'],
            4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'],
            5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'],
            6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'],
            7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'],
            8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'],
            9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'],
            10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'],
            11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'],
            12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'],
            13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'],
            14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'],
            15 => ['inicio' => '22:10:00', 'fin' => '23:00:00'],
        ],
        'sabado' => [
            1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'],
            2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'],
            3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'],
            4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'],
            5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'],
            6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'],
        ],
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando detección de clases no realizadas...');
        $this->info('Tiempo de gracia: ' . self::TIEMPO_GRACIA_MINUTOS . ' minutos');

        $hoy = Carbon::now();
        $diaActual = strtolower($hoy->locale('es')->isoFormat('dddd'));
        $horaActual = $hoy->format('H:i:s');
        $fechaActual = $hoy->toDateString();

        // Normalizar día
        $diaKey = $this->normalizarDia($diaActual);
        
        // Solo ejecutar en días laborales
        if ($diaActual === 'domingo') {
            $this->info('Hoy es domingo, no se ejecuta la detección.');
            return 0;
        }

        // Verificar si hay horarios para este día
        if (!isset($this->horariosModulos[$diaKey])) {
            $this->info("No hay horarios definidos para el día: $diaKey");
            return 0;
        }

        // Obtener el período actual
        $periodo = SemesterHelper::getCurrentPeriod();
        $this->info("Período académico: $periodo");
        $this->info("Día: $diaActual ($diaKey), Hora actual: $horaActual");

        // Mapear el día a su prefijo
        $prefijoDia = $this->obtenerPrefijoDia($diaActual);
        if (!$prefijoDia) {
            $this->error('No se pudo determinar el prefijo del día.');
            return 1;
        }

        // Obtener todos los tenants
        $tenants = Tenant::all();
        
        foreach ($tenants as $tenant) {
            $this->procesarTenant($tenant, $diaKey, $prefijoDia, $periodo, $fechaActual);
        }

        return 0;
    }

    /**
     * Procesar detección para un tenant específico
     */
    private function procesarTenant($tenant, $diaKey, $prefijoDia, $periodo, $fechaActual)
    {
        try {
            // Establecer este tenant como el actual
            $tenant->makeCurrent();

            $this->info("Procesando tenant: {$tenant->name} ({$tenant->database})");

            $hoy = Carbon::now();
            $horaActual = $hoy->format('H:i:s');

            // Verificar si hoy es feriado o día sin actividad para este tenant
            if (\App\Models\DiaFeriado::esFeriado($fechaActual)) {
                $this->info("  [{$tenant->database}] Hoy es feriado o día sin actividad. Se omiten detecciones automáticas.");
                return;
            }

            // Obtener módulos que ya pasaron el tiempo de gracia
            $modulosParaVerificar = $this->obtenerModulosParaVerificar($diaKey, $horaActual);
            
            if (empty($modulosParaVerificar)) {
                $this->info("  [{$tenant->database}] No hay módulos que hayan pasado el tiempo de gracia todavía.");
                return;
            }

            $this->info("  [{$tenant->database}] Módulos a verificar: " . implode(', ', $modulosParaVerificar));

            // Obtener planificaciones de los módulos a verificar
            $planificaciones = $this->obtenerPlanificacionesModulos($prefijoDia, $modulosParaVerificar, $periodo);

            $clasesNoRealizadasDetectadas = 0;
            $atrasosDetectados = 0;
            $clasesRealizadas = 0;

            // Agrupar planificaciones por asignatura + espacio (para manejar clases de múltiples módulos)
            $clasesAgrupadas = $planificaciones->groupBy(function($plan) {
                return $plan->id_asignatura . '-' . $plan->id_espacio;
            });

            foreach ($clasesAgrupadas as $key => $modulosClase) {
                $primerModulo = $modulosClase->sortBy(function($p) {
                    $parts = explode('.', $p->id_modulo);
                    return isset($parts[1]) ? (int)$parts[1] : 0;
                })->first();

                if (!$primerModulo || !$primerModulo->asignatura) {
                    continue;
                }

                // Priorizar el profesor del HORARIO sobre el de la ASIGNATURA
                // ya que el horario es específico para este bloque y espacio.
                $runProfesor = $primerModulo->horario->run_profesor ?? $primerModulo->asignatura->run_profesor ?? null;

                if (!$runProfesor) {
                    continue;
                }


                // Variable $yaRegistrada global eliminada para permitir evaluación por módulo.
                // Anteriormente, si un módulo estaba registrado, omitía todo el bloque.

                // Obtener hora de inicio del primer módulo de esta clase
                $primerModuloNum = $this->obtenerNumeroModulo($primerModulo->id_modulo);
                $horaInicioClase = $this->horariosModulos[$diaKey][$primerModuloNum]['inicio'] ?? null;

                if (!$horaInicioClase) {
                    continue;
                }

                $margenMinutos = \App\Helpers\ModulosHelper::getMargenIngresoMinutos($primerModulo->id_modulo);
                // Calcular hora límite (inicio + margen de ingreso)
                $horaLimite = Carbon::parse($fechaActual . ' ' . $horaInicioClase)
                    ->addMinutes($margenMinutos)
                    ->format('H:i:s');

                // Normalizar el RUN del profesor para la comparación
                $runLimpio = $runProfesor;
                if ($runProfesor && str_contains($runProfesor, '-')) {
                    $parts = explode('-', $runProfesor);
                    $runLimpio = $parts[0];
                }
                $runLimpio = preg_replace('/[^0-9]/', '', $runLimpio);

                // Verificar si el profesor tiene reserva activa en el espacio esperado
                // Priorizar reservas activas sobre finalizadas (por si hubo doble escaneo)
                // y dentro del mismo estado, la más reciente.
                $reserva = Reserva::where('fecha_reserva', $fechaActual)
                    ->where('id_espacio', $primerModulo->id_espacio)
                    ->where(function($q) use ($runProfesor, $runLimpio) {
                        $q->where('run_profesor', $runProfesor)
                          ->orWhere('run_solicitante', $runProfesor)
                          ->orWhereRaw("REPLACE(REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', ''), 'K', '') = ?", [$runLimpio])
                          ->orWhereRaw("REPLACE(REPLACE(REPLACE(REPLACE(run_solicitante, '.', ''), '-', ''), ' ', ''), 'K', '') = ?", [$runLimpio])
                          ->orWhereRaw("REPLACE(REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', ''), 'k', '') = ?", [$runLimpio])
                          ->orWhereRaw("REPLACE(REPLACE(REPLACE(REPLACE(run_solicitante, '.', ''), '-', ''), ' ', ''), 'k', '') = ?", [$runLimpio]);
                    })
                    ->whereIn('estado', ['activa', 'finalizada'])
                    ->whereNotNull('hora')
                    ->orderByRaw("CASE WHEN estado = 'activa' THEN 0 ELSE 1 END")
                    ->orderByDesc('id_reserva')
                    ->first();

                if ($reserva) {
                    // El profesor SÍ hizo la reserva/registro
                    $horaEntrada = $reserva->hora;

                    // Verificar si llegó con atraso (después de los 15 minutos)
                    if ($horaEntrada > $horaLimite) {
                        $minutosAtraso = Carbon::parse($horaInicioClase)->diffInMinutes(Carbon::parse($horaEntrada));
                    
                    // Registrar atraso si existe la tabla
                        $this->registrarAtraso($primerModulo, $runProfesor, $fechaActual, $horaEntrada, $minutosAtraso, $periodo);
                        $atrasosDetectados++;
                        
                        $this->info(sprintf(
                            '  ⏰ ATRASO detectado: %s - Profesor: %s - Atraso: %d min',
                            $primerModulo->asignatura->nombre_asignatura ?? 'Desconocida',
                            $primerModulo->asignatura->profesor->name ?? 'Desconocido',
                            $minutosAtraso
                        ));
                    } else {
                        $clasesRealizadas++;
                    }

                    // Verificar retiros anticipados si la reserva está finalizada
                    if ($reserva->estado === 'finalizada' && !empty($reserva->hora_salida)) {
                        $this->verificarRetiroAnticipado($modulosClase, $reserva, $diaKey, $fechaActual, $periodo, $runProfesor);
                    }
                } else {
                    // El profesor NO hizo reserva y ya pasó el tiempo de gracia
                    if (!$this->option('dry-run')) {
                        // Registrar cada módulo de la clase como no realizado
                        foreach ($modulosClase as $modulo) {
                            // Verificar si ya existe registro de clase no realizada para este módulo hoy
                            $yaRegistrada = ClaseNoRealizada::where('id_asignatura', $modulo->id_asignatura)
                                ->where('id_espacio', $modulo->id_espacio)
                                ->where('id_modulo', $modulo->id_modulo)
                                ->where('fecha_clase', $fechaActual)
                                ->exists();

                            if ($yaRegistrada) {
                                continue; // Ya fue procesada
                            }

                            $claseNoRealizada = ClaseNoRealizada::registrarClaseNoRealizada([
                                'id_asignatura' => $modulo->id_asignatura,
                                'id_espacio' => $modulo->id_espacio,
                                'id_modulo' => $modulo->id_modulo,
                                'run_profesor' => $runProfesor,
                                'fecha_clase' => $fechaActual,
                                'periodo' => $periodo,
                                'motivo' => 'No se registró ingreso del profesor después de ' . self::TIEMPO_GRACIA_MINUTOS . ' minutos (detección automática)',
                                'hora_deteccion' => now(),
                            ]);

                            if ($claseNoRealizada && $claseNoRealizada->wasRecentlyCreated) {
                                // Solo notificar una vez por clase (primer módulo)
                                if ($modulo->id === $primerModulo->id) {
                                    Notificacion::crearNotificacionClaseNoRealizada($claseNoRealizada);
                                }
                            }
                        }
                    }

                    $clasesNoRealizadasDetectadas++;
                    $this->error(sprintf(
                        '  ❌ CLASE NO REALIZADA: %s - Profesor: %s - Espacio: %s',
                        $primerModulo->asignatura->nombre_asignatura ?? 'Desconocida',
                        $primerModulo->asignatura->profesor->name ?? 'Desconocido',
                        $primerModulo->espacio->nombre_espacio ?? 'Desconocido'
                    ));
                }
            }

            $this->info("  [{$tenant->database}] Resumen:");
            $this->info("    - Clases no realizadas detectadas: $clasesNoRealizadasDetectadas");
            $this->info("    - Atrasos detectados: $atrasosDetectados");
            $this->info("    - Clases realizadas: $clasesRealizadas");

        } catch (\Exception $e) {
            $this->error("Error procesando tenant {$tenant->database}: " . $e->getMessage());
            Log::error("Error en DetectarClasesNoRealizadas para tenant {$tenant->database}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Verifica retiros anticipados basado en la holgura permitida
     */
    private function verificarRetiroAnticipado($modulosClase, $reserva, $diaKey, $fechaActual, $periodo, $runProfesor)
    {
        if ($this->option('dry-run')) {
            return;
        }

        // Ordenar módulos por número
        $modulos = $modulosClase->sortBy(function($p) {
            return $this->obtenerNumeroModulo($p->id_modulo);
        })->values();

        $horaSalida = $reserva->hora_salida;

        foreach ($modulos as $index => $modulo) {
            $n = $index + 1; // Número de módulo en la secuencia (1, 2, 3...)
            $toleranciaMinutos = 10 * ($n - 1); // 10 min * (N-1)
            
            $numModulo = $this->obtenerNumeroModulo($modulo->id_modulo);
            $horaFinModulo = $this->horariosModulos[$diaKey][$numModulo]['fin'] ?? null;
            
            if (!$horaFinModulo) continue;

            $horaRequerida = Carbon::parse($horaFinModulo)->subMinutes($toleranciaMinutos)->format('H:i:s');

            // Si la hora de salida es menor a la hora requerida, este módulo no se realizó completamente
            $minutosAntes = Carbon::parse($horaSalida)->diffInMinutes(Carbon::parse($horaRequerida));
            if ($horaSalida < $horaRequerida && $minutosAntes >= 5) {
                // Verificar si ya está registrado este módulo específico
                $yaRegistrado = ClaseNoRealizada::where('id_asignatura', $modulo->id_asignatura)
                    ->where('id_espacio', $modulo->id_espacio)
                    ->where('id_modulo', $modulo->id_modulo)
                    ->where('fecha_clase', $fechaActual)
                    ->exists();

                if (!$yaRegistrado) {
                    $claseNoRealizada = ClaseNoRealizada::registrarClaseNoRealizada([
                        'id_asignatura' => $modulo->id_asignatura,
                        'id_espacio' => $modulo->id_espacio,
                        'id_modulo' => $modulo->id_modulo,
                        'run_profesor' => $runProfesor,
                        'fecha_clase' => $fechaActual,
                        'periodo' => $periodo,
                        'motivo' => "Retiro anticipado: El profesor finalizó a las {$horaSalida}, antes del mínimo requerido ({$horaRequerida}) para el módulo {$n} del bloque.",
                        'hora_deteccion' => now(),
                    ]);
                    
                    $this->error(sprintf(
                        '  ❌ RETIRO ANTICIPADO: %s - Profesor: %s - Módulo: %s - Salida: %s < Req: %s',
                        $modulo->asignatura->nombre_asignatura ?? 'Desconocida',
                        $modulo->asignatura->profesor->name ?? 'Desconocido',
                        $modulo->id_modulo,
                        $horaSalida,
                        $horaRequerida
                    ));
                }
            }
        }
    }

    /**
     * Obtener módulos que ya pasaron el tiempo de gracia
     */
    private function obtenerModulosParaVerificar($diaKey, $horaActual)
    {
        $modulos = [];
        $horariosDelDia = $this->horariosModulos[$diaKey] ?? [];

        // Obtener prefijo del día a partir de diaKey
        $mapaPrefijos = [
            'lunes' => 'LU',
            'martes' => 'MA',
            'miercoles' => 'MI',
            'miércoles' => 'MI',
            'jueves' => 'JU',
            'viernes' => 'VI',
            'sabado' => 'SA',
            'sábado' => 'SA',
        ];
        $prefijoDia = $mapaPrefijos[$diaKey] ?? 'LU';

        foreach ($horariosDelDia as $numModulo => $horario) {
            $idModulo = $prefijoDia . '.' . $numModulo;
            $margenMinutos = \App\Helpers\ModulosHelper::getMargenIngresoMinutos($idModulo);

            // Calcular hora límite: inicio + margen de ingreso
            $horaLimite = Carbon::parse($horario['inicio'])
                ->addMinutes($margenMinutos)
                ->format('H:i:s');

            // Si la hora actual es mayor que la hora límite, incluir este módulo
            if ($horaActual >= $horaLimite) {
                $modulos[] = $numModulo;
            }
        }

        return $modulos;
    }

    /**
     * Obtener planificaciones de módulos específicos
     */
    private function obtenerPlanificacionesModulos($prefijoDia, $modulosNumeros, $periodo)
    {
        $modulosIds = array_map(function($num) use ($prefijoDia) {
            return $prefijoDia . '.' . $num;
        }, $modulosNumeros);

        return Planificacion_Asignatura::with(['asignatura.profesor', 'horario.profesor', 'espacio', 'modulo'])
            ->whereIn('id_modulo', $modulosIds)
            ->whereHas('horario', function ($q) use ($periodo) {
                $q->where('periodo', $periodo);
            })
            ->get();

    }

    /**
     * Obtener número de módulo desde id_modulo (ej: "LU.3" -> 3)
     */
    private function obtenerNumeroModulo($idModulo)
    {
        $parts = explode('.', $idModulo);
        return isset($parts[1]) ? (int)$parts[1] : 0;
    }

    /**
     * Registrar atraso del profesor
     */
    private function registrarAtraso($planificacion, $runProfesor, $fecha, $horaEntrada, $minutosAtraso, $periodo)
    {
        try {
            // Verificar si existe la tabla de atrasos
            if (!DB::getSchemaBuilder()->hasTable('profesor_atrasos')) {
                Log::info("Tabla profesor_atrasos no existe, se omite registro de atraso");
                return;
            }

            $diaKey = $this->normalizarDia(Carbon::parse($fecha)->locale('es')->isoFormat('dddd'));
            $numModulo = $this->obtenerNumeroModulo($planificacion->id_modulo);
            $horaProgramada = $this->horariosModulos[$diaKey][$numModulo]['inicio'] ?? null;

            DB::table('profesor_atrasos')->updateOrInsert(
                [
                    'id_asignatura' => $planificacion->id_asignatura,
                    'id_espacio' => $planificacion->id_espacio,
                    'run_profesor' => $runProfesor,
                    'fecha' => $fecha,
                ],
                [
                    'id_planificacion' => $planificacion->id,
                    'id_modulo' => $planificacion->id_modulo,
                    'hora_programada' => $horaProgramada,
                    'hora_llegada' => $horaEntrada,
                    'minutos_atraso' => $minutosAtraso,
                    'periodo' => $periodo,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        } catch (\Exception $e) {
            Log::warning("No se pudo registrar atraso: " . $e->getMessage());
        }
    }

    /**
     * Obtener el prefijo del día
     */
    private function obtenerPrefijoDia($diaActual)
    {
        $mapaDias = [
            'lunes' => 'LU',
            'martes' => 'MA',
            'miércoles' => 'MI',
            'miercoles' => 'MI',
            'jueves' => 'JU',
            'viernes' => 'VI',
            'sábado' => 'SA',
            'sabado' => 'SA',
        ];

        return $mapaDias[strtolower($diaActual)] ?? null;
    }

    /**
     * Normalizar el nombre del día
     */
    private function normalizarDia($diaActual)
    {
        return \App\Helpers\ModulosHelper::normalizarDia($diaActual);
    }
}
