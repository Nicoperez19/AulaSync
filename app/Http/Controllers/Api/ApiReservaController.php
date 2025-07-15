<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reserva;
use App\Models\Espacio;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ApiReservaController extends Controller
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
            // Validar datos de entrada
            $request->validate([
                'run' => 'required|exists:users,run',
                'espacio_id' => 'required|exists:espacios,id_espacio'
            ]);

            // Obtener la hora actual
            $horaActual = Carbon::now();
            $diaActual = strtolower($horaActual->locale('es')->isoFormat('dddd'));
            $horaActualStr = $horaActual->format('H:i:s');

            // Verificar si el espacio está ocupado
            $espacio = Espacio::findOrFail($request->espacio_id);
            if ($espacio->estado === 'Ocupado') {
                return response()->json([
                    'success' => false,
                    'message' => 'El espacio se encuentra ocupado'
                ], 400);
            }

            // Verificar si tiene clase programada
            $tieneClase = DB::table('planificacion_asignaturas as pa')
                ->join('horarios as h', 'pa.id_horario', '=', 'h.id_horario')
                ->join('modulos as m', 'pa.id_modulo', '=', 'm.id_modulo')
                ->join('asignaturas as a', 'pa.id_asignatura', '=', 'a.id_asignatura')
                ->where('pa.id_espacio', $request->espacio_id)
                ->where('h.run', $request->run)
                ->where('m.dia', $diaActual)
                ->where(function($query) use ($horaActualStr) {
                    $query->where('m.hora_inicio', '<=', $horaActualStr)
                          ->where('m.hora_termino', '>=', $horaActualStr);
                })
                ->select('a.nombre_asignatura', 'm.hora_inicio', 'm.hora_termino')
                ->first();

            // Si no tiene clase programada, permitir uso libre
            if (!$tieneClase) {
                $tieneClase = (object)[
                    'nombre_asignatura' => 'Uso libre',
                    'hora_inicio' => $horaActualStr,
                    'hora_termino' => Carbon::parse($horaActualStr)->addMinutes(50)->format('H:i:s')
                ];
            }

            DB::beginTransaction();
            try {
                // Generar ID de reserva
                $lastReserva = Reserva::orderBy('id_reserva', 'desc')->first();
                $newIdNumber = $lastReserva ? 
                    str_pad(intval(substr($lastReserva->id_reserva, 1)) + 1, 3, '0', STR_PAD_LEFT) : 
                    '001';
                $newId = 'R' . $newIdNumber;

                // Crear la reserva
                $reserva = new Reserva();
                $reserva->id_reserva = $newId;
                $reserva->run = $request->run;
                $reserva->id_espacio = $request->espacio_id;
                $reserva->fecha_reserva = $horaActual->format('Y-m-d');
                $reserva->hora = $horaActualStr;
                $reserva->tipo_reserva = $tieneClase->nombre_asignatura === 'Uso libre' ? 'espontanea' : 'clase';
                $reserva->estado = 'activa';
                $reserva->hora_salida = null;
                $reserva->created_at = $horaActual;
                $reserva->updated_at = $horaActual;
                $reserva->save();

                // Actualizar estado del espacio
                $espacio->estado = 'Ocupado';
                $espacio->save();

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Ingreso registrado correctamente',
                    'espacio_nombre' => $espacio->nombre_espacio,
                    'hora_termino' => $tieneClase->hora_termino,
                    'asignatura' => $tieneClase->nombre_asignatura
                ]);

            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }

        } catch (\Exception $e) {
            \Log::error('Error en registrarIngresoClase: ' . $e->getMessage());
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
            $reserva->updated_at = Carbon::now();
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
                'user_id' => 'required|exists:users,run',
                'espacio_id' => 'required|exists:espacios,id_espacio',
                'modulos' => 'required|array|min:1',
                'modulos.*' => 'required|string'
            ]);

            // Verificar que el usuario es profesor
            $usuario = User::where('run', $request->user_id)
                          ->whereHas('roles', function($query) {
                              $query->where('name', 'profesor');
                          })
                          ->firstOrFail();

            $fechaReserva = now()->format('Y-m-d');
            $horaActual = now()->format('H:i:s');

            $idsModulos = $request->modulos;
            $modulos = \App\Models\Modulo::whereIn('id_modulo', $idsModulos)->orderBy('hora_inicio')->get();

            $horaInicio = $modulos->first()->hora_inicio;
            $horaTermino = $modulos->last()->hora_termino;

            // Verificar si ya existe una reserva para alguno de esos módulos
            $existeReserva = \App\Models\Reserva::where('id_espacio', $request->espacio_id)
                ->where('fecha_reserva', $fechaReserva)
                ->where(function ($query) use ($horaInicio, $horaTermino) {
                    $query->whereBetween('hora', [$horaInicio, $horaTermino]);
                })
                ->exists();

            if ($existeReserva) {
                return response()->json([
                    'success' => false,
                    'message' => 'El espacio no está disponible en el horario seleccionado'
                ], 400);
            }

            // Crear la reserva
            $lastReserva = \App\Models\Reserva::orderBy('id_reserva', 'desc')->first();
            if ($lastReserva) {
                $lastIdNumber = intval(substr($lastReserva->id_reserva, 1));
                $newIdNumber = str_pad($lastIdNumber + 1, 3, '0', STR_PAD_LEFT);
            } else {
                $newIdNumber = '001';
            }
            $newId = 'R' . $newIdNumber;

            $reserva = \App\Models\Reserva::create([
                'id_reserva' => $newId,
                'hora' => $horaInicio,
                'fecha_reserva' => $fechaReserva,
                'id_espacio' => $request->espacio_id,
                'run_profesor' => $request->user_id,
                'tipo_reserva' => 'espontanea',
                'estado' => 'activa',
                'hora_salida' => $horaTermino
            ]);

            return response()->json([
                'success' => true,
                'mensaje' => 'Reserva espontánea registrada correctamente',
                'espacio_nombre' => $reserva->espacio->nombre_espacio ?? '',
                'hora_termino' => $reserva->hora_salida
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la reserva: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getEspacioEstado($id)
    {
        try {
            $espacio = Espacio::findOrFail($id);
            return response()->json([
                'success' => true,
                'estado' => $espacio->estado
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el estado del espacio: ' . $e->getMessage()
            ], 500);
        }
    }

    public function devolverLlaves(Request $request)
    {
        try {
            $request->validate([
                'run' => 'required|exists:users,run',
                'espacio_id' => 'required|exists:espacios,id_espacio'
            ]);

            $usuario = User::where('run', $request->run)->first();
            $espacio = Espacio::where('id_espacio', $request->espacio_id)->first();

            if (!$usuario || !$espacio) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario o espacio no encontrado'
                ], 404);
            }

            // Verificar si el espacio está ocupado
            if ($espacio->estado !== 'Ocupado') {
                return response()->json([
                    'success' => false,
                    'message' => 'El espacio no está ocupado'
                ], 400);
            }

            // Buscar la reserva activa para este espacio
            $reservaActiva = Reserva::where('id_espacio', $request->espacio_id)
                ->where('estado', 'activa')
                ->where('fecha_reserva', Carbon::today())
                ->first();

            if ($reservaActiva) {
                // Actualizar la reserva
                $reservaActiva->update([
                    'estado' => 'finalizada',
                    'hora_salida' => Carbon::now()->format('H:i:s')
                ]);
            }

            // Cambiar el estado del espacio a disponible
            $espacio->update(['estado' => 'Disponible']);

            return response()->json([
                'success' => true,
                'message' => 'Devolución exitosa',
                'data' => [
                    'usuario' => $usuario->name,
                    'espacio' => $espacio->nombre_espacio,
                    'hora_devolucion' => Carbon::now()->format('H:i:s')
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en devolución de llaves: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la devolución: ' . $e->getMessage()
            ], 500);
        }
    }

    // Método migrado desde App\Http\Controllers\ReservaController
    public function getReservaActiva($id)
    {
        try {
            // Primero verificamos el estado del espacio
            $espacio = \App\Models\Espacio::where('id_espacio', $id)->first();
            
            if (!$espacio) {
                return response()->json([
                    'success' => false,
                    'message' => 'Espacio no encontrado'
                ], 404);
            }

            // Si el espacio está ocupado pero no hay reserva activa
            if ($espacio->estado === 'Ocupado') {
                // Buscar la última reserva activa para este espacio
                $ultimaReserva = \App\Models\Reserva::where('id_espacio', $id)
                    ->where('estado', 'activa')
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($ultimaReserva) {
                    return response()->json([
                        'success' => true,
                        'reserva' => [
                            'id' => $ultimaReserva->id_reserva,
                            'profesor_nombre' => $ultimaReserva->user->name,
                            'profesor_email' => $ultimaReserva->user->email,
                            'hora_inicio' => $ultimaReserva->hora,
                            'hora_termino' => $ultimaReserva->hora_salida,
                            'fecha' => $ultimaReserva->fecha_reserva,
                            'espacio_nombre' => $espacio->nombre_espacio,
                            'tipo_reserva' => 'Ocupación sin reserva',
                            'estado_espacio' => 'Ocupado'
                        ]
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'reserva' => [
                        'id' => null,
                        'profesor_nombre' => 'Sin información',
                        'profesor_email' => 'Sin información',
                        'hora_inicio' => null,
                        'hora_termino' => null,
                        'fecha' => null,
                        'espacio_nombre' => $espacio->nombre_espacio,
                        'tipo_reserva' => 'Ocupación sin reserva',
                        'estado_espacio' => 'Ocupado'
                    ]
                ]);
            }

            // Si no está ocupado, buscamos reservas activas
            $reserva = \App\Models\Reserva::where('id_espacio', $id)
                ->where('fecha_reserva', \Carbon\Carbon::today())
                ->where('hora', '<=', \Carbon\Carbon::now()->format('H:i:s'))
                ->where('hora_salida', '>=', \Carbon\Carbon::now()->format('H:i:s'))
                ->with(['user', 'espacio'])
                ->first();

            if (!$reserva) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay reserva activa para este espacio'
                ]);
            }

            return response()->json([
                'success' => true,
                'reserva' => [
                    'id' => $reserva->id_reserva,
                    'profesor_nombre' => $reserva->user->name,
                    'profesor_email' => $reserva->user->email,
                    'hora_inicio' => $reserva->hora,
                    'hora_termino' => $reserva->hora_salida,
                    'fecha' => $reserva->fecha_reserva,
                    'espacio_nombre' => $reserva->espacio->nombre_espacio,
                    'tipo_reserva' => $reserva->tipo_reserva,
                    'estado_espacio' => $espacio->estado
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al obtener reserva activa: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la reserva activa'
            ], 500);
        }
    }
}
