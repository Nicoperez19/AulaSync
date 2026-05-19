<?php

namespace App\Http\Controllers;

use App\Helpers\SemesterHelper;
use App\Mail\ConfirmacionDevolucion;
use App\Mail\ConfirmacionReserva;
use App\Models\Asistencia;
use App\Models\Bloque;
use App\Models\Espacio;
use App\Models\Mapa;
use App\Models\Modulo;
use App\Models\Piso;
use App\Models\Planificacion_Asignatura;
use App\Models\PlanificacionProfesorColaborador;
use App\Models\Profesor;
use App\Models\ClaseNoRealizada;
use App\Models\Reserva;
use App\Models\Sede;
use App\Models\Solicitante;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

use App\Traits\RunNormalizer;

class PlanoDigitalController extends Controller
{
    use \App\Traits\PlanoDigitalHelperTrait;

    use RunNormalizer;

    public function index()
    {
        $sedes = Sede::with([
            'universidad',
            'facultades.pisos.mapas' => function ($query) {
                // Solo cargar mapas con ruta válida
                $query
                    ->where('ruta_mapa', '!=', '0')
                    ->whereNotNull('ruta_mapa')
                    ->where('ruta_mapa', '!=', '');
            }
        ])->get();

        // Verificar si hay mapas disponibles con rutas válidas
        $mapasDisponibles = Mapa::withoutGlobalScopes()
            ->where('ruta_mapa', '!=', '0')
            ->whereNotNull('ruta_mapa')
            ->where('ruta_mapa', '!=', '')
            ->count();
        $tieneMapas = $mapasDisponibles > 0;

        return view('layouts.plano_digital.index', compact('sedes', 'tieneMapas', 'mapasDisponibles'));
    }

    public function show($id)
    {
        try {
            // Establecer contexto del tenant desde el request
            $this->establecerContextoTenant();

            $mapa = $this->obtenerMapa($id);

            // Validar que el mapa tenga una imagen válida
            if (!$mapa->ruta_mapa || $mapa->ruta_mapa === '0' || $mapa->ruta_mapa === '') {
                if (request()->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El mapa no tiene una imagen configurada. Por favor, configure el mapa desde el menú de Mapas.'
                    ], 404);
                }

                return redirect()->route('plano.index')->with('error', 'El mapa no tiene una imagen configurada. Por favor, configure el mapa desde el menú de Mapas.');
            }

            $estadoActual = $this->obtenerEstadoActual(Carbon::now());
            $bloques = $this->prepararBloques($mapa, $estadoActual);

            // Obtener todos los pisos de la misma facultad con sus mapas
            $pisos = Piso::with([
                'mapas' => function ($query) {
                    $query
                        ->where('ruta_mapa', '!=', '0')
                        ->whereNotNull('ruta_mapa')
                        ->where('ruta_mapa', '!=', '');
                }
            ])
                ->where('id_facultad', $mapa->piso->id_facultad)
                ->orderBy('numero_piso')
                ->get();

            // Formatear los pisos con sus mapas
            $pisosFormateados = $pisos->map(function ($piso) {
                $primerMapa = $piso->mapas->first();
                return [
                    'id' => $piso->id,
                    'numero' => $piso->numero_piso,
                    'nombre' => $piso->nombre_piso ? $piso->nombre_piso : "Piso {$piso->numero_piso}",
                    'id_mapa' => $primerMapa ? $primerMapa->id_mapa : null
                ];
            });

