<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Models\Asignatura;
use App\Models\AsistenciaEstudiante;
use App\Models\Espacio;
use App\Models\Estudiante;
use App\Models\ProfesorColaborador;
use App\Models\Reserva;
use App\Models\SesionAsistencia;
use App\Models\Tenant;
use App\Policies\ClaseDocentePolicy;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AsistenciaController extends Controller
{
    protected ClaseDocentePolicy $policy;

    public function __construct(ClaseDocentePolicy $policy)
    {
        $this->policy = $policy;
    }

    /**
     * Muestra la pantalla para pasar asistencia de una clase.
     */
    public function show(Request $request, string $id)
    {
        $user = Auth::user();
        $docenteRun = (string) ($request->attributes->get('docente_activo_run') ?? $user->run);
        $esSuperadmin = (bool) $request->attributes->get('es_superadmin_simulando', false);
        $hoy = Carbon::today();
        $fechaConsultaStr = $request->query('fecha');
        $fechaConsulta = $fechaConsultaStr ? Carbon::parse($fechaConsultaStr) : Carbon::today();
        $esHoy = $fechaConsulta->isToday();
        $esFechaPasada = $fechaConsulta->isBefore($hoy);

        // 1. Resolver si es asignatura o colaboración temporal
        $esColaboracion = str_starts_with($id, 'colab_');
        $asignatura = null;
        $colaborador = null;
        $tituloClase = '';
        $codigoClase = '';
        $seccionClase = '';
        $rolDocente = 'titular';

        if ($esColaboracion) {
            $colabId = substr($id, 6);
            $colaborador = ProfesorColaborador::with(['asignatura.estudiantes', 'asignatura.carrera', 'planificaciones.espacio', 'planificaciones.modulo'])->findOrFail($colabId);

            if (!$this->policy->pasarAsistenciaColaboracion($user, $colaborador)) {
                abort(403, 'No tienes autorización para gestionar la asistencia de esta colaboración.');
            }

            $tituloClase = $colaborador->nombre_asignatura;
            $codigoClase = $colaborador->asignatura?->codigo_asignatura ?? 'TEMP-' . $colaborador->id;
            $seccionClase = $colaborador->asignatura?->seccion ?? 'N/A';
            $rolDocente = 'colaborador';
            $asignatura = $colaborador->asignatura;
        } else {
            $asignatura = Asignatura::with(['carrera', 'estudiantes', 'planificaciones.espacio', 'planificaciones.modulo'])->findOrFail($id);

            if (!$this->policy->pasarAsistenciaAsignatura($user, $asignatura)) {
                abort(403, 'No tienes autorización para gestionar la asistencia de esta asignatura.');
            }

            $tituloClase = $asignatura->nombre_asignatura;
            $codigoClase = $asignatura->codigo_asignatura;
            $seccionClase = $asignatura->seccion;

            if ($asignatura->run_profesor_reemplazo && (string)$asignatura->run_profesor_reemplazo === $docenteRun) {
                $rolDocente = 'reemplazo';
            } elseif ((string)$asignatura->run_profesor === $docenteRun) {
                $rolDocente = 'titular';
            } else {
                $rolDocente = 'colaborador';
            }
        }

        // 2. Verificar ingreso al espacio (opcional, para vincular a la reserva si existe)
        $reservaActiva = Reserva::where('fecha_reserva', $fechaConsulta->format('Y-m-d'))
            ->where('estado', 'activa')
            ->whereNull('hora_salida')
            ->where('run_profesor', $docenteRun)
            ->with('espacio')
            ->first();

        $bloqueadoPorIngreso = false;
        $mensajeBloqueo = null;

        // 3. Buscar o preparar sesión de asistencia para la fecha seleccionada
        $sesionQuery = SesionAsistencia::where('fecha', $fechaConsulta->format('Y-m-d'));
        if ($esColaboracion && !$asignatura) {
            $sesionQuery->where('id_profesor_colaborador', $colaborador->id);
        } else {
            $sesionQuery->where('id_asignatura', $asignatura->id_asignatura);
        }
        $sesion = $sesionQuery->with(['asistencias.estudiante', 'espacio'])->latest()->first();

        // 4. Lista de estudiantes inscritos y asistencias
        $estudiantesData = collect();

        if ($asignatura) {
            $inscritos = $asignatura->estudiantes;
            foreach ($inscritos as $est) {
                $asistenciaExistente = $sesion ? $sesion->asistencias->firstWhere('estudiante_id', $est->id) : null;
                $estudiantesData->push([
                    'id' => $est->id,
                    'run' => $est->run,
                    'nombre' => $est->nombre,
                    'email' => $est->email,
                    'inscrito' => true,
                    'presente' => $asistenciaExistente ? (bool) $asistenciaExistente->presente : false, // Por defecto desmarcado; se van marcando al escanear el QR o manualmente
                    'observacion' => $asistenciaExistente?->observacion,
                ]);
            }
        }

        // Agregar estudiantes no inscritos agregados manualmente en esta sesión
        if ($sesion) {
            $noInscritosEnSesion = $sesion->asistencias->where('inscrito', false);
            foreach ($noInscritosEnSesion as $asistNoInscrito) {
                $est = $asistNoInscrito->estudiante;
                if ($est && !$estudiantesData->contains('id', $est->id)) {
                    $estudiantesData->push([
                        'id' => $est->id,
                        'run' => $est->run,
                        'nombre' => $est->nombre,
                        'email' => $est->email,
                        'inscrito' => false,
                        'presente' => (bool) $asistNoInscrito->presente,
                        'observacion' => $asistNoInscrito->observacion,
                    ]);
                }
            }
        }

        // Ordenar alfabéticamente por apellidos y nombres (normalizando acentos)
        $estudiantesData = $estudiantesData->sortBy(function ($item) {
            return \Illuminate\Support\Str::ascii(mb_strtolower($item['nombre'] ?? ''));
        })->values();

        // Resolver espacio: 1. Reserva activa, 2. Sesión guardada, 3. Planificación del día consultado, 4. Planificación habitual
        $diaSemana = $this->obtenerDiaSemanaEspanol($fechaConsulta);
        $espacio = $reservaActiva?->espacio ?? $sesion?->espacio;

        if (!$espacio) {
            $planificaciones = $asignatura ? $asignatura->planificaciones : ($colaborador ? $colaborador->planificaciones : collect());

            // Buscar planificación del día consultado
            $planDia = $planificaciones->first(function ($p) use ($diaSemana) {
                return $p->modulo && strcasecmp(\App\Helpers\ModulosHelper::normalizarDia($p->modulo->dia), $diaSemana) === 0 && $p->espacio !== null;
            });

            // Si no hay ese día, buscar planificación habitual de la asignatura
            $planHabitual = $planificaciones->first(function ($p) {
                return $p->espacio !== null;
            });

            $espacio = $planDia?->espacio ?? $planHabitual?->espacio;
        }

        $espacioActualNombre = $this->obtenerEspacioFormateado($espacio) ?? 'Sin espacio registrado';

        return view('docente.asistencia.tomar', compact(
            'id',
            'esColaboracion',
            'asignatura',
            'colaborador',
            'tituloClase',
            'codigoClase',
            'seccionClase',
            'rolDocente',
            'reservaActiva',
            'bloqueadoPorIngreso',
            'mensajeBloqueo',
            'sesion',
            'estudiantesData',
            'espacioActualNombre',
            'esSuperadmin',
            'fechaConsulta',
            'esHoy',
            'esFechaPasada'
        ));
    }

    /**
     * Guarda o actualiza la asistencia de la clase.
     */
    public function store(Request $request, string $id)
    {
        $user = Auth::user();
        $docenteRun = (string) ($request->attributes->get('docente_activo_run') ?? $user->run);
        $esSuperadmin = (bool) $request->attributes->get('es_superadmin_simulando', false);
        $hoy = Carbon::today();
        $fechaStr = $request->input('fecha');
        $fechaGuardar = $fechaStr ? Carbon::parse($fechaStr) : Carbon::today();

        $esColaboracion = str_starts_with($id, 'colab_');
        $asignatura = null;
        $colaborador = null;
        $rolDocente = 'titular';

        if ($esColaboracion) {
            $colabId = substr($id, 6);
            $colaborador = ProfesorColaborador::findOrFail($colabId);

            if (!$this->policy->pasarAsistenciaColaboracion($user, $colaborador)) {
                abort(403, 'No tienes autorización para gestionar la asistencia de esta colaboración.');
            }

            $rolDocente = 'colaborador';
            $asignatura = $colaborador->asignatura;
        } else {
            $asignatura = Asignatura::findOrFail($id);

            if (!$this->policy->pasarAsistenciaAsignatura($user, $asignatura)) {
                abort(403, 'No tienes autorización para gestionar la asistencia de esta asignatura.');
            }

            if ($asignatura->run_profesor_reemplazo && (string)$asignatura->run_profesor_reemplazo === $docenteRun) {
                $rolDocente = 'reemplazo';
            } elseif ((string)$asignatura->run_profesor === $docenteRun) {
                $rolDocente = 'titular';
            } else {
                $rolDocente = 'colaborador';
            }
        }

        // Buscar reserva activa si existe (opcional)
        $reservaActiva = Reserva::where('fecha_reserva', $fechaGuardar->format('Y-m-d'))
            ->where('estado', 'activa')
            ->whereNull('hora_salida')
            ->where('run_profesor', $docenteRun)
            ->first();

        // Si es clase temporal sin asignatura, la actividad es requerida
        if ($esColaboracion && !$asignatura && empty($request->input('actividad'))) {
            return back()->withInput()->with('error', 'Para clases temporales es obligatorio indicar qué actividad se realizó.');
        }

        // Si no hay reserva activa, intentar asignar el espacio planificado de la asignatura
        $idEspacioGuardar = $reservaActiva?->id_espacio;
        if (!$idEspacioGuardar) {
            $planificaciones = $asignatura ? $asignatura->planificaciones : ($colaborador ? $colaborador->planificaciones : collect());
            $diaSemana = $this->obtenerDiaSemanaEspanol($fechaGuardar);
            $planDia = $planificaciones->first(function ($p) use ($diaSemana) {
                return $p->modulo && strcasecmp(\App\Helpers\ModulosHelper::normalizarDia($p->modulo->dia), $diaSemana) === 0 && $p->espacio !== null;
            });
            $planHabitual = $planificaciones->first(fn($p) => $p->espacio !== null);
            $idEspacioGuardar = $planDia?->id_espacio ?? $planHabitual?->id_espacio;
        }

        DB::connection('tenant')->beginTransaction();

        try {
            // 1. Crear o actualizar SesionAsistencia
            $sesionData = [
                'id_reserva' => $reservaActiva?->id_reserva,
                'id_espacio' => $idEspacioGuardar,
                'id_asignatura' => $asignatura?->id_asignatura,
                'id_profesor_colaborador' => $colaborador?->id,
                'fecha' => $fechaGuardar->format('Y-m-d'),
                'run_profesor' => $docenteRun,
                'rol_docente' => $rolDocente,
                'actividad' => $request->input('actividad'),
                'registrado_por_run' => (string) $user->run,
                'es_prueba' => $esSuperadmin,
            ];

            $sesion = SesionAsistencia::updateOrCreate(
                [
                    'fecha' => $fechaGuardar->format('Y-m-d'),
                    'id_asignatura' => $asignatura?->id_asignatura,
                    'id_profesor_colaborador' => $colaborador?->id,
                ],
                $sesionData
            );

            // 2. Procesar marcas de asistencia de estudiantes existentes
            $asistenciasInput = $request->input('asistencia', []); // array [estudiante_id => 1 / 0]

            foreach ($asistenciasInput as $estudianteId => $valor) {
                $presente = (bool) $valor;
                $estudiante = Estudiante::find($estudianteId);
                if (!$estudiante) {
                    continue;
                }

                $estaInscrito = $asignatura ? $asignatura->estudiantes()->where('estudiantes.id', $estudianteId)->exists() : false;

                AsistenciaEstudiante::updateOrCreate(
                    [
                        'sesion_id' => $sesion->id,
                        'estudiante_id' => $estudianteId,
                    ],
                    [
                        'presente' => $presente,
                        'inscrito' => $estaInscrito,
                        'metodo' => 'checkbox',
                    ]
                );
            }

            // 3. Procesar nuevos estudiantes agregados manualmente (no listados)
            $nuevosRuns = $request->input('nuevo_estudiante_run', []);
            $nuevosNombres = $request->input('nuevo_estudiante_nombre', []);

            if (is_array($nuevosRuns)) {
                foreach ($nuevosRuns as $index => $runNuevo) {
                    $runNuevoLimpio = Estudiante::normalizarRun($runNuevo);
                    $nombreNuevo = trim($nuevosNombres[$index] ?? '');

                    if (!empty($runNuevoLimpio) && !empty($nombreNuevo)) {
                        $estudianteNuevo = Estudiante::firstOrCreate(
                            ['run' => $runNuevoLimpio],
                            [
                                'nombre' => $nombreNuevo,
                                'origen' => 'manual',
                            ]
                        );

                        AsistenciaEstudiante::updateOrCreate(
                            [
                                'sesion_id' => $sesion->id,
                                'estudiante_id' => $estudianteNuevo->id,
                            ],
                            [
                                'presente' => true,
                                'inscrito' => false,
                                'metodo' => 'checkbox',
                                'observacion' => 'Agregado manualmente durante la clase',
                            ]
                        );
                    }
                }
            }

            DB::connection('tenant')->commit();

            $totalPresentes = $sesion->totalPresentes();
            $totalTotal = $sesion->totalRegistrados();

            $fechaTexto = $fechaGuardar->isToday() ? 'de hoy' : 'del ' . $fechaGuardar->format('d/m/Y');
            $mensajeExito = "Asistencia {$fechaTexto} guardada correctamente. Presentes: {$totalPresentes} de {$totalTotal} estudiantes.";
            if ($esSuperadmin) {
                $mensajeExito .= " (Registrado en MODO PRUEBA por el superadministrador).";
            }

            return redirect()->route('docente.asistencia.show', [
                'id' => $id,
                'fecha' => $fechaGuardar->format('Y-m-d'),
            ])->with('success', $mensajeExito);
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            Log::error('Error guardando asistencia docente', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withInput()->with('error', 'Ocurrió un error al guardar la asistencia: ' . $e->getMessage());
        }
    }

    /**
     * Muestra el historial completo de sesiones de asistencia registradas para una clase.
     */
    public function historial(Request $request, string $id)
    {
        $user = Auth::user();
        $docenteRun = (string) ($request->attributes->get('docente_activo_run') ?? $user->run);
        $esSuperadmin = (bool) $request->attributes->get('es_superadmin_simulando', false);

        $esColaboracion = str_starts_with($id, 'colab_');
        $asignatura = null;
        $colaborador = null;
        $tituloClase = '';
        $codigoClase = '';
        $seccionClase = '';
        $carreraNombre = '';

        if ($esColaboracion) {
            $colabId = substr($id, 6);
            $colaborador = ProfesorColaborador::with(['asignatura.carrera', 'asignatura.estudiantes'])->findOrFail($colabId);

            if (!$this->policy->pasarAsistenciaColaboracion($user, $colaborador)) {
                abort(403, 'No tienes autorización para consultar el historial de esta colaboración.');
            }

            $tituloClase = $colaborador->nombre_asignatura;
            $codigoClase = $colaborador->asignatura?->codigo_asignatura ?? 'TEMP-' . $colaborador->id;
            $seccionClase = $colaborador->asignatura?->seccion ?? 'N/A';
            $carreraNombre = $colaborador->asignatura?->carrera?->nombre ?? ($colaborador->descripcion ?? 'Clase Temporal');

            $sesiones = SesionAsistencia::where('id_profesor_colaborador', $colaborador->id)
                ->with(['espacio', 'asistencias.estudiante'])
                ->orderByDesc('fecha')
                ->get();

            $totalInscritos = $colaborador->asignatura ? $colaborador->asignatura->estudiantes->count() : $colaborador->cantidad_inscritos;
        } else {
            $asignatura = Asignatura::with(['carrera', 'estudiantes'])->findOrFail($id);

            if (!$this->policy->pasarAsistenciaAsignatura($user, $asignatura)) {
                abort(403, 'No tienes autorización para consultar el historial de esta asignatura.');
            }

            $tituloClase = $asignatura->nombre_asignatura;
            $codigoClase = $asignatura->codigo_asignatura;
            $seccionClase = $asignatura->seccion;
            $carreraNombre = $asignatura->carrera?->nombre ?? ($asignatura->id_carrera ? 'Carrera ' . $asignatura->id_carrera : 'Sin carrera');

            $sesiones = SesionAsistencia::where('id_asignatura', $asignatura->id_asignatura)
                ->with(['espacio', 'asistencias.estudiante'])
                ->orderByDesc('fecha')
                ->get();

            $totalInscritos = $asignatura->estudiantes->count();
        }

        // Estadísticas acumuladas
        $totalSesiones = $sesiones->count();
        $totalPresentesAcumulado = $sesiones->sum(fn($s) => $s->totalPresentes());
        $totalRegistrosAcumulado = $sesiones->sum(fn($s) => $s->totalRegistrados());
        $porcentajeGlobal = $totalRegistrosAcumulado > 0 
            ? round(($totalPresentesAcumulado / $totalRegistrosAcumulado) * 100, 1) 
            : 0;

        return view('docente.asistencia.historial', compact(
            'id',
            'esColaboracion',
            'asignatura',
            'colaborador',
            'tituloClase',
            'codigoClase',
            'seccionClase',
            'carreraNombre',
            'sesiones',
            'totalInscritos',
            'totalSesiones',
            'totalPresentesAcumulado',
            'totalRegistrosAcumulado',
            'porcentajeGlobal',
            'esSuperadmin'
        ));
    }

    /**
     * Formatea el nombre visual del espacio con su código (ej: "TH-10 (Sala de Clases)").
     */
    protected function obtenerEspacioFormateado(?Espacio $espacio): ?string
    {
        if (!$espacio) {
            return null;
        }

        $id = trim((string) ($espacio->id_espacio ?? ''));
        $nombre = trim((string) ($espacio->nombre_espacio ?? ''));

        if ($id && $nombre && strcasecmp($id, $nombre) !== 0) {
            return "{$id} ({$nombre})";
        }

        return $nombre ?: ($id ?: null);
    }

    /**
     * Obtiene el nombre del día en español normalizado.
     */
    protected function obtenerDiaSemanaEspanol(Carbon $fecha): string
    {
        $dias = [
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
            7 => 'Domingo',
        ];

        return $dias[$fecha->dayOfWeekIso] ?? 'Lunes';
    }

    /**
     * Endpoint API para el Agente C# en segundo plano.
     * Registra la asistencia del estudiante vía lector QR y le envía el correo de confirmación.
     */
    public function registrarPorQr(Request $request)
    {
        $apiKeyRecibida = $request->header('X-API-KEY');
        $clavesValidas = array_filter([
            'SIA_QR_AGENT_SECRET_2026',
            'AULASYNC_QR_AGENT_SECRET_2026',
            config('app.qr_agent_key'),
            env('QR_AGENT_KEY')
        ]);

        if (!in_array($apiKeyRecibida, $clavesValidas, true)) {
            return response()->json(['error' => 'No autorizado. API Key inválida.'], 401);
        }

        $tenantId = $request->input('tenant_id') ?: $request->session()->get('tenant_id');
        $tenant = $tenantId
            ? Tenant::where('is_active', true)->find($tenantId)
            : null;

        if (!$tenant) {
            return response()->json(['error' => 'No se pudo identificar una sede activa para registrar la asistencia.'], 422);
        }

        $tenant->makeCurrent();

        $rawInput = trim((string) $request->input('raw_qr', ''));

        // Extraer RUN del escaneo
        $runEnviado = preg_replace('/\D/', '', (string) $request->input('run_estudiante', ''));
        if (strlen($runEnviado) === 9) {
            $runEnviado = substr($runEnviado, 0, -1);
        }
        $runLimpio = preg_match('/^\d{7,8}$/', $runEnviado) ? $runEnviado : null;

        if ($runLimpio === null && preg_match('/(?:RUN|RUT)[^0-9]{0,8}(\d{1,2}\.?\d{3}\.?\d{3}|\d{7,8})/i', $rawInput, $matches)) {
            $runLimpio = preg_replace('/\D/', '', $matches[1]);
            $runLimpio = $matches[1];
        } elseif ($runLimpio === null && preg_match('/[?&](?:run|rut|alumno|estudiante|id)=([^\s&]+)/i', $rawInput, $matches)) {
            $parametro = preg_replace('/\D/', '', $matches[1]);
            if (strlen($parametro) === 9) {
                $parametro = substr($parametro, 0, -1);
            }
            $runLimpio = preg_match('/^\d{7,8}$/', $parametro) ? $parametro : null;
        } elseif ($runLimpio === null && preg_match('/(\d{1,2}(?:\.\d{3}){2}|\d{7,8})/i', $rawInput, $matches)) {
            $runLimpio = preg_replace('/\D/', '', $matches[1]);
        }

        if ($runLimpio === null && empty($rawInput)) {
            return response()->json(['error' => 'RUN de estudiante no proporcionado.'], 422);
        }
        if ($runLimpio === null || !preg_match('/^\d{7,8}$/', $runLimpio)) {
            return response()->json(['error' => 'No se pudo extraer un RUN válido del código QR.'], 422);
        }

        $hoy = Carbon::today();

        // 1. Cargar la asignatura activa o colaboración temporal
        $idAsignatura = $request->input('id_asignatura');
        if (!$idAsignatura) {
            return response()->json(['error' => 'No se recibió el ID de la asignatura activa.'], 422);
        }

        $esColaboracion = str_starts_with($idAsignatura, 'colab_');
        $sesion = null;
        $asignatura = null;
        $colaborador = null;
        $docente = null;
        $nombreClase = 'Clase';
        $sala = 'Sala no asignada';
        $rolDocente = 'titular';
        $planificaciones = collect();

        if ($esColaboracion) {
            $colabId = substr($idAsignatura, 6);
            $colaborador = ProfesorColaborador::with(['asignatura.estudiantes', 'asignatura.carrera', 'profesor', 'planificaciones.espacio', 'planificaciones.modulo'])->find($colabId);
            if (!$colaborador) {
                return response()->json(['error' => 'La clase temporal o colaboración no existe en la sede activa.'], 404);
            }
            $asignatura = $colaborador->asignatura;
            $nombreClase = $colaborador->nombre_asignatura;
            $docente = $colaborador->profesor ?? $asignatura?->profesor;
            $planificaciones = $colaborador->planificaciones->isNotEmpty() ? $colaborador->planificaciones : ($asignatura?->planificaciones ?? collect());
            $espacio = $planificaciones->first()?->espacio;
            $sala = $this->obtenerEspacioFormateado($espacio) ?? 'Sala no asignada';
            $rolDocente = 'colaborador';
        } else {
            $asignatura = Asignatura::with(['planificaciones.espacio', 'planificaciones.modulo', 'profesor', 'estudiantes'])->find($idAsignatura);
            if (!$asignatura) {
                return response()->json(['error' => 'La asignatura indicada no existe en la sede activa.'], 404);
            }
            $nombreClase = $asignatura->nombre_asignatura;
            $docente = $asignatura->profesor;
            $planificaciones = $asignatura->planificaciones ?? collect();
            $espacio = $planificaciones->first()?->espacio;
            $sala = $this->obtenerEspacioFormateado($espacio) ?? 'Sala no asignada';
        }

        // CONDICIÓN 1: Validar si la asignatura tiene clases programadas el día de hoy
        $diaHoyEspanol = $this->obtenerDiaSemanaEspanol($hoy);
        $tieneClaseHoy = $planificaciones->contains(function ($p) use ($diaHoyEspanol) {
            return $p->modulo && strcasecmp(\App\Helpers\ModulosHelper::normalizarDia($p->modulo->dia), $diaHoyEspanol) === 0;
        });

        // Si la planificación tiene módulos definidos y ninguno coincide con hoy, se rechaza
        if ($planificaciones->isNotEmpty() && !$tieneClaseHoy) {
            return response()->json([
                'error' => "La clase '{$nombreClase}' no tiene módulos programados para el día de hoy ({$diaHoyEspanol})."
            ], 422);
        }

        // CONDICIÓN 2: El estudiante DEBE estar inscrito en la asignatura
        $estudiantesDisponibles = $asignatura ? $asignatura->estudiantes : collect();
        $estudiante = null;

        if ($estudiantesDisponibles && $estudiantesDisponibles->isNotEmpty()) {
            $estudiante = $estudiantesDisponibles->first(function ($e) use ($runLimpio) {
                $eRunLimpio = preg_replace('/\D/', '', (string)$e->run);
                if (strlen($eRunLimpio) === 9) {
                    $eRunLimpio = substr($eRunLimpio, 0, -1);
                }
                return $eRunLimpio === $runLimpio;
            });
        }

        if (!$estudiante) {
            return response()->json([
                'error' => "El estudiante con RUN {$runLimpio} no está inscrito en la asignatura '{$nombreClase}'."
            ], 422);
        }

        // Buscar o crear la sesión de asistencia para el día de hoy
        $sesion = SesionAsistencia::firstOrCreate(
            [
                'fecha' => $hoy->toDateString(),
                'id_asignatura' => $asignatura?->id_asignatura,
                'id_profesor_colaborador' => $colaborador?->id,
            ],
            [
                'id_espacio' => $espacio?->id_espacio,
                'run_profesor' => $docente?->run ?? $asignatura?->run_profesor,
                'rol_docente' => $rolDocente,
                'registrado_por_run' => 'AGENTE_QR',
                'actividad' => $esColaboracion ? ($colaborador->descripcion ?? 'Registro automático vía Lector QR') : 'Registro automático vía Lector QR',
            ]
        );

        // CONDICIÓN 3: Si ya estaba registrado, confirmar éxito sin duplicar correo
        $asistenciaPrevia = AsistenciaEstudiante::where('sesion_id', $sesion->id)
            ->where('estudiante_id', $estudiante->id)
            ->first();

        if ($asistenciaPrevia && $asistenciaPrevia->presente) {
            $horaPrevia = $asistenciaPrevia->updated_at ? $asistenciaPrevia->updated_at->format('H:i') : '';
            $mensajeYaRegistrado = $horaPrevia
                ? "Asistencia ya confirmada anteriormente a las {$horaPrevia} hrs."
                : "La asistencia de {$estudiante->nombre} ya se encontraba registrada.";

            return response()->json([
                'exito' => true,
                'ya_registrado' => true,
                'mensaje' => $mensajeYaRegistrado,
                'estudiante' => $estudiante->nombre,
                'run' => $estudiante->run,
                'clase' => $nombreClase,
                'sala' => $sala,
                'correo_enviado' => false,
                'correo_destinatario' => $estudiante->email,
            ]);
        }

        // Registrar asistencia como presente
        AsistenciaEstudiante::updateOrCreate(
            [
                'sesion_id' => $sesion->id,
                'estudiante_id' => $estudiante->id,
            ],
            [
                'presente' => true,
                'inscrito' => true,
                'metodo' => 'qr',
                'observacion' => 'Registrado vía Lector QR en segundo plano a las ' . now()->format('H:i:s'),
            ]
        );

        // Enviar correo de confirmación al estudiante
        $correoEnviado = false;
        if (!empty($estudiante->email) && filter_var($estudiante->email, FILTER_VALIDATE_EMAIL)) {
            try {
                \Illuminate\Support\Facades\Mail::to($estudiante->email)->send(
                    new \App\Mail\AsistenciaRegistradaMail(
                        $estudiante,
                        $asignatura,
                        $sesion,
                        $docente,
                        $nombreClase,
                        $sala
                    )
                );
                $correoEnviado = true;
            } catch (\Throwable $mailEx) {
                Log::warning('[AsistenciaController] Error enviando correo de asistencia: ' . $mailEx->getMessage());
            }
        }

        return response()->json([
            'exito' => true,
            'mensaje' => 'Asistencia registrada exitosamente',
            'estudiante' => $estudiante->nombre,
            'run' => $estudiante->run,
            'clase' => $nombreClase,
            'sala' => $sala,
            'correo_enviado' => $correoEnviado,
            'correo_destinatario' => $estudiante->email,
        ]);
    }

    public function estadoEnVivo(Request $request, string $id)
    {
        $fechaConsultaStr = $request->query('fecha');
        $fechaConsulta = $fechaConsultaStr ? Carbon::parse($fechaConsultaStr) : Carbon::today();
        $esColaboracion = str_starts_with($id, 'colab_');

        $sesionQuery = SesionAsistencia::where('fecha', $fechaConsulta->format('Y-m-d'));
        if ($esColaboracion) {
            $colabId = substr($id, 6);
            $colaborador = ProfesorColaborador::find($colabId);
            if ($colaborador && !$colaborador->id_asignatura) {
                $sesionQuery->where('id_profesor_colaborador', $colaborador->id);
            } else {
                $sesionQuery->where('id_asignatura', $colaborador?->id_asignatura);
            }
        } else {
            $sesionQuery->where('id_asignatura', $id);
        }

        $sesion = $sesionQuery->with(['asistencias.estudiante'])->latest()->first();

        if (!$sesion) {
            return response()->json([
                'ok' => true,
                'hay_sesion' => false,
                'presentes' => [],
                'total_presentes' => 0,
            ]);
        }

        $presentes = [];
        foreach ($sesion->asistencias as $asist) {
            if ($asist->presente) {
                $presentes[] = [
                    'estudiante_id' => $asist->estudiante_id,
                    'run' => $asist->estudiante?->run,
                    'nombre' => $asist->estudiante?->nombre,
                    'inscrito' => (bool) $asist->inscrito,
                    'metodo' => $asist->metodo,
                    'hora' => $asist->updated_at ? $asist->updated_at->format('H:i:s') : null,
                ];
            }
        }

        return response()->json([
            'ok' => true,
            'hay_sesion' => true,
            'presentes' => $presentes,
            'total_presentes' => count($presentes),
        ]);
    }
}
