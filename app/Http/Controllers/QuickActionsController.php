<?php

namespace App\Http\Controllers;

use App\Helpers\SemesterHelper;
use App\Mail\ConfirmacionReserva;
use App\Models\Asignatura;
use App\Models\Espacio;
use App\Models\Planificacion_Asignatura;
use App\Models\Profesor;
use App\Models\Reserva;
use App\Models\Solicitante;
use App\Models\Tenant;
use App\Models\User;
use App\Traits\RunNormalizer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class QuickActionsController extends Controller
{
    use RunNormalizer;

    public function __construct()
    {
        $this->middleware('can:acciones rapidas');
    }

    /**
     * Mostrar menú de acciones rápidas.
     */
    public function index()
    {
        return view('quick_actions.index');
    }

    /**
     * Mostrar formulario para crear reserva.
     */
    public function crearReserva()
    {
        return view('quick_actions.crear-reserva');
    }

    /**
     * Mostrar gestor de reservas.
     */
    public function gestionarReservas()
    {
        return view('quick_actions.gestionar-reservas');
    }

    /**
     * Mostrar gestor de espacios.
     */
    public function gestionarEspacios()
    {
        return view('quick_actions.gestionar-espacios');
    }

    /**
     * Obtener datos para el dashboard.
     */
    public function getDashboardData()
    {
        try {
            $fechaHoy = today()->format('Y-m-d');
            $horaActual = now()->format('H:i:s');

            // Total reservas del día (activas + programadas)
            $reservas_hoy = Reserva::whereDate('fecha_reserva', $fechaHoy)
                ->whereIn('estado', ['activa', 'programada'])
                ->count();

            // Espacios en mantenimiento
            $espacios_mantencion = Espacio::whereIn('estado', ['Mantenimiento', 'Mantención'])->count();

            // Espacios con reserva ACTIVA en este momento (más fiable que el campo estado)
            $espaciosConReservaActiva = Reserva::where('fecha_reserva', $fechaHoy)
                ->where('estado', 'activa')
                ->distinct('id_espacio')
                ->pluck('id_espacio');

            $espacios_ocupados = $espaciosConReservaActiva->count();

            // Espacios disponibles = Total - Ocupados - Mantenimiento
            $total_espacios = Espacio::count();
            $espacios_libres = max(0, $total_espacios - $espacios_ocupados - $espacios_mantencion);

            return response()->json([
                'success' => true,
                'reservas_hoy' => $reservas_hoy,
                'espacios_libres' => $espacios_libres,
                'espacios_ocupados' => $espacios_ocupados,
                'espacios_mantencion' => $espacios_mantencion,
                'timestamp' => now()->format('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener datos del dashboard: '.$e->getMessage(),
                'reservas_hoy' => 0,
                'espacios_libres' => 0,
                'espacios_ocupados' => 0,
                'espacios_mantencion' => 0,
            ], 500);
        }
    }

    /**
     * Obtener espacios para gestión.
     */
    public function getEspacios(Request $request)
    {
        try {
            $query = Espacio::with('piso')
                ->select('espacios.id_espacio', 'espacios.nombre_espacio', 'espacios.tipo_espacio', 'espacios.piso_id', 'espacios.puestos_disponibles', 'espacios.capacidad_maxima', 'espacios.estado')
                ->leftJoin('pisos', 'espacios.piso_id', '=', 'pisos.id')
                ->orderBy('espacios.estado', 'asc') // Disponibles primero
                ->orderBy('pisos.numero_piso', 'asc')
                ->orderBy('espacios.id_espacio', 'asc');

            // Aplicar filtros si existen
            if ($request->has('estado') && $request->estado) {
                $query->where('espacios.estado', $request->estado);
            }

            if ($request->has('piso') && $request->piso) {
                $query->where('espacios.piso_id', $request->piso);
            }

            $espaciosRaw = $query->get();

            // Transformar los datos para mantener compatibilidad con el frontend
            $espacios = $espaciosRaw->map(function ($espacio) {
                $capacidadReal = ($espacio->capacidad_maxima && $espacio->capacidad_maxima > 0)
                    ? $espacio->capacidad_maxima
                    : ($espacio->puestos_disponibles ?? 0);

                return [
                    'codigo' => $espacio->id_espacio,
                    'nombre' => $espacio->nombre_espacio,
                    'tipo' => $espacio->tipo_espacio,
                    'piso' => $espacio->piso ? $espacio->piso->numero_piso : $espacio->piso_id,
                    'capacidad' => $capacidadReal,
                    'capacidad_maxima' => $capacidadReal,
                    'estado' => $espacio->estado,
                    // Campos originales por si se necesitan
                    'id_espacio' => $espacio->id_espacio,
                    'nombre_espacio' => $espacio->nombre_espacio,
                    'piso_id' => $espacio->piso_id,
                ];
            });

            return response()->json([
                'success' => true,
                'espacios' => $espacios,
                'data' => $espacios,
                'count' => $espacios->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener espacios en QuickActions: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al cargar espacios: '.$e->getMessage(),
                'espacios' => [],
                'error_details' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener reservas para gestión.
     */
    public function getReservas(Request $request)
    {
        try {
            // Obtener el tenant de la sesión
            $tenant = null;
            if (session()->has('tenant_id')) {
                $tenant = Tenant::find(session('tenant_id'));
            }

            // Fallback: obtener el primer tenant activo si no hay sesión
            if (!$tenant) {
                $tenant = Tenant::where('is_active', true)->first();
                if ($tenant) {
                    $tenant->makeCurrent();
                }
            }

            if (!$tenant) {
                Log::error('No se encontró tenant configurado para getReservas');

                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró tenant configurado',
                    'reservas' => [],
                    'data' => [],
                    'total' => 0,
                ]);
            }

            // 🔧 CRÍTICO: Configurar la conexión 'tenant' ANTES de hacer queries
            if ($tenant->database) {
                config([
                    'database.connections.tenant.database' => $tenant->database,
                ]);
                app('db')->purge('tenant');
                app('db')->disconnect('tenant');
            }

            // Construir la query DESPUÉS de configurar la conexión
            $query = Reserva::on('tenant')
                ->orderBy('fecha_reserva', 'desc')
                ->orderBy('hora');
            $reservasRaw = $query->get();

            // Mejorado para incluir más información
            $reservas = $reservasRaw->map(function ($reserva) {
                // Obtener información del responsable
                $nombreResponsable = 'Sin asignar';
                $runResponsable = 'N/A';
                $tipoResponsable = 'desconocido';

                if ($reserva->run_profesor) {
                    $profesor = Profesor::on('tenant')->where('run_profesor', $reserva->run_profesor)->first();
                    $nombreResponsable = $profesor ? $profesor->name : $reserva->run_profesor;
                    $runResponsable = $reserva->run_profesor;
                    $tipoResponsable = 'profesor';
                } elseif ($reserva->run_solicitante) {
                    $solicitante = Solicitante::on('tenant')->where('run_solicitante', $reserva->run_solicitante)->first();
                    $nombreResponsable = $solicitante ? $solicitante->nombre : $reserva->run_solicitante;
                    $runResponsable = $reserva->run_solicitante;
                    $tipoResponsable = 'solicitante';
                }

                // Obtener información del espacio
                $espacio = Espacio::on('tenant')->where('id_espacio', $reserva->id_espacio)->first();
                $nombreEspacio = $espacio ? $espacio->nombre_espacio : 'Espacio desconocido';

                // Obtener información de la asignatura
                $asignaturaInfo = 'Sin asignatura';
                if ($reserva->id_asignatura) {
                    $asignatura = Asignatura::on('tenant')->where('id_asignatura', $reserva->id_asignatura)->first();
                    if ($asignatura) {
                        $asignaturaInfo = $asignatura->codigo_asignatura.' - '.$asignatura->nombre_asignatura;
                    }
                }

                // Procesar módulos y horarios
                $modulosInfo = $this->procesarModulosYHorarios($reserva);

                // Verificar si fue editada (comparando created_at con updated_at)
                $fueEditada = $reserva->created_at
                    && $reserva->updated_at
                    && $reserva->updated_at->gt($reserva->created_at->addSeconds(5));

                return [
                    'id' => $reserva->id_reserva,
                    'nombre_responsable' => $nombreResponsable,
                    'run_responsable' => $runResponsable,
                    'tipo_responsable' => $tipoResponsable,
                    'id_espacio' => $reserva->id_espacio,
                    'nombre_espacio' => $nombreEspacio,
                    'asignatura' => $asignaturaInfo,
                    'fecha' => $reserva->fecha_reserva instanceof Carbon
                        ? $reserva->fecha_reserva->format('Y-m-d')
                        : $reserva->fecha_reserva,
                    'hora' => $reserva->hora,
                    'modulos' => $reserva->modulos ?? 1,
                    'modulos_info' => $modulosInfo,
                    'tipo_reserva' => $reserva->tipo_reserva,
                    'estado' => strtolower($reserva->estado ?? 'activa'),
                    'observaciones' => $reserva->observaciones ?? '',
                    'editada' => $fueEditada,
                ];
            });

            return response()->json([
                'success' => true,
                'reservas' => $reservas,  // Añadido para consistencia con JavaScript
                'data' => $reservas,  // Mantenemos 'data' por compatibilidad
                'total' => $reservas->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener reservas en QuickActions: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al cargar reservas: '.$e->getMessage(),
                'reservas' => [],
            ], 500);
        }
    }

    /**
     * Buscar personas por RUN para autocompletado.
     */
    public function buscarPersonas(Request $request)
    {
        try {
            $termino = $request->get('q', '');

            if (strlen($termino) < 2) {
                return response()->json([
                    'success' => true,
                    'personas' => [],
                ]);
            }

            $personas = [];

            // Buscar en profesores (tabla: profesors)
            $profesores = Profesor::where('run_profesor', 'LIKE', '%'.$termino.'%')
                ->orWhere('name', 'LIKE', '%'.$termino.'%')
                ->limit(10)
                ->get();

            foreach ($profesores as $profesor) {
                $personas[] = [
                    'run' => $profesor->run_profesor,
                    'nombre' => $profesor->name,
                    'email' => $profesor->email ?? '',
                    'telefono' => $profesor->celular ?? '',
                    'tipo' => 'profesor',
                    'display' => $profesor->run_profesor.' - '.$profesor->name.' (Profesor)',
                ];
            }

            // Buscar en solicitantes (tabla: solicitantes)
            $solicitantes = Solicitante::on('tenant')
                ->where('run_solicitante', 'LIKE', '%'.$termino.'%')
                ->orWhere('nombre', 'LIKE', '%'.$termino.'%')
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
                    'display' => $solicitante->run_solicitante.' - '.$solicitante->nombre.' (Solicitante)',
                ];
            }

            return response()->json([
                'success' => true,
                'personas' => $personas,
                'count' => count($personas),
                'termino_buscado' => $termino,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al buscar personas: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al buscar personas: '.$e->getMessage(),
                'personas' => [],
                'error_details' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Buscar asignaturas por código o nombre (autocompletado).
     */
    public function buscarAsignaturas(Request $request)
    {
        try {
            $termino = $request->input('q', '');

            if (strlen($termino) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'El término de búsqueda debe tener al menos 2 caracteres',
                    'asignaturas' => [],
                ]);
            }

            // Buscar asignaturas por código o nombre
            $asignaturas = Asignatura::where('codigo_asignatura', 'LIKE', '%'.$termino.'%')
                ->orWhere('nombre_asignatura', 'LIKE', '%'.$termino.'%')
                ->limit(20)
                ->get();

            $resultado = $asignaturas->map(function ($asignatura) {
                return [
                    'id_asignatura' => $asignatura->id_asignatura,
                    'codigo_asignatura' => $asignatura->codigo_asignatura,
                    'nombre_asignatura' => $asignatura->nombre_asignatura,
                    'display' => $asignatura->codigo_asignatura.' - '.$asignatura->nombre_asignatura,
                ];
            });

            return response()->json([
                'success' => true,
                'asignaturas' => $resultado,
                'count' => $resultado->count(),
                'termino_buscado' => $termino,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al buscar asignaturas: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al buscar asignaturas: '.$e->getMessage(),
                'asignaturas' => [],
                'error_details' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Procesar creación de nueva reserva.
     */
    public function procesarCrearReserva(Request $request)
    {
        try {
            // Validar datos básicos - Teléfono es OPCIONAL
            $request->validate([
                'nombre' => 'required|string|max:255',
                'run' => 'required|string|max:20',
                'correo' => 'required|email|max:255',
                'telefono' => 'nullable|string|max:20',  // OPCIONAL - puede ser null
                'tipo' => 'required|in:profesor,solicitante,colaborador',
                'id_asignatura' => 'nullable|string',
                'espacio' => 'required|string',
                'fecha' => 'required|date',
                'tipo_frecuencia' => 'nullable|in:puntual,recurrente',
                'fecha_fin' => 'nullable|date|after_or_equal:fecha',
                'modulo_inicial' => 'required|integer|min:1|max:16',
                'modulo_final' => 'required|integer|min:1|max:16',
                'observaciones' => 'nullable|string|max:500',
                'nombre_actividad' => 'nullable|string|max:255',
                'descripcion_actividad' => 'nullable|string|max:500',
                'forzar' => 'nullable|boolean',
            ]);

            // Normalizar RUN
            $runNormalizado = $this->normalizeRun($request->run);

            $idAsignatura = $request->id_asignatura;
            if ($idAsignatura === 'otro') {
                $idAsignatura = null;
            }

            if ($idAsignatura && !Asignatura::where('id_asignatura', $idAsignatura)->exists()) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'La asignatura seleccionada no existe en este tenant',
                ], 422);
            }

            // Validar que si es profesor o colaborador tenga asignatura
            if ($request->tipo === 'colaborador' && !$idAsignatura) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Debe seleccionar una asignatura para las reservas de profesor colaborador',
                ], 400);
            }

            // Validar que si es solicitante externo tenga nombre de actividad
            if ($request->tipo === 'solicitante' && !$request->nombre_actividad) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Debe indicar el nombre de la actividad para reservas de solicitantes externos',
                ], 400);
            }

            // Verificar que el módulo inicial sea menor o igual al final
            if ($request->modulo_inicial > $request->modulo_final) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'El módulo inicial no puede ser mayor al módulo final',
                ], 400);
            }

            // Verificar que el espacio existe
            $espacio = Espacio::where('id_espacio', $request->espacio)->first();
            if (!$espacio) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'El espacio seleccionado no existe',
                ], 400);
            }

            // Obtener día de la semana de la fecha de la reserva
            $diasSemana = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
            $diaReserva = $diasSemana[Carbon::parse($request->fecha)->dayOfWeek];

            // Prefijo del día (ej: "JU" para jueves)
            $prefijosDias = ['DO', 'LU', 'MA', 'MI', 'JU', 'VI', 'SA'];
            $prefijoReserva = $prefijosDias[Carbon::parse($request->fecha)->dayOfWeek];

            // Construir id_modulo (ej: "JU.5,JU.6")
            $modulosReserva = [];
            for ($i = $request->modulo_inicial; $i <= $request->modulo_final; ++$i) {
                $modulosReserva[] = $prefijoReserva.'.'.$i;
            }
            $idModuloString = implode(',', $modulosReserva);
            $idModuloInicial = $prefijoReserva.'.'.$request->modulo_inicial;
            $idModuloFinal = $prefijoReserva.'.'.$request->modulo_final;

            // Horarios predefinidos para módulos (sin depender de la tabla Modulo)
            // Esto permite crear reservas manuales sin que exista el módulo en la BD
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
                13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'],
                14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'],
                15 => ['inicio' => '22:10:00', 'fin' => '23:00:00'],
            ];

            // Obtener horas de los módulos inicial y final
            // Primero intentar obtener de la tabla Modulo (si existen registros)
            $moduloInicial = \App\Models\Modulo::where('id_modulo', $idModuloInicial)->first();
            $moduloFinal = \App\Models\Modulo::where('id_modulo', $idModuloFinal)->first();

            // Si no existen módulos en la BD, usar horarios predefinidos
            if ($moduloInicial) {
                $horaInicio = $moduloInicial->hora_inicio;
            } else {
                $horaInicio = $horariosModulos[$request->modulo_inicial]['inicio'] ?? '08:10:00';
            }

            if ($moduloFinal) {
                $horaFin = $moduloFinal->hora_termino;
            } else {
                $horaFin = $horariosModulos[$request->modulo_final]['fin'] ?? '09:00:00';
            }

            $duracionModulos = $request->modulo_final - $request->modulo_inicial + 1;

            // Calcular lista de fechas (Puntual vs Recurrente Semestral)
            $fechasASerReservadas = [];
            $esRecurrente = ($request->input('tipo_frecuencia') === 'recurrente' && $request->input('fecha_fin'));

            if ($esRecurrente) {
                $inicio = Carbon::parse($request->fecha);
                $fin = Carbon::parse($request->fecha_fin);
                $curr = $inicio->copy();
                while ($curr->lte($fin)) {
                    $fechasASerReservadas[] = $curr->format('Y-m-d');
                    $curr->addWeek();
                }
            } else {
                $fechasASerReservadas[] = $request->fecha;
            }

            // Asignar responsable previamente
            $runProfesor = null;
            $runSolicitante = null;

            if ($request->tipo === 'profesor' || $request->tipo === 'colaborador') {
                $tipoProfesor = $request->tipo === 'colaborador' ? 'Colaborador' : 'Invitado';

                $profesor = Profesor::updateOrCreate(
                    ['run_profesor' => $runNormalizado],
                    [
                        'name' => $request->nombre,
                        'email' => $request->correo,
                        'celular' => $request->telefono,
                        'tipo_profesor' => $tipoProfesor,
                    ]
                );

                $runProfesor = $profesor->run_profesor;
            } else {
                $solicitante = Solicitante::on('tenant')->updateOrCreate(
                    ['run_solicitante' => $runNormalizado],
                    [
                        'nombre' => $request->nombre,
                        'correo' => $request->correo,
                        'telefono' => $request->telefono,
                        'tipo_solicitante' => 'visitante',
                        'activo' => true,
                        'fecha_registro' => now(),
                    ]
                );

                $runSolicitante = $solicitante->run_solicitante;
            }

            $reservasCreadas = [];
            $usuario = auth()->user();

            foreach ($fechasASerReservadas as $indexFecha => $fechaObjStr) {
                $diasSemana = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
                $prefijosDias = ['DO', 'LU', 'MA', 'MI', 'JU', 'VI', 'SA'];
                $prefijoReserva = $prefijosDias[Carbon::parse($fechaObjStr)->dayOfWeek];

                $modulosReserva = [];
                for ($i = $request->modulo_inicial; $i <= $request->modulo_final; ++$i) {
                    $modulosReserva[] = $prefijoReserva.'.'.$i;
                }
                $idModuloString = implode(',', $modulosReserva);
                $idModuloInicial = $prefijoReserva.'.'.$request->modulo_inicial;
                $idModuloFinal = $prefijoReserva.'.'.$request->modulo_final;

                $moduloInicial = \App\Models\Modulo::where('id_modulo', $idModuloInicial)->first();
                $moduloFinal = \App\Models\Modulo::where('id_modulo', $idModuloFinal)->first();

                $horaInicio = $moduloInicial ? $moduloInicial->hora_inicio : ($horariosModulos[$request->modulo_inicial]['inicio'] ?? '08:10:00');
                $horaFin = $moduloFinal ? $moduloFinal->hora_termino : ($horariosModulos[$request->modulo_final]['fin'] ?? '09:00:00');

                // VALIDAR QUE NO EXISTA UNA RESERVA ACTIVA EN ESTA FECHA
                $reservaExistente = Reserva::where('id_espacio', $request->espacio)
                    ->where('fecha_reserva', $fechaObjStr)
                    ->whereIn('estado', ['activa', 'programada'])
                    ->where(function ($q) use ($request) {
                        $q->where(function ($inner) use ($request) {
                            $inner
                                ->where('modulo_inicio', '<=', $request->modulo_final)
                                ->where('modulo_fin', '>=', $request->modulo_inicial);
                        });
                    })
                    ->first();

                if ($reservaExistente && !$request->input('forzar', false)) {
                    if (!$esRecurrente) {
                        return response()->json([
                            'success' => false,
                            'mensaje' => 'Ya existe una reserva activa para el espacio '.$request->espacio
                                .' el día '.$fechaObjStr.' en los módulos solicitados.',
                            'reserva_conflicto' => $reservaExistente->id_reserva,
                        ], 409);
                    }
                    continue;
                }

                $idReserva = 'RES-'.strtoupper(uniqid());

                $rangoModulos = 'Módulos: '.$request->modulo_inicial.'-'.$request->modulo_final.' | ';
                $forzadoTag = $request->input('forzar', false) ? '⚠️ RESERVA FORZADA | ' : '';
                $tipoTag = $esRecurrente ? '🔁 RESERVA RECURRENTE SEMESTRAL | ' : '';
                $observacionesAutomaticas = $tipoTag.$forzadoTag.'RESERVA CREADA MANUALMENTE por '.($usuario->name ?? 'Administrador').' el '.now()->format('d/m/Y H:i:s').' | '.$rangoModulos;
                $observacionesCompletas = $observacionesAutomaticas.($request->observaciones ?? '');

                $fechaActual = now()->format('Y-m-d');
                $horaActualStr = now()->format('H:i:s');
                $esMismoDia = ($fechaObjStr === $fechaActual);

                $estaEnFranjaActual = false;
                if ($esMismoDia) {
                    $horaInicioModulo = $horariosModulos[$request->modulo_inicial]['inicio'] ?? null;
                    $horaFinModulo = $horariosModulos[$request->modulo_final]['fin'] ?? null;
                    if ($horaInicioModulo && $horaFinModulo) {
                        $estaEnFranjaActual = ($horaActualStr >= $horaInicioModulo && $horaActualStr <= $horaFinModulo);
                    }
                }

                $estadoReserva = ($esMismoDia && $estaEnFranjaActual) ? 'activa' : 'programada';
                $tipoReserva = $esRecurrente ? 'recurrente' : ((($request->tipo === 'profesor' || $request->tipo === 'colaborador') && $idAsignatura) ? 'clase' : 'directa');

                $datosReserva = [
                    'id_reserva' => $idReserva,
                    'fecha_reserva' => $fechaObjStr,
                    'id_espacio' => $request->espacio,
                    'id_asignatura' => $idAsignatura,
                    'modulos' => $duracionModulos,
                    'modulo_inicio' => $request->modulo_inicial,
                    'modulo_fin' => $request->modulo_final,
                    'hora' => $horaInicio,
                    'hora_salida' => $horaFin,
                    'tipo_reserva' => $tipoReserva,
                    'estado' => $estadoReserva,
                    'id_modulo' => $idModuloString,
                    'observaciones' => $observacionesCompletas,
                    'nombre_actividad' => $request->nombre_actividad,
                    'descripcion_actividad' => $request->descripcion_actividad,
                    'run_profesor' => $runProfesor,
                    'run_solicitante' => $runSolicitante,
                    'creado_por' => $usuario ? $usuario->name . ' (' . ($usuario->run ?? 'Admin') . ')' : 'Sistema',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $reservaCreated = Reserva::create($datosReserva);
                $reservasCreadas[] = $reservaCreated;

                if ($indexFecha === 0) {
                    $this->enviarCorreoReserva($reservaCreated);
                }

                $this->ocuparEspacioSiEsReservaActual($reservaCreated);
            }

            $this->limpiarCachéEstados();

            if (empty($reservasCreadas)) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'No se pudo crear ninguna reserva en las fechas solicitadas por solapamiento.',
                ], 409);
            }

            $cant = count($reservasCreadas);
            $mensaje = $cant > 1
                ? "Se crearon exitosamente {$cant} reservas recurrentes semanales."
                : "Reserva creada exitosamente.";

            return response()->json([
                'success' => true,
                'mensaje' => $mensaje,
                'id_reserva' => $reservasCreadas[0]->id_reserva,
                'total_creadas' => $cant,
                'datos' => [
                    'responsable' => $request->nombre,
                    'espacio' => $espacio->nombre_espacio,
                    'id_espacio' => $espacio->id_espacio,
                    'fecha' => $request->fecha,
                    'modulos' => $request->modulo_inicial.' - '.$request->modulo_final,
                    'hora' => $horaInicio,
                    'tipo' => $request->tipo === 'profesor' ? 'Académica' : 'Externa',
                    'estado' => $reservasCreadas[0]->estado === 'programada' ? 'Programada' : 'Activa',
                    'nombre_actividad' => $request->nombre_actividad,
                    'creado_por' => $usuario->name ?? 'Administrador',
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Datos inválidos: '.collect($e->errors())->flatten()->implode(', '),
                'errores' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('❌ Error al crear reserva en Quick Actions: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'mensaje' => 'Error interno del servidor: '.$e->getMessage(),
                'error_details' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verificar conflictos de planificación para un espacio/fecha/módulos
     * Retorna las clases programadas que chocan con la reserva solicitada.
     */
    public function verificarConflictos(Request $request)
    {
        try {
            $request->validate([
                'espacio' => 'required|string',
                'fecha' => 'required|date',
                'modulo_inicial' => 'required|integer|min:1|max:16',
                'modulo_final' => 'required|integer|min:1|max:16',
            ]);

            $conflictos = [];

            // 1. Verificar planificaciones académicas regulares (clases del semestre)
            $fechaReserva = Carbon::parse($request->fecha);
            $prefijosDias = ['DO', 'LU', 'MA', 'MI', 'JU', 'VI', 'SA'];
            $prefijoDia = $prefijosDias[$fechaReserva->dayOfWeek];
            $periodo = SemesterHelper::getCurrentPeriod($fechaReserva);

            // Buscar planificaciones que ocupen el espacio en los módulos solicitados
            for ($modulo = $request->modulo_inicial; $modulo <= $request->modulo_final; ++$modulo) {
                $idModulo = $prefijoDia.'.'.$modulo;

                $planificacion = Planificacion_Asignatura::with(['asignatura.profesor', 'asignatura.carrera', 'modulo'])
                    ->where('id_espacio', $request->espacio)
                    ->where('id_modulo', $idModulo)
                    ->whereHas('horario', function ($q) use ($periodo) {
                        $q->where('periodo', $periodo);
                    })
                    ->first();

                if ($planificacion) {
                    $conflictos[] = [
                        'tipo' => 'planificacion',
                        'modulo' => $modulo,
                        'id_modulo' => $idModulo,
                        'asignatura' => $planificacion->asignatura->nombre_asignatura ?? 'Sin nombre',
                        'codigo' => $planificacion->asignatura->codigo_asignatura ?? '-',
                        'profesor' => $planificacion->horario->profesor->name ?? 'Sin profesor',
                        'carrera' => $planificacion->asignatura->carrera->nombre ?? '-',
                    ];
                }
            }

            // 2. Verificar reservas existentes (activas o programadas)
            $reservaExistente = Reserva::with(['profesor', 'solicitante', 'asignatura'])
                ->where('id_espacio', $request->espacio)
                ->where('fecha_reserva', $request->fecha)
                ->whereIn('estado', ['activa', 'programada'])
                ->where(function ($q) use ($request) {
                    $q->where(function ($inner) use ($request) {
                        $inner
                            ->where('modulo_inicio', '<=', $request->modulo_final)
                            ->where('modulo_fin', '>=', $request->modulo_inicial);
                    });
                })
                ->get();

            foreach ($reservaExistente as $reserva) {
                $conflictos[] = [
                    'tipo' => 'reserva',
                    'id_reserva' => $reserva->id_reserva,
                    'estado' => $reserva->estado,
                    'modulo_inicio' => $reserva->modulo_inicio,
                    'modulo_fin' => $reserva->modulo_fin,
                    'responsable' => $reserva->profesor->name ?? $reserva->solicitante->nombre ?? 'Desconocido',
                    'asignatura' => $reserva->asignatura->nombre_asignatura ?? $reserva->nombre_actividad ?? '-',
                ];
            }

            return response()->json([
                'success' => true,
                'tiene_conflictos' => count($conflictos) > 0,
                'conflictos' => $conflictos,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al verificar conflictos: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'mensaje' => 'Error al verificar conflictos: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cambiar estado de un espacio.
     */
    public function cambiarEstadoEspacio(Request $request, $codigo)
    {
        try {
            // Validar el estado
            $request->validate([
                'estado' => 'required|in:Disponible,Ocupado,Mantenimiento',
            ]);

            // Buscar el espacio solo por id_espacio
            $espacio = Espacio::where('id_espacio', $codigo)->first();

            if (!$espacio) {
                Log::error("❌ Espacio {$codigo} no encontrado");

                return response()->json([
                    'success' => false,
                    'mensaje' => 'Espacio no encontrado',
                ], 404);
            }

            // Obtener estado anterior
            $estadoAnterior = $espacio->estado;

            // Si el nuevo estado es igual al anterior, no hacer nada
            if ($estadoAnterior === $request->estado) {
                Log::warning("⚠️ Espacio {$codigo} ya estaba en estado {$request->estado}");

                return response()->json([
                    'success' => true,
                    'mensaje' => "Espacio ya se encontraba en estado {$request->estado}",
                    'espacio' => [
                        'codigo' => $espacio->id_espacio,
                        'nombre' => $espacio->nombre_espacio,
                        'estado_anterior' => $estadoAnterior,
                        'estado_nuevo' => $request->estado,
                        'reservas_finalizadas' => [],
                    ],
                ]);
            }

            // Actualizar estado - verificamos qué campo usar
            // El campo 'estado' es el estándar definido en las migraciones
            if (Schema::connection('tenant')->hasColumn('espacios', 'estado_espacio')) {
                $espacio->estado_espacio = $request->estado;
            }

            // Siempre actualizamos el campo 'estado' que es el principal
            $espacio->estado = $request->estado;

            // Guardar cambios
            if (!$espacio->save()) {
                Log::error("❌ Error al guardar cambios en espacio {$codigo}");

                return response()->json([
                    'success' => false,
                    'mensaje' => 'Error al guardar los cambios del espacio',
                ], 500);
            }

            Log::info("✅ Estado del espacio {$codigo} actualizado de {$estadoAnterior} a {$request->estado}");

            // [NUEVO] Limpiar el caché de estados
            $this->limpiarCachéEstados();

            // Si el espacio se libera (pasa a Disponible), verificar reservas activas actuales
            $reservasFinalizadas = [];
            if ($request->estado === 'Disponible' && in_array($estadoAnterior, ['Ocupado', 'Reservado'])) {
                Log::info("🔄 Finalizando reservas activas para espacio {$codigo}");
                $reservasFinalizadas = $this->finalizarReservasActivasActuales($codigo);

                if (!empty($reservasFinalizadas)) {
                    Log::info('✅ Se finalizaron '.count($reservasFinalizadas)." reservas para espacio {$codigo}: ".implode(', ', $reservasFinalizadas));
                } else {
                    Log::info("ℹ️ No había reservas activas para finalizar en espacio {$codigo}");
                }
            }

            $mensaje = "Estado del espacio {$codigo} cambiado de {$estadoAnterior} a {$request->estado}";
            if (!empty($reservasFinalizadas)) {
                $cantidadReservas = count($reservasFinalizadas);
                $mensaje .= ". Se finalizaron automáticamente {$cantidadReservas} reserva(s) activa(s): ".implode(', ', $reservasFinalizadas);
            }

            return response()->json([
                'success' => true,
                'mensaje' => $mensaje,
                'espacio' => [
                    'codigo' => $espacio->id_espacio,
                    'nombre' => $espacio->nombre_espacio,
                    'estado_anterior' => $estadoAnterior,
                    'estado_nuevo' => $request->estado,
                    'reservas_finalizadas' => $reservasFinalizadas,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Estado inválido: '.collect($e->errors())->flatten()->implode(', '),
            ], 422);
        } catch (\Exception $e) {
            Log::error('❌ Error al cambiar estado de espacio: '.$e->getMessage().' | Trace: '.$e->getTraceAsString());

            return response()->json([
                'success' => false,
                'mensaje' => 'Error interno del servidor: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Liberar todos los espacios ocupados (Acción Masiva).
     */
    public function liberacionMasiva(Request $request)
    {
        try {
            // 1. Obtener todos los espacios que no estén en mantenimiento
            $espacios = Espacio::whereNotIn('estado', ['Mantención', 'Mantenimiento'])->get();

            $conteo = 0;
            foreach ($espacios as $espacio) {
                // Solo procesar si estaba ocupado o para asegurar estado disponible
                $estadoAnterior = $espacio->estado;

                // Cambiar estado a Disponible
                $espacio->estado = 'Disponible';
                if (Schema::connection('tenant')->hasColumn('espacios', 'estado_espacio')) {
                    $espacio->estado_espacio = 'Disponible';
                }
                $espacio->save();

                // Si estaba ocupado, finalizar reservas activas
                if ($estadoAnterior === 'Ocupado') {
                    $this->finalizarReservasActivasActuales($espacio->id_espacio);
                }
                ++$conteo;
            }

            // Limpiar caché de estados
            $this->limpiarCachéEstados();

            return response()->json([
                'success' => true,
                'mensaje' => "Se han procesado {$conteo} espacios. Todos han sido marcados como Disponibles.",
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Error en liberación masiva: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'mensaje' => 'Error al realizar la liberación masiva: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cambiar estado de una reserva.
     */
    public function cambiarEstadoReserva(Request $request, $id)
    {
        try {
            $request->validate([
                'estado' => 'required|in:activa,programada,finalizada,cancelada',
            ]);

            // Buscar reserva por id_reserva (que es string, no int)
            $reserva = Reserva::where('id_reserva', $id)->first();

            if (!$reserva) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Reserva no encontrada',
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

            // [NUEVO] Limpiar el caché de estados
            $this->limpiarCachéEstados();

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
                    'espacio_liberado' => $espacioLiberado,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Estado inválido: '.collect($e->errors())->flatten()->implode(', '),
            ], 422);
        } catch (\Exception $e) {
            Log::error('❌ Error al cambiar estado de reserva: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'mensaje' => 'Error interno del servidor: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Liberar espacio automáticamente al finalizar una reserva
     * Versión mejorada: libera el espacio si no hay más reservas activas actuales o futuras inmediatas.
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
                ->where('id_reserva', '!=', $reserva->id_reserva)  // Excluir la reserva que se está finalizando
                ->where('fecha_reserva', $fechaActual)
                ->get();

            // Verificar si alguna de estas reservas está actualmente en curso
            $hayReservaEnCurso = false;
            foreach ($otrasReservasActivas as $otraReserva) {
                $horaInicioOtra = $this->convertirHoraAMinutos($otraReserva->hora);

                // Estimar duración basada en módulos o asumir 1 hora
                $duracionEstimada = 60;  // minutos por defecto
                if ($otraReserva->observaciones && preg_match('/Módulos: (\d+)-(\d+)/', $otraReserva->observaciones, $matches)) {
                    $modulosCount = (int) $matches[2] - (int) $matches[1] + 1;
                    $duracionEstimada = $modulosCount * 50;  // 50 minutos por módulo
                } elseif (is_numeric($otraReserva->modulos)) {
                    $duracionEstimada = (int) $otraReserva->modulos * 50;
                }

                $horaFinEstimada = $horaInicioOtra + $duracionEstimada;

                // Si la hora actual está dentro del rango de esta reserva
                if ($horaActualEnMinutos >= $horaInicioOtra && $horaActualEnMinutos <= $horaFinEstimada) {
                    $hayReservaEnCurso = true;

                    break;
                }
            }

            // Solo liberar el espacio si:
            $fechaReserva = $reserva->fecha_reserva instanceof Carbon
                ? $reserva->fecha_reserva->format('Y-m-d')
                : substr($reserva->fecha_reserva, 0, 10);

            if ($fechaReserva === $fechaActual && !$hayReservaEnCurso) {
                $espacio = Espacio::where('id_espacio', $reserva->id_espacio)->first();
                if ($espacio) {
                    $estadoActual = Schema::hasColumn('espacios', 'estado_espacio') ? $espacio->estado_espacio : $espacio->estado;

                    if ($estadoActual === 'Ocupado') {
                        if (Schema::hasColumn('espacios', 'estado_espacio')) {
                            $espacio->estado_espacio = 'Disponible';
                        } else {
                            $espacio->estado = 'Disponible';
                        }
                        $espacio->save();

                        return true;
                    }
                }
            } else {
                $motivo = $fechaReserva !== $fechaActual ? 'no es del día actual' : 'hay otras reservas activas en curso';
            }

            return false;
        } catch (\Exception $e) {
            Log::error('❌ Error al verificar liberación de espacio: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Ocupar espacio automáticamente si la reserva creada es actual
     * (misma fecha y el módulo actual coincide con el horario de la reserva).
     */
    private function ocuparEspacioSiEsReservaActual($reserva)
    {
        try {
            // Obtener fecha y hora actual
            $fechaActual = now()->format('Y-m-d');
            $horaActual = now()->format('H:i:s');

            // Verificar si la reserva es del día actual
            // NOTA: fecha_reserva es un objeto Carbon (por el cast), por lo que necesitamos formatearlo
            $fechaReserva = $reserva->fecha_reserva instanceof Carbon
                ? $reserva->fecha_reserva->format('Y-m-d')
                : $reserva->fecha_reserva;

            if ($fechaReserva !== $fechaActual) {
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
                13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'],
                14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'],
                15 => ['inicio' => '22:10:00', 'fin' => '23:00:00'],
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
                return false;
            }

            // Verificar si la reserva incluye el módulo actual
            // Para reservas recién creadas, usar la información de módulos
            $modulosReserva = $reserva->modulos;
            $moduloInicio = null;
            $moduloFin = null;

            // Primero intentar extraer de las observaciones que contienen "Módulos: X-Y"
            if ($reserva->observaciones && preg_match('/Módulos: (\d+)-(\d+)/', $reserva->observaciones, $matches)) {
                $moduloInicio = (int) $matches[1];
                $moduloFin = (int) $matches[2];
            } elseif ($modulosReserva && preg_match('/(\d+)\s*-\s*(\d+)/', $modulosReserva, $matches)) {
                // Si modulos tiene formato "X - Y"
                $moduloInicio = (int) $matches[1];
                $moduloFin = (int) $matches[2];
            } elseif (is_numeric($modulosReserva)) {
                // Si modulos es la duración, usar la hora de inicio para determinar módulos
                $horaReserva = $reserva->hora;
                foreach ($horariosModulos as $modulo => $horario) {
                    if ($horaReserva >= $horario['inicio'] && $horaReserva <= $horario['fin']) {
                        $moduloInicio = $modulo;
                        $moduloFin = $modulo + (int) $modulosReserva - 1;
                        break;
                    }
                }
            }

            // Si aún no se determinaron, usar la hora de la reserva
            if (!$moduloInicio || !$moduloFin) {
                $horaReserva = $reserva->hora;
                foreach ($horariosModulos as $modulo => $horario) {
                    if ($horaReserva >= $horario['inicio'] && $horaReserva <= $horario['fin']) {
                        $moduloInicio = $modulo;
                        // Asumir duración de 1 módulo si no se puede determinar
                        $moduloFin = is_numeric($modulosReserva) ? $modulo + (int) $modulosReserva - 1 : $modulo;
                        break;
                    }
                }
            }

            // Verificar si el módulo actual está dentro del rango de la reserva
            // IMPORTANTE: También verificar que la hora de inicio ya haya pasado
            if ($moduloInicio && $moduloFin && $moduloActual >= $moduloInicio && $moduloActual <= $moduloFin) {
                // Verificar que la hora de inicio de la reserva ya haya llegado o pasado
                $horaReserva = $reserva->hora;
                if ($horaActual >= $horaReserva) {
                    // Es una reserva actual - ocupar el espacio
                    $espacio = Espacio::where('id_espacio', $reserva->id_espacio)->first();
                    if ($espacio) {
                        $estadoActual = Schema::hasColumn('espacios', 'estado_espacio') ? $espacio->estado_espacio : $espacio->estado;
                        if ($estadoActual === 'Disponible') {
                            if (Schema::hasColumn('espacios', 'estado_espacio')) {
                                $espacio->estado_espacio = 'Ocupado';
                            } else {
                                $espacio->estado = 'Ocupado';
                            }
                            $espacio->save();

                            return true;
                        }
                    } elseif ($espacio) {
                    }
                } else {
                }
            } else {
            }

            return false;
        } catch (\Exception $e) {
            Log::error('❌ Error al verificar ocupación de espacio: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Finalizar reservas activas cuando se libera un espacio manualmente
     * Finaliza la última reserva activa de hoy y todas las posteriores en cascada.
     */
    private function finalizarReservasActivasActuales($codigoEspacio)
    {
        try {
            // Obtener fecha y hora actual
            $fechaActual = now()->format('Y-m-d');
            $horaActual = now()->format('H:i:s');

            // 🔧 FIX: Buscar TODAS las reservas activas para este espacio (no solo de hoy)
            // Cuando se libera un espacio manualmente, se deben finalizar todas las reservas activas
            $reservasActivas = Reserva::where('id_espacio', $codigoEspacio)
                ->where('estado', 'activa')
                ->orderBy('fecha_reserva')
                ->orderBy('hora')
                ->get();

            if ($reservasActivas->isEmpty()) {
                return [];
            }

            $reservasFinalizadas = [];

            // 🔧 FIX: Finalizar TODAS las reservas activas del espacio
            // Ya que el espacio se está liberando manualmente, todas las reservas activas deben finalizarse
            foreach ($reservasActivas as $reserva) {
                try {
                    $fechaReserva = $reserva->fecha_reserva instanceof Carbon
                        ? $reserva->fecha_reserva->format('Y-m-d')
                        : Carbon::parse($reserva->fecha_reserva)->format('Y-m-d');

                    // Diferenciar el motivo según si la reserva es de hoy o futura
                    if ($fechaReserva === $fechaActual) {
                        // Reserva de hoy
                        $horaInicioReserva = $this->convertirHoraAMinutos($reserva->hora);
                        $horaActualEnMinutos = $this->convertirHoraAMinutos($horaActual);

                        if ($horaActualEnMinutos >= $horaInicioReserva) {
                            $motivo = 'FINALIZADA: El espacio fue liberado manualmente durante la clase';
                        } else {
                            $motivo = 'FINALIZADA: El espacio fue liberado manualmente (reserva futura no ejecutada)';
                        }
                    } else {
                        // Reserva futura
                        $motivo = 'FINALIZADA: El espacio fue liberado manualmente (reserva futura cancelada)';
                    }

                    $reserva->estado = 'finalizada';
                    $reserva->hora_salida = $horaActual;
                    $reserva->observaciones = ($reserva->observaciones ?? '')." | {$motivo} el ".now()->format('d/m/Y H:i:s');
                    $reserva->save();

                    $reservasFinalizadas[] = $reserva->id_reserva;

                    Log::info("✅ Reserva {$reserva->id_reserva} finalizada al liberar espacio {$codigoEspacio}");
                } catch (\Exception $e) {
                    Log::error("❌ Error al finalizar reserva {$reserva->id_reserva}: ".$e->getMessage());
                }
            }

            return $reservasFinalizadas;
        } catch (\Exception $e) {
            Log::error('❌ Error al finalizar reservas activas: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Procesar información de módulos y horarios para mostrar en frontend.
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
            13 => ['inicio' => '20:10', 'fin' => '21:00'],
            14 => ['inicio' => '21:10', 'fin' => '22:00'],
            15 => ['inicio' => '22:10', 'fin' => '23:00'],
        ];

        $moduloInicio = null;
        $moduloFin = null;
        $cantidadModulos = 1;

        // Intentar extraer de observaciones primero
        if ($reserva->observaciones && preg_match('/Módulos: (\d+)-(\d+)/', $reserva->observaciones, $matches)) {
            $moduloInicio = (int) $matches[1];
            $moduloFin = (int) $matches[2];
            $cantidadModulos = $moduloFin - $moduloInicio + 1;
        } elseif ($reserva->modulos && preg_match('/(\d+)\s*-\s*(\d+)/', $reserva->modulos, $matches)) {
            // Si modulos tiene formato "X - Y"
            $moduloInicio = (int) $matches[1];
            $moduloFin = (int) $matches[2];
            $cantidadModulos = $moduloFin - $moduloInicio + 1;
        } else {
            // Determinar por hora de inicio y duración en módulos
            $horaReserva = substr($reserva->hora, 0, 5);  // HH:MM
            foreach ($horariosModulos as $modulo => $horario) {
                if ($horaReserva >= $horario['inicio'] && $horaReserva <= $horario['fin']) {
                    $moduloInicio = $modulo;
                    $cantidadModulos = is_numeric($reserva->modulos) ? (int) $reserva->modulos : 1;
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
                'texto_completo' => "Módulos {$moduloInicio}-{$moduloFin} ({$horaInicio} - {$horaFin}) • {$cantidadModulos} módulo".($cantidadModulos > 1 ? 's' : ''),
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
            'texto_completo' => 'Hora: '.substr($reserva->hora, 0, 5)." • Duración: {$cantidadModulos} módulo".($cantidadModulos > 1 ? 's' : ''),
        ];
    }

    /**
     * Convertir hora en formato H:i:s a minutos desde medianoche
     * Para comparaciones más fáciles de horarios.
     */
    private function convertirHoraAMinutos($hora)
    {
        $partes = explode(':', $hora);
        $horas = (int) $partes[0];
        $minutos = (int) $partes[1];

        return ($horas * 60) + $minutos;
    }

    /**
     * Mostrar formulario para editar una reserva.
     */
    public function editarReserva($id)
    {
        try {
            // Buscar reserva
            $reserva = Reserva::where('id_reserva', $id)
                ->with(['espacio', 'profesor', 'solicitante'])
                ->first();

            if (!$reserva) {
                return redirect()
                    ->route('quick-actions.gestionar-reservas')
                    ->with('error', 'Reserva no encontrada');
            }

            // Verificar que la reserva esté activa o programada
            if ($reserva->estado !== 'activa' && $reserva->estado !== 'programada') {
                return redirect()
                    ->route('quick-actions.gestionar-reservas')
                    ->with('error', 'Solo se pueden editar reservas activas o programadas');
            }

            // Obtener espacios disponibles (usando id_espacio como identificador)
            $espacios = Espacio::select('id_espacio', 'nombre_espacio')
                ->orderBy('id_espacio')
                ->get();

            return view('quick_actions.editar-reserva', compact('reserva', 'espacios'));
        } catch (\Exception $e) {
            Log::error('Error al cargar formulario de edición de reserva: '.$e->getMessage());

            return redirect()
                ->route('quick-actions.gestionar-reservas')
                ->with('error', 'Error al cargar la reserva: '.$e->getMessage());
        }
    }

    /**
     * Actualizar una reserva existente.
     */
    public function actualizarReserva(Request $request, $id)
    {
        try {
            // Validación - usar id_espacio en lugar de codigo_espacio
            $request->validate([
                'id_espacio' => 'required|string',
                'fecha' => 'required|date',
                'hora' => 'required',
                'modulos' => 'required|integer|min:1',
                'modulo_inicio' => 'required|integer',
                'modulo_fin' => 'required|integer',
                'observaciones' => 'nullable|string',
            ]);

            // Buscar reserva
            $reserva = Reserva::where('id_reserva', $id)->first();

            if (!$reserva) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Reserva no encontrada',
                ], 404);
            }

            // Verificar que esté activa o programada
            if ($reserva->estado !== 'activa' && $reserva->estado !== 'programada') {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Solo se pueden editar reservas activas o programadas',
                ], 400);
            }

            // Obtener el espacio usando id_espacio
            $espacio = Espacio::where('id_espacio', $request->id_espacio)->first();

            if (!$espacio) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Espacio no encontrado',
                ], 404);
            }

            // Verificar conflictos de disponibilidad (excluyendo esta misma reserva)
            $conflicto = Reserva::where('id_espacio', $request->id_espacio)
                ->where('fecha_reserva', $request->fecha)
                ->where('id_reserva', '!=', $id)
                ->whereIn('estado', ['activa', 'programada'])
                ->where(function ($q) use ($request) {
                    $q
                        ->where('modulo_inicio', '<=', $request->modulo_fin)
                        ->where('modulo_fin', '>=', $request->modulo_inicio);
                })
                ->exists();

            if ($conflicto) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'El espacio ya está reservado para el horario seleccionado',
                ], 400);
            }

            // Actualizar datos
            $reserva->id_espacio = $espacio->id_espacio;
            $reserva->fecha_reserva = $request->fecha;
            $reserva->hora = $request->hora;
            $reserva->modulos = $request->modulos;
            $reserva->modulo_inicio = $request->modulo_inicio;
            $reserva->modulo_fin = $request->modulo_fin;
            $reserva->observaciones = $request->observaciones;

            // Marcar como editada (se usa updated_at automáticamente por Laravel)
            $reserva->touch();  // Esto actualiza el timestamp updated_at

            $reserva->save();

            // [NUEVO] Limpiar el caché de estados
            $this->limpiarCachéEstados();

            return response()->json([
                'success' => true,
                'mensaje' => 'Reserva actualizada correctamente',
                'reserva' => [
                    'id' => $reserva->id_reserva,
                    'espacio' => $espacio->id_espacio,
                    'fecha' => $reserva->fecha_reserva,
                    'hora' => $reserva->hora,
                    'modulos' => $reserva->modulos,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Datos inválidos: '.collect($e->errors())->flatten()->implode(', '),
            ], 422);
        } catch (\Exception $e) {
            Log::error('❌ Error al actualizar reserva: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'mensaje' => 'Error interno del servidor: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Limpiar el caché de estados de espacios para un tenant.
     */
    private function limpiarCachéEstados()
    {
        try {
            $tenantId = Tenant::current()?->id;
            if ($tenantId) {
                Cache::forget("estados_espacios_{$tenantId}");
                Log::info("Caché de estados de espacios limpiado para tenant: {$tenantId}");
            }
        } catch (\Exception $e) {
            Log::error('Error al limpiar caché de estados: '.$e->getMessage());
        }
    }

    /**
     * Envía el correo de confirmación de reserva al profesor o solicitante.
     */
    private function enviarCorreoReserva(Reserva $reserva): void
    {
        try {
            $reserva->load(['profesor', 'solicitante', 'espacio', 'asignatura']);
            $email = $this->resolverEmailReserva($reserva);
            if ($email) {
                Mail::to($email)->send(new ConfirmacionReserva($reserva));
                Log::info("📧 Correo de confirmación de reserva enviado en Quick Actions a: {$email} [Reserva: {$reserva->id_reserva}]");
            }
        } catch (\Exception $e) {
            Log::error('❌ Error al enviar correo de confirmación de reserva en Quick Actions: ' . $e->getMessage(), [
                'id_reserva' => $reserva->id_reserva,
            ]);
        }
    }

    /**
     * Resuelve el correo electrónico del responsable de la reserva.
     */
    private function resolverEmailReserva(Reserva $reserva): ?string
    {
        if ($reserva->run_profesor && $reserva->profesor) {
            return $reserva->profesor->email ?: null;
        }
        if ($reserva->run_solicitante && $reserva->solicitante) {
            return $reserva->solicitante->correo ?: null;
        }
        return null;
    }

    /**
     * Mostrar gestor de reservas de salas de estudio.
     */
    public function gestionarSalasEstudio()
    {
        return view('quick_actions.gestionar-salas-estudio');
    }

    /**
     * Obtener reservas de salas de estudio en JSON con datos enriquecidos.
     */
    public function getReservasSalasEstudio(Request $request)
    {
        try {
            $tenant = session()->has('tenant_id') ? Tenant::find(session('tenant_id')) : Tenant::where('is_active', true)->first();
            if ($tenant && $tenant->database) {
                config(['database.connections.tenant.database' => $tenant->database]);
                app('db')->purge('tenant');
                app('db')->disconnect('tenant');
            }

            // Obtener espacios de tipo Sala de Estudio
            $espaciosEstudio = Espacio::on('tenant')
                ->where('tipo_espacio', 'like', '%estudio%')
                ->orWhere('tipo_espacio', 'Sala de Estudio')
                ->pluck('id_espacio');

            $query = Reserva::on('tenant')
                ->where(function($q) use ($espaciosEstudio) {
                    $q->whereIn('id_espacio', $espaciosEstudio)
                      ->orWhere('observaciones', 'like', '%Sala de estudio%')
                      ->orWhere('tipo_reserva', 'espontanea');
                })
                ->orderBy('fecha_reserva', 'desc')
                ->orderBy('hora', 'desc');

            if ($request->filled('estado') && $request->estado !== 'todos') {
                $query->where('estado', $request->estado);
            }

            if ($request->filled('espacio') && $request->espacio !== 'todos') {
                $query->where('id_espacio', $request->espacio);
            }

            if ($request->filled('fecha')) {
                $query->whereDate('fecha_reserva', $request->fecha);
            }

            $reservasRaw = $query->get();

            $reservas = $reservasRaw->map(function ($reserva) {
                $nombreResponsable = 'Sin asignar';
                $runResponsable = 'N/A';

                if ($reserva->run_solicitante) {
                    $solicitante = Solicitante::on('tenant')->where('run_solicitante', $reserva->run_solicitante)->first();
                    $nombreResponsable = $solicitante ? $solicitante->nombre : $reserva->run_solicitante;
                    $runResponsable = $reserva->run_solicitante;
                } elseif ($reserva->run_profesor) {
                    $profesor = Profesor::on('tenant')->where('run_profesor', $reserva->run_profesor)->first();
                    $nombreResponsable = $profesor ? $profesor->name : $reserva->run_profesor;
                    $runResponsable = $reserva->run_profesor;
                }

                $espacio = Espacio::on('tenant')->where('id_espacio', $reserva->id_espacio)->first();
                $nombreEspacio = $espacio ? ($espacio->nombre_espacio ?? $espacio->id_espacio) : $reserva->id_espacio;

                // Calcular minutos transcurridos si está activa
                $minutosTranscurridos = 0;
                $vencida = false;
                $proximaVencer = false;
                if ($reserva->estado === 'activa' && $reserva->fecha_reserva && $reserva->hora) {
                    $inicioStr = ($reserva->fecha_reserva instanceof \Carbon\Carbon ? $reserva->fecha_reserva->format('Y-m-d') : $reserva->fecha_reserva) . ' ' . $reserva->hora;
                    $inicio = \Carbon\Carbon::parse($inicioStr);
                    $minutosTranscurridos = (int) $inicio->diffInMinutes(now(), false);

                    if ($minutosTranscurridos >= 120) {
                        $vencida = true;
                    } elseif ($minutosTranscurridos >= 105) {
                        $proximaVencer = true;
                    }
                }

                return [
                    'id_reserva' => $reserva->id_reserva,
                    'id_espacio' => $reserva->id_espacio,
                    'nombre_espacio' => $nombreEspacio,
                    'run_responsable' => $runResponsable,
                    'nombre_responsable' => $nombreResponsable,
                    'fecha_reserva' => $reserva->fecha_reserva ? ($reserva->fecha_reserva instanceof \Carbon\Carbon ? $reserva->fecha_reserva->format('d/m/Y') : \Carbon\Carbon::parse($reserva->fecha_reserva)->format('d/m/Y')) : 'N/A',
                    'hora_inicio' => $reserva->hora ? substr($reserva->hora, 0, 5) : 'N/A',
                    'hora_salida' => $reserva->hora_salida ? substr($reserva->hora_salida, 0, 5) : null,
                    'estado' => $reserva->estado,
                    'minutos_transcurridos' => max(0, $minutosTranscurridos),
                    'vencida' => $vencida,
                    'proxima_vencer' => $proximaVencer,
                    'observaciones' => $reserva->observaciones,
                ];
            });

            return response()->json([
                'success' => true,
                'reservas' => $reservas,
                'total' => $reservas->count(),
                'vigentes' => $reservas->where('estado', 'activa')->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error en getReservasSalasEstudio: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'reservas' => []], 500);
        }
    }

    /**
     * Procesar escaneo de carnet para salas de estudio
     */
    public function procesarEscaneoSalaEstudio(Request $request)
    {
        try {
            $validated = $request->validate([
                'id_espacio' => 'nullable|string',
                'run' => 'required|string',
                'nombre' => 'nullable|string',
                'correo' => 'nullable|string|email',
                'telefono' => 'nullable|string',
                'tipo_solicitante' => 'nullable|string',
                'paso' => 'nullable|string',
            ]);

            $idEspacioRaw = $validated['id_espacio'] ?? null;
            $rawRun = $validated['run'];
            $paso = $validated['paso'] ?? ($idEspacioRaw && $idEspacioRaw !== 'DESCONOCIDO' ? 'asignar_sala' : 'validar_usuario');

            // Extraer RUN de parámetros de URL o formatos QR
            if (preg_match('#[?&]run=([^&/]+)#i', $rawRun, $m)) {
                $runClean = preg_replace('/[^0-9kK]/', '', $m[1]);
            } elseif (preg_match('#RUN[^0-9]*([0-9.Kk-]+)#i', $rawRun, $m)) {
                $runClean = preg_replace('/[^0-9kK]/', '', $m[1]);
            } elseif (preg_match('#([0-9]{1,2}(?:\.[0-9]{3}){2}-?[0-9Kk]|[0-9]{7,9}-?[0-9Kk]?)#i', $rawRun, $m)) {
                $runClean = preg_replace('/[^0-9kK]/', '', $m[1]);
            } else {
                $runClean = preg_replace('/[^0-9kK]/', '', $rawRun);
            }

            if (empty($runClean)) {
                return response()->json(['success' => false, 'message' => 'RUN no válido.'], 422);
            }

            $runBase = preg_replace('/[^0-9]/', '', $runClean);
            $dv = strtoupper(substr($runClean, -1));
            $runConGuion = (is_numeric($dv) ? $runClean : (strlen($runBase) >= 7 ? substr($runBase, 0, -1) . '-' . $dv : $runClean));
            $runSinGuion = $runBase . (is_numeric($dv) ? '' : $dv);

            // Verificar vetos
            $veto = \App\Models\VetoSalaEstudio::vetoActivo($runClean) ?? \App\Models\VetoSalaEstudio::vetoActivo($runBase);
            if ($veto) {
                return response()->json([
                    'success' => false,
                    'message' => '🚫 Acceso Denegado: Usuario vetado. Motivo: ' . $veto->observacion,
                    'vetado' => true,
                ], 403);
            }

            // Búsqueda flexible de usuario o solicitante
            $usuario = User::whereIn('run', [$runClean, $runBase, $runConGuion, $runSinGuion])->first();
            $solicitante = Solicitante::on('tenant')->whereIn('run_solicitante', [$runClean, $runBase, $runConGuion, $runSinGuion])->first();

            $run = $solicitante ? $solicitante->run_solicitante : ($usuario ? $usuario->run : $runClean);

            // Si se enviaron datos de registro nuevos y no existía
            if (!$usuario && !$solicitante && !empty($validated['nombre']) && !empty($validated['correo'])) {
                $solicitante = Solicitante::on('tenant')->create([
                    'run_solicitante' => $runClean,
                    'nombre' => $validated['nombre'],
                    'correo' => $validated['correo'],
                    'telefono' => $validated['telefono'] ?? null,
                    'tipo_solicitante' => $validated['tipo_solicitante'] ?? 'estudiante',
                ]);
                $run = $solicitante->run_solicitante;
            }

            // Si aún no existe, solicitar registro
            if (!$usuario && !$solicitante) {
                return response()->json([
                    'success' => false,
                    'requiere_registro' => true,
                    'run' => $runClean,
                    'message' => 'El usuario no se encuentra registrado. Por favor complete el registro.',
                ]);
            }

            $nombreAlumno = $solicitante ? $solicitante->nombre : ($usuario ? $usuario->name : 'Usuario');

            // Verificar si ya hay una reserva activa del responsable en CUALQUIER sala de estudio => DEVOLUCIÓN
            $reservaActiva = Reserva::on('tenant')
                ->where('estado', 'activa')
                ->whereNull('hora_salida')
                ->where(function($q) use ($runClean, $runBase, $runConGuion, $runSinGuion) {
                    $q->whereIn('run_solicitante', [$runClean, $runBase, $runConGuion, $runSinGuion])
                      ->orWhereIn('run_profesor', [$runClean, $runBase, $runConGuion, $runSinGuion]);
                })
                ->whereDate('fecha_reserva', today())
                ->first();

            if ($reservaActiva) {
                // Devolución / Cierre automático
                $reservaActiva->estado = 'finalizada';
                $reservaActiva->hora_salida = now()->format('H:i:s');
                $reservaActiva->observaciones = trim(($reservaActiva->observaciones ?? '') . ' | Sala devuelta mediante escáner');
                $reservaActiva->save();

                // Liberar espacio
                $espacioLiberado = Espacio::on('tenant')->where('id_espacio', $reservaActiva->id_espacio)->first();
                if ($espacioLiberado) {
                    $espacioLiberado->estado = 'Disponible';
                    $espacioLiberado->save();
                }

                return response()->json([
                    'success' => true,
                    'devolucion' => true,
                    'nombre_estudiante' => $nombreAlumno,
                    'id_espacio' => $reservaActiva->id_espacio,
                    'message' => "Devolución registrada exitosamente para {$nombreAlumno} en la sala {$reservaActiva->id_espacio}.",
                ]);
            }

            // Si es Paso 1 (Validar usuario y no es devolución) => Requerir escaneo de sala SIN crear reserva aún
            if ($paso === 'validar_usuario' || empty($idEspacioRaw) || $idEspacioRaw === 'DESCONOCIDO') {
                return response()->json([
                    'success' => true,
                    'requiere_escaneo_sala' => true,
                    'nombre_estudiante' => $nombreAlumno,
                    'run' => $runClean,
                ]);
            }

            // PASO 2: CREAR RESERVA DE 2 HORAS PARA LA SALA ESPECIFICADA
            $countReservasActivas = Reserva::on('tenant')
                ->where('estado', 'activa')
                ->whereNull('hora_salida')
                ->where(function($q) use ($runClean, $runBase, $runConGuion, $runSinGuion) {
                    $q->whereIn('run_solicitante', [$runClean, $runBase, $runConGuion, $runSinGuion])
                      ->orWhereIn('run_profesor', [$runClean, $runBase, $runConGuion, $runSinGuion]);
                })
                ->whereDate('fecha_reserva', today())
                ->count();

            if ($countReservasActivas >= 2) {
                return response()->json([
                    'success' => false,
                    'message' => "🚫 Límite alcanzado: El usuario {$nombreAlumno} ya posee 2 salas de estudio reservadas activas.",
                ], 422);
            }

            $idEspacioLimpio = strtoupper(str_replace("'", "-", $idEspacioRaw));

            $espacio = Espacio::on('tenant')->whereIn('id_espacio', [$idEspacioRaw, $idEspacioLimpio])->first();
            if (!$espacio) {
                $idSoloAlfa = preg_replace('/[^A-Z0-9]/i', '', $idEspacioRaw);
                $espacio = Espacio::on('tenant')->get()->first(function($e) use ($idSoloAlfa) {
                    return preg_replace('/[^A-Z0-9]/i', '', $e->id_espacio) === $idSoloAlfa;
                });
            }

            if (!$espacio) {
                return response()->json(['success' => false, 'message' => "El espacio '{$idEspacioRaw}' no existe."], 404);
            }
            $idEspacio = $espacio->id_espacio;

            $ahora = now();
            $nuevaReserva = Reserva::on('tenant')->create([
                'id_reserva' => Reserva::generarIdUnico(),
                'id_espacio' => $idEspacio,
                'run_solicitante' => $run,
                'fecha_reserva' => $ahora->toDateString(),
                'hora' => $ahora->format('H:i:s'),
                'estado' => 'activa',
                'tipo_reserva' => 'espontanea',
                'observaciones' => 'Sala de Estudio - Lector QR (2h max)',
            ]);

            // Marcar espacio como ocupado
            $espacio->estado = 'Ocupado';
            $espacio->save();

            return response()->json([
                'success' => true,
                'reserva_creada' => true,
                'nombre_estudiante' => $nombreAlumno,
                'id_espacio' => $idEspacio,
                'message' => "Reserva iniciada exitosamente por 2 horas para {$nombreAlumno} en la sala {$idEspacio}.",
                'reserva' => $nuevaReserva,
            ]);


        } catch (\Exception $e) {
            Log::error('Error en procesarEscaneoSalaEstudio: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al procesar escaneo: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Verificar en segundo plano reservas activas de salas de estudio para notificaciones
     */
    public function verificarNotificacionesSalasEstudio()
    {
        try {
            $espaciosEstudio = Espacio::on('tenant')
                ->where('tipo_espacio', 'like', '%estudio%')
                ->orWhere('tipo_espacio', 'Sala de Estudio')
                ->pluck('id_espacio');

            $reservasActivas = Reserva::on('tenant')
                ->where('estado', 'activa')
                ->where(function($q) use ($espaciosEstudio) {
                    $q->whereIn('id_espacio', $espaciosEstudio)
                      ->orWhere('observaciones', 'like', '%Sala de estudio%')
                      ->orWhere('tipo_reserva', 'espontanea');
                })
                ->whereDate('fecha_reserva', today())
                ->get();

            $advertenciasEnviadas = 0;
            $vencidosEnviadas = 0;

            foreach ($reservasActivas as $reserva) {
                if ($reserva->fecha_reserva && $reserva->hora) {
                    $inicioStr = ($reserva->fecha_reserva instanceof \Carbon\Carbon ? $reserva->fecha_reserva->format('Y-m-d') : $reserva->fecha_reserva) . ' ' . $reserva->hora;
                    $inicio = \Carbon\Carbon::parse($inicioStr);
                    $minutosTranscurridos = (int) $inicio->diffInMinutes(now(), false);

                    $solicitante = Solicitante::on('tenant')->where('run_solicitante', $reserva->run_solicitante)->first();
                    $nombreAlumno = $solicitante ? $solicitante->nombre : ($reserva->run_solicitante ?? 'Alumno');

                    // Notificación a las 2 horas cumplidas (120 min)
                    if ($minutosTranscurridos >= 120) {
                        \App\Models\Notificacion::crearNotificacionSalaEstudioVencida($reserva, $nombreAlumno);
                        $vencidosEnviadas++;
                    }
                    // Notificación a los 105 min (15 min antes de las 2 horas)
                    elseif ($minutosTranscurridos >= 105) {
                        \App\Models\Notificacion::crearNotificacionSalaEstudioAdvertencia($reserva, $nombreAlumno);
                        $advertenciasEnviadas++;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'advertencias_enviadas' => $advertenciasEnviadas,
                'vencidos_enviadas' => $vencidosEnviadas,
            ]);
        } catch (\Exception $e) {
            Log::error('Error en verificarNotificacionesSalasEstudio: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}

