<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Universidad extends Model
{
    use HasFactory;
    protected $table = 'universidades'; 
    public $incrementing = false; 
    protected $keyType = 'string'; 
    protected $primaryKey = 'id_universidad';

    protected $fillable = [
        'id_universidad',
        'nombre_universidad',
        'imagen_logo',
    ];

    public function sedes()
    {
        return $this->hasMany(Sede::class, 'id_universidad', 'id_universidad');
<<<<<<< HEAD
    }
<<<<<<< HEAD
=======

    public function facultades()
    {
        return $this->hasMany(Facultad::class, 'id_universidad', 'id_universidad');
=======
>>>>>>> 6c05e560f5edb88b89bd0fe7d8d71ecb8386c841
    }
>>>>>>> Nperez
}
