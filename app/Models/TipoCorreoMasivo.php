<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TipoCorreoMasivo extends Model
{
    use HasFactory;

    protected $table = 'tipos_correos_masivos';

    protected $fillable = [
        'nombre',
        'codigo',
        'descripcion',
        'tipo',
        'frecuencia',
        'activo',
        'configuracion',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'configuracion' => 'array',
    ];

    /**
     * Destinatarios asignados a este tipo de correo
     */
    public function destinatarios(): BelongsToMany
    {
        return $this->belongsToMany(
            DestinatarioCorreo::class,
            'tipo_correo_destinatario',
            'tipo_correo_masivo_id',
            'destinatario_correo_id'
        )
        ->withPivot('habilitado')
        ->withTimestamps();
    }

    /**
     * Solo destinatarios habilitados
     */
    public function destinatariosHabilitados(): BelongsToMany
    {
        return $this->destinatarios()->wherePivot('habilitado', true);
    }

    /**
     * Scope para tipos activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para tipos del sistema
     */
    public function scopeSistema($query)
    {
        return $query->where('tipo', 'sistema');
    }

    /**
     * Scope para tipos personalizados
     */
    public function scopeCustom($query)
    {
        return $query->where('tipo', 'custom');
    }
}
