<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'tipo_reserva',
        'estado',
        'hora_salida',
        'created_at',
        'updated_at'
    ];

    public function espacio()
    {
        return $this->belongsTo(Espacio::class, 'id_espacio', 'id_espacio');
    }

    public function profesor()
    {
        return $this->belongsTo(Profesor::class, 'run_profesor', 'run_profesor');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'run_profesor', 'run');
    }

    public function usuarioNoRegistrado()
    {
        return $this->belongsTo(UsuarioNoRegistrado::class, 'run_profesor', 'run');
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
