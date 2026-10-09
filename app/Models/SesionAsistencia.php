<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SesionAsistencia extends Model
{
    use HasFactory, BelongsToTenant;

    protected $connection = 'tenant';
    protected $table = 'sesiones_asistencia';

    protected $fillable = [
        'id_reserva',
        'id_espacio',
        'id_asignatura',
        'id_profesor_colaborador',
        'fecha',
        'id_modulo',
        'run_profesor',
        'rol_docente',
        'actividad',
        'registrado_por_run',
        'es_prueba',
    ];

    protected $casts = [
        'fecha' => 'date',
        'es_prueba' => 'boolean',
    ];

    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'id_reserva', 'id_reserva');
    }

    public function espacio()
    {
        return $this->belongsTo(Espacio::class, 'id_espacio', 'id_espacio');
    }

    public function asignatura()
    {
        return $this->belongsTo(Asignatura::class, 'id_asignatura', 'id_asignatura');
    }

    public function profesorColaborador()
    {
        return $this->belongsTo(ProfesorColaborador::class, 'id_profesor_colaborador');
    }

    public function modulo()
    {
        return $this->belongsTo(Modulo::class, 'id_modulo', 'id_modulo');
    }

    public function profesor()
    {
        return $this->belongsTo(Profesor::class, 'run_profesor', 'run_profesor');
    }

    public function asistencias()
    {
        return $this->hasMany(AsistenciaEstudiante::class, 'sesion_id');
    }

    public function totalPresentes(): int
    {
        return $this->asistencias()->where('presente', true)->count();
    }

    public function totalAusentes(): int
    {
        return $this->asistencias()->where('presente', false)->count();
    }

    public function totalRegistrados(): int
    {
        return $this->asistencias()->count();
    }

    public function porcentajeAsistencia(): float
    {
        $total = $this->totalRegistrados();
        if ($total === 0) {
            return 0.0;
        }
        return round(($this->totalPresentes() / $total) * 100, 1);
    }
}
