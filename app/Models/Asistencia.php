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
        'rut_asistente',
        'nombre_asistente',
        'hora_llegada',
        'hora_termino',
        'contenido_visto',
    ];

    protected $casts = [
        'hora_llegada' => 'datetime:H:i:s',
        'hora_termino' => 'datetime:H:i:s',
    ];

    /**
     * Relación con la reserva
     */
    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'id_reserva', 'id_reserva');
    }

    /**
     * Obtener el contenido visto o mensaje por defecto
     */
    public function getContenidoVistoAttribute($value)
    {
        return $value ?? 'Sin información adicionada';
    }
}
