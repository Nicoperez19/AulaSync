<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asistencia;
use App\Models\Reserva;
use App\Models\Espacio;
use App\Models\User;
use App\Models\Modulo;
use App\Events\AttendanceRegistered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 * Controlador para registro de asistencia de alumnos mediante escáner QR
 * 
 * Este controlador maneja:
 * 1. Entrada de alumnos a clases planificadas (con reserva activa)
 * 2. Entrada/Salida de alumnos en reservas espontáneas (sin reserva previa)
 * 3. Consulta de estado de asistencia de un alumno
 * 
 * Lógica de módulos aledaños:
 * - Si hay una reserva planificada que abarca varios módulos consecutivos,
 *   se considera una sola clase y el alumno queda presente durante toda ella.
 */
class StudentQRAttendanceController extends Controller
{
    /**
     * Marcar entrada de alumno mediante QR
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * Lógica:
     * 1. Busca si hay reserva activa en el espacio
     * 2. Si hay reserva planificada → registra entrada (queda presente toda la clase)
     * 3. Si no hay reserva → crea asistencia espontánea (debe marcar salida después)
     */
    public function marcarEntrada(Request $request)
    {
        // Validación de entrada
        $validator = Validator::make($request->all(), [
            'rut_alumno' => 'required|string|max:20',
            'id_espacio' => 'required|string|exists:espacios,id_espacio',
            'nombre_alumno' => 'nullable|string|max:255',
        ], [
            'rut_alumno.required' => 'El RUT del alumno es obligatorio',
            'id_espacio.required' => 'El ID del espacio es obligatorio',
            'id_espacio.exists' => 'El espacio especificado no existe',
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

            $rutAlumno = $this->limpiarRut($request->rut_alumno);
            $idEspacio = $request->id_espacio;
            $now = Carbon::now();

            Log::info('Registro de entrada QR alumno iniciado', [
                'rut_alumno' => $rutAlumno,
                'id_espacio' => $idEspacio,
                'timestamp' => $now->toDateTimeString()
            ]);

            // Verificar que el espacio existe
            $espacio = Espacio::find($idEspacio);
            if (!$espacio) {
                return response()->json([
                    'success' => false,
                    'message' => 'El espacio especificado no existe'
                ], 404);
            }

            // Verificar si el alumno ya está presente en alguna sala
            $asistenciaActiva = Asistencia::where('rut_asistente', $rutAlumno)
                ->where('estado', Asistencia::ESTADO_PRESENTE)
                ->whereDate('created_at', $now->toDateString())
                ->first();

            if ($asistenciaActiva) {
                // Si está en el mismo espacio, retornar información
                if ($asistenciaActiva->id_espacio === $idEspacio) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ya tienes asistencia registrada en esta sala',
                        'data' => [
                            'asistencia' => $this->formatearAsistencia($asistenciaActiva),
                            'accion_requerida' => $asistenciaActiva->tipo_entrada === Asistencia::TIPO_ESPONTANEA 
                                ? 'marcar_salida' 
                                : 'ninguna'
                        ]
                    ], 409);
                } else {
                    // Está en otra sala, debe salir primero
                    return response()->json([
                        'success' => false,
                        'message' => 'Ya tienes asistencia activa en otra sala. Debes marcar salida primero.',
                        'data' => [
                            'sala_actual' => $asistenciaActiva->espacio->nombre_espacio ?? $asistenciaActiva->id_espacio,
                            'hora_entrada' => $asistenciaActiva->hora_llegada
                        ]
                    ], 409);
                }
            }

            // Buscar reserva activa en el espacio
            $reservaActiva = $this->buscarReservaActiva($idEspacio, $now);
            
            // Obtener nombre del alumno
            $nombreAlumno = $request->nombre_alumno;
            if (!$nombreAlumno) {
                $user = User::where('run', $rutAlumno)->first();
                $nombreAlumno = $user ? $user->name : 'Alumno #' . $rutAlumno;
            }

