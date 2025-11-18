<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Reserva;
use App\Models\Espacio;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/*
|--------------------------------------------------------------------------
| Canal Privado de Sala (Room Channel)
|--------------------------------------------------------------------------
|
| Este canal permite a los usuarios autorizados suscribirse a eventos
| en tiempo real relacionados con una sala específica, como registros
| de asistencia y cambios en la ocupación.
|
| Usuarios autorizados:
| - Administradores del sistema
| - Profesores con reserva activa en la sala
| - Usuarios con permisos de visualización de sala
|
*/
Broadcast::channel('room.{roomId}', function ($user, $roomId) {
    // Verificar si el espacio existe
    $espacio = Espacio::find($roomId);
    if (!$espacio) {
        return false;
    }

    // Permitir a administradores
    if ($user->hasRole('admin') || $user->hasRole('super-admin')) {
        return [
            'id' => $user->run,
            'name' => $user->name,
            'role' => 'admin'
        ];
    }

    // Permitir a profesores con reserva activa en esta sala
    $tieneReservaActiva = Reserva::where('id_espacio', $roomId)
        ->where('estado', 'activa')
        ->where('fecha_reserva', now()->toDateString())
        ->where(function ($query) use ($user) {
            $query->where('run_profesor', $user->run)
                  ->orWhere('run_solicitante', $user->run);
        })
        ->exists();

    if ($tieneReservaActiva) {
        return [
            'id' => $user->run,
            'name' => $user->name,
            'role' => 'instructor'
        ];
    }

    // Permitir a usuarios con permiso específico de visualización
    if ($user->can('view-room-attendance')) {
        return [
            'id' => $user->run,
            'name' => $user->name,
            'role' => 'viewer'
        ];
    }

    // Denegar acceso por defecto
    return false;
});