            return view('layouts.plano_digital.show', [
                'mapa' => $mapa,
                'bloques' => $bloques,
                'pisos' => $pisosFormateados,
                'horariosModulos' => $this->obtenerMapaHorariosModulos()
            ]);
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al cargar los pisos: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Error al cargar los pisos: ' . $e->getMessage());
        }
    }

    public function bloques($id)
    {
        try {
            $mapa = $this->obtenerMapa($id);
            $estadoActual = $this->obtenerEstadoActual(Carbon::now());
            $bloques = $this->prepararBloques($mapa, $estadoActual);

            return response()->json(['bloques' => $bloques]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener los bloques'], 500);
        }
    }









    public function getModuloActual(Request $request, $id)
    {
        try {
            $horaActual = $request->input('hora');
            $diaActual = $request->input('dia');

            $modulo = Modulo::where('dia', $diaActual)
                ->where('hora_inicio', '<=', $horaActual)
                ->where('hora_termino', '>=', $horaActual)
                ->first();

            if ($modulo) {
                // Formatear las horas para mostrar solo HH:mm
                $modulo->hora_inicio = substr($modulo->hora_inicio, 0, 5);
                $modulo->hora_termino = substr($modulo->hora_termino, 0, 5);
            }

            return response()->json([
                'modulo' => $modulo
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener el módulo actual'], 500);
        }
    }

    public function getPlanoData($id)
    {
        $mapa = Mapa::with(['piso.facultad.sede'])->findOrFail($id);
        $estadoActual = $this->obtenerEstadoActual(Carbon::now());
        $bloques = $this->prepararBloques($mapa, $estadoActual);

        return response()->json([
            'mapa' => [
                'id' => $mapa->id_mapa,
                'nombre' => $mapa->nombre_mapa,
                'ruta_mapa' => asset('storage/' . $mapa->ruta_mapa),
                'piso' => [
                    'numero' => $mapa->piso->numero_piso,
                    'facultad' => $mapa->piso->facultad->nombre_facultad,
                    'sede' => $mapa->piso->facultad->sede->nombre_sede
                ]
            ],
            'bloques' => $bloques
        ]);
    }

    public function estadosEspacios()
    {
        // Establecer contexto del tenant desde el request
        $this->establecerContextoTenant();

        $tenantId = Tenant::current()?->id ?? 'default';
        $cacheKey = "estados_espacios_{$tenantId}";

        // [OPTIMIZACIÓN] Cache de 20 segundos para evitar recalcular en cada polling
        $resultado = \Illuminate\Support\Facades\Cache::remember($cacheKey, 20, function () {
            $horaActual = Carbon::now();
            $diaActual = strtolower($horaActual->locale('es')->isoFormat('dddd'));
            $horaActualStr = $horaActual->format('H:i:s');
            $fechaActual = $horaActual->toDateString();

            $periodo = SemesterHelper::getCurrentPeriod();
            $estadoActual = $this->obtenerEstadoActual($horaActual);
            $moduloActual = $this->obtenerModuloActual($estadoActual);

            $espacios = Espacio::all();
            $horaLimite = $horaActual->copy()->addMinutes(15)->format('H:i:s');

            $planificacionesActivasYProximas = Planificacion_Asignatura::with(['modulo', 'espacio', 'asignatura', 'horario.profesor'])
                ->whereHas('horario', function ($query) use ($periodo) {
                    $query->where('periodo', $periodo);
                })
                ->whereHas('modulo', function ($query) use ($diaActual, $horaActualStr, $horaLimite) {
                    $query->where('dia', $diaActual)
                        ->where(function($q) use ($horaActualStr, $horaLimite) {
                            $q->where(function($q2) use ($horaActualStr) {
                                $q2->where('hora_inicio', '<=', $horaActualStr)
                                   ->where('hora_termino', '>', $horaActualStr);
                            })
                            ->orWhere(function($q2) use ($horaActualStr, $horaLimite) {
                                $q2->where('hora_inicio', '>', $horaActualStr)
                                   ->where('hora_inicio', '<=', $horaLimite);
                            });
                        });
                })
                ->get();

            $reservasActivas = Reserva::with(['asignatura', 'profesor', 'solicitante'])
                ->where('fecha_reserva', $fechaActual)
                ->where('estado', 'activa')
                ->get();

            $reservasProgramadas = Reserva::with(['asignatura', 'profesor', 'solicitante'])
                ->where('fecha_reserva', $fechaActual)
                ->where('estado', 'programada')
                ->get();

            $reservasProximas = Reserva::with(['asignatura', 'profesor', 'solicitante'])
                ->where('fecha_reserva', $fechaActual)
                ->where('estado', 'activa')
                ->where('hora', '>', $horaActualStr)
                ->where('hora', '<=', $horaLimite)
                ->get();

            $clasesNoRealizadasGlobal = ClaseNoRealizada::with('modulo')
                ->where('fecha_clase', $fechaActual)
                ->get()
                ->groupBy('id_espacio');

            return [
                'success' => true,
                'espacios' => $espacios->map(function ($espacio) use ($horaActual, $horaActualStr, $diaActual, $planificacionesActivasYProximas, $reservasActivas, $reservasProgramadas, $reservasProximas, $moduloActual, $horaLimite, $clasesNoRealizadasGlobal) {

                $estadoTabla = $espacio->estado;

                $tieneReservaActiva = $reservasActivas->where('id_espacio', $espacio->id_espacio)->isNotEmpty();
                $tieneReservaProxima = $reservasProximas->where('id_espacio', $espacio->id_espacio)->isNotEmpty();

                $clasesNoRealizadasHoy = $clasesNoRealizadasGlobal->get($espacio->id_espacio, collect());

                $tieneClaseSinAsistentes = false;
                foreach ($clasesNoRealizadasHoy as $clase) {
                    if ($clase->modulo && $clase->modulo->hora_termino > $horaActualStr) {
                        $tieneClaseSinAsistentes = true;
                        break;
                    }
                }

                $claseEnCurso = $planificacionesActivasYProximas
                    ->where('id_espacio', $espacio->id_espacio)
                    ->filter(function ($p) use ($horaActualStr) {
                        return $p->modulo->hora_inicio <= $horaActualStr && $p->modulo->hora_termino > $horaActualStr;
                    })->first();

                $claseProxima = $planificacionesActivasYProximas
                    ->where('id_espacio', $espacio->id_espacio)
                    ->filter(function ($p) use ($horaActualStr, $horaLimite) {
                        return $p->modulo->hora_inicio > $horaActualStr && $p->modulo->hora_inicio <= $horaLimite;
                    })->first();

                $tieneClaseEnCurso = $claseEnCurso !== null;
                $tieneClaseProxima = $claseProxima !== null;

                if ($estadoTabla === 'Mantención' || $estadoTabla === 'Mantenimiento') {
                    $estado = 'Mantención';
                } elseif ($tieneClaseSinAsistentes) {
                    $estado = 'Clase no registrada';
                } elseif ($tieneReservaActiva) {
                    $reservaActiva = $reservasActivas->where('id_espacio', $espacio->id_espacio)->first();
                    if ($reservaActiva && $reservaActiva->tipo_reserva === 'espontanea') {
                        $estado = 'Reserva Espontánea';
                    } else {
                        $estado = 'Clase registrada';
                    }
                } elseif ($estadoTabla === 'Ocupado') {
                    $estado = 'Ocupado';
                } else {
                    $resProg = $reservasProgramadas->where('id_espacio', $espacio->id_espacio)->first();
                    $progEnFranja = false;
                    if ($resProg && $moduloActual) {
                        $numModActual = (int) (explode('.', $moduloActual->id_modulo)[1] ?? 0);
                        $modIniProg = (int) ($resProg->modulo_inicio ?? 0);
                        $modFinProg = (int) ($resProg->modulo_fin ?? 0);
                        $progEnFranja = ($numModActual && $modIniProg && $modFinProg && $numModActual >= $modIniProg && $numModActual <= $modFinProg);
                    }

                    if ($progEnFranja) {
                        $estado = 'Programado';
                    } elseif ($tieneClaseEnCurso && $estadoTabla !== 'Ocupado') {
                        $estado = 'Clase Programada';
                    } elseif ($tieneClaseProxima || $tieneReservaProxima) {
                        $estado = 'Reservado';
                    } elseif ($estadoTabla === 'Disponible') {
                        $estado = 'Disponible';
                    } else {
                        $estado = $estadoTabla;
                    }
                }

                $informacionAdicional = null;
                if ($tieneClaseEnCurso && $claseEnCurso) {
                    $informacionAdicional = [
                        'asignatura' => $claseEnCurso->asignatura->nombre_asignatura ?? 'Sin asignatura',
                        'profesor' => $claseEnCurso->horario->profesor->name ?? 'No especificado',
                        'modulo' => explode('.', $claseEnCurso->modulo->id_modulo)[1] ?? 'No especificado',
                        'hora_inicio' => substr($claseEnCurso->modulo->hora_inicio, 0, 5),
                        'hora_termino' => substr($claseEnCurso->modulo->hora_termino, 0, 5)
                    ];
                } elseif ($tieneReservaActiva) {
                    $reservaActiva = $reservasActivas->where('id_espacio', $espacio->id_espacio)->first();
                    if ($reservaActiva) {
                        $asignaturaInfo = 'Reserva espontánea';
                        if ($reservaActiva->tipo_reserva !== 'espontanea' && $reservaActiva->asignatura) {
                            $asignaturaInfo = $reservaActiva->asignatura->nombre_asignatura;
                        }
                        $nombreProfesor = 'No especificado';
                        if ($reservaActiva->profesor) {
                            $nombreProfesor = $reservaActiva->profesor->name;
                        } elseif ($reservaActiva->solicitante) {
                            $nombreProfesor = $reservaActiva->solicitante->nombre;
                        }
                        $informacionAdicional = [
                            'asignatura' => $asignaturaInfo,
                            'profesor' => $nombreProfesor,
                            'modulo' => 'Reserva manual',
                            'hora_inicio' => substr($reservaActiva->hora, 0, 5),
                            'hora_termino' => 'Manual'
                        ];
                    }
                }

                return [
                    'id_espacio' => $espacio->id_espacio,
                    'estado' => $estado,
                    'informacion_clase_actual' => $informacionAdicional
                ];
            })];
        });

        return response()->json($resultado);
    }




    /**

     * Devolver un espacio ocupado
     */
    public function devolverEspacio(Request $request)
    {
        return $this->planoDigitalService->devolverEspacio($request);
    }

    /**
     * Registrar si hubo o no asistentes en una clase
     * (llamado cuando se devuelven llaves en el primer módulo)
     */
    public function registrarAsistenciaClase(Request $request)
    {
        return $this->planoDigitalService->registrarAsistenciaClase($request);
    }

    /**
     * Forzar el cierre de una reserva anterior e iniciar la del nuevo docente
     */
    public function forzarCierreYTomarEspacio(Request $request)
    {
        return $this->planoDigitalService->forzarCierreYTomarEspacio($request);
    }

    /**
     * Verificar estado del espacio y reservas del usuario
     */
    public function verificarEstadoEspacioYReserva(Request $request)
    {
        return $this->planoDigitalService->verificarEstadoEspacioYReserva($request);
    }

    /**
     * Verificar usuario (profesor, solicitante o usuario no registrado)
     */
    public function procesarPrimeraLectura($run)
    {
        try {
            $run = $this->normalizeRun($run);
            $this->establecerContextoTenant();

            $userService = new \App\Services\UserService();
            $datosUsuario = $userService->buscarPorRun($run);

            if ($datosUsuario) {
                $response = [
                    'verificado' => true,
                    'tipo_usuario' => $datosUsuario['tipo_usuario'],
                    'usuario' => $datosUsuario['usuario'],
                    'tiene_clases' => false,
                    'mensaje' => ucfirst(str_replace('_registrado', '', $datosUsuario['tipo_usuario'])) . ' verificado correctamente'
                ];

                if ($datosUsuario['tipo_usuario'] === 'profesor') {
                    $horaActual = now()->format('H:i:s');
                    $diaActual = now()->dayOfWeek;
                    $clasesResponse = $this->verificarClasesProgramadas($run, $horaActual, $diaActual);
                    $clasesInfo = $clasesResponse instanceof \Illuminate\Http\JsonResponse 
                        ? $clasesResponse->getData(true) 
                        : (is_array($clasesResponse) ? $clasesResponse : []);

                    $response['tiene_clases'] = $clasesInfo['tiene_clases'] ?? false;
                    $response['clases_detalle'] = $clasesInfo;
                }

                return response()->json($response);
            }

            // Usuario no encontrado
            return response()->json([
                'verificado' => false,
                'tipo_usuario' => 'solicitante_nuevo',
                'run_escaneado' => $run,
                'mensaje' => 'Usuario no encontrado. Se requiere registro.',
                'requiere_registro' => true
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en procesarPrimeraLectura: ' . $e->getMessage());
            return response()->json([
                'verificado' => false,
                'mensaje' => 'Error al procesar la lectura: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar usuario (profesor, solicitante o usuario no registrado)
     */
    public function verificarUsuario($run)
    {
        try {
            $run = $this->normalizeRun($run);
            // Establecer contexto del tenant desde el request

            $this->establecerContextoTenant();

            $userService = new \App\Services\UserService();
            $datosUsuario = $userService->buscarPorRun($run);

            if ($datosUsuario) {
                return response()->json([
                    'verificado' => true,
                    'tipo_usuario' => $datosUsuario['tipo_usuario'],
                    'usuario' => $datosUsuario['usuario'],
                    'mensaje' => ucfirst(str_replace('_registrado', '', $datosUsuario['tipo_usuario'])) . ' verificado correctamente'
                ]);
            }

            // Usuario no encontrado - Mostrar modal de registro como solicitante
            return response()->json([
                'verificado' => false,
                'tipo_usuario' => 'solicitante_nuevo',
                'run_escaneado' => $run,
                'mensaje' => 'Usuario no encontrado. Se requiere registro como solicitante.',
                'requiere_registro' => true
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al verificar usuario: ' . $e->getMessage());
            return response()->json([
                'verificado' => false,
                'mensaje' => 'Error al verificar usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar espacio
     */
    public function verificarEspacio($idEspacio)
    {
        try {
            // Establecer contexto del tenant desde el request
            $this->establecerContextoTenant();

            $espacio = Espacio::with(['piso.facultad.sede'])
                ->where('id_espacio', $idEspacio)
                ->first();

            if (!$espacio) {
                return response()->json([
                    'verificado' => false,
                    'mensaje' => 'Espacio no encontrado'
                ], 404);
            }

            // Verificar si el espacio está disponible
            $disponible = $espacio->estado === 'Disponible';

            return response()->json([
                'verificado' => true,
                'disponible' => $disponible,
                'espacio' => [
                    'id' => $espacio->id_espacio,
                    'nombre' => $espacio->nombre_espacio,
                    'tipo' => $espacio->tipo_espacio,
                    'puestos' => $espacio->puestos_disponibles,
                    'estado' => $espacio->estado,
                    'piso' => $espacio->piso->numero_piso,
                    'facultad' => $espacio->piso->facultad->nombre_facultad,
                    'sede' => $espacio->piso->facultad->sede->nombre_sede
                ],
                'mensaje' => $disponible ? 'Espacio disponible' : 'Espacio no disponible'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al verificar espacio: ' . $e->getMessage());
            return response()->json([
                'verificado' => false,
                'mensaje' => 'Error al verificar espacio: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear reserva (método principal) - OBSOLETO, usar ProfesorController/SolicitanteController
     */
    public function crearReserva(Request $request)
    {
        return $this->planoDigitalService->crearReserva($request);
    }

    /**
     * Verificar clases programadas
     */
    public function verificarClasesProgramadas($run, $horaActual, $diaActual)
    {
        try {
            // Convertir el día numérico a nombre del día
            $diasSemana = [
                0 => 'domingo',
                1 => 'lunes',
                2 => 'martes',
                3 => 'miércoles',
                4 => 'jueves',
                5 => 'viernes',
                6 => 'sábado'
            ];

            $nombreDia = $diasSemana[$diaActual] ?? 'lunes';

            // Obtener período actual
            $periodo = SemesterHelper::getCurrentPeriod();


            // Buscar planificaciones del profesor para el día actual
            $planificaciones = Planificacion_Asignatura::with(['asignatura', 'modulo', 'espacio'])
                ->whereHas('asignatura', function ($query) use ($run) {
                    $runLimpio = str_replace(['.', '-', ' '], '', $run);
                    $query->whereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runLimpio]);
                })
                ->whereHas('modulo', function ($query) use ($nombreDia) {
                    $query->where('dia', $nombreDia);
                })
                ->whereHas('horario', function ($query) use ($periodo) {
                    $query->where('periodo', $periodo);
                })
                ->get();



            // Verificar si el profesor tiene clases programadas para el día actual
            $tieneClases = $planificaciones->count() > 0;

            // Verificar si tiene alguna clase actual (en curso)
            $claseActual = null;
            $siguienteClase = null;

            if ($tieneClases) {
                $horaActualTime = Carbon::createFromFormat('H:i:s', $horaActual);

                // Buscar clase actual (en curso)
                $claseActual = $planificaciones->filter(function ($plan) use ($horaActualTime) {
                    $inicio = Carbon::createFromFormat('H:i:s', $plan->modulo->hora_inicio);
                    $fin = Carbon::createFromFormat('H:i:s', $plan->modulo->hora_termino);
                    return $horaActualTime->between($inicio, $fin, true);
                })->first();

                // Buscar siguiente clase (futura)
                $siguienteClase = $planificaciones->filter(function ($plan) use ($horaActualTime) {
                    $inicio = Carbon::createFromFormat('H:i:s', $plan->modulo->hora_inicio);
                    return $inicio->gt($horaActualTime);
                })->sortBy('modulo.hora_inicio')->first();
            }

            // El profesor "tiene clases" si:
            // 1. Tiene planificaciones para el día actual
            $tieneClasesEnHorario = $tieneClases;

            // Agrupar por asignatura y detectar módulos consecutivos
            $clasesConModulosConsecutivos = [];
            $planificacionesAgrupadas = $planificaciones->groupBy('asignatura.nombre_asignatura');

            foreach ($planificacionesAgrupadas as $nombreAsignatura => $planificacionesAsignatura) {
                $modulosOrdenados = $planificacionesAsignatura->sortBy('modulo.hora_inicio');
                $secuenciasModulos = [];
                $secuenciaActual = [];

                foreach ($modulosOrdenados as $planificacion) {
                    if (empty($secuenciaActual)) {
                        $secuenciaActual[] = $planificacion;
                    } else {
                        $ultimoModulo = end($secuenciaActual)->modulo;
                        $moduloActual = $planificacion->modulo;

                        // Verificar si son consecutivos (el siguiente empieza cuando termina el anterior)
                        if ($ultimoModulo->hora_termino === $moduloActual->hora_inicio) {
                            $secuenciaActual[] = $planificacion;
                        } else {
                            // No son consecutivos, guardar secuencia anterior y empezar nueva
                            if (!empty($secuenciaActual)) {
                                $secuenciasModulos[] = $secuenciaActual;
                            }
                            $secuenciaActual = [$planificacion];
                        }
                    }
                }

                // Agregar la última secuencia
                if (!empty($secuenciaActual)) {
                    $secuenciasModulos[] = $secuenciaActual;
                }

                $clasesConModulosConsecutivos[$nombreAsignatura] = $secuenciasModulos;
            }



            return response()->json([
                'success' => true,
                'tiene_clases' => $tieneClasesEnHorario,
                'total_planificaciones' => $planificaciones->count(),
                'clase_actual' => $claseActual ? [
                    'asignatura' => $claseActual->asignatura->nombre_asignatura,
                    'espacio' => $claseActual->espacio->nombre_espacio,
                    'hora_inicio' => $claseActual->modulo->hora_inicio,
                    'hora_termino' => $claseActual->modulo->hora_termino
                ] : null,
                'siguiente_clase' => $siguienteClase ? [
                    'asignatura' => $siguienteClase->asignatura->nombre_asignatura,
                    'espacio' => $siguienteClase->espacio->nombre_espacio,
                    'hora_inicio' => $siguienteClase->modulo->hora_inicio,
                    'hora_termino' => $siguienteClase->modulo->hora_termino
                ] : null,
                'planificaciones' => $planificaciones->map(function ($plan) {
                    return [
                        'asignatura' => $plan->asignatura->nombre_asignatura,
                        'espacio' => $plan->espacio->nombre_espacio,
                        'modulo' => $plan->modulo->id_modulo,
                        'hora_inicio' => $plan->modulo->hora_inicio,
                        'hora_termino' => $plan->modulo->hora_termino
                    ];
                }),
                'secuencias_modulos' => $clasesConModulosConsecutivos
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al verificar clases programadas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al verificar clases: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Registra la asistencia de múltiples asistentes en una sala de estudio
     */
    public function registrarAsistenciaSalaEstudio(Request $request)
    {
        return $this->planoDigitalService->registrarAsistenciaSalaEstudio($request);
    }

    /**
     * Obtener la información actual del espacio (ocupante) para desocupación
     * Este endpoint es llamado ANTES de desocupar, para obtener el RUN desde el servidor
     * Esto asegura que funcione correctamente en múltiples máquinas
     */
    public function obtenerInfoEspacioParaDesocupar(Request $request)
    {
        try {
            // Establecer contexto del tenant desde el request
            $this->establecerContextoTenant();

            $request->validate([
                'id_espacio' => 'required|string'
            ]);

            $idEspacio = $request->input('id_espacio');

            // Buscar el espacio
            $espacio = Espacio::where('id_espacio', $idEspacio)->first();

            if (!$espacio) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Espacio no encontrado'
                ], 404);
            }

            // Obtener la reserva activa actual en este espacio
            $reservaActiva = Reserva::where('id_espacio', $idEspacio)
                ->where('estado', 'activa')
                ->where('fecha_reserva', Carbon::today())
                ->first();

            if (!$reservaActiva) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'No hay una reserva activa en este espacio',
                    'data' => null
                ]);
            }

            // Retornar el RUN del ocupante actual (convertir a string para validación)
            $runOcupante = (string) ($reservaActiva->run_profesor ?? $reservaActiva->run_solicitante);

            return response()->json([
                'success' => true,
                'data' => [
                    'id_espacio' => $idEspacio,
                    'run_usuario' => $runOcupante,
                    'nombre_ocupante' => $reservaActiva->nombre_usuario ?? 'No especificado',
                    'tipo_reserva' => $reservaActiva->tipo_reserva ?? 'normal'
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en obtenerInfoEspacioParaDesocupar: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al obtener información del espacio'
            ], 500);
        }
    }

    /**
     * Envía el correo de confirmación de reserva al profesor o solicitante.
     */


    /**
     * Envía el correo de confirmación de devolución al profesor o solicitante.
     */


    /**
     * Resuelve el correo electrónico del responsable de la reserva.
     */


    /**
     * Establece el contexto del tenant desde el request
     */


    /**
     * Obtener mapa de horarios de módulos (número → inicio/fin)
     * Horarios estándar de la institución.
     */


    /**
     * Limpiar el caché de estados de espacios para el tenant actual
     */


    /**
     * Normalizar el ID del espacio para soportar diferentes formatos de escaneo.
     * Ej: "LA-1" o "LA1" en la sede "CH" se normalizará a "CH-LA1"
     */

}
