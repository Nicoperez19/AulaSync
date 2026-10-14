<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modulo extends Model
{
    use BelongsToTenant, HasFactory;

    protected $connection = 'tenant';

    protected $table = 'modulos';

    protected $primaryKey = 'id_modulo';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_modulo',
        'dia',
        'hora_inicio',
        'hora_termino',
    ];

    protected $appends = ['nombre_modulo'];

    /**
     * Obtiene una representación legible del módulo
     */
    public function getNombreModuloAttribute()
    {
        return $this->id_modulo.' ('.substr($this->hora_inicio, 0, 5).' - '.substr($this->hora_termino, 0, 5).')';
    }

    public function planificaciones(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Planificacion_Asignatura::class, 'id_modulo', 'id_modulo');
    }

    public function planificacionesColaboradores(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PlanificacionProfesorColaborador::class, 'id_modulo', 'id_modulo');
    }
}
