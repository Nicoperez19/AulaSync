<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'message',
        'data',
        'read_at',
        'user_run',
        'priority',
        'expires_at',
        'action_url',
        'action_text'
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'expires_at' => 'datetime'
    ];

    // Tipos de notificaciones
    const TYPE_KEY_RETURN = 'key_return';
    const TYPE_RESERVATION = 'reservation';
    const TYPE_SYSTEM = 'system';
    const TYPE_WARNING = 'warning';
    const TYPE_INFO = 'info';

    // Prioridades
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_run', 'run');
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', Carbon::now());
        });
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function markAsRead()
    {
        $this->update(['read_at' => Carbon::now()]);
    }

    public function markAsUnread()
    {
        $this->update(['read_at' => null]);
    }

    public function isRead()
    {
        return !is_null($this->read_at);
    }

    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function getPriorityColorAttribute()
    {
        return [
            self::PRIORITY_LOW => 'text-gray-500',
            self::PRIORITY_MEDIUM => 'text-blue-500',
            self::PRIORITY_HIGH => 'text-orange-500',
            self::PRIORITY_URGENT => 'text-red-500'
        ][$this->priority] ?? 'text-gray-500';
    }

    public function getPriorityIconAttribute()
    {
        return [
            self::PRIORITY_LOW => 'information-circle',
            self::PRIORITY_MEDIUM => 'exclamation',
            self::PRIORITY_HIGH => 'exclamation-triangle',
            self::PRIORITY_URGENT => 'x-circle'
        ][$this->priority] ?? 'information-circle';
    }

    public function getTypeIconAttribute()
    {
        return [
            self::TYPE_KEY_RETURN => 'key',
            self::TYPE_RESERVATION => 'calendar',
            self::TYPE_SYSTEM => 'cog',
            self::TYPE_WARNING => 'exclamation-triangle',
            self::TYPE_INFO => 'information-circle'
        ][$this->type] ?? 'bell';
    }

    public function getTypeColorAttribute()
    {
        return [
            self::TYPE_KEY_RETURN => 'text-yellow-600 bg-yellow-100',
            self::TYPE_RESERVATION => 'text-blue-600 bg-blue-100',
            self::TYPE_SYSTEM => 'text-gray-600 bg-gray-100',
            self::TYPE_WARNING => 'text-orange-600 bg-orange-100',
            self::TYPE_INFO => 'text-green-600 bg-green-100'
        ][$this->type] ?? 'text-gray-600 bg-gray-100';
    }
} 