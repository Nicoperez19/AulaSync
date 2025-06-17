<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Horario;
use App\Models\Planificacion_Asignatura;
use App\Models\Espacio;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HorarioController extends Controller
{
    public function verificarUsuario($run)
    {
        try {
            $usuario = User::where('run', $run)->first();

            if (!$usuario) {
                return response()->json([
                    'verificado' => false,
                    'mensaje' => 'Usuario no encontrado'
                ]);
            }

            return response()->json([
                'verificado' => true,
                'usuario' => [
                    'run' => $usuario->run,
                    'nombre' => $usuario->name
                ],
                'mensaje' => 'Usuario verificado correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'verificado' => false,
                'mensaje' => 'Error al verificar usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    public function verificarEspacio($idEspacio)
    {
        try {
            $espacio = Espacio::find($idEspacio);

            if (!$espacio) {
                return response()->json([
                    'verificado' => false,
                    'mensaje' => 'Espacio no encontrado'
                ]);
            }

            // Verificar si el espacio está ocupado
            if ($espacio->estado === 'ocupado') {
                return response()->json([
                    'verificado' => true,
                    'disponible' => false,
                    'mensaje' => 'El espacio está ocupado'
                ]);
            }

            // Verificar si hay una planificación actual para este espacio
            $ahora = Carbon::now();
            $diaActual = $ahora->dayOfWeek;
            $horaActual = $ahora->format('H:i:s');

            $planificacionActual = Planificacion_Asignatura::where('id_espacio', $idEspacio)
                ->whereHas('modulo', function($query) use ($diaActual, $horaActual) {
                    $query->where('dia', $diaActual)
                          ->where('hora_inicio', '<=', $horaActual)
                          ->where('hora_termino', '>=', $horaActual);
                })
                ->with(['modulo', 'asignatura'])
                ->first();

            return response()->json([
                'verificado' => true,
                'disponible' => true,
                'espacio' => [
                    'id' => $espacio->id_espacio,
                    'nombre' => $espacio->nombre_espacio,
                    'tipo' => $espacio->tipo_espacio
                ],
                'planificacion' => $planificacionActual ? [
                    'asignatura' => $planificacionActual->asignatura->nombre_asignatura,
                    'horario' => $planificacionActual->modulo->hora_inicio . ' - ' . $planificacionActual->modulo->hora_termino
                ] : null,
                'mensaje' => 'Espacio disponible'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'verificado' => false,
                'mensaje' => 'Error al verificar espacio: ' . $e->getMessage()
            ], 500);
        }
    }

    public function crearReserva(Request $request)
    {
        try {
            $espacio = Espacio::find($request->id_espacio);
            
            if (!$espacio) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Espacio no encontrado'
                ]);
            }

            if ($espacio->estado === 'ocupado') {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'El espacio ya está ocupado'
                ]);
            }

            // Crear la reserva
            $reserva = new Reserva();
            $reserva->id_reserva = Str::uuid();
            $reserva->hora = Carbon::now()->format('H:i:s');
            $reserva->fecha_reserva = Carbon::now()->toDateString();
            $reserva->id_espacio = $request->id_espacio;
            $reserva->run = $request->run;
            $reserva->tipo_reserva = 'directa';
            $reserva->estado = 'activa';
            $reserva->save();

            // Actualizar estado del espacio
            $espacio->estado = 'ocupado';
            $espacio->save();

            return response()->json([
                'success' => true,
                'reserva' => [
                    'id' => $reserva->id_reserva,
                    'hora' => $reserva->hora,
                    'fecha' => $reserva->fecha_reserva
                ],
                'mensaje' => 'Reserva creada exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al crear reserva: ' . $e->getMessage()
            ], 500);
        }
    }
} 