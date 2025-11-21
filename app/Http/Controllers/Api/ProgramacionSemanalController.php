<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Planificacion_Asignatura;
use App\Models\Asistencia;
use App\Models\Reserva;
use App\Models\Espacio;
use App\Helpers\SemesterHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProgramacionSemanalController extends Controller
{
    /**
     * GET /api/programacion-semanal/{id_espacio}
     * Consulta la programación semanal por sala de clases
     * 
     * @param string $id_espacio
     * @return \Illuminate\Http\JsonResponse
     */
    public function obtenerProgramacionSemanal($id_espacio)
    {
        try {
            // Verificar que el espacio existe
            $espacio = Espacio::find($id_espacio);
            
            if (!$espacio) {
                return response()->json([
                    'success' => false,
                    'message' => 'Espacio no encontrado'
                ], 404);
            }

            // Obtener el período actual
            $periodo = SemesterHelper::getCurrentPeriod();

            // Obtener la programación semanal (con distinct para evitar duplicados)
            $programacion = Planificacion_Asignatura::with([
                'modulo',
                'asignatura.profesor',
                'asignatura.carrera',
                'espacio'
            ])
            ->where('id_espacio', $id_espacio)
            ->whereHas('horario', function($query) use ($periodo) {
                $query->where('periodo', $periodo);
            })
            ->get()
            ->unique(function($item) {
                // Clave única: modulo + asignatura + profesor
                return $item->id_modulo . '-' . $item->id_asignatura . '-' . ($item->asignatura->run_profesor ?? 'null');
            })
            ->groupBy(function($item) {
                return $item->modulo->dia;
            })
            ->map(function($items, $dia) {
                // Transformar cada planificación a un formato intermedio con número de módulo
                $rows = $items->map(function($planificacion) {
                    $idModulo = $planificacion->modulo->id_modulo ?? '';
                    $parts = explode('.', $idModulo);
                    $numero = isset($parts[1]) ? intval($parts[1]) : null;

                    return [
                        'modulo_num' => $numero,
                        'hora_inicio' => $planificacion->modulo->hora_inicio,
                        'hora_termino' => $planificacion->modulo->hora_termino,
                        'asignatura_id' => $planificacion->asignatura->id_asignatura ?? null,
                        'asignatura' => [
                            'codigo' => $planificacion->asignatura->codigo_asignatura,
                            'nombre' => $planificacion->asignatura->nombre_asignatura,
                            'seccion' => $planificacion->asignatura->seccion,
                            'carrera' => $planificacion->asignatura->carrera ? [
                                'id' => $planificacion->asignatura->carrera->id_carrera,
                                'nombre' => $planificacion->asignatura->carrera->nombre,
                            ] : null,
                        ],
                        'profesor' => [
                            'run' => $planificacion->asignatura->profesor->run_profesor ?? null,
                            'nombre' => $planificacion->asignatura->profesor->name ?? 'Sin asignar',
                            'email' => $planificacion->asignatura->profesor->email ?? null,
                        ]
                    ];
                })->sortBy('modulo_num')->values()->all();

                // Agrupar módulos consecutivos que pertenecen a la misma asignatura/profesor
                $result = [];
                foreach ($rows as $r) {
                    if (empty($result)) {
                        $result[] = [
                            'modulo' => $r['modulo_num'],
                            'cantidad_modulos' => 1,
                            'modulo_fin' => $r['modulo_num'],
                            'hora_inicio' => $r['hora_inicio'],
                            'hora_termino' => $r['hora_termino'],
                            'profesor_a_cargo' => $r['profesor'],
                            'asignatura' => $r['asignatura']
                        ];
                        continue;
                    }

                    $lastIndex = count($result) - 1;
                    $last = $result[$lastIndex];

                    // Si es la misma asignatura y profesor y el módulo es consecutivo, extender el bloque
                    if ($last['asignatura']['codigo'] === $r['asignatura']['codigo']
                        && $last['profesor_a_cargo']['run'] === $r['profesor']['run']
                        && $r['modulo_num'] === ($last['modulo_fin'] + 1)) {

                        $result[$lastIndex]['cantidad_modulos'] += 1;
                        $result[$lastIndex]['modulo_fin'] = $r['modulo_num'];
                        $result[$lastIndex]['hora_termino'] = $r['hora_termino'];
                    } else {
                        // Nuevo bloque
                        $result[] = [
                            'modulo' => $r['modulo_num'],
                            'cantidad_modulos' => 1,
                            'modulo_fin' => $r['modulo_num'],
                            'hora_inicio' => $r['hora_inicio'],
                            'hora_termino' => $r['hora_termino'],
                            'profesor_a_cargo' => $r['profesor'],
                            'asignatura' => $r['asignatura']
                        ];
                    }
                }

                return $result;
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'espacio' => [
                        'id' => $espacio->id_espacio,
                        'nombre' => $espacio->nombre_espacio,
                        'tipo' => $espacio->tipo_espacio,
                        'capacidad_maxima' => $espacio->capacidad_maxima,
                    ],
                    'periodo' => $periodo,
                    'programacion_semanal' => $programacion
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la programación semanal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/asistencia
     * Registra la asistencia de una clase y finaliza la reserva
     * 
     * Este endpoint está diseñado para ser consumido por aplicaciones nativas externas.
     * 
     * Recibe:
     * - id_reserva: ID de la reserva (string, requerido)
     * - lista_asistencia: Array con los asistentes (array, requerido, mínimo 1)
     *   - rut: RUT sin dígito verificador (string, requerido)
     *   - nombre: Nombre completo del asistente (string, requerido)
     *   - hora_llegada: Hora de llegada en formato HH:MM:SS (string, requerido)
     *   - observaciones: Observaciones del estudiante (string, opcional)
     * - finalizar_ahora: Si true, finaliza la reserva inmediatamente. Si false, se finaliza a la hora programada (boolean, opcional, default: true)
     * 
     * Ejemplo de request:
     * {
     *   "id_reserva": "R20251027145530123",
     *   "lista_asistencia": [
     *     {
     *       "rut": "12345678",
     *       "nombre": "Juan Pérez",
     *       "hora_llegada": "14:55:00",
     *       "observaciones": "Llegó a tiempo"
     *     },
     *     {
     *       "rut": "87654321",
     *       "nombre": "María González",
     *       "hora_llegada": "15:00:00",
     *       "observaciones": ""
     *     }
     *   ],
     *   "finalizar_ahora": true
     * }
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function registrarAsistencia(Request $request)
    {
        try {
            // Validar los datos recibidos
            $validator = Validator::make($request->all(), [
                'id_reserva' => 'required|string|exists:reservas,id_reserva',
                'lista_asistencia' => 'required|array|min:1',
                'lista_asistencia.*.rut' => 'required|string',
                'lista_asistencia.*.nombre' => 'required|string',
                'lista_asistencia.*.hora_llegada' => 'required|date_format:H:i:s',
                'lista_asistencia.*.observaciones' => 'nullable|string',
                'finalizar_ahora' => 'nullable|boolean'
            ], [
                'id_reserva.required' => 'El ID de reserva es obligatorio',
                'id_reserva.exists' => 'La reserva no existe',
                'lista_asistencia.required' => 'La lista de asistencia es obligatoria',
                'lista_asistencia.min' => 'Debe haber al menos un asistente',
                'lista_asistencia.*.rut.required' => 'El RUT del asistente es obligatorio',
                'lista_asistencia.*.nombre.required' => 'El nombre del asistente es obligatorio',
                'lista_asistencia.*.hora_llegada.required' => 'La hora de llegada es obligatoria',
                'lista_asistencia.*.hora_llegada.date_format' => 'La hora de llegada debe tener el formato HH:MM:SS (ej: 14:30:00)'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Obtener la reserva con sus relaciones
            $reserva = Reserva::with(['espacio', 'profesor', 'asignatura'])->find($request->id_reserva);
            
            if (!$reserva) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reserva no encontrada'
                ], 404);
            }

            // Verificar que la reserva esté activa
            if ($reserva->estado === 'finalizada') {
                return response()->json([
                    'success' => false,
                    'message' => 'La reserva ya ha sido finalizada',
                    'data' => [
                        'reserva_id' => $reserva->id_reserva,
                        'estado' => $reserva->estado,
                        'hora_salida' => $reserva->hora_salida
                    ]
                ], 400);
            }

            // Determinar si se debe finalizar ahora o no
            $finalizarAhora = $request->input('finalizar_ahora', true);

            // Iniciar transacción
            DB::beginTransaction();

            // Registrar asistencias
            $asistenciasRegistradas = [];
            $idAsignatura = $reserva->id_asignatura;

            foreach ($request->lista_asistencia as $asistente) {
                $asistencia = Asistencia::create([
                    'id_reserva' => $request->id_reserva,
                    'id_asignatura' => $idAsignatura,
                    'rut_asistente' => $asistente['rut'],
                    'nombre_asistente' => $asistente['nombre'],
                    'hora_llegada' => $asistente['hora_llegada'],
                    'observaciones' => $asistente['observaciones'] ?? null
                ]);

                $asistenciasRegistradas[] = [
                    'id' => $asistencia->id,
                    'rut' => $asistencia->rut_asistente,
                    'nombre' => $asistencia->nombre_asistente,
                    'hora_llegada' => $asistencia->hora_llegada,
                    'observaciones' => $asistencia->observaciones
                ];
            }

            // Si se debe finalizar ahora, actualizar la reserva y el espacio
            if ($finalizarAhora) {
                $horaActual = now()->format('H:i:s');
                $reserva->hora_salida = $horaActual;
                $reserva->estado = 'finalizada';
                $reserva->save();

                // Cambiar estado del espacio a disponible
                if ($reserva->espacio) {
                    $reserva->espacio->estado = 'Disponible';
                    $reserva->espacio->save();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $finalizarAhora 
                    ? 'Asistencia registrada y reserva finalizada exitosamente' 
                    : 'Asistencia registrada exitosamente. La reserva se finalizará automáticamente',
                'data' => [
                    'reserva' => [
                        'id' => $reserva->id_reserva,
                        'espacio' => [
                            'id' => $reserva->espacio->id_espacio ?? null,
                            'nombre' => $reserva->espacio->nombre_espacio ?? 'N/A',
                            'estado' => $reserva->espacio->estado ?? 'N/A'
                        ],
                        'asignatura' => $reserva->asignatura ? [
                            'id' => $reserva->asignatura->id_asignatura,
                            'codigo' => $reserva->asignatura->codigo_asignatura,
                            'nombre' => $reserva->asignatura->nombre_asignatura,
                            'seccion' => $reserva->asignatura->seccion
                        ] : null,
                        'fecha' => $reserva->fecha_reserva,
                        'hora_inicio' => $reserva->hora,
                        'hora_salida' => $reserva->hora_salida,
                        'estado' => $reserva->estado,
                        'profesor' => [
                            'run' => $reserva->profesor->run_profesor ?? null,
                            'nombre' => $reserva->profesor->name ?? 'N/A'
                        ]
                    ],
                    'asistencias_registradas' => $asistenciasRegistradas,
                    'total_asistentes' => count($asistenciasRegistradas),
                    'finalizada' => $finalizarAhora
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la asistencia',
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    /**
     * GET /api/reservas/activa/{id_espacio}
     * Obtiene la reserva activa de un espacio específico
     * 
     * Este endpoint devuelve la reserva que está actualmente activa para un espacio dado,
     * junto con toda la información relacionada (profesor, asignatura, espacio, etc.)
     * 
     * IMPORTANTE: Un espacio se considera "ocupado" cuando:
     * 1. Tiene una reserva activa en este momento, O
     * 2. El estado del espacio está marcado como "Ocupado" (puede ser ocupación sin reserva)
     * 
     * Ejemplo de uso:
     * GET /api/reservas/activa/TH-03
     * 
     * @param string $id_espacio - ID del espacio (ej: TH-03, TH-LAB1, etc.)
     * @return \Illuminate\Http\JsonResponse
     */
    public function obtenerReservaActiva($id_espacio)
    {
        try {
            // Verificar que el espacio existe
            $espacio = Espacio::find($id_espacio);
            
            if (!$espacio) {
                return response()->json([
                    'success' => false,
                    'message' => 'Espacio no encontrado',
                    'id_espacio' => $id_espacio
                ], 404);
            }

            // Obtener la fecha y hora actuales
            $fechaActual = now()->format('Y-m-d');
            $horaActual = now()->format('H:i:s');

            // Buscar la reserva activa para el espacio
            $reserva = Reserva::with([
                'espacio',
                'profesor',
                'asignatura.profesor',
                'solicitante',
                'asistencias'
            ])
            ->where('id_espacio', $id_espacio)
            ->where('estado', 'activa')
            ->where('fecha_reserva', $fechaActual)
            ->where('hora', '<=', $horaActual)
            ->where(function($query) use ($horaActual) {
                $query->whereNull('hora_salida')
                      ->orWhere('hora_salida', '>=', $horaActual);
            })
            ->orderBy('hora', 'desc')
            ->first();

            // Determinar si el espacio está ocupado
            $espacioOcupado = ($reserva !== null) || ($espacio->estado === 'Ocupado');

            // Si no hay reserva activa
            if (!$reserva) {
                $mensaje = $espacio->estado === 'Ocupado' 
                    ? 'El espacio está ocupado pero no tiene una reserva formal activa' 
                    : 'El espacio está disponible, no hay reserva activa en este momento';

                return response()->json([
                    'success' => true,
                    'message' => $mensaje,
                    'data' => [
                        'espacio' => [
                            'id' => $espacio->id_espacio,
                            'nombre' => $espacio->nombre_espacio,
                            'tipo' => $espacio->tipo_espacio,
                            'estado' => $espacio->estado,
                            'puestos_disponibles' => $espacio->puestos_disponibles,
                            'ocupado' => $espacioOcupado
                        ],
                        'reserva_activa' => null,
                        'fecha_consulta' => $fechaActual,
                        'hora_consulta' => $horaActual,
                        'nota' => $espacio->estado === 'Ocupado' 
                            ? 'El espacio puede estar siendo usado sin una reserva formal' 
                            : null
                    ]
                ], 200);
            }

            // Preparar datos del profesor o solicitante
            $usuarioReserva = null;
            if ($reserva->run_profesor && $reserva->profesor) {
                $usuarioReserva = [
                    'tipo' => 'profesor',
                    'run' => $reserva->profesor->run_profesor,
                    'nombre' => $reserva->profesor->name,
                    'email' => $reserva->profesor->email ?? null,
                    'celular' => $reserva->profesor->celular ?? null
                ];
            } elseif ($reserva->run_solicitante && $reserva->solicitante) {
                $usuarioReserva = [
                    'tipo' => 'solicitante',
                    'run' => $reserva->solicitante->run_solicitante,
                    'nombre' => $reserva->solicitante->nombre,
                    'email' => $reserva->solicitante->correo ?? null,
                    'telefono' => $reserva->solicitante->telefono ?? null
                ];
            }

            // Preparar datos de la asignatura
            $asignaturaData = null;
            if ($reserva->asignatura) {
                $asignaturaData = [
                    'id' => $reserva->asignatura->id_asignatura,
                    'codigo' => $reserva->asignatura->codigo_asignatura,
                    'nombre' => $reserva->asignatura->nombre_asignatura,
                    'seccion' => $reserva->asignatura->seccion ?? null,
                    'profesor_titular' => $reserva->asignatura->profesor ? [
                        'run' => $reserva->asignatura->profesor->run_profesor,
                        'nombre' => $reserva->asignatura->profesor->name
                    ] : null
                ];
            }

            // Preparar datos de asistencia
            $asistenciaData = [
                'total_registrados' => $reserva->asistencias->count(),
                'estudiantes' => $reserva->asistencias->map(function($asistencia) {
                    return [
                        'id' => $asistencia->id,
                        'rut' => $asistencia->rut_asistente,
                        'nombre' => $asistencia->nombre_asistente,
                        'hora_llegada' => $asistencia->hora_llegada,
                        'observaciones' => $asistencia->observaciones
                    ];
                })
            ];

            // Si no hay hora_salida, obtenerla desde la tabla Modulo
            $horaFin = $reserva->hora_salida;
            if (!$horaFin && $reserva->modulos > 0) {
                // Obtener el día de la semana de la reserva
                $diasSemana = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
                $diaReserva = $diasSemana[Carbon::parse($reserva->fecha_reserva)->dayOfWeek];
                
                // Obtener el módulo final desde la tabla Modulo usando id_modulo
                $numeroModuloFinal = $this->extraerNumeroModulo($reserva->hora, $diaReserva, $reserva->modulos);
                if ($numeroModuloFinal) {
                    // Construir id_modulo (ej: "JU.10")
                    $prefijosDias = ['DO', 'LU', 'MA', 'MI', 'JU', 'VI', 'SA'];
                    $diasArray = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
                    $indexDia = array_search($diaReserva, $diasArray);
                    $prefijo = $indexDia !== false ? $prefijosDias[$indexDia] : 'LU';
                    
                    $idModuloFinal = $prefijo . '.' . $numeroModuloFinal;
                    $moduloFinal = \App\Models\Modulo::where('id_modulo', $idModuloFinal)->first();
                    
                    if ($moduloFinal) {
                        $horaFin = $moduloFinal->hora_termino;
                    }
                }
            }

            // Calcular duración de la reserva
            $horaInicio = Carbon::parse($reserva->hora);
            $horaFinCalc = $horaFin ? Carbon::parse($horaFin) : Carbon::parse($horaActual);
            $duracion = $horaInicio->diffInMinutes($horaFinCalc);

            // Preparar datos de inscritos (no asistencia registrada)
            $inscritosData = [];
            if ($reserva->asignatura && $reserva->asignatura->id_asignatura) {
                // Obtener inscritos de la asignatura
                $inscritos = \App\Models\Planificacion_Asignatura::where('id_asignatura', $reserva->asignatura->id_asignatura)
                    ->first();
                if ($inscritos) {
                    $inscritosData = [
                        'total_inscritos' => $inscritos->inscritos ?? 0,
                        'presentes' => $reserva->asistencias->count(),
                        'ausentes' => ($inscritos->inscritos ?? 0) - $reserva->asistencias->count()
                    ];
                }
            }

            // Respuesta exitosa con toda la información
            return response()->json([
                'success' => true,
                'message' => 'Reserva activa encontrada - El espacio está ocupado',
                'data' => [
                    'reserva' => [
                        'id' => $reserva->id_reserva,
                        'tipo' => $reserva->tipo_reserva,
                        'estado' => $reserva->estado,
                        'fecha' => $reserva->fecha_reserva,
                        'hora_inicio' => $reserva->hora,
                        'hora_salida' => $horaFin,
                        'duracion_minutos' => $duracion,
                        'modulos' => $reserva->modulos,
                        'observaciones' => $reserva->observaciones,
                        'creada_el' => $reserva->created_at->format('Y-m-d H:i:s')
                    ],
                    'espacio' => [
                        'id' => $espacio->id_espacio,
                        'nombre' => $espacio->nombre_espacio,
                        'tipo' => $espacio->tipo_espacio,
                        'estado' => $espacio->estado,
                        'puestos_disponibles' => $espacio->puestos_disponibles,
                        'ocupado' => true  // Siempre ocupado si hay reserva activa
                    ],
                    'usuario_reserva' => $usuarioReserva,
                    'asignatura' => $asignaturaData,
                    'inscritos' => $inscritosData,
                    'asistencia_registrada' => $asistenciaData,
                    'fecha_consulta' => $fechaActual,
                    'hora_consulta' => $horaActual
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la reserva activa',
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    /**
     * Extraer el número de módulo final basado en hora inicial y cantidad de módulos
     */
    private function extraerNumeroModulo($horaInicio, $diaActual, $cantidadModulos)
    {
        // Prefijo del día
        $prefijosDias = ['DO', 'LU', 'MA', 'MI', 'JU', 'VI', 'SA'];
        $diasArray = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
        $indexDia = array_search($diaActual, $diasArray);
        $prefijo = $indexDia !== false ? $prefijosDias[$indexDia] : 'LU';
        
        // Buscar el módulo que comienza con esta hora
        for ($i = 1; $i <= 15; $i++) {
            $idModulo = $prefijo . '.' . $i;
            $modulo = \App\Models\Modulo::where('id_modulo', $idModulo)->first();
            
            if ($modulo && $modulo->hora_inicio === $horaInicio) {
                // Calcular el módulo final
                return $i + ($cantidadModulos - 1);
            }
        }

        return null;
    }
}
