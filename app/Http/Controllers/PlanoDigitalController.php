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
    use RunNormalizer;

    public function index()
    {
        $sedes = Sede::with(['universidad', 'facultades.pisos.mapas' => function ($query) {
            // Solo cargar mapas con ruta válida
            $query
                ->where('ruta_mapa', '!=', '0')
                ->whereNotNull('ruta_mapa')
                ->where('ruta_mapa', '!=', '');
        }])->get();

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
            $pisos = Piso::with(['mapas' => function ($query) {
                $query
                    ->where('ruta_mapa', '!=', '0')
                    ->whereNotNull('ruta_mapa')
                    ->where('ruta_mapa', '!=', '');
            }])
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
                'pisos' => $pisosFormateados
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

    private function obtenerMapa($id)
    {
        return Mapa::withoutGlobalScopes()
            ->with(['bloques.espacio', 'piso.facultad.sede.universidad'])
            ->where('id_mapa', $id)
            ->firstOrFail();
    }

    private function obtenerEstadoActual(Carbon $horaActual)
    {
        $diaActual = strtolower($horaActual->locale('es')->isoFormat('dddd'));
        $horaActualStr = $horaActual->format('H:i:s');
        $fechaActual = $horaActual->format('Y-m-d');

        return [
            'hora' => $horaActualStr,
            'dia' => $diaActual,
            'fecha' => $fechaActual,
            'codigo_dia' => $this->obtenerCodigoDia($diaActual)
        ];
    }

    private function obtenerCodigoDia(string $diaActual): ?string
    {
        return match ($diaActual) {
            'lunes' => 'LU',
            'martes' => 'MA',
            'miércoles' => 'MI',
            'jueves' => 'JU',
            'viernes' => 'VI',
            'sábado' => 'SA',
            default => null
        };
    }

    private function prepararBloques(Mapa $mapa, array $estadoActual): array
    {
        $moduloActual = $this->obtenerModuloActual($estadoActual);
        $planificacionesActivas = $this->obtenerPlanificacionesActivas($mapa, $moduloActual);
        $planificacionesProximas = $this->obtenerPlanificacionesProximas($mapa, $estadoActual);

        // USAR EL MISMO $estadoActual para consistencia
        $horaActualStr = $estadoActual['hora'];
        $fechaActual = $estadoActual['fecha'];

        // Obtener reservas próximas (próximos 10 minutos) - MISMO CÁLCULO DE HORA
        $horaActualCarbon = Carbon::createFromFormat('H:i:s', $horaActualStr);
        $horaLimite = $horaActualCarbon->copy()->addMinutes(10)->format('H:i:s');

        $reservasProximas = Reserva::with(['asignatura', 'profesor', 'solicitante'])
            ->where('fecha_reserva', $fechaActual)
            ->where('estado', 'activa')
            ->where('hora', '>', $horaActualStr)
            ->where('hora', '<=', $horaLimite)
            ->whereIn('id_espacio', $mapa->bloques->pluck('id_espacio'))
            ->get();

        return $mapa->bloques->map(function ($bloque) use ($planificacionesActivas, $planificacionesProximas, $reservasProximas, $mapa, $horaActualStr, $fechaActual, $moduloActual) {
            $idEspacio = $bloque->id_espacio;
            $espacio = $bloque->espacio;

            // Verificar si hay una reserva activa en este espacio
            // Obtener la reserva completa con relaciones para mostrar la información correcta
            $reservaActiva = Reserva::with(['profesor', 'asignatura.carrera', 'solicitante'])
                ->where('id_espacio', $idEspacio)
                ->where('estado', 'activa')
                ->where('fecha_reserva', $fechaActual)
                ->first();

            // Verificar si hay una reserva PROGRAMADA (creada con antelación, aún no activada)
            $reservaProgramada = null;
            if (!$reservaActiva) {
                $reservaProgramada = Reserva::with(['profesor', 'asignatura.carrera', 'solicitante'])
                    ->where('id_espacio', $idEspacio)
                    ->where('estado', 'programada')
                    ->where('fecha_reserva', $fechaActual)
                    ->first();

                // Solo considerar la reserva programada si el módulo actual está dentro de su rango
                if ($reservaProgramada && $moduloActual) {
                    $numModActual = (int) (explode('.', $moduloActual->id_modulo)[1] ?? 0);
                    $modIniProg = (int) ($reservaProgramada->modulo_inicio ?? 0);
                    $modFinProg = (int) ($reservaProgramada->modulo_fin ?? 0);
                    if ($numModActual && $modIniProg && $modFinProg) {
                        if ($numModActual < $modIniProg || $numModActual > $modFinProg) {
                            $reservaProgramada = null;  // Fuera de franja → ignorar
                        }
                    }
                }
            }

            // Verificar si hay una clase no realizada en la tabla clases_no_realizadas para hoy
            $clasesNoRealizadasHoy = \App\Models\ClaseNoRealizada::with('modulo')
                ->where('id_espacio', $idEspacio)
                ->where('fecha_clase', $fechaActual)
                ->get();

            // Verificar si hay alguna clase no realizada cuyo módulo AÚN NO HA TERMINADO
            $claseSinAsistentesActiva = false;
            foreach ($clasesNoRealizadasHoy as $clase) {
                if ($clase->modulo && $clase->modulo->hora_termino > $horaActualStr) {
                    // El módulo aún no ha terminado, mostrar como no realizada
                    $claseSinAsistentesActiva = true;
                    break;
                }
            }

            // Determinar estado basado en actividad real (no en BD)
            if ($espacio->estado === 'Mantención' || $espacio->estado === 'Mantenimiento') {
                // Espacio en mantención - no permitir reservas
                $estadoFinal = 'Mantención';
            } elseif ($reservaActiva) {
                // 1. Hay reserva activa = Ocupado
                $estadoFinal = 'Ocupado';

                // Corregir BD si está inconsistente
                if ($espacio->estado !== 'Ocupado') {
                    $espacio->estado = 'Ocupado';
                    $espacio->save();
                }
            } elseif ($reservaProgramada) {
                // 1.5. Hay reserva PROGRAMADA y el módulo actual está en su rango
                $estadoFinal = 'Programado';
            } elseif ($claseSinAsistentesActiva) {
                // 2. Hubo una clase sin asistentes y el módulo aún no termina
                $estadoFinal = 'ClaseSinAsistentes';

                // Corregir BD si está inconsistente
                if ($espacio->estado !== 'Disponible') {
                    $espacio->estado = 'Disponible';
                    $espacio->save();
                }
            } else {
                // 3. Verificar planificaciones (actuales y próximas)
                $planificacionActiva = $planificacionesActivas->firstWhere('id_espacio', $idEspacio);
                $planificacionProxima = $planificacionesProximas->firstWhere('id_espacio', $idEspacio);
                $reservaProxima = $reservasProximas->firstWhere('id_espacio', $idEspacio);

                // Verificar que la planificación próxima realmente esté en rango (validación EXTRA agresiva)
                if ($planificacionProxima && isset($planificacionProxima->modulo->hora_inicio)) {
                    // Calcular diferencia de minutos
                    $horaActualCarbon = Carbon::createFromFormat('H:i:s', $horaActualStr);
                    $horaProximaCarbon = Carbon::createFromFormat('H:i:s', $planificacionProxima->modulo->hora_inicio);
                    $diferencia = $horaProximaCarbon->diffInMinutes($horaActualCarbon, false);

                    // Si la diferencia es > 10 min, NO es próxima, es futura
                    if ($diferencia > 10 || $diferencia < 0) {
                        \Log::debug("Planificación filtrada para espacio {$idEspacio}: diferencia={$diferencia} min (próxima a {$planificacionProxima->modulo->hora_inicio}, actual {$horaActualStr})");
                        $planificacionProxima = null;
                    } else {
                        \Log::debug("Planificación MANTENIDA para espacio {$idEspacio}: diferencia={$diferencia} min (próxima a {$planificacionProxima->modulo->hora_inicio}, actual {$horaActualStr})");
                    }
                }

                if ($planificacionActiva) {
                    // Hay clase en curso = Reservado (naranja)
                    $estadoFinal = 'Reservado';
                } elseif ($planificacionProxima || $reservaProxima) {
                    // Solo hay clase próxima (próximos 10 min) = Próximo (azul)
                    $estadoFinal = 'Próximo';
                } else {
                    // 4. No hay actividad = Disponible
                    $estadoFinal = 'Disponible';

                    // Corregir BD si está marcado como Ocupado sin actividad
                    if ($espacio->estado === 'Ocupado') {
                        $espacio->estado = 'Disponible';
                        $espacio->save();
                        \Log::info("Espacio {$idEspacio} corregido: Ocupado → Disponible (sin actividad)");
                    }
                }
            }

            return [
                'id' => $idEspacio,
                'nombre' => $bloque->espacio->nombre_espacio,
                'x' => $bloque->posicion_x,
                'y' => $bloque->posicion_y,
                'estado' => $estadoFinal,
                'tipo' => $espacio->tipo_espacio,
                'capacidad' => $espacio->capacidad_maxima,
                'piso' => $mapa->piso->numero_piso,
                'detalles' => array_merge(
                    $this->prepararDetallesBloque(
                        $bloque->espacio,
                        $planificacionActiva ?? null,
                        $reservaActiva ?? $reservaProgramada,  // Pasar la reserva (activa o programada)
                        $planificacionProxima ?? null
                    ),
                    [
                        'estado' => $espacio->estado,
                        'facultad' => $mapa->piso->facultad->nombre_facultad,
                        'clase_sin_asistentes' => $claseSinAsistentesActiva,
                        'clases_no_realizadas' => $clasesNoRealizadasHoy,
                        'es_programada' => ($estadoFinal === 'Programado'),
                        'nombre_actividad' => $reservaProgramada->nombre_actividad ?? null,
                    ]
                )
            ];
        })->toArray();
    }

    private function obtenerModuloActual(array $estadoActual): ?Modulo
    {
        $modulo = Modulo::where('dia', $estadoActual['dia'])
            ->where('hora_inicio', '<=', $estadoActual['hora'])
            ->where('hora_termino', '>=', $estadoActual['hora'])
            ->first();

        // Si no hay módulo en curso (fuera de horario), asumir módulo 1 del día
        if (!$modulo) {
            $codigoDia = $this->obtenerCodigoDia($estadoActual['dia']);
            if ($codigoDia) {
                $modulo = Modulo::where('id_modulo', $codigoDia . '.1')->first();
            }
        }

        return $modulo;
    }

    private function obtenerPlanificacionesActivas(Mapa $mapa, ?Modulo $moduloActual)
    {
        if (!$moduloActual) {
            return collect([]);
        }

        $periodo = SemesterHelper::getCurrentPeriod();

        // Obtener la hora actual
        $horaActual = Carbon::now()->format('H:i:s');

        // Obtener planificaciones de asignaturas regulares
        $planificacionesRegulares = Planificacion_Asignatura::with(['horario.profesor', 'asignatura.profesor', 'modulo', 'espacio'])

            ->where('id_modulo', $moduloActual->id_modulo)
            ->whereHas('horario', function ($query) use ($periodo) {
                $query->where('periodo', $periodo);
            })
            ->whereHas('modulo', function ($query) use ($horaActual) {
                $query->where('hora_termino', '>=', $horaActual);  // Solo módulos que no han terminado
            })
            ->whereHas('espacio', function ($query) use ($mapa) {
                $query->whereIn('id_espacio', $mapa->bloques->pluck('id_espacio'));
            })
            ->get();

        // Obtener planificaciones de profesores colaboradores
        $planificacionesTemporales = PlanificacionProfesorColaborador::with(['profesorColaborador', 'modulo', 'espacio'])
            ->where('id_modulo', $moduloActual->id_modulo)
            ->whereHas('modulo', function ($query) use ($horaActual) {
                $query->where('hora_termino', '>=', $horaActual);  // Solo módulos que no han terminado
            })
            ->whereHas('espacio', function ($query) use ($mapa) {
                $query->whereIn('id_espacio', $mapa->bloques->pluck('id_espacio'));
            })
            ->get();

        return $planificacionesRegulares->merge($planificacionesTemporales);
    }

    private function obtenerPlanificacionesProximas(Mapa $mapa, array $estadoActual)
    {
        $horaActual = Carbon::parse($estadoActual['hora']);
        $diaActual = $estadoActual['dia'];

        $periodo = SemesterHelper::getCurrentPeriod();

        // Verificar si estamos en el rango especial de 05:00 a 05:10
        $horaActualStr = $horaActual->format('H:i:s');
        $esRangoEspecial = ($horaActualStr >= '05:00:00' && $horaActualStr <= '05:10:00');

        if ($esRangoEspecial) {
            // Para el rango 05:00-05:10, buscar clases que empiecen a las 05:10
            $horaInicioBusqueda = '05:00:00';
            $horaFinBusqueda = '05:10:00';
        } else {
            // Para otros horarios, usar la lógica normal (10 minutos después)
            $horaInicioBusqueda = $horaActual->format('H:i:s');
            $horaFinBusqueda = $horaActual->copy()->addMinutes(10)->format('H:i:s');
        }

        // Obtener planificaciones regulares próximas
        $planificacionesRegularesProximas = Planificacion_Asignatura::with(['horario.profesor', 'asignatura.profesor', 'modulo', 'espacio'])

            ->whereHas('horario', function ($query) use ($periodo) {
                $query->where('periodo', $periodo);
            })
            ->whereHas('modulo', function ($query) use ($horaInicioBusqueda, $horaFinBusqueda, $diaActual, $esRangoEspecial) {
                $query->where('dia', $diaActual);

                if ($esRangoEspecial) {
                    // Para el rango 05:00-05:10, buscar módulos que empiecen a las 05:10
                    $query->where('hora_inicio', '=', '05:10:00');
                } else {
                    // Para otros horarios, buscar módulos que empiecen en los próximos 10 minutos
                    // IMPORTANTE: >= para incluir clases que comienzan AHORA también
                    $query
                        ->where('hora_inicio', '>=', $horaInicioBusqueda)
                        ->where('hora_inicio', '<=', $horaFinBusqueda);
                }
            })
            ->whereHas('espacio', function ($query) use ($mapa) {
                $query->whereIn('id_espacio', $mapa->bloques->pluck('id_espacio'));
            })
            ->get();

        // Obtener planificaciones de profesores colaboradores próximas
        $planificacionesTemporalesProximas = PlanificacionProfesorColaborador::with(['profesorColaborador', 'modulo', 'espacio'])
            ->whereHas('modulo', function ($query) use ($horaInicioBusqueda, $horaFinBusqueda, $diaActual, $esRangoEspecial) {
                $query->where('dia', $diaActual);

                if ($esRangoEspecial) {
                    // Para el rango 05:00-05:10, buscar módulos que empiecen a las 05:10
                    $query->where('hora_inicio', '=', '05:10:00');
                } else {
                    // Para otros horarios, buscar módulos que empiecen en los próximos 10 minutos
                    // IMPORTANTE: >= para incluir clases que comienzan AHORA también
                    $query
                        ->where('hora_inicio', '>=', $horaInicioBusqueda)
                        ->where('hora_inicio', '<=', $horaFinBusqueda);
                }
            })
            ->whereHas('espacio', function ($query) use ($mapa) {
                $query->whereIn('id_espacio', $mapa->bloques->pluck('id_espacio'));
            })
            ->get();

        return $planificacionesRegularesProximas->merge($planificacionesTemporalesProximas);
    }

    private function prepararDetallesBloque($espacio, $planificacion, $reserva, $planificacionProxima): array
    {
        $detalles = [
            'tipo_espacio' => $espacio->tipo_espacio ?? 'No especificado',
            'puestos_disponibles' => $espacio->puestos_disponibles ?? 0,
            'planificacion' => null,
            'reserva' => null,
            'planificacion_proxima' => null
        ];

        // PRIORIDAD 1: Si hay reserva activa de profesor con asignatura Y es programada/cambio de sala
        // Esto asegura que se muestre la clase que realmente se está dando, no la programada
        // Las reservas espontáneas NO deben mostrar información de clase
        if ($reserva && $reserva->run_profesor && $reserva->asignatura && $reserva->tipo_reserva !== 'espontanea') {
            // El profesor está dando esta asignatura (puede ser diferente a la planificada)
            $detalles['planificacion'] = [
                'asignatura' => $reserva->asignatura->nombre_asignatura ?? 'Sin asignatura',
                'codigo_asignatura' => $reserva->asignatura->codigo_asignatura ?? '-',
                'profesor' => ucwords($reserva->profesor->name ?? 'No asignado'),
                'carrera' => $reserva->asignatura->carrera->nombre ?? '-',
                'es_reserva_activa' => true,  // Flag para identificar que viene de reserva
                'modulos' => []  // No mostrar módulos ya que es la clase actual
            ];
        } elseif ($planificacion && $planificacion->asignatura) {
            // PRIORIDAD 2: Si NO hay reserva, mostrar la planificación del espacio
            $detalles['planificacion'] = [
                'asignatura' => $planificacion->asignatura->nombre_asignatura ?? 'Sin asignatura',
                'codigo_asignatura' => $planificacion->asignatura->codigo_asignatura ?? '-',
                'profesor' => ucwords($planificacion->horario->profesor->name ?? 'No asignado'),
                'carrera' => $planificacion->asignatura->carrera->nombre ?? '-',
                'es_reserva_activa' => false,
                'modulos' => $planificacion
                    ->asignatura
                    ->planificaciones()
                    ->where('id_espacio', $espacio->id_espacio)
                    ->with('modulo')
                    ->get()
                    ->map(function ($plan) {
                        return [
                            'dia' => $plan->modulo->dia ?? 'No especificado',
                            'hora_inicio' => $plan->modulo->hora_inicio ?? '00:00:00',
                            'hora_termino' => $plan->modulo->hora_termino ?? '00:00:00'
                        ];
                    })
                    ->toArray()
            ];
        }

        if ($planificacionProxima && $planificacionProxima->asignatura) {
            $detalles['planificacion_proxima'] = [
                'asignatura' => $planificacionProxima->asignatura->nombre_asignatura ?? 'Sin asignatura',
                'profesor' => ucwords($planificacionProxima->horario->profesor->name ?? 'No asignado'),
                'hora_inicio' => substr($planificacionProxima->modulo->hora_inicio ?? '00:00', 0, 5),
                'hora_termino' => substr($planificacionProxima->modulo->hora_termino ?? '00:00', 0, 5),
                'modulo' => explode('.', $planificacionProxima->modulo->id_modulo ?? '')[1] ?? 'No especificado'
            ];
        }

        if ($reserva) {
            $detalles['reserva'] = [
                'fecha_reserva' => $reserva->fecha_reserva ?? 'No especificada',
                'hora' => $reserva->hora ?? '00:00:00',
                'hora_salida' => $reserva->hora_salida ?? null
            ];
            // Incluir el nombre del usuario que ocupa el espacio
            $detalles['usuario_ocupando'] = $reserva->user ? $reserva->user->name : null;

            // Incluir información adicional del usuario si está disponible
            if ($reserva->user) {
                $detalles['usuario_info'] = [
                    'nombre' => $reserva->user->name ?? 'No especificado',
                    'email' => $reserva->user->email ?? 'No especificado',
                    'run' => $reserva->user->run ?? 'No especificado'
                ];
            }
        } else {
            $detalles['usuario_ocupando'] = null;
            $detalles['usuario_info'] = null;
        }

        return $detalles;
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

        $horaActual = Carbon::now();
        $diaActual = strtolower($horaActual->locale('es')->isoFormat('dddd'));
        $horaActualStr = $horaActual->format('H:i:s');
        $fechaActual = $horaActual->toDateString();

        // Determinar el período actual usando el helper
        $periodo = SemesterHelper::getCurrentPeriod();

        // Obtener el módulo actual (importante para filtrar correctamente)
        $estadoActual = $this->obtenerEstadoActual($horaActual);
        $moduloActual = $this->obtenerModuloActual($estadoActual);

        // Obtener todos los espacios
        $espacios = Espacio::all();

        // Obtener SOLO las planificaciones del módulo actual (no todas del período)
        if ($moduloActual) {
            $planificacionesActivas = Planificacion_Asignatura::with(['modulo', 'espacio', 'asignatura', 'horario.profesor'])
                ->where('id_modulo', $moduloActual->id_modulo)
                ->whereHas('horario', function ($query) use ($periodo) {
                    $query->where('periodo', $periodo);
                })
                ->get();
        } else {
            $planificacionesActivas = collect([]);
        }

        // Obtener reservas activas para hoy
        $reservasActivas = Reserva::with(['asignatura', 'profesor', 'solicitante'])
            ->where('fecha_reserva', $fechaActual)
            ->where('estado', 'activa')
            ->get();

        // Obtener reservas programadas para hoy (creadas con antelación)
        $reservasProgramadas = Reserva::with(['asignatura', 'profesor', 'solicitante'])
            ->where('fecha_reserva', $fechaActual)
            ->where('estado', 'programada')
            ->get();

        // Obtener reservas próximas (próximos 10 minutos)
        $horaLimite = $horaActual->copy()->addMinutes(10)->format('H:i:s');
        $reservasProximas = Reserva::with(['asignatura', 'profesor', 'solicitante'])
            ->where('fecha_reserva', $fechaActual)
            ->where('estado', 'activa')
            ->where('hora', '>', $horaActualStr)
            ->where('hora', '<=', $horaLimite)
            ->get();

        return response()->json([
            'success' => true,
            'espacios' => $espacios->map(function ($espacio) use ($horaActual, $horaActualStr, $diaActual, $planificacionesActivas, $reservasActivas, $reservasProgramadas, $reservasProximas, $moduloActual) {
                $estadoTabla = $espacio->estado;  // Estado actual en la tabla espacios

                // Verificar si el espacio está ocupado por una reserva activa
                $tieneReservaActiva = $reservasActivas->where('id_espacio', $espacio->id_espacio)->isNotEmpty();

                // Verificar si el espacio tiene una reserva próxima
                $tieneReservaProxima = $reservasProximas->where('id_espacio', $espacio->id_espacio)->isNotEmpty();

                // Verificar si hay una clase no realizada en la tabla clases_no_realizadas para hoy
                $clasesNoRealizadasHoy = \App\Models\ClaseNoRealizada::with('modulo')
                    ->where('id_espacio', $espacio->id_espacio)
                    ->where('fecha_clase', $horaActual->toDateString())
                    ->get();

                // Verificar si hay alguna clase no realizada cuyo módulo AÚN NO HA TERMINADO
                $tieneClaseSinAsistentes = false;
                foreach ($clasesNoRealizadasHoy as $clase) {
                    if ($clase->modulo && $clase->modulo->hora_termino > $horaActualStr) {
                        // El módulo aún no ha terminado, mostrar como no realizada
                        $tieneClaseSinAsistentes = true;
                        break;
                    }
                }

                // Verificar si el espacio tiene una clase programada que debería estar en curso
                $claseEnCurso = $planificacionesActivas
                    ->where('id_espacio', $espacio->id_espacio)
                    ->filter(function ($planificacion) use ($horaActualStr) {
                        return $planificacion->modulo->hora_inicio <= $horaActualStr &&
                            $planificacion->modulo->hora_termino > $horaActualStr;
                    })
                    ->first();

                $tieneClaseEnCurso = $claseEnCurso !== null;

                // Verificar si el espacio tiene una clase próxima (entre módulos)
                $tieneClaseProxima = false;
                $planificacionesDelEspacio = $planificacionesActivas->where('id_espacio', $espacio->id_espacio);

                foreach ($planificacionesDelEspacio as $planificacion) {
                    $horaInicioModulo = $planificacion->modulo->hora_inicio;
                    $horaActualCarbon = Carbon::createFromFormat('H:i:s', $horaActualStr);
                    $horaInicioCarbon = Carbon::createFromFormat('H:i:s', $horaInicioModulo);

                    $diferencia = $horaInicioCarbon->diffInMinutes($horaActualCarbon);

                    // Si la clase comienza dentro de los próximos 10 minutos Y no está actualmente en curso
                    if ($horaInicioCarbon->gt($horaActualCarbon) &&
                            $diferencia <= 10 &&
                            !$tieneClaseEnCurso) {
                        $tieneClaseProxima = true;
                        \Log::debug("Clase próxima encontrada para {$espacio->id_espacio}: {$horaInicioModulo} (diferencia: {$diferencia} min)");
                        break;
                    } else {
                        \Log::debug("Clase descartada para {$espacio->id_espacio}: hora={$horaInicioModulo}, diferencia={$diferencia}, gt={$horaInicioCarbon->gt($horaActualCarbon)}, tieneEnCurso={$tieneClaseEnCurso}");
                    }
                }

                // Determinar el estado final según la lógica correcta
                if ($estadoTabla === 'Mantención' || $estadoTabla === 'Mantenimiento') {
                    // Si el espacio está en mantención, no permitir reservas
                    $estado = 'Mantención';
                } elseif ($tieneClaseSinAsistentes) {
                    // Si hay una clase sin asistentes hoy, mostrar estado especial
                    $estado = 'ClaseSinAsistentes';
                } elseif ($estadoTabla === 'Ocupado') {
                    // Si el estado en la tabla es "Ocupado", mostrar rojo y mantenerlo hasta devolución
                    $estado = 'Ocupado';
                } elseif ($tieneReservaActiva) {
                    $estado = 'Reservado';
                } elseif ($reservasProgramadas->where('id_espacio', $espacio->id_espacio)->isNotEmpty()) {
                    // Hay una reserva programada — verificar que el módulo actual esté en el rango
                    $resProg = $reservasProgramadas->where('id_espacio', $espacio->id_espacio)->first();
                    $progEnFranja = true;
                    if ($moduloActual) {
                        $numModActual = (int) (explode('.', $moduloActual->id_modulo)[1] ?? 0);
                        $modIniProg = (int) ($resProg->modulo_inicio ?? 0);
                        $modFinProg = (int) ($resProg->modulo_fin ?? 0);
                        if ($numModActual && $modIniProg && $modFinProg) {
                            $progEnFranja = ($numModActual >= $modIniProg && $numModActual <= $modFinProg);
                        }
                    }
                    if ($progEnFranja) {
                        $estado = 'Programado';
                    } else {
                        $estado = 'Disponible';
                    }
                } elseif ($tieneClaseEnCurso && $estadoTabla !== 'Ocupado') {
                    // Clase en curso en el módulo actual - mostrar naranja
                    $estado = 'Reservado';  // Naranja
                } elseif ($tieneClaseProxima || $tieneReservaProxima) {  // ← AGREGADO $tieneReservaProxima
                    // Clase próxima o reserva próxima - mostrar Reservado
                    $estado = 'Reservado';  // Cambiado de 'Proximo' a 'Reservado' para consistencia
                } elseif ($estadoTabla === 'Disponible') {
                    $estado = 'Disponible';
                } else {
                    $estado = $estadoTabla;
                }

                // Preparar información adicional para el modal
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
                    // Obtener información de la reserva manual activa
                    $reservaActiva = $reservasActivas->where('id_espacio', $espacio->id_espacio)->first();
                    if ($reservaActiva) {
                        // Para reservas espontáneas, NO mostrar asignatura
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
            })
        ]);
    }

    /**
     * Devolver un espacio ocupado
     */
    public function devolverEspacio(Request $request)
    {
        try {
            // Establecer contexto del tenant desde el request
            $this->establecerContextoTenant();

            $request->validate([
                'id_espacio' => 'required|string',
                'run_usuario' => 'required|string',
                'tipo_desocupacion' => 'sometimes|string|in:normal,forzosa',
                'run_administrador' => 'required_if:tipo_desocupacion,forzosa|string'
            ]);

            $idEspacio = $request->input('id_espacio');
            $runUsuario = $request->input('run_usuario');
            $tipoDesocupacion = $request->input('tipo_desocupacion', 'normal');
            $runAdministrador = $request->input('run_administrador');

            // Log para debugging
            \Log::info('Devolución de espacio iniciada', [
                'id_espacio' => $idEspacio,
                'run_usuario' => $runUsuario,
                'tipo_desocupacion' => $tipoDesocupacion,
                'run_administrador' => $runAdministrador
            ]);

            // Buscar el espacio
            $espacio = Espacio::where('id_espacio', $idEspacio)->first();

            if (!$espacio) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Espacio no encontrado'
                ], 404);
            }

            // Verificar si el usuario tiene una reserva activa en este espacio
            $reservaActiva = null;

            // Si el run_usuario indica desocupación forzosa por falta de info (empieza con FORCE_)
            if (strpos($runUsuario, 'FORCE_') === 0) {
                // Buscar cualquier reserva activa en este espacio
                $reservaActiva = Reserva::where('id_espacio', $idEspacio)
                    ->where('estado', 'activa')
                    ->first();

                \Log::info("Desocupación forzosa sin RUN específico para espacio: {$idEspacio}");
            } else {
                // Búsqueda normal por RUN de usuario
                $reservaActiva = Reserva::where(function ($query) use ($runUsuario) {
                    $query
                        ->where('run_profesor', $runUsuario)
                        ->orWhere('run_solicitante', $runUsuario);
                })
                    ->where('id_espacio', $idEspacio)
                    ->where('estado', 'activa')
                    ->first();
            }

            if (!$reservaActiva) {
                \Log::warning("Intento de devolución sin reserva activa - Usuario: {$runUsuario}, Espacio: {$idEspacio}");

                // Verificar si el espacio ya está disponible (puede que ya se haya devuelto)
                if ($espacio->estado === 'Disponible') {
                    return response()->json([
                        'success' => true,
                        'mensaje' => 'El espacio ya está disponible',
                        'espacio' => [
                            'id' => $espacio->id_espacio,
                            'nombre' => $espacio->nombre_espacio,
                            'estado' => $espacio->estado
                        ]
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'mensaje' => 'No tienes una reserva activa en este espacio'
                ], 400);
            }

            // NUEVA LÓGICA: Verificar si es un profesor devolviendo durante el primer módulo de una clase
            $devolucionPrimerModulo = false;
            $infoClase = null;

            if ($reservaActiva->run_profesor) {
                // Obtener hora actual y día
                $horaActual = now()->format('H:i:s');
                $diaActual = strtolower(now()->locale('es')->isoFormat('dddd'));

                // Buscar planificaciones del profesor en este espacio para hoy
                $planificaciones = Planificacion_Asignatura::with(['asignatura', 'modulo'])
                    ->where('id_espacio', $idEspacio)
                    ->whereHas('asignatura', function ($query) use ($reservaActiva) {
                        $query->where('run_profesor', $reservaActiva->run_profesor);
                    })
                    ->whereHas('modulo', function ($query) use ($diaActual) {
                        $query->where('dia', $diaActual);
                    })
                    ->orderBy('id_modulo')
                    ->get();

                if ($planificaciones->count() > 0) {
                    // Agrupar módulos consecutivos
                    $secuenciasModulos = [];
                    $secuenciaActual = [];

                    foreach ($planificaciones as $planificacion) {
                        if (empty($secuenciaActual)) {
                            $secuenciaActual[] = $planificacion;
                        } else {
                            $ultimoModulo = end($secuenciaActual)->modulo;
                            $moduloActual = $planificacion->modulo;

                            // Verificar si son consecutivos
                            if ($ultimoModulo->hora_termino === $moduloActual->hora_inicio) {
                                $secuenciaActual[] = $planificacion;
                            } else {
                                if (!empty($secuenciaActual)) {
                                    $secuenciasModulos[] = $secuenciaActual;
                                }
                                $secuenciaActual = [$planificacion];
                            }
                        }
                    }

                    if (!empty($secuenciaActual)) {
                        $secuenciasModulos[] = $secuenciaActual;
                    }

                    // Buscar si estamos en el primer módulo de una secuencia de múltiples módulos
                    foreach ($secuenciasModulos as $secuencia) {
                        if (count($secuencia) > 1) {  // Solo si tiene más de 1 módulo
                            $primerModulo = $secuencia[0]->modulo;

                            // Verificar si la hora actual está dentro del primer módulo
                            if ($horaActual >= $primerModulo->hora_inicio && $horaActual <= $primerModulo->hora_termino) {
                                $devolucionPrimerModulo = true;
                                $infoClase = [
                                    'asignatura' => $secuencia[0]->asignatura->nombre_asignatura,
                                    'codigo_asignatura' => $secuencia[0]->asignatura->codigo_asignatura,
                                    'total_modulos' => count($secuencia),
                                    'modulo_actual' => 1,
                                    'hora_inicio' => $primerModulo->hora_inicio,
                                    'hora_termino' => $secuencia[count($secuencia) - 1]->modulo->hora_termino
                                ];
                                break;
                            }
                        }
                    }
                }
            }

            // Actualizar la reserva activa del usuario: establecer hora_salida y cambiar estado a finalizada
            if ($reservaActiva) {
                $reservaActiva->hora_salida = now()->format('H:i:s');
                $reservaActiva->estado = 'finalizada';

                // Si es una desocupación forzosa, agregar información adicional
                if ($tipoDesocupacion === 'forzosa') {
                    $reservaActiva->observaciones = ($reservaActiva->observaciones ?? '')
                        . "; DESOCUPACIÓN FORZOSA por administrador RUN: {$runAdministrador} el " . now()->format('Y-m-d H:i:s');
                }

                $reservaActiva->observaciones = trim(($reservaActiva->observaciones ?? '') . "\nFinalizada por el usuario a las " . now()->format('H:i:s'));
                $reservaActiva->save();

                // Enviar correo de confirmación de devolución
                $this->enviarCorreoDevolucion($reservaActiva);
            }

            // Buscar si hay reservas finalizadas automáticamente que el profesor está devolviendo tarde
            $reservaAutoFinalizada = Reserva::where(function ($query) use ($runUsuario) {
                $query
                    ->where('run_profesor', $runUsuario)
                    ->orWhere('run_solicitante', $runUsuario);
            })
                ->where('id_espacio', $idEspacio)
                ->where('estado', 'finalizada')
                ->where('fecha_reserva', now()->toDateString())
                ->whereNotNull('observaciones')
                ->where('observaciones', 'LIKE', '%finalizó automáticamente por excederse en el tiempo%')
                ->orderBy('updated_at', 'desc')
                ->first();

            if ($reservaAutoFinalizada) {
                // El profesor está devolviendo la llave después de que la reserva fue auto-finalizada
                $observacionActual = $reservaAutoFinalizada->observaciones ?? '';
                $nuevaObservacion = "\nProfesor finalizó la clase más tarde y devolvió llave de acceso a las " . now()->format('H:i:s') . '.';
                $reservaAutoFinalizada->observaciones = $observacionActual . $nuevaObservacion;
                $reservaAutoFinalizada->save();

                \Log::info("Reserva auto-finalizada {$reservaAutoFinalizada->id_reserva} actualizada: profesor devolvió llave tarde");
            }

            // Cambiar el estado del espacio a disponible
            $espacio->estado = 'Disponible';
            $espacio->save();

            // Registrar la devolución en un log
            $mensajeLog = $tipoDesocupacion === 'forzosa'
                ? "Espacio {$idEspacio} FORZOSAMENTE devuelto por administrador {$runAdministrador} - Usuario ocupante: {$runUsuario} - Reserva ID: {$reservaActiva->id_reserva}"
                : "Espacio {$idEspacio} devuelto exitosamente por usuario {$runUsuario} - Reserva ID: {$reservaActiva->id_reserva}";

            \Log::info($mensajeLog);

            $mensajeRespuesta = $tipoDesocupacion === 'forzosa'
                ? 'Espacio desocupado forzosamente por el administrador'
                : 'Espacio devuelto exitosamente';

            $respuesta = [
                'success' => true,
                'mensaje' => $mensajeRespuesta,
                'tipo_desocupacion' => $tipoDesocupacion,
                'espacio' => [
                    'id' => $espacio->id_espacio,
                    'nombre' => $espacio->nombre_espacio,
                    'estado' => $espacio->estado
                ]
            ];

            // Si es devolución en primer módulo, agregar información adicional
            if ($devolucionPrimerModulo) {
                $respuesta['devolucion_primer_modulo'] = true;
                $respuesta['info_clase'] = $infoClase;
                $respuesta['id_reserva'] = $reservaActiva->id_reserva;

                \Log::info("Devolución detectada en primer módulo - Reserva: {$reservaActiva->id_reserva}, Asignatura: {$infoClase['asignatura']}");
            }

            return response()->json($respuesta);
        } catch (\Exception $e) {
            \Log::error('Error al devolver espacio: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'mensaje' => 'Error al procesar la devolución: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Registrar si hubo o no asistentes en una clase
     * (llamado cuando se devuelven llaves en el primer módulo)
     */
    public function registrarAsistenciaClase(Request $request)
    {
        try {
            // Establecer contexto del tenant desde el request
            $this->establecerContextoTenant();

            $request->validate([
                'id_reserva' => 'required|string',
                'hubo_asistentes' => 'required|boolean'
            ]);

            $idReserva = $request->input('id_reserva');
            $huboAsistentes = $request->input('hubo_asistentes');

            // Buscar la reserva
            $reserva = Reserva::find($idReserva);

            if (!$reserva) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Reserva no encontrada'
                ], 404);
            }

            // Agregar observación sobre asistencia
            $mensajeAsistencia = $huboAsistentes
                ? '✓ Clase con asistentes confirmado por profesor'
                : '✗ Clase SIN asistentes - Devolución en primer módulo';

            $observacionActual = $reserva->observaciones ?? '';
            $reserva->observaciones = trim($observacionActual . "\n" . $mensajeAsistencia);

            // Guardar en el campo dedicado
            $reserva->hubo_asistentes = $huboAsistentes;
            $reserva->save();

            // NUEVA LÓGICA: Registrar Clase No Realizada si no hubo asistentes
            if (!$huboAsistentes && $reserva->id_asignatura) {
                try {
                    $periodo = SemesterHelper::getCurrentPeriod();
                    
                    // Buscar el módulo actual basado en la reserva
                    $idModulo = null;
                    if ($reserva->modulo_inicio) {
                        // El formato suele ser "DIA.MODULO", ej: "lunes.1"
                        $dia = strtolower(now()->locale('es')->isoFormat('dddd'));
                        
                        // Normalizar nombres de días si es necesario (Carbon vs BD)
                        $diasMapa = [
                            'monday' => 'lunes', 'tuesday' => 'martes', 'wednesday' => 'miércoles',
                            'thursday' => 'jueves', 'friday' => 'viernes', 'saturday' => 'sábado', 'sunday' => 'domingo'
                        ];
                        $diaEng = strtolower(now()->englishDayOfWeek);
                        $diaNormalizado = $diasMapa[$diaEng] ?? $dia;

                        $idModulo = $diaNormalizado . '.' . $reserva->modulo_inicio;
                    } else {
                        // Fallback: buscar por hora actual
                        $horaActual = now()->format('H:i:s');
                        $dia = strtolower(now()->locale('es')->isoFormat('dddd'));
                        $modulo = Modulo::where('dia', $dia)
                            ->where('hora_inicio', '<=', $horaActual)
                            ->where('hora_termino', '>=', $horaActual)
                            ->first();
                        $idModulo = $modulo ? $modulo->id_modulo : null;
                    }

                    if ($idModulo) {
                        ClaseNoRealizada::updateOrCreate(
                            [
                                'id_asignatura' => $reserva->id_asignatura,
                                'id_espacio' => $reserva->id_espacio,
                                'id_modulo' => $idModulo,
                                'fecha_clase' => $reserva->fecha_reserva,
                            ],
                            [
                                'run_profesor' => $reserva->run_profesor,
                                'periodo' => $periodo,
                                'motivo' => 'Confirmado por profesor: Clase sin asistentes',
                                'observaciones' => 'El docente devolvió las llaves en el primer módulo e informó que no hubo asistentes.',
                                'estado' => 'no_realizada',
                                'hora_deteccion' => now(),
                            ]
                        );
                        \Log::info("Clase no realizada registrada por confirmación de profesor", [
                            'reserva' => $reserva->id_reserva,
                            'asignatura' => $reserva->id_asignatura,
                            'modulo' => $idModulo
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::error("Error al registrar clase no realizada: " . $e->getMessage());
                }
            }

            \Log::info("Asistencia registrada para reserva {$idReserva}: " . ($huboAsistentes ? 'CON asistentes' : 'SIN asistentes'));

            return response()->json([
                'success' => true,
                'mensaje' => 'Asistencia registrada correctamente',
                'hubo_asistentes' => $huboAsistentes
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al registrar asistencia de clase: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'mensaje' => 'Error al registrar asistencia: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Forzar el cierre de una reserva anterior e iniciar la del nuevo docente
     */
    public function forzarCierreYTomarEspacio(Request $request)
    {
        return DB::transaction(function () use ($request) {
            try {
                $this->establecerContextoTenant();

                $request->validate([
                    'run_usuario' => 'required|string',
                    'id_espacio' => 'required|string',
                    'id_reserva_anterior' => 'required|string'
                ]);

                $runNuevo = $this->normalizeRun($request->input('run_usuario'));
                $idEspacio = $request->input('id_espacio');
                $idReservaAnterior = $request->input('id_reserva_anterior');

                // 1. Obtener datos de los involucrados
                $docenteNuevo = Profesor::where('run_profesor', $runNuevo)->first();
                if (!$docenteNuevo) {
                    return response()->json(['success' => false, 'mensaje' => 'Docente nuevo no encontrado (' . $runNuevo . ')'], 404);
                }


                $reservaAnterior = Reserva::with(['profesor', 'solicitante'])->find($idReservaAnterior);
                if (!$reservaAnterior || $reservaAnterior->id_espacio !== $idEspacio) {
                    return response()->json(['success' => false, 'mensaje' => 'Reserva anterior no válida'], 400);
                }

                $nombreAnterior = $reservaAnterior->profesor ? $reservaAnterior->profesor->name : ($reservaAnterior->solicitante ? $reservaAnterior->solicitante->nombre : 'Usuario desconocido');
                $runAnterior = $reservaAnterior->run_profesor ?? $reservaAnterior->run_solicitante;

                // 2. Finalizar reserva anterior
                $reservaAnterior->hora_salida = now()->format('H:i:s');
                $reservaAnterior->estado = 'finalizada';
                $reservaAnterior->observaciones = ($reservaAnterior->observaciones ?? '')
                    . "; CIERRE FORZADO por el docente del siguiente módulo: {$docenteNuevo->name} ({$runNuevo}) el " . now()->format('Y-m-d H:i:s');
                $reservaAnterior->save();

                // 3. Crear nueva reserva para el docente actual
                // Primero verificar si tiene planificación o reserva programada
                $horaActual = now();
                $diaActual = strtolower($horaActual->locale('es')->isoFormat('dddd'));
                $horaActualStr = $horaActual->format('H:i:s');

                $planificacion = Planificacion_Asignatura::where('id_espacio', $idEspacio)
                    ->whereHas('asignatura', function ($q) use ($runNuevo) {
                        $q->where('run_profesor', $runNuevo);
                    })
                    ->whereHas('modulo', function ($q) use ($diaActual, $horaActualStr) {
                        $q
                            ->where('dia', $diaActual)
                            ->where('hora_inicio', '<=', $horaActualStr)
                            ->where('hora_termino', '>=', $horaActualStr);
                    })
                    ->first();

                $reservaProgramada = Reserva::where('id_espacio', $idEspacio)
                    ->where('run_profesor', $runNuevo)
                    ->where('estado', 'programada')
                    ->where('fecha_reserva', Carbon::today()->toDateString())
                    ->first();

                if ($reservaProgramada) {
                    // Activar la programada
                    $reservaProgramada->estado = 'activa';
                    $reservaProgramada->hora = $horaActualStr;
                    $reservaProgramada->observaciones = ($reservaProgramada->observaciones ?? '')
                        . "; Sesión iniciada forzosamente; el docente anterior ({$nombreAnterior} - {$runAnterior}) no liberó el espacio";
                    $reservaProgramada->save();
                    $nuevaReservaId = $reservaProgramada->id_reserva;
                } else {
                    // Crear nueva reserva (como registrarAsistenciaProfesor)
                    $nuevaReserva = new Reserva();
                    $nuevaReserva->run_profesor = $runNuevo;
                    $nuevaReserva->id_espacio = $idEspacio;
                    $nuevaReserva->fecha_reserva = Carbon::today()->toDateString();
                    $nuevaReserva->hora = $horaActualStr;
                    $nuevaReserva->estado = 'activa';
                    $nuevaReserva->tipo_reserva = 'clase';

                    if ($planificacion) {
                        $nuevaReserva->id_asignatura = $planificacion->id_asignatura;
                        // Extraer el número de módulo del id_modulo (ej: "LU.5" -> 5)
                        $numModulo = explode('.', $planificacion->id_modulo)[1] ?? null;
                        $nuevaReserva->modulo_inicio = $numModulo;
                        $nuevaReserva->modulo_fin = $numModulo;
                        
                        // Si el objeto modulo está cargado, usar su hora de término
                        if ($planificacion->modulo) {
                            $nuevaReserva->hora_salida = $planificacion->modulo->hora_termino;
                        } else {
                            // Fallback: intentar cargar el módulo o usar el mapeo estándar
                            $moduloObj = Modulo::find($planificacion->id_modulo);
                            if ($moduloObj) {
                                $nuevaReserva->hora_salida = $moduloObj->hora_termino;
                            } else {
                                // Mapeo estándar de horas de término por módulo
                                $horariosFin = [
                                    1 => '09:00:00', 2 => '10:00:00', 3 => '11:00:00', 4 => '12:00:00',
                                    5 => '13:00:00', 6 => '14:00:00', 7 => '15:00:00', 8 => '16:00:00',
                                    9 => '17:00:00', 10 => '18:00:00', 11 => '19:00:00', 12 => '20:00:00',
                                    13 => '21:00:00', 14 => '22:00:00', 15 => '23:00:00'
                                ];
                                if ($numModulo && isset($horariosFin[(int)$numModulo])) {
                                    $nuevaReserva->hora_salida = $horariosFin[(int)$numModulo];
                                }
                            }
                        }
                    }

                    $nuevaReserva->observaciones = "Sesión iniciada forzosamente; el docente anterior ({$nombreAnterior} - {$runAnterior}) no liberó el espacio";
                    $nuevaReserva->save();
                    $nuevaReservaId = $nuevaReserva->id_reserva;
                }

                // 4. Asegurar que el espacio esté como Ocupado
                $espacio = Espacio::where('id_espacio', $idEspacio)->first();
                if ($espacio) {
                    $espacio->estado = 'Ocupado';
                    $espacio->save();
                }

                \Log::info('CIERRE FORZADO EXITOSO', [
                    'espacio' => $idEspacio,
                    'forzado_por' => $runNuevo,
                    'afectado' => $runAnterior
                ]);

                return response()->json([
                    'success' => true,
                    'mensaje' => 'Cierre forzado realizado con éxito. Su clase ha sido iniciada.',
                    'id_reserva' => $nuevaReservaId
                ]);
            } catch (\Exception $e) {
                \Log::error('Error en forzarCierreYTomarEspacio: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Error al procesar el cierre forzado: ' . $e->getMessage()
                ], 500);
            }
        });
    }

    /**
     * Verificar estado del espacio y reservas del usuario
     */
    public function verificarEstadoEspacioYReserva(Request $request)
    {
        try {
            // Establecer contexto del tenant desde el request
            $this->establecerContextoTenant();

            // Registro de diagnóstico: confirmar que la función fue invocada y mostrar payload (temporal)
            \Log::info('verificarEstadoEspacioYReserva called', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'payload' => $request->all(),
                'connection' => \DB::connection('tenant')->getName(),
                'database' => \DB::connection('tenant')->getDatabaseName(),
                'tenant_current' => Tenant::current() ? Tenant::current()->name : 'null'
            ]);
            $request->validate([
                'run' => 'required|string',
                'id_espacio' => 'required|string'
            ]);

            $runUsuario = $this->normalizeRun($request->input('run'));
            $idEspacio = $request->input('id_espacio');


            // Verificar que el espacio existe (ignorar scopes globales para evitar problemas de filtrado por sede/tenant)
            $espacio = Espacio::withoutGlobalScopes()->where('id_espacio', $idEspacio)->first();
            if (!$espacio) {
                \Log::warning('Espacio no encontrado en verificarEstadoEspacioYReserva', [
                    'id_espacio' => $idEspacio,
                    'database' => \DB::connection('tenant')->getDatabaseName()
                ]);
                return response()->json([
                    'tipo' => 'error',
                    'mensaje' => 'Espacio no encontrado: ' . $idEspacio
                ], 404);
            }

            // Verificar si el usuario tiene una reserva activa en otro espacio (prioridad alta)
            $reservaExistente = Reserva::where(function ($query) use ($runUsuario) {
                $query
                    ->where('run_profesor', $runUsuario)
                    ->orWhere('run_solicitante', $runUsuario);
            })
                ->where('id_espacio', '!=', $idEspacio)
                ->where('estado', 'activa')
                ->whereNull('hora_salida')
                ->first();

            if ($reservaExistente) {
                // El usuario ya tiene una reserva activa en otro espacio
                return response()->json([
                    'tipo' => 'reserva_existente',
                    'mensaje' => 'Ya tienes una reserva activa en otro espacio. Debes finalizarla antes de solicitar una nueva.',
                    'espacio_disponible' => false
                ]);
            }

            // Verificar si el usuario tiene una reserva activa en este espacio
            $reservaActiva = Reserva::where(function ($query) use ($runUsuario) {
                $query
                    ->where('run_profesor', $runUsuario)
                    ->orWhere('run_solicitante', $runUsuario);
            })
                ->where('id_espacio', $idEspacio)
                ->where('estado', 'activa')
                ->first();

            // Verificar si el espacio está disponible
            $espacioDisponible = $espacio->estado === 'Disponible';

            if ($reservaActiva) {
                // El usuario tiene una reserva activa en este espacio
                \Log::info("Reserva activa encontrada para devolución - Usuario: {$runUsuario}, Espacio: {$idEspacio}, Reserva ID: {$reservaActiva->id_reserva}");
                return response()->json([
                    'tipo' => 'devolucion',
                    'mensaje' => 'Tienes una reserva activa en este espacio. ¿Deseas devolver las llaves?',
                    'reserva' => [
                        'id_reserva' => $reservaActiva->id_reserva,
                        'hora_inicio' => $reservaActiva->hora,
                        'fecha' => $reservaActiva->fecha_reserva,
                        'espacio' => $espacio->nombre_espacio
                    ],
                    'espacio_disponible' => false
                ]);
            }

            // Verificar si el usuario tiene una reserva PROGRAMADA en este espacio
            $reservaProgramada = Reserva::where(function ($query) use ($runUsuario) {
                $query
                    ->where('run_profesor', $runUsuario)
                    ->orWhere('run_solicitante', $runUsuario);
            })
                ->where('id_espacio', $idEspacio)
                ->where('estado', 'programada')
                ->where('fecha_reserva', Carbon::today()->toDateString())
                ->first();

            if ($reservaProgramada) {
                // El usuario tiene una reserva programada para este espacio.
                // Verificar si la hora actual está dentro del rango de módulos de la reserva.
                $ahora = Carbon::now();
                $puedeActivar = false;

                if ($reservaProgramada->modulo_inicio && $reservaProgramada->modulo_fin) {
                    // Verificar con la hora actual vs horarios de los módulos
                    $horaActualStr = $ahora->format('H:i:s');
                    $horarioModulos = $this->obtenerMapaHorariosModulos();

                    $horaInicioModulo = $horarioModulos[$reservaProgramada->modulo_inicio]['inicio'] ?? null;
                    $horaFinModulo = $horarioModulos[$reservaProgramada->modulo_fin]['fin'] ?? null;

                    if ($horaInicioModulo && $horaFinModulo) {
                        $puedeActivar = ($horaActualStr >= $horaInicioModulo && $horaActualStr <= $horaFinModulo);
                    }
                }

                if ($puedeActivar) {
                    // Activar la reserva: cambiar de programada → activa
                    $reservaProgramada->estado = 'activa';
                    $reservaProgramada->hora = $ahora->format('H:i:s');
                    $reservaProgramada->save();

                    // Marcar espacio como Ocupado
                    $espacio->estado = 'Ocupado';
                    $espacio->save();

                    \Log::info("✅ Reserva programada activada por escaneo - Usuario: {$runUsuario}, Espacio: {$idEspacio}, Reserva: {$reservaProgramada->id_reserva}");

                    return response()->json([
                        'tipo' => 'activacion_reserva',
                        'success' => true,
                        'mensaje' => 'Reserva programada activada exitosamente. El espacio ha sido asignado.',
                        'reserva' => [
                            'id_reserva' => $reservaProgramada->id_reserva,
                            'hora_inicio' => $reservaProgramada->hora,
                            'fecha' => $reservaProgramada->fecha_reserva,
                            'espacio' => $espacio->nombre_espacio,
                            'nombre_actividad' => $reservaProgramada->nombre_actividad,
                        ],
                        'espacio_disponible' => false
                    ]);
                } else {
                    // El usuario tiene reserva programada pero NO estamos en la franja horaria
                    \Log::info("⏰ Reserva programada encontrada pero fuera de horario - Usuario: {$runUsuario}, Reserva: {$reservaProgramada->id_reserva}");

                    return response()->json([
                        'tipo' => 'reserva_fuera_horario',
                        'success' => false,
                        'mensaje' => 'Tienes una reserva programada para este espacio, pero aún no es el horario correspondiente. La reserva se activará cuando llegue el momento.',
                        'reserva' => [
                            'id_reserva' => $reservaProgramada->id_reserva,
                            'fecha' => $reservaProgramada->fecha_reserva,
                            'modulo_inicio' => $reservaProgramada->modulo_inicio,
                            'modulo_fin' => $reservaProgramada->modulo_fin,
                            'nombre_actividad' => $reservaProgramada->nombre_actividad,
                        ],
                        'espacio_disponible' => false
                    ]);
                }
            }

            if ($espacioDisponible) {
                // Antes de permitir nueva reserva, verificar si hay una reserva programada
                // de OTRO usuario que cubre el módulo actual
                $reservaProgramadaOtro = Reserva::with(['profesor', 'solicitante'])
                    ->where('id_espacio', $idEspacio)
                    ->where('estado', 'programada')
                    ->where('fecha_reserva', Carbon::today()->toDateString())
                    ->where(function ($query) use ($runUsuario) {
                        $query->where(function ($q) use ($runUsuario) {
                            $q->whereNotNull('run_profesor')->where('run_profesor', '!=', $runUsuario);
                        })->orWhere(function ($q) use ($runUsuario) {
                            $q->whereNotNull('run_solicitante')->where('run_solicitante', '!=', $runUsuario);
                        });
                    })
                    ->first();

                if ($reservaProgramadaOtro && $reservaProgramadaOtro->modulo_inicio && $reservaProgramadaOtro->modulo_fin) {
                    $horaActualStr = Carbon::now()->format('H:i:s');
                    $horarioModulos = $this->obtenerMapaHorariosModulos();
                    $horaInicioMod = $horarioModulos[$reservaProgramadaOtro->modulo_inicio]['inicio'] ?? null;
                    $horaFinMod = $horarioModulos[$reservaProgramadaOtro->modulo_fin]['fin'] ?? null;

                    if ($horaInicioMod && $horaFinMod && $horaActualStr >= $horaInicioMod && $horaActualStr <= $horaFinMod) {
                        // Hay una reserva programada de otro usuario que cubre el horario actual
                        $nombreOcupante = $reservaProgramadaOtro->profesor->name
                            ?? $reservaProgramadaOtro->solicitante->nombre
                            ?? 'Otro usuario';

                        return response()->json([
                            'tipo' => 'espacio_ocupado',
                            'mensaje' => "El espacio tiene una reserva programada por {$nombreOcupante} en este horario.",
                            'espacio_disponible' => false,
                            'ocupante' => [
                                'tipo' => $reservaProgramadaOtro->run_profesor ? 'profesor' : 'solicitante',
                                'nombre' => $nombreOcupante,
                                'run' => $reservaProgramadaOtro->run_profesor ?? $reservaProgramadaOtro->run_solicitante,
                                'hora_inicio' => $horarioModulos[$reservaProgramadaOtro->modulo_inicio]['inicio'] ?? '-',
                                'fecha' => $reservaProgramadaOtro->fecha_reserva,
                            ]
                        ]);
                    }
                }

                // El espacio está disponible para crear una nueva reserva
                return response()->json([
                    'tipo' => 'nueva_reserva',
                    'mensaje' => 'Espacio disponible para reservar',
                    'espacio_disponible' => true
                ]);
            } else {
                // El espacio está ocupado por otro usuario - buscar información de la reserva activa más reciente
                $reservaOcupante = Reserva::with('asignatura')
                    ->where('id_espacio', $idEspacio)
                    ->where('estado', 'activa')
                    ->orderBy('created_at', 'desc')
                    ->first();

                $mensaje = 'El espacio está ocupado por otro usuario';
                $informacionOcupante = null;

                if ($reservaOcupante) {
                    if ($reservaOcupante->run_profesor) {
                        // Es un profesor
                        $profesor = Profesor::where('run_profesor', $reservaOcupante->run_profesor)->first();
                        if ($profesor) {
                            $mensaje = "El espacio está ocupado por el profesor {$profesor->name}";

                            // Obtener información de la asignatura si existe
                            $asignaturaInfo = 'Sin asignatura';
                            if ($reservaOcupante->id_asignatura) {
                                $asignatura = \App\Models\Asignatura::where('id_asignatura', $reservaOcupante->id_asignatura)->first();
                                if ($asignatura) {
                                    $asignaturaInfo = $asignatura->nombre_asignatura;
                                }
                            }

                            $informacionOcupante = [
                                'tipo' => 'profesor',
                                'nombre' => $profesor->name,
                                'run' => $profesor->run_profesor,
                                'email' => $profesor->email,
                                'tipo_profesor' => $profesor->tipo_profesor,
                                'hora_inicio' => $reservaOcupante->hora,
                                'fecha' => $reservaOcupante->fecha_reserva,
                                'asignatura' => $asignaturaInfo
                            ];
                        }
                    } elseif ($reservaOcupante->run_solicitante) {
                        // Es un solicitante
                        $solicitante = Solicitante::on('tenant')->where('run_solicitante', $reservaOcupante->run_solicitante)->first();
                        if ($solicitante) {
                            $mensaje = "El espacio está ocupado por el solicitante {$solicitante->nombre}";
                            $informacionOcupante = [
                                'tipo' => 'solicitante',
                                'nombre' => $solicitante->nombre,
                                'run' => $solicitante->run_solicitante,
                                'hora_inicio' => $reservaOcupante->hora,
                                'fecha' => $reservaOcupante->fecha_reserva
                            ];
                        }
                    }
                }

                // NUEVA LÓGICA: Verificar si el usuario que escanea tiene una clase programada ahora
                // para permitir forzar el cierre si la sala está ocupada por un tercero.
                $puedeForzarCierre = false;
                $idReservaAnterior = $reservaOcupante ? $reservaOcupante->id_reserva : null;

                if ($reservaOcupante && (string)$reservaOcupante->run_profesor !== (string)$runUsuario && (string)$reservaOcupante->run_solicitante !== (string)$runUsuario) {
                    $horaActual = Carbon::now();
                    $diaActual = strtolower($horaActual->locale('es')->isoFormat('dddd'));
                    $horaActualStr = $horaActual->format('H:i:s');

                    \Log::info('Verificando si scanner puede forzar cierre', [
                        'scanner_run' => $runUsuario,
                        'dia' => $diaActual,
                        'hora' => $horaActualStr,
                        'espacio' => $idEspacio
                    ]);

                    // Buscar si el usuario que escanea tiene planificación en este bloque
                    $planificacionScanner = Planificacion_Asignatura::where('id_espacio', $idEspacio)
                        ->whereHas('asignatura', function ($q) use ($runUsuario) {
                            $q->where('run_profesor', $runUsuario);
                        })
                        ->whereHas('modulo', function ($q) use ($diaActual, $horaActualStr) {
                            $q
                                ->where('dia', $diaActual)
                                ->where('hora_inicio', '<=', $horaActualStr)
                                ->where('hora_termino', '>=', $horaActualStr);
                        })
                        ->first();

                    if ($planificacionScanner) {
                        \Log::info('Planificación encontrada para forzar cierre', ['id' => $planificacionScanner->id]);
                        $puedeForzarCierre = true;
                    } else {
                        // También verificar reservas programadas del scanner
                        $reservaProgramadaScanner = Reserva::where('id_espacio', $idEspacio)
                            ->where(function($q) use ($runUsuario) {
                                $q->where('run_profesor', $runUsuario)
                                  ->orWhere('run_solicitante', $runUsuario);
                            })
                            ->where('estado', 'programada')
                            ->where('fecha_reserva', Carbon::today()->toDateString())
                            ->first();

                        if ($reservaProgramadaScanner) {
                            \Log::info('Reserva programada encontrada para forzar cierre', ['id' => $reservaProgramadaScanner->id_reserva]);
                            $puedeForzarCierre = true;
                        }
                    }
                }


                return response()->json([
                    'tipo' => 'espacio_ocupado',
                    'mensaje' => $mensaje,
                    'espacio_disponible' => false,
                    'ocupante' => $informacionOcupante,
                    'puede_forzar_cierre' => $puedeForzarCierre,
                    'id_reserva_anterior' => $idReservaAnterior
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Error al verificar estado del espacio y reserva: ' . $e->getMessage());
            return response()->json([
                'tipo' => 'error',
                'mensaje' => 'Error al verificar estado del espacio y reserva: ' . $e->getMessage()
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

            // CASO 1: Verificar si es profesor registrado
            $profesor = Profesor::select('run_profesor', 'name', 'email', 'celular', 'tipo_profesor')
                ->where('run_profesor', $run)
                ->first();

            if ($profesor) {
                return response()->json([
                    'verificado' => true,
                    'tipo_usuario' => 'profesor',
                    'usuario' => [
                        'run' => $profesor->run_profesor,
                        'nombre' => $profesor->name,
                        'email' => $profesor->email,
                        'telefono' => $profesor->celular,
                        'tipo_profesor' => $profesor->tipo_profesor
                    ],
                    'mensaje' => 'Profesor verificado correctamente'
                ]);
            }

            // CASO 2: Verificar si es solicitante registrado (BD tenant)
            $solicitante = Solicitante::on('tenant')
                ->where('run_solicitante', $run)
                ->where('activo', true)
                ->first();

            if ($solicitante) {
                return response()->json([
                    'verificado' => true,
                    'tipo_usuario' => 'solicitante_registrado',
                    'usuario' => [
                        'run' => $solicitante->run_solicitante,
                        'nombre' => $solicitante->nombre,
                        'email' => $solicitante->correo,
                        'telefono' => $solicitante->telefono
                    ],
                    'mensaje' => 'Solicitante verificado correctamente'
                ]);
            }

            // CASO 3: Usuario no encontrado - Mostrar modal de registro como solicitante
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
     * Crear reserva (método principal)
     */
    public function crearReserva(Request $request)
    {
        // Usar transacción para prevenir race conditions
        return DB::transaction(function () use ($request) {
            try {
                $request->validate([
                    'run_usuario' => 'required|string',
                    'id_espacio' => 'required|string',
                    'tipo_usuario' => 'required|in:profesor,solicitante,solicitante_registrado'
                ]);

                $runUsuario = $request->input('run_usuario');
                $idEspacio = $request->input('id_espacio');
                $tipoUsuario = $request->input('tipo_usuario');

                // VALIDACIÓN GLOBAL ANTI-MÚLTIPLES RESERVAS
                // Verificar si ya tiene reservas activas ANTES de continuar
                $reservasActivasExistentes = null;

                if ($tipoUsuario === 'profesor') {
                    $reservasActivasExistentes = Reserva::where('run_profesor', $runUsuario)
                        ->where('estado', 'activa')
                        ->where(function ($query) {
                            $query
                                ->whereNull('hora_salida')
                                ->orWhere('hora_salida', '');
                        })
                        ->lockForUpdate()  // Bloquear para prevenir race conditions
                        ->get();
                } else {
                    $reservasActivasExistentes = Reserva::where('run_solicitante', $runUsuario)
                        ->where('estado', 'activa')
                        ->where(function ($query) {
                            $query
                                ->whereNull('hora_salida')
                                ->orWhere('hora_salida', '');
                        })
                        ->lockForUpdate()  // Bloquear para prevenir race conditions
                        ->get();
                }

                if ($reservasActivasExistentes && $reservasActivasExistentes->count() > 0) {
                    $espaciosOcupados = $reservasActivasExistentes->pluck('id_espacio')->toArray();
                    \Log::warning('Intento de crear múltiples reservas detectado', [
                        'run_usuario' => $runUsuario,
                        'tipo_usuario' => $tipoUsuario,
                        'espacios_ya_ocupados' => $espaciosOcupados,
                        'espacio_solicitado' => $idEspacio
                    ]);

                    return response()->json([
                        'success' => false,
                        'mensaje' => '⚠️ ACCESO DENEGADO: Ya tienes ' . $reservasActivasExistentes->count() . ' reserva(s) activa(s) en: ' . implode(', ', $espaciosOcupados) . '. Solo se permite una reserva activa por usuario.',
                        'reservas_activas' => $reservasActivasExistentes->map(function ($r) {
                            return [
                                'id_reserva' => $r->id_reserva,
                                'espacio' => $r->id_espacio,
                                'hora_inicio' => $r->hora,
                                'fecha' => $r->fecha_reserva
                            ];
                        })
                    ], 400);
                }

                // Verificar que el espacio existe
                $espacio = Espacio::where('id_espacio', $idEspacio)->lockForUpdate()->first();
                if (!$espacio) {
                    return response()->json([
                        'success' => false,
                        'mensaje' => 'Espacio no encontrado'
                    ], 404);
                }

                // Verificar que el espacio esté disponible
                if ($espacio->estado !== 'Disponible') {
                    return response()->json([
                        'success' => false,
                        'mensaje' => 'El espacio no está disponible'
                    ], 400);
                }

                $horaActual = now()->format('H:i:s');
                $fechaActual = now()->format('Y-m-d');
                $ahora = now();

                // Validar horario académico
                $hora = (int) now()->format('H');
                $minutos = (int) now()->format('i');
                $horaEnMinutos = $hora * 60 + $minutos;

                $inicioAcademico = 8 * 60 + 10;  // 08:10
                $finAcademico = 23 * 60;  // 23:00

                if ($horaEnMinutos < $inicioAcademico || $horaEnMinutos >= $finAcademico) {
                    return response()->json([
                        'success' => false,
                        'mensaje' => 'No se pueden crear reservas fuera del horario académico (08:10 - 23:00).'
                    ], 400);
                }

                // Crear reserva según el tipo de usuario
                if ($tipoUsuario === 'profesor') {
                    return $this->crearReservaProfesor($request, $espacio, $horaActual, $fechaActual, $ahora);
                } elseif ($tipoUsuario === 'solicitante' || $tipoUsuario === 'solicitante_registrado') {
                    return $this->crearReservaSolicitante($request, $espacio, $horaActual, $fechaActual, $ahora);
                } else {
                    return response()->json([
                        'success' => false,
                        'mensaje' => 'Tipo de usuario no válido'
                    ], 400);
                }
            } catch (\Illuminate\Validation\ValidationException $e) {
                \Log::error('Error de validación al crear reserva: ' . json_encode($e->errors()));
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Error de validación en los datos enviados',
                    'errors' => $e->errors()
                ], 422);
            } catch (\Exception $e) {
                \Log::error('Error al crear reserva: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Error al crear reserva: ' . $e->getMessage()
                ], 500);
            }
        });  // Cierre de la transacción
    }

    /**
     * Crear reserva para profesor
     */
    private function crearReservaProfesor($request, $espacio, $horaActual, $fechaActual, $ahora)
    {
        $runUsuario = $request->input('run_usuario');

        // Verificar si el profesor existe
        $profesor = Profesor::where('run_profesor', $runUsuario)->first();
        if (!$profesor) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Profesor no encontrado'
            ], 404);
        }

        // Verificar si ya tiene una reserva activa (más robusta)
        $reservaExistente = Reserva::where('run_profesor', $runUsuario)
            ->where('estado', 'activa')
            ->where(function ($query) {
                $query
                    ->whereNull('hora_salida')
                    ->orWhere('hora_salida', '');
            })
            ->first();

        // Log para debug
        \Log::info('Verificando reservas existentes para profesor', [
            'run_profesor' => $runUsuario,
            'reserva_existente_encontrada' => $reservaExistente !== null,
            'id_reserva_existente' => $reservaExistente ? $reservaExistente->id_reserva : null,
            'espacio_existente' => $reservaExistente ? $reservaExistente->id_espacio : null
        ]);

        if ($reservaExistente) {
            return response()->json([
                'success' => false,
                'mensaje' => "Ya tienes una reserva activa en el espacio {$reservaExistente->id_espacio}. Debes finalizarla antes de crear una nueva.",
                'reserva_existente' => [
                    'id_reserva' => $reservaExistente->id_reserva,
                    'espacio' => $reservaExistente->id_espacio,
                    'hora_inicio' => $reservaExistente->hora,
                    'fecha' => $reservaExistente->fecha_reserva
                ]
            ], 400);
        }

        // Validación adicional: verificar todas las reservas activas en el sistema para este profesor
        $todasReservasActivas = Reserva::where('run_profesor', $runUsuario)
            ->where('estado', 'activa')
            ->get();

        \Log::info('Todas las reservas activas del profesor', [
            'run_profesor' => $runUsuario,
            'total_reservas_activas' => $todasReservasActivas->count(),
            'reservas' => $todasReservasActivas->map(function ($r) {
                return [
                    'id_reserva' => $r->id_reserva,
                    'espacio' => $r->id_espacio,
                    'estado' => $r->estado,
                    'hora_salida' => $r->hora_salida,
                    'fecha' => $r->fecha_reserva
                ];
            })
        ]);

        if ($todasReservasActivas->count() > 0) {
            $espaciosOcupados = $todasReservasActivas->pluck('id_espacio')->toArray();
            return response()->json([
                'success' => false,
                'mensaje' => 'Tienes ' . $todasReservasActivas->count() . ' reserva(s) activa(s) en: ' . implode(', ', $espaciosOcupados) . '. Debes finalizarlas antes de crear una nueva.',
                'reservas_activas' => $todasReservasActivas->map(function ($r) {
                    return [
                        'id_reserva' => $r->id_reserva,
                        'espacio' => $r->id_espacio,
                        'hora_inicio' => $r->hora,
                        'fecha' => $r->fecha_reserva
                    ];
                })
            ], 400);
        }

        // Verificar si el profesor tiene una clase programada en este espacio y momento
        $diaActual = strtolower($ahora->locale('es')->isoFormat('dddd'));
        $periodo = SemesterHelper::getCurrentPeriod();

        // Log para debug
        \Log::info('Buscando clases para profesor', [
            'run_profesor' => $runUsuario,
            'dia_actual' => $diaActual,
            'hora_actual' => $horaActual,
            'periodo' => $periodo,
            'id_espacio' => $espacio->id_espacio
        ]);

        // Buscar la clase programada actual (en curso)
        $claseProgramadaActual = Planificacion_Asignatura::with([
            'asignatura:id_asignatura,nombre_asignatura,run_profesor',
            'modulo:id_modulo,dia,hora_inicio,hora_termino'
        ])
            ->whereHas('asignatura', function ($query) use ($runUsuario) {
                $query->where('run_profesor', $runUsuario);
            })
            ->whereHas('modulo', function ($query) use ($diaActual, $horaActual) {
                $query
                    ->where('dia', $diaActual)
                    ->where('hora_inicio', '<=', $horaActual)
                    ->where('hora_termino', '>', $horaActual);
            })
            ->whereHas('horario', function ($query) use ($periodo) {
                $query->where('periodo', $periodo);
            })
            ->where('id_espacio', $espacio->id_espacio)
            ->first();

        // =====================================================
        // BÚSQUEDA DE CLASE ASOCIADA A LA RESERVA
        // Prioridad: 1) En curso aquí → 2) Próxima aquí (15min) → 3) En curso en otra sala (cambio) → 4) Espontánea
        // =====================================================
        $siguienteClaseProgramada = null;
        $claseEnOtraSala = null;
        $esCambioDeSala = false;

        if (!$claseProgramadaActual) {
            // Buscar siguiente clase que INICIA en los próximos 15 minutos en ESTA sala
            // (evita asociar clases que empiezan horas después)
            $horaLimiteAnticipada = Carbon::createFromFormat('H:i:s', $horaActual)->addMinutes(15)->format('H:i:s');

            $siguienteClaseProgramada = Planificacion_Asignatura::with([
                'asignatura:id_asignatura,nombre_asignatura,run_profesor',
                'modulo:id_modulo,dia,hora_inicio,hora_termino'
            ])
                ->whereHas('asignatura', function ($query) use ($runUsuario) {
                    $query->where('run_profesor', $runUsuario);
                })
                ->whereHas('modulo', function ($query) use ($diaActual, $horaActual, $horaLimiteAnticipada) {
                    $query
                        ->where('dia', $diaActual)
                        ->where('hora_inicio', '>', $horaActual)
                        ->where('hora_inicio', '<=', $horaLimiteAnticipada);
                })
                ->whereHas('horario', function ($query) use ($periodo) {
                    $query->where('periodo', $periodo);
                })
                ->where('id_espacio', $espacio->id_espacio)
                ->orderBy('id_modulo')
                ->first();

            // CAMBIO DE SALA: Si no hay clase en esta sala (ni en curso ni próxima),
            // verificar si el profesor tiene una clase EN CURSO en otra sala.
            // Esto permite que el profesor realice su clase programada en una sala diferente
            // y quede registro correcto de que SÍ realizó la clase.
            if (!$siguienteClaseProgramada) {
                $claseEnOtraSala = Planificacion_Asignatura::with([
                    'asignatura:id_asignatura,nombre_asignatura,run_profesor',
                    'modulo:id_modulo,dia,hora_inicio,hora_termino',
                    'espacio:id_espacio,nombre_espacio'
                ])
                    ->whereHas('asignatura', function ($query) use ($runUsuario) {
                        $query->where('run_profesor', $runUsuario);
                    })
                    ->whereHas('modulo', function ($query) use ($diaActual, $horaActual) {
                        $query
                            ->where('dia', $diaActual)
                            ->where('hora_inicio', '<=', $horaActual)
                            ->where('hora_termino', '>', $horaActual);
                    })
                    ->whereHas('horario', function ($query) use ($periodo) {
                        $query->where('periodo', $periodo);
                    })
                    ->where('id_espacio', '!=', $espacio->id_espacio)
                    ->first();

                if ($claseEnOtraSala) {
                    $esCambioDeSala = true;
                    \Log::info('Cambio de sala detectado', [
                        'run_profesor' => $runUsuario,
                        'sala_original' => $claseEnOtraSala->id_espacio,
                        'sala_nueva' => $espacio->id_espacio,
                        'asignatura' => $claseEnOtraSala->asignatura->nombre_asignatura ?? 'N/A'
                    ]);
                }
            }
        }

        // Determinar la clase encontrada según prioridad:
        // 1) Clase en curso en esta sala, 2) Clase próxima en esta sala, 3) Clase en curso en otra sala (cambio)
        $claseEncontrada = $claseProgramadaActual ?? $siguienteClaseProgramada ?? $claseEnOtraSala;
        $esClaseAnticipada = !$claseProgramadaActual && $siguienteClaseProgramada !== null;

        $todosLosModulosClase = null;
        $horaInicioCompleta = $horaActual;
        $horaFinCompleta = null;

        if ($claseEncontrada) {
            // Para cambio de sala, buscar módulos en la sala ORIGINAL (donde está programada la clase)
            $espacioBusquedaModulos = $esCambioDeSala ? $claseEnOtraSala->id_espacio : $espacio->id_espacio;

            $todosLosModulosClase = Planificacion_Asignatura::with([
                'asignatura:id_asignatura,nombre_asignatura,run_profesor',
                'modulo:id_modulo,dia,hora_inicio,hora_termino'
            ])
                ->where('id_asignatura', $claseEncontrada->id_asignatura)
                ->where('id_espacio', $espacioBusquedaModulos)
                ->whereHas('modulo', function ($query) use ($diaActual) {
                    $query->where('dia', $diaActual);
                })
                ->whereHas('horario', function ($query) use ($periodo) {
                    $query->where('periodo', $periodo);
                })
                ->orderBy('id_modulo')
                ->get();

            // Detectar módulos consecutivos
            $modulosConsecutivos = [];
            $moduloObjetivoIndex = null;

            // Encontrar el índice del módulo objetivo (actual o siguiente clase)
            foreach ($todosLosModulosClase as $index => $planificacion) {
                if ($claseProgramadaActual || $esCambioDeSala) {
                    // Para clase en curso o cambio de sala, buscar módulo actual por hora
                    if ($planificacion->modulo->hora_inicio <= $horaActual &&
                            $planificacion->modulo->hora_termino > $horaActual) {
                        $moduloObjetivoIndex = $index;
                        break;
                    }
                } else {
                    // Para siguiente clase (anticipada), buscar el módulo de la siguiente clase
                    if ($siguienteClaseProgramada && $planificacion->id_modulo === $siguienteClaseProgramada->id_modulo) {
                        $moduloObjetivoIndex = $index;
                        break;
                    }
                }
            }

            if ($moduloObjetivoIndex !== null) {
                // Agregar el módulo objetivo
                $modulosConsecutivos[] = $todosLosModulosClase[$moduloObjetivoIndex];

                // Buscar módulos anteriores consecutivos
                for ($i = $moduloObjetivoIndex - 1; $i >= 0; $i--) {
                    $moduloAnterior = $todosLosModulosClase[$i];
                    $siguienteModulo = $todosLosModulosClase[$i + 1];

                    if ($moduloAnterior->modulo->hora_termino === $siguienteModulo->modulo->hora_inicio) {
                        array_unshift($modulosConsecutivos, $moduloAnterior);
                    } else {
                        break;
                    }
                }

                // Buscar módulos posteriores consecutivos
                for ($i = $moduloObjetivoIndex + 1; $i < count($todosLosModulosClase); $i++) {
                    $moduloActual = $todosLosModulosClase[$i - 1];
                    $moduloSiguiente = $todosLosModulosClase[$i];

                    if ($moduloActual->modulo->hora_termino === $moduloSiguiente->modulo->hora_inicio) {
                        $modulosConsecutivos[] = $moduloSiguiente;
                    } else {
                        break;
                    }
                }

                // Determinar horas de inicio y fin de toda la secuencia
                if (!empty($modulosConsecutivos)) {
                    $horaInicioCompleta = $modulosConsecutivos[0]->modulo->hora_inicio;
                    $horaFinCompleta = end($modulosConsecutivos)->modulo->hora_termino;
                }
            }
        }

        // Log del resultado final de la búsqueda
        \Log::info('Resultado final de búsqueda de clases', [
            'clase_actual_encontrada' => $claseProgramadaActual !== null,
            'siguiente_clase_encontrada' => $siguienteClaseProgramada !== null,
            'clase_encontrada_final' => $claseEncontrada !== null,
            'es_clase_anticipada' => $esClaseAnticipada,
            'modulos_consecutivos_count' => !empty($modulosConsecutivos) ? count($modulosConsecutivos) : 0
        ]);

        // Log adicional para debuggear el problema de "asignatura no especificada"
        if ($claseEncontrada) {
            \Log::info('Detalles de la clase encontrada', [
                'id_planificacion' => $claseEncontrada->id_planificacion ?? 'N/A',
                'id_asignatura' => $claseEncontrada->id_asignatura ?? 'N/A',
                'asignatura_cargada' => $claseEncontrada->asignatura !== null,
                'nombre_asignatura' => $claseEncontrada->asignatura ? $claseEncontrada->asignatura->nombre_asignatura : 'ASIGNATURA ES NULL',
                'id_modulo' => $claseEncontrada->id_modulo ?? 'N/A',
                'modulo_cargado' => $claseEncontrada->modulo !== null
            ]);
        }

        // Crear la reserva
        $reserva = new Reserva();
        $reserva->id_reserva = Reserva::generarIdUnico();
        $reserva->run_profesor = $runUsuario;
        $reserva->id_espacio = $espacio->id_espacio;
        $reserva->fecha_reserva = $fechaActual;

        $reserva->estado = 'activa';

        // Si tiene clase programada (actual o siguiente), asignar automáticamente como clase programada
        if ($claseEncontrada && !empty($modulosConsecutivos)) {
            // Validar que la clase encontrada tenga asignatura antes de usarla
            if (!$claseEncontrada->asignatura) {
                \Log::error('Clase encontrada sin asignatura válida', [
                    'id_planificacion' => $claseEncontrada->id_planificacion,
                    'id_asignatura' => $claseEncontrada->id_asignatura
                ]);

                // Intentar recargar la asignatura manualmente
                $claseEncontrada->load('asignatura');

                if (!$claseEncontrada->asignatura) {
                    // Si aún no hay asignatura, crear una reserva espontánea en su lugar
                    \Log::warning('No se pudo cargar la asignatura, creando reserva espontánea');
                    $reserva->tipo_reserva = 'espontanea';
                    $reserva->hora = $horaActual;
                    $mensaje = 'Reserva espontánea creada (problema con datos de asignatura)';
                    $informacionModulos = null;
                } else {
                    \Log::info('Asignatura recargada exitosamente', [
                        'nombre_asignatura' => $claseEncontrada->asignatura->nombre_asignatura
                    ]);
                }
            }

            // Proceder solo si tenemos una asignatura válida
            if ($claseEncontrada->asignatura) {
                $reserva->tipo_reserva = 'programada';
                $reserva->id_planificacion = $claseEncontrada->id_planificacion ?? null;
                $reserva->id_asignatura = $claseEncontrada->id_asignatura;

                // Guardar la hora REAL de llegada del profesor (no la hora programada del módulo).
                // Esto permite calcular atrasos en informes comparando hora vs. hora_inicio del módulo.
                $reserva->hora = $horaActual;

                // Calcular duración total en módulos
                $totalModulos = count($modulosConsecutivos);
                $modulosInfo = [];
                foreach ($modulosConsecutivos as $modulo) {
                    $modulosInfo[] = explode('.', $modulo->modulo->id_modulo)[1] ?? 'N/A';
                }

                // Detectar atraso: el profesor llegó después del inicio programado del módulo.
                // Solo aplica cuando la clase ya estaba en curso ($claseProgramadaActual) y no es cambio de sala.
                $esAtraso = false;
                $minutosAtraso = 0;
                $infoAtraso = '';
                if ($claseProgramadaActual !== null && !$esCambioDeSala) {
                    $horaInicioClaseCarbon = Carbon::createFromFormat('H:i:s', $horaInicioCompleta);
                    $horaActualCarbon = Carbon::createFromFormat('H:i:s', $horaActual);
                    if ($horaActualCarbon->gt($horaInicioClaseCarbon)) {
                        $esAtraso = true;
                        $minutosAtraso = $horaInicioClaseCarbon->diffInMinutes($horaActualCarbon);
                        $infoAtraso = " (atraso: {$minutosAtraso} min desde las " . substr($horaInicioCompleta, 0, 5) . ')';
                    }
                }

                // Determinar el tipo de asignación (prioridad: cambio de sala > anticipada > atraso > en horario)
                $tipoAsignacion = $esCambioDeSala ? 'cambio de sala' : ($esClaseAnticipada ? 'anticipada' : ($esAtraso ? 'con atraso' : 'en horario'));
                $tiempoAnticipacion = '';
                $infoCambioDeSala = '';

                if ($esCambioDeSala) {
                    $salaOriginal = $claseEnOtraSala->id_espacio;
                    $infoCambioDeSala = " (sala original: {$salaOriginal})";
                }

                if ($esClaseAnticipada) {
                    $horaInicioClase = Carbon::createFromFormat('H:i:s', $horaInicioCompleta);
                    $horaActualCarbon = Carbon::createFromFormat('H:i:s', $horaActual);
                    $minutosAnticipacion = $horaInicioClase->diffInMinutes($horaActualCarbon);
                    $tiempoAnticipacion = " ({$minutosAnticipacion} min antes)";
                }

                $nombreAsignatura = $claseEncontrada->asignatura->nombre_asignatura ?? 'Error al cargar asignatura';

                $reserva->observaciones = sprintf(
                    'Reserva asignada automáticamente %s%s%s%s - Clase programada: %s | Módulos: %s (%s - %s) | Duración: %d módulos',
                    $tipoAsignacion,
                    $tiempoAnticipacion,
                    $infoAtraso,
                    $infoCambioDeSala,
                    $nombreAsignatura,
                    implode(', ', $modulosInfo),
                    substr($horaInicioCompleta, 0, 5),
                    substr($horaFinCompleta, 0, 5),
                    $totalModulos
                );

                $mensaje = sprintf(
                    'Reserva de clase programada asignada automáticamente %s por %d módulos (%s - %s)%s%s%s',
                    $tipoAsignacion,
                    $totalModulos,
                    substr($horaInicioCompleta, 0, 5),
                    substr($horaFinCompleta, 0, 5),
                    $tiempoAnticipacion,
                    $infoAtraso,
                    $infoCambioDeSala
                );

                $informacionModulos = [
                    'total_modulos' => $totalModulos,
                    'modulos' => $modulosInfo,
                    'hora_inicio_completa' => substr($horaInicioCompleta, 0, 5),
                    'hora_fin_completa' => substr($horaFinCompleta, 0, 5),
                    'asignatura' => $nombreAsignatura,
                    'es_anticipada' => $esClaseAnticipada,
                    'es_atraso' => $esAtraso,
                    'minutos_atraso' => $minutosAtraso,
                    'es_cambio_sala' => $esCambioDeSala,
                    'sala_original' => $esCambioDeSala ? ($claseEnOtraSala->id_espacio ?? null) : null,
                    'minutos_anticipacion' => $esClaseAnticipada ? $minutosAnticipacion : 0
                ];
            }
        } else {
            // Log adicional para entender por qué no se detecta la clase
            \Log::info('No se encontró clase programada, creando reserva espontánea', [
                'run_profesor' => $runUsuario,
                'espacio' => $espacio->id_espacio,
                'hora_actual' => $horaActual,
                'dia' => $diaActual
            ]);

            $reserva->tipo_reserva = 'espontanea';
            $reserva->id_asignatura = null;  // Explícitamente sin clase asociada
            $reserva->hora = $horaActual;
            $mensaje = 'Reserva espontánea creada exitosamente';
            $informacionModulos = null;
        }

        $reserva->save();

        // Enviar correo de confirmación de reserva al profesor
        $this->enviarCorreoReserva($reserva);

        // Cambiar estado del espacio
        $espacio->estado = 'Ocupado';
        $espacio->save();

        return response()->json([
            'success' => true,
            'mensaje' => $mensaje,
            'es_clase_programada' => $claseEncontrada !== null,
            'es_clase_anticipada' => $esClaseAnticipada,
            'es_atraso' => $esAtraso,
            'minutos_atraso' => $minutosAtraso,
            'es_cambio_sala' => $esCambioDeSala,
            'reserva' => [
                'id' => $reserva->id_reserva,
                'espacio' => $espacio->nombre_espacio,
                'fecha' => $fechaActual,
                'hora_inicio' => substr($reserva->hora, 0, 5),
                'tipo_reserva' => $reserva->tipo_reserva,
                'informacion_modulos' => $informacionModulos
            ]
        ]);
    }

    /**
     * Crear reserva para solicitante
     */
    private function crearReservaSolicitante($request, $espacio, $horaActual, $fechaActual, $ahora)
    {
        $runUsuario = $request->input('run_usuario');

        // Verificar si el solicitante existe
        $solicitante = Solicitante::on('tenant')
            ->where('run_solicitante', $runUsuario)
            ->where('activo', true)
            ->first();

        if (!$solicitante) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Solicitante no encontrado'
            ], 404);
        }

        // Verificar si ya tiene una reserva activa (más robusta)
        $reservaExistente = Reserva::where('run_solicitante', $runUsuario)
            ->where('estado', 'activa')
            ->where(function ($query) {
                $query
                    ->whereNull('hora_salida')
                    ->orWhere('hora_salida', '');
            })
            ->first();

        // Log para debug
        \Log::info('Verificando reservas existentes para solicitante', [
            'run_solicitante' => $runUsuario,
            'reserva_existente_encontrada' => $reservaExistente !== null,
            'id_reserva_existente' => $reservaExistente ? $reservaExistente->id_reserva : null,
            'espacio_existente' => $reservaExistente ? $reservaExistente->id_espacio : null
        ]);

        if ($reservaExistente) {
            return response()->json([
                'success' => false,
                'mensaje' => "Ya tienes una reserva activa en el espacio {$reservaExistente->id_espacio}. Debes finalizarla antes de crear una nueva.",
                'reserva_existente' => [
                    'id_reserva' => $reservaExistente->id_reserva,
                    'espacio' => $reservaExistente->id_espacio,
                    'hora_inicio' => $reservaExistente->hora,
                    'fecha' => $reservaExistente->fecha_reserva
                ]
            ], 400);
        }

        // Validación adicional: verificar todas las reservas activas en el sistema para este solicitante
        $todasReservasActivas = Reserva::where('run_solicitante', $runUsuario)
            ->where('estado', 'activa')
            ->get();

        \Log::info('Todas las reservas activas del solicitante', [
            'run_solicitante' => $runUsuario,
            'total_reservas_activas' => $todasReservasActivas->count(),
            'reservas' => $todasReservasActivas->map(function ($r) {
                return [
                    'id_reserva' => $r->id_reserva,
                    'espacio' => $r->id_espacio,
                    'estado' => $r->estado,
                    'hora_salida' => $r->hora_salida,
                    'fecha' => $r->fecha_reserva
                ];
            })
        ]);

        if ($todasReservasActivas->count() > 0) {
            $espaciosOcupados = $todasReservasActivas->pluck('id_espacio')->toArray();
            return response()->json([
                'success' => false,
                'mensaje' => 'Tienes ' . $todasReservasActivas->count() . ' reserva(s) activa(s) en: ' . implode(', ', $espaciosOcupados) . '. Debes finalizarlas antes de crear una nueva.',
                'reservas_activas' => $todasReservasActivas->map(function ($r) {
                    return [
                        'id_reserva' => $r->id_reserva,
                        'espacio' => $r->id_espacio,
                        'hora_inicio' => $r->hora,
                        'fecha' => $r->fecha_reserva
                    ];
                })
            ], 400);
        }

        // Crear la reserva
        $reserva = new Reserva();
        $reserva->id_reserva = Reserva::generarIdUnico();
        $reserva->run_solicitante = $runUsuario;
        $reserva->id_espacio = $espacio->id_espacio;
        $reserva->fecha_reserva = $fechaActual;
        $reserva->hora = $horaActual;
        $reserva->run_profesor = null;  // explícito: reserva creada por solicitante
        $reserva->tipo_reserva = 'espontanea';
        $reserva->estado = 'activa';
        $reserva->save();

        // Enviar correo de confirmación de reserva al solicitante
        $this->enviarCorreoReserva($reserva);

        // Cambiar estado del espacio
        $espacio->estado = 'Ocupado';
        $espacio->save();

        return response()->json([
            'success' => true,
            'mensaje' => 'Reserva creada exitosamente',
            'reserva' => [
                'id' => $reserva->id_reserva,
                'espacio' => $espacio->nombre_espacio,
                'fecha' => $fechaActual,
                'hora_inicio' => $horaActual
            ]
        ]);
    }

    /**
     * Verificar clases programadas
     */
    public function verificarClasesProgramadas($run, $horaActual, $diaActual)
    {
        try {
            // Log para debug
            \Log::info('verificarClasesProgramadas - Iniciando', [
                'run' => $run,
                'hora_actual' => $horaActual,
                'dia_actual' => $diaActual
            ]);

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

            \Log::info('verificarClasesProgramadas - Día convertido', [
                'dia_numerico' => $diaActual,
                'nombre_dia' => $nombreDia
            ]);

            // Obtener período actual
            $periodo = SemesterHelper::getCurrentPeriod();

            \Log::info('verificarClasesProgramadas - Período obtenido', [
                'periodo' => $periodo
            ]);

            // Buscar planificaciones del profesor para el día actual
            $planificaciones = Planificacion_Asignatura::with(['asignatura', 'modulo', 'espacio'])
                ->whereHas('asignatura', function ($query) use ($run) {
                    $query->where('run_profesor', $run);
                })
                ->whereHas('modulo', function ($query) use ($nombreDia) {
                    $query->where('dia', $nombreDia);
                })
                ->whereHas('horario', function ($query) use ($periodo) {
                    $query->where('periodo', $periodo);
                })
                ->get();

            \Log::info('verificarClasesProgramadas - Planificaciones encontradas', [
                'total' => $planificaciones->count(),
                'planificaciones' => $planificaciones->map(function ($p) {
                    return [
                        'asignatura' => $p->asignatura->nombre_asignatura ?? 'N/A',
                        'espacio' => $p->espacio->nombre_espacio ?? 'N/A',
                        'hora_inicio' => $p->modulo->hora_inicio ?? 'N/A',
                        'hora_termino' => $p->modulo->hora_termino ?? 'N/A'
                    ];
                })
            ]);

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

            \Log::info('verificarClasesProgramadas - Resultado final', [
                'tiene_clases' => $tieneClasesEnHorario,
                'total_planificaciones' => $planificaciones->count(),
                'clase_actual_encontrada' => $claseActual !== null,
                'siguiente_clase_encontrada' => $siguienteClase !== null
            ]);

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
     * Busca un usuario (profesor o estudiante) por su código QR
     */
    public function buscarUsuarioPorQR($qr)
    {
        try {
            // Primero buscar en profesores
            $profesor = Profesor::where('run_profesor', $qr)->first();

            if ($profesor) {
                return response()->json([
                    'success' => true,
                    'tipo' => 'profesor',
                    'nombre' => $profesor->nombre_profesor,
                    'run' => $profesor->run_profesor,
                    'email' => $profesor->email_profesor ?? null
                ]);
            }

            // Si no es profesor, buscar en usuarios (estudiantes, etc.)
            $usuario = \App\Models\User::where('run', $qr)->first();

            if ($usuario) {
                return response()->json([
                    'success' => true,
                    'tipo' => 'usuario',
                    'nombre' => $usuario->name,
                    'run' => $usuario->run,
                    'email' => $usuario->email
                ]);
            }

            // No encontrado
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error al buscar usuario por QR: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar usuario'
            ], 500);
        }
    }

    /**
     * Registra la asistencia de múltiples asistentes en una sala de estudio
     */
    public function registrarAsistenciaSalaEstudio(Request $request)
    {
        try {
            // Asegurar contexto tenant para consultas/escrituras
            $this->establecerContextoTenant();

            $validated = $request->validate([
                'espacio_id' => 'required|string|exists:tenant.espacios,id_espacio',
                'asistentes' => 'required|array|min:1',
                'asistentes.*.run' => 'required|string',
                'asistentes.*.nombre' => 'required|string'
            ]);

            $espacioId = $validated['espacio_id'];
            $asistentes = collect($validated['asistentes'])
                ->map(function ($asistente) {
                    return [
                        'run' => preg_replace('/[^0-9kK]/', '', (string) $asistente['run']),
                        'nombre' => trim((string) $asistente['nombre']),
                    ];
                })
                ->filter(fn($a) => $a['run'] !== '' && $a['nombre'] !== '')
                ->unique('run')
                ->values();

            if ($asistentes->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debe registrar al menos un asistente válido'
                ], 422);
            }

            // Verificar que el espacio sea una sala de estudio
            $espacio = Espacio::where('id_espacio', $espacioId)->first();

            if (!$espacio) {
                return response()->json([
                    'success' => false,
                    'message' => 'Espacio no encontrado'
                ], 404);
            }

            if (strtolower($espacio->tipo_espacio) !== 'sala de estudio') {
                return response()->json([
                    'success' => false,
                    'message' => 'El espacio no es una sala de estudio'
                ], 400);
            }

            // Verificar capacidad
            if ($espacio->capacidad_maxima && $asistentes->count() > (int) $espacio->capacidad_maxima) {
                return response()->json([
                    'success' => false,
                    'message' => 'La cantidad de asistentes excede la capacidad máxima de la sala'
                ], 400);
            }

            $ahora = Carbon::now();
            $fechaActual = $ahora->toDateString();
            $horaActual = $ahora->format('H:i:s');

            $reserva = DB::transaction(function () use ($espacio, $espacioId, $asistentes, $fechaActual, $horaActual, $ahora) {
                // Evitar múltiples reservas activas simultáneas en la misma sala de estudio
                $reservaActiva = Reserva::where('id_espacio', $espacioId)
                    ->whereDate('fecha_reserva', $fechaActual)
                    ->where('estado', 'activa')
                    ->lockForUpdate()
                    ->first();

                if ($reservaActiva) {
                    // Si vuelve a escanear el responsable, se interpreta como devolución/cierre de sala
                    $runEscaneados = $asistentes->pluck('run')->values();
                    if ($runEscaneados->contains((string) $reservaActiva->run_solicitante)) {
                        $horaSalida = $ahora->format('H:i:s');

                        $reservaActiva->estado = 'finalizada';
                        $reservaActiva->hora_salida = $horaSalida;
                        $reservaActiva->observaciones = trim(($reservaActiva->observaciones ?? '') . ' | Sala de estudio finalizada por responsable');
                        $reservaActiva->save();

                        // Finalizar asistencias en curso de esa reserva
                        Asistencia::where('id_reserva', $reservaActiva->id_reserva)
                            ->where('estado', Asistencia::ESTADO_PRESENTE)
                            ->update([
                                'estado' => Asistencia::ESTADO_FINALIZADO,
                                'hora_salida' => $horaSalida,
                                'updated_at' => now(),
                            ]);

                        // Liberar espacio
                        $espacio->estado = 'Disponible';
                        if (!is_null($espacio->capacidad_maxima)) {
                            $espacio->puestos_disponibles = (int) $espacio->capacidad_maxima;
                        }
                        $espacio->save();

                        return [
                            'accion' => 'devolucion',
                            'reserva' => $reservaActiva,
                            'run_responsable' => (string) $reservaActiva->run_solicitante,
                        ];
                    }

                    throw new \RuntimeException('Ya existe una reserva activa en esta sala de estudio. Solo el responsable puede finalizarla escaneando nuevamente su carnet.');
                }

                // El primer carnet escaneado es el responsable de la reserva
                $responsable = $asistentes->first();

                $solicitanteResponsable = Solicitante::firstOrCreate(
                    ['run_solicitante' => $responsable['run']],
                    [
                        'nombre' => $responsable['nombre'],
                        'correo' => $responsable['run'] . '@temp.com',
                        'telefono' => '000000000',
                        'tipo_solicitante' => 'estudiante',
                        'activo' => true,
                        'fecha_registro' => now(),
                    ]
                );

                // Crear reserva grupal (formato real de la tabla reservas)
                $reservaNueva = new Reserva();
                $reservaNueva->id_reserva = Reserva::generarIdUnico();
                $reservaNueva->id_espacio = $espacioId;
                $reservaNueva->run_solicitante = $solicitanteResponsable->run_solicitante;
                $reservaNueva->fecha_reserva = $fechaActual;
                $reservaNueva->hora = $horaActual;
                $reservaNueva->hora_salida = $ahora->copy()->addHours(2)->format('H:i:s');
                $reservaNueva->estado = 'activa';
                $reservaNueva->tipo_reserva = 'espontanea';
                $reservaNueva->observaciones = sprintf(
                    'Sala de Estudio | Responsable: %s (%s) | Asistentes: %d',
                    $responsable['nombre'],
                    $responsable['run'],
                    $asistentes->count()
                );
                $reservaNueva->save();

                // Registrar asistencia de todos los escaneados (incluye responsable)
                foreach ($asistentes as $asistente) {
                    // Mantener solicitantes mínimos para trazabilidad de RUN/nombre
                    Solicitante::firstOrCreate(
                        ['run_solicitante' => $asistente['run']],
                        [
                            'nombre' => $asistente['nombre'],
                            'correo' => $asistente['run'] . '@temp.com',
                            'telefono' => '000000000',
                            'tipo_solicitante' => 'estudiante',
                            'activo' => true,
                            'fecha_registro' => now(),
                        ]
                    );

                    Asistencia::create([
                        'id_reserva' => $reservaNueva->id_reserva,
                        'id_espacio' => $espacioId,
                        'rut_asistente' => $asistente['run'],
                        'nombre_asistente' => $asistente['nombre'],
                        'hora_llegada' => $horaActual,
                        'tipo_entrada' => Asistencia::TIPO_ESPONTANEA,
                        'estado' => Asistencia::ESTADO_PRESENTE,
                        'observaciones' => $asistente['run'] === $responsable['run']
                            ? 'Responsable de reserva de sala de estudio'
                            : 'Asistente de sala de estudio',
                    ]);
                }

                // Actualizar estado/cupos del espacio
                $espacio->estado = 'Ocupado';
                if (!is_null($espacio->capacidad_maxima)) {
                    $espacio->puestos_disponibles = max(0, (int) $espacio->capacidad_maxima - $asistentes->count());
                }
                $espacio->save();

                return [
                    'accion' => 'registro',
                    'reserva' => $reservaNueva,
                    'run_responsable' => $responsable['run'] ?? null,
                ];
            });

            $accion = $reserva['accion'] ?? 'registro';
            $reservaResultado = $reserva['reserva'];
            $runResponsable = $reserva['run_responsable'] ?? ($asistentes->first()['run'] ?? null);

            \Log::info('Asistencia registrada en sala de estudio', [
                'espacio_id' => $espacioId,
                'cantidad_asistentes' => $asistentes->count(),
                'responsable' => $runResponsable,
                'reserva_id' => $reservaResultado->id_reserva,
                'accion' => $accion
            ]);

            return response()->json([
                'success' => true,
                'accion' => $accion,
                'message' => $accion === 'devolucion'
                    ? 'Sala de estudio devuelta exitosamente por el responsable'
                    : 'Asistencia registrada exitosamente',
                'reserva_id' => $reservaResultado->id_reserva,
                'cantidad_asistentes' => $asistentes->count(),
                'run_responsable' => $runResponsable
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error al registrar asistencia en sala de estudio: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al registrar asistencia: ' . $e->getMessage()
            ], 500);
        }
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
    private function enviarCorreoReserva(Reserva $reserva): void
    {
        try {
            $reserva->load(['profesor', 'solicitante', 'espacio', 'asignatura']);
            $email = $this->resolverEmailReserva($reserva);
            if ($email) {
                Mail::to($email)->send(new ConfirmacionReserva($reserva));
            }
        } catch (\Exception $e) {
            Log::error('Error al enviar correo de confirmación de reserva: ' . $e->getMessage(), [
                'id_reserva' => $reserva->id_reserva,
            ]);
        }
    }

    /**
     * Envía el correo de confirmación de devolución al profesor o solicitante.
     */
    private function enviarCorreoDevolucion(Reserva $reserva): void
    {
        try {
            $reserva->load(['profesor', 'solicitante', 'espacio', 'asignatura']);
            $email = $this->resolverEmailReserva($reserva);
            if ($email) {
                Mail::to($email)->send(new ConfirmacionDevolucion($reserva));
            }
        } catch (\Exception $e) {
            Log::error('Error al enviar correo de confirmación de devolución: ' . $e->getMessage(), [
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
     * Establece el contexto del tenant desde el request
     */
    private function establecerContextoTenant()
    {
        $tenantId = tenant_id() ?? session('tenant_id') ?? null;

        if (!$tenantId) {
            $host = request()->getHost();
            $tenant = Tenant::where('domain', $host)
                ->orWhere('domain', 'LIKE', '%' . $host . '%')
                ->first();

            if ($tenant) {
                $tenant->makeCurrent();
                Log::info('Tenant establecido por HOST en PlanoDigitalController', [
                    'tenant' => $tenant->name,
                    'database' => $tenant->database,
                    'host' => $host
                ]);
            }
        } elseif (session()->has('tenant_id')) {
            $tenant = Tenant::find(session('tenant_id'));
            if ($tenant) {
                $tenant->makeCurrent();
                Log::info('Tenant establecido por SESION en PlanoDigitalController', [
                    'tenant' => $tenant->name,
                    'database' => $tenant->database
                ]);
            }
        }
    }

    /**
     * Obtener mapa de horarios de módulos (número → inicio/fin)
     * Horarios estándar de la institución.
     */
    private function obtenerMapaHorariosModulos(): array
    {
        return [
            1 => ['inicio' => '08:10:00', 'fin' => '08:55:00'],
            2 => ['inicio' => '08:55:00', 'fin' => '09:40:00'],
            3 => ['inicio' => '09:55:00', 'fin' => '10:40:00'],
            4 => ['inicio' => '10:40:00', 'fin' => '11:25:00'],
            5 => ['inicio' => '11:35:00', 'fin' => '12:20:00'],
            6 => ['inicio' => '12:20:00', 'fin' => '13:05:00'],
            7 => ['inicio' => '13:30:00', 'fin' => '14:15:00'],
            8 => ['inicio' => '14:15:00', 'fin' => '15:00:00'],
            9 => ['inicio' => '15:15:00', 'fin' => '16:00:00'],
            10 => ['inicio' => '16:00:00', 'fin' => '16:45:00'],
            11 => ['inicio' => '16:55:00', 'fin' => '17:40:00'],
            12 => ['inicio' => '17:40:00', 'fin' => '18:25:00'],
            13 => ['inicio' => '18:30:00', 'fin' => '19:15:00'],
            14 => ['inicio' => '19:15:00', 'fin' => '20:00:00'],
        ];
    }
}
