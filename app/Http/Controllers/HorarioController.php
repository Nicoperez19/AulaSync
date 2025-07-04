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
use Illuminate\Support\Facades\DB;

class HorarioController extends Controller
{
    public function verificarUsuario($run)
    {
        try {
            $usuario = User::select('run', 'name')->where('run', $run)->first();

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
            $espacio = Espacio::select('id_espacio', 'nombre_espacio', 'tipo_espacio', 'estado')->find($idEspacio);

            if (!$espacio) {
                return response()->json([
                    'verificado' => false,
                    'mensaje' => 'Espacio no encontrado'
                ]);
            }

            if ($espacio->estado === 'Ocupado') {
                return response()->json([
                    'verificado' => true,
                    'disponible' => false,
                    'mensaje' => 'El espacio está ocupado',
                    'espacio' => [
                        'id' => $espacio->id_espacio,
                        'nombre' => $espacio->nombre_espacio,
                        'tipo' => $espacio->tipo_espacio
                    ]
                ]);
            }

            $ahora = Carbon::now();
            $diaActual = $ahora->dayOfWeek;
            $horaActual = $ahora->format('H:i:s');

            // Determinar el período actual
            $mesActual = date('n');
            $anioActual = date('Y');
            $semestre = ($mesActual >= 1 && $mesActual <= 7) ? 1 : 2;
            $periodo = $anioActual . '-' . $semestre;
            
            $planificacionActual = Planificacion_Asignatura::select('id_asignatura', 'id_modulo')
                ->where('id_espacio', $idEspacio)
                ->whereHas('horario', function($query) use ($periodo) {
                    $query->where('periodo', $periodo);
                })
                ->whereHas('modulo', function($query) use ($diaActual, $horaActual) {
                    $query->where('dia', $diaActual)
                          ->where('hora_inicio', '<=', $horaActual)
                          ->where('hora_termino', '>=', $horaActual);
                })
                ->with(['modulo:id_modulo,hora_inicio,hora_termino,dia', 'asignatura:id_asignatura,nombre_asignatura'])
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
                    'asignatura' => $planificacionActual->asignatura->nombre_asignatura ?? null,
                    'horario' => ($planificacionActual->modulo->hora_inicio ?? '') . ' - ' . ($planificacionActual->modulo->hora_termino ?? '')
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
        DB::beginTransaction();
        try {
            \Log::info('=== INICIO CREAR RESERVA ===');
            \Log::info('Datos recibidos:', $request->all());
            
            $espacio = Espacio::select('id_espacio', 'estado', 'nombre_espacio')->find($request->id_espacio);
            
            if (!$espacio) {
                DB::rollBack();
                \Log::error('Espacio no encontrado:', ['id_espacio' => $request->id_espacio]);
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Espacio no encontrado'
                ]);
            }

            \Log::info('Espacio encontrado:', [
                'id_espacio' => $espacio->id_espacio,
                'nombre' => $espacio->nombre_espacio,
                'estado_actual' => $espacio->estado
            ]);

            if ($espacio->estado === 'Ocupado') {
                DB::rollBack();
                \Log::warning('Espacio ya está ocupado');
                return response()->json([
                    'success' => false,
                    'mensaje' => '¿Desea devolver las llaves?',
                    'tipo' => 'devolucion',
                    'espacio' => $espacio->nombre_espacio
                ]);
            }

            // Verificar si el usuario ya tiene una reserva activa en cualquier sala
            $yaTieneReserva = Reserva::where('run', $request->run)
                ->where('estado', 'activa')
                ->whereNull('hora_salida')
                ->exists();

            if ($yaTieneReserva) {
                DB::rollBack();
                \Log::warning('Usuario ya tiene reserva activa:', ['run' => $request->run]);
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Ya tienes una reserva activa en otra sala. Debes finalizarla antes de solicitar una nueva.'
                ]);
            }

            $lastReserva = Reserva::select('id_reserva')->orderByDesc('id_reserva')->first();
            $newIdNumber = $lastReserva ? 
                str_pad(intval(substr($lastReserva->id_reserva, 1)) + 1, 3, '0', STR_PAD_LEFT) : 
                '001';
            $newId = 'R' . $newIdNumber;

            \Log::info('Creando nueva reserva:', ['id_reserva' => $newId]);

            $reserva = new Reserva();
            $reserva->id_reserva = $newId;
            $reserva->hora = Carbon::now()->format('H:i:s');
            $reserva->fecha_reserva = Carbon::now()->toDateString();
            $reserva->id_espacio = $request->id_espacio;
            $reserva->run = $request->run;
            $reserva->tipo_reserva = 'directa';
            $reserva->estado = 'activa';
            $reserva->save();

            \Log::info('Reserva creada exitosamente:', [
                'id_reserva' => $reserva->id_reserva,
                'espacio_id' => $reserva->id_espacio,
                'run' => $reserva->run
            ]);

            \Log::info('Estado del espacio antes de actualizar:', ['estado' => $espacio->estado]);
            $espacio->estado = 'Ocupado';
            $espacio->save();
            \Log::info('Estado del espacio después de actualizar:', ['estado' => $espacio->estado]);

            // Verificar que el cambio se guardó correctamente
            $espacioVerificado = Espacio::select('id_espacio', 'estado', 'nombre_espacio')->find($request->id_espacio);
            \Log::info('Verificación del estado del espacio:', [
                'id_espacio' => $espacioVerificado->id_espacio,
                'estado_verificado' => $espacioVerificado->estado
            ]);

            DB::commit();
            \Log::info('=== RESERVA CREADA EXITOSAMENTE ===');
            
            return response()->json([
                'success' => true,
                'reserva' => [
                    'id' => $reserva->id_reserva,
                    'hora' => $reserva->hora,
                    'fecha' => $reserva->fecha_reserva,
                    'espacio' => $espacio->nombre_espacio,
                    'estado' => $espacio->estado
                ],
                'mensaje' => 'Reserva creada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al crear reserva:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al crear reserva: ' . $e->getMessage()
            ], 500);
        }
    }

    public function devolverLlaves(Request $request)
    {
        DB::beginTransaction();
        try {
            $espacio = Espacio::select('id_espacio', 'estado', 'nombre_espacio')->find($request->id_espacio);
            
            if (!$espacio) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Espacio no encontrado'
                ]);
            }

            $reservaActiva = Reserva::select('id_reserva', 'estado', 'hora_salida', 'id_espacio')
                ->where('id_espacio', $request->id_espacio)
                ->where('estado', 'activa')
                ->first();

            if (!$reservaActiva) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'mensaje' => 'No hay una reserva activa para este espacio'
                ]);
            }

            $reservaActiva->estado = 'finalizada';
            $reservaActiva->hora_salida = Carbon::now()->format('H:i:s');
            $reservaActiva->save();

            $espacio->estado = 'Disponible';
            $espacio->save();

            DB::commit();
            return response()->json([
                'success' => true,
                'mensaje' => 'Devolución de llaves registrada exitosamente',
                'reserva' => [
                    'id' => $reservaActiva->id_reserva,
                    'hora_salida' => $reservaActiva->hora_salida,
                    'espacio' => $espacio->nombre_espacio,
                    'estado' => $espacio->estado
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al registrar devolución: ' . $e->getMessage()
            ], 500);
        }
    }
} 