<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class StudentBan extends Model
{
    use HasFactory;

    protected $fillable = [
        'run',
        'reason',
        'banned_until',
    ];

    protected $casts = [
        'banned_until' => 'datetime',
    ];

    /**
     * Verificar si un usuario está actualmente baneado
     */
    public static function isUserBanned($run): bool
    {
        return self::where('run', $run)
            ->where('banned_until', '>', now())
            ->exists();
    }

    /**
     * Obtener el baneo activo de un usuario
     */
    public static function getActiveBan($run): ?self
    {
        return self::where('run', $run)
            ->where('banned_until', '>', now())
            ->orderBy('banned_until', 'desc')
            ->first();
    }

    /**
     * Verificar si el baneo está activo
     */
    public function isActive(): bool
    {
        return $this->banned_until > now();
    }

    /**
     * Obtener tiempo restante del baneo en formato legible
     */
    public function getRemainingTimeAttribute(): string
    {
        if (!$this->isActive()) {
            return 'Baneo expirado';
        }

        $now = now();
        $diff = $now->diff($this->banned_until);

        if ($diff->days > 0) {
            return $diff->days . ' día' . ($diff->days > 1 ? 's' : '');
        } elseif ($diff->h > 0) {
            return $diff->h . ' hora' . ($diff->h > 1 ? 's' : '');
        } else {
            return $diff->i . ' minuto' . ($diff->i > 1 ? 's' : '');
        }
    }

    /**
     * Limpiar baneos expirados (puede ejecutarse en un job programado)
     */
    public static function cleanExpiredBans(): int
    {
        return self::where('banned_until', '<=', now())->delete();
    }
}
