<?php

namespace App\Services;

use App\Models\LicenciaProfesor;
use App\Models\RecuperacionClase;
use App\Models\Planificacion_Asignatura;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LicenciaRecuperacionService
{
    /**
     * Genera automáticamente las clases a recuperar para una licencia
     * 
     * @param LicenciaProfesor $licencia
     * @return int Número de clases generadas
     */
    public function generarClasesARecuperar(LicenciaProfesor $licencia)
    {
        Log::info("=== INICIO: Generando clases a recuperar para licencia ID: {$licencia->id_licencia} ===");
        Log::info("Genera recuperación: " . ($licencia->genera_recuperacion ? 'SI' : 'NO'));
        Log::info("Profesor: {$licencia->run_profesor}");
        Log::info("Rango: {$licencia->fecha_inicio} a {$licencia->fecha_fin}");
        
        if (!$licencia->genera_recuperacion) {
            Log::info("Licencia no genera recuperación. Saliendo.");
            return 0;
        }

        $clasesGeneradas = 0;

        try {
            DB::beginTransaction();

            // Obtener todas las planificaciones del profesor en el rango de fechas de la licencia
            $planificaciones = $this->obtenerPlanificacionesEnRango($licencia);
            Log::info("Planificaciones encontradas: " . $planificaciones->count());

            foreach ($planificaciones as $planificacion) {
                // Verificar si ya existe una recuperación para esta clase
                $existeRecuperacion = RecuperacionClase::where('id_licencia', $licencia->id_licencia)
                    ->where('run_profesor', $licencia->run_profesor)
                    ->where('id_asignatura', $planificacion->id_asignatura)
                    ->where('fecha_clase_original', $planificacion->fecha_clase)
                    ->where('id_modulo_original', $planificacion->id_modulo)
                    ->exists();

                if (!$existeRecuperacion) {
                    RecuperacionClase::create([
                        'id_licencia' => $licencia->id_licencia,
                        'run_profesor' => $licencia->run_profesor,
                        'id_asignatura' => $planificacion->id_asignatura,
                        'id_espacio' => $planificacion->id_espacio,
                        'fecha_clase_original' => $planificacion->fecha_clase,
                        'id_modulo_original' => $planificacion->id_modulo,
                        'estado' => 'pendiente',
                        'notificado' => false,
                    ]);

                    $clasesGeneradas++;
                }
            }

            DB::commit();

            Log::info("Generadas {$clasesGeneradas} clases a recuperar para licencia ID: {$licencia->id_licencia}");
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error generando clases a recuperar: " . $e->getMessage());
            throw $e;
        }

        return $clasesGeneradas;
    }

    /**
     * Obtiene las planificaciones de clases del profesor en el rango de la licencia
     * 
     * @param LicenciaProfesor $licencia
     * @return \Illuminate\Support\Collection
     */
    protected function obtenerPlanificacionesEnRango(LicenciaProfesor $licencia)
    {
        $fechaInicio = Carbon::parse($licencia->fecha_inicio);
        $fechaFin = Carbon::parse($licencia->fecha_fin);
        
        Log::info("Obteniendo planificaciones para profesor: {$licencia->run_profesor}, desde {$fechaInicio} hasta {$fechaFin}");
        
        $planificaciones = collect();

        // Obtener todos los horarios del profesor
        $horarios = DB::table('horarios')
            ->where('run_profesor', $licencia->run_profesor)
            ->get();

        Log::info("Horarios encontrados: " . $horarios->count());

        foreach ($horarios as $horario) {
            // Para cada horario, obtener sus planificaciones
            $planificacionesHorario = DB::table('planificacion_asignaturas as pa')
                ->join('modulos as m', 'pa.id_modulo', '=', 'm.id_modulo')
                ->where('pa.id_horario', $horario->id_horario)
                ->select(
                    'pa.id_asignatura',
                    'pa.id_modulo',
                    'pa.id_espacio',
                    'm.dia'
                )
                ->get();

            Log::info("Planificaciones en horario {$horario->id_horario}: " . $planificacionesHorario->count());

            // Para cada planificación, generar las fechas específicas en el rango
            foreach ($planificacionesHorario as $plan) {
                $fechasClases = $this->generarFechasClases(
                    $plan->dia, 
                    $fechaInicio, 
                    $fechaFin
                );

                Log::info("Fechas generadas para día '{$plan->dia}': " . count($fechasClases));

                foreach ($fechasClases as $fechaClase) {
                    $planificaciones->push((object)[
                        'id_asignatura' => $plan->id_asignatura,
                        'id_modulo' => $plan->id_modulo,
                        'id_espacio' => $plan->id_espacio,
                        'fecha_clase' => $fechaClase,
                        'dia_semana' => $plan->dia,
                    ]);
                }
            }
        }

        Log::info("Total de clases a recuperar: " . $planificaciones->count());

        return $planificaciones;
    }

    /**
     * Genera las fechas de clases para un día de la semana específico en un rango
     * 
     * @param string $diaSemana Puede venir como "lunes", "Lunes", "L", etc.
     * @param Carbon $fechaInicio
     * @param Carbon $fechaFin
     * @return array
     */
    protected function generarFechasClases($diaSemana, $fechaInicio, $fechaFin)
    {
        $fechas = [];
        
        // Normalizar el día (convertir a minúsculas y sin acentos)
        $diaNormalizado = strtolower(trim($diaSemana));
        
        // Mapeo de días en español a números (1 = Lunes, 7 = Domingo)
        $diasMap = [
            'lunes' => 1,
            'l' => 1,
            'martes' => 2,
            'ma' => 2,
            'miércoles' => 3,
            'miercoles' => 3,
            'mi' => 3,
            'x' => 3,
            'jueves' => 4,
            'ju' => 4,
            'j' => 4,
            'viernes' => 5,
            'v' => 5,
            'vi' => 5,
            'sábado' => 6,
            'sabado' => 6,
            'sa' => 6,
            's' => 6,
            'domingo' => 7,
            'do' => 7,
            'd' => 7,
        ];

        $numeroDia = $diasMap[$diaNormalizado] ?? null;
        
        if (!$numeroDia) {
            Log::warning("Día de semana no reconocido: {$diaSemana}");
            return $fechas;
        }

        $fechaActual = $fechaInicio->copy();

        // Avanzar hasta el primer día de la semana que coincida
        while ($fechaActual->dayOfWeekIso != $numeroDia && $fechaActual <= $fechaFin) {
            $fechaActual->addDay();
        }

        // Agregar todas las fechas que coincidan con ese día de la semana
        while ($fechaActual <= $fechaFin) {
            $fechas[] = $fechaActual->format('Y-m-d');
            $fechaActual->addWeek();
        }

        return $fechas;
    }

    /**
     * Elimina las clases a recuperar pendientes de una licencia
     * (No elimina las que ya están en proceso o reagendadas)
     * 
     * @param LicenciaProfesor $licencia
     * @return int Número de clases eliminadas
     */
    public function eliminarClasesARecuperar(LicenciaProfesor $licencia)
    {
        return RecuperacionClase::where('id_licencia', $licencia->id_licencia)
            ->where('estado', 'pendiente')
            ->delete();
    }

    /**
     * Regenera las clases a recuperar para una licencia
     * (Útil cuando se edita una licencia)
     * 
     * @param LicenciaProfesor $licencia
     * @return int Número de clases generadas
     */
    public function regenerarClasesARecuperar(LicenciaProfesor $licencia)
    {
        // Eliminar las clases existentes solo si están en estado 'pendiente'
        RecuperacionClase::where('id_licencia', $licencia->id_licencia)
            ->where('estado', 'pendiente')
            ->delete();

        // Generar nuevamente
        return $this->generarClasesARecuperar($licencia);
    }
}
