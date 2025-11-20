<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Models\Reserva;
use App\Models\Espacio;
use App\Models\Profesor;
use App\Models\Solicitante;
use App\Models\Asignatura;
use Carbon\Carbon;

class QuickActionsController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:acciones rapidas');
    }

    /**
     * Mostrar menú de acciones rápidas
     */
    public function index()
    {
        return view('quick_actions.index');
    }

    /**
     * Mostrar formulario para crear reserva
     */
    public function crearReserva()
    {
        return view('quick_actions.crear-reserva');
    }

    /**
     * Mostrar gestor de reservas
     */
    public function gestionarReservas()
    {
        return view('quick_actions.gestionar-reservas');
    }

    /**
     * Mostrar gestor de espacios
     */
    public function gestionarEspacios()
    {
        return view('quick_actions.gestionar-espacios');
    }

    /**
     * Obtener datos para el dashboard
     */
    public function getDashboardData()
    {
        try {
            // Estadísticas básicas
            $reservas_hoy = Reserva::whereDate('fecha_reserva', today())->count();
            $espacios_libres = Espacio::where('estado', 'Disponible')->count();
            $espacios_ocupados = Espacio::where('estado', 'Ocupado')->count();
            $espacios_mantencion = Espacio::where('estado', 'Mantenimiento')->count();

            return response()->json([
                'success' => true,
                'reservas_hoy' => $reservas_hoy,
                'espacios_libres' => $espacios_libres,
                'espacios_ocupados' => $espacios_ocupados,
                'espacios_mantencion' => $espacios_mantencion,
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener datos del dashboard: ' . $e->getMessage(),
                'reservas_hoy' => 0,
                'espacios_libres' => 0,
                'espacios_ocupados' => 0,
                'espacios_mantencion' => 0
            ], 500);
        }
    }

    /**
     * Obtener espacios para gestión
     */
    public function getEspacios(Request $request)
    {
        try {
            $query = Espacio::with('piso')
                ->select('id_espacio', 'nombre_espacio', 'tipo_espacio', 'piso_id', 'puestos_disponibles', 'estado')
                ->orderBy('id_espacio');

            // Aplicar filtros si existen
            if ($request->has('estado') && $request->estado) {
                $query->where('estado', $request->estado);
            }

            if ($request->has('piso') && $request->piso) {
                $query->where('piso_id', $request->piso);
            }

            $espaciosRaw = $query->get();

            // Transformar los datos para mantener compatibilidad con el frontend
            $espacios = $espaciosRaw->map(function ($espacio) {
                return [
                    'codigo' => $espacio->id_espacio,
                    'nombre' => $espacio->nombre_espacio,
                    'tipo' => $espacio->tipo_espacio,
                    'piso' => $espacio->piso ? $espacio->piso->numero_piso : $espacio->piso_id,
                    'capacidad' => $espacio->puestos_disponibles,
                    'estado' => $espacio->estado,
                    // Campos originales por si se necesitan
                    'id_espacio' => $espacio->id_espacio,
                    'nombre_espacio' => $espacio->nombre_espacio,
                    'piso_id' => $espacio->piso_id
                ];
            });

            return response()->json([
                'success' => true,
                'espacios' => $espacios,
                'data' => $espacios,
                'count' => $espacios->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener espacios en QuickActions: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar espacios: ' . $e->getMessage(),
                'espacios' => [],
                'error_details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener reservas para gestión
     */
    public function getReservas(Request $request)
    {
        try {
            Log::info('📋 Solicitando reservas desde Quick Actions');

            // Temporalmente sin relaciones para debug
            $query = Reserva::orderBy('fecha_reserva', 'desc')
                ->orderBy('hora');

            // Aplicar filtros si existen
            if ($request->has('estado') && $request->estado) {
                $query->where('estado', $request->estado);
            }

            if ($request->has('fecha') && $request->fecha) {
                $query->whereDate('fecha_reserva', $request->fecha);
            }

            $reservasRaw = $query->get();
            Log::info('📊 Total de reservas encontradas: ' . $reservasRaw->count());

            // Mejorado para incluir más información
            $reservas = $reservasRaw->map(function ($reserva) {
                // Obtener información del responsable
                $nombreResponsable = 'Sin asignar';
                $runResponsable = 'N/A';
                $tipoResponsable = 'desconocido';

                if ($reserva->run_profesor) {
                    $profesor = \App\Models\Profesor::where('run_profesor', $reserva->run_profesor)->first();
                    $nombreResponsable = $profesor ? $profesor->name : $reserva->run_profesor;
                    $runResponsable = $reserva->run_profesor;
                    $tipoResponsable = 'profesor';
                } elseif ($reserva->run_solicitante) {
                    $solicitante = \App\Models\Solicitante::where('run_solicitante', $reserva->run_solicitante)->first();
                    $nombreResponsable = $solicitante ? $solicitante->nombre : $reserva->run_solicitante;
                    $runResponsable = $reserva->run_solicitante;
                    $tipoResponsable = 'solicitante';
                }

                // Obtener información del espacio
                $espacio = \App\Models\Espacio::where('id_espacio', $reserva->id_espacio)->first();
                $nombreEspacio = $espacio ? $espacio->nombre_espacio : 'Espacio desconocido';

                // Obtener información de la asignatura
                $asignaturaInfo = 'Sin asignatura';
                if ($reserva->id_asignatura) {
                    $asignatura = \App\Models\Asignatura::where('id_asignatura', $reserva->id_asignatura)->first();
                    if ($asignatura) {
                        $asignaturaInfo = $asignatura->codigo_asignatura . ' - ' . $asignatura->nombre_asignatura;
                    }
                }

                // Procesar módulos y horarios
                $modulosInfo = $this->procesarModulosYHorarios($reserva);

                // Verificar si fue editada (comparando created_at con updated_at)
                $fueEditada = $reserva->created_at && $reserva->updated_at &&
                             $reserva->updated_at->gt($reserva->created_at->addSeconds(5));

                return [
                    'id' => $reserva->id_reserva,
                    'nombre_responsable' => $nombreResponsable,
                    'run_responsable' => $runResponsable,
                    'tipo_responsable' => $tipoResponsable,
                    'id_espacio' => $reserva->id_espacio,
                    'nombre_espacio' => $nombreEspacio,
                    'asignatura' => $asignaturaInfo,
                    'fecha' => $reserva->fecha_reserva,
                    'hora' => $reserva->hora,
                    'modulos' => $reserva->cant_modulos ?? 1,
                    'modulos_info' => $modulosInfo,
                    'tipo_reserva' => $reserva->tipo_reserva,
                    'estado' => strtolower($reserva->estado ?? 'activa'),
                    'observaciones' => $reserva->observaciones ?? '',
                    'editada' => $fueEditada
                ];
            });

            Log::info('✅ Enviando ' . $reservas->count() . ' reservas procesadas al frontend');

            return response()->json([
                'success' => true,
                'reservas' => $reservas,  // Añadido para consistencia con JavaScript
                'data' => $reservas,      // Mantenemos 'data' por compatibilidad
                'total' => $reservas->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener reservas en QuickActions: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar reservas: ' . $e->getMessage(),
                'reservas' => []
            ], 500);
        }
    }

    /**
     * Buscar personas por RUN para autocompletado
     */
    public function buscarPersonas(Request $request)
    {
        try {
            $termino = $request->get('q', '');

            if (strlen($termino) < 2) {
                return response()->json([
                    'success' => true,
                    'personas' => []
                ]);
            }

            $personas = [];

            // Buscar en profesores (tabla: profesors)
            $profesores = Profesor::where('run_profesor', 'LIKE', '%' . $termino . '%')
                ->orWhere('name', 'LIKE', '%' . $termino . '%')
                ->limit(10)
                ->get();

            foreach ($profesores as $profesor) {
                $personas[] = [
                    'run' => $profesor->run_profesor,
                    'nombre' => $profesor->name,
                    'email' => $profesor->email ?? '',
                    'telefono' => $profesor->celular ?? '',
                    'tipo' => 'profesor',
                    'display' => $profesor->run_profesor . ' - ' . $profesor->name . ' (Profesor)'
                ];
            }

            // Buscar en solicitantes (tabla: solicitantes)
            $solicitantes = Solicitante::where('run_solicitante', 'LIKE', '%' . $termino . '%')
                ->orWhere('nombre', 'LIKE', '%' . $termino . '%')
                ->where('activo', true)
                ->limit(10)
                ->get();

            foreach ($solicitantes as $solicitante) {
                $personas[] = [
                    'run' => $solicitante->run_solicitante,
                    'nombre' => $solicitante->nombre,
                    'email' => $solicitante->correo ?? '',
                    'telefono' => $solicitante->telefono ?? '',
                    'tipo' => 'solicitante',
                    'display' => $solicitante->run_solicitante . ' - ' . $solicitante->nombre . ' (Solicitante)'
                ];
            }

            return response()->json([
                'success' => true,
                'personas' => $personas,
                'count' => count($personas),
                'termino_buscado' => $termino
            ]);

        } catch (\Exception $e) {
            Log::error('Error al buscar personas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar personas: ' . $e->getMessage(),
                'personas' => [],
                'error_details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar asignaturas por código o nombre (autocompletado)
     */
    public function buscarAsignaturas(Request $request)
    {
        try {
            $termino = $request->input('q', '');

            if (strlen($termino) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'El término de búsqueda debe tener al menos 2 caracteres',
                    'asignaturas' => []
                ]);
            }

            Log::info('🔍 Buscando asignaturas con término: ' . $termino);

            // Buscar asignaturas por código o nombre
            $asignaturas = Asignatura::where('codigo_asignatura', 'LIKE', '%' . $termino . '%')
                ->orWhere('nombre_asignatura', 'LIKE', '%' . $termino . '%')
                ->limit(20)
                ->get();

            $resultado = $asignaturas->map(function($asignatura) {
                return [
                    'id_asignatura' => $asignatura->id_asignatura,
                    'codigo_asignatura' => $asignatura->codigo_asignatura,
                    'nombre_asignatura' => $asignatura->nombre_asignatura,
                    'display' => $asignatura->codigo_asignatura . ' - ' . $asignatura->nombre_asignatura
                ];
            });

            Log::info('✅ Asignaturas encontradas: ' . $resultado->count());

            return response()->json([
                'success' => true,
                'asignaturas' => $resultado,
                'count' => $resultado->count(),
                'termino_buscado' => $termino
            ]);

        } catch (\Exception $e) {
            Log::error('Error al buscar asignaturas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar asignaturas: ' . $e->getMessage(),
                'asignaturas' => [],
                'error_details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Procesar creación de nueva reserva
     */
    public function procesarCrearReserva(Request $request)
    {
        try {
            Log::info('📝 Iniciando creación de reserva desde Quick Actions', $request->all());

            // Validar datos básicos - Teléfono es OPCIONAL
            $request->validate([
                'nombre' => 'required|string|max:255',
                'run' => 'required|string|max:20',
                'correo' => 'required|email|max:255',
                'telefono' => 'nullable|string|max:20', // OPCIONAL - puede ser null
                'tipo' => 'required|in:profesor,solicitante,colaborador',
                'id_asignatura' => 'nullable|string|exists:asignaturas,id_asignatura',
                'espacio' => 'required|string|exists:espacios,id_espacio',
                'fecha' => 'required|date',
                'modulo_inicial' => 'required|integer|min:1|max:12',
                'modulo_final' => 'required|integer|min:1|max:12',
                'observaciones' => 'nullable|string|max:500'
            ]);

            Log::info('📝 Datos validados correctamente', [
                'espacio' => $request->espacio,
                'tipo' => $request->tipo
            ]);

            // Validar que si es profesor o colaborador tenga asignatura
            if (($request->tipo === 'profesor' || $request->tipo === 'colaborador') && !$request->id_asignatura) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Debe seleccionar una asignatura para las reservas de profesores'
                ], 400);
            }

            // Verificar que el módulo inicial sea menor o igual al final
            if ($request->modulo_inicial > $request->modulo_final) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'El módulo inicial no puede ser mayor al módulo final'
                ], 400);
            }

            // Verificar que el espacio existe
            $espacio = Espacio::where('id_espacio', $request->espacio)->first();
            if (!$espacio) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'El espacio seleccionado no existe'
                ], 400);
            }

            // Obtener día de la semana de la fecha de la reserva
            $diasSemana = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
            $diaReserva = $diasSemana[\Carbon\Carbon::parse($request->fecha)->dayOfWeek];
            
            // Prefijo del día (ej: "JU" para jueves)
            $prefijosDias = ['DO', 'LU', 'MA', 'MI', 'JU', 'VI', 'SA'];
            $prefijoReserva = $prefijosDias[\Carbon\Carbon::parse($request->fecha)->dayOfWeek];

            // Construir id_modulo inicial (ej: "JU.5")
            $idModuloInicial = $prefijoReserva . '.' . $request->modulo_inicial;
            $idModuloFinal = $prefijoReserva . '.' . $request->modulo_final;

            // Obtener módulo inicial desde la tabla Modulo
            $moduloInicial = \App\Models\Modulo::where('id_modulo', $idModuloInicial)->first();

            if (!$moduloInicial) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'No se encontró el módulo inicial especificado (id_modulo: ' . $idModuloInicial . ')'
                ], 400);
            }

            // Obtener módulo final desde la tabla Modulo
            $moduloFinal = \App\Models\Modulo::where('id_modulo', $idModuloFinal)->first();

            if (!$moduloFinal) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'No se encontró el módulo final especificado (id_modulo: ' . $idModuloFinal . ')'
                ], 400);
            }

            // Calcular hora de inicio y fin correctas desde la tabla Modulo
            $horaInicio = $moduloInicial->hora_inicio;
            $horaFin = $moduloFinal->hora_termino;
            $duracionModulos = $request->modulo_final - $request->modulo_inicial + 1;

            // VALIDAR QUE NO EXISTA UNA RESERVA ACTIVA EN ESE ESPACIO, FECHA Y HORARIO
            $reservaExistente = Reserva::where('id_espacio', $request->espacio)
                ->where('fecha_reserva', $request->fecha)
                ->where('estado', 'activa')
                ->where(function($query) use ($horaInicio, $horaFin) {
                    // Verificar solapamiento de horarios
                    $query->where(function($q) use ($horaInicio, $horaFin) {
                        // La reserva existente empieza antes y termina durante la nueva
                        $q->where('hora', '<=', $horaInicio)
                          ->where('hora_salida', '>', $horaInicio);
                    })->orWhere(function($q) use ($horaInicio, $horaFin) {
                        // La reserva existente empieza durante la nueva
                        $q->where('hora', '>=', $horaInicio)
                          ->where('hora', '<', $horaFin);
                    })->orWhere(function($q) use ($horaInicio, $horaFin) {
                        // La nueva reserva contiene completamente la existente
                        $q->where('hora', '>=', $horaInicio)
                          ->where('hora_salida', '<=', $horaFin);
                    });
                })
                ->first();

            if ($reservaExistente) {
                Log::warning('⚠️ Intento de crear reserva duplicada', [
                    'espacio' => $request->espacio,
                    'fecha' => $request->fecha,
                    'modulos_solicitados' => $request->modulo_inicial . '-' . $request->modulo_final,
                    'reserva_existente' => $reservaExistente->id_reserva
                ]);

                return response()->json([
                    'success' => false,
                    'mensaje' => 'Ya existe una reserva activa para el espacio ' . $request->espacio .
                                ' el día ' . $request->fecha . ' en los módulos solicitados. ' .
                                'Por favor seleccione otro horario o espacio.',
                    'reserva_conflicto' => $reservaExistente->id_reserva
                ], 409); // 409 Conflict
            }

            // Generar ID único para la reserva
            $idReserva = 'RES-' . strtoupper(uniqid());

            // Preparar observaciones con información de creación manual
            $usuario = auth()->user();
            $rangoModulos = "Módulos: " . $request->modulo_inicial . "-" . $request->modulo_final . " | ";
            $observacionesAutomaticas = "RESERVA CREADA MANUALMENTE por " . ($usuario->name ?? 'Administrador') . " el " . now()->format('d/m/Y H:i:s') . " | " . $rangoModulos;
            $observacionesCompletas = $observacionesAutomaticas . ($request->observaciones ?? '');

            // Preparar datos de la reserva
            // Campo modulos es unsignedSmallInteger - calculamos duración en módulos (ya se calculó antes)

            $datosReserva = [
                'id_reserva' => $idReserva,
                'fecha_reserva' => $request->fecha,
                'id_espacio' => $request->espacio,
                'id_asignatura' => $request->id_asignatura,
                'modulos' => $duracionModulos,
                'hora' => $horaInicio,
                'hora_salida' => $horaFin,
                'tipo_reserva' => $request->tipo === 'profesor' || $request->tipo === 'colaborador' ? 'clase' : 'espontanea',
                'estado' => 'activa',
                'observaciones' => $observacionesCompletas,
                'created_at' => now(),
                'updated_at' => now()
            ];

            Log::info('🏢 Datos de reserva preparados:', [
                'id_espacio' => $request->espacio,
                'datos_completos' => $datosReserva
            ]);

            // Asignar responsable según el tipo
            if ($request->tipo === 'profesor' || $request->tipo === 'colaborador') {
                // Buscar o crear profesor
                $profesor = Profesor::where('run_profesor', $request->run)->first();
                if (!$profesor) {
                    // Crear nuevo profesor básico
                    $tipoProfesor = $request->tipo === 'colaborador' ? 'Colaborador' : 'Invitado';
                    $profesor = Profesor::create([
                        'run_profesor' => $request->run,
                        'name' => $request->nombre,
                        'email' => $request->correo,
                        'celular' => $request->telefono, // Puede ser null
                        'tipo_profesor' => $tipoProfesor
                    ]);
                }
                $datosReserva['run_profesor'] = $request->run;
            } else {
                // Buscar o crear solicitante
                $solicitante = Solicitante::where('run_solicitante', $request->run)->first();
                if (!$solicitante) {
                    // Crear nuevo solicitante
                    $solicitante = Solicitante::create([
                        'run_solicitante' => $request->run,
                        'nombre' => $request->nombre,
                        'correo' => $request->correo,
                        'telefono' => $request->telefono, // Puede ser null
                        'tipo_solicitante' => 'visitante',
                        'activo' => true,
                        'fecha_registro' => now()
                    ]);
                }
                $datosReserva['run_solicitante'] = $request->run;
            }

            // Crear la reserva
            $reserva = Reserva::create($datosReserva);

            Log::info('💾 Reserva guardada en BD:', [
                'id_reserva' => $reserva->id_reserva,
                'id_espacio_guardado' => $reserva->id_espacio,
                'fecha_reserva' => $reserva->fecha_reserva,
                'modelo_completo' => $reserva->toArray()
            ]);

            // Verificar si la reserva es actual para ocupar el espacio automáticamente
            $espacioOcupado = $this->ocuparEspacioSiEsReservaActual($reserva);

            Log::info('✅ Reserva creada exitosamente', [
                'id_reserva' => $idReserva,
                'espacio_ocupado' => $espacioOcupado
            ]);

            $mensaje = 'Reserva creada exitosamente';
            if ($espacioOcupado) {
                $mensaje .= '. Espacio ' . $request->espacio . ' marcado como ocupado automáticamente';
            }

            return response()->json([
                'success' => true,
                'mensaje' => $mensaje,
                'id_reserva' => $idReserva,
                'datos' => [
                    'responsable' => $request->nombre,
                    'espacio' => $espacio->nombre_espacio,
                    'fecha' => $request->fecha,
                    'modulos' => $request->modulo_inicial . ' - ' . $request->modulo_final,
                    'hora' => $horaInicio,
                    'tipo' => $request->tipo === 'profesor' ? 'Académica' : 'Externa',
                    'creado_por' => $usuario->name ?? 'Administrador',
                    'espacio_ocupado' => $espacioOcupado
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('❌ Error de validación en creación de reserva', $e->errors());
            return response()->json([
                'success' => false,
                'mensaje' => 'Datos inválidos: ' . collect($e->errors())->flatten()->implode(', '),
                'errores' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('❌ Error al crear reserva en Quick Actions: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'mensaje' => 'Error interno del servidor: ' . $e->getMessage(),
                'error_details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambiar estado de un espacio
     */
    public function cambiarEstadoEspacio(Request $request, $codigo)
    {
        try {
            Log::info('🔄 Cambiando estado de espacio', [
                'codigo' => $codigo,
                'nuevo_estado' => $request->estado
            ]);

            // Validar el estado
            $request->validate([
                'estado' => 'required|in:Disponible,Ocupado,Mantenimiento'
            ]);

            // Buscar el espacio solo por id_espacio
            $espacio = Espacio::where('id_espacio', $codigo)->first();

            if (!$espacio) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Espacio no encontrado'
                ], 404);
            }

            // Actualizar estado - verificamos qué campo usar
            $estadoAnterior = $espacio->estado_espacio ?? $espacio->estado ?? 'Disponible';

            if (Schema::hasColumn('espacios', 'estado_espacio')) {
                $espacio->estado_espacio = $request->estado;
            } else {
                $espacio->estado = $request->estado;
            }

            $espacio->save();

            // Si el espacio se libera (pasa a Disponible), verificar reservas activas actuales
            $reservasFinalizadas = [];
            if ($request->estado === 'Disponible' && $estadoAnterior === 'Ocupado') {
                $reservasFinalizadas = $this->finalizarReservasActivasActuales($codigo);
            }

            Log::info('✅ Estado de espacio actualizado', [
                'codigo' => $codigo,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => $request->estado,
                'reservas_finalizadas' => $reservasFinalizadas
            ]);

            $mensaje = "Estado del espacio {$codigo} cambiado de {$estadoAnterior} a {$request->estado}";
            if (!empty($reservasFinalizadas)) {
                $cantidadReservas = count($reservasFinalizadas);
                $mensaje .= ". Se finalizaron automáticamente {$cantidadReservas} reserva(s) activa(s): " . implode(', ', $reservasFinalizadas);
            }

            return response()->json([
                'success' => true,
                'mensaje' => $mensaje,
                'espacio' => [
                    'codigo' => $espacio->id_espacio,
                    'nombre' => $espacio->nombre_espacio,
                    'estado_anterior' => $estadoAnterior,
                    'estado_nuevo' => $request->estado,
                    'reservas_finalizadas' => $reservasFinalizadas
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Estado inválido: ' . collect($e->errors())->flatten()->implode(', ')
            ], 422);
        } catch (\Exception $e) {
            Log::error('❌ Error al cambiar estado de espacio: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'mensaje' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambiar estado de una reserva
     */
    public function cambiarEstadoReserva(Request $request, $id)
    {
        try {
            Log::info('🔄 Cambiando estado de reserva', [
                'id_reserva' => $id,
                'nuevo_estado' => $request->estado
            ]);

            $request->validate([
                'estado' => 'required|in:activa,finalizada,cancelada'
            ]);

            // Buscar reserva por id_reserva (que es string, no int)
            $reserva = Reserva::where('id_reserva', $id)->first();

            if (!$reserva) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Reserva no encontrada'
                ], 404);
            }

            $estadoAnterior = $reserva->estado ?? 'activa';

            // Actualizar el campo estado
            $reserva->estado = $request->estado;

            // Variables para el control del espacio
            $espacioLiberado = false;
            $espacioId = $reserva->id_espacio;

            // Actualizar hora de salida si se finaliza
            if ($request->estado === 'finalizada') {
                $reserva->hora_salida = now()->format('H:i:s');

                // Verificar si es una reserva actual para liberar el espacio
                $espacioLiberado = $this->liberarEspacioSiEsReservaActual($reserva);
            }

            $reserva->save();

            Log::info('✅ Estado de reserva actualizado', [
                'id_reserva' => $id,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => $request->estado
            ]);

            $mensaje = "Reserva {$id} {$request->estado} correctamente";
            if ($request->estado === 'finalizada') {
                $mensaje .= " (hora de salida: {$reserva->hora_salida})";
                if ($espacioLiberado) {
                    $mensaje .= ". Espacio {$espacioId} liberado automáticamente";
                }
            }

            return response()->json([
                'success' => true,
                'mensaje' => $mensaje,
                'reserva' => [
                    'id' => $reserva->id_reserva,
                    'estado_anterior' => $estadoAnterior,
                    'estado_nuevo' => $request->estado,
                    'hora_salida' => $reserva->hora_salida,
                    'espacio' => $reserva->espacio->nombre_espacio ?? 'Sin espacio',
                    'usuario' => $reserva->nombreUsuario ?? 'Sin usuario',
                    'espacio_liberado' => $espacioLiberado
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Estado inválido: ' . collect($e->errors())->flatten()->implode(', ')
            ], 422);
        } catch (\Exception $e) {
            Log::error('❌ Error al cambiar estado de reserva: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'mensaje' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Liberar espacio automáticamente al finalizar una reserva
     * Versión mejorada: libera el espacio si no hay más reservas activas actuales o futuras inmediatas
     */
    private function liberarEspacioSiEsReservaActual($reserva)
    {
        try {
            // Obtener fecha y hora actual
            $fechaActual = now()->format('Y-m-d');
            $horaActual = now()->format('H:i:s');
            $horaActualEnMinutos = $this->convertirHoraAMinutos($horaActual);

            // Verificar si hay otras reservas activas en el mismo espacio que deban seguir ocupándolo
            $otrasReservasActivas = Reserva::where('id_espacio', $reserva->id_espacio)
                ->where('estado', 'activa')
                ->where('id_reserva', '!=', $reserva->id_reserva) // Excluir la reserva que se está finalizando
                ->where('fecha_reserva', $fechaActual)
                ->get();

            // Verificar si alguna de estas reservas está actualmente en curso
            $hayReservaEnCurso = false;
            foreach ($otrasReservasActivas as $otraReserva) {
                $horaInicioOtra = $this->convertirHoraAMinutos($otraReserva->hora);

                // Estimar duración basada en módulos o asumir 1 hora
                $duracionEstimada = 60; // minutos por defecto
                if ($otraReserva->observaciones && preg_match('/Módulos: (\d+)-(\d+)/', $otraReserva->observaciones, $matches)) {
                    $modulosCount = (int)$matches[2] - (int)$matches[1] + 1;
                    $duracionEstimada = $modulosCount * 50; // 50 minutos por módulo
                } elseif (is_numeric($otraReserva->modulos)) {
                    $duracionEstimada = (int)$otraReserva->modulos * 50;
                }

                $horaFinEstimada = $horaInicioOtra + $duracionEstimada;

                // Si la hora actual está dentro del rango de esta reserva
                if ($horaActualEnMinutos >= $horaInicioOtra && $horaActualEnMinutos <= $horaFinEstimada) {
                    $hayReservaEnCurso = true;
                    Log::info('⚠️  Hay otra reserva activa en curso en el mismo espacio', [
                        'reserva_en_curso' => $otraReserva->id_reserva,
                        'hora_inicio' => $otraReserva->hora,
                        'duracion_estimada' => $duracionEstimada . ' minutos'
                    ]);
                    break;
                }
            }

            // Solo liberar el espacio si:
            // 1. La reserva finalizada es de hoy, Y
            // 2. No hay otras reservas activas en curso
            if ($reserva->fecha_reserva === $fechaActual && !$hayReservaEnCurso) {
                $espacio = Espacio::where('id_espacio', $reserva->id_espacio)->first();
                if ($espacio && $espacio->estado === 'Ocupado') {
                    $espacio->estado = 'Disponible';
                    $espacio->save();

                    Log::info('🔓 Espacio liberado automáticamente', [
                        'id_espacio' => $reserva->id_espacio,
                        'reserva_finalizada' => $reserva->id_reserva,
                        'fecha_reserva' => $reserva->fecha_reserva,
                        'hora_finalizacion' => $horaActual,
                        'otras_reservas_activas' => $otrasReservasActivas->count()
                    ]);

                    return true;
                }
            } else {
                $motivo = $reserva->fecha_reserva !== $fechaActual ? 'no es del día actual' : 'hay otras reservas activas en curso';
                Log::info('📋 No se libera el espacio', [
                    'motivo' => $motivo,
                    'fecha_reserva' => $reserva->fecha_reserva,
                    'fecha_actual' => $fechaActual,
                    'otras_reservas_en_curso' => $hayReservaEnCurso
                ]);
            }

            return false;

        } catch (\Exception $e) {
            Log::error('❌ Error al verificar liberación de espacio: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Ocupar espacio automáticamente si la reserva creada es actual
     * (misma fecha y el módulo actual coincide con el horario de la reserva)
     */
    private function ocuparEspacioSiEsReservaActual($reserva)
    {
        try {
            // Obtener fecha y hora actual
            $fechaActual = now()->format('Y-m-d');
            $horaActual = now()->format('H:i:s');

            // Verificar si la reserva es del día actual
            if ($reserva->fecha_reserva !== $fechaActual) {
                Log::info('📅 Reserva no es del día actual - no se ocupa el espacio', [
                    'fecha_reserva' => $reserva->fecha_reserva,
                    'fecha_actual' => $fechaActual
                ]);
                return false;
            }

            // Mapeo de módulos a horarios (mismo que el método de liberación)
            $horariosModulos = [
                1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'],
                2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'],
                3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'],
                4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'],
                5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'],
                6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'],
                7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'],
                8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'],
                9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'],
                10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'],
                11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'],
                12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'],
            ];

            // Determinar módulo actual basado en la hora
            $moduloActual = null;
            foreach ($horariosModulos as $modulo => $horario) {
                if ($horaActual >= $horario['inicio'] && $horaActual <= $horario['fin']) {
                    $moduloActual = $modulo;
                    break;
                }
            }

            if (!$moduloActual) {
                Log::info('⏰ No hay módulo activo en este momento - no se ocupa el espacio', [
                    'hora_actual' => $horaActual
                ]);
                return false;
            }

            // Verificar si la reserva incluye el módulo actual
            // Para reservas recién creadas, usar la información de módulos
            $modulosReserva = $reserva->modulos;
            $moduloInicio = null;
            $moduloFin = null;

            // Primero intentar extraer de las observaciones que contienen "Módulos: X-Y"
            if ($reserva->observaciones && preg_match('/Módulos: (\d+)-(\d+)/', $reserva->observaciones, $matches)) {
                $moduloInicio = (int)$matches[1];
                $moduloFin = (int)$matches[2];
            } elseif ($modulosReserva && preg_match('/(\d+)\s*-\s*(\d+)/', $modulosReserva, $matches)) {
                // Si modulos tiene formato "X - Y"
                $moduloInicio = (int)$matches[1];
                $moduloFin = (int)$matches[2];
            } elseif (is_numeric($modulosReserva)) {
                // Si modulos es la duración, usar la hora de inicio para determinar módulos
                $horaReserva = $reserva->hora;
                foreach ($horariosModulos as $modulo => $horario) {
                    if ($horaReserva >= $horario['inicio'] && $horaReserva <= $horario['fin']) {
                        $moduloInicio = $modulo;
                        $moduloFin = $modulo + (int)$modulosReserva - 1;
                        break;
                    }
                }
            }

            // Si aún no se determinaron, usar la hora de la reserva
            if (!$moduloInicio || !$moduloFin) {
                Log::info('⚠️  Determinando módulos por hora de inicio de la reserva', [
                    'modulos_reserva' => $modulosReserva,
                    'hora_reserva' => $reserva->hora,
                    'observaciones' => $reserva->observaciones
                ]);

                $horaReserva = $reserva->hora;
                foreach ($horariosModulos as $modulo => $horario) {
                    if ($horaReserva >= $horario['inicio'] && $horaReserva <= $horario['fin']) {
                        $moduloInicio = $modulo;
                        // Asumir duración de 1 módulo si no se puede determinar
                        $moduloFin = is_numeric($modulosReserva) ? $modulo + (int)$modulosReserva - 1 : $modulo;
                        break;
                    }
                }
            }

            // Verificar si el módulo actual está dentro del rango de la reserva
            if ($moduloInicio && $moduloFin && $moduloActual >= $moduloInicio && $moduloActual <= $moduloFin) {
                // Es una reserva actual - ocupar el espacio
                $espacio = Espacio::where('id_espacio', $reserva->id_espacio)->first();
                if ($espacio && $espacio->estado === 'Disponible') {
                    $espacio->estado = 'Ocupado';
                    $espacio->save();

                    Log::info('🔒 Espacio ocupado automáticamente por reserva actual', [
                        'id_espacio' => $reserva->id_espacio,
                        'id_reserva' => $reserva->id_reserva,
                        'modulo_actual' => $moduloActual,
                        'modulos_reserva' => "{$moduloInicio}-{$moduloFin}",
                        'fecha_reserva' => $reserva->fecha_reserva,
                        'hora_actual' => $horaActual,
                        'hora_reserva' => $reserva->hora
                    ]);

                    return true;
                } elseif ($espacio) {
                    Log::info('⚠️  Espacio ya está ocupado o en otro estado', [
                        'id_espacio' => $reserva->id_espacio,
                        'estado_actual' => $espacio->estado
                    ]);
                }
            } else {
                Log::info('📋 Reserva no corresponde al módulo actual - no se ocupa el espacio', [
                    'modulo_actual' => $moduloActual,
                    'modulos_reserva' => "{$moduloInicio}-{$moduloFin}",
                    'hora_actual' => $horaActual,
                    'hora_reserva' => $reserva->hora
                ]);
            }

            return false;

        } catch (\Exception $e) {
            Log::error('❌ Error al verificar ocupación de espacio: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Finalizar reservas activas cuando se libera un espacio manualmente
     * Finaliza la última reserva activa de hoy y todas las posteriores en cascada
     */
    private function finalizarReservasActivasActuales($codigoEspacio)
    {
        try {
            // Obtener fecha y hora actual
            $fechaActual = now()->format('Y-m-d');
            $horaActual = now()->format('H:i:s');

            // Buscar TODAS las reservas activas para este espacio desde hoy hacia adelante
            // Ordenadas por fecha y hora para procesar en orden cronológico
            $reservasActivas = Reserva::where('id_espacio', $codigoEspacio)
                ->where('estado', 'activa')
                ->where('fecha_reserva', '>=', $fechaActual)
                ->orderBy('fecha_reserva')
                ->orderBy('hora')
                ->get();

            if ($reservasActivas->isEmpty()) {
                Log::info('📋 No hay reservas activas para finalizar en el espacio', [
                    'espacio' => $codigoEspacio,
                    'fecha' => $fechaActual
                ]);
                return [];
            }

            // Encontrar la última reserva que ya comenzó (de hoy)
            $reservasDeHoy = $reservasActivas->where('fecha_reserva', $fechaActual);
            $ultimaReservaIniciada = null;
            $horaActualEnMinutos = $this->convertirHoraAMinutos($horaActual);

            foreach ($reservasDeHoy as $reserva) {
                $horaInicioReserva = $this->convertirHoraAMinutos($reserva->hora);

                // Si la reserva ya comenzó (hora actual >= hora de inicio)
                if ($horaActualEnMinutos >= $horaInicioReserva) {
                    $ultimaReservaIniciada = $reserva;
                } else {
                    break; // Las siguientes aún no han comenzado
                }
            }

            $reservasFinalizadas = [];
            $esCascada = false;
            $motivoFinalizacion = null;

            // Si hay una reserva que ya comenzó hoy, esa es la principal a finalizar
            if ($ultimaReservaIniciada) {
                $motivoFinalizacion = "FINALIZADA: El espacio fue liberado manualmente, indicando que la actividad terminó";

                $ultimaReservaIniciada->estado = 'finalizada';
                $ultimaReservaIniciada->hora_salida = $horaActual;
                $ultimaReservaIniciada->observaciones .= " | {$motivoFinalizacion} el " . now()->format('d/m/Y H:i:s');
                $ultimaReservaIniciada->save();

                $reservasFinalizadas[] = $ultimaReservaIniciada->id_reserva;

                Log::info('🔚 Reserva principal finalizada por liberación de espacio', [
                    'id_reserva' => $ultimaReservaIniciada->id_reserva,
                    'espacio' => $codigoEspacio,
                    'hora_inicio' => $ultimaReservaIniciada->hora,
                    'hora_salida' => $horaActual
                ]);

                $esCascada = true;
            }

            // Finalizar reservas ANTERIORES de hoy que aún estén activas
            foreach ($reservasDeHoy as $reserva) {
                // Saltar la reserva principal que ya procesamos
                if ($ultimaReservaIniciada && $reserva->id === $ultimaReservaIniciada->id) {
                    continue;
                }

                // Solo finalizar reservas que comenzaron ANTES de la hora actual
                $esAnterior = $this->convertirHoraAMinutos($reserva->hora) < $horaActualEnMinutos;

                if ($esAnterior) {
                    $motivoAnterior = "FINALIZADA EN CASCADA: Al liberar el espacio manualmente, esta reserva anterior que aún estaba activa fue finalizada automáticamente";

                    $reserva->estado = 'finalizada';
                    $reserva->hora_salida = $horaActual; // Usar la hora actual como hora de salida
                    $reserva->observaciones .= " | {$motivoAnterior} el " . now()->format('d/m/Y H:i:s');
                    $reserva->save();

                    $reservasFinalizadas[] = $reserva->id_reserva;

                    Log::info('� Reserva finalizada en cascada', [
                        'id_reserva' => $reserva->id_reserva,
                        'fecha_reserva' => $reserva->fecha_reserva,
                        'hora_reserva' => $reserva->hora,
                        'motivo' => 'cascada_anterior'
                    ]);
                }
            }

            if (!empty($reservasFinalizadas)) {
                Log::info('✅ Finalización completa de reservas por liberación de espacio', [
                    'espacio' => $codigoEspacio,
                    'total_finalizadas' => count($reservasFinalizadas),
                    'reservas_finalizadas' => $reservasFinalizadas,
                    'reserva_principal' => $ultimaReservaIniciada ? $ultimaReservaIniciada->id_reserva : 'ninguna',
                    'fecha_liberacion' => now()->format('d/m/Y H:i:s')
                ]);
            }

            return $reservasFinalizadas;

        } catch (\Exception $e) {
            Log::error('❌ Error al finalizar reservas activas: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Procesar información de módulos y horarios para mostrar en frontend
     */
    private function procesarModulosYHorarios($reserva)
    {
        // Mapeo de módulos a horarios
        $horariosModulos = [
            1 => ['inicio' => '08:10', 'fin' => '09:00'],
            2 => ['inicio' => '09:10', 'fin' => '10:00'],
            3 => ['inicio' => '10:10', 'fin' => '11:00'],
            4 => ['inicio' => '11:10', 'fin' => '12:00'],
            5 => ['inicio' => '12:10', 'fin' => '13:00'],
            6 => ['inicio' => '13:10', 'fin' => '14:00'],
            7 => ['inicio' => '14:10', 'fin' => '15:00'],
            8 => ['inicio' => '15:10', 'fin' => '16:00'],
            9 => ['inicio' => '16:10', 'fin' => '17:00'],
            10 => ['inicio' => '17:10', 'fin' => '18:00'],
            11 => ['inicio' => '18:10', 'fin' => '19:00'],
            12 => ['inicio' => '19:10', 'fin' => '20:00'],
        ];

        $moduloInicio = null;
        $moduloFin = null;
        $cantidadModulos = 1;

        // Intentar extraer de observaciones primero
        if ($reserva->observaciones && preg_match('/Módulos: (\d+)-(\d+)/', $reserva->observaciones, $matches)) {
            $moduloInicio = (int)$matches[1];
            $moduloFin = (int)$matches[2];
            $cantidadModulos = $moduloFin - $moduloInicio + 1;
        } elseif ($reserva->modulos && preg_match('/(\d+)\s*-\s*(\d+)/', $reserva->modulos, $matches)) {
            // Si modulos tiene formato "X - Y"
            $moduloInicio = (int)$matches[1];
            $moduloFin = (int)$matches[2];
            $cantidadModulos = $moduloFin - $moduloInicio + 1;
        } else {
            // Determinar por hora de inicio y duración en módulos
            $horaReserva = substr($reserva->hora, 0, 5); // HH:MM
            foreach ($horariosModulos as $modulo => $horario) {
                if ($horaReserva >= $horario['inicio'] && $horaReserva <= $horario['fin']) {
                    $moduloInicio = $modulo;
                    $cantidadModulos = is_numeric($reserva->modulos) ? (int)$reserva->modulos : 1;
                    $moduloFin = $moduloInicio + $cantidadModulos - 1;
                    break;
                }
            }
        }

        // Construir información completa
        if ($moduloInicio && $moduloFin && isset($horariosModulos[$moduloInicio]) && isset($horariosModulos[$moduloFin])) {
            $horaInicio = $horariosModulos[$moduloInicio]['inicio'];
            $horaFin = $horariosModulos[$moduloFin]['fin'];

            return [
                'modulo_inicial' => $moduloInicio,
                'modulo_final' => $moduloFin,
                'cantidad_modulos' => $cantidadModulos,
                'hora_inicio' => $horaInicio,
                'hora_fin' => $horaFin,
                'rango_horario' => "{$horaInicio} - {$horaFin}",
                'texto_completo' => "Módulos {$moduloInicio}-{$moduloFin} ({$horaInicio} - {$horaFin}) • {$cantidadModulos} módulo" . ($cantidadModulos > 1 ? 's' : '')
            ];
        }

        // Fallback si no se puede determinar
        return [
            'modulo_inicial' => null,
            'modulo_final' => null,
            'cantidad_modulos' => $cantidadModulos,
            'hora_inicio' => substr($reserva->hora, 0, 5),
            'hora_fin' => 'Desconocido',
            'rango_horario' => substr($reserva->hora, 0, 5),
            'texto_completo' => "Hora: " . substr($reserva->hora, 0, 5) . " • Duración: {$cantidadModulos} módulo" . ($cantidadModulos > 1 ? 's' : '')
        ];
    }

    /**
     * Convertir hora en formato H:i:s a minutos desde medianoche
     * Para comparaciones más fáciles de horarios
     */
    private function convertirHoraAMinutos($hora)
    {
        $partes = explode(':', $hora);
        $horas = (int)$partes[0];
        $minutos = (int)$partes[1];
        return ($horas * 60) + $minutos;
    }

    /**
     * Mostrar formulario para editar una reserva
     */
    public function editarReserva($id)
    {
        try {
            // Buscar reserva
            $reserva = Reserva::where('id_reserva', $id)
                ->with(['espacio', 'profesor', 'solicitante'])
                ->first();

            if (!$reserva) {
                return redirect()->route('quick-actions.gestionar-reservas')
                    ->with('error', 'Reserva no encontrada');
            }

            // Verificar que la reserva esté activa
            if ($reserva->estado !== 'activa') {
                return redirect()->route('quick-actions.gestionar-reservas')
                    ->with('error', 'Solo se pueden editar reservas activas');
            }

            // Obtener espacios disponibles
            $espacios = Espacio::select('codigo_espacio', 'nombre_espacio')
                ->where('activo', true)
                ->orderBy('codigo_espacio')
                ->get();

            return view('quick_actions.editar-reserva', compact('reserva', 'espacios'));
        } catch (\Exception $e) {
            Log::error('Error al cargar formulario de edición de reserva: ' . $e->getMessage());
            return redirect()->route('quick-actions.gestionar-reservas')
                ->with('error', 'Error al cargar la reserva: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar una reserva existente
     */
    public function actualizarReserva(Request $request, $id)
    {
        try {
            Log::info('🔄 Actualizando reserva', [
                'id_reserva' => $id,
                'datos' => $request->all()
            ]);

            // Validación
            $request->validate([
                'codigo_espacio' => 'required|exists:espacios,codigo_espacio',
                'fecha' => 'required|date',
                'hora' => 'required',
                'modulos' => 'required|integer|min:1',
                'observaciones' => 'nullable|string'
            ]);

            // Buscar reserva
            $reserva = Reserva::where('id_reserva', $id)->first();

            if (!$reserva) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Reserva no encontrada'
                ], 404);
            }

            // Verificar que esté activa
            if ($reserva->estado !== 'activa') {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Solo se pueden editar reservas activas'
                ], 400);
            }

            // Obtener el espacio
            $espacio = Espacio::where('codigo_espacio', $request->codigo_espacio)->first();

            if (!$espacio) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Espacio no encontrado'
                ], 404);
            }

            // Actualizar datos
            $reserva->id_espacio = $espacio->id_espacio;
            $reserva->fecha = $request->fecha;
            $reserva->hora = $request->hora;
            $reserva->cant_modulos = $request->modulos;
            $reserva->observaciones = $request->observaciones;

            // Marcar como editada (se usa updated_at automáticamente por Laravel)
            $reserva->touch(); // Esto actualiza el timestamp updated_at

            $reserva->save();

            Log::info('✅ Reserva actualizada exitosamente', [
                'id_reserva' => $id,
                'editada' => true
            ]);

            return response()->json([
                'success' => true,
                'mensaje' => 'Reserva actualizada correctamente',
                'reserva' => [
                    'id' => $reserva->id_reserva,
                    'espacio' => $espacio->codigo_espacio,
                    'fecha' => $reserva->fecha,
                    'hora' => $reserva->hora,
                    'modulos' => $reserva->cant_modulos
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Datos inválidos: ' . collect($e->errors())->flatten()->implode(', ')
            ], 422);
        } catch (\Exception $e) {
            Log::error('❌ Error al actualizar reserva: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'mensaje' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }
}
