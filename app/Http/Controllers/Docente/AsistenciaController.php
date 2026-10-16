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
                    'presente' => $asistenciaExistente ? (bool) $asistenciaExistente->presente : true, // Si hay registro se respeta, si no, por defecto presente
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
}
