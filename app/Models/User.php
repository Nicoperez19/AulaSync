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
    protected $keyType = 'integer';
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
        'fecha_nacimiento' => 'date',
        'anio_ingreso' => 'integer',
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
        return $this->belongsTo(Universidad::class, 'id_universidad', 'id_universidad');
    }

    public function facultad()
    {
        return $this->belongsTo(Facultad::class, 'id_facultad', 'id_facultad');
    }

    public function carrera()
    {
        return $this->belongsTo(Carrera::class, 'id_carrera', 'id_carrera');
    }

    public function areaAcademica()
    {
        return $this->belongsTo(AreaAcademica::class, 'id_area_academica', 'id_area_academica');
    }

    public function asignaturas()
    {
        return $this->hasMany(Asignatura::class, 'run', 'run');
    }

    public function dataLoads()
    {
        return $this->hasMany(DataLoad::class, 'user_run', 'run');
    }

    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'run', 'run');
    }
}
