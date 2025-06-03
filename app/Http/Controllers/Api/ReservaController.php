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
                'run' => 'required|exists:users,run',
                'espacio_id' => 'required|exists:espacios,id_espacio'
            ]);

            // Verificar si el usuario es profesor usando Spatie
            $user = User::where('run', $request->run)
                ->whereHas('roles', function($query) {
                    $query->where('name', 'profesor');
                })
                ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'El usuario no es un profesor válido'
                ], 400);
            }

            // Obtener la hora actual
            $horaActual = Carbon::now();
            $diaActual = strtolower($horaActual->locale('es')->isoFormat('dddd'));
            $horaActualStr = $horaActual->format('H:i:s');

            // Verificar si tiene clase programada
            $tieneClase = DB::table('planificacion_asignaturas')
                ->join('horarios', 'planificacion_asignaturas.id_horario', '=', 'horarios.id_horario')
                ->join('modulos', 'planificacion_asignaturas.id_modulo', '=', 'modulos.id_modulo')
                ->where('planificacion_asignaturas.id_espacio', $request->espacio_id)
                ->where('horarios.run', $user->run)
                ->where('modulos.dia', $diaActual)
                ->where('modulos.hora_inicio', '<=', $horaActualStr)
                ->where('modulos.hora_termino', '>=', $horaActualStr)
                ->exists();

            if (!$tieneClase) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene una clase programada en este espacio en este horario'
                ], 400);
            }

            // Actualizar estado del espacio
            $espacio = Espacio::findOrFail($request->espacio_id);
            $espacio->estado = 'Ocupado';
            $espacio->save();

            // Registrar el ingreso
            $lastReserva = Reserva::orderBy('id_reserva', 'desc')->first();
            if ($lastReserva) {
                $lastIdNumber = intval(substr($lastReserva->id_reserva, 1));
                $newIdNumber = str_pad($lastIdNumber + 1, 3, '0', STR_PAD_LEFT);
            } else {
                $newIdNumber = '001';
            }
            $newId = 'R' . $newIdNumber;

            DB::beginTransaction();
            try {
                $reserva = new Reserva();
                $reserva->id_reserva = $newId;
                $reserva->run = $request->run;
                $reserva->id_espacio = $request->espacio_id;
                $reserva->fecha_reserva = $horaActual->format('Y-m-d');
                $reserva->hora = $horaActualStr;
                $reserva->tipo_reserva = 'clase';
                $reserva->estado = 'activa';
                $reserva->save();

                // Asegurar que el estado del espacio se guarde
                $espacio->estado = 'Ocupado';
                $espacio->save();

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Ingreso registrado correctamente',
                    'espacio_nombre' => $espacio->nombre_espacio,
                    'hora_termino' => $horaActual->addMinutes(50)->format('H:i')
                ]);
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el ingreso: ' . $e->getMessage()
            ], 500);
        }
    }

    public function registrarSalidaClase(Request $request)
    {
        try {
            \Log::info('Datos recibidos en registrarSalidaClase:', $request->all());

            // Validar los datos de entrada
            $request->validate([
                'run' => 'required',
                'espacio_id' => 'required'
            ]);

            DB::beginTransaction();

            // Buscar la reserva activa para el espacio sin restricción de fecha
            $reserva = Reserva::where('id_espacio', $request->espacio_id)
                ->where('estado', 'activa')
                ->first();

            \Log::info('Reserva encontrada:', ['reserva' => $reserva]);

            if (!$reserva) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró una reserva activa para este espacio'
                ], 404);
            }

            // Actualizar la reserva
            $reserva->estado = 'finalizada';
            $reserva->hora_salida = Carbon::now()->format('H:i:s');
            $reserva->save();

            // Actualizar el estado del espacio
            $espacio = Espacio::find($request->espacio_id);
            if ($espacio) {
                $espacio->estado = 'Disponible';
                $espacio->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Salida registrada correctamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al registrar salida: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la salida: ' . $e->getMessage()
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
