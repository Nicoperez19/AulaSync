<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campus extends Model
{

    protected $connection = 'mysql';

    protected $primaryKey = 'id_campus';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_campus',
        'nombre_campus',
        'id_sede',
    ];

    public function sede(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Sede::class, 'id_sede', 'id_sede');
    }

    public function facultades(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Facultad::class, 'id_campus', 'id_campus');
    }
}
