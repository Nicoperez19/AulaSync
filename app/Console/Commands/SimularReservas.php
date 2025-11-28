<?php

namespace App\Console\Commands;

use App\Helpers\SemesterHelper;
use App\Models\Espacio;
use App\Models\Planificacion_Asignatura;
use App\Models\Profesor;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SimularReservas extends Command
{
    protected $signature = 'simular:reservas 
                            {--velocidad=1 : Velocidad de simulación (1 = tiempo real, 2 = 2x más rápido, etc.)}
                            {--probabilidad-falta=15 : Probabilidad de que un profesor falte (0-100)}
                            {--probabilidad-temprano=20 : Probabilidad de terminar 20 minutos antes (0-100)}
                            {--probabilidad-tarde=15 : Probabilidad de terminar hasta 30 minutos después (0-100)}
                            {--solo-mostrar : Solo mostrar las clases programadas sin simular}';

    protected $description = 'Simula profesores haciendo reservas y finalizándolas en tiempo real para testing';

    private $estadisticas = [
        'reservas_creadas' => 0,
        'reservas_finalizadas' => 0,
        'profesores_faltaron' => 0,
        'finalizaciones_tempranas' => 0,
        'finalizaciones_tardias' => 0,
        'finalizaciones_normales' => 0,
    ];

    public function handle()
    {
        $this->info('╔══════════════════════════════════════════════════════════════╗');
        $this->info('║           SIMULADOR DE RESERVAS - AULASYNC                   ║');
        $this->info('╚══════════════════════════════════════════════════════════════╝');
        $this->newLine();

        // Obtener opciones
        $velocidad = (float) $this->option('velocidad');
        $probFalta = (int) $this->option('probabilidad-falta');
        $probTemprano = (int) $this->option('probabilidad-temprano');
        $probTarde = (int) $this->option('probabilidad-tarde');
        $soloMostrar = $this->option('solo-mostrar');

        $this->info('📊 Configuración de simulación:');
        $this->info("   ├─ Velocidad: {$velocidad}x");
        $this->info("   ├─ Probabilidad de falta: {$probFalta}%");
        $this->info("   ├─ Probabilidad de terminar temprano: {$probTemprano}%");
        $this->info("   └─ Probabilidad de terminar tarde: {$probTarde}%");
        $this->newLine();

        // Obtener información del período actual
        $periodo = SemesterHelper::getCurrentPeriod();
        $this->info("📅 Período académico actual: {$periodo}");
        $this->newLine();

        if ($soloMostrar) {
            $this->mostrarClasesProgramadas($periodo);

            return 0;
        }

        $this->warn('⚠️  Presiona Ctrl+C para detener la simulación');
        $this->newLine();

        // Ejecutar el bucle de simulación
        $this->ejecutarSimulacion($velocidad, $probFalta, $probTemprano, $probTarde, $periodo);

        return 0;
    }

    /**
     * Mostrar las clases programadas para hoy
     */
    private function mostrarClasesProgramadas(string $periodo): void
    {
        $ahora = Carbon::now();
        $diaActual = $this->getDiaActual();

        $this->info("📋 Clases programadas para hoy ({$diaActual}):");
        $this->newLine();

        $planificaciones = Planificacion_Asignatura::with(['modulo', 'espacio', 'asignatura', 'horario.profesor'])
            ->whereHas('horario', function ($query) use ($periodo) {
                $query->where('periodo', $periodo);
            })
            ->whereHas('modulo', function ($query) use ($diaActual) {
                $query->where('dia', $diaActual);
            })
            ->get()
            ->sortBy(function ($planificacion) {
                return $planificacion->modulo->hora_inicio;
            });

        if ($planificaciones->isEmpty()) {
            $this->warn('No hay clases programadas para hoy.');

            return;
        }

        $headers = ['Módulo', 'Hora Inicio', 'Hora Término', 'Espacio', 'Profesor', 'Asignatura'];
        $rows = [];

        foreach ($planificaciones as $planificacion) {
            $profesor = $planificacion->horario->profesor ?? null;
            $rows[] = [
                $planificacion->id_modulo,
                $planificacion->modulo->hora_inicio,
                $planificacion->modulo->hora_termino,
                $planificacion->espacio->nombre_espacio ?? $planificacion->id_espacio,
                $profesor ? $profesor->name : 'Sin profesor',
                $planificacion->asignatura->nombre_asignatura ?? 'N/A',
            ];
        }

        $this->table($headers, $rows);
        $this->info('Total: '.count($rows).' clases programadas');
    }

    /**
     * Ejecutar el bucle principal de simulación
     */
    private function ejecutarSimulacion(float $velocidad, int $probFalta, int $probTemprano, int $probTarde, string $periodo): void
    {
        $intervaloChequeo = 60 / $velocidad; // Segundos entre chequeos (1 minuto simulado)
        $reservasActivas = []; // Almacena reservas activas con su hora de finalización prevista

        $this->info('🚀 Iniciando simulación...');
        $this->newLine();

        while (true) {
            $ahora = Carbon::now();
            $diaActual = $this->getDiaActual();
            $horaActual = $ahora->format('H:i:s');

            $this->line("⏰ [{$ahora->format('Y-m-d H:i:s')}] Verificando actividad...");

            // 1. Verificar clases que deberían iniciar ahora
            $this->verificarInicioClases($diaActual, $horaActual, $periodo, $probFalta, $reservasActivas);

            // 2. Verificar reservas que deberían finalizar
            $this->verificarFinalizacionReservas($ahora, $probTemprano, $probTarde, $reservasActivas);

            // 3. Mostrar estadísticas periódicas
            $this->mostrarEstadisticasPeriodicas();

            // Esperar el intervalo antes del próximo chequeo
            sleep((int) $intervaloChequeo);
        }
    }

    /**
     * Verificar e iniciar clases que deberían comenzar ahora
     */
    private function verificarInicioClases(string $diaActual, string $horaActual, string $periodo, int $probFalta, array &$reservasActivas): void
    {
        // Buscar planificaciones que inician en este momento (con margen de 2 minutos)
        $horaInicio = Carbon::parse($horaActual)->subMinutes(1)->format('H:i:s');
        $horaFin = Carbon::parse($horaActual)->addMinutes(1)->format('H:i:s');

        $planificaciones = Planificacion_Asignatura::with(['modulo', 'espacio', 'asignatura', 'horario.profesor'])
            ->whereHas('horario', function ($query) use ($periodo) {
                $query->where('periodo', $periodo);
            })
            ->whereHas('modulo', function ($query) use ($diaActual, $horaInicio, $horaFin) {
                $query->where('dia', $diaActual)
                    ->whereBetween('hora_inicio', [$horaInicio, $horaFin]);
            })
            ->get();

        foreach ($planificaciones as $planificacion) {
            $profesor = $planificacion->horario->profesor ?? null;
            if (! $profesor) {
                continue;
            }

            // Verificar si ya existe una reserva activa para este profesor en este espacio
            $reservaExistente = Reserva::where('run_profesor', $profesor->run_profesor)
                ->where('id_espacio', $planificacion->id_espacio)
                ->where('fecha_reserva', Carbon::now()->format('Y-m-d'))
                ->where('estado', 'activa')
                ->first();

            if ($reservaExistente) {
                continue; // Ya tiene reserva, no crear otra
            }

            // Determinar si el profesor falta
            if (rand(1, 100) <= $probFalta) {
                $this->estadisticas['profesores_faltaron']++;
                $this->warn("   ❌ FALTA: {$profesor->name} no se presentó a clase en {$planificacion->espacio->nombre_espacio}");
                Log::info("Simulación: Profesor {$profesor->run_profesor} faltó a clase", [
                    'espacio' => $planificacion->id_espacio,
                    'modulo' => $planificacion->id_modulo,
                ]);

                continue;
            }

            // Crear la reserva
            $this->crearReservaSimulada($profesor, $planificacion, $reservasActivas);
        }
    }

    /**
     * Crear una reserva simulada para un profesor
     */
    private function crearReservaSimulada(Profesor $profesor, Planificacion_Asignatura $planificacion, array &$reservasActivas): void
    {
        $espacio = $planificacion->espacio;

        // Verificar que el espacio esté disponible
        if ($espacio->estado !== 'Disponible') {
            $this->warn("   ⚠️  Espacio {$espacio->nombre_espacio} no disponible para {$profesor->name}");

            return;
        }

        // Verificar si el profesor ya tiene una reserva activa
        $reservaExistente = Reserva::where('run_profesor', $profesor->run_profesor)
            ->where('estado', 'activa')
            ->whereNull('hora_salida')
            ->first();

        if ($reservaExistente) {
            $this->warn("   ⚠️  {$profesor->name} ya tiene una reserva activa");

            return;
        }

        // Crear la reserva
        $reserva = new Reserva;
        $reserva->id_reserva = Reserva::generarIdUnico();
        $reserva->run_profesor = $profesor->run_profesor;
        $reserva->id_espacio = $espacio->id_espacio;
        $reserva->fecha_reserva = Carbon::now()->format('Y-m-d');
        $reserva->hora = Carbon::now()->format('H:i:s');
        $reserva->tipo_reserva = 'clase';
        $reserva->estado = 'activa';
        $reserva->modulos = $planificacion->id_modulo;
        $reserva->save();

        // Cambiar estado del espacio
        $espacio->estado = 'Ocupado';
        $espacio->save();

        // Registrar en reservas activas con hora de finalización prevista
        $horaTermino = Carbon::parse($planificacion->modulo->hora_termino);
        $reservasActivas[$reserva->id_reserva] = [
            'reserva' => $reserva,
            'hora_termino_prevista' => $horaTermino,
            'profesor_nombre' => $profesor->name,
            'espacio_nombre' => $espacio->nombre_espacio,
        ];

        $this->estadisticas['reservas_creadas']++;
        $this->info("   ✅ RESERVA CREADA: {$profesor->name} en {$espacio->nombre_espacio} (hasta {$horaTermino->format('H:i')})");

        Log::info('Simulación: Reserva creada', [
            'id_reserva' => $reserva->id_reserva,
            'profesor' => $profesor->run_profesor,
            'espacio' => $espacio->id_espacio,
        ]);
    }

    /**
     * Verificar y procesar finalizaciones de reservas
     */
    private function verificarFinalizacionReservas(Carbon $ahora, int $probTemprano, int $probTarde, array &$reservasActivas): void
    {
        foreach ($reservasActivas as $idReserva => $infoReserva) {
            $horaTerminoPrevista = $infoReserva['hora_termino_prevista'];
            $reserva = Reserva::find($idReserva);

            if (! $reserva || $reserva->estado !== 'activa') {
                unset($reservasActivas[$idReserva]);

                continue;
            }

            // Determinar si debe finalizar la reserva
            $debeTerminar = false;
            $tipoFinalizacion = 'normal';
            $minutosAjuste = 0;

            // Verificar si termina temprano (20 minutos antes)
            $horaTerminoTemprano = $horaTerminoPrevista->copy()->subMinutes(20);
            if ($ahora >= $horaTerminoTemprano && $ahora < $horaTerminoPrevista) {
                if (rand(1, 100) <= $probTemprano) {
                    $debeTerminar = true;
                    $tipoFinalizacion = 'temprano';
                    $minutosAjuste = -20;
                }
            }

            // Verificar si termina en su hora normal
            if (! $debeTerminar && $ahora >= $horaTerminoPrevista) {
                // Verificar si termina tarde (hasta 30 minutos después)
                if (rand(1, 100) <= $probTarde) {
                    $minutosRetraso = rand(1, 30);
                    $horaTerminoTarde = $horaTerminoPrevista->copy()->addMinutes($minutosRetraso);

                    if ($ahora >= $horaTerminoTarde) {
                        $debeTerminar = true;
                        $tipoFinalizacion = 'tarde';
                        $minutosAjuste = $minutosRetraso;
                    }
                } else {
                    $debeTerminar = true;
                    $tipoFinalizacion = 'normal';
                }
            }

            // También forzar finalización si ya pasaron más de 30 minutos
            $horaLimite = $horaTerminoPrevista->copy()->addMinutes(30);
            if ($ahora >= $horaLimite) {
                $debeTerminar = true;
                $tipoFinalizacion = 'tarde';
                $minutosAjuste = 30;
            }

            if ($debeTerminar) {
                $this->finalizarReservaSimulada($reserva, $infoReserva, $tipoFinalizacion, $minutosAjuste);
                unset($reservasActivas[$idReserva]);
            }
        }
    }

    /**
     * Finalizar una reserva simulada
     */
    private function finalizarReservaSimulada(Reserva $reserva, array $infoReserva, string $tipoFinalizacion, int $minutosAjuste): void
    {
        $reserva->hora_salida = Carbon::now()->format('H:i:s');
        $reserva->estado = 'finalizada';
        $reserva->save();

        // Liberar el espacio
        $espacio = Espacio::find($reserva->id_espacio);
        if ($espacio) {
            $espacio->estado = 'Disponible';
            $espacio->save();
        }

        $this->estadisticas['reservas_finalizadas']++;

        $icono = '🔄';
        $mensaje = '';

        switch ($tipoFinalizacion) {
            case 'temprano':
                $this->estadisticas['finalizaciones_tempranas']++;
                $icono = '⏪';
                $mensaje = '(20 min antes)';
                break;
            case 'tarde':
                $this->estadisticas['finalizaciones_tardias']++;
                $icono = '⏩';
                $mensaje = "(+{$minutosAjuste} min tarde)";
                break;
            default:
                $this->estadisticas['finalizaciones_normales']++;
                $icono = '✔️';
                $mensaje = '(a tiempo)';
        }

        $this->info("   {$icono} RESERVA FINALIZADA: {$infoReserva['profesor_nombre']} dejó {$infoReserva['espacio_nombre']} {$mensaje}");

        Log::info('Simulación: Reserva finalizada', [
            'id_reserva' => $reserva->id_reserva,
            'tipo' => $tipoFinalizacion,
            'ajuste_minutos' => $minutosAjuste,
        ]);
    }

    /**
     * Mostrar estadísticas periódicas
     */
    private function mostrarEstadisticasPeriodicas(): void
    {
        static $ultimoReporte = null;
        $ahora = Carbon::now();

        // Mostrar estadísticas cada 5 minutos
        if ($ultimoReporte === null || $ahora->diffInMinutes($ultimoReporte) >= 5) {
            $this->newLine();
            $this->info('📊 ESTADÍSTICAS DE SIMULACIÓN:');
            $this->info("   ├─ Reservas creadas: {$this->estadisticas['reservas_creadas']}");
            $this->info("   ├─ Reservas finalizadas: {$this->estadisticas['reservas_finalizadas']}");
            $this->info("   ├─ Profesores que faltaron: {$this->estadisticas['profesores_faltaron']}");
            $this->info("   ├─ Finalizaciones tempranas: {$this->estadisticas['finalizaciones_tempranas']}");
            $this->info("   ├─ Finalizaciones tardías: {$this->estadisticas['finalizaciones_tardias']}");
            $this->info("   └─ Finalizaciones normales: {$this->estadisticas['finalizaciones_normales']}");
            $this->newLine();

            $ultimoReporte = $ahora;
        }
    }

    /**
     * Obtener el día actual en español para coincidir con los módulos
     */
    private function getDiaActual(): string
    {
        $diasMap = [
            'monday' => 'lunes',
            'tuesday' => 'martes',
            'wednesday' => 'miércoles',
            'thursday' => 'jueves',
            'friday' => 'viernes',
            'saturday' => 'sábado',
            'sunday' => 'domingo',
        ];

        $diaIngles = strtolower(Carbon::now()->format('l'));

        return $diasMap[$diaIngles] ?? $diaIngles;
    }
}
