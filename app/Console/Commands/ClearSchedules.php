<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Horario;
use App\Models\Planificacion_Asignatura;
use Illuminate\Support\Facades\Log;

class ClearSchedules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedules:clear {periodo : El periodo a limpiar (ej: 2026-1)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Elimina todas las planificaciones y horarios de un periodo específico';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $periodo = $this->argument('periodo');

        $this->warn("¡ADVERTENCIA! Esta acción eliminará permanentemente todos los horarios y planificaciones del periodo: {$periodo}");
        
        if (!$this->confirm("¿Estás seguro de que deseas continuar?")) {
            $this->info("Operación cancelada.");
            return;
        }

        try {
            $horariosIds = Horario::where('periodo', $periodo)->pluck('id_horario');
            $planificacionesEliminadas = Planificacion_Asignatura::whereIn('id_horario', $horariosIds)->delete();
            $horariosEliminados = Horario::where('periodo', $periodo)->delete();

            $this->info("✓ Eliminadas {$planificacionesEliminadas} planificaciones.");
            $this->info("✓ Eliminados {$horariosEliminados} horarios.");
            
            Log::info("Limpieza manual de horarios (Command) para el periodo {$periodo}: {$planificacionesEliminadas} planificaciones y {$horariosEliminados} horarios eliminados.");
        } catch (\Exception $e) {
            $this->error("Error durante la limpieza: " . $e->getMessage());
            Log::error("Error en comando schedules:clear: " . $e->getMessage());
        }
    }
}
