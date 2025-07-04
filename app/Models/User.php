<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Services\QRService;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles;

    protected $primaryKey = 'run';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'run',
        'qr_run',
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

    public function asignaturas()
    {
        return $this->hasMany(Asignatura::class, 'run');
    }

    public function dataLoads()
    {
        return $this->hasMany(DataLoad::class, 'user_run', 'run');
    }

    public function horarios()
    {
        return $this->hasMany(Horario::class, 'run', 'run');
    }

    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'run', 'run');
    }

    /**
     * Genera el código QR para el usuario y lo guarda en la base de datos
     */
    public function generateQR()
    {
        $qrService = new QRService();
        $qrFileName = $qrService->generateQRForUser($this->run);
        $this->qr_run = $qrFileName;
        $this->save();
        return $this;
    }
}
