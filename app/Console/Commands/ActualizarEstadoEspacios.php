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

            // Obtener reservas activas usando el scope unificado 'vigentes'
            // Esto asegura consistencia en la lógica de determinación de estado
            $reservasActivas = Reserva::on('tenant')
                ->vigentes($ahora->toDateString(), $horaActual)
                ->get();

            $this->line("  Reservas activas encontradas: " . $reservasActivas->count());
            $this->line("  Hora actual para filtro: {$horaActual}");

            // Debug: Mostrar detalles de las reservas encontradas
            if ($reservasActivas->count() > 0) {
                foreach ($reservasActivas as $res) {
                    Log::info('Reserva activa encontrada en comando', [
                        'id_reserva' => $res->id_reserva,
                        'id_espacio' => $res->id_espacio,
                        'estado' => $res->estado,
                        'fecha' => $res->fecha_reserva,
                        'hora_inicio' => $res->hora,
                        'hora_fin' => $res->hora_salida,
                        'run_solicitante' => $res->run_solicitante ?? 'N/A',
                        'hora_actual' => $horaActual
                    ]);
                }
            } else {
                // Buscar TODAS las reservas para debug
                $todasReservas = Reserva::on('tenant')->get();
                Log::warning('No se encontraron reservas activas para hoy en horario actual', [
                    'fecha_buscada' => $ahora->toDateString(),
                    'hora_actual' => $horaActual,
                    'total_reservas_en_bd' => $todasReservas->count(),
                    'reservas_por_estado' => $todasReservas->groupBy('estado')->map->count()
                ]);
                
                // Mostrar detalles de las primeras 10 reservas para debugging
                foreach ($todasReservas->take(10) as $res) {
                    Log::warning('Reserva encontrada (debug)', [
                        'id_reserva' => $res->id_reserva,
                        'estado' => $res->estado,
                        'fecha' => $res->fecha_reserva,
                        'hora_inicio' => $res->hora,
                        'hora_fin' => $res->hora_salida,
                        'dentro_de_rango' => ($res->hora <= $horaActual && $res->hora_salida > $horaActual) ? 'SI' : 'NO'
                    ]);
                }
            }

            $espaciosActualizados = 0;
            $espaciosOcupados = 0;
            $espaciosDisponibles = 0;

            foreach ($espacios as $espacio) {
                // Normalizar estado anterior a minúsculas para comparaciones consistentes
                $estadoAnterior = strtolower($espacio->estado);
                
                // Verificar si hay reserva activa para este espacio
                $tieneReservaActiva = $reservasActivas->where('id_espacio', $espacio->id_espacio)->isNotEmpty();
                
                // Verificar si hay clase programada que debería estar en curso
                $planificacionActual = $planificacionesActivas->where('id_espacio', $espacio->id_espacio)
                    ->filter(function($planificacion) use ($horaActual) {
                        return $planificacion->modulo->hora_inicio <= $horaActual && 
                               $planificacion->modulo->hora_termino > $horaActual;
                    })->first();
                
                $tieneClaseActual = $planificacionActual !== null;
                
                // Determinar el estado correcto (siempre en minúsculas)
                $nuevoEstado = 'disponible';
                
                if ($tieneReservaActiva) {
                    $nuevoEstado = 'ocupado';
                    $espaciosOcupados++;
                    
                    Log::info('Espacio marcado como ocupado por reserva activa', [
                        'id_espacio' => $espacio->id_espacio,
                        'nombre_espacio' => $espacio->nombre_espacio,
                        'reservas_encontradas' => $reservasActivas->where('id_espacio', $espacio->id_espacio)->pluck('id_reserva')->toArray()
                    ]);
                } elseif ($tieneClaseActual) {
                    // Si hay clase programada pero no hay reserva activa, 
                    // el espacio debería estar ocupado pero no se ha registrado el ingreso
                    $nuevoEstado = 'disponible';
                    $espaciosDisponibles++;
                    
                    if ($estadoAnterior === 'ocupado') {
                        $this->warn("  Espacio {$espacio->nombre_espacio} marcado como ocupado pero no tiene reserva activa. Cambiando a disponible.");
                    }
                } else {
                    $espaciosDisponibles++;
                    
                    if ($estadoAnterior === 'ocupado') {
                        Log::warning('Espacio ocupado sin reserva detectado', [
                            'espacio' => $espacio->nombre_espacio,
                            'id_espacio' => $espacio->id_espacio,
                            'estado_anterior' => $estadoAnterior,
                            'reservas_activas_total' => $reservasActivas->count(),
                            'reservas_para_este_espacio' => $reservasActivas->where('id_espacio', $espacio->id_espacio)->count()
                        ]);
                        $this->warn("  Espacio {$espacio->nombre_espacio} marcado como ocupado pero no tiene actividad. Cambiando a disponible.");
                    }
                }
                
                // Actualizar el estado si es diferente (comparación case-insensitive)
                if ($estadoAnterior !== $nuevoEstado) {
                    $espacio->estado = $nuevoEstado;
                    $espacio->save();
                    $espaciosActualizados++;
                    
                    Log::info('Estado de espacio actualizado', [
                        'id_espacio' => $espacio->id_espacio,
                        'nombre_espacio' => $espacio->nombre_espacio,
                        'estado_anterior' => $estadoAnterior,
                        'estado_nuevo' => $nuevoEstado
                    ]);
                    
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