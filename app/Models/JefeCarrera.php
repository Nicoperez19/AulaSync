<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JefeCarrera extends Model
{
    use BelongsToTenant, HasFactory;

    protected $connection = 'tenant';

    protected $table = 'jefes_carrera';

    protected $fillable = [
        'nombre',
        'email',
        'telefono',
        'id_carrera',
    ];

    public function carrera(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Carrera::class, 'id_carrera', 'id_carrera');
    }
}
