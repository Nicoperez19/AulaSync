<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reserva;
use App\Models\Espacio;
use App\Models\Modulo;
use App\Models\Planificacion_Asignatura;
use App\Models\Tenant;
use App\Helpers\SemesterHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class FinalizarReservasExpiradas extends Command
{
    protected $signature = 'reservas:finalizar-expiradas';
    protected $description = 'Finaliza automáticamente las reservas de tipo clase al término de cada módulo';

    // Horarios de módulos por día
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
            15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']
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
            15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']
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
            15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']
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
            15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']
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
            15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']
        ]
    ];

    public function handle()
    {
        $this->info('=== FINALIZANDO RESERVAS EXPIRADAS ===');
        
        $ahora = Carbon::now();
        $fechaHoy = $ahora->toDateString();
        $horaActual = $ahora->format('H:i:s');
        $diaActual = strtolower($ahora->locale('es')->isoFormat('dddd'));

        // Normalizar día (miércoles puede venir con o sin tilde)
        $mapaDias = [
            'lunes' => 'lunes',
            'martes' => 'martes', 
            'miércoles' => 'miercoles',
            'miercoles' => 'miercoles',
            'jueves' => 'jueves',
            'viernes' => 'viernes'
        ];
        
        $diaKey = $mapaDias[$diaActual] ?? $diaActual;
        
        // Si es fin de semana, no hay nada que hacer
        if (!isset($this->horariosModulos[$diaKey])) {
            $this->info('No hay módulos programados para hoy.');
            return 0;
        }

        $periodo = SemesterHelper::getCurrentPeriod();
        $this->info("Fecha: {$fechaHoy}, Hora: {$horaActual}, Día: {$diaKey}, Período: {$periodo}");

        // Buscar reservas activas de hoy que son de tipo 'clase' (clases con horario)
        $reservasActivas = Reserva::where('estado', 'activa')
            ->where('fecha_reserva', $fechaHoy)
            ->where('tipo_reserva', 'clase')
            ->whereNotNull('id_asignatura')
            ->get();

        $this->info("Total de reservas activas de clase: " . $reservasActivas->count());

        $finalizadas = 0;
        $sinFinalizar = 0;

        foreach ($reservasActivas as $reserva) {
            try {
                // Obtener todas las planificaciones para esta asignatura, espacio y período
                if (!$reserva->id_asignatura) {
                    $this->warn("Reserva {$reserva->id_reserva} no tiene asignatura asociada. Saltando...");
                    $sinFinalizar++;
                    continue;
                }

                $planificaciones = Planificacion_Asignatura::where('id_asignatura', $reserva->id_asignatura)
                    ->where('id_espacio', $reserva->id_espacio)
                    ->whereHas('horario', function($q) use ($periodo) {
                        $q->where('periodo', $periodo);
                    })
                    ->with('modulo')
                    ->get();

                if ($planificaciones->isEmpty()) {
                    $this->warn("No se encontró planificación para la reserva {$reserva->id_reserva}");
                    $sinFinalizar++;
                    continue;
                }

                // Obtener el módulo de fin de la clase (el último módulo planificado)
                $planificacionesOrdenadas = $planificaciones->sortBy(function($planificacion) {
                    $moduloParts = explode('.', $planificacion->id_modulo);
                    return isset($moduloParts[1]) ? (int)$moduloParts[1] : 0;
                });

                $ultimaPlanificacion = $planificacionesOrdenadas->last();
                
                if (!$ultimaPlanificacion || !$ultimaPlanificacion->modulo) {
                    $this->warn("No se encontró módulo final para la reserva {$reserva->id_reserva}");
                    $sinFinalizar++;
                    continue;
                }

                // Extraer el número de módulo
                $moduloParts = explode('.', $ultimaPlanificacion->id_modulo);
                $numeroModuloFin = isset($moduloParts[1]) ? (int)$moduloParts[1] : null;

                if (!$numeroModuloFin) {
                    $this->warn("No se pudo extraer número de módulo para {$reserva->id_reserva}");
                    $sinFinalizar++;
                    continue;
                }

                // Obtener la hora de fin del último módulo
                $horariosDelDia = $this->horariosModulos[$diaKey];
                if (!isset($horariosDelDia[$numeroModuloFin])) {
                    $this->warn("No se encontró horario para módulo {$numeroModuloFin}");
                    $sinFinalizar++;
                    continue;
                }

                $horaFinModulo = $horariosDelDia[$numeroModuloFin]['fin'];
                
                // Calcular tiempo transcurrido desde el fin del módulo
                $finModulo = Carbon::createFromTimeString($horaFinModulo);
                $ahora = Carbon::createFromTimeString($horaActual);
                $minutosDesdeFinModulo = $finModulo->diffInMinutes($ahora, false);

                // Finalizar la reserva exactamente cuando termina la clase (sin tiempo de gracia)
                if ($minutosDesdeFinModulo >= 0) {
                    DB::beginTransaction();
                    try {
                        // Finalizar la reserva
                        $reserva->estado = 'finalizada';
                        $reserva->hora_salida = $horaFinModulo; // Usar la hora de fin de la clase
                        
                        // Agregar observación
                        $observacionActual = $reserva->observaciones ?? '';
                        $nuevaObservacion = "Reserva finalizada automáticamente al término del módulo de clase a las {$horaFinModulo}.";
                        
                        $reserva->observaciones = $observacionActual 
                            ? $observacionActual . "\n" . $nuevaObservacion 
                            : $nuevaObservacion;
                        
                        $reserva->save();

                        // Liberar el espacio
                        $espacio = Espacio::where('id_espacio', $reserva->id_espacio)->first();
                        if ($espacio && $espacio->estado === 'Ocupado') {
                            $espacio->estado = 'Disponible';
                            $espacio->save();
                            $this->info("✅ Espacio {$espacio->id_espacio} liberado automáticamente");
                        }

                        DB::commit();
                        
                        $this->info("✅ Reserva {$reserva->id_reserva} finalizada automáticamente al término de clase");
                        $finalizadas++;
                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error("Error al finalizar reserva {$reserva->id_reserva}: " . $e->getMessage());
                        $this->error("❌ Error al finalizar reserva {$reserva->id_reserva}: " . $e->getMessage());
                        $sinFinalizar++;
                    }
                } else {
                    // La clase aún no ha terminado
                    $minutosRestantes = abs($minutosDesdeFinModulo);
                    $this->info("⏱️  Reserva {$reserva->id_reserva} terminará en {$minutosRestantes} minutos (a las {$horaFinModulo})");
                    $sinFinalizar++;
                }

            } catch (\Exception $e) {
                Log::error("Error procesando reserva {$reserva->id_reserva}: " . $e->getMessage());
                $this->error("Error procesando reserva {$reserva->id_reserva}: " . $e->getMessage());
                $sinFinalizar++;
            }
        }

        $this->info("\n=== RESUMEN ===");
        $this->info("Reservas finalizadas: {$finalizadas}");
        $this->info("Reservas sin finalizar: {$sinFinalizar}");
        $this->info("Total procesadas: " . ($finalizadas + $sinFinalizar));

        return 0;
    }
}
