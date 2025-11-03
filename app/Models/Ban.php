<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Ban extends Model
{
    use HasFactory;

    protected $fillable = [
        'run_solicitante',
        'razon',
        'fecha_inicio',
        'fecha_fin',
        'activo',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'activo' => 'boolean',
    ];

    /**
     * Relación con el solicitante baneado
     */
    public function solicitante()
    {
        return $this->belongsTo(Solicitante::class, 'run_solicitante', 'run_solicitante');
    }

    /**
     * Scope para bans activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para bans vigentes (activos y dentro del período)
     */
    public function scopeVigentes($query)
    {
        $ahora = Carbon::now();
        return $query->where('activo', true)
                    ->where('fecha_inicio', '<=', $ahora)
                    ->where('fecha_fin', '>=', $ahora);
    }

    /**
     * Verificar si el ban está vigente
     */
    public function estaVigente()
    {
        $ahora = Carbon::now();
        return $this->activo && 
               $this->fecha_inicio <= $ahora && 
               $this->fecha_fin >= $ahora;
    }

    /**
     * Verificar si un solicitante está baneado
     */
    public static function estaBaneado($runSolicitante)
    {
        return self::where('run_solicitante', $runSolicitante)
                   ->vigentes()
                   ->exists();
    }

    /**
     * Obtener el ban vigente de un solicitante
     */
    public static function obtenerBanVigente($runSolicitante)
    {
        return self::where('run_solicitante', $runSolicitante)
                   ->vigentes()
                   ->first();
    }

    /**
     * Obtener días restantes del ban
     */
    public function diasRestantes()
    {
        if (!$this->estaVigente()) {
            return 0;
        }
        
        return Carbon::now()->diffInDays($this->fecha_fin, false);
    }
}
