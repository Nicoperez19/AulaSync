<?php

namespace App\Events;

use App\Models\Asistencia;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Evento disparado cuando se registra una asistencia
 * 
 * Este evento se transmite en tiempo real a través de broadcasting
 * para actualizar los contadores de ocupación en el frontend.
 */
class AttendanceRegistered implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $roomId;
    public $reservationId;
    public $attendance;
    public $currentOccupancy;
    public $roomCapacity;
    public $instructorInfo;

    /**
     * Create a new event instance.
     *
     * @param string $roomId ID de la sala
     * @param string $reservationId ID de la reserva
     * @param Asistencia $attendance Instancia de asistencia registrada
     * @param int $currentOccupancy Ocupación actual (número de asistentes)
     * @param int|null $roomCapacity Capacidad máxima de la sala
     * @param array|null $instructorInfo Información del profesor/solicitante
     */
    public function __construct(
        string $roomId,
        string $reservationId,
        Asistencia $attendance,
        int $currentOccupancy,
        ?int $roomCapacity = null,
        ?array $instructorInfo = null
    ) {
        $this->roomId = $roomId;
        $this->reservationId = $reservationId;
        $this->attendance = $attendance;
        $this->currentOccupancy = $currentOccupancy;
        $this->roomCapacity = $roomCapacity;
        $this->instructorInfo = $instructorInfo;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('room.' . $this->roomId),
        ];
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'attendance.registered';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'room_id' => $this->roomId,
            'reservation_id' => $this->reservationId,
            'attendance' => [
                'id' => $this->attendance->id,
                'student_id' => $this->attendance->rut_asistente,
                'student_name' => $this->attendance->nombre_asistente,
                'arrival_time' => $this->attendance->hora_llegada,
                'registered_at' => $this->attendance->created_at->toIso8601String(),
            ],
            'occupancy' => [
                'current' => $this->currentOccupancy,
                'capacity' => $this->roomCapacity,
                'percentage' => $this->roomCapacity 
                    ? round(($this->currentOccupancy / $this->roomCapacity) * 100, 2)
                    : null,
            ],
            'instructor' => $this->instructorInfo,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Determine if this event should broadcast.
     *
     * @return bool
     */
    public function shouldBroadcast(): bool
    {
        // Siempre transmitir cuando se registre una asistencia
        return true;
    }
}
