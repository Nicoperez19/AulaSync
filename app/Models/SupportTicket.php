<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportTicket extends Model
{
    /**
     * Stored in the central database, shared across all tenants.
     */
    protected $connection = 'mysql';

    protected $fillable = [
        'user_id',
        'id_sede',
        'title',
        'description',
        'status',
        'priority',
        'assigned_to',
        'closed_at',
    ];

    protected $casts = [
        'closed_at' => 'datetime',
    ];

    // ─── Status helpers ──────────────────────────────────────────────────────

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'open'        => 'Abierto',
            'in_progress' => 'En proceso',
            'closed'      => 'Cerrado',
            default       => $this->status,
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'open'        => 'blue',
            'in_progress' => 'yellow',
            'closed'      => 'green',
            default       => 'gray',
        };
    }

    public function priorityLabel(): string
    {
        return match ($this->priority) {
            'low'    => 'Baja',
            'medium' => 'Media',
            'high'   => 'Alta',
            default  => $this->priority,
        };
    }

    public function priorityColor(): string
    {
        return match ($this->priority) {
            'low'    => 'gray',
            'medium' => 'yellow',
            'high'   => 'red',
            default  => 'gray',
        };
    }

    // ─── Relaciones ───────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(SupportTicketReply::class, 'ticket_id')->orderBy('created_at');
    }
}
