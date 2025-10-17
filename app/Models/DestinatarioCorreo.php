<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DestinatarioCorreo extends Model
{
    use HasFactory;

    protected $table = 'destinatarios_correos';

    protected $fillable = [
        'user_id',
        'es_externo',
        'email_externo',
        'nombre_externo',
        'rol',
        'cargo',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'es_externo' => 'boolean',
    ];

    /**
     * Usuario asociado al destinatario (solo para destinatarios internos)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'run');
    }

    /**
     * Tipos de correos asignados a este destinatario
     */
    public function tiposCorreos(): BelongsToMany
    {
        return $this->belongsToMany(
            TipoCorreoMasivo::class,
            'tipo_correo_destinatario',
            'destinatario_correo_id',
            'tipo_correo_masivo_id'
        )
        ->withPivot('habilitado')
        ->withTimestamps();
    }

    /**
     * Tipos de correos habilitados para este destinatario
     */
    public function tiposCorreosHabilitados(): BelongsToMany
    {
        return $this->tiposCorreos()->wherePivot('habilitado', true);
    }

    /**
     * Scope para destinatarios activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para búsqueda avanzada
     * Busca por nombre, email, RUN y asignaturas
     */
    public function scopeBuscar($query, $termino)
    {
        if (empty($termino)) {
            return $query;
        }

        return $query->where(function($q) use ($termino) {
            // Buscar en datos del usuario (destinatarios internos)
            $q->whereHas('user', function($subQ) use ($termino) {
                $subQ->where('name', 'like', '%' . $termino . '%')
                     ->orWhere('email', 'like', '%' . $termino . '%')
                     ->orWhere('run', 'like', '%' . $termino . '%');
            })
            // Buscar en destinatarios externos
            ->orWhere('email_externo', 'like', '%' . $termino . '%')
            ->orWhere('nombre_externo', 'like', '%' . $termino . '%')
            // Buscar en rol y cargo
            ->orWhere('rol', 'like', '%' . $termino . '%')
            ->orWhere('cargo', 'like', '%' . $termino . '%');
        });
    }

    /**
     * Scope para filtrar por tipo de usuario (profesor, solicitante, etc)
     */
    public function scopePorTipoUsuario($query, $tipo = null)
    {
        if (!$tipo) {
            return $query;
        }

        return $query->whereHas('user.roles', function($q) use ($tipo) {
            $q->where('name', $tipo);
        });
    }

    /**
     * Obtener nombre completo del destinatario
     */
    public function getNombreCompletoAttribute(): string
    {
        // Si es externo, usar nombre externo
        if ($this->es_externo) {
            $nombre = $this->nombre_externo ?? $this->email_externo ?? 'N/A';
        } else {
            $nombre = $this->user->name ?? 'N/A';
        }
        
        if ($this->rol) {
            $nombre .= " ({$this->rol})";
        }
        return $nombre;
    }

    /**
     * Obtener email del destinatario (interno o externo)
     */
    public function getEmailAttribute(): ?string
    {
        if ($this->es_externo) {
            return $this->email_externo;
        }
        return $this->user->email ?? null;
    }

    /**
     * Obtener información completa del destinatario para búsqueda
     */
    public function getInfoBusquedaAttribute(): string
    {
        $info = [];
        
        if ($this->es_externo) {
            $info[] = $this->nombre_externo ?? 'Sin nombre';
            $info[] = $this->email_externo;
            $info[] = 'Externo';
        } else if ($this->user) {
            $info[] = $this->user->name;
            $info[] = $this->user->email;
            $info[] = 'RUN: ' . $this->user->run;
        }
        
        if ($this->rol) {
            $info[] = 'Rol: ' . $this->rol;
        }
        
        if ($this->cargo) {
            $info[] = $this->cargo;
        }
        
        return implode(' | ', $info);
    }
}