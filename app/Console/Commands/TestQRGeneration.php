<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Planificacion_Asignatura;
use App\Models\Espacio;
use App\Models\Reserva;
use Carbon\Carbon;

class TestQRGeneration extends Command
{
    protected $signature = 'test:notificaciones-devolucion-llaves';
    protected $description = 'Prueba las notificaciones de devolución de llaves y muestra información detallada';

    public function handle()
    {
        $this->info("=== PRUEBA DE NOTIFICACIONES DE DEVOLUCIÓN DE LLAVES ===");
        $this->info("Fecha y hora actual: " . Carbon::now()->format('d/m/Y H:i:s'));
        
        // Simular diferentes escenarios
        $this->probarEscenarioActual();
        $this->probarEscenarioConEspaciosOcupados();
        $this->probarEscenarioConEspaciosDisponibles();
        
        $this->info("\n=== PRUEBA COMPLETADA ===");
    }
    
    private function probarEscenarioActual()
    {
        $this->info("\n--- ESCENARIO ACTUAL ---");
        
        $now = Carbon::now();
        $timeLimit = $now->copy()->addMinutes(10);
        
        $this->info("Verificando planificaciones que terminan entre: " . $now->format('H:i') . " y " . $timeLimit->format('H:i'));
        
        // Obtener planificaciones que terminan en los próximos 10 minutos
        $planificaciones = Planificacion_Asignatura::with(['modulo', 'espacio', 'asignatura.user'])
            ->whereHas('modulo', function ($query) use ($now, $timeLimit) {
                $query->where('dia', strtolower($now->locale('es')->isoFormat('dddd')))
                      ->whereTime('hora_termino', '>', $now->format('H:i:s'))
                      ->whereTime('hora_termino', '<=', $timeLimit->format('H:i:s'));
            })
            ->get();
            
        $this->info("Planificaciones encontradas: " . $planificaciones->count());
        
        if ($planificaciones->isEmpty()) {
            $this->info("No hay planificaciones que terminen en los próximos 10 minutos.");
            return;
        }
        
        foreach ($planificaciones as $plan) {
            $espacio = $plan->espacio;
            $profesor = $plan->asignatura->user->name ?? 'Profesor no asignado';
            $horaTermino = Carbon::parse($plan->modulo->hora_termino)->format('H:i');
            
            $this->line("\nPlanificación:");
            $this->line("  - Espacio: " . ($espacio->nombre_espacio ?? 'No asignado'));
            $this->line("  - Profesor: {$profesor}");
            $this->line("  - Hora de término: {$horaTermino}");
            $this->line("  - Estado del espacio: " . ($espacio->estado ?? 'No definido'));
            
            // Verificar si el espacio está realmente ocupado
            if ($espacio->estado === 'Ocupado') {
                $this->line("  ✓ NOTIFICACIÓN: Se generará notificación para devolución de llaves");
            } else {
                $this->line("  ✗ SIN NOTIFICACIÓN: El espacio no está ocupado (estado: {$espacio->estado})");
            }
        }
    }
    
    private function probarEscenarioConEspaciosOcupados()
    {
        $this->info("\n--- ESCENARIO: ESPACIOS OCUPADOS ---");
        
        // Buscar espacios que están marcados como ocupados
        $espaciosOcupados = Espacio::where('estado', 'Ocupado')->get();
        
        $this->info("Espacios marcados como ocupados: " . $espaciosOcupados->count());
        
        if ($espaciosOcupados->isEmpty()) {
            $this->info("No hay espacios marcados como ocupados.");
            return;
        }
        
        foreach ($espaciosOcupados as $espacio) {
            $this->line("\nEspacio ocupado: {$espacio->nombre_espacio}");
            
            // Verificar si tiene reserva activa
            $reservaActiva = Reserva::where('id_espacio', $espacio->id_espacio)
                ->where('fecha_reserva', Carbon::today())
                ->where('estado', 'activa')
                ->first();
                
            if ($reservaActiva) {
                $this->line("  - Tiene reserva activa");
                $this->line("  - Usuario: " . ($reservaActiva->user->name ?? 'No encontrado'));
                $this->line("  - Hora de entrada: " . $reservaActiva->hora);
                $this->line("  - Sin hora de salida: " . ($reservaActiva->hora_salida ? 'No' : 'Sí'));
            } else {
                $this->line("  - No tiene reserva activa (posible inconsistencia)");
            }
            
            // Verificar planificaciones para este espacio
            $planificaciones = Planificacion_Asignatura::with(['modulo', 'asignatura.user'])
                ->where('id_espacio', $espacio->id_espacio)
                ->whereHas('modulo', function ($query) {
                    $query->where('dia', strtolower(Carbon::now()->locale('es')->isoFormat('dddd')));
                })
                ->get();
                
            if ($planificaciones->isNotEmpty()) {
                $this->line("  - Tiene planificaciones para hoy:");
                foreach ($planificaciones as $plan) {
                    $this->line("    * " . ($plan->asignatura->nombre_asignatura ?? 'Sin asignatura') . 
                               " - " . ($plan->asignatura->user->name ?? 'Sin profesor') .
                               " (" . $plan->modulo->hora_inicio . " - " . $plan->modulo->hora_termino . ")");
                }
            } else {
                $this->line("  - No tiene planificaciones para hoy");
            }
        }
    }
    
    private function probarEscenarioConEspaciosDisponibles()
    {
        $this->info("\n--- ESCENARIO: ESPACIOS DISPONIBLES ---");
        
        // Buscar espacios que están marcados como disponibles
        $espaciosDisponibles = Espacio::where('estado', 'Disponible')->get();
        
        $this->info("Espacios marcados como disponibles: " . $espaciosDisponibles->count());
        
        // Verificar si alguno debería estar ocupado según la planificación
        $now = Carbon::now();
        $diaActual = strtolower($now->locale('es')->isoFormat('dddd'));
        $horaActual = $now->format('H:i:s');
        
        $planificacionesActivas = Planificacion_Asignatura::with(['modulo', 'espacio', 'asignatura.user'])
            ->whereHas('modulo', function ($query) use ($diaActual, $horaActual) {
                $query->where('dia', $diaActual)
                      ->where('hora_inicio', '<=', $horaActual)
                      ->where('hora_termino', '>', $horaActual);
            })
            ->whereHas('espacio', function ($query) {
                $query->where('estado', 'Disponible');
            })
            ->get();
            
        $this->info("Espacios disponibles que deberían estar ocupados: " . $planificacionesActivas->count());
        
        if ($planificacionesActivas->isNotEmpty()) {
            $this->warn("¡ATENCIÓN! Los siguientes espacios están disponibles pero deberían estar ocupados:");
            
            foreach ($planificacionesActivas as $plan) {
                $espacio = $plan->espacio;
                $profesor = $plan->asignatura->user->name ?? 'Profesor no asignado';
                
                $this->line("  - {$espacio->nombre_espacio}: {$profesor} (módulo actual)");
            }
        } else {
            $this->info("Todos los espacios disponibles están correctamente marcados.");
        }
    }
} 