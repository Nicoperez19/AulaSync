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
     * Registra la asistencia de una clase
     * 
     * Recibe:
     * - id_reserva: ID de la reserva
     * - hora_termino: Hora de término de la clase
     * - lista_asistencia: Array con los asistentes
     *   - rut: RUT sin dígito verificador
     *   - nombre: Nombre del asistente
     *   - hora_llegada: Hora de llegada
     * - contenido_visto: Contenido visto en la clase (opcional)
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
                'hora_termino' => 'required|date_format:H:i:s',
                'lista_asistencia' => 'required|array|min:1',
                'lista_asistencia.*.rut' => 'required|string',
                'lista_asistencia.*.nombre' => 'required|string',
                'lista_asistencia.*.hora_llegada' => 'required|date_format:H:i:s',
                'contenido_visto' => 'nullable|string'
            ], [
                'id_reserva.required' => 'El ID de reserva es obligatorio',
                'id_reserva.exists' => 'La reserva no existe',
                'hora_termino.required' => 'La hora de término es obligatoria',
                'hora_termino.date_format' => 'La hora de término debe tener el formato HH:MM:SS',
                'lista_asistencia.required' => 'La lista de asistencia es obligatoria',
                'lista_asistencia.min' => 'Debe haber al menos un asistente',
                'lista_asistencia.*.rut.required' => 'El RUT del asistente es obligatorio',
                'lista_asistencia.*.nombre.required' => 'El nombre del asistente es obligatorio',
                'lista_asistencia.*.hora_llegada.required' => 'La hora de llegada es obligatoria',
                'lista_asistencia.*.hora_llegada.date_format' => 'La hora de llegada debe tener el formato HH:MM:SS'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verificar que la reserva existe y está activa
            $reserva = Reserva::with(['espacio', 'profesor'])->find($request->id_reserva);
            
            if (!$reserva) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reserva no encontrada'
                ], 404);
            }

            // Iniciar transacción
            DB::beginTransaction();

            // Actualizar la reserva con la hora de salida
            $reserva->hora_salida = $request->hora_termino;
            $reserva->estado = 'finalizada';
            $reserva->save();

            // Registrar asistencias
            $asistenciasRegistradas = [];
            $contenidoVisto = $request->contenido_visto ?? 'Sin información adicionada';

            foreach ($request->lista_asistencia as $asistente) {
                $asistencia = Asistencia::create([
                    'id_reserva' => $request->id_reserva,
                    'rut_asistente' => $asistente['rut'],
                    'nombre_asistente' => $asistente['nombre'],
                    'hora_llegada' => $asistente['hora_llegada'],
                    'hora_termino' => $request->hora_termino,
                    'contenido_visto' => $contenidoVisto
                ]);

                $asistenciasRegistradas[] = [
                    'id' => $asistencia->id,
                    'rut' => $asistencia->rut_asistente,
                    'nombre' => $asistencia->nombre_asistente,
                    'hora_llegada' => $asistencia->hora_llegada,
                    'hora_termino' => $asistencia->hora_termino,
                    'contenido_visto' => $asistencia->contenido_visto
                ];
            }

            // Cambiar estado del espacio a disponible
            if ($reserva->espacio) {
                $reserva->espacio->estado = 'Disponible';
                $reserva->espacio->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Asistencia registrada exitosamente',
                'data' => [
                    'reserva' => [
                        'id' => $reserva->id_reserva,
                        'espacio' => $reserva->espacio->nombre_espacio ?? 'N/A',
                        'fecha' => $reserva->fecha_reserva,
                        'hora_inicio' => $reserva->hora,
                        'hora_termino' => $reserva->hora_salida,
                        'profesor' => $reserva->profesor->name ?? 'N/A'
                    ],
                    'asistencias_registradas' => $asistenciasRegistradas,
                    'total_asistentes' => count($asistenciasRegistradas),
                    'contenido_visto' => $contenidoVisto
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la asistencia',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
