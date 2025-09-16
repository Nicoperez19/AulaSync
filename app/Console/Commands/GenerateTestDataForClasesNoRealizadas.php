<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ClaseNoRealizada;
use App\Models\Profesor;
use App\Models\Asignatura;
use App\Models\Espacio;
use Carbon\Carbon;

class GenerateTestDataForClasesNoRealizadas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:generate-clases-no-realizadas {--count=5}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera datos de prueba para la tabla clases_no_realizadas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = $this->option('count');
        
        $this->info("Generando {$count} registros de prueba...");
        
        // Obtener algunos profesores, asignaturas y espacios existentes
        $profesores = \App\Models\Profesor::take(5)->get();
        
        $asignaturas = Asignatura::take(5)->get();
        $espacios = Espacio::take(5)->get();
        
        if ($profesores->isEmpty() || $asignaturas->isEmpty() || $espacios->isEmpty()) {
            $this->error('No hay suficientes datos base (profesores, asignaturas o espacios)');
            return 1;
        }
        
        for ($i = 0; $i < $count; $i++) {
            $profesor = $profesores->random();
            $asignatura = $asignaturas->random();
            $espacio = $espacios->random();
            
            ClaseNoRealizada::create([
                'id_asignatura' => $asignatura->id_asignatura,
                'id_espacio' => $espacio->id_espacio,
                'id_modulo' => 'LU.' . rand(1, 6), // Módulo de lunes
                'run_profesor' => $profesor->run_profesor,
                'fecha_clase' => Carbon::now()->subDays(rand(1, 30)),
                'periodo' => '2024-2',
                'motivo' => 'Datos de prueba - No se registró ingreso en el primer módulo',
                'observaciones' => $i % 2 == 0 ? 'Observación de prueba ' . ($i + 1) : null,
                'estado' => ['pendiente', 'justificado', 'confirmado'][rand(0, 2)],
                'hora_deteccion' => Carbon::now()->subDays(rand(1, 30))->addMinutes(rand(10, 60)),
            ]);
        }
        
        $this->info("✅ Se generaron {$count} registros de prueba exitosamente");
        
        return 0;
    }
}
