<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asistencia;
use App\Models\Reserva;
use App\Models\Espacio;
use App\Models\User;
use App\Events\AttendanceRegistered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 * Controlador para registro de asistencia de estudiantes
 * 
 * Este controlador maneja el registro de asistencia de estudiantes
 * en clases activas, validando la existencia de reservas activas
 * y evitando duplicados.
 */
class AttendanceController extends Controller
{
    /**
     * Registrar la asistencia de un estudiante
     * 
     * Valida que exista una reserva activa en la sala en el momento actual
     * y registra la asistencia del estudiante evitando duplicados.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validación de entrada
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|string|max:20',
            'room_id' => 'required_without:reservation_id|string|exists:espacios,id_espacio',
            'reservation_id' => 'required_without:room_id|string|exists:reservas,id_reserva',
            'student_name' => 'nullable|string|max:255',
        ], [
            'student_id.required' => 'El ID del estudiante es obligatorio',
            'room_id.required_without' => 'Debe proporcionar room_id o reservation_id',
            'room_id.exists' => 'La sala especificada no existe',
            'reservation_id.required_without' => 'Debe proporcionar room_id o reservation_id',
            'reservation_id.exists' => 'La reserva especificada no existe',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $studentId = $request->student_id;
            $roomId = $request->room_id;
            $reservationId = $request->reservation_id;
            $now = Carbon::now();

            // Log para debugging
            Log::info('Registro de asistencia iniciado', [
                'student_id' => $studentId,
                'room_id' => $roomId,
                'reservation_id' => $reservationId,
                'timestamp' => $now->toDateTimeString()
            ]);

            // Buscar reserva activa
            $reserva = null;

            if ($reservationId) {
                // Si se proporciona reservation_id, buscar directamente
                $reserva = Reserva::where('id_reserva', $reservationId)
                    ->where('estado', 'activa')
                    ->with(['espacio', 'profesor', 'solicitante'])
                    ->first();

                if (!$reserva) {
                    return response()->json([
                        'success' => false,
                        'message' => 'La reserva especificada no existe o no está activa'
                    ], 404);
                }

                $roomId = $reserva->id_espacio;
            } else {
                // Buscar por sala y horario actual
                $reserva = Reserva::where('id_espacio', $roomId)
                    ->where('estado', 'activa')
                    ->where('fecha_reserva', $now->toDateString())
                    ->with(['espacio', 'profesor', 'solicitante'])
                    ->get()
                    ->filter(function ($res) use ($now) {
                        // Validar que la hora actual esté dentro del rango de la reserva
                        $horaReserva = Carbon::parse($res->fecha_reserva . ' ' . $res->hora);
                        $duracionModulos = $res->modulos ?? 1;
                        $horaFin = $horaReserva->copy()->addMinutes(50 * $duracionModulos);
                        
                        return $now->between($horaReserva, $horaFin);
                    })
                    ->first();

                if (!$reserva) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No hay una reserva activa en esta sala en este momento',
                        'details' => [
                            'room_id' => $roomId,
                            'current_time' => $now->format('H:i:s'),
                            'current_date' => $now->format('Y-m-d')
                        ]
                    ], 404);
                }
            }

            // Verificar si el espacio existe
            $espacio = Espacio::find($roomId);
            if (!$espacio) {
                return response()->json([
                    'success' => false,
                    'message' => 'La sala especificada no existe'
                ], 404);
            }

            // Verificar duplicados - evitar que el mismo estudiante se registre dos veces en la misma clase
            $asistenciaExistente = Asistencia::where('id_reserva', $reserva->id_reserva)
                ->where('rut_asistente', $studentId)
                ->first();

            if ($asistenciaExistente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este estudiante ya tiene registrada su asistencia para esta clase',
                    'attendance' => [
                        'id' => $asistenciaExistente->id,
                        'registered_at' => $asistenciaExistente->hora_llegada,
                        'created_at' => $asistenciaExistente->created_at->format('Y-m-d H:i:s')
                    ]
                ], 409); // 409 Conflict
            }

            // Obtener nombre del estudiante (si existe en la tabla users)
            $studentName = $request->student_name;
            if (!$studentName) {
                $user = User::where('run', $studentId)->first();
                $studentName = $user ? $user->name : 'Estudiante #' . $studentId;
            }

            // Determinar la asignatura asociada si existe
            $asignaturaId = null;
            if ($reserva->tipo_reserva === 'clase') {
                // Buscar la planificación asociada a esta reserva
                $planificacion = DB::connection('tenant')->table('planificacion_asignaturas as pa')
                    ->join('modulos as m', 'pa.id_modulo', '=', 'm.id_modulo')
                    ->where('pa.id_espacio', $roomId)
                    ->where('m.dia', $now->dayOfWeek)
                    ->where('m.hora_inicio', '<=', $now->format('H:i:s'))
                    ->where('m.hora_termino', '>=', $now->format('H:i:s'))
                    ->select('pa.id_asignatura')
                    ->first();

                if ($planificacion) {
                    $asignaturaId = $planificacion->id_asignatura;
                }
            }

            // Registrar la asistencia
            $asistencia = Asistencia::create([
                'id_reserva' => $reserva->id_reserva,
                'id_asignatura' => $asignaturaId,
                'rut_asistente' => $studentId,
                'nombre_asistente' => $studentName,
                'hora_llegada' => $now->format('H:i:s'),
                'observaciones' => null,
            ]);

            // Contar asistencias actuales para esta reserva
            $currentOccupancy = Asistencia::where('id_reserva', $reserva->id_reserva)->count();

            // Preparar información del profesor/solicitante
            $instructorInfo = null;
            if ($reserva->run_profesor && $reserva->profesor) {
                $instructorInfo = [
                    'type' => 'profesor',
                    'name' => $reserva->profesor->name,
                    'id' => $reserva->run_profesor
                ];
            } elseif ($reserva->run_solicitante && $reserva->solicitante) {
                $instructorInfo = [
                    'type' => 'solicitante',
                    'name' => $reserva->solicitante->nombre,
                    'id' => $reserva->run_solicitante
                ];
            }

            DB::commit();

            // Disparar evento de broadcasting
            event(new AttendanceRegistered(
                $roomId,
                $reserva->id_reserva,
                $asistencia,
                $currentOccupancy,
                $espacio->puestos_disponibles ?? null,
                $instructorInfo
            ));

            Log::info('Asistencia registrada exitosamente', [
                'attendance_id' => $asistencia->id,
                'student_id' => $studentId,
                'room_id' => $roomId,
                'reservation_id' => $reserva->id_reserva,
                'current_occupancy' => $currentOccupancy
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Asistencia registrada exitosamente',
                'data' => [
                    'attendance' => [
                        'id' => $asistencia->id,
                        'student_id' => $asistencia->rut_asistente,
                        'student_name' => $asistencia->nombre_asistente,
                        'arrival_time' => $asistencia->hora_llegada,
                        'registered_at' => $asistencia->created_at->format('Y-m-d H:i:s')
                    ],
                    'reservation' => [
                        'id' => $reserva->id_reserva,
                        'room_id' => $roomId,
                        'room_name' => $espacio->nombre_espacio,
                        'date' => $reserva->fecha_reserva,
                        'start_time' => $reserva->hora,
                        'type' => $reserva->tipo_reserva,
                        'instructor' => $instructorInfo
                    ],
                    'occupancy' => [
                        'current' => $currentOccupancy,
                        'capacity' => $espacio->puestos_disponibles
                    ],
                    'subject' => $asignaturaId ? [
                        'id' => $asignaturaId
                    ] : null
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al registrar asistencia', [
                'student_id' => $request->student_id ?? null,
                'room_id' => $request->room_id ?? null,
                'reservation_id' => $request->reservation_id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la asistencia',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Obtener el listado de asistencias de una reserva específica
     * 
     * @param string $reservationId
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($reservationId)
    {
        try {
            $reserva = Reserva::with(['espacio', 'profesor', 'solicitante'])
                ->find($reservationId);

            if (!$reserva) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reserva no encontrada'
                ], 404);
            }

            $asistencias = Asistencia::where('id_reserva', $reservationId)
                ->with('asignatura')
                ->orderBy('hora_llegada', 'asc')
                ->get()
                ->map(function ($asistencia) {
                    return [
                        'id' => $asistencia->id,
                        'student_id' => $asistencia->rut_asistente,
                        'student_name' => $asistencia->nombre_asistente,
                        'arrival_time' => $asistencia->hora_llegada,
                        'registered_at' => $asistencia->created_at->format('Y-m-d H:i:s'),
                        'subject' => $asistencia->asignatura ? [
                            'id' => $asistencia->asignatura->id_asignatura,
                            'name' => $asistencia->asignatura->nombre_asignatura
                        ] : null
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'reservation' => [
                        'id' => $reserva->id_reserva,
                        'room_id' => $reserva->id_espacio,
                        'room_name' => $reserva->espacio->nombre_espacio,
                        'date' => $reserva->fecha_reserva,
                        'start_time' => $reserva->hora,
                        'status' => $reserva->estado
                    ],
                    'attendances' => $asistencias,
                    'total_attendances' => $asistencias->count(),
                    'capacity' => $reserva->espacio->puestos_disponibles
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error al obtener asistencias', [
                'reservation_id' => $reservationId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las asistencias',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }
}
