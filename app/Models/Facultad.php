<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facultad extends Model
{
    use HasFactory;
    protected $table = 'facultades';
<<<<<<< HEAD

    protected $primaryKey = 'id_facultad';

    protected $fillable = [
        'id_facultad',
        'nombre_facultad',
        'ubicacion_facultad',
        'logo_facultad',
<<<<<<< HEAD
=======
    protected $primaryKey = 'id_facultad';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'nombre_facultad',
        'id_universidad',
>>>>>>> Nperez
=======
>>>>>>> 6c05e560f5edb88b89bd0fe7d8d71ecb8386c841
        'id_sede',
        'id_campus',
    ];

<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> 6c05e560f5edb88b89bd0fe7d8d71ecb8386c841
    public function sede() {
        return $this->belongsTo(Sede::class, 'id_sede');
    }
    
    public function campus() {
        return $this->belongsTo(Campus::class, 'id_campus');
<<<<<<< HEAD
    }

=======
    public function sede()
    {
        return $this->belongsTo(Sede::class, 'id_sede');
    }

    public function campus()
    {
        return $this->belongsTo(Campus::class, 'id_campus');
    }

    public function universidad()
    {
        return $this->belongsTo(Universidad::class, 'id_universidad');
=======
>>>>>>> 6c05e560f5edb88b89bd0fe7d8d71ecb8386c841
    }

>>>>>>> Nperez
    public function areaAcademicas()
    {
        return $this->hasMany(AreaAcademica::class, 'id_facultad');
    }
    public function pisos()
    {
        return $this->hasMany(Piso::class, 'id_facultad');
    }
}
