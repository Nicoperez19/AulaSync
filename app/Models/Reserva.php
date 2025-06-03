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
        'run',
        'tipo_reserva',
        'estado',
        'hora_salida'
    ];

    public function espacio()
    {
        return $this->belongsTo(Espacio::class, 'id_espacio', 'id_espacio');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'run');
    }


}
