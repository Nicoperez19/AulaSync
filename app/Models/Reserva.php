<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Profesor;
use Illuminate\Support\Str;
use App\Traits\BelongsToTenant;

class Reserva extends Model
{
    use HasFactory, BelongsToTenant;

    protected $connection = 'tenant';
    protected $table = 'reservas';

    protected $primaryKey = 'id_reserva';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id_reserva',
        'hora',
        'fecha_reserva',
        'id_espacio',
        'id_asignatura',
        'run_profesor',
        'run_solicitante',
        'modulos',
        'tipo_reserva',
        'estado',
        'hora_salida',
        'observaciones',
        'hubo_asistentes',
        'nombre_actividad',
        'descripcion_actividad',
        'modulo_inicio',
        'modulo_fin',
        'clase_finalizada_anticipadamente',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'hubo_asistentes' => 'boolean',
        'clase_finalizada_anticipadamente' => 'boolean',
        'fecha_reserva' => 'date',
    ];

    /**
     * Generar un ID único para la reserva
     * Formato: R + timestamp + contador (ej: R202508211455301)
     */
    public static function generarIdUnico()
    {
        do {
            $timestamp = now()->format('YmdHis'); // YYYYMMDDHHMMSS
            $contador = rand(1, 999); // Número aleatorio de 1-999
            $idReserva = 'R' . $timestamp . str_pad($contador, 3, '0', STR_PAD_LEFT);

            // Verificar que el ID no exista
            $existe = self::where('id_reserva', $idReserva)->exists();
        } while ($existe);

        return $idReserva;
    }

    public function espacio()
    {
        return $this->belongsTo(Espacio::class, 'id_espacio', 'id_espacio');
    }

    public function profesor()
    {
        return $this->belongsTo(Profesor::class, 'run_profesor', 'run_profesor');
    }

    // Relación con User (mantenida para compatibilidad, pero se recomienda usar profesor)
    public function user()
    {
        return $this->belongsTo(User::class, 'run_profesor', 'run');
    }



    /**
     * Relación con el solicitante (si la reserva es de un solicitante)
     */
    public function solicitante()
    {
        return $this->belongsTo(Solicitante::class, 'run_solicitante', 'run_solicitante');
    }

    /**
     * Obtener el usuario que realizó la reserva (profesor o solicitante)
     */
    public function usuarioReserva()
    {
        if ($this->run_profesor) {
            return $this->profesor();
        } elseif ($this->run_solicitante) {
            return $this->solicitante();
        }
        return null;
    }

    /**
     * Obtener el nombre del usuario que realizó la reserva
     */
    public function getNombreUsuarioAttribute()
    {
        if ($this->run_profesor && $this->profesor) {
            return $this->profesor->name;
        } elseif ($this->run_solicitante && $this->solicitante) {
            return $this->solicitante->nombre;
        }
        return 'Usuario desconocido';
    }

    /**
     * Obtener el tipo de usuario que realizó la reserva
     */
    public function getTipoUsuarioAttribute()
    {
        if ($this->run_profesor) {
            return 'profesor';
        } elseif ($this->run_solicitante) {
            return 'solicitante';
        }
        return 'desconocido';
    }

    public function asignatura()
    {
        return $this->belongsTo(Asignatura::class, 'id_asignatura', 'id_asignatura');
    }

    /**
     * Relación con las asistencias de la reserva
     */
    public function asistencias()
    {
        return $this->hasMany(Asistencia::class, 'id_reserva', 'id_reserva');
    }

    /**
     * Scope para obtener reservas vigentes en un momento dado
     * Incluye reservas activas que cubren la hora especificada
     */
    public function scopeVigentes($query, $fecha = null, $hora = null)
    {
        $fecha = $fecha ?? now()->toDateString();
        $hora = $hora ?? now()->format('H:i:s');

        return $query->where('estado', 'activa')
                     ->where('fecha_reserva', $fecha)
                     ->where(function($q) use ($hora) {
                         $q->where('hora', '<=', $hora)
                           ->where('hora_salida', '>', $hora);
                     });
    }

    /**
     * Scope para obtener reservas futuras desde un momento dado
     * Incluye reservas activas o programadas que comienzan después de la hora especificada
     */
    public function scopeFuturas($query, $fecha = null, $hora = null)
    {
        $fecha = $fecha ?? now()->toDateString();
        $hora = $hora ?? now()->format('H:i:s');

        return $query->whereIn('estado', ['activa', 'programada'])
                     ->where('fecha_reserva', $fecha)
                     ->where('hora', '>', $hora)
                     ->orderBy('hora', 'asc');
    }

    /**
     * Scope para obtener reservas programadas (con antelación, aún no activas)
     */
    public function scopeProgramadas($query, $fecha = null)
    {
        $fecha = $fecha ?? now()->toDateString();

        return $query->where('estado', 'programada')
                     ->where('fecha_reserva', $fecha);
    }

    /**
     * Verificar si la reserva corresponde al módulo/hora actual y debe activarse
     */
    public function debeActivarse(): bool
    {
        $ahora = now();
        $fechaHoy = $ahora->toDateString();
        $horaActual = $ahora->format('H:i:s');

        // Solo para reservas programadas del día actual
        if ($this->estado !== 'programada') {
            return false;
        }

        $fechaReserva = $this->fecha_reserva instanceof \Carbon\Carbon
            ? $this->fecha_reserva->format('Y-m-d')
            : $this->fecha_reserva;

        if ($fechaReserva !== $fechaHoy) {
            return false;
        }

        // Verificar si la hora actual está dentro del rango de módulos de la reserva
        $diaActual = strtolower($ahora->locale('es')->isoFormat('dddd'));
        $diaNormalizado = \App\Helpers\ModulosHelper::normalizarDia($diaActual);
        $horariosModulos = \App\Helpers\ModulosHelper::getHorariosModulos()[$diaNormalizado] ?? [];

        $moduloInicio = $this->modulo_inicio;
        $moduloFin = $this->modulo_fin;

        if (!$moduloInicio || !$moduloFin) {
            return false;
        }

        $horaInicioReserva = $horariosModulos[$moduloInicio]['inicio'] ?? null;
        $horaFinReserva = $horariosModulos[$moduloFin]['fin'] ?? null;

        if (!$horaInicioReserva || !$horaFinReserva) {
            return false;
        }

        return $horaActual >= $horaInicioReserva && $horaActual <= $horaFinReserva;
    }
}
