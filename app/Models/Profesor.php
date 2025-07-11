<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profesor extends Model
{
    use HasFactory;

    protected $table = 'profesors';

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
        'id_area_academica'
    ];





    // Relación con Universidad
    public function universidad()
    {
        return $this->belongsTo(Universidad::class, 'id_universidad');
    }

    // Relación con Facultad
    public function facultad()
    {
        return $this->belongsTo(Facultad::class, 'id_facultad');
    }

    // Relación con Carrera
    public function carrera()
    {
        return $this->belongsTo(Carrera::class, 'id_carrera');
    }

    // Relación con Area Académica
    public function areaAcademica()
    {
        return $this->belongsTo(AreaAcademica::class, 'id_area_academica');
    }

    // Relación con Horarios
    public function horarios()
    {
        return $this->hasMany(Horario::class, 'run_profesor', 'run_profesor');
    }

    // Relación con Asignaturas
    public function asignaturas()
    {
        return $this->hasMany(Asignatura::class, 'run_profesor', 'run_profesor');
    }

    // Relación con Reservas
    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'run_profesor', 'run_profesor');
    }

    // Relación con DataLoads
    public function dataLoads()
    {
        return $this->hasMany(DataLoad::class, 'profesor_run', 'run_profesor');
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
        return $this->esTipo('Profesor Colaborador');
    }

    // Método para verificar si es Ayudante
    public function esAyudante()
    {
        return $this->esTipo('Ayudante');
    }

  
}
