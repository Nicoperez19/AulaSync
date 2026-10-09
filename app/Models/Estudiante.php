<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estudiante extends Model
{
    use HasFactory, BelongsToTenant;

    protected $connection = 'tenant';
    protected $table = 'estudiantes';

    protected $fillable = [
        'run',
        'nombre',
        'email',
        'id_carrera',
        'origen',
    ];

    /**
     * Relación con asignaturas en las que está inscrito
     */
    public function asignaturas()
    {
        return $this->belongsToMany(Asignatura::class, 'inscripciones', 'estudiante_id', 'id_asignatura')
            ->withPivot('periodo')
            ->withTimestamps();
    }

    /**
     * Relación con asistencias marcadas
     */
    public function asistencias()
    {
        return $this->hasMany(AsistenciaEstudiante::class, 'estudiante_id');
    }

    /**
     * Helper para normalizar RUN (quitar puntos, guiones y espacios)
     */
    public static function normalizarRun(?string $run): string
    {
        if (!$run) {
            return '';
        }
        return strtoupper(preg_replace('/[^0-9kK]/', '', trim($run)));
    }
}
