<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class AuditoriaClase extends Model
{
    use HasFactory, BelongsToTenant;

    protected $connection = 'tenant';

    protected $table = 'auditoria_clases';

    const UPDATED_AT = null;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'user_nombre',
        'user_rol',
        'accion',
        'entidad_tipo',
        'entidad_id',
        'run_profesor',
        'id_asignatura',
        'id_espacio',
        'id_modulo',
        'fecha_clase',
        'estado_anterior',
        'estado_nuevo',
        'valores_anteriores',
        'valores_nuevos',
        'motivo',
        'observaciones',
        'lote_uuid',
        'origen',
        'ip',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'fecha_clase' => 'date',
        'valores_anteriores' => 'array',
        'valores_nuevos' => 'array',
        'created_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::updating(function () {
            throw new \RuntimeException('Los registros de auditoría son inmutables y no pueden modificarse.');
        });

        static::deleting(function () {
            throw new \RuntimeException('Los registros de auditoría son inmutables y no pueden eliminarse.');
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function profesor()
    {
        return $this->belongsTo(Profesor::class, 'run_profesor', 'run_profesor');
    }

    public function asignatura()
    {
        return $this->belongsTo(Asignatura::class, 'id_asignatura', 'id_asignatura');
    }

    public function espacio()
    {
        return $this->belongsTo(Espacio::class, 'id_espacio', 'id_espacio');
    }

    public function modulo()
    {
        return $this->belongsTo(Modulo::class, 'id_modulo', 'id_modulo');
    }

    public function scopePorProfesor($query, $run)
    {
        return $query->where('run_profesor', $run);
    }

    public function scopePorAccion($query, $accion)
    {
        return $query->where('accion', $accion);
    }

    public function scopePorLote($query, $uuid)
    {
        return $query->where('lote_uuid', $uuid);
    }

    public function scopeRangoFechas($query, $desde, $hasta)
    {
        if ($desde && $hasta) {
            return $query->whereBetween('fecha_clase', [$desde, $hasta]);
        } elseif ($desde) {
            return $query->where('fecha_clase', '>=', $desde);
        } elseif ($hasta) {
            return $query->where('fecha_clase', '<=', $hasta);
        }
        return $query;
    }
}
