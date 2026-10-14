<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profesor extends Model
{
    use BelongsToTenant, HasFactory;

    protected $connection = 'tenant';

    protected $table = 'profesors';

    protected $primaryKey = 'run_profesor';

    public $incrementing = false;

    protected $fillable = [
        'run_profesor',
        'name',
        'email',
        'celular',
        'direccion',
        'fecha_nacimiento',
        'anio_ingreso',
        'tipo_profesor',
        'id_universidad',
        'id_facultad',
        'id_carrera',
        'id_area_academica',
        'sede_id',
    ];

    // Relación con Universidad
    public function universidad(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Universidad::class, 'id_universidad');
    }

    // Relación con Facultad
    public function facultad(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Facultad::class, 'id_facultad');
    }

    // Relación con Carrera
    public function carrera(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Carrera::class, 'id_carrera');
    }

    // Relación con Area Académica
    public function areaAcademica(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(AreaAcademica::class, 'id_area_academica');
    }

    // Relación con Sede
    public function sede(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Sede::class, 'sede_id', 'id_sede');
    }

    // Relación con Horarios
    public function horarios(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Horario::class, 'run_profesor', 'run_profesor');
    }

    // Relación con Asignaturas
    public function asignaturas(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Asignatura::class, 'run_profesor', 'run_profesor');
    }

    // Relación con Reservas
    public function reservas(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Reserva::class, 'run_profesor', 'run_profesor');
    }

    // Relación con DataLoads
    public function dataLoads(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DataLoad::class, 'profesor_run', 'run_profesor');
    }

    // Relación con Licencias
    public function licencias(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LicenciaProfesor::class, 'run_profesor', 'run_profesor');
    }

    // Relación con Recuperaciones de Clases
    public function recuperaciones(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RecuperacionClase::class, 'run_profesor', 'run_profesor');
    }

    // Relación con Clases Temporales (como colaborador)
    public function clasesTemporalesColaborador(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProfesorColaborador::class, 'id_profesor', 'run_profesor');
    }

    // Método para obtener el nombre completo
    public function getNombreCompleto()
    {
        return $this->name;
    }

    // Método para verificar si es un tipo específico de profesor
    public function esTipo($tipo)
    {
        return $this->tipo_profesor === $tipo;
    }

    // Método para verificar si es Profesor Responsable
    public function esResponsable()
    {
        return $this->esTipo('Profesor Responsable');
    }

    // Método para verificar si es Profesor Colaborador
    public function esColaborador()
    {
        return $this->esTipo('Profesor Colaborador') || $this->esTipo('Colaborador');
    }

    // Método para verificar si es Invitado
    public function esInvitado()
    {
        return $this->esTipo('Invitado');
    }

    // Método para verificar si es Ayudante
    public function esAyudante()
    {
        return $this->esTipo('Ayudante');
    }
}
