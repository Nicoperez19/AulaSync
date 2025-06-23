<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Piso extends Model
{
    use HasFactory;
    protected $table = 'pisos'; 
<<<<<<< HEAD
    protected $primaryKey = 'id';  // Cambiamos el nombre del primaryKey
    public $incrementing = true;  // Esto es por defecto en Laravel cuando usas 'id' autoincremental
    protected $keyType = 'int';   // El tipo es 'int' porque el campo id es autoincremental
=======
    protected $primaryKey = 'id';  
    public $incrementing = true;  
    protected $keyType = 'int';   
>>>>>>> Nperez

    protected $fillable = [
        'numero_piso',
        'id_facultad',
    ];

    public function facultad()
    {
        return $this->belongsTo(Facultad::class, 'id_facultad');
    }
<<<<<<< HEAD
=======
       public function mapas()
    {
        return $this->hasMany(Mapa::class);
    }

    public function espacios()
    {
        return $this->hasMany(Espacio::class);
    }
>>>>>>> Nperez
}
