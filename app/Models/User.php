<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $primaryKey = 'run';
    public $incrementing = false;
<<<<<<< HEAD
<<<<<<< HEAD
    protected $keyType = 'string';
=======
    protected $keyType = 'integer';
>>>>>>> Nperez
=======
    protected $keyType = 'string';
>>>>>>> 6c05e560f5edb88b89bd0fe7d8d71ecb8386c841
    protected $fillable = [
        'run',
        'name',
        'email',
        'password',
        'celular',
        'direccion',
        'fecha_nacimiento',
        'anio_ingreso',
        'tipo_profesor',
        'id_universidad',
        'id_facultad',
        'id_carrera',
        'id_area_academica',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
<<<<<<< HEAD
<<<<<<< HEAD
        'run' => 'string',
        'fecha_nacimiento' => 'date',
=======
        'fecha_nacimiento' => 'date',
        'anio_ingreso' => 'integer',
>>>>>>> Nperez
=======
        'run' => 'string',
        'fecha_nacimiento' => 'date',
>>>>>>> 6c05e560f5edb88b89bd0fe7d8d71ecb8386c841
    ];

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return 'run';
    }

    public function universidad()
    {
<<<<<<< HEAD
        return $this->belongsTo(Universidad::class, 'id_universidad');
=======
        return $this->belongsTo(Universidad::class, 'id_universidad', 'id_universidad');
>>>>>>> Nperez
    }

    public function facultad()
    {
<<<<<<< HEAD
        return $this->belongsTo(Facultad::class, 'id_facultad');
=======
        return $this->belongsTo(Facultad::class, 'id_facultad', 'id_facultad');
>>>>>>> Nperez
    }

    public function carrera()
    {
<<<<<<< HEAD
        return $this->belongsTo(Carrera::class, 'id_carrera');
=======
        return $this->belongsTo(Carrera::class, 'id_carrera', 'id_carrera');
>>>>>>> Nperez
    }

    public function areaAcademica()
    {
<<<<<<< HEAD
        return $this->belongsTo(AreaAcademica::class, 'id_area_academica');
=======
        return $this->belongsTo(AreaAcademica::class, 'id_area_academica', 'id_area_academica');
>>>>>>> Nperez
    }

    public function asignaturas()
    {
<<<<<<< HEAD
<<<<<<< HEAD
        return $this->hasMany(Asignatura::class, 'run');
=======
        return $this->hasMany(Asignatura::class, 'run', 'run');
>>>>>>> Nperez
=======
        return $this->hasMany(Asignatura::class, 'run');
>>>>>>> 6c05e560f5edb88b89bd0fe7d8d71ecb8386c841
    }

    public function dataLoads()
    {
        return $this->hasMany(DataLoad::class, 'user_run', 'run');
    }

<<<<<<< HEAD
<<<<<<< HEAD

=======
    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'run', 'run');
    }
>>>>>>> Nperez
=======

>>>>>>> 6c05e560f5edb88b89bd0fe7d8d71ecb8386c841
}
