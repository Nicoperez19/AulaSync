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
        return $this->belongsTo(UsuarioNoRegistrado::class, 'run', 'run');
    }

    public function asignatura()
    {
        return $this->belongsTo(Asignatura::class, 'id_asignatura', 'id_asignatura');
    }
}
