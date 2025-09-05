<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reserva;
use App\Models\Espacio;

class LimpiarDatosPruebaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:limpiar-inconsistencias';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpiar datos de prueba creados para testear inconsistencias';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Limpiando datos de prueba...');

        // Eliminar reservas de prueba
        $reservasEliminadas = Reserva::whereIn('id_reserva', ['RTEST001', 'RTEST002'])->delete();
        $this->info("✅ {$reservasEliminadas} reservas de prueba eliminadas");

        // Restaurar espacios a disponible
        $espaciosRestaurados = Espacio::whereIn('id_espacio', ['TH-C1', 'TH-30', 'TH-LA8'])
            ->update(['estado' => 'disponible']);
        $this->info("✅ {$espaciosRestaurados} espacios restaurados a disponible");

        $this->info('🧹 Datos de prueba limpiados correctamente');

        return 0;
    }
}
