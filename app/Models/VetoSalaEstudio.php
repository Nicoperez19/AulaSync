<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VetoSalaEstudio extends Model
{
    use HasFactory;

    protected $table = 'vetos_sala_estudio';

    protected $fillable = [
        'run_vetado',
        'tipo_veto',
        'id_reserva_origen',
        'observacion',
        'estado',
        'vetado_por',
        'liberado_por',
        'fecha_veto',
        'fecha_liberacion',
    ];

    protected $casts = [
        'fecha_veto' => 'datetime',
        'fecha_liberacion' => 'datetime',
    ];

    /**
     * Relación con el solicitante vetado
     */
    public function solicitante()
    {
        return $this->belongsTo(Solicitante::class, 'run_vetado', 'run_solicitante');
    }

    /**
     * Relación con la reserva que originó el veto
     */
    public function reservaOrigen()
    {
        return $this->belongsTo(Reserva::class, 'id_reserva_origen');
    }

    /**
     * Verificar si un RUN está vetado
     */
    public static function estaVetado($run)
    {
        return self::where('run_vetado', $run)
            ->where('estado', 'activo')
            ->exists();
    }

    /**
     * Obtener el veto activo de un RUN
     */
    public static function vetoActivo($run)
    {
        return self::where('run_vetado', $run)
            ->where('estado', 'activo')
            ->first();
    }
}
