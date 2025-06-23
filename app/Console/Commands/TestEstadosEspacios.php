<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Espacio;
use App\Models\Planificacion_Asignatura;
use App\Models\Reserva;
use App\Models\Modulo;
use Carbon\Carbon;

class TestEstadosEspacios extends Command
{
    protected $signature = 'test:estados-espacios {--time= : Hora específica para probar (formato: HH:mm)}';
    protected $description = 'Prueba la funcionalidad de estados de espacios';

    public function handle()
    {
        $horaEspecifica = $this->option('time');
        
        if ($horaEspecifica) {
            $horaActual = Carbon::createFromFormat('H:i', $horaEspecifica);
            $this->info("Probando con hora específica: {$horaEspecifica}");
        } else {
            $horaActual = Carbon::now();
            $this->info("Probando con hora actual: " . $horaActual->format('H:i:s'));
        }
        
        $diaActual = strtolower($horaActual->locale('es')->isoFormat('dddd'));
        $horaActualStr = $horaActual->format('H:i:s');
        
        $this->info("Día actual: {$diaActual}");
        
        // Determinar el período actual
        $mesActual = date('n');
        $anioActual = date('Y');
        $semestre = ($mesActual >= 1 && $mesActual <= 7) ? 1 : 2;
        $periodo = $anioActual . '-' . $semestre;
        
        $this->info("Período: {$periodo}");
        
        // Obtener todos los espacios
        $espacios = Espacio::all();
        $this->info("Total de espacios: " . $espacios->count());
        
        // Obtener módulos del día actual
        $modulosDelDia = Modulo::where('dia', $diaActual)->orderBy('hora_inicio')->get();
        $this->info("Módulos del día: " . $modulosDelDia->count());
        
        // Mostrar módulos y marcar el actual
        $moduloActual = null;
        foreach ($modulosDelDia as $modulo) {
            $esActual = ($modulo->hora_inicio <= $horaActualStr && $modulo->hora_termino > $horaActualStr);
            $marca = $esActual ? " [ACTUAL]" : "";
            $this->line("  - {$modulo->id_modulo}: {$modulo->hora_inicio} - {$modulo->hora_termino}{$marca}");
            
            if ($esActual) {
                $moduloActual = $modulo;
            }
        }
        
        // Obtener planificaciones activas
        $planificacionesActivas = Planificacion_Asignatura::with(['modulo', 'espacio', 'asignatura.user'])
            ->whereHas('horario', function ($query) use ($periodo) {
                $query->where('periodo', $periodo);
            })
            ->whereHas('modulo', function ($query) use ($diaActual) {
                $query->where('dia', $diaActual);
            })
            ->get();
            
        $this->info("Planificaciones activas: " . $planificacionesActivas->count());
        
        // Obtener reservas activas
        $reservasActivas = Reserva::where('fecha_reserva', $horaActual->toDateString())
            ->where('estado', 'activa')
            ->get();
            
        $this->info("Reservas activas: " . $reservasActivas->count());
        
        // Probar la lógica de estados
        $this->info("\n=== ESTADOS DE ESPACIOS ===");
        
        $estadosCount = [
            'Ocupado' => 0,
            'Disponible' => 0,
            'Reservado' => 0,
            'Proximo' => 0
        ];
        
        foreach ($espacios as $espacio) {
            $estado = $espacio->estado;
            
            // Verificar reserva activa
            $tieneReservaActiva = $reservasActivas->where('id_espacio', $espacio->id_espacio)->isNotEmpty();
            
            // Verificar clase actual
            $planificacionActual = $planificacionesActivas->where('id_espacio', $espacio->id_espacio)
                ->filter(function($planificacion) use ($horaActualStr) {
                    return $planificacion->modulo->hora_inicio <= $horaActualStr && 
                           $planificacion->modulo->hora_termino > $horaActualStr;
                })->first();
            
            $tieneClaseActual = $planificacionActual !== null;
            
            // Verificar clase próxima
            $tieneClaseProxima = false;
            $planificacionesDelEspacio = $planificacionesActivas->where('id_espacio', $espacio->id_espacio);
            
            foreach ($planificacionesDelEspacio as $planificacion) {
                $horaInicioModulo = $planificacion->modulo->hora_inicio;
                $horaActualCarbon = Carbon::createFromFormat('H:i:s', $horaActualStr);
                $horaInicioCarbon = Carbon::createFromFormat('H:i:s', $horaInicioModulo);
                
                if ($horaInicioCarbon->gt($horaActualCarbon) && 
                    $horaInicioCarbon->diffInMinutes($horaActualCarbon) <= 10 &&
                    !$tieneClaseActual) {
                    $tieneClaseProxima = true;
                    break;
                }
            }
            
            // Determinar estado final según la nueva lógica
            if ($tieneReservaActiva) {
                $estado = 'Reservado';
            } elseif ($espacio->estado === 'Ocupado') {
                // Si el estado en la tabla es "Ocupado", mostrar rojo
                $estado = 'Ocupado';
            } elseif ($tieneClaseActual && $espacio->estado !== 'Ocupado') {
                // Si hay clase en curso pero el estado no es "Ocupado", mostrar naranja
                $estado = 'Reservado'; // Usamos 'Reservado' para el color naranja
            } elseif ($tieneClaseProxima) {
                // Si hay clase próxima (entre módulos), mostrar azul
                $estado = 'Proximo';
            } elseif ($espacio->estado === 'Disponible') {
                // Si el estado es "Disponible" y no hay horario asociado, mostrar verde
                $estado = 'Disponible';
            } else {
                // Estado por defecto
                $estado = $espacio->estado;
            }
            
            $estadosCount[$estado]++;
            
            $this->line("Espacio {$espacio->id_espacio}: {$estado}");
            
            if ($tieneClaseActual) {
                $this->line("  - Tiene clase actual");
                // Mostrar información de la clase actual
                if ($planificacionActual) {
                    $this->line("    Asignatura: " . ($planificacionActual->asignatura->nombre_asignatura ?? 'No especificada'));
                    $this->line("    Profesor: " . ($planificacionActual->asignatura->user->name ?? 'No especificado'));
                    $this->line("    Módulo: " . (explode('.', $planificacionActual->modulo->id_modulo)[1] ?? 'No especificado'));
                    $this->line("    Horario: " . substr($planificacionActual->modulo->hora_inicio, 0, 5) . " - " . substr($planificacionActual->modulo->hora_termino, 0, 5));
                }
            }
            if ($tieneClaseProxima) {
                $this->line("  - Tiene clase próxima");
            }
            if ($tieneReservaActiva) {
                $this->line("  - Tiene reserva activa");
            }
        }
        
        $this->info("\n=== RESUMEN DE ESTADOS ===");
        foreach ($estadosCount as $estado => $count) {
            $this->line("{$estado}: {$count} espacios");
        }
        
        if ($moduloActual) {
            $this->info("\nMódulo actual: {$moduloActual->id_modulo} ({$moduloActual->hora_inicio} - {$moduloActual->hora_termino})");
        }
        
        // Verificar notificaciones de devolución de llaves
        $this->verificarNotificacionesDevolucionLlaves();
        
        $this->info("\nPrueba completada.");
    }
    
