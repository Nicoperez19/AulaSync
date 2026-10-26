<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsistenciaEstudiante extends Model
{
    use HasFactory, BelongsToTenant;

    protected $connection = 'tenant';
    protected $table = 'asistencia_estudiantes';

    protected $fillable = [
        'sesion_id',
        'estudiante_id',
        'presente',
        'inscrito',
        'metodo',
        'observacion',
    ];

    protected $casts = [
        'presente' => 'boolean',
        'inscrito' => 'boolean',
    ];

    public function sesion()
    {
        return $this->belongsTo(SesionAsistencia::class, 'sesion_id');
    }

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'estudiante_id');
    }
}
