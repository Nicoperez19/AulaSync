<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reserva;
use App\Models\Planificacion_Asignatura;
use App\Models\Modulo;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class FinalizarReservasNoDevueltas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservas:finalizar-no-devueltas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Finaliza automáticamente las reservas de profesores que no devolvieron las llaves una hora después de terminado el módulo';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando búsqueda de reservas no devueltas después de 1 hora del módulo...');

        // Obtener todas las reservas activas de profesores
        $reservasActivas = Reserva::where('estado', 'activa')
            ->whereNotNull('run_profesor')
            ->whereNull('hora_salida')
            ->get();

        $reservasFinalizadas = 0;

        foreach ($reservasActivas as $reserva) {
            // Obtener la planificación asociada si existe
            $planificacion = Planificacion_Asignatura::with('modulo')
                ->where('id_espacio', $reserva->id_espacio)
                ->whereHas('modulo', function ($query) use ($reserva) {
                    $query->where('dia', $this->obtenerDiaSemana($reserva->fecha_reserva));
                })
                ->first();

            if (!$planificacion || !$planificacion->modulo) {
                continue; // No hay módulo asociado
            }

            $modulo = $planificacion->modulo;

            // Calcular hora de termino del módulo
            $fechaModulo = Carbon::parse($reserva->fecha_reserva);
            $horaTerminoModulo = Carbon::parse($reserva->fecha_reserva . ' ' . $modulo->hora_termino);

            // Sumar 1 hora a la hora de término del módulo
            $horaLimiteDevolucion = $horaTerminoModulo->copy()->addHours(1);

            // Verificar si ya pasó la hora límite
            $ahora = Carbon::now();
            if ($ahora->gte($horaLimiteDevolucion)) {
                // La hora límite ya pasó - finalizar la reserva
                $reserva->estado = 'finalizada';
                $reserva->hora_salida = $horaLimiteDevolucion->format('H:i:s');
                $reserva->observaciones = trim(
                    ($reserva->observaciones ?? '') . "\n" .
                    "Reserva finalizada automáticamente después de 1 hora del módulo (Hora límite: " . $horaLimiteDevolucion->format('H:i:s') . "). El profesor no devolvió la llave."
                );
                $reserva->save();

                $reservasFinalizadas++;

                Log::info("Reserva finalizada automáticamente por no devolución de llave", [
                    'id_reserva' => $reserva->id_reserva,
                    'run_profesor' => $reserva->run_profesor,
                    'id_espacio' => $reserva->id_espacio,
                    'fecha_reserva' => $reserva->fecha_reserva,
                    'hora_termino_modulo' => $horaTerminoModulo->format('H:i:s'),
                    'hora_limite_devolucion' => $horaLimiteDevolucion->format('H:i:s'),
                    'ahora' => $ahora->format('Y-m-d H:i:s')
                ]);
            }
        }

        $this->info("Se finalizaron {$reservasFinalizadas} reservas por no devolución de llaves.");

        return 0;
    }

    /**
     * Obtener el día de la semana en español a partir de una fecha
     */
    private function obtenerDiaSemana($fecha): string
    {
        $carbon = Carbon::parse($fecha);
        $dias = ['Sunday' => 'Domingo', 'Monday' => 'Lunes', 'Tuesday' => 'Martes', 'Wednesday' => 'Miércoles', 'Thursday' => 'Jueves', 'Friday' => 'Viernes', 'Saturday' => 'Sábado'];
        $diaSemanaIngles = $carbon->format('l');
        return $dias[$diaSemanaIngles] ?? 'Desconocido';
    }
}
