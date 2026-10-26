<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class OcupacionSemanalSnapshot extends Model
{
    use HasFactory, BelongsToTenant;

    protected $connection = 'tenant';

    protected $table = 'ocupacion_semanal_snapshots';

    protected $fillable = [
        'tenant_id',
        'semana_inicio',
        'semana_fin',
        'anio',
        'semana_iso',
        'periodo',
        'tipo_filtro',
        'total_espacios',
        'datos',
        'resumen',
        'origen',
        'generado_por',
    ];

    protected $casts = [
        'semana_inicio' => 'date',
        'semana_fin' => 'date',
        'datos' => 'array',
        'resumen' => 'array',
        'anio' => 'integer',
        'semana_iso' => 'integer',
        'total_espacios' => 'integer',
    ];

    public function generador()
    {
        return $this->belongsTo(User::class, 'generado_por');
    }

    public function scopePeriodo($query, $periodo)
    {
        return $query->where('periodo', $periodo);
    }

    public function scopeTipo($query, $tipo)
    {
        return $query->where('tipo_filtro', $tipo);
    }

    public function scopeAnio($query, $anio)
    {
        return $query->where('anio', $anio);
    }
}
