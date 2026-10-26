<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Horario extends Model
{
    use HasFactory, BelongsToTenant;

    protected $connection = 'tenant';
    protected $table = 'horarios';
    protected $primaryKey = 'id_horario';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_horario',
        'nombre',
        'periodo',
        'run_profesor',
    ];

    public function profesor()
    {
        return $this->belongsTo(Profesor::class, 'run_profesor', 'run_profesor');
    }

    public function planificaciones()
    {
        return $this->hasMany(Planificacion_Asignatura::class, 'id_horario', 'id_horario');
    }
}
