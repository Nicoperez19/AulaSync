<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provincia extends Model
{
    use HasFactory;

    protected $table = 'provincias';
    protected $primaryKey = 'id_provincia';
    protected $fillable = ['nombre_provincia ','regiones_id'];

    public function region()
    {
        return $this->belongsTo(Region::class, 'regiones_id', 'id_region');
    }

    public function comunas()
    {
        return $this->hasMany(Comuna::class, 'provincias_id', 'id_provincia');
    }
}