            if ($reservaActiva) {
                // === CASO 1: Hay reserva activa (clase planificada o espontánea del profesor) ===
                $asistencia = $this->registrarAsistenciaPlanificada($reservaActiva, $rutAlumno, $nombreAlumno, $idEspacio, $now);
                
                DB::commit();

                // Disparar evento de broadcasting si está disponible
                $this->dispararEventoAsistencia($idEspacio, $reservaActiva->id_reserva, $asistencia, $espacio);

                return response()->json([
                    'success' => true,
                    'message' => 'Asistencia registrada correctamente',
                    'data' => [
                        'tipo' => 'planificada',
                        'asistencia' => $this->formatearAsistencia($asistencia),
                        'reserva' => [
                            'id' => $reservaActiva->id_reserva,
                            'tipo' => $reservaActiva->tipo_reserva,
                            'profesor' => $reservaActiva->profesor->name ?? $reservaActiva->solicitante->nombre ?? 'Desconocido',
                            'asignatura' => $reservaActiva->asignatura->nombre_asignatura ?? 'Uso libre',
                            'hora_fin_estimada' => $this->calcularHoraFinReserva($reservaActiva)
                        ],
                        'espacio' => [
                            'id' => $espacio->id_espacio,
                            'nombre' => $espacio->nombre_espacio
                        ],
                        'accion_requerida' => 'ninguna',
                        'mensaje_usuario' => 'Tu asistencia quedó registrada para toda la clase'
                    ]
                ], 201);
            } else {
                // === CASO 2: No hay reserva activa (entrada espontánea) ===
                $asistencia = $this->registrarAsistenciaEspontanea($rutAlumno, $nombreAlumno, $idEspacio, $now);
                
                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Entrada espontánea registrada. Recuerda marcar tu salida al retirarte.',
                    'data' => [
                        'tipo' => 'espontanea',
                        'asistencia' => $this->formatearAsistencia($asistencia),
                        'espacio' => [
                            'id' => $espacio->id_espacio,
                            'nombre' => $espacio->nombre_espacio
                        ],
                        'accion_requerida' => 'marcar_salida',
                        'mensaje_usuario' => 'No hay clase programada. Debes escanear el QR nuevamente cuando te retires para registrar tu salida.'
                    ]
                ], 201);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al registrar entrada QR alumno', [
                'rut_alumno' => $request->rut_alumno ?? null,
                'id_espacio' => $request->id_espacio ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la entrada',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Marcar salida de alumno mediante QR
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function marcarSalida(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rut_alumno' => 'required|string|max:20',
            'id_espacio' => 'required|string|exists:espacios,id_espacio',
        ], [
            'rut_alumno.required' => 'El RUT del alumno es obligatorio',
            'id_espacio.required' => 'El ID del espacio es obligatorio',
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

            $rutAlumno = $this->limpiarRut($request->rut_alumno);
            $idEspacio = $request->id_espacio;
            $now = Carbon::now();

            // Buscar asistencia activa del alumno en este espacio
            $asistencia = Asistencia::where('rut_asistente', $rutAlumno)
                ->where('id_espacio', $idEspacio)
                ->where('estado', Asistencia::ESTADO_PRESENTE)
                ->whereDate('created_at', $now->toDateString())
                ->first();

            if (!$asistencia) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes una entrada activa en esta sala'
                ], 404);
            }

            // Verificar tipo de entrada
            if ($asistencia->tipo_entrada === Asistencia::TIPO_PLANIFICADA) {
                // Para clases planificadas, verificar si la clase ya terminó
                $reservaActiva = $this->buscarReservaActiva($idEspacio, $now);
                
                if ($reservaActiva && $reservaActiva->id_reserva === $asistencia->id_reserva) {
                    return response()->json([
                        'success' => false,
                        'message' => 'La clase aún está en curso. Tu asistencia quedará registrada hasta que termine.',
                        'data' => [
                            'tipo' => 'planificada',
                            'hora_fin_estimada' => $this->calcularHoraFinReserva($reservaActiva)
                        ]
                    ], 400);
                }
            }

            // Marcar salida
            $asistencia->marcarSalida();

            DB::commit();

            Log::info('Salida QR alumno registrada', [
                'rut_alumno' => $rutAlumno,
                'id_espacio' => $idEspacio,
                'duracion_minutos' => $asistencia->duracion_minutos
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Salida registrada correctamente',
                'data' => [
                    'asistencia' => $this->formatearAsistencia($asistencia),
                    'duracion_minutos' => $asistencia->duracion_minutos,
                    'mensaje_usuario' => 'Tu salida ha sido registrada. ¡Hasta pronto!'
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al registrar salida QR alumno', [
                'rut_alumno' => $request->rut_alumno ?? null,
                'id_espacio' => $request->id_espacio ?? null,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la salida',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Toggle entrada/salida de alumno (escaneo único que detecta estado)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleAsistencia(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rut_alumno' => 'required|string|max:20',
            'id_espacio' => 'required|string|exists:espacios,id_espacio',
            'nombre_alumno' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $rutAlumno = $this->limpiarRut($request->rut_alumno);
        $idEspacio = $request->id_espacio;
        $now = Carbon::now();

        // Buscar asistencia activa del alumno en este espacio
        $asistenciaActiva = Asistencia::where('rut_asistente', $rutAlumno)
            ->where('id_espacio', $idEspacio)
            ->where('estado', Asistencia::ESTADO_PRESENTE)
            ->whereDate('created_at', $now->toDateString())
            ->first();

        if ($asistenciaActiva) {
            // Si es espontánea, marcar salida
            if ($asistenciaActiva->tipo_entrada === Asistencia::TIPO_ESPONTANEA) {
                return $this->marcarSalida($request);
            } else {
                // Si es planificada, informar que debe esperar
                return response()->json([
                    'success' => false,
                    'message' => 'Ya tienes asistencia registrada para esta clase',
                    'data' => [
                        'tipo' => 'planificada',
                        'asistencia' => $this->formatearAsistencia($asistenciaActiva),
                        'accion_requerida' => 'ninguna'
                    ]
                ], 409);
            }
        } else {
            // No tiene asistencia activa, marcar entrada
            return $this->marcarEntrada($request);
        }
    }

    /**
     * Verificar estado de asistencia de un alumno
     * 
     * @param string $rutAlumno
     * @param string|null $idEspacio
     * @return \Illuminate\Http\JsonResponse
     */
    public function verificarEstado($rutAlumno, $idEspacio = null)
    {
        try {
            $rutLimpio = $this->limpiarRut($rutAlumno);
            $now = Carbon::now();

            $query = Asistencia::where('rut_asistente', $rutLimpio)
                ->where('estado', Asistencia::ESTADO_PRESENTE)
                ->whereDate('created_at', $now->toDateString())
                ->with(['espacio', 'reserva', 'asignatura']);

            if ($idEspacio) {
                $query->where('id_espacio', $idEspacio);
            }

            $asistenciaActiva = $query->first();

            if (!$asistenciaActiva) {
                return response()->json([
                    'success' => true,
                    'presente' => false,
                    'message' => 'El alumno no tiene asistencia activa'
                ], 200);
            }

            return response()->json([
                'success' => true,
                'presente' => true,
                'data' => [
                    'asistencia' => $this->formatearAsistencia($asistenciaActiva),
                    'espacio' => [
                        'id' => $asistenciaActiva->id_espacio,
                        'nombre' => $asistenciaActiva->espacio->nombre_espacio ?? null
                    ],
                    'tipo' => $asistenciaActiva->tipo_entrada,
                    'accion_requerida' => $asistenciaActiva->tipo_entrada === Asistencia::TIPO_ESPONTANEA 
                        ? 'marcar_salida' 
                        : 'ninguna'
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error al verificar estado de asistencia', [
                'rut_alumno' => $rutAlumno,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al verificar el estado'
            ], 500);
        }
    }

    /**
     * Obtener historial de asistencias de un alumno
     * 
     * @param Request $request
     * @param string $rutAlumno
     * @return \Illuminate\Http\JsonResponse
     */
    public function historialAlumno(Request $request, $rutAlumno)
    {
        try {
            $rutLimpio = $this->limpiarRut($rutAlumno);
            $fechaInicio = $request->query('fecha_inicio', Carbon::now()->subDays(30)->toDateString());
            $fechaFin = $request->query('fecha_fin', Carbon::now()->toDateString());
            $limite = min($request->query('limite', 50), 100);

            $asistencias = Asistencia::where('rut_asistente', $rutLimpio)
                ->whereBetween('created_at', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
                ->with(['espacio', 'reserva.asignatura', 'asignatura'])
                ->orderBy('created_at', 'desc')
                ->limit($limite)
                ->get()
                ->map(function ($asistencia) {
                    return $this->formatearAsistencia($asistencia);
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'rut_alumno' => $rutLimpio,
                    'periodo' => [
                        'inicio' => $fechaInicio,
                        'fin' => $fechaFin
                    ],
                    'total' => $asistencias->count(),
                    'asistencias' => $asistencias
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error al obtener historial de asistencias', [
                'rut_alumno' => $rutAlumno,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el historial'
            ], 500);
        }
    }

    /**
     * Obtener lista de alumnos presentes en un espacio
     * 
     * @param string $idEspacio
     * @return \Illuminate\Http\JsonResponse
     */
    public function alumnosPresentesEnEspacio($idEspacio)
    {
        try {
            $espacio = Espacio::find($idEspacio);
            if (!$espacio) {
                return response()->json([
                    'success' => false,
                    'message' => 'Espacio no encontrado'
                ], 404);
            }

            $now = Carbon::now();

            $presentes = Asistencia::where('id_espacio', $idEspacio)
                ->where('estado', Asistencia::ESTADO_PRESENTE)
                ->whereDate('created_at', $now->toDateString())
                ->with(['reserva'])
                ->orderBy('hora_llegada', 'asc')
                ->get()
                ->map(function ($asistencia) {
                    return [
                        'rut' => $asistencia->rut_asistente,
                        'nombre' => $asistencia->nombre_asistente,
                        'hora_entrada' => Carbon::parse($asistencia->hora_llegada)->format('H:i'),
                        'tipo_entrada' => $asistencia->tipo_entrada
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'espacio' => [
                        'id' => $espacio->id_espacio,
                        'nombre' => $espacio->nombre_espacio,
                        'capacidad' => $espacio->puestos_disponibles
                    ],
                    'ocupacion_actual' => $presentes->count(),
                    'alumnos_presentes' => $presentes
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error al obtener alumnos presentes', [
                'id_espacio' => $idEspacio,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la información'
            ], 500);
        }
    }

    // ==================== MÉTODOS PRIVADOS ====================

    /**
     * Limpiar RUT (quitar puntos y guión, normalizar)
     */
    private function limpiarRut(string $rut): string
    {
        return preg_replace('/[^0-9kK]/', '', strtoupper($rut));
    }

    /**
     * Buscar reserva activa en un espacio para el momento actual
     * Considera módulos aledaños/consecutivos como una sola clase
     */
    private function buscarReservaActiva(string $idEspacio, Carbon $now): ?Reserva
    {
        // Primero buscar reserva directamente activa
        $reserva = Reserva::where('id_espacio', $idEspacio)
            ->where('estado', 'activa')
            ->where('fecha_reserva', $now->toDateString())
            ->with(['profesor', 'solicitante', 'asignatura', 'espacio'])
            ->first();

        if ($reserva) {
            return $reserva;
        }

        // Si no hay reserva activa, buscar por planificación (módulos programados)
        $diaActual = strtolower($now->locale('es')->isoFormat('dddd'));
        $horaActualStr = $now->format('H:i:s');

        // Buscar si hay una clase planificada en este momento
        $planificacion = DB::connection('tenant')->table('planificacion_asignaturas as pa')
            ->join('modulos as m', 'pa.id_modulo', '=', 'm.id_modulo')
            ->join('horarios as h', 'pa.id_horario', '=', 'h.id_horario')
            ->where('pa.id_espacio', $idEspacio)
            ->where('m.dia', $diaActual)
            ->where('m.hora_inicio', '<=', $horaActualStr)
            ->where('m.hora_termino', '>=', $horaActualStr)
            ->select('pa.*', 'h.run_profesor', 'm.hora_inicio', 'm.hora_termino')
            ->first();

        if ($planificacion) {
            // Buscar la reserva asociada a esta planificación
            $reserva = Reserva::where('id_espacio', $idEspacio)
                ->where('fecha_reserva', $now->toDateString())
                ->where('run_profesor', $planificacion->run_profesor)
                ->where('estado', 'activa')
                ->with(['profesor', 'solicitante', 'asignatura', 'espacio'])
                ->first();

            return $reserva;
        }

        return null;
    }

    /**
     * Calcular hora de fin de una reserva considerando módulos aledaños
     */
    private function calcularHoraFinReserva(Reserva $reserva): string
    {
        // Si tiene hora_salida definida, usarla
        if ($reserva->hora_salida) {
            return Carbon::parse($reserva->hora_salida)->format('H:i');
        }

        // Si tiene número de módulos, calcular
        if ($reserva->modulos && $reserva->modulos > 0) {
            $horaInicio = Carbon::parse($reserva->hora);
            return $horaInicio->addMinutes(50 * $reserva->modulos)->format('H:i');
        }

        // Por defecto, 50 minutos desde la hora de inicio
        return Carbon::parse($reserva->hora)->addMinutes(50)->format('H:i');
    }

    /**
     * Registrar asistencia para clase planificada
     */
    private function registrarAsistenciaPlanificada(Reserva $reserva, string $rutAlumno, string $nombreAlumno, string $idEspacio, Carbon $now): Asistencia
    {
        // Verificar si ya existe asistencia para esta reserva
        $asistenciaExistente = Asistencia::where('id_reserva', $reserva->id_reserva)
            ->where('rut_asistente', $rutAlumno)
            ->first();

        if ($asistenciaExistente) {
            throw new \Exception('Ya tienes asistencia registrada para esta clase');
        }

        return Asistencia::create([
            'id_reserva' => $reserva->id_reserva,
            'id_asignatura' => $reserva->id_asignatura,
            'id_espacio' => $idEspacio,
            'rut_asistente' => $rutAlumno,
            'nombre_asistente' => $nombreAlumno,
            'hora_llegada' => $now->format('H:i:s'),
            'hora_salida' => null,
            'tipo_entrada' => Asistencia::TIPO_PLANIFICADA,
            'estado' => Asistencia::ESTADO_PRESENTE,
            'observaciones' => null
        ]);
    }

    /**
     * Registrar asistencia espontánea (sin reserva)
     */
    private function registrarAsistenciaEspontanea(string $rutAlumno, string $nombreAlumno, string $idEspacio, Carbon $now): Asistencia
    {
        return Asistencia::create([
            'id_reserva' => null,
            'id_asignatura' => null,
            'id_espacio' => $idEspacio,
            'rut_asistente' => $rutAlumno,
            'nombre_asistente' => $nombreAlumno,
            'hora_llegada' => $now->format('H:i:s'),
            'hora_salida' => null,
            'tipo_entrada' => Asistencia::TIPO_ESPONTANEA,
            'estado' => Asistencia::ESTADO_PRESENTE,
            'observaciones' => 'Entrada espontánea - sin reserva activa'
        ]);
    }

    /**
     * Formatear asistencia para respuesta JSON
     */
    private function formatearAsistencia(Asistencia $asistencia): array
    {
        return [
            'id' => $asistencia->id,
            'rut_alumno' => $asistencia->rut_asistente,
            'nombre_alumno' => $asistencia->nombre_asistente,
            'hora_entrada' => Carbon::parse($asistencia->hora_llegada)->format('H:i'),
            'hora_salida' => $asistencia->hora_salida 
                ? Carbon::parse($asistencia->hora_salida)->format('H:i') 
                : null,
            'tipo' => $asistencia->tipo_entrada,
            'estado' => $asistencia->estado,
            'fecha' => $asistencia->created_at->format('Y-m-d'),
            'espacio' => $asistencia->id_espacio,
            'reserva_id' => $asistencia->id_reserva,
            'asignatura' => $asistencia->asignatura 
                ? $asistencia->asignatura->nombre_asignatura 
                : null,
            'duracion_minutos' => $asistencia->duracion_minutos
        ];
    }

    /**
     * Disparar evento de asistencia registrada (para broadcasting)
     */
    private function dispararEventoAsistencia(string $idEspacio, string $reservaId, Asistencia $asistencia, Espacio $espacio): void
    {
        try {
            $currentOccupancy = Asistencia::where('id_reserva', $reservaId)
                ->where('estado', Asistencia::ESTADO_PRESENTE)
                ->count();

            if (class_exists(AttendanceRegistered::class)) {
                event(new AttendanceRegistered(
                    $idEspacio,
                    $reservaId,
                    $asistencia,
                    $currentOccupancy,
                    $espacio->puestos_disponibles ?? null,
                    null
                ));
            }
        } catch (\Exception $e) {
            Log::warning('No se pudo disparar evento de asistencia', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
