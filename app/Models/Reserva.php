<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Profesor;
use Illuminate\Support\Str;

class Reserva extends Model
{
    use HasFactory;

    protected $table = 'reservas';

    protected $primaryKey = 'id_reserva';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id_reserva',
        'hora',
        'fecha_reserva',
        'id_espacio',
        'run_profesor',
        'run_solicitante',
    'modulos',
        'tipo_reserva',
        'estado',
        'hora_salida',
        'observaciones',
        'created_at',
        'updated_at'
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
}
