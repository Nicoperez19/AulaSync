<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles;

    protected $primaryKey = 'run';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'run',
        'name',
        'email',
        'password',
        'celular',
        'direccion',
        'fecha_nacimiento',
        'anio_ingreso',
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
        'run' => 'string',
        'fecha_nacimiento' => 'date',
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
        return $this->belongsTo(Universidad::class, 'id_universidad');
    }

    public function facultad()
    {
        return $this->belongsTo(Facultad::class, 'id_facultad');
    }

    public function carrera()
    {
        return $this->belongsTo(Carrera::class, 'id_carrera');
    }

    public function areaAcademica()
    {
        return $this->belongsTo(AreaAcademica::class, 'id_area_academica');
    }

    public function profesor()
    {
        return $this->hasOne(Profesor::class, 'run_profesor', 'run');
    }


}
