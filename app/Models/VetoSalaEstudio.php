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
     * Asegura que la columna fecha_fin_veto exista en el tenant actual; retorna true si está disponible
     */
    protected static function asegurarColumnaFechaFin(): bool
    {
        try {
            $schema = \Illuminate\Support\Facades\Schema::connection('tenant');
            if (!$schema->hasTable('vetos_sala_estudio')) {
                return false;
            }
            if ($schema->hasColumn('vetos_sala_estudio', 'fecha_fin_veto')) {
                return true;
            }
            $schema->table('vetos_sala_estudio', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->timestamp('fecha_fin_veto')->nullable()->after('fecha_veto');
            });
            return $schema->hasColumn('vetos_sala_estudio', 'fecha_fin_veto');
        } catch (\Throwable $e) {
            return false;
        }
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
        try {
            $veto = self::where('run_vetado', $run)
                ->where('estado', 'activo')
                ->orderBy('fecha_veto', 'desc')
                ->first();

            if (!$veto) {
                return null;
            }

            // Determinar fecha de término del veto (columna fecha_fin_veto o 7 días para sanciones automáticas)
            $fechaFin = $veto->fecha_fin_veto;
            if (!$fechaFin && $veto->fecha_veto && str_contains(strtolower($veto->observacion ?? ''), 'sanción automática')) {
                $fechaFin = $veto->fecha_veto->copy()->addDays(7);
            }

            // Si tiene fecha de término y ya expiró, liberarlo automáticamente
            if ($fechaFin && now()->greaterThanOrEqualTo($fechaFin)) {
                $veto->update([
                    'estado' => 'liberado',
                    'liberado_por' => 'Sistema (Cumplimiento de sanción)',
                    'fecha_liberacion' => now(),
                ]);
                return null;
            }

            return $veto;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Aplicar sanción/bloqueo automático por exceder el tiempo de 2 horas (por defecto 7 días)
     */
    public static function aplicarVetoPorTiempoExcedido($run, $idReserva = null, $minutosTranscurridos = null, $diasSancion = 7)
    {
        try {
            $vetoExistente = self::vetoActivo($run);
            if ($vetoExistente) {
                return $vetoExistente;
            }

            $minutosTxt = $minutosTranscurridos ? " ({$minutosTranscurridos} min transcurridos)" : " (>120 min)";
            $observacion = "Sanción automática (1 semana): Excedió el tiempo límite de 2 horas en sala de estudio{$minutosTxt}. Bloqueo por {$diasSancion} días.";

            $datos = [
                'run_vetado' => $run,
                'tipo_veto' => 'individual',
                'id_reserva_origen' => $idReserva,
                'observacion' => $observacion,
                'estado' => 'activo',
                'vetado_por' => 'Sistema (Tiempo Excedido)',
                'fecha_veto' => now(),
            ];

            if (self::asegurarColumnaFechaFin()) {
                $datos['fecha_fin_veto'] = now()->addDays($diasSancion);
            }

            return self::create($datos);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('No se pudo registrar veto de sala de estudio: ' . $e->getMessage());
            return null;
        }
    }
}
