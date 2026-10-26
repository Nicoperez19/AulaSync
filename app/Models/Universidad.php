<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Universidad extends Model
{

    protected $connection = 'mysql';

    protected $table = 'universidades';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $primaryKey = 'id_universidad';

    protected $fillable = [
        'id_universidad',
        'nombre_universidad',
        'imagen_logo',
    ];

    public function sedes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Sede::class, 'id_universidad', 'id_universidad');
    }

    public function facultades(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Facultad::class, 'id_universidad', 'id_universidad');
    }
}
