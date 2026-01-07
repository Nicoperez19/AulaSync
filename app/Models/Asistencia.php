<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Traits\BelongsToTenant;

class Asistencia extends Model
{
    use HasFactory, BelongsToTenant;
    
    protected $connection = 'tenant';
    protected $table = 'asistencias';

    protected $fillable = [
        'id_reserva',
        'id_asignatura',
        'id_espacio',
        'rut_asistente',
        'nombre_asistente',
        'hora_llegada',
        'hora_salida',
        'tipo_entrada',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'hora_llegada' => 'datetime:H:i:s',
        'hora_salida' => 'datetime:H:i:s',
    ];

    /**
     * Constantes para tipo de entrada
     */
    const TIPO_PLANIFICADA = 'planificada';
    const TIPO_ESPONTANEA = 'espontanea';

    /**
     * Constantes para estado
     */
    const ESTADO_PRESENTE = 'presente';
    const ESTADO_FINALIZADO = 'finalizado';

    /**
     * Relación con la reserva
     */
    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'id_reserva', 'id_reserva');
    }

    /**
     * Relación con la asignatura
     */
    public function asignatura()
    {
        return $this->belongsTo(Asignatura::class, 'id_asignatura', 'id_asignatura');
    }

    /**
     * Relación con el espacio
     */
    public function espacio()
    {
        return $this->belongsTo(Espacio::class, 'id_espacio', 'id_espacio');
    }

    /**
     * Scope para obtener asistencias activas (presentes)
     */
    public function scopePresentes($query)
    {
        return $query->where('estado', self::ESTADO_PRESENTE);
    }

    /**
     * Scope para obtener asistencias de hoy
     */
    public function scopeHoy($query)
    {
        return $query->whereDate('created_at', Carbon::today());
    }

    /**
     * Scope para obtener asistencias de un espacio
     */
    public function scopeEnEspacio($query, $idEspacio)
    {
        return $query->where('id_espacio', $idEspacio);
    }

    /**
     * Scope para obtener asistencias de un alumno
     */
    public function scopeDelAlumno($query, $rutAlumno)
    {
        return $query->where('rut_asistente', $rutAlumno);
    }

    /**
     * Verificar si el alumno está actualmente presente
     */
    public function estaPresente(): bool
    {
        return $this->estado === self::ESTADO_PRESENTE;
    }

    /**
     * Marcar salida del alumno
     */
    public function marcarSalida(): bool
    {
        $this->hora_salida = Carbon::now()->format('H:i:s');
        $this->estado = self::ESTADO_FINALIZADO;
        return $this->save();
    }

    /**
     * Calcular duración de la presencia en minutos
     */
    public function getDuracionMinutosAttribute(): ?int
    {
        if (!$this->hora_salida) {
            return null;
        }
        
        return Carbon::parse($this->hora_llegada)->diffInMinutes(Carbon::parse($this->hora_salida));
    }
}
