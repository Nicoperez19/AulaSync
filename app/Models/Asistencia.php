<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asistencia extends Model
{
    use HasFactory;

    protected $table = 'asistencias';

    protected $fillable = [
        'id_reserva',
        'id_asignatura',
        'rut_asistente',
        'nombre_asistente',
        'hora_llegada',
        'observaciones',
    ];

    protected $casts = [
        'hora_llegada' => 'datetime:H:i:s',
    ];

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
}
