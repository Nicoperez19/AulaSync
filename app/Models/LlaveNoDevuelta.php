<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class LlaveNoDevuelta extends Model
{
    use HasFactory, BelongsToTenant;

    protected $connection = 'tenant';
    protected $table = 'llaves_no_devueltas';

    protected $fillable = [
        'id_reserva',
        'id_espacio',
        'run_profesor',
        'id_asignatura',
        'fecha_clase',
        'hora_entrada',
        'hora_termino_esperada',
        'cerrado_en',
    ];

    protected $casts = [
        'fecha_clase' => 'date',
        'cerrado_en'  => 'datetime',
    ];

    // Relaciones

    public function espacio()
    {
        return $this->belongsTo(Espacio::class, 'id_espacio', 'id_espacio');
    }

    public function profesor()
    {
        return $this->belongsTo(Profesor::class, 'run_profesor', 'run_profesor');
    }

    public function asignatura()
    {
        return $this->belongsTo(Asignatura::class, 'id_asignatura', 'id_asignatura');
    }

    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'id_reserva', 'id_reserva');
    }

    // Scopes

    public function scopePorFecha($query, $inicio, $fin = null)
    {
        return $fin
            ? $query->whereBetween('fecha_clase', [$inicio, $fin])
            : $query->whereDate('fecha_clase', $inicio);
    }

    public function scopePorProfesor($query, $run)
    {
        return $query->where('run_profesor', $run);
    }

    public function scopePorEspacio($query, $idEspacio)
    {
        return $query->where('id_espacio', $idEspacio);
    }
}
