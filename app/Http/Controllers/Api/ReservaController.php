<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reserva;
use App\Models\Espacio;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReservaController extends Controller
{
    public function verificarEspacio($userId, $espacioId)
    {
        try {
            $espacio = Espacio::findOrFail($espacioId);
            $usuario = User::where('id', $userId)
                          ->whereHas('roles', function($query) {
                              $query->where('name', 'profesor');
                          })
                          ->firstOrFail();
            
            // Obtener la hora actual
            $ahora = Carbon::now();
            $diaActual = strtolower($ahora->locale('es')->isoFormat('dddd'));
            
            // Verificar si el espacio está ocupado
            $reservaActiva = Reserva::where('espacio_id', $espacioId)
                ->where('fecha', $ahora->toDateString())
                ->where('hora_inicio', '<=', $ahora->format('H:i:s'))
                ->where('hora_termino', '>=', $ahora->format('H:i:s'))
                ->first();

            if ($reservaActiva) {
                return response()->json([
                    'estado' => 'ocupado',
                    'profesor_nombre' => $reservaActiva->user->name,
                    'hora_termino' => $reservaActiva->hora_termino
                ]);
            }

            // Verificar si el profesor tiene clase programada en este espacio
            $tieneClaseProgramada = DB::table('horarios')
                ->where('user_id', $userId)
                ->where('espacio_id', $espacioId)
                ->where('dia', $diaActual)
                ->where('hora_inicio', '<=', $ahora->format('H:i:s'))
                ->where('hora_termino', '>=', $ahora->format('H:i:s'))
                ->exists();

            return response()->json([
                'estado' => 'disponible',
                'tieneClaseProgramada' => $tieneClaseProgramada
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al verificar el espacio: ' . $e->getMessage()
            ], 500);
        }
    }

    public function registrarIngresoClase(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'espacio_id' => 'required|exists:espacios,id'
            ]);

            // Verificar que el usuario es profesor
            $usuario = User::where('id', $request->user_id)
                          ->whereHas('roles', function($query) {
                              $query->where('name', 'profesor');
                          })
                          ->firstOrFail();

            $ahora = Carbon::now();
            $diaActual = strtolower($ahora->locale('es')->isoFormat('dddd'));

            // Verificar si el profesor tiene clase programada
            $horario = DB::table('horarios')
                ->where('user_id', $request->user_id)
                ->where('espacio_id', $request->espacio_id)
                ->where('dia', $diaActual)
                ->where('hora_inicio', '<=', $ahora->format('H:i:s'))
                ->where('hora_termino', '>=', $ahora->format('H:i:s'))
                ->first();

            if (!$horario) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene una clase programada en este espacio en este momento'
                ], 400);
            }

            // Crear la reserva
            $reserva = Reserva::create([
                'user_id' => $request->user_id,
                'espacio_id' => $request->espacio_id,
                'fecha' => $ahora->toDateString(),
                'hora_inicio' => $ahora->format('H:i:s'),
                'hora_termino' => $horario->hora_termino,
                'tipo' => 'clase'
            ]);

            return response()->json([
                'success' => true,
                'mensaje' => 'Ingreso registrado correctamente',
                'espacio_nombre' => $reserva->espacio->nombre,
                'hora_termino' => $reserva->hora_termino
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el ingreso: ' . $e->getMessage()
            ], 500);
        }
    }

    public function registrarReservaEspontanea(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'espacio_id' => 'required|exists:espacios,id',
                'duracion' => 'required|integer|min:30|max:240'
            ]);

            // Verificar que el usuario es profesor
            $usuario = User::where('id', $request->user_id)
                          ->whereHas('roles', function($query) {
                              $query->where('name', 'profesor');
                          })
                          ->firstOrFail();

            $ahora = Carbon::now();
            $horaTermino = $ahora->copy()->addMinutes($request->duracion);

            // Verificar si el espacio está disponible en el rango de tiempo
            $existeReserva = Reserva::where('espacio_id', $request->espacio_id)
                ->where('fecha', $ahora->toDateString())
                ->where(function ($query) use ($ahora, $horaTermino) {
                    $query->whereBetween('hora_inicio', [$ahora->format('H:i:s'), $horaTermino->format('H:i:s')])
                        ->orWhereBetween('hora_termino', [$ahora->format('H:i:s'), $horaTermino->format('H:i:s')]);
                })
                ->exists();

            if ($existeReserva) {
                return response()->json([
                    'success' => false,
                    'message' => 'El espacio no está disponible en el horario seleccionado'
                ], 400);
            }

            // Crear la reserva
            $reserva = Reserva::create([
                'user_id' => $request->user_id,
                'espacio_id' => $request->espacio_id,
                'fecha' => $ahora->toDateString(),
                'hora_inicio' => $ahora->format('H:i:s'),
                'hora_termino' => $horaTermino->format('H:i:s'),
                'tipo' => 'espontanea'
            ]);

            return response()->json([
                'success' => true,
                'mensaje' => 'Reserva espontánea registrada correctamente',
                'espacio_nombre' => $reserva->espacio->nombre,
                'hora_termino' => $reserva->hora_termino
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la reserva: ' . $e->getMessage()
            ], 500);
        }
    }
}