    private function verificarNotificacionesDevolucionLlaves()
    {
        $this->info("\n=== VERIFICACIÓN DE NOTIFICACIONES DE DEVOLUCIÓN DE LLAVES ===");
        
        $now = Carbon::now();
        $timeLimit = $now->copy()->addMinutes(10);
        
        $this->info("Verificando espacios que terminan entre: " . $now->format('H:i') . " y " . $timeLimit->format('H:i'));
        
        // Obtener planificaciones que terminan en los próximos 10 minutos
        $planificaciones = Planificacion_Asignatura::with(['modulo', 'espacio', 'asignatura.user'])
            ->whereHas('modulo', function ($query) use ($now, $timeLimit) {
                $query->where('dia', strtolower($now->locale('es')->isoFormat('dddd')))
                      ->whereTime('hora_termino', '>', $now->format('H:i:s'))
                      ->whereTime('hora_termino', '<=', $timeLimit->format('H:i:s'));
            })
            ->get();
            
        $this->info("Planificaciones que terminan en los próximos 10 minutos: " . $planificaciones->count());
        
        if ($planificaciones->isEmpty()) {
            $this->info("No hay planificaciones que terminen en los próximos 10 minutos.");
            return;
        }
        
        $notificacionesGeneradas = 0;
        $notificacionesOmitidas = 0;
        
        foreach ($planificaciones as $plan) {
            $espacio = $plan->espacio;
            $profesor = $plan->asignatura->user->name ?? 'Profesor no asignado';
            $horaTermino = Carbon::parse($plan->modulo->hora_termino)->format('H:i');
            
            $this->line("\nPlanificación encontrada:");
            $this->line("  - Espacio: " . ($espacio->nombre_espacio ?? 'No asignado'));
            $this->line("  - Profesor: {$profesor}");
            $this->line("  - Hora de término: {$horaTermino}");
            $this->line("  - Estado del espacio: " . ($espacio->estado ?? 'No definido'));
            
            // Verificar si el espacio está realmente ocupado
            if ($espacio->estado === 'Ocupado') {
                $this->line("  ✓ NOTIFICACIÓN GENERADA: El espacio está ocupado y requiere devolución de llaves");
                $notificacionesGeneradas++;
            } else {
                $this->line("  ✗ NOTIFICACIÓN OMITIDA: El espacio no está ocupado (estado: {$espacio->estado})");
                $notificacionesOmitidas++;
            }
        }
        
        $this->info("\n=== RESUMEN DE NOTIFICACIONES ===");
        $this->info("Notificaciones generadas: {$notificacionesGeneradas}");
        $this->info("Notificaciones omitidas: {$notificacionesOmitidas}");
        $this->info("Total de planificaciones verificadas: " . $planificaciones->count());
        
        if ($notificacionesGeneradas > 0) {
            $this->warn("¡Hay {$notificacionesGeneradas} profesor(es) que deben devolver llaves!");
        } else {
            $this->info("No hay notificaciones de devolución de llaves pendientes.");
        }
    }
} 