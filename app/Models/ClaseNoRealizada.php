<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClaseNoRealizada extends Model
{
    use HasFactory;

    protected $table = 'clases_no_realizadas';

    protected $fillable = [
        'id_asignatura',
        'id_espacio',
        'id_modulo',
        'run_profesor',
        'fecha_clase',
        'periodo',
        'motivo',
        'observaciones',
        'estado',
        'hora_deteccion',
    ];

    protected $casts = [
        'fecha_clase' => 'date',
        'hora_deteccion' => 'datetime',
    ];

    // Relaciones
    public function asignatura()
    {
        return $this->belongsTo(Asignatura::class, 'id_asignatura', 'id_asignatura');
    }

    public function espacio()
    {
        return $this->belongsTo(Espacio::class, 'id_espacio', 'id_espacio');
    }

    public function profesor()
    {
        return $this->belongsTo(Profesor::class, 'run_profesor', 'run_profesor');
    }

    public function modulo()
    {
        return $this->belongsTo(Modulo::class, 'id_modulo', 'id_modulo');
    }

    // Scopes
    public function scopePorProfesor($query, $runProfesor)
    {
        return $query->where('run_profesor', $runProfesor);
    }

    public function scopePorPeriodo($query, $periodo)
    {
        return $query->where('periodo', $periodo);
    }

    public function scopePorFecha($query, $fechaInicio, $fechaFin = null)
    {
        if ($fechaFin) {
            return $query->whereBetween('fecha_clase', [$fechaInicio, $fechaFin]);
        }

        return $query->whereDate('fecha_clase', $fechaInicio);
    }

    public function scopeNoRealizadas($query)
    {
        return $query->where('estado', 'no_realizada');
    }

    // Métodos estáticos
    public static function registrarClaseNoRealizada($datosClase)
    {
        // Verificar si la fecha es un día feriado o sin actividades
        if (\App\Models\DiaFeriado::esFeriado($datosClase['fecha_clase'])) {
            // Si es feriado, registrar como justificado automáticamente
            $registroExistente = static::where('id_asignatura', $datosClase['id_asignatura'])
                ->where('id_espacio', $datosClase['id_espacio'])
                ->where('id_modulo', $datosClase['id_modulo'])
                ->where('fecha_clase', $datosClase['fecha_clase'])
                ->first();

            if (! $registroExistente) {
                $feriado = \App\Models\DiaFeriado::obtenerFeriadoEnFecha($datosClase['fecha_clase']);

                return static::create([
                    'id_asignatura' => $datosClase['id_asignatura'],
                    'id_espacio' => $datosClase['id_espacio'],
                    'id_modulo' => $datosClase['id_modulo'],
                    'fecha_clase' => $datosClase['fecha_clase'],
                    'run_profesor' => $datosClase['run_profesor'],
                    'periodo' => $datosClase['periodo'],
                    'motivo' => $feriado ? $feriado->nombre : 'Día sin actividades',
                    'observaciones' => 'Justificado automáticamente - '.($feriado ? ($feriado->descripcion ?? 'Sin descripción') : 'Periodo sin actividad universitaria'),
                    'estado' => 'justificado',
                    'hora_deteccion' => Carbon::now(),
                ]);
            }

            return $registroExistente;
        }

        // Verificar si ya existe un registro para esta clase HOY
        $registroExistente = static::where('id_asignatura', $datosClase['id_asignatura'])
            ->where('id_espacio', $datosClase['id_espacio'])
            ->where('id_modulo', $datosClase['id_modulo'])
            ->where('fecha_clase', $datosClase['fecha_clase'])
            ->first();

        // Si ya existe, no crear duplicado
        if ($registroExistente) {
            return $registroExistente;
        }

        // Antes de crear, verificar si el profesor SÍ registró entrada
        $tuvoEntrada = \App\Models\Reserva::where('id_espacio', $datosClase['id_espacio'])
            ->where('fecha_reserva', $datosClase['fecha_clase'])
            ->whereNotNull('run_profesor')
            ->whereNotNull('hora') // hora es la hora de entrada en la tabla reservas
            ->exists();

        // Si el profesor SÍ entró, NO registrar como clase no realizada
        if ($tuvoEntrada) {
            return null;
        }

        // Crear el nuevo registro
        return static::create([
            'id_asignatura' => $datosClase['id_asignatura'],
            'id_espacio' => $datosClase['id_espacio'],
            'id_modulo' => $datosClase['id_modulo'],
            'fecha_clase' => $datosClase['fecha_clase'],
            'run_profesor' => $datosClase['run_profesor'],
            'periodo' => $datosClase['periodo'],
            'motivo' => $datosClase['motivo'] ?? 'No se registró ingreso en el primer módulo',
            'observaciones' => $datosClase['observaciones'] ?? null,
            'estado' => 'no_realizada',
            'hora_deteccion' => Carbon::now(),
        ]);
    }

    /**
     * Limpiar registros incorrectos cuando un profesor registra entrada tarde
     * Se llama cuando se crea una reserva de profesor
     * Ahora mueve los registros a profesor_atrasos en lugar de marcar como justificado
     * 
     * Elimina TODOS los registros de clases_no_realizadas para esa clase/fecha/espacio
     * porque si el profesor llegó (aunque tarde), la clase SÍ se realizó.
     */
    public static function limpiarRegistrosIncorrectos($idEspacio, $fechaReserva, $horaEntrada = null, $runProfesor = null)
    {
        // Buscar TODOS los registros de hoy para este espacio (sin importar estado)
        // Si el profesor llegó tarde, la clase SÍ se realizó, así que todos los registros
        // relacionados deben eliminarse de clases_no_realizadas
        $registros = static::where('id_espacio', $idEspacio)
            ->where('fecha_clase', $fechaReserva)
            ->get();

        $contadorMovidos = 0;

        foreach ($registros as $registro) {
            // Solo mover a atrasos si era un registro de "no_realizada" o "justificado" con autocorregido
            $debeRegistrarAtraso = $registro->estado === 'no_realizada' || 
                ($registro->estado === 'justificado' && str_contains($registro->observaciones ?? '', 'Auto-corregido'));

            if ($debeRegistrarAtraso) {
                // Intentar obtener la planificación correspondiente
                $planificacion = \App\Models\Planificacion_Asignatura::where('id_asignatura', $registro->id_asignatura)
                    ->where('id_espacio', $registro->id_espacio)
                    ->where('id_modulo', $registro->id_modulo)
                    ->first();

                // Calcular minutos de atraso si tenemos hora de entrada
                $minutosAtraso = 0;
                $horaProgramada = null;
                
                if ($horaEntrada && $registro->modulo) {
                    $horaProgramada = $registro->modulo->hora_inicio;
                    if ($horaProgramada) {
                        $minutosAtraso = Carbon::parse($horaProgramada)->diffInMinutes(Carbon::parse($horaEntrada));
                    }
                }

                // Verificar que no exista ya un registro de atraso para esta combinación
                $existeAtraso = \Illuminate\Support\Facades\DB::table('profesor_atrasos')
                    ->where('id_asignatura', $registro->id_asignatura)
                    ->where('id_espacio', $registro->id_espacio)
                    ->where('id_modulo', $registro->id_modulo)
                    ->where('fecha', $registro->fecha_clase)
                    ->exists();

                if (!$existeAtraso) {
                    try {
                        \Illuminate\Support\Facades\DB::table('profesor_atrasos')->insert([
                            'id_planificacion' => $planificacion ? $planificacion->id : 0,
                            'id_asignatura' => $registro->id_asignatura,
                            'id_espacio' => $registro->id_espacio,
                            'id_modulo' => $registro->id_modulo,
                            'run_profesor' => $runProfesor ?? $registro->run_profesor,
                            'fecha' => $registro->fecha_clase,
                            'hora_programada' => $horaProgramada,
                            'hora_llegada' => $horaEntrada,
                            'minutos_atraso' => $minutosAtraso,
                            'periodo' => $registro->periodo,
                            'observaciones' => 'Profesor llegó tarde pero realizó la clase',
                            'justificado' => false,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $contadorMovidos++;
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::warning("No se pudo crear registro de atraso: " . $e->getMessage());
                    }
                }
            }

            // Eliminar el registro de clases_no_realizadas (siempre, porque la clase SÍ se realizó)
            try {
                $registro->delete();
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning("No se pudo eliminar registro: " . $e->getMessage());
            }
        }

        return $contadorMovidos;
    }

    public static function obtenerEstadisticasPorProfesor($periodo = null, $fechaInicio = null, $fechaFin = null)
    {
        $query = static::with(['asignatura', 'profesor'])
            ->selectRaw('run_profesor, COUNT(*) as total_ausencias')
            ->groupBy('run_profesor');

        if ($periodo) {
            $query->where('periodo', $periodo);
        }

        if ($fechaInicio && $fechaFin) {
            $query->whereBetween('fecha_clase', [$fechaInicio, $fechaFin]);
        } elseif ($fechaInicio) {
            $query->whereDate('fecha_clase', '>=', $fechaInicio);
        }

        return $query->get();
    }
}
