<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Solicitante
 * 
 * Maneja usuarios externos que solicitan espacios pero no son profesores registrados.
 * Estos usuarios pueden ser estudiantes, personal administrativo, o visitantes.
 */
class Solicitante extends Model
{
    use HasFactory;

    protected $table = 'solicitantes';

    protected $fillable = [
        'run_solicitante',
        'nombre',
        'correo',
        'telefono',
        'tipo_solicitante', // 'estudiante', 'personal', 'visitante', etc.
        'institucion_origen', // Universidad, empresa, etc.
        'activo',
        'fecha_registro'
    ];

    protected $casts = [
        'activo' => 'boolean',
        'fecha_registro' => 'datetime',
    ];

    /**
     * Relación con las reservas realizadas por este solicitante
     */
    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'run_solicitante', 'run_solicitante');
    }

    /**
     * Scope para solicitantes activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para buscar por tipo de solicitante
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_solicitante', $tipo);
    }

    /**
     * Verificar si el solicitante tiene reservas activas
     */
    public function tieneReservasActivas()
    {
        return $this->reservas()
            ->where('estado', 'activa')
            ->whereNull('hora_salida')
            ->exists();
    }

    /**
     * Obtener reservas activas del solicitante
     */
    public function reservasActivas()
    {
        return $this->reservas()
            ->where('estado', 'activa')
            ->whereNull('hora_salida')
            ->get();
    }

    /**
     * Verificar si el solicitante puede hacer una nueva reserva
     * (máximo 2 reservas por día)
     */
    public function puedeReservar()
    {
        $reservasHoy = $this->reservas()
            ->whereDate('fecha_reserva', now()->toDateString())
            ->count();

        return $reservasHoy < 2; // Máximo 2 reservas por día
    }

    /**
     * Obtener el número de reservas realizadas hoy
     */
    public function reservasHoy()
    {
        return $this->reservas()
            ->whereDate('fecha_reserva', now()->toDateString())
            ->count();
    }
} 