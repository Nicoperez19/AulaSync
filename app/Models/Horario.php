<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    use BelongsToTenant, HasFactory;

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

    public function profesor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Profesor::class, 'run_profesor', 'run_profesor');
    }

    public function planificaciones(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Planificacion_Asignatura::class, 'id_horario', 'id_horario');
    }
}
