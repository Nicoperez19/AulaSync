<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asignatura extends Model
{
    use BelongsToTenant, HasFactory;

    protected $connection = 'tenant';

    protected $primaryKey = 'id_asignatura';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'asignaturas';

    protected $fillable = [
        'id_asignatura', // id_curso
        'codigo_asignatura', // cod_ramo
        'nombre_asignatura', // ramo_nombre
        'seccion',
        'horas_directas',
        'horas_indirectas',
        'area_conocimiento',
        'periodo',
        'run_profesor', // profesor
        'id_carrera',
    ];

    public function reservas(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Reserva::class, 'id_asignatura', 'id_asignatura');
    }

    public function profesor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Profesor::class, 'run_profesor', 'run_profesor');
    }

    public function carrera(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Carrera::class, 'id_carrera', 'id_carrera');
    }

    public function planificaciones(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Planificacion_Asignatura::class, 'id_asignatura', 'id_asignatura');
    }

    public function colaboradores(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProfesorColaborador::class, 'id_asignatura', 'id_asignatura');
    }
}
