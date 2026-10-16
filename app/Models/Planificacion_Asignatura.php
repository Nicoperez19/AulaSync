<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Planificacion_Asignatura extends Model
{
    use HasFactory, BelongsToTenant;

    protected $connection = 'tenant';
    protected $table = 'planificacion_asignaturas';

    protected $primaryKey = 'id';

    protected $fillable = [
        'id_asignatura',
        'id_horario',
        'id_modulo',
        'id_espacio',
        'inscritos',
        'data_load_id',
        'fecha_inicio_vigencia',
        'fecha_fin_vigencia',
        'es_espacio_externo',
    ];

    protected $casts = [
        'id_horario' => 'string',
        'id_asignatura' => 'string',
        'id_modulo' => 'string',
        'id_espacio' => 'string',
        'fecha_inicio_vigencia' => 'date',
        'fecha_fin_vigencia' => 'date',
        'es_espacio_externo' => 'boolean',
    ];

    protected static function booted()
    {
        static::addGlobalScope('vigencia_activa', function ($builder) {
            $hoy = \Carbon\Carbon::today()->toDateString();
            $table = $builder->getModel()->getTable();
            $builder->where(function ($q) use ($table, $hoy) {
                $q->whereNull("{$table}.fecha_fin_vigencia")
                  ->orWhere("{$table}.fecha_fin_vigencia", '>=', $hoy);
            });
        });
    }

    public function scopeVigentesEn($query, $fecha = null)
    {
        $fechaStr = $fecha ? \Carbon\Carbon::parse($fecha)->toDateString() : \Carbon\Carbon::today()->toDateString();

        return $query->withoutGlobalScope('vigencia_activa')
            ->where(function ($q) use ($fechaStr) {
                $q->whereNull('fecha_inicio_vigencia')
                  ->orWhere('fecha_inicio_vigencia', '<=', $fechaStr);
            })->where(function ($q) use ($fechaStr) {
                $q->whereNull('fecha_fin_vigencia')
                  ->orWhere('fecha_fin_vigencia', '>=', $fechaStr);
            });
    }

    public function asignatura()
    {
        return $this->belongsTo(Asignatura::class, 'id_asignatura', 'id_asignatura');
    }

    public function horario()
    {
        return $this->belongsTo(Horario::class, 'id_horario', 'id_horario');
    }

    public function modulo()
    {
        return $this->belongsTo(Modulo::class, 'id_modulo', 'id_modulo');
    }

    public function espacio()
    {
        return $this->belongsTo(Espacio::class, 'id_espacio', 'id_espacio');
    }
}
