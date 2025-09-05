<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reserva;
use App\Models\Espacio;
use Carbon\Carbon;

class CrearDatosPruebaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:crear-inconsistencias';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crear datos de prueba para testear el sistema de alertas de inconsistencias';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creando datos de prueba para generar inconsistencias...');

        // 1. Crear una reserva activa en un espacio disponible
        $reservaPrueba = new Reserva();
        $reservaPrueba->id_reserva = 'RTEST001';
        $reservaPrueba->hora = '10:00:00';
        $reservaPrueba->fecha_reserva = Carbon::now()->format('Y-m-d');
        $reservaPrueba->id_espacio = 'TH-30'; // Usar un ID que existe
        $reservaPrueba->run_profesor = '19716146'; // RUN que existe en la BD
        $reservaPrueba->tipo_reserva = 'espontanea';
        $reservaPrueba->estado = 'activa';
        $reservaPrueba->save();

        $this->info("✅ Reserva de prueba creada: {$reservaPrueba->id_reserva}");

        // 2. Crear una reserva antigua (de ayer) que sigue activa
        $reservaAntigua = new Reserva();
        $reservaAntigua->id_reserva = 'RTEST002';
        $reservaAntigua->hora = '14:00:00';
        $reservaAntigua->fecha_reserva = Carbon::yesterday()->format('Y-m-d');
        $reservaAntigua->id_espacio = 'TH-LA8'; // Usar otro ID que existe
        $reservaAntigua->run_profesor = '11111111'; // Otro RUN válido
        $reservaAntigua->tipo_reserva = 'directa';
        $reservaAntigua->estado = 'activa';
        $reservaAntigua->save();

        $this->info("✅ Reserva antigua creada: {$reservaAntigua->id_reserva}");

        // 3. Marcar un espacio como ocupado sin reserva
        $espacio = Espacio::where('id_espacio', 'TH-C1')->first();
        if ($espacio) {
            $espacio->estado = 'Ocupado';
            $espacio->save();
            $this->info("✅ Espacio {$espacio->id_espacio} marcado como ocupado sin reserva");
        }

        $this->info('');
        $this->info('🚨 Datos de prueba creados. Ahora ejecuta:');
        $this->info('   php artisan sistema:verificar-estado');
        $this->info('');
        $this->info('Para limpiar los datos de prueba, ejecuta:');
        $this->info('   php artisan test:limpiar-inconsistencias');

        return 0;
    }
}
