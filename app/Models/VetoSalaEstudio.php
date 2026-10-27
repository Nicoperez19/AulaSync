<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VetoSalaEstudio extends Model
{
    use HasFactory, BelongsToTenant;

    protected $connection = 'tenant';

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
        'fecha_fin_veto',
        'fecha_liberacion',
    ];

    protected $casts = [
        'fecha_veto' => 'datetime',
        'fecha_fin_veto' => 'datetime',
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
     * Verificar si un RUN está vetado (considera fecha_fin_veto si existe)
     */
    public static function estaVetado($run)
    {
        return self::vetoActivo($run) !== null;
    }

    /**
     * Obtener el veto activo de un RUN (libera automáticamente si el plazo venció)
     */
    public static function vetoActivo($run)
    {
        $veto = self::where('run_vetado', $run)
            ->where('estado', 'activo')
            ->orderBy('fecha_veto', 'desc')
            ->first();

        if (!$veto) {
            return null;
        }

        // Si tiene fecha_fin_veto y ya expiró, liberarlo automáticamente
        if ($veto->fecha_fin_veto && now()->greaterThanOrEqualTo($veto->fecha_fin_veto)) {
            $veto->update([
                'estado' => 'liberado',
                'liberado_por' => 'Sistema (Cumplimiento de sanción)',
                'fecha_liberacion' => now(),
            ]);
            return null;
        }

        return $veto;
    }

    /**
     * Aplicar sanción/bloqueo automático por exceder el tiempo de 2 horas (por defecto 7 días)
     */
    public static function aplicarVetoPorTiempoExcedido($run, $idReserva = null, $minutosTranscurridos = null, $diasSancion = 7)
    {
        $vetoExistente = self::vetoActivo($run);
        if ($vetoExistente) {
            return $vetoExistente;
        }

        $minutosTxt = $minutosTranscurridos ? " ({$minutosTranscurridos} min transcurridos)" : " (>120 min)";
        $observacion = "Sanción automática (1 semana): Excedió el tiempo límite de 2 horas en sala de estudio{$minutosTxt}. Bloqueo por {$diasSancion} días.";

        return self::create([
            'run_vetado' => $run,
            'tipo_veto' => 'individual',
            'id_reserva_origen' => $idReserva,
            'observacion' => $observacion,
            'estado' => 'activo',
            'vetado_por' => 'Sistema (Tiempo Excedido)',
            'fecha_veto' => now(),
            'fecha_fin_veto' => now()->addDays($diasSancion),
        ]);
    }
}
