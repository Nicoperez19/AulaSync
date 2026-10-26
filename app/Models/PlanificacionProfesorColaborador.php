<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanificacionProfesorColaborador extends Model
{
    use BelongsToTenant, HasFactory;

    protected $connection = 'tenant';

    protected $table = 'planificaciones_profesores_colaboradores';

    protected $fillable = [
        'id_profesor_colaborador',
        'id_modulo',
        'id_espacio',
        'fecha_inicio_vigencia',
        'fecha_fin_vigencia',
    ];

    protected $casts = [
        'id_modulo' => 'string',
        'id_espacio' => 'string',
        'fecha_inicio_vigencia' => 'date',
        'fecha_fin_vigencia' => 'date',
    ];

    protected static array $vigenciaColumnCache = [];

    public static function tieneColumnaVigencia(): bool
    {
        try {
            $dbName = config('database.connections.tenant.database') ?? 'default';
            if (!array_key_exists($dbName, static::$vigenciaColumnCache)) {
                static::$vigenciaColumnCache[$dbName] = \Illuminate\Support\Facades\Schema::connection('tenant')
                    ->hasColumn('planificaciones_profesores_colaboradores', 'fecha_fin_vigencia');
            }
            return static::$vigenciaColumnCache[$dbName];
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected static function booted()
    {
        static::addGlobalScope('vigencia_activa', function ($builder) {
            if (!static::tieneColumnaVigencia()) {
                return;
            }
            $hoy = \Carbon\Carbon::today()->toDateString();
            $table = $builder->getModel()->getTable();
            $builder->where(function ($q) use ($table, $hoy) {
                $q->whereNull("{$table}.fecha_fin_vigencia")
                  ->orWhere("{$table}.fecha_fin_vigencia", '>=', $hoy);
            });
        });
    }

    /**
     * Relación con ProfesorColaborador
     */
    public function profesorColaborador(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProfesorColaborador::class, 'id_profesor_colaborador');
    }

    /**
     * Relación con Modulo
     */
    public function modulo(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Modulo::class, 'id_modulo', 'id_modulo');
    }

    /**
     * Relación con Espacio
     */
    public function espacio(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Espacio::class, 'id_espacio', 'id_espacio');
    }

    /**
     * Scope para filtrar por día
     */
    public function scopePorDia($query, $dia)
    {
        return $query->whereHas('modulo', function ($q) use ($dia) {
            $q->where('dia', $dia);
        });
    }

    /**
     * Scope para obtener planificaciones vigentes
     */
    public function scopeVigentes($query, $fecha = null)
    {
        return $query->whereHas('profesorColaborador', function ($q) use ($fecha) {
            $q->activosYVigentes($fecha);
        });
    }
}
