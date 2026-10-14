<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsistenteAcademico extends Model
{
    use BelongsToTenant, HasFactory;

    protected $connection = 'tenant';

    protected $table = 'asistentes_academicos';

    protected $fillable = [
        'nombre',
        'email',
        'nombre_remitente',
        'telefono',
        'id_area_academica',
    ];

    public function areaAcademica(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(AreaAcademica::class, 'id_area_academica', 'id_area_academica');
    }
}
