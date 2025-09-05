<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Espacio;
use App\Models\Reserva;
use Carbon\Carbon;

class LiberarEspaciosCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espacios:liberar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Libera todos los espacios ocupados y finaliza las reservas activas a las 12 de la noche';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando proceso de liberación de espacios y reservas...');

        // 1. Finalizar todas las reservas activas
        $reservasFinalizadas = Reserva::where('estado', 'activa')
            ->update([
                'estado' => 'finalizada',
                'hora_salida' => Carbon::now()->format('H:i:s'),
                'updated_at' => Carbon::now()
            ]);

        $this->info("Se finalizaron {$reservasFinalizadas} reservas activas.");

        // 2. Cambiar todos los espacios ocupados a disponibles
        $espaciosLiberados = Espacio::where('estado', 'Ocupado')
            ->update([
                'estado' => 'disponible',
                'updated_at' => Carbon::now()
            ]);

        $this->info("Se liberaron {$espaciosLiberados} espacios ocupados.");

        $this->info('Proceso de liberación completado exitosamente.');
        $this->info("Total de operaciones: {$reservasFinalizadas} reservas finalizadas + {$espaciosLiberados} espacios liberados");

        return 0;
    }
}
