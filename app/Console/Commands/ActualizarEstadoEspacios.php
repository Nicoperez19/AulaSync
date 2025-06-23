<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Espacio;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ActualizarEstadoEspacios extends Command
{
    protected $signature = 'espacios:actualizar-estado';
    protected $description = 'Actualiza el estado de los espacios basado en las reservas y clases programadas';

    public function handle()
    {
        $ahora = Carbon::now();
        $diaActual = strtolower($ahora->locale('es')->isoFormat('dddd'));
        $horaActual = $ahora->format('H:i:s');

        // Obtener todos los espacios que están marcados como ocupados
        $espaciosOcupados = Espacio::where('estado', 'ocupado')->get();

        foreach ($espaciosOcupados as $espacio) {
            // Verificar si hay reservas activas
            $reservaActiva = Reserva::where('espacio_id', $espacio->id_espacio)
                ->where('fecha', $ahora->toDateString())
                ->where('hora_termino', '>=', $horaActual)
                ->first();

            // Verificar si hay clases programadas
            $claseActiva = DB::table('horarios')
                ->where('espacio_id', $espacio->id_espacio)
                ->where('dia', $diaActual)
                ->where('hora_termino', '>=', $horaActual)
                ->first();

            // Si no hay reservas ni clases activas, marcar el espacio como disponible
            if (!$reservaActiva && !$claseActiva) {
                $espacio->estado = 'disponible';
                $espacio->save();
                $this->info("Espacio {$espacio->nombre_espacio} marcado como disponible");
            }
        }

        $this->info('Proceso de actualización de estados completado');
    }
} 