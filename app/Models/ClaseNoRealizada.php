<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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
        'hora_deteccion'
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
            ->whereNotNull('hora_entrada')
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
     */
    public static function limpiarRegistrosIncorrectos($idEspacio, $fechaReserva)
    {
        // Buscar registros de hoy para este espacio que no estén justificados
        $registrosIncorrectos = static::where('id_espacio', $idEspacio)
            ->where('fecha_clase', $fechaReserva)
            ->where('estado', 'no_realizada')
            ->get();

        foreach ($registrosIncorrectos as $registro) {
            // Actualizar el estado a justificado con una observación
            $registro->update([
                'estado' => 'justificado',
                'observaciones' => ($registro->observaciones ?? '') . ' [Auto-corregido: El profesor registró entrada tarde]',
            ]);
        }

        return $registrosIncorrectos->count();
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
