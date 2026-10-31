<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class IncidenciaEspacioEvento extends Model
{
    use BelongsToTenant;

    protected $connection = 'tenant';
    protected $table = 'incidencias_espacios_eventos';

    protected $fillable = [
        'incidencia_id',
        'tipo',
        'estado_anterior',
        'estado_nuevo',
        'detalle',
        'usuario_run',
        'usuario_nombre',
    ];

    public function incidencia()
    {
        return $this->belongsTo(IncidenciaEspacio::class, 'incidencia_id');
    }
}
