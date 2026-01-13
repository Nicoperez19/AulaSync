<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Espacio;
use App\Models\Reserva;
use App\Models\Planificacion_Asignatura;
use App\Models\Tenant;
use App\Helpers\SemesterHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class ActualizarEstadoEspacios extends Command
{
    protected $signature = 'espacios:actualizar-estado';
    protected $description = 'Actualiza el estado de los espacios basado en las reservas y clases programadas';

    public function handle()
    {
        $this->info('Iniciando actualización de estados de espacios...');

        // Obtener todos los tenants
        $tenants = Tenant::all();

        if ($tenants->isEmpty()) {
            $this->warn('No se encontraron tenants configurados.');
            return 0;
        }

        foreach ($tenants as $tenant) {
            $this->processTenant($tenant);
        }

        return 0;
    }

    protected function processTenant(Tenant $tenant)
    {
        $this->info("\nProcesando tenant: {$tenant->name} ({$tenant->domain})");

        try {
            // Configurar conexión de tenant
            Config::set('database.connections.tenant.database', $tenant->database);
            DB::purge('tenant');

            $ahora = Carbon::now();
            $diaActual = strtolower($ahora->locale('es')->isoFormat('dddd'));
            $horaActual = $ahora->format('H:i:s');

            // Determinar el período actual usando el helper
            $anioActual = SemesterHelper::getCurrentAcademicYear();
            $semestre = SemesterHelper::getCurrentSemester();
            $periodo = SemesterHelper::getCurrentPeriod();

            $this->line("  Hora actual: {$horaActual}");
            $this->line("  Día actual: {$diaActual}");
            $this->line("  Período: {$periodo}");

            // Obtener todos los espacios
            $espacios = Espacio::on('tenant')->get();
            $this->line("  Total de espacios a verificar: " . $espacios->count());

            // Obtener planificaciones activas para el período actual
            $planificacionesActivas = Planificacion_Asignatura::on('tenant')
                ->with(['modulo', 'espacio', 'asignatura.profesor'])
                ->whereHas('horario', function ($query) use ($periodo) {
                    $query->where('periodo', $periodo);
                })
                ->whereHas('modulo', function ($query) use ($diaActual) {
                    $query->where('dia', $diaActual);
                })
                ->get();

            $this->line("  Planificaciones activas encontradas: " . $planificacionesActivas->count());

            // Obtener reservas activas para hoy
            $reservasActivas = Reserva::on('tenant')
                ->where('fecha_reserva', $ahora->toDateString())
                ->where('estado', 'activa')
                ->get();

            $this->line("  Reservas activas encontradas: " . $reservasActivas->count());

            $espaciosActualizados = 0;
            $espaciosOcupados = 0;
            $espaciosDisponibles = 0;

            foreach ($espacios as $espacio) {
                $estadoAnterior = $espacio->estado;
                
                // Verificar si hay reserva activa para este espacio
                $tieneReservaActiva = $reservasActivas->where('id_espacio', $espacio->id_espacio)->isNotEmpty();
                
                // Verificar si hay clase programada que debería estar en curso
                $planificacionActual = $planificacionesActivas->where('id_espacio', $espacio->id_espacio)
                    ->filter(function($planificacion) use ($horaActual) {
                        return $planificacion->modulo->hora_inicio <= $horaActual && 
                               $planificacion->modulo->hora_termino > $horaActual;
                    })->first();
                
                $tieneClaseActual = $planificacionActual !== null;
                
                // Determinar el estado correcto
                $nuevoEstado = 'Disponible';
                
                if ($tieneReservaActiva) {
                    $nuevoEstado = 'Ocupado';
                    $espaciosOcupados++;
                } elseif ($tieneClaseActual) {
                    // Si hay clase programada pero no hay reserva activa, 
                    // el espacio debería estar ocupado pero no se ha registrado el ingreso
                    $nuevoEstado = 'Disponible';
                    $espaciosDisponibles++;
                    
                    if ($estadoAnterior === 'Ocupado') {
                        $this->warn("  Espacio {$espacio->nombre_espacio} marcado como ocupado pero no tiene reserva activa. Cambiando a disponible.");
                    }
                } else {
                    $espaciosDisponibles++;
                    
                    if ($estadoAnterior === 'Ocupado') {
                        $this->warn("  Espacio {$espacio->nombre_espacio} marcado como ocupado pero no tiene actividad. Cambiando a disponible.");
                    }
                }
                
                // Actualizar el estado si es diferente
                if ($estadoAnterior !== $nuevoEstado) {
                    $espacio->estado = $nuevoEstado;
                    $espacio->save();
                    $espaciosActualizados++;
                    
                    $this->line("  Espacio {$espacio->nombre_espacio}: {$estadoAnterior} → {$nuevoEstado}");
                }
            }

            $this->newLine();
            $this->info("  === RESUMEN DE ACTUALIZACIÓN ===");
            $this->info("  Espacios actualizados: {$espaciosActualizados}");
            $this->info("  Espacios ocupados: {$espaciosOcupados}");
            $this->info("  Espacios disponibles: {$espaciosDisponibles}");
            $this->info("  Total de espacios: " . $espacios->count());
        } catch (\Exception $e) {
            $this->error("  Error procesando tenant {$tenant->name}: " . $e->getMessage());
            Log::error("Error en ActualizarEstadoEspacios para tenant {$tenant->name}", [
                'tenant' => $tenant->domain,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
} 