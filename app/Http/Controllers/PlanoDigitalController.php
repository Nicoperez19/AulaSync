<?php

namespace App\Http\Controllers;

use App\Helpers\SemesterHelper;
use App\Mail\ConfirmacionDevolucion;
use App\Mail\ConfirmacionReserva;
use App\Models\Asistencia;
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
use App\Helpers\ModulosHelper;
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
        $sedes = Sede::with(['universidad', 'facultades'])->get();
        $mapasDisponibles = 0;

        foreach ($sedes as $sede) {
            $tenant = Tenant::where('sede_id', $sede->id_sede)->orWhere('domain', strtolower($sede->id_sede))->first();
            if ($tenant) {
                $tenant->makeCurrent();
                $pisos = Piso::with([
                    'mapas' => function ($query) {
                        $query->where('ruta_mapa', '!=', '0')
                              ->whereNotNull('ruta_mapa')
                              ->where('ruta_mapa', '!=', '');
                    }
                ])->get();

                foreach ($sede->facultades as $facultad) {
                    $facultad->setRelation('pisos', $pisos->where('id_facultad', $facultad->id_facultad)->values());
                }

                $mapasTenant = $pisos->flatMap->mapas->count();
                $mapasDisponibles += $mapasTenant;
            }
        }

        $tieneMapas = $mapasDisponibles > 0;

        return view('plano-digital.index', compact('sedes', 'tieneMapas', 'mapasDisponibles'));
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
            $idFacultad = $mapa->piso ? $mapa->piso->id_facultad : null;
            $pisosQuery = Piso::with([
                'mapas' => function ($query) {
                    $query
                        ->where('ruta_mapa', '!=', '0')
                        ->whereNotNull('ruta_mapa')
                        ->where('ruta_mapa', '!=', '');
                }
            ]);
            if ($idFacultad) {
                $pisosQuery->where('id_facultad', $idFacultad);
            }
            $pisos = $pisosQuery->orderBy('numero_piso')->get();

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

            $diaActualNormalizado = ModulosHelper::normalizarDia(Carbon::now()->locale('es')->isoFormat('dddd'));
            $horaActual = Carbon::now()->format('H:i:s');
            $moduloActualNumero = ModulosHelper::obtenerModuloActual($horaActual, $diaActualNormalizado);
            $codigoDia = $this->obtenerCodigoDia($diaActualNormalizado);

            $clasesModuloActual = $this->obtenerClasesModuloActual($moduloActualNumero, $codigoDia);

            return view('plano-digital.show', [
                'mapa' => $mapa,
                'bloques' => $bloques,
                'pisos' => $pisosFormateados,
                'horariosModulos' => ModulosHelper::getHorariosModulos()[$diaActualNormalizado] ?? [],
                'clasesModuloActual' => $clasesModuloActual,
                'moduloActualNumero' => $moduloActualNumero,
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
            $this->establecerContextoTenant();
            $mapa = $this->obtenerMapa($id);
            $estadoActual = $this->obtenerEstadoActual(Carbon::now());
            $bloques = $this->prepararBloques($mapa, $estadoActual);
            $clasesModuloActual = $this->obtenerClasesModuloActual();

            return response()->json([
                'bloques' => $bloques,
                'clasesModuloActual' => $clasesModuloActual,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener los bloques: ' . $e->getMessage()], 500);
        }
    }

    private function obtenerClasesModuloActual(?int $moduloActualNumero = null, ?string $codigoDia = null): array
    {
        try {
            if ($moduloActualNumero === null || $codigoDia === null) {
                $diaActualNormalizado = ModulosHelper::normalizarDia(Carbon::now()->locale('es')->isoFormat('dddd'));
                $horaActual = Carbon::now()->format('H:i:s');
                $moduloActualNumero = ModulosHelper::obtenerModuloActual($horaActual, $diaActualNormalizado);
                $codigoDia = $this->obtenerCodigoDia($diaActualNormalizado);
            }

            if (!$moduloActualNumero || !$codigoDia) {
                return [];
            }

            $idModuloActual = $codigoDia . '.' . $moduloActualNumero;

            $planificaciones = Planificacion_Asignatura::with(['horario.profesor', 'espacio', 'modulo', 'asignatura'])
                ->where('id_modulo', $idModuloActual)
                ->whereHas('horario', function ($query) {
                    $query->where('periodo', SemesterHelper::getCurrentPeriod());
                })
                ->get()
                ->toBase()
                ->map(function ($planificacion) {
                    $codigo = $planificacion->modulo?->id_modulo ? explode('.', $planificacion->modulo->id_modulo)[1] ?? '—' : '—';
                    $nombreDocente = $planificacion->horario?->profesor?->name ?? 'Docente no asignado';
                    return [
                        'codigo' => strtoupper($codigo),
                        'docente' => trim(mb_convert_case($nombreDocente, MB_CASE_TITLE, 'UTF-8')),
                        'asignatura' => trim($planificacion->asignatura?->nombre_asignatura ?? 'Sin asignatura'),
                        'id_espacio' => trim($planificacion->espacio?->id_espacio ?? $planificacion->id_espacio ?? '—'),
                        'sala' => trim($planificacion->espacio?->nombre_espacio ?? $planificacion->espacio?->codigo_espacio ?? 'Sala no asignada'),
                    ];
                });

            $profesoresColaboradores = PlanificacionProfesorColaborador::with(['profesorColaborador.profesor', 'profesorColaborador.asignatura', 'espacio', 'modulo'])
                ->where('id_modulo', $idModuloActual)
                ->whereHas('profesorColaborador', function ($query) {
                    $query->activosYVigentes();
                })
                ->get()
                ->toBase()
                ->map(function ($planificacion) {
                    $codigo = $planificacion->modulo?->id_modulo ? explode('.', $planificacion->modulo->id_modulo)[1] ?? '—' : '—';
                    $nombreDocente = $planificacion->profesorColaborador?->profesor?->name ?? 'Docente no asignado';
                    return [
                        'codigo' => strtoupper($codigo),
                        'docente' => trim(mb_convert_case($nombreDocente, MB_CASE_TITLE, 'UTF-8')),
                        'asignatura' => trim($planificacion->profesorColaborador?->nombre_asignatura ?? $planificacion->profesorColaborador?->asignatura?->nombre_asignatura ?? 'Sin asignatura'),
                        'id_espacio' => trim($planificacion->espacio?->id_espacio ?? $planificacion->id_espacio ?? '—'),
                        'sala' => trim($planificacion->espacio?->nombre_espacio ?? $planificacion->espacio?->codigo_espacio ?? 'Sala no asignada'),
                    ];
                });

            return $planificaciones->concat($profesoresColaboradores)
                ->sortBy(fn ($clase) => [$clase['id_espacio'] ?? 'zz', $clase['docente'] ?? 'zz'])
                ->values()
                ->all();
        } catch (\Throwable $e) {
            \Log::error('Error en obtenerClasesModuloActual: ' . $e->getMessage());
            return [];
        }
    }

    private function obtenerMapa($id)
    {
        $mapa = Mapa::withoutGlobalScopes()
            ->with(['bloques.espacio', 'piso.facultad.sede.universidad'])
            ->where('id_mapa', $id)
            ->first();

        if ($mapa) {
            return $mapa;
        }

        // Si no se encuentra en la conexión actual, buscar en todos los tenants
        foreach (Tenant::all() as $t) {
            $t->makeCurrent();
            $mapa = Mapa::withoutGlobalScopes()
                ->with(['bloques.espacio', 'piso.facultad.sede.universidad'])
                ->where('id_mapa', $id)
                ->first();
            if ($mapa) {
                if (request()->hasSession()) {
                    session(['tenant_id' => $t->id]);
                }
                return $mapa;
            }
        }

        abort(404, "Mapa no encontrado: {$id}");
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
        $diaLimpio = ModulosHelper::normalizarDia($diaActual);
        return match ($diaLimpio) {
            'lunes' => 'LU',
            'martes' => 'MA',
            'miercoles' => 'MI',
            'jueves' => 'JU',
            'viernes' => 'VI',
            'sabado' => 'SA',
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

        $reservasFinalizadasAnticipadamente = Reserva::where('fecha_reserva', $fechaActual)
            ->where('estado', 'finalizada')
            ->where(function ($q) use ($horaActualStr) {
                $q->where('clase_finalizada_anticipadamente', true)
                  ->orWhere(function ($q2) use ($horaActualStr) {
                      $q2->whereNotNull('hora_salida')
                         ->where('hora_salida', '<=', $horaActualStr);
                  });
            })
            ->whereIn('id_espacio', $mapa->bloques->pluck('id_espacio'))
            ->get();

        $espacioIds = $mapa->bloques->pluck('id_espacio')->filter()->unique()->values();

        // [OPTIMIZACIÓN DE RENDIMIENTO] Pre-cargar todas las reservas y clases en UNA sola consulta
        // eliminando el problema N+1 que ralentizaba la actualización del plano digital
        $reservasActivasMap = Reserva::with(['profesor', 'asignatura.carrera', 'solicitante'])
            ->whereIn('id_espacio', $espacioIds)
            ->where('fecha_reserva', $fechaActual)
            ->whereIn('estado', ['activa', 'programada'])
            ->get()
            ->groupBy('id_espacio');

        $clasesNoRealizadasMap = ClaseNoRealizada::with('modulo')
            ->whereIn('id_espacio', $espacioIds)
            ->where('fecha_clase', $fechaActual)
            ->where('estado', 'no_realizada')
            ->get()
            ->groupBy('id_espacio');

        return $mapa->bloques->map(function ($bloque) use (
            $planificacionesActivas, $planificacionesProximas, $reservasProximas,
            $reservasFinalizadasAnticipadamente, $reservasActivasMap, $clasesNoRealizadasMap,
            $mapa, $horaActualStr, $fechaActual, $moduloActual
        ) {
            $idEspacio = $bloque->id_espacio;
            $espacio = $bloque->espacio;

            // Obtener reservas pre-cargadas en memoria sin consultar la BD
            $reservasDelEspacio = $reservasActivasMap->get($idEspacio, collect());
            $reservaActiva = $reservasDelEspacio->firstWhere('estado', 'activa');
            $reservaProgramada = null;

            if (!$reservaActiva) {
                $reservaProgramada = $reservasDelEspacio->firstWhere('estado', 'programada');

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

            // Obtener clases no realizadas pre-cargadas en memoria
            $clasesNoRealizadasHoy = $clasesNoRealizadasMap->get($idEspacio, collect());
            $claseSinAsistentesActiva = false;
            foreach ($clasesNoRealizadasHoy as $clase) {
                if ($clase->modulo && $clase->modulo->hora_termino > $horaActualStr) {
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

                // Filtrar planificaciones si la clase de ESTE módulo fue finalizada anticipadamente o el profesor ya registró salida
                $coincideReservaConPlan = function ($r, $plan) {
                    $numModPlan = ModulosHelper::getNumeroModulo($plan->id_modulo ?? null);
                    if ($numModPlan !== null && $r->modulo_inicio !== null && $r->modulo_fin !== null) {
                        if ($numModPlan < (int) $r->modulo_inicio || $numModPlan > (int) $r->modulo_fin) {
                            return false;
                        }
                    } elseif ($plan->modulo && $r->hora_salida && $r->hora_salida <= $plan->modulo->hora_inicio) {
                        return false;
                    }

                    if ($r->id_asignatura && !empty($plan->id_asignatura) && $r->id_asignatura == $plan->id_asignatura) {
                        return true;
                    }
                    $runDocente = $plan->horario?->run_profesor ?? $plan->asignatura?->run_profesor ?? $plan->profesorColaborador?->run_profesor_colaborador ?? null;
                    if ($runDocente && $r->run_profesor && $this->normalizeRun($runDocente) === $this->normalizeRun($r->run_profesor)) {
                        return true;
                    }
                    return false;
                };

                if ($planificacionActiva) {
                    $claseFinalizada = $reservasFinalizadasAnticipadamente->where('id_espacio', $idEspacio)
                        ->first(fn ($r) => $coincideReservaConPlan($r, $planificacionActiva));
                    if ($claseFinalizada) {
                        $planificacionActiva = null;
                    }
                }
                if ($planificacionProxima) {
                    $claseFinalizada = $reservasFinalizadasAnticipadamente->where('id_espacio', $idEspacio)
                        ->first(fn ($r) => $coincideReservaConPlan($r, $planificacionProxima));
                    if ($claseFinalizada) {
                        $planificacionProxima = null;
                    }
                }

                // Verificar que la planificación próxima realmente esté en rango (validación EXTRA agresiva)
                if ($planificacionProxima && isset($planificacionProxima->modulo->hora_inicio)) {
                    // Calcular diferencia de minutos
                    $horaActualCarbon = Carbon::createFromFormat('H:i:s', $horaActualStr);
                    $horaProximaCarbon = Carbon::createFromFormat('H:i:s', $planificacionProxima->modulo->hora_inicio);
                    $diferencia = $horaProximaCarbon->diffInMinutes($horaActualCarbon, false);

                    // Si la diferencia es > 10 min, NO es próxima, es futura

                }

                if ($planificacionActiva) {
                    // Hay clase en curso = Reservado (naranja)
                    $estadoFinal = 'Reservado';
                } elseif ($planificacionProxima || $reservaProxima) {
                    // Solo hay clase próxima (próximos 10 min) = Reservado (naranja)
                    $estadoFinal = 'Reservado';
                } else {
                    // 4. No hay actividad = Disponible
                    $estadoFinal = 'Disponible';

                    // Corregir BD si está marcado como Ocupado sin actividad
                        $espacio->estado = 'Disponible';
                        $espacio->save();
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
        $diaLimpio = ModulosHelper::normalizarDia($estadoActual['dia']);
        $moduloNumero = ModulosHelper::obtenerModuloActual($estadoActual['hora'], $diaLimpio);

        $codigoDia = $this->obtenerCodigoDia($estadoActual['dia']);
        if ($codigoDia && $moduloNumero) {
            return Modulo::where('id_modulo', $codigoDia . '.' . $moduloNumero)->first();
        }

        return null;
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

        // Obtener planificaciones de profesores colaboradores (solo vigentes en el semestre activo)
        $planificacionesTemporales = PlanificacionProfesorColaborador::with(['profesorColaborador', 'modulo', 'espacio'])
            ->where('id_modulo', $moduloActual->id_modulo)
            ->whereHas('profesorColaborador', function ($query) {
                $query->activosYVigentes();
            })
            ->whereHas('modulo', function ($query) use ($horaActual) {
                $query->where('hora_termino', '>=', $horaActual);  // Solo módulos que no han terminado
            })
            ->whereHas('espacio', function ($query) use ($mapa) {
                $query->whereIn('id_espacio', $mapa->bloques->pluck('id_espacio'));
            })
            ->get();

        return $planificacionesRegulares->concat($planificacionesTemporales);
    }

    private function obtenerPlanificacionesProximas(Mapa $mapa, array $estadoActual)
    {
        $horaActual = Carbon::parse($estadoActual['hora']);
        $diaActual = $estadoActual['dia'];
        $diaLimpio = ModulosHelper::normalizarDia($diaActual);
        $diasPosibles = array_values(array_unique([$diaActual, $diaLimpio]));

        $periodo = SemesterHelper::getCurrentPeriod();

        // Verificar si estamos en el rango especial de 05:00 a 05:10
        $horaActualStr = $horaActual->format('H:i:s');
        $esRangoEspecial = ($horaActualStr >= '05:00:00' && $horaActualStr <= '05:10:00');

        if ($esRangoEspecial) {
            // Para el rango 05:00-05:10, buscar clases que empiecen a las 05:10
            $horaInicioBusqueda = '05:00:00';
            $horaFinBusqueda = '05:10:00';
        } else {
            // Para otros horarios, usar la lógica normal (15 minutos después)
            $horaInicioBusqueda = $horaActual->format('H:i:s');
            $horaFinBusqueda = $horaActual->copy()->addMinutes(15)->format('H:i:s');
        }

        // Obtener planificaciones regulares próximas
        $planificacionesRegularesProximas = Planificacion_Asignatura::with(['horario.profesor', 'asignatura.profesor', 'modulo', 'espacio'])

            ->whereHas('horario', function ($query) use ($periodo) {
                $query->where('periodo', $periodo);
            })
            ->whereHas('modulo', function ($query) use ($horaInicioBusqueda, $horaFinBusqueda, $diasPosibles, $esRangoEspecial) {
                $query->whereIn('dia', $diasPosibles);

                if ($esRangoEspecial) {
                    // Para el rango 05:00-05:10, buscar módulos que empiecen a las 05:10
                    $query->where('hora_inicio', '=', '05:10:00');
                } else {
                    // Para otros horarios, buscar módulos que empiecen en los próximos 15 minutos
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

        // Obtener planificaciones de profesores colaboradores próximas (solo vigentes en el semestre activo)
        $planificacionesTemporalesProximas = PlanificacionProfesorColaborador::with(['profesorColaborador', 'modulo', 'espacio'])
            ->whereHas('profesorColaborador', function ($query) {
                $query->activosYVigentes();
            })
            ->whereHas('modulo', function ($query) use ($horaInicioBusqueda, $horaFinBusqueda, $diasPosibles, $esRangoEspecial) {
                $query->whereIn('dia', $diasPosibles);

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

        return $planificacionesRegularesProximas->concat($planificacionesTemporalesProximas);
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
        } elseif ($planificacion) {
            if ($planificacion instanceof PlanificacionProfesorColaborador) {
                $asigColab = $planificacion->profesorColaborador?->asignatura;
                $detalles['planificacion'] = [
                    'asignatura' => $planificacion->profesorColaborador->nombre_asignatura ?? 'Sin asignatura',
                    'codigo_asignatura' => $asigColab->codigo_asignatura ?? '-',
                    'profesor' => ucwords($planificacion->profesorColaborador->profesor->name ?? 'No asignado'),
                    'carrera' => $asigColab->carrera->nombre ?? '-',
                    'es_reserva_activa' => false,
                    'modulos' => [
                        [
                            'dia' => $planificacion->modulo->dia ?? 'No especificado',
                            'hora_inicio' => $planificacion->modulo->hora_inicio ?? '00:00:00',
                            'hora_termino' => $planificacion->modulo->hora_termino ?? '00:00:00'
                        ]
                    ]
                ];
            } elseif ($planificacion->asignatura) {
                // PRIORIDAD 2: Si NO hay reserva, mostrar la planificación del espacio
                $detalles['planificacion'] = [
                    'asignatura' => $planificacion->asignatura->nombre_asignatura ?? 'Sin asignatura',
                    'codigo_asignatura' => $planificacion->asignatura->codigo_asignatura ?? '-',
                    'profesor' => ucwords($planificacion->horario?->profesor?->name ?? $planificacion->asignatura?->profesor?->name ?? 'No asignado'),
                    'carrera' => $planificacion->asignatura?->carrera?->nombre ?? '-',
                    'es_reserva_activa' => false,
                    'modulos' => $planificacion->modulo ? [
                        [
                            'dia' => $planificacion->modulo->dia ?? 'No especificado',
                            'hora_inicio' => $planificacion->modulo->hora_inicio ?? '00:00:00',
                            'hora_termino' => $planificacion->modulo->hora_termino ?? '00:00:00'
                        ]
                    ] : []
                ];
            }
        }

        if ($planificacionProxima) {
            if ($planificacionProxima instanceof PlanificacionProfesorColaborador) {
                $detalles['planificacion_proxima'] = [
                    'asignatura' => $planificacionProxima->profesorColaborador?->nombre_asignatura ?? 'Sin asignatura',
                    'profesor' => ucwords($planificacionProxima->profesorColaborador?->profesor?->name ?? 'No asignado'),
                    'hora_inicio' => substr($planificacionProxima->modulo?->hora_inicio ?? '00:00', 0, 5),
                    'hora_termino' => substr($planificacionProxima->modulo?->hora_termino ?? '00:00', 0, 5),
                    'modulo' => explode('.', $planificacionProxima->modulo?->id_modulo ?? '')[1] ?? 'No especificado'
                ];
            } elseif ($planificacionProxima->asignatura) {
                $detalles['planificacion_proxima'] = [
                    'asignatura' => $planificacionProxima->asignatura->nombre_asignatura ?? 'Sin asignatura',
                    'profesor' => ucwords($planificacionProxima->horario?->profesor?->name ?? $planificacionProxima->asignatura?->profesor?->name ?? 'No asignado'),
                    'hora_inicio' => substr($planificacionProxima->modulo?->hora_inicio ?? '00:00', 0, 5),
                    'hora_termino' => substr($planificacionProxima->modulo?->hora_termino ?? '00:00', 0, 5),
                    'modulo' => explode('.', $planificacionProxima->modulo?->id_modulo ?? '')[1] ?? 'No especificado'
                ];
            }
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

            $colaboradoresActivosYProximos = PlanificacionProfesorColaborador::with(['modulo', 'espacio', 'profesorColaborador.profesor', 'profesorColaborador.asignatura'])
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
                ->whereHas('profesorColaborador', fn($q) => $q->where('estado', 'activo')
                    ->where('fecha_inicio', '<=', $fechaActual)
                    ->where('fecha_termino', '>=', $fechaActual))
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
                ->where('estado', 'no_realizada')
                ->get()
                ->groupBy('id_espacio');

            $reservasFinalizadasAnticipadamente = Reserva::where('fecha_reserva', $fechaActual)
                ->where('estado', 'finalizada')
                ->where(function ($q) use ($horaActualStr) {
                    $q->where('clase_finalizada_anticipadamente', true)
                      ->orWhere(function ($q2) use ($horaActualStr) {
                          $q2->whereNotNull('hora_salida')
                             ->where('hora_salida', '<=', $horaActualStr);
                      });
                })
                ->get();

            return [
                'success' => true,
                'espacios' => $espacios->map(function ($espacio) use ($horaActual, $horaActualStr, $diaActual, $planificacionesActivasYProximas, $colaboradoresActivosYProximos, $reservasActivas, $reservasProgramadas, $reservasProximas, $moduloActual, $horaLimite, $clasesNoRealizadasGlobal, $reservasFinalizadasAnticipadamente) {

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

                $coincideReservaEstado = function ($r, $p, $esColab = false) {
                    $numModPlan = ModulosHelper::getNumeroModulo($p->id_modulo ?? null);
                    if ($numModPlan !== null && $r->modulo_inicio !== null && $r->modulo_fin !== null) {
                        if ($numModPlan < (int) $r->modulo_inicio || $numModPlan > (int) $r->modulo_fin) {
                            return false;
                        }
                    } elseif ($p->modulo && $r->hora_salida && $r->hora_salida <= $p->modulo->hora_inicio) {
                        return false;
                    }

                    $idAsigPlan = $esColab ? ($p->profesorColaborador?->id_asignatura ?? null) : ($p->id_asignatura ?? null);
                    if ($r->id_asignatura && $idAsigPlan && $r->id_asignatura == $idAsigPlan) {
                        return true;
                    }
                    $runDocente = $esColab
                        ? ($p->profesorColaborador?->run_profesor_colaborador ?? $p->profesorColaborador?->run_profesor ?? null)
                        : ($p->horario?->run_profesor ?? $p->asignatura?->run_profesor ?? null);
                    if ($runDocente && $r->run_profesor && $this->normalizeRun($runDocente) === $this->normalizeRun($r->run_profesor)) {
                        return true;
                    }
                    return false;
                };

                $claseEnCurso = $planificacionesActivasYProximas
                    ->where('id_espacio', $espacio->id_espacio)
                    ->filter(function ($p) use ($horaActualStr, $reservasFinalizadasAnticipadamente, $espacio, $coincideReservaEstado) {
                        if ($p->modulo && $p->modulo->hora_inicio <= $horaActualStr && $p->modulo->hora_termino > $horaActualStr) {
                            $claseFinalizada = $reservasFinalizadasAnticipadamente->where('id_espacio', $espacio->id_espacio)
                                ->first(fn ($r) => $coincideReservaEstado($r, $p, false));
                            return !$claseFinalizada;
                        }
                        return false;
                    })->first();

                $colaboradorEnCurso = $colaboradoresActivosYProximos
                    ->where('id_espacio', $espacio->id_espacio)
                    ->filter(function ($p) use ($horaActualStr, $reservasFinalizadasAnticipadamente, $espacio, $coincideReservaEstado) {
                        if ($p->modulo && $p->modulo->hora_inicio <= $horaActualStr && $p->modulo->hora_termino > $horaActualStr) {
                            $claseFinalizada = $reservasFinalizadasAnticipadamente->where('id_espacio', $espacio->id_espacio)
                                ->first(fn ($r) => $coincideReservaEstado($r, $p, true));
                            return !$claseFinalizada;
                        }
                        return false;
                    })->first();

                $claseProxima = $planificacionesActivasYProximas
                    ->where('id_espacio', $espacio->id_espacio)
                    ->filter(function ($p) use ($horaActualStr, $horaLimite, $reservasFinalizadasAnticipadamente, $espacio, $coincideReservaEstado) {
                        if ($p->modulo && $p->modulo->hora_inicio > $horaActualStr && $p->modulo->hora_inicio <= $horaLimite) {
                            $claseFinalizada = $reservasFinalizadasAnticipadamente->where('id_espacio', $espacio->id_espacio)
                                ->first(fn ($r) => $coincideReservaEstado($r, $p, false));
                            return !$claseFinalizada;
                        }
                        return false;
                    })->first();

                $colaboradorProxima = $colaboradoresActivosYProximos
                    ->where('id_espacio', $espacio->id_espacio)
                    ->filter(function ($p) use ($horaActualStr, $horaLimite, $reservasFinalizadasAnticipadamente, $espacio, $coincideReservaEstado) {
                        if ($p->modulo && $p->modulo->hora_inicio > $horaActualStr && $p->modulo->hora_inicio <= $horaLimite) {
                            $claseFinalizada = $reservasFinalizadasAnticipadamente->where('id_espacio', $espacio->id_espacio)
                                ->first(fn ($r) => $coincideReservaEstado($r, $p, true));
                            return !$claseFinalizada;
                        }
                        return false;
                    })->first();

                $tieneClaseEnCurso = ($claseEnCurso !== null) || ($colaboradorEnCurso !== null);
                $tieneClaseProxima = ($claseProxima !== null) || ($colaboradorProxima !== null);

                if ($estadoTabla === 'Mantención' || $estadoTabla === 'Mantenimiento') {
                    $estado = 'Mantención';
                } elseif ($tieneReservaActiva) {
                    $reservaActiva = $reservasActivas->where('id_espacio', $espacio->id_espacio)->first();
                    if ($reservaActiva && $reservaActiva->tipo_reserva === 'espontanea') {
                        $estado = 'Reserva Espontánea';
                    } else {
                        $estado = 'Clase registrada';
                    }
                } elseif ($tieneClaseSinAsistentes) {
                    $estado = 'Clase no registrada';
                } elseif ($estadoTabla === 'Ocupado' && $tieneReservaActiva) {
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
                if ($tieneClaseEnCurso) {
                    $curso = $claseEnCurso ?? $colaboradorEnCurso;
                    if ($curso) {
                        $asignaturaNombre = ($curso instanceof Planificacion_Asignatura)
                            ? ($curso->asignatura?->nombre_asignatura ?? 'Sin asignatura')
                            : ($curso->profesorColaborador?->nombre_asignatura ?? 'Sin asignatura');
                        $profesorNombre = ($curso instanceof Planificacion_Asignatura)
                            ? ($curso->horario?->profesor?->name ?? $curso->asignatura?->profesor?->name ?? 'No especificado')
                            : ($curso->profesorColaborador?->profesor?->name ?? 'No especificado');

                        $informacionAdicional = [
                            'asignatura' => $asignaturaNombre,
                            'profesor' => $profesorNombre,
                            'modulo' => explode('.', $curso->modulo?->id_modulo ?? '')[1] ?? 'No especificado',
                            'hora_inicio' => substr($curso->modulo?->hora_inicio ?? '00:00', 0, 5),
                            'hora_termino' => substr($curso->modulo?->hora_termino ?? '00:00', 0, 5)
                        ];
                    }
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
     * Alias compatible para la ruta /api/devolver-llaves
     */
    public function devolverLlaves(Request $request)
    {
        return $this->devolverEspacio($request);
    }

    /**
     * Devolver un espacio ocupado
     */
    public function devolverEspacio(Request $request)
    {
        try {
            // Establecer contexto del tenant desde el request
            $this->establecerContextoTenant();

            if (!$request->has('run_usuario') && $request->has('run')) {
                $request->merge(['run_usuario' => $request->input('run')]);
            }

            $request->validate([
                'id_espacio' => 'required|string',
                'run_usuario' => 'required',
                'tipo_desocupacion' => 'sometimes|string|in:normal,forzosa',
                'run_administrador' => 'required_if:tipo_desocupacion,forzosa'
            ]);

            $runUsuario = $request->input('run_usuario');
            $tipoDesocupacion = $request->input('tipo_desocupacion', 'normal');
            $runAdministrador = $request->input('run_administrador');

            // Buscar el espacio soportando alias y scopes de tenant
            $espacio = \App\Helpers\EspacioAliasHelper::buscarEspacio($request->input('id_espacio'));
            if (!$espacio) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Espacio no encontrado'
                ], 404);
            }

            $idEspacio = $espacio->id_espacio;
            $espaciosEquiv = \App\Helpers\EspacioAliasHelper::obtenerEquivalentes($idEspacio);

            // Normalizar RUNs del usuario (incluyendo formato exacto de DB si existe profesor o solicitante)
            $runUsuarioRaw = (string) $runUsuario;
            $runLimpio = strtoupper(preg_replace('/[^0-9Kk]/', '', $runUsuarioRaw));
            $runSinDv = strlen($runLimpio) > 7 ? substr($runLimpio, 0, -1) : $runLimpio;
            $profesorDoc = Profesor::buscarProfesor($runUsuarioRaw);
            $solicitanteDoc = Solicitante::buscarSolicitante($runUsuarioRaw);
            $runsUsuario = array_values(array_unique(array_filter([
                $profesorDoc ? $profesorDoc->run_profesor : null,
                $solicitanteDoc ? $solicitanteDoc->run_solicitante : null,
                $runUsuarioRaw,
                $runLimpio,
                $runSinDv
            ])));

            // Verificar si el usuario tiene una reserva activa en este espacio (usando búsqueda indexada rápida)
            $reservaActiva = null;

            if (strpos($runUsuario, 'FORCE_') === 0) {
                $reservaActiva = Reserva::whereIn('id_espacio', $espaciosEquiv)
                    ->where('estado', 'activa')
                    ->latest('updated_at')
                    ->first();
            } else {
                $reservaActiva = Reserva::where(function ($query) use ($runsUsuario) {
                    $query->whereIn('run_profesor', $runsUsuario)
                          ->orWhereIn('run_solicitante', $runsUsuario);
                })
                    ->whereIn('id_espacio', $espaciosEquiv)
                    ->where('estado', 'activa')
                    ->latest('updated_at')
                    ->first();
            }

            // Si no hay reserva activa para el usuario, verificar si tiene una reserva de HOY que ya fue finalizada
            if (!$reservaActiva) {
                $reservaHoy = Reserva::where(function ($query) use ($runsUsuario) {
                    $query->whereIn('run_profesor', $runsUsuario)
                          ->orWhereIn('run_solicitante', $runsUsuario);
                })
                    ->whereIn('id_espacio', $espaciosEquiv)
                    ->where('fecha_reserva', now()->toDateString())
                    ->latest('updated_at')
                    ->first();

                if (!$reservaHoy && ($runLimpio || $runSinDv)) {
                    $reservaHoy = Reserva::where(function ($query) use ($runLimpio, $runSinDv) {
                        $query->whereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runLimpio])
                              ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_solicitante, '.', ''), '-', ''), ' ', '') = ?", [$runLimpio]);
                        if ($runSinDv) {
                            $query->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runSinDv])
                                  ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_solicitante, '.', ''), '-', ''), ' ', '') = ?", [$runSinDv]);
                        }
                    })
                        ->whereIn('id_espacio', $espaciosEquiv)
                        ->where('fecha_reserva', now()->toDateString())
                        ->latest('updated_at')
                        ->first();
                }

                // Asegurar siempre que el espacio quede en Disponible
                $espacio->estado = 'Disponible';
                $espacio->save();

                if ($reservaHoy && !str_contains($reservaHoy->observaciones ?? '', 'Devolución de llave')) {
                    $reservaHoy->observaciones = trim(($reservaHoy->observaciones ?? '') . "\nDevolución de llave registrada a las " . now()->format('H:i:s'));
                    $reservaHoy->save();
                }

                $this->limpiarCacheEstadosEspacios();

                return response()->json([
                    'success' => true,
                    'mensaje' => 'Devolución de llave registrada exitosamente. El espacio se encuentra disponible.',
                    'espacio' => [
                        'id' => $espacio->id_espacio,
                        'nombre' => $espacio->nombre_espacio,
                        'estado' => 'Disponible'
                    ]
                ]);
            }

            // Protección de 10 segundos contra rebotes involuntarios del lector de código de barras
            $segundosDesdeActivacion = $reservaActiva->updated_at ? $reservaActiva->updated_at->diffInSeconds(now()) : 999;
            if ($tipoDesocupacion !== 'forzosa' && $segundosDesdeActivacion < 10) {
                return response()->json([
                    'success' => true,
                    'devolucion_bloqueada' => true,
                    'mensaje' => 'Tu clase/sección en esta sala ya se encuentra activa y registrada exitosamente.',
                    'espacio' => [
                        'id' => $espacio->id_espacio,
                        'nombre' => $espacio->nombre_espacio,
                        'estado' => $espacio->estado
                    ]
                ]);
            }

            // NUEVA LÓGICA: Verificar si es un profesor devolviendo durante el primer módulo de una clase
            $devolucionPrimerModulo = false;
            $infoClase = null;

            if ($reservaActiva->run_profesor) {
                // Obtener hora actual y día
                $horaActual = now()->format('H:i:s');
                $diaActual = strtolower(now()->locale('es')->isoFormat('dddd'));

                // Buscar planificaciones del profesor en este espacio para hoy en el período actual
                $periodoActual = SemesterHelper::getCurrentPeriod();
                $planificaciones = Planificacion_Asignatura::with(['asignatura', 'modulo'])
                    ->where('id_espacio', $idEspacio)
                    ->whereHas('horario', function ($query) use ($periodoActual) {
                        $query->where('periodo', $periodoActual);
                    })
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
                $horaActualSalida = now()->format('H:i:s');

                // Si estamos en medio de un bloque multi-módulo (y no es forzosa),
                // NO sobreescribir hora_salida programada para no invalidar módulos siguientes.
                // Registrar la salida real en observaciones y marcar anticipadamente.
                $esSalidaEnBloqueMulti = $devolucionPrimerModulo
                    && $tipoDesocupacion !== 'forzosa'
                    && !empty($reservaActiva->hora_salida)
                    && $horaActualSalida < $reservaActiva->hora_salida;

                if ($esSalidaEnBloqueMulti) {
                    // Preservar hora_salida programada: la evaluación de retiro anticipado
                    // se hará al comparar observaciones vs planificación real del profesor.
                    $reservaActiva->clase_finalizada_anticipadamente = true;
                    $reservaActiva->observaciones = trim(
                        ($reservaActiva->observaciones ?? '') .
                        "\nSalida anticipada registrada a las {$horaActualSalida} (bloque multi-módulo, hora_salida programada conservada)"
                    );
                } else {
                    // Fuera del bloque o desocupación forzosa: actualizar hora_salida normalmente
                    $reservaActiva->hora_salida = $horaActualSalida;
                    $reservaActiva->clase_finalizada_anticipadamente = true;

                    // Si es una desocupación forzosa, agregar información adicional
                    if ($tipoDesocupacion === 'forzosa') {
                        $reservaActiva->observaciones = ($reservaActiva->observaciones ?? '')
                            . "; DESOCUPACIÓN FORZOSA por administrador RUN: {$runAdministrador} el " . now()->format('Y-m-d H:i:s');
                    }

                    $reservaActiva->observaciones = trim(($reservaActiva->observaciones ?? '') . "\nFinalizada por el usuario a las " . $horaActualSalida);
                }

                $reservaActiva->estado = 'finalizada';
                $reservaActiva->save();

                // Enviar correo de confirmación de devolución
                $this->enviarCorreoDevolucion($reservaActiva);
            }

            // Cambiar el estado del espacio a disponible
            $espacio->estado = 'Disponible';
            $espacio->save();

            // Registrar la devolución en un log
            $mensajeLog = $tipoDesocupacion === 'forzosa'
                ? "Espacio {$idEspacio} FORZOSAMENTE devuelto por administrador {$runAdministrador} - Usuario ocupante: {$runUsuario} - Reserva ID: {$reservaActiva->id_reserva}"
                : "Espacio {$idEspacio} devuelto exitosamente por usuario {$runUsuario} - Reserva ID: {$reservaActiva->id_reserva}";



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
            }

            // [OPTIMIZACIÓN] Invalidar caché del plano y del espacio individual
            $this->limpiarCacheEstadosEspacios();
            \Illuminate\Support\Facades\Cache::forget("espacio_info_{$idEspacio}");
            \Illuminate\Support\Facades\Cache::forget("espacio_info_{$idEspacio}_time");

            return response()->json($respuesta);
        } catch (\Exception $e) {
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
                            'monday' => 'lunes',
                            'tuesday' => 'martes',
                            'wednesday' => 'miércoles',
                            'thursday' => 'jueves',
                            'friday' => 'viernes',
                            'saturday' => 'sábado',
                            'sunday' => 'domingo'
                        ];
                        $diaEng = strtolower(now()->englishDayOfWeek);
                        $diaNormalizado = $diasMapa[$diaEng] ?? $dia;
                        $codigoDia = $this->obtenerCodigoDia($diaNormalizado);

                        $idModulo = ($codigoDia ?? $diaNormalizado) . '.' . $reserva->modulo_inicio;
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

                    }
                } catch (\Exception $e) {
                }
            }

            return response()->json([
                'success' => true,
                'mensaje' => 'Asistencia registrada correctamente',
                'hubo_asistentes' => $huboAsistentes
            ]);
        } catch (\Exception $e) {
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
                    'run_usuario' => 'required',
                    'id_espacio' => 'required|string',
                    'id_reserva_anterior' => 'required|string'
                ]);

                $runRawNuevo = (string) $request->input('run_usuario');
                $runNuevo = $this->normalizeRun($runRawNuevo);
                $runLimpioNuevo = strtoupper(preg_replace('/[^0-9Kk]/', '', $runRawNuevo));
                $runSinDvNuevo = strlen($runLimpioNuevo) > 7 ? substr($runLimpioNuevo, 0, -1) : $runNuevo;
                $idEspacio = $this->normalizeEspacioId($request->input('id_espacio'));
                $idReservaAnterior = $request->input('id_reserva_anterior');

                // 1. Obtener datos de los involucrados
                $docenteNuevo = Profesor::buscarProfesor($runRawNuevo);
                if (!$docenteNuevo) {
                    $docenteNuevo = Profesor::withoutGlobalScopes()->where(function ($q) use ($runNuevo, $runLimpioNuevo, $runSinDvNuevo) {
                        $q->where('run_profesor', $runNuevo)
                          ->orWhere('run_profesor', $runLimpioNuevo)
                          ->orWhere('run_profesor', $runSinDvNuevo)
                          ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runNuevo])
                          ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runLimpioNuevo])
                          ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runSinDvNuevo]);
                    })->first();
                }
                if (!$docenteNuevo) {
                    return response()->json(['success' => false, 'mensaje' => 'Docente nuevo no encontrado (' . $runNuevo . ')'], 404);
                }
                $runNuevo = $docenteNuevo->run_profesor;

                $espacioObj = \App\Helpers\EspacioAliasHelper::buscarEspacio($idEspacio);
                if ($espacioObj) {
                    $idEspacio = $espacioObj->id_espacio;
                }
                $espaciosEquiv = \App\Helpers\EspacioAliasHelper::obtenerEquivalentes($idEspacio);

                $reservaAnterior = Reserva::with(['profesor', 'solicitante'])->find($idReservaAnterior);
                if (!$reservaAnterior || (!in_array($reservaAnterior->id_espacio, $espaciosEquiv) && $reservaAnterior->id_espacio !== $idEspacio)) {
                    return response()->json(['success' => false, 'mensaje' => 'Reserva anterior no válida'], 400);
                }

                $nombreAnterior = $reservaAnterior->profesor ? $reservaAnterior->profesor->name : ($reservaAnterior->solicitante ? $reservaAnterior->solicitante->nombre : 'Usuario desconocido');
                $runAnterior = $reservaAnterior->run_profesor ?? $reservaAnterior->run_solicitante;

                // 2. Finalizar/cancelar reserva anterior
                $reservaAnterior->hora_salida = now()->format('H:i:s');
                $accionDesc = $reservaAnterior->estado === 'programada' ? 'RESERVA PROGRAMADA CANCELADA' : 'CIERRE FORZADO';
                $reservaAnterior->estado = 'finalizada';
                $reservaAnterior->observaciones = ($reservaAnterior->observaciones ?? '')
                    . "; {$accionDesc} por el docente titular: {$docenteNuevo->name} ({$runNuevo}) el " . now()->format('Y-m-d H:i:s');
                $reservaAnterior->save();

                // 3. Crear nueva reserva para el docente actual
                // Primero verificar si tiene planificación o reserva programada
                $horaActual = now();
                $diaActual = strtolower($horaActual->locale('es')->isoFormat('dddd'));
                $horaActualStr = $horaActual->format('H:i:s');

                $periodoActual = SemesterHelper::getCurrentPeriod();
                $planificacion = Planificacion_Asignatura::where('id_espacio', $idEspacio)
                    ->whereHas('horario', function ($q) use ($periodoActual) {
                        $q->where('periodo', $periodoActual);
                    })
                    ->whereHas('asignatura', function ($q) use ($runNuevo) {
                        $q->where('run_profesor', $runNuevo);
                    })
                    ->whereHas('modulo', function ($q) use ($diaActual, $horaActualStr, $horaActual) {
                        $q
                            ->where('dia', $diaActual)
                            ->where('hora_inicio', '<=', $horaActual->copy()->addMinutes(15)->toTimeString())
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
                    $reservaProgramada->tipo_reserva = 'clase';
                    $reservaProgramada->observaciones = ($reservaProgramada->observaciones ?? '')
                        . "; Sesión iniciada forzosamente; el docente anterior ({$nombreAnterior} - {$runAnterior}) no liberó el espacio";
                    $reservaProgramada->save();
                    $nuevaReservaId = $reservaProgramada->id_reserva;
                } else {
                    // Crear nueva reserva (como registrarAsistenciaProfesor)
                    $nuevaReserva = new Reserva();
                    $nuevaReserva->id_reserva = Reserva::generarIdUnico();
                    $nuevaReserva->run_profesor = $runNuevo;
                    $nuevaReserva->id_espacio = $idEspacio;
                    $nuevaReserva->fecha_reserva = Carbon::today()->toDateString();
                    $nuevaReserva->hora = $horaActualStr;
                    $nuevaReserva->estado = 'activa';
                    $nuevaReserva->tipo_reserva = 'clase';

                    if ($planificacion) {
                        $nuevaReserva->id_asignatura = $planificacion->id_asignatura;
                        
                        // Encontrar bloques consecutivos de planificación del mismo docente en este espacio
                        $periodo = SemesterHelper::getCurrentPeriod();
                        $espacioLimpioForzado = str_replace(['-', ' '], '', $idEspacio);
                        $planificacionesMismoBloque = Planificacion_Asignatura::with('modulo')
                            ->where(function ($eq) use ($idEspacio, $espacioLimpioForzado) {
                                $eq->where('id_espacio', $idEspacio)
                                   ->orWhereRaw("REPLACE(REPLACE(id_espacio, '-', ''), ' ', '') = ?", [$espacioLimpioForzado]);
                            })
                            ->where('id_asignatura', $planificacion->id_asignatura)
                            ->whereHas('horario', function ($q) use ($periodo) {
                                $q->where('periodo', $periodo);
                            })
                            ->whereHas('modulo', function ($q) use ($diaActual) {
                                $q->where('dia', $diaActual);
                            })
                            ->get();

                        $numModuloActual = ModulosHelper::getNumeroModulo($planificacion->id_modulo);
                        $moduloFin = $numModuloActual;
                        
                        $modulosAsociados = [];
                        foreach ($planificacionesMismoBloque as $planItem) {
                            if ($planItem->modulo) {
                                $num = ModulosHelper::getNumeroModulo($planItem->id_modulo);
                                $modulosAsociados[$num] = $planItem->modulo;
                            }
                        }
                        
                        $cantidadModulos = 1;
                        while (isset($modulosAsociados[$moduloFin + 1])) {
                            $moduloFin++;
                            $cantidadModulos++;
                        }
                        
                        $moduloFinalObj = $modulosAsociados[$moduloFin] ?? $planificacion->modulo;
                        $nuevaReserva->modulo_inicio = $numModuloActual;
                        $nuevaReserva->modulo_fin = $moduloFin;
                        $nuevaReserva->hora_salida = $moduloFinalObj->hora_termino ?? null;
                        $nuevaReserva->modulos = $cantidadModulos;
                    } else {
                        // Buscar en PlanificacionProfesorColaborador
                        $hoyColab = Carbon::today()->toDateString();
                        $espacioLimpioForzado = str_replace(['-', ' '], '', $idEspacio);
                        $planColaborador = PlanificacionProfesorColaborador::whereRaw("REPLACE(REPLACE(id_espacio, '-', ''), ' ', '') = ?", [$espacioLimpioForzado])
                            ->whereHas('profesorColaborador', function ($q) use ($runNuevo, $hoyColab) {
                                $q->where('estado', 'activo')
                                  ->where('fecha_inicio', '<=', $hoyColab)
                                  ->where('fecha_termino', '>=', $hoyColab)
                                  ->whereRaw("REPLACE(REPLACE(REPLACE(run_profesor_colaborador, '.', ''), '-', ''), ' ', '') = ?", [$runNuevo]);
                            })
                            ->whereHas('modulo', function ($q) use ($diaActual, $horaActualStr, $horaActual) {
                                $q
                                    ->where('dia', $diaActual)
                                    ->where('hora_inicio', '<=', $horaActual->copy()->addMinutes(15)->toTimeString())
                                    ->where('hora_termino', '>=', $horaActualStr);
                            })
                            ->first();

                        if ($planColaborador) {
                            $nuevaReserva->id_asignatura = $planColaborador->profesorColaborador->id_asignatura ?? null;
                            $idColab = $planColaborador->id_profesor_colaborador;
                            $planificacionesMismoBloque = PlanificacionProfesorColaborador::with('modulo')
                                ->where(function ($eq) use ($idEspacio, $espacioLimpioForzado) {
                                    $eq->where('id_espacio', $idEspacio)
                                       ->orWhereRaw("REPLACE(REPLACE(id_espacio, '-', ''), ' ', '') = ?", [$espacioLimpioForzado]);
                                })
                                ->where('id_profesor_colaborador', $idColab)
                                ->whereHas('modulo', function ($q) use ($diaActual) {
                                    $q->where('dia', $diaActual);
                                })
                                ->get();

                            $numModuloActual = ModulosHelper::getNumeroModulo($planColaborador->id_modulo);
                            $moduloFin = $numModuloActual;
                            $modulosAsociados = [];
                            foreach ($planificacionesMismoBloque as $planItem) {
                                if ($planItem->modulo) {
                                    $num = ModulosHelper::getNumeroModulo($planItem->id_modulo);
                                    $modulosAsociados[$num] = $planItem->modulo;
                                }
                            }
                            $cantidadModulos = 1;
                            while (isset($modulosAsociados[$moduloFin + 1])) {
                                $moduloFin++;
                                $cantidadModulos++;
                            }
                            $moduloFinalObj = $modulosAsociados[$moduloFin] ?? $planColaborador->modulo;
                            $nuevaReserva->modulo_inicio = $numModuloActual;
                            $nuevaReserva->modulo_fin = $moduloFin;
                            $nuevaReserva->hora_salida = $moduloFinalObj->hora_termino ?? null;
                            $nuevaReserva->modulos = $cantidadModulos;
                        } elseif ($infoRecForzado = \App\Models\ClaseNoRealizada::obtenerRecuperacionProgramada($idEspacio, $runNuevo, Carbon::today()->toDateString(), $horaActualStr, 30)) {
                            $nuevaReserva->id_asignatura = $infoRecForzado['id_asignatura'];
                            $nuevaReserva->modulo_inicio = $infoRecForzado['modulo_inicio'];
                            $nuevaReserva->modulo_fin = $infoRecForzado['modulo_fin'];
                            $nuevaReserva->hora_salida = $infoRecForzado['hora_termino'];
                            $nuevaReserva->modulos = $infoRecForzado['cantidad_modulos'];
                            $nuevaReserva->tipo_reserva = 'recuperacion';
                        } else {
                            $diaNormalizado = ModulosHelper::normalizarDia($diaActual);
                            $numModActual = ModulosHelper::obtenerModuloActual($horaActualStr, $diaNormalizado);
                            if ($numModActual) {
                                $horarioMod = ModulosHelper::getHorarioModulo($diaNormalizado, $numModActual);
                                $nuevaReserva->modulo_inicio = $numModActual;
                                $nuevaReserva->modulo_fin = $numModActual;
                                $nuevaReserva->hora_salida = $horarioMod['fin'] ?? null;
                                $nuevaReserva->modulos = 1;
                            }
                        }
                    }

                    $nuevaReserva->observaciones = "Sesión iniciada forzosamente; el docente anterior ({$nombreAnterior} - {$runAnterior}) no liberó el espacio";
                    $nuevaReserva->save();
                    $this->enviarCorreoReserva($nuevaReserva);
                    $nuevaReservaId = $nuevaReserva->id_reserva;
                }

                // 4. Asegurar que el espacio esté como Ocupado
                $espacio = Espacio::where('id_espacio', $idEspacio)->first();
                if ($espacio) {
                    $espacio->estado = 'Ocupado';
                    $espacio->save();
                }



                return response()->json([
                    'success' => true,
                    'mensaje' => 'Cierre forzado realizado con éxito. Su clase ha sido iniciada.',
                    'id_reserva' => $nuevaReservaId
                ]);
            } catch (\Exception $e) {
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

            // Registro de diagnóstico: confirmar que la función fue invocada (opcional)

            $request->validate([
                'run' => 'required',
                'id_espacio' => 'required|string'
            ]);

            $runRawInput = (string) $request->input('run');
            $runUsuario = $this->normalizeRun($runRawInput);
            $runLimpio = strtoupper(preg_replace('/[^0-9Kk]/', '', $runRawInput));
            $runSinDv = strlen($runLimpio) > 7 ? substr($runLimpio, 0, -1) : $runUsuario;
            $profesorDoc = Profesor::buscarProfesor($runRawInput);
            $solicitanteDoc = Solicitante::buscarSolicitante($runRawInput);
            $espacio = \App\Helpers\EspacioAliasHelper::buscarEspacio($request->input('id_espacio'));
            if (!$espacio) {
                return response()->json([
                    'tipo' => 'error',
                    'mensaje' => 'Espacio no encontrado: ' . $request->input('id_espacio')
                ], 404);
            }
            $idEspacio = $espacio->id_espacio;
            $espaciosEquiv = \App\Helpers\EspacioAliasHelper::obtenerEquivalentes($idEspacio);

            $runsUsuario = array_values(array_unique(array_filter([
                $profesorDoc ? $profesorDoc->run_profesor : null,
                $solicitanteDoc ? $solicitanteDoc->run_solicitante : null,
                $runRawInput,
                $runUsuario,
                $runLimpio,
                $runSinDv
            ])));

            // Verificar primero si el usuario tiene una reserva activa en ESTE espacio (priorizar devolución)
            $reservaActiva = Reserva::where(function ($query) use ($runsUsuario) {
                $query->whereIn('run_profesor', $runsUsuario)
                      ->orWhereIn('run_solicitante', $runsUsuario);
            })
                ->whereIn('id_espacio', $espaciosEquiv)
                ->where('estado', 'activa')
                ->latest('updated_at')
                ->first();

            // Si el usuario tiene una reserva activa en este espacio, verificar si corresponde a un bloque/módulo anterior
            // que ya finalizó y el docente tiene una nueva clase programada inmediatamente (ej. bloque consecutivo / nueva sección)
            if ($reservaActiva) {
                $ahoraCheck = Carbon::now();
                $horaActualCheckStr = $ahoraCheck->format('H:i:s');
                $diaActualCheck = strtolower($ahoraCheck->locale('es')->isoFormat('dddd'));
                $diasCheckActiva = array_values(array_unique([$diaActualCheck, ModulosHelper::normalizarDia($diaActualCheck)]));
                $periodoCheckActiva = SemesterHelper::getCurrentPeriod();
                $espacioLimpioCheckActiva = str_replace(['-', ' '], '', $idEspacio);

                $fechaResStr = $reservaActiva->fecha_reserva instanceof Carbon 
                    ? $reservaActiva->fecha_reserva->toDateString() 
                    : (is_string($reservaActiva->fecha_reserva) ? substr($reservaActiva->fecha_reserva, 0, 10) : null);

                $esDeFechaAnterior = ($fechaResStr && $fechaResStr < Carbon::today()->toDateString());
                $esHorarioTerminado = ($reservaActiva->hora_salida && $reservaActiva->hora_salida <= $horaActualCheckStr);

                // Buscar si tiene una nueva clase programada ahora (con margen de 15 min de anticipación)
                $nuevaClaseProgramada = Planificacion_Asignatura::where(function ($eq) use ($idEspacio, $espaciosEquiv, $espacioLimpioCheckActiva) {
                    $eq->where('id_espacio', $idEspacio)
                       ->orWhereIn('id_espacio', $espaciosEquiv)
                       ->orWhereRaw("REPLACE(REPLACE(id_espacio, '-', ''), ' ', '') = ?", [$espacioLimpioCheckActiva]);
                })
                    ->where(function ($q) use ($periodoCheckActiva) {
                        $q->whereHas('horario', function ($hq) use ($periodoCheckActiva) {
                            $hq->where('periodo', $periodoCheckActiva);
                        })->orDoesntHave('horario');
                    })
                    ->where(function ($qPrincipal) use ($runUsuario, $runLimpio, $runSinDv) {
                        $qPrincipal->whereHas('asignatura', function ($q) use ($runUsuario, $runLimpio, $runSinDv) {
                            $q->whereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runUsuario])
                              ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runLimpio])
                              ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runSinDv])
                              ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor_reemplazo, '.', ''), '-', ''), ' ', '') = ?", [$runUsuario])
                              ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor_reemplazo, '.', ''), '-', ''), ' ', '') = ?", [$runLimpio])
                              ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor_reemplazo, '.', ''), '-', ''), ' ', '') = ?", [$runSinDv]);
                        })->orWhereHas('horario', function ($q) use ($runUsuario, $runLimpio, $runSinDv) {
                            $q->whereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runUsuario])
                              ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runLimpio])
                              ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runSinDv]);
                        });
                    })
                    ->whereHas('modulo', function ($q) use ($diasCheckActiva, $horaActualCheckStr, $ahoraCheck) {
                        $q->whereIn('dia', $diasCheckActiva)
                          ->where('hora_inicio', '<=', $ahoraCheck->copy()->addMinutes(15)->toTimeString())
                          ->where('hora_termino', '>=', $horaActualCheckStr);
                    })
                    ->first();

                if (!$nuevaClaseProgramada) {
                    $nuevaClaseProgramada = PlanificacionProfesorColaborador::where(function ($eq) use ($idEspacio, $espaciosEquiv, $espacioLimpioCheckActiva) {
                        $eq->where('id_espacio', $idEspacio)
                           ->orWhereIn('id_espacio', $espaciosEquiv)
                           ->orWhereRaw("REPLACE(REPLACE(id_espacio, '-', ''), ' ', '') = ?", [$espacioLimpioCheckActiva]);
                    })
                        ->whereHas('profesorColaborador', function ($q) use ($runUsuario, $runLimpio, $runSinDv) {
                            $q->where('estado', 'activo')
                              ->where('fecha_inicio', '<=', Carbon::today()->toDateString())
                              ->where('fecha_termino', '>=', Carbon::today()->toDateString())
                              ->where(function ($sq) use ($runUsuario, $runLimpio, $runSinDv) {
                                  $sq->whereRaw("REPLACE(REPLACE(REPLACE(run_profesor_colaborador, '.', ''), '-', ''), ' ', '') = ?", [$runUsuario])
                                     ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor_colaborador, '.', ''), '-', ''), ' ', '') = ?", [$runLimpio])
                                     ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor_colaborador, '.', ''), '-', ''), ' ', '') = ?", [$runSinDv]);
                              });
                        })
                        ->whereHas('modulo', function ($q) use ($diasCheckActiva, $horaActualCheckStr, $ahoraCheck) {
                            $q->whereIn('dia', $diasCheckActiva)
                              ->where('hora_inicio', '<=', $ahoraCheck->copy()->addMinutes(15)->toTimeString())
                              ->where('hora_termino', '>=', $horaActualCheckStr);
                        })
                        ->first();
                }

                $numModuloNuevo = $nuevaClaseProgramada ? ModulosHelper::getNumeroModulo($nuevaClaseProgramada->id_modulo) : null;
                $numModuloAnterior = $reservaActiva->modulo_fin ?? $reservaActiva->modulo_inicio;
                $esSiguienteModulo = ($numModuloAnterior && $numModuloNuevo && $numModuloAnterior < $numModuloNuevo);

                // Si la reserva anterior ya cumplió su horario o es de un módulo anterior y el docente tiene clase ahora:
                if ($esDeFechaAnterior || (($esHorarioTerminado || $esSiguienteModulo) && $nuevaClaseProgramada)) {
                    $reservaActiva->estado = 'finalizada';
                    if (!$reservaActiva->hora_salida || $esDeFechaAnterior) {
                        $reservaActiva->hora_salida = $horaActualCheckStr;
                    }
                    $reservaActiva->observaciones = trim(($reservaActiva->observaciones ?? '') . ' | Auto-finalizada al iniciar siguiente clase/sección programada');
                    $reservaActiva->save();
                    $reservaActiva = null;

                    if ($espacio->estado !== 'Disponible') {
                        $espacio->estado = 'Disponible';
                        $espacio->save();
                    }
                }
            }

            // Si no está devolviendo una reserva activa en este espacio, verificar si tiene reserva activa en otro espacio
            if (!$reservaActiva) {
                $reservaExistente = Reserva::where(function ($query) use ($runsUsuario) {
                    $query->whereIn('run_profesor', $runsUsuario)
                          ->orWhereIn('run_solicitante', $runsUsuario);
                })
                    ->whereNotIn('id_espacio', $espaciosEquiv)
                    ->where('estado', 'activa')
                    ->whereNull('hora_salida')
                    ->first();

                if ($reservaExistente) {
                    $esAnterior = false;
                    $fechaResStr = $reservaExistente->fecha_reserva instanceof Carbon 
                        ? $reservaExistente->fecha_reserva->toDateString() 
                        : (is_string($reservaExistente->fecha_reserva) ? substr($reservaExistente->fecha_reserva, 0, 10) : null);
                    if ($fechaResStr && $fechaResStr < Carbon::today()->toDateString()) {
                        $esAnterior = true;
                    }
                    if ($reservaExistente->hora_salida && $reservaExistente->hora_salida < Carbon::now()->format('H:i:s')) {
                        $esAnterior = true;
                    }

                    if ($esAnterior) {
                        $reservaExistente->estado = 'finalizada';
                        $reservaExistente->observaciones = ($reservaExistente->observaciones ?? '') . ' | Auto-finalizada al ingresar a ' . $idEspacio;
                        $reservaExistente->save();
                        $reservaExistente = null;
                    }
                }

                if ($reservaExistente) {
                    return response()->json([
                        'tipo' => 'reserva_existente',
                        'id_espacio' => $espacio->id_espacio,
                        'mensaje' => 'Ya tienes una reserva activa en otro espacio. Debes finalizarla antes de solicitar una nueva.',
                        'espacio_disponible' => false
                    ]);
                }
            }

            // Verificar si el espacio está disponible
            $espacioDisponible = $espacio->estado === 'Disponible';

            if ($reservaActiva) {
                // Protección de 10 segundos contra rebote involuntario del lector físico de código de barras
                $segundosDesdeActivacion = $reservaActiva->updated_at ? $reservaActiva->updated_at->diffInSeconds(now()) : 999;
                if ($segundosDesdeActivacion < 10) {
                    return response()->json([
                        'tipo' => 'activacion_reciente',
                        'id_espacio' => $espacio->id_espacio,
                        'success' => true,
                        'mensaje' => 'Tu nueva clase/sección en esta sala ya fue registrada exitosamente y se encuentra activa.',
                        'reserva' => [
                            'id_reserva' => $reservaActiva->id_reserva,
                            'hora_inicio' => $reservaActiva->hora,
                            'fecha' => $reservaActiva->fecha_reserva,
                            'espacio' => $espacio->nombre_espacio
                        ],
                        'espacio_disponible' => false
                    ]);
                }

                // El usuario tiene una reserva activa en este espacio -> Devolución
                return response()->json([
                    'tipo' => 'devolucion',
                    'id_espacio' => $espacio->id_espacio,
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

            // Si el usuario NO tiene una reserva activa, pero sí tuvo una reserva HOY en este espacio que ya fue finalizada
            // y está escaneando el espacio, procesar la devolución de llaves directamente sin error
            $reservaFinalizadaHoyUsuario = Reserva::where(function ($query) use ($runsUsuario) {
                $query->whereIn('run_profesor', $runsUsuario)
                      ->orWhereIn('run_solicitante', $runsUsuario);
            })
                ->whereIn('id_espacio', $espaciosEquiv)
                ->where('fecha_reserva', Carbon::today()->toDateString())
                ->where('estado', 'finalizada')
                ->latest('updated_at')
                ->first();

            if (!$reservaFinalizadaHoyUsuario && ($runLimpio || $runSinDv)) {
                $reservaFinalizadaHoyUsuario = Reserva::where(function ($query) use ($runLimpio, $runSinDv) {
                    $query->whereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runLimpio])
                          ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_solicitante, '.', ''), '-', ''), ' ', '') = ?", [$runLimpio]);
                    if ($runSinDv) {
                        $query->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runSinDv])
                              ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_solicitante, '.', ''), '-', ''), ' ', '') = ?", [$runSinDv]);
                    }
                })
                    ->whereIn('id_espacio', $espaciosEquiv)
                    ->where('fecha_reserva', Carbon::today()->toDateString())
                    ->where('estado', 'finalizada')
                    ->latest('updated_at')
                    ->first();
            }

            if ($reservaFinalizadaHoyUsuario) {
                $segundosDesdeCierre = $reservaFinalizadaHoyUsuario->updated_at
                    ? $reservaFinalizadaHoyUsuario->updated_at->diffInSeconds(now())
                    : 9999;
                $espacioAtascadoOcupado = $espacio->estado !== 'Disponible'
                    && !Reserva::whereIn('id_espacio', $espaciosEquiv)->where('estado', 'activa')->exists();

                // Solo tratar como confirmación idempotente de devolución si se cerró recién (<= 3 min)
                // o si la sala quedó pegada en Ocupado sin otra reserva activa.
                // Si cerró hace más tiempo y el espacio está Disponible, permitir continuar hacia un nuevo bloque/reserva.
                if ($segundosDesdeCierre <= 180 || $espacioAtascadoOcupado) {
                    if ($espacio->estado !== 'Disponible') {
                        $espacio->estado = 'Disponible';
                        $espacio->save();
                    }

                    return response()->json([
                        'tipo' => 'devolucion',
                        'id_espacio' => $espacio->id_espacio,
                        'mensaje' => 'Devolución de llave confirmada exitosamente. El espacio se encuentra disponible.',
                        'espacio_disponible' => true,
                        'reserva' => [
                            'id_reserva' => $reservaFinalizadaHoyUsuario->id_reserva,
                            'hora_salida' => $reservaFinalizadaHoyUsuario->hora_salida ?? now()->format('H:i:s'),
                            'espacio' => $espacio->nombre_espacio
                        ]
                    ]);
                }
            }

            // Datos temporales actuales
            $ahora = Carbon::now();
            $horaActualStr = $ahora->format('H:i:s');
            $diaActualNormalizado = ModulosHelper::normalizarDia($ahora->locale('es')->isoFormat('dddd'));
            $horarioModulos = ModulosHelper::getHorariosModulos()[$diaActualNormalizado] ?? [];
            $periodoCheck = SemesterHelper::getCurrentPeriod();
            $diaCheck = strtolower($ahora->locale('es')->isoFormat('dddd'));
            $diasCheck = array_values(array_unique([$diaCheck, $diaActualNormalizado]));
            $espacioLimpioCheck = str_replace(['-', ' '], '', $idEspacio);
            $hoyFechaScanner = Carbon::today()->toDateString();

            // Helper para verificar si un bloque/asignatura específico ya fue finalizado hoy
            $verificarReservaFinalizadaScanner = function ($idAsig, $idMod, $moduloObj = null) use ($runUsuario, $runLimpio, $runSinDv, $espaciosEquiv, $idEspacio, $hoyFechaScanner, $horaActualStr, $horarioModulos) {
                $numModulo = ModulosHelper::getNumeroModulo($idMod);
                $resFin = Reserva::where(function ($query) use ($runUsuario, $runLimpio, $runSinDv) {
                    $query->whereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runUsuario])
                          ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_solicitante, '.', ''), '-', ''), ' ', '') = ?", [$runUsuario])
                          ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runLimpio])
                          ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_solicitante, '.', ''), '-', ''), ' ', '') = ?", [$runLimpio])
                          ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runSinDv])
                          ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_solicitante, '.', ''), '-', ''), ' ', '') = ?", [$runSinDv]);
                })
                    ->where(function ($eq) use ($espaciosEquiv, $idEspacio) {
                        $eq->whereIn('id_espacio', $espaciosEquiv)
                           ->orWhere('id_espacio', $idEspacio);
                    })
                    ->where('fecha_reserva', $hoyFechaScanner)
                    ->where('estado', 'finalizada')
                    ->where(function ($q) use ($idAsig, $numModulo) {
                        if ($numModulo !== null) {
                            $q->where('modulo_inicio', '<=', $numModulo)
                              ->where('modulo_fin', '>=', $numModulo);
                        }
                        if (!empty($idAsig)) {
                            $q->where('id_asignatura', $idAsig);
                        }
                    })
                    ->latest('updated_at')
                    ->first();

                if ($resFin) {
                    $horaInicioMod = $moduloObj->hora_inicio ?? ($numModulo && isset($horarioModulos[$numModulo]['inicio']) ? $horarioModulos[$numModulo]['inicio'] : null);
                    $horaTerminoMod = $moduloObj->hora_termino ?? ($numModulo && isset($horarioModulos[$numModulo]['fin']) ? $horarioModulos[$numModulo]['fin'] : null);

                    // Si la reserva finalizada comenzó en un módulo anterior y fue cerrada antes o justo al inicio de este nuevo módulo,
                    // corresponde al bloque anterior y NO debe bloquear el inicio de la nueva sección.
                    if ($numModulo !== null && $resFin->modulo_inicio !== null && $resFin->modulo_inicio < $numModulo) {
                        if (!$horaInicioMod || !$resFin->hora_salida || $resFin->hora_salida <= $horaInicioMod) {
                            return null;
                        }
                    }

                    // Si la reserva fue cerrada por doble escaneo accidental en el receso antes de que inicie el módulo (ej. 16:09 < 16:10),
                    // o duró menos de 5 minutos mientras el módulo sigue vigente, reactivarla automáticamente en lugar de bloquear al docente.
                    $cerradaAntesDelInicio = ($horaInicioMod && $resFin->hora_salida && $resFin->hora_salida <= $horaInicioMod);
                    $duracionSegundos = ($resFin->hora && $resFin->hora_salida)
                        ? abs(strtotime($resFin->hora_salida) - strtotime($resFin->hora))
                        : 999;
                    $moduloSigueVigente = (!$horaTerminoMod || $horaActualStr <= $horaTerminoMod);

                    if ($cerradaAntesDelInicio || ($duracionSegundos < 300 && $moduloSigueVigente)) {
                        return null;
                    }
                }

                return $resFin;
            };

            // Verificar con anticipación (hasta 15 minutos antes) si el usuario tiene una clase regular o temporal en este espacio AHORA
            $tieneClaseAhora = false;
            $claseProgramadaScanner = null;
            $claseColabScanner = null;
            $recScanner = null;
            $reservaFinalizadaHoy = null;

            $candidatosRegularesScanner = Planificacion_Asignatura::with(['modulo', 'asignatura'])
                ->where(function ($eq) use ($idEspacio, $espaciosEquiv, $espacioLimpioCheck) {
                    $eq->where('id_espacio', $idEspacio)
                       ->orWhereIn('id_espacio', $espaciosEquiv)
                       ->orWhereRaw("REPLACE(REPLACE(id_espacio, '-', ''), ' ', '') = ?", [$espacioLimpioCheck]);
                })
                ->where(function ($q) use ($periodoCheck) {
                    $q->whereHas('horario', function ($hq) use ($periodoCheck) {
                        $hq->where('periodo', $periodoCheck);
                    })->orDoesntHave('horario');
                })
                ->where(function ($qPrincipal) use ($runUsuario, $runLimpio, $runSinDv) {
                    $qPrincipal->whereHas('asignatura', function ($q) use ($runUsuario, $runLimpio, $runSinDv) {
                        $q->whereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runUsuario])
                          ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runLimpio])
                          ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runSinDv])
                          ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor_reemplazo, '.', ''), '-', ''), ' ', '') = ?", [$runUsuario])
                          ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor_reemplazo, '.', ''), '-', ''), ' ', '') = ?", [$runLimpio])
                          ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor_reemplazo, '.', ''), '-', ''), ' ', '') = ?", [$runSinDv]);
                    })->orWhereHas('horario', function ($q) use ($runUsuario, $runLimpio, $runSinDv) {
                        $q->whereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runUsuario])
                          ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runLimpio])
                          ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runSinDv]);
                    });
                })
                ->whereHas('modulo', function ($q) use ($diasCheck, $horaActualStr, $ahora) {
                    $q->whereIn('dia', $diasCheck)
                      ->where('hora_inicio', '<=', $ahora->copy()->addMinutes(15)->toTimeString())
                      ->where('hora_termino', '>=', $horaActualStr);
                })
                ->get();

            $candidatoRegFinalizado = null;
            $reservaRegFinalizada = null;

            foreach ($candidatosRegularesScanner as $candReg) {
                $resFin = $verificarReservaFinalizadaScanner($candReg->id_asignatura, $candReg->id_modulo, $candReg->modulo);
                if (!$resFin) {
                    if (!$claseProgramadaScanner) {
                        $claseProgramadaScanner = $candReg;
                    } else {
                        $esActivoActual = $candReg->modulo && $candReg->modulo->hora_inicio <= $horaActualStr && $candReg->modulo->hora_termino >= $horaActualStr;
                        $esActivoPrevio = $claseProgramadaScanner->modulo && $claseProgramadaScanner->modulo->hora_inicio <= $horaActualStr && $claseProgramadaScanner->modulo->hora_termino >= $horaActualStr;
                        if ($esActivoActual && !$esActivoPrevio) {
                            $claseProgramadaScanner = $candReg;
                        }
                    }
                } else {
                    if (!$candidatoRegFinalizado) {
                        $candidatoRegFinalizado = $candReg;
                        $reservaRegFinalizada = $resFin;
                    }
                }
            }

            if ($claseProgramadaScanner) {
                $tieneClaseAhora = true;
            } elseif ($candidatoRegFinalizado) {
                $claseProgramadaScanner = $candidatoRegFinalizado;
                $reservaFinalizadaHoy = $reservaRegFinalizada;
                $tieneClaseAhora = true;
            } else {
                $candidatosColabScanner = PlanificacionProfesorColaborador::with(['modulo', 'profesorColaborador.asignatura'])
                    ->where(function ($eq) use ($idEspacio, $espaciosEquiv, $espacioLimpioCheck) {
                        $eq->where('id_espacio', $idEspacio)
                           ->orWhereIn('id_espacio', $espaciosEquiv)
                           ->orWhereRaw("REPLACE(REPLACE(id_espacio, '-', ''), ' ', '') = ?", [$espacioLimpioCheck]);
                    })
                    ->whereHas('profesorColaborador', function ($q) use ($runUsuario, $runLimpio, $runSinDv, $hoyFechaScanner) {
                        $q->where('estado', 'activo')
                          ->where('fecha_inicio', '<=', $hoyFechaScanner)
                          ->where('fecha_termino', '>=', $hoyFechaScanner)
                          ->where(function ($sq) use ($runUsuario, $runLimpio, $runSinDv) {
                              $sq->whereRaw("REPLACE(REPLACE(REPLACE(run_profesor_colaborador, '.', ''), '-', ''), ' ', '') = ?", [$runUsuario])
                                 ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor_colaborador, '.', ''), '-', ''), ' ', '') = ?", [$runLimpio])
                                 ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor_colaborador, '.', ''), '-', ''), ' ', '') = ?", [$runSinDv]);
                          });
                    })
                    ->whereHas('modulo', function ($q) use ($diasCheck, $horaActualStr, $ahora) {
                        $q->whereIn('dia', $diasCheck)
                          ->where('hora_inicio', '<=', $ahora->copy()->addMinutes(15)->toTimeString())
                          ->where('hora_termino', '>=', $horaActualStr);
                    })
                    ->get();

                $candidatoColabFinalizado = null;
                $reservaColabFinalizada = null;

                foreach ($candidatosColabScanner as $candColab) {
                    $resFinColab = $verificarReservaFinalizadaScanner(
                        $candColab->profesorColaborador->id_asignatura ?? null,
                        $candColab->id_modulo,
                        $candColab->modulo
                    );
                    if (!$resFinColab) {
                        if (!$claseColabScanner) {
                            $claseColabScanner = $candColab;
                        }
                    } else {
                        if (!$candidatoColabFinalizado) {
                            $candidatoColabFinalizado = $candColab;
                            $reservaColabFinalizada = $resFinColab;
                        }
                    }
                }

                if ($claseColabScanner) {
                    $tieneClaseAhora = true;
                } elseif ($candidatoColabFinalizado) {
                    $claseColabScanner = $candidatoColabFinalizado;
                    $reservaFinalizadaHoy = $reservaColabFinalizada;
                    $tieneClaseAhora = true;
                } else {
                    // 1.c Fallback: Profesor colaborador activo asociado a una asignatura planificada en este espacio
                    $asigIdsColabScanner = \App\Models\ProfesorColaborador::where('estado', 'activo')
                        ->where('fecha_inicio', '<=', $hoyFechaScanner)
                        ->where('fecha_termino', '>=', $hoyFechaScanner)
                        ->whereNotNull('id_asignatura')
                        ->where(function ($sq) use ($runUsuario, $runLimpio, $runSinDv) {
                            $sq->whereRaw("REPLACE(REPLACE(REPLACE(run_profesor_colaborador, '.', ''), '-', ''), ' ', '') = ?", [$runUsuario])
                               ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor_colaborador, '.', ''), '-', ''), ' ', '') = ?", [$runLimpio])
                               ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor_colaborador, '.', ''), '-', ''), ' ', '') = ?", [$runSinDv]);
                        })
                        ->pluck('id_asignatura')
                        ->unique()
                        ->values()
                        ->all();

                    if (!empty($asigIdsColabScanner)) {
                        $candidatosAsigColabScanner = Planificacion_Asignatura::with(['modulo', 'asignatura'])
                            ->where(function ($eq) use ($idEspacio, $espaciosEquiv, $espacioLimpioCheck) {
                                $eq->where('id_espacio', $idEspacio)
                                   ->orWhereIn('id_espacio', $espaciosEquiv)
                                   ->orWhereRaw("REPLACE(REPLACE(id_espacio, '-', ''), ' ', '') = ?", [$espacioLimpioCheck]);
                            })
                            ->whereIn('id_asignatura', $asigIdsColabScanner)
                            ->whereHas('modulo', function ($q) use ($diasCheck, $horaActualStr, $ahora) {
                                $q->whereIn('dia', $diasCheck)
                                  ->where('hora_inicio', '<=', $ahora->copy()->addMinutes(15)->toTimeString())
                                  ->where('hora_termino', '>=', $horaActualStr);
                            })
                            ->get();

                        foreach ($candidatosAsigColabScanner as $candACS) {
                            $resFinACS = $verificarReservaFinalizadaScanner($candACS->id_asignatura, $candACS->id_modulo, $candACS->modulo);
                            if (!$resFinACS) {
                                if (!$claseProgramadaScanner) {
                                    $claseProgramadaScanner = $candACS;
                                    $tieneClaseAhora = true;
                                }
                            } elseif (!$reservaFinalizadaHoy) {
                                $claseProgramadaScanner = $candACS;
                                $reservaFinalizadaHoy = $resFinACS;
                                $tieneClaseAhora = true;
                            }
                        }
                    }

                    if (!$tieneClaseAhora) {
                        $recScanner = \App\Models\ClaseNoRealizada::obtenerRecuperacionProgramada(
                            $idEspacio,
                            $runUsuario,
                            $hoyFechaScanner,
                            $horaActualStr,
                            60
                        );
                        if ($recScanner) {
                            $tieneClaseAhora = true;
                            $resFinRec = $verificarReservaFinalizadaScanner(
                                $recScanner['id_asignatura'] ?? null,
                                $recScanner['id_modulo'] ?? null
                            );
                            if ($resFinRec) {
                                $reservaFinalizadaHoy = $resFinRec;
                            }
                        }
                    }
                }
            }

            // Si el docente tiene clase oficial en este bloque pero ya la finalizó hoy, no permitir reabrirla automáticamente
            if ($tieneClaseAhora) {
                if ($reservaFinalizadaHoy) {
                    $esAutoFinalizada = (
                        str_contains($reservaFinalizadaHoy->observaciones ?? '', 'Finalizada automáticamente') ||
                        str_contains($reservaFinalizadaHoy->observaciones ?? '', 'finalizó automáticamente') ||
                        str_contains($reservaFinalizadaHoy->observaciones ?? '', 'Auto-finalizada') ||
                        str_contains($reservaFinalizadaHoy->observaciones ?? '', 'Llave no devuelta') ||
                        str_contains($reservaFinalizadaHoy->observaciones ?? '', 'tiempo límite excedido')
                    );

                    if ($esAutoFinalizada) {
                        if (!str_contains($reservaFinalizadaHoy->observaciones ?? '', 'Devolución de llave registrada') && !str_contains($reservaFinalizadaHoy->observaciones ?? '', 'devolvió llave de acceso')) {
                            $reservaFinalizadaHoy->observaciones = trim(
                                ($reservaFinalizadaHoy->observaciones ?? '') .
                                "\nDevolución de llave registrada por el docente a las {$horaActualStr} (devolución tardía tras auto-cierre)."
                            );
                            $reservaFinalizadaHoy->save();
                        }

                        // Asegurar que el espacio esté disponible
                        if ($espacio->estado !== 'Disponible') {
                            $espacio->estado = 'Disponible';
                            $espacio->save();
                        }

                        return response()->json([
                            'tipo' => 'devolucion',
                            'id_espacio' => $espacio->id_espacio,
                            'mensaje' => 'Devolución de llave registrada exitosamente a las ' . substr($horaActualStr, 0, 5) . '. (La sala había sido cerrada automáticamente por el sistema al término del módulo).',
                            'espacio_disponible' => true,
                            'reserva' => [
                                'id_reserva' => $reservaFinalizadaHoy->id_reserva,
                                'hora_salida' => $horaActualStr,
                                'espacio' => $espacio->nombre_espacio
                            ]
                        ]);
                    }

                    // Asegurar que el espacio esté disponible
                    if ($espacio->estado !== 'Disponible') {
                        $espacio->estado = 'Disponible';
                        $espacio->save();
                    }

                    return response()->json([
                        'tipo' => 'devolucion',
                        'id_espacio' => $espacio->id_espacio,
                        'mensaje' => 'Devolución de llave confirmada exitosamente a las ' . substr($horaActualStr, 0, 5) . '. La clase ya se encontraba finalizada y el espacio se encuentra disponible.',
                        'espacio_disponible' => true,
                        'reserva' => [
                            'id_reserva' => $reservaFinalizadaHoy->id_reserva,
                            'hora_salida' => $reservaFinalizadaHoy->hora_salida ?? $horaActualStr,
                            'espacio' => $espacio->nombre_espacio
                        ]
                    ]);
                }
            } else {
                // Si el docente no tiene un nuevo bloque iniciando ahora pero viene a devolver la llave
                // después de que el sistema auto-finalizara su reserva previa hoy,
                // registrar su salida tardía en lugar de abrir una reserva espontánea falsa.
                $reservaAutoCerradaReciente = Reserva::where(function ($query) use ($runUsuario) {
                    $query->whereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runUsuario])
                          ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_solicitante, '.', ''), '-', ''), ' ', '') = ?", [$runUsuario]);
                })
                    ->whereIn('id_espacio', $espaciosEquiv)
                    ->where('fecha_reserva', Carbon::today()->toDateString())
                    ->where('estado', 'finalizada')
                    ->where(function ($q) {
                        $q->where('observaciones', 'LIKE', '%Finalizada automáticamente%')
                          ->orWhere('observaciones', 'LIKE', '%finalizó automáticamente%')
                          ->orWhere('observaciones', 'LIKE', '%Auto-finalizada%')
                          ->orWhere('observaciones', 'LIKE', '%Llave no devuelta%')
                          ->orWhere('observaciones', 'LIKE', '%tiempo límite excedido%');
                    })
                    ->where(function ($q) {
                        $q->whereNull('observaciones')
                          ->orWhere(function ($sq) {
                              $sq->where('observaciones', 'NOT LIKE', '%Devolución de llave registrada%')
                                 ->where('observaciones', 'NOT LIKE', '%devolvió llave de acceso%');
                          });
                    })
                    ->latest('updated_at')
                    ->first();

                if ($reservaAutoCerradaReciente) {
                    if (!str_contains($reservaAutoCerradaReciente->observaciones ?? '', 'Devolución de llave registrada') && !str_contains($reservaAutoCerradaReciente->observaciones ?? '', 'devolvió llave de acceso')) {
                        $reservaAutoCerradaReciente->observaciones = trim(
                            ($reservaAutoCerradaReciente->observaciones ?? '') .
                            "\nDevolución de llave registrada por el docente a las {$horaActualStr} (devolución tardía tras auto-cierre)."
                        );
                        $reservaAutoCerradaReciente->save();
                    }

                    // Asegurar que el espacio esté disponible
                    if ($espacio->estado !== 'Disponible') {
                        $espacio->estado = 'Disponible';
                        $espacio->save();
                    }

                    return response()->json([
                        'tipo' => 'clase_ya_finalizada',
                        'id_espacio' => $espacio->id_espacio,
                        'mensaje' => 'Devolución de llave registrada exitosamente a las ' . substr($horaActualStr, 0, 5) . '. (La sala había sido cerrada automáticamente por el sistema).',
                        'espacio_disponible' => true,
                        'reserva' => [
                            'id_reserva' => $reservaAutoCerradaReciente->id_reserva,
                            'hora_salida' => $horaActualStr,
                            'espacio' => $espacio->nombre_espacio
                        ]
                    ]);
                }
            }

            // Verificar si el usuario tiene reservas PROGRAMADAS en este espacio hoy
            $reservasProgramadasUsuario = Reserva::where(function ($query) use ($runUsuario, $runLimpio, $runSinDv) {
                $query->whereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runUsuario])
                      ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_solicitante, '.', ''), '-', ''), ' ', '') = ?", [$runUsuario])
                      ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runLimpio])
                      ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_solicitante, '.', ''), '-', ''), ' ', '') = ?", [$runLimpio])
                      ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runSinDv])
                      ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_solicitante, '.', ''), '-', ''), ' ', '') = ?", [$runSinDv]);
            })
                ->whereIn('id_espacio', $espaciosEquiv)
                ->where('estado', 'programada')
                ->where('fecha_reserva', Carbon::today()->toDateString())
                ->get();

            $reservaActivable = null;
            $reservaFueraHorario = null;

            foreach ($reservasProgramadasUsuario as $rp) {
                if ($rp->modulo_inicio && $rp->modulo_fin) {
                    $horaInicioMod = $horarioModulos[$rp->modulo_inicio]['inicio'] ?? null;
                    $horaFinMod = $horarioModulos[$rp->modulo_fin]['fin'] ?? null;

                    if ($horaInicioMod && $horaFinMod) {
                        $horaInicioConMargen = Carbon::createFromFormat('H:i:s', $horaInicioMod)->subMinutes(15)->format('H:i:s');
                        if ($horaActualStr >= $horaInicioConMargen && $horaActualStr <= $horaFinMod) {
                            $reservaActivable = $rp;
                            break;
                        }
                    }
                }
                if (!$reservaFueraHorario) {
                    $reservaFueraHorario = $rp;
                }
            }

            if ($reservaActivable) {
                // Activar la reserva: cambiar de programada → activa
                $reservaActivable->estado = 'activa';
                $reservaActivable->hora = $ahora->format('H:i:s');
                if ($tieneClaseAhora) {
                    $reservaActivable->tipo_reserva = 'clase';
                    if ($claseProgramadaScanner && empty($reservaActivable->id_asignatura)) {
                        $reservaActivable->id_asignatura = $claseProgramadaScanner->id_asignatura;
                    }
                }
                $reservaActivable->save();

                // Marcar espacio como Ocupado
                $espacio->estado = 'Ocupado';
                $espacio->save();

                return response()->json([
                    'tipo' => 'activacion_reserva',
                    'id_espacio' => $espacio->id_espacio,
                    'success' => true,
                    'mensaje' => 'Reserva programada activada exitosamente. El espacio ha sido asignado.',
                    'reserva' => [
                        'id_reserva' => $reservaActivable->id_reserva,
                        'hora_inicio' => $reservaActivable->hora,
                        'fecha' => $reservaActivable->fecha_reserva,
                        'espacio' => $espacio->nombre_espacio,
                        'nombre_actividad' => $reservaActivable->nombre_actividad,
                    ],
                    'espacio_disponible' => false
                ]);
            }

            // Solo bloquear por fuera de horario si el docente NO tiene una clase oficial en este momento
            if ($reservaFueraHorario && !$tieneClaseAhora) {
                return response()->json([
                    'tipo' => 'reserva_fuera_horario',
                    'id_espacio' => $espacio->id_espacio,
                    'success' => false,
                    'mensaje' => 'Tienes una reserva programada para este espacio, pero aún no es el horario correspondiente. La reserva se activará cuando llegue el momento.',
                    'reserva' => [
                        'id_reserva' => $reservaFueraHorario->id_reserva,
                        'fecha' => $reservaFueraHorario->fecha_reserva,
                        'modulo_inicio' => $reservaFueraHorario->modulo_inicio,
                        'modulo_fin' => $reservaFueraHorario->modulo_fin,
                        'nombre_actividad' => $reservaFueraHorario->nombre_actividad,
                    ],
                    'espacio_disponible' => false
                ]);
            }

            // Si el espacio figuraba como no disponible pero no tiene reserva activa vigente (o la última ya expiró), liberarlo
            if (!$espacioDisponible) {
                $reservaOcupanteCheck = Reserva::whereIn('id_espacio', $espaciosEquiv)
                    ->where('estado', 'activa')
                    ->orderBy('created_at', 'desc')
                    ->first();

                if (!$reservaOcupanteCheck) {
                    $espacio->estado = 'Disponible';
                    $espacio->save();
                    $espacioDisponible = true;
                } else {
                    $fechaResOcStr = $reservaOcupanteCheck->fecha_reserva instanceof Carbon
                        ? $reservaOcupanteCheck->fecha_reserva->toDateString()
                        : (is_string($reservaOcupanteCheck->fecha_reserva) ? substr($reservaOcupanteCheck->fecha_reserva, 0, 10) : null);
                    $esExpiradaOc = ($fechaResOcStr && $fechaResOcStr < Carbon::today()->toDateString())
                        || ($reservaOcupanteCheck->hora_salida && $reservaOcupanteCheck->hora_salida <= $horaActualStr);

                    if ($esExpiradaOc) {
                        $reservaOcupanteCheck->estado = 'finalizada';
                        if (!$reservaOcupanteCheck->hora_salida) {
                            $reservaOcupanteCheck->hora_salida = $horaActualStr;
                        }
                        $reservaOcupanteCheck->observaciones = trim(($reservaOcupanteCheck->observaciones ?? '') . ' | Auto-finalizada por término de horario');
                        $reservaOcupanteCheck->save();

                        $espacio->estado = 'Disponible';
                        $espacio->save();
                        $espacioDisponible = true;
                    }
                }
            }

            if ($espacioDisponible) {
                // Antes de permitir nueva reserva, verificar si hay una reserva programada
                // de OTRO usuario que cubre el módulo actual
                $reservasProgramadasOtro = Reserva::with(['profesor', 'solicitante'])
                    ->where('id_espacio', $idEspacio)
                    ->where('estado', 'programada')
                    ->where('fecha_reserva', Carbon::today()->toDateString())
                    ->where(function ($query) use ($runUsuario) {
                        $query->where(function ($q) use ($runUsuario) {
                            $q->whereNotNull('run_profesor')->whereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') != ?", [$runUsuario]);
                        })->orWhere(function ($q) use ($runUsuario) {
                            $q->whereNotNull('run_solicitante')->whereRaw("REPLACE(REPLACE(REPLACE(run_solicitante, '.', ''), '-', ''), ' ', '') != ?", [$runUsuario]);
                        });
                    })
                    ->get();

                $reservaProgramadaOtroActual = null;
                foreach ($reservasProgramadasOtro as $rpo) {
                    if ($rpo->modulo_inicio && $rpo->modulo_fin) {
                        $horaInicioMod = $horarioModulos[$rpo->modulo_inicio]['inicio'] ?? null;
                        $horaFinMod = $horarioModulos[$rpo->modulo_fin]['fin'] ?? null;
                        if ($horaInicioMod && $horaFinMod && $horaActualStr >= $horaInicioMod && $horaActualStr <= $horaFinMod) {
                            $reservaProgramadaOtroActual = $rpo;
                            break;
                        }
                    }
                }

                if ($reservaProgramadaOtroActual) {
                    $nombreOcupante = $reservaProgramadaOtroActual->profesor->name
                        ?? $reservaProgramadaOtroActual->solicitante->nombre
                        ?? 'Otro usuario';

                    return response()->json([
                        'tipo' => 'espacio_ocupado',
                        'id_espacio' => $espacio->id_espacio,
                        'mensaje' => $tieneClaseAhora 
                            ? "El espacio tiene una reserva temporal programada por {$nombreOcupante}, pero usted tiene clase programada en este bloque."
                            : "El espacio tiene una reserva programada por {$nombreOcupante} en este horario.",
                        'espacio_disponible' => false,
                        'puede_forzar_cierre' => $tieneClaseAhora,
                        'es_reserva_programada' => true,
                        'id_reserva_anterior' => $reservaProgramadaOtroActual->id_reserva,
                        'ocupante' => [
                            'tipo' => $reservaProgramadaOtroActual->run_profesor ? 'profesor' : 'solicitante',
                            'nombre' => $nombreOcupante,
                            'run' => $reservaProgramadaOtroActual->run_profesor ?? $reservaProgramadaOtroActual->run_solicitante,
                            'hora_inicio' => $horarioModulos[$reservaProgramadaOtroActual->modulo_inicio]['inicio'] ?? '-',
                            'fecha' => $reservaProgramadaOtroActual->fecha_reserva,
                        ]
                    ]);
                }

                // [NUEVO] Verificar si hay una clase programada (Planificacion_Asignatura) de otro docente
                // que cubra el horario actual o que inicie en los próximos 15 minutos.
                // IMPORTANTE: Solo evaluar si el docente escaneado NO tiene su propia clase/recuperación en este bloque.
                $claseProgramadaOtro = null;
                if (!$tieneClaseAhora) {
                    $horaActual = Carbon::now();
                    $diaActual = strtolower($horaActual->locale('es')->isoFormat('dddd'));
                    $horaActualStr = $horaActual->format('H:i:s');
                    $runUsuarioLimpio = str_replace(['.', '-', ' '], '', $runUsuario);

                    $periodoActual = SemesterHelper::getCurrentPeriod();
                    $idEspacioLimpio = str_replace(['-', ' '], '', $idEspacio);
                    $claseProgramadaOtro = Planificacion_Asignatura::with(['asignatura.profesor', 'modulo'])
                        ->where(function ($eq) use ($idEspacio, $espaciosEquiv, $idEspacioLimpio) {
                            $eq->where('id_espacio', $idEspacio)
                               ->orWhereIn('id_espacio', $espaciosEquiv)
                               ->orWhereRaw("REPLACE(REPLACE(id_espacio, '-', ''), ' ', '') = ?", [$idEspacioLimpio]);
                        })
                        ->whereHas('horario', function ($query) use ($periodoActual) {
                            $query->where('periodo', $periodoActual);
                        })
                        ->whereHas('asignatura', function ($query) use ($runUsuarioLimpio) {
                            $query->whereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') != ?", [$runUsuarioLimpio]);
                        })
                        ->whereHas('modulo', function ($query) use ($diasCheck, $horaActualStr) {
                            $query->whereIn('dia', $diasCheck)
                                  ->where('hora_inicio', '<=', $horaActualStr)
                                  ->where('hora_termino', '>', $horaActualStr);
                        })
                        ->first();

                    if (!$claseProgramadaOtro) {
                        // Si no hay clase en curso, buscar si hay una clase que empiece en los próximos 15 minutos
                        $horaLimiteAnticipada = $horaActual->copy()->addMinutes(15)->format('H:i:s');
                        $claseProgramadaOtro = Planificacion_Asignatura::with(['asignatura.profesor', 'modulo'])
                            ->where(function ($eq) use ($idEspacio, $espaciosEquiv, $idEspacioLimpio) {
                                $eq->where('id_espacio', $idEspacio)
                                   ->orWhereIn('id_espacio', $espaciosEquiv)
                                   ->orWhereRaw("REPLACE(REPLACE(id_espacio, '-', ''), ' ', '') = ?", [$idEspacioLimpio]);
                            })
                            ->whereHas('horario', function ($query) use ($periodoActual) {
                                $query->where('periodo', $periodoActual);
                            })
                            ->whereHas('asignatura', function ($query) use ($runUsuarioLimpio) {
                                $query->whereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') != ?", [$runUsuarioLimpio]);
                            })
                            ->whereHas('modulo', function ($query) use ($diasCheck, $horaActualStr, $horaLimiteAnticipada) {
                                $query->whereIn('dia', $diasCheck)
                                      ->where('hora_inicio', '>', $horaActualStr)
                                      ->where('hora_inicio', '<=', $horaLimiteAnticipada);
                            })
                            ->first();
                    }
                }

                if ($claseProgramadaOtro) {
                    $nombreDocente = $claseProgramadaOtro->asignatura?->profesor?->name ?? 'Docente programado';
                    $nombreAsignatura = $claseProgramadaOtro->asignatura?->nombre_asignatura ?? 'Clase regular';
                    $horaInicio = $claseProgramadaOtro->modulo?->hora_inicio ?? '-';
                    $horaTermino = $claseProgramadaOtro->modulo?->hora_termino ?? '-';

                    return response()->json([
                        'tipo' => 'clase_programada_otro_docente',
                        'id_espacio' => $espacio->id_espacio,
                        'mensaje' => "Esta sala tiene una clase programada de {$nombreDocente} ({$nombreAsignatura}) en el bloque {$horaInicio} - {$horaTermino}, pero el docente aún no ha asistido.",
                        'espacio_disponible' => false,
                        'clase' => [
                            'docente' => $nombreDocente,
                            'asignatura' => $nombreAsignatura,
                            'hora_inicio' => $horaInicio,
                            'hora_termino' => $horaTermino
                        ]
                    ]);
                }

                // El espacio está disponible para crear una nueva reserva
                return response()->json([
                    'tipo' => 'nueva_reserva',
                    'id_espacio' => $espacio->id_espacio,
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
                // para permitir forzar el cierre si la sala está ocupada por un tercero (o por él mismo en una sesión antigua).
                $puedeForzarCierre = false;
                $idReservaAnterior = $reservaOcupante ? $reservaOcupante->id_reserva : null;

                $horaActual = Carbon::now();
                $diaActual = strtolower($horaActual->locale('es')->isoFormat('dddd'));
                $horaActualStr = $horaActual->format('H:i:s');

                // 1. Verificamos si la reserva ocupante es "stale" (de días anteriores o ya expiró su tiempo programado)
                $esReservaAntigua = false;
                if ($reservaOcupante) {
                    $fechaReservaStr = $reservaOcupante->fecha_reserva instanceof Carbon 
                        ? $reservaOcupante->fecha_reserva->toDateString() 
                        : (is_string($reservaOcupante->fecha_reserva) ? substr($reservaOcupante->fecha_reserva, 0, 10) : null);
                    
                    if ($fechaReservaStr && $fechaReservaStr < Carbon::today()->toDateString()) {
                        $esReservaAntigua = true;
                    }
                }

                // 2. Buscar si el usuario que escanea tiene planificación en este bloque (margen de 15 min)
                $periodoScanner = SemesterHelper::getCurrentPeriod();
                $planificacionScanner = Planificacion_Asignatura::whereRaw("REPLACE(REPLACE(id_espacio, '-', ''), ' ', '') = ?", [str_replace(['-', ' '], '', $idEspacio)])
                    ->whereHas('horario', function ($q) use ($periodoScanner) {
                        $q->where('periodo', $periodoScanner);
                    })
                    ->whereHas('asignatura', function ($q) use ($runUsuario) {
                        $q->whereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runUsuario]);
                    })
                    ->whereHas('modulo', function ($q) use ($diaActual, $horaActualStr, $horaActual) {
                        $q
                            ->where('dia', $diaActual)
                            ->where('hora_inicio', '<=', $horaActual->copy()->addMinutes(15)->toTimeString())
                            ->where('hora_termino', '>=', $horaActualStr);
                    })
                    ->first();

                if (!$planificacionScanner) {
                    // Buscar también en PlanificacionProfesorColaborador
                    $planificacionScanner = PlanificacionProfesorColaborador::whereRaw("REPLACE(REPLACE(id_espacio, '-', ''), ' ', '') = ?", [str_replace(['-', ' '], '', $idEspacio)])
                        ->whereHas('profesorColaborador', function ($q) use ($runUsuario) {
                            $q->where('estado', 'activo')
                              ->where('fecha_inicio', '<=', Carbon::today()->toDateString())
                              ->where('fecha_termino', '>=', Carbon::today()->toDateString())
                              ->whereRaw("REPLACE(REPLACE(REPLACE(run_profesor_colaborador, '.', ''), '-', ''), ' ', '') = ?", [$runUsuario]);
                        })
                        ->whereHas('modulo', function ($q) use ($diaActual, $horaActualStr, $horaActual) {
                            $q
                                ->where('dia', $diaActual)
                                ->where('hora_inicio', '<=', $horaActual->copy()->addMinutes(15)->toTimeString())
                                ->where('hora_termino', '>=', $horaActualStr);
                        })
                        ->first();
                }

                // Si la reserva activa en la sala ya superó su hora de término prevista, considerarla stale
                if ($reservaOcupante && $reservaOcupante->hora_salida && $reservaOcupante->hora_salida < $horaActualStr) {
                    $esReservaAntigua = true;
                }

                // Si la reserva ocupante en el espacio es del mismo docente y el docente tiene clase ahora,
                // O si la reserva ocupante ya expiró y el docente tiene clase ahora:
                // Auto-finalizar la sesión anterior y dejar disponible el espacio sin mostrar alerta de sala ocupada
                $runOcupanteNorm = $reservaOcupante ? str_replace(['.', '-', ' '], '', $reservaOcupante->run_profesor ?? '') : '';
                $runScannerNorm = str_replace(['.', '-', ' '], '', $runUsuario);
                $esMismoDocente = ($runOcupanteNorm !== '' && $runOcupanteNorm === $runScannerNorm);

                if ($reservaOcupante && ($esMismoDocente || $esReservaAntigua) && ($tieneClaseAhora || $planificacionScanner)) {
                    $reservaOcupante->estado = 'finalizada';
                    if (!$reservaOcupante->hora_salida || $esReservaAntigua) {
                        $reservaOcupante->hora_salida = $horaActualStr;
                    }
                    $reservaOcupante->observaciones = trim(($reservaOcupante->observaciones ?? '') . ' | Auto-finalizada al iniciar nueva clase');
                    $reservaOcupante->save();

                    $espacio->estado = 'Disponible';
                    $espacio->save();

                    return response()->json([
                        'tipo' => 'nueva_reserva',
                        'id_espacio' => $espacio->id_espacio,
                        'mensaje' => 'Espacio disponible para reservar',
                        'espacio_disponible' => true
                    ]);
                }

                if ($planificacionScanner || $tieneClaseAhora || $esReservaAntigua) {
                    $puedeForzarCierre = true;
                } else {
                    // También verificar reservas programadas del scanner (margen de 15 min)
                    $reservaProgramadaScanner = Reserva::whereRaw("REPLACE(REPLACE(id_espacio, '-', ''), ' ', '') = ?", [str_replace(['-', ' '], '', $idEspacio)])
                        ->where(function ($q) use ($runUsuario) {
                            $q->whereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runUsuario])
                              ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_solicitante, '.', ''), '-', ''), ' ', '') = ?", [$runUsuario]);
                        })
                        ->where('estado', 'programada')
                        ->where('fecha_reserva', Carbon::today()->toDateString())
                        ->first();

                    if ($reservaProgramadaScanner) {
                        // Verificar margen de 15 minutos para la reserva programada
                        $diaActualNormalizado = ModulosHelper::normalizarDia(Carbon::now()->locale('es')->isoFormat('dddd'));
                        $horarioModulos = ModulosHelper::getHorariosModulos()[$diaActualNormalizado] ?? [];
                        $horaInicioMod = $horarioModulos[$reservaProgramadaScanner->modulo_inicio]['inicio'] ?? null;
                        
                        if ($horaInicioMod) {
                            $horaInicioConMargen = Carbon::createFromFormat('H:i:s', $horaInicioMod)->subMinutes(15)->format('H:i:s');
                            if ($horaActualStr >= $horaInicioConMargen) {
                                $puedeForzarCierre = true;
                            }
                        }
                    }
                }


                return response()->json([
                    'tipo' => 'espacio_ocupado',
                    'id_espacio' => $espacio->id_espacio,
                    'mensaje' => $mensaje,
                    'espacio_disponible' => false,
                    'ocupante' => $informacionOcupante,
                    'puede_forzar_cierre' => $puedeForzarCierre,
                    'id_reserva_anterior' => $idReservaAnterior
                ]);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'tipo' => 'error',
                'mensaje' => 'Error de validación: ' . implode(', ', \Illuminate\Support\Arr::flatten($e->errors())),
                'errores' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'tipo' => 'error',
                'mensaje' => 'Error al verificar estado del espacio y reserva: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar usuario (profesor, solicitante o usuario no registrado)
     */
    public function procesarPrimeraLectura($run)
    {
        try {
            $runRaw = (string) $run;
            $run = $this->normalizeRun($runRaw);
            $runLimpio = strtoupper(preg_replace('/[^0-9Kk]/', '', $runRaw));
            $runSinDv = strlen($runLimpio) > 7 ? substr($runLimpio, 0, -1) : $run;
            $this->establecerContextoTenant();

            // 1. Intentar verificar como Profesor (tenant actual o cross-tenant, soportando RUN con o sin DV)
            $profesor = Profesor::buscarProfesor($runRaw);

            if ($profesor) {
                // Es profesor - verificar clases programadas inmediatamente
                $horaActual = now()->format('H:i:s');
                $diaActual = now()->dayOfWeek;
                $clasesResponse = $this->verificarClasesProgramadas($profesor->run_profesor, $horaActual, $diaActual);
                $clasesInfo = $clasesResponse instanceof \Illuminate\Http\JsonResponse 
                    ? $clasesResponse->getData(true) 
                    : (is_array($clasesResponse) ? $clasesResponse : []);

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
                    'tiene_clases' => $clasesInfo['tiene_clases'] ?? false,
                    'clases_detalle' => $clasesInfo,
                    'mensaje' => 'Profesor verificado correctamente'
                ]);
            }

            // 2. Intentar verificar como Solicitante Registrado (BD tenant, soportando RUN con o sin DV)
            $solicitante = Solicitante::on('tenant')
                ->where('activo', true)
                ->where(function ($q) use ($run, $runLimpio, $runSinDv) {
                    $q->where('run_solicitante', $run)
                      ->orWhere('run_solicitante', $runLimpio)
                      ->orWhere('run_solicitante', $runSinDv)
                      ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_solicitante, '.', ''), '-', ''), ' ', '') = ?", [$run])
                      ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_solicitante, '.', ''), '-', ''), ' ', '') = ?", [$runLimpio])
                      ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_solicitante, '.', ''), '-', ''), ' ', '') = ?", [$runSinDv]);
                })
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
                    'tiene_clases' => false,
                    'mensaje' => 'Solicitante verificado correctamente'
                ]);
            }

            // 3. Usuario no encontrado
            return response()->json([
                'verificado' => false,
                'tipo_usuario' => 'solicitante_nuevo',
                'run_escaneado' => $run,
                'mensaje' => 'Usuario no encontrado. Se requiere registro.',
                'requiere_registro' => true
            ]);

        } catch (\Exception $e) {
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
            $runRaw = (string) $run;
            $run = $this->normalizeRun($runRaw);
            $runLimpio = strtoupper(preg_replace('/[^0-9Kk]/', '', $runRaw));
            $runSinDv = strlen($runLimpio) > 7 ? substr($runLimpio, 0, -1) : $run;
            // Establecer contexto del tenant desde el request

            $this->establecerContextoTenant();

            // CASO 1: Verificar si es profesor registrado (tenant actual o cross-tenant, soportando RUN con o sin DV)
            $profesor = Profesor::buscarProfesor($runRaw);

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

            // CASO 2: Verificar si es solicitante registrado (BD tenant, soportando RUN con o sin DV)
            $solicitante = Solicitante::on('tenant')
                ->where('activo', true)
                ->where(function ($q) use ($run, $runLimpio, $runSinDv) {
                    $q->where('run_solicitante', $run)
                      ->orWhere('run_solicitante', $runLimpio)
                      ->orWhere('run_solicitante', $runSinDv)
                      ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_solicitante, '.', ''), '-', ''), ' ', '') = ?", [$run])
                      ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_solicitante, '.', ''), '-', ''), ' ', '') = ?", [$runLimpio])
                      ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_solicitante, '.', ''), '-', ''), ' ', '') = ?", [$runSinDv]);
                })
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

            $idEspacioNorm = $this->normalizeEspacioId($idEspacio);
            $espaciosEquiv = \App\Helpers\EspacioAliasHelper::obtenerEquivalentes($idEspacioNorm);
            $espacio = Espacio::withoutGlobalScopes()
                ->with(['piso.facultad.sede'])
                ->where(function ($q) use ($idEspacio, $idEspacioNorm, $espaciosEquiv) {
                    $q->where('id_espacio', $idEspacio)
                      ->orWhere('id_espacio', $idEspacioNorm)
                      ->orWhereIn('id_espacio', $espaciosEquiv);
                })
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
                    'piso' => $espacio->piso?->numero_piso,
                    'facultad' => $espacio->piso?->facultad?->nombre_facultad,
                    'sede' => $espacio->piso?->facultad?->sede?->nombre_sede
                ],
                'mensaje' => $disponible ? 'Espacio disponible' : 'Espacio no disponible'
            ]);
        } catch (\Exception $e) {
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
        return response()->json([
            'success' => false,
            'mensaje' => 'Este endpoint está en desuso y ha sido unificado en los controladores específicos.'
        ], 410);
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
            $diaNormalizado = ModulosHelper::normalizarDia($nombreDia);
            $diasPosibles = array_values(array_unique([$nombreDia, $diaNormalizado]));

            // Obtener período actual
            $periodo = SemesterHelper::getCurrentPeriod();
            $runLimpio = strtoupper(preg_replace('/[^0-9Kk]/', '', $run));
            $runSinDv = strlen($runLimpio) > 1 ? substr($runLimpio, 0, -1) : $runLimpio;

            // Buscar planificaciones del profesor para el día actual
            $planificaciones = Planificacion_Asignatura::with(['asignatura', 'modulo', 'espacio'])
                ->where(function ($qPrincipal) use ($run, $runLimpio, $runSinDv) {
                    $qPrincipal->whereHas('asignatura', function ($query) use ($run, $runLimpio, $runSinDv) {
                        $query->where('run_profesor', $run)
                              ->orWhere('run_profesor', $runLimpio)
                              ->orWhere('run_profesor', $runSinDv)
                              ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runLimpio])
                              ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runSinDv]);
                    })->orWhereHas('horario', function ($query) use ($run, $runLimpio, $runSinDv) {
                        $query->where('run_profesor', $run)
                              ->orWhere('run_profesor', $runLimpio)
                              ->orWhere('run_profesor', $runSinDv)
                              ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runLimpio])
                              ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runSinDv]);
                    });
                })
                ->whereHas('modulo', function ($query) use ($diasPosibles) {
                    $query->whereIn('dia', $diasPosibles);
                })
                ->where(function ($query) use ($periodo) {
                    $query->whereHas('horario', function ($hq) use ($periodo) {
                        $hq->where('periodo', $periodo);
                    })->orDoesntHave('horario');
                })
                ->get();

            // Incluir también clases reagendadas / en recuperación para el día de hoy
            $hoyStr = Carbon::today()->toDateString();
            $recuperacionesHoy = \App\Models\RecuperacionClase::with(['asignatura', 'moduloReagendado', 'espacioReagendado', 'espacio'])
                ->whereIn('estado', ['reagendada', 'pendiente'])
                ->whereDate('fecha_reagendada', $hoyStr)
                ->where(function ($q) use ($run, $runLimpio, $runSinDv) {
                    $q->where('run_profesor', $run)
                      ->orWhere('run_profesor', $runLimpio)
                      ->orWhere('run_profesor', $runSinDv)
                      ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runLimpio])
                      ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runSinDv]);
                    if ($runSinDv) {
                        $q->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') LIKE ?", [$runSinDv . '%']);
                    }
                    if ($runLimpio) {
                        $q->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') LIKE ?", [$runLimpio . '%']);
                    }
                })
                ->get();

            $prefijosDias = [0 => 'DO', 1 => 'LU', 2 => 'MA', 3 => 'MI', 4 => 'JU', 5 => 'VI', 6 => 'SA'];
            $prefijoDiaActual = $prefijosDias[Carbon::today()->dayOfWeek] ?? 'LU';

            foreach ($recuperacionesHoy as $recItem) {
                $espObj = $recItem->espacioReagendado ?: $recItem->espacio;
                if (!$espObj || !$recItem->asignatura) {
                    continue;
                }
                $modulosRaw = explode(',', (string) ($recItem->id_modulo_reagendado ?: $recItem->id_modulo_original));
                foreach ($modulosRaw as $mRaw) {
                    $mTrim = trim($mRaw);
                    if ($mTrim === '') continue;
                    $modObj = \App\Models\Modulo::find($mTrim);
                    if (!$modObj && is_numeric($mTrim)) {
                        $modObj = \App\Models\Modulo::find("{$prefijoDiaActual}.{$mTrim}");
                    }
                    if ($modObj) {
                        $planificaciones->push((object) [
                            'id_asignatura' => $recItem->id_asignatura,
                            'id_espacio'    => $espObj->id_espacio,
                            'id_modulo'     => $modObj->id_modulo,
                            'asignatura'    => $recItem->asignatura,
                            'modulo'        => $modObj,
                            'espacio'       => $espObj,
                            'es_recuperacion' => true,
                        ]);
                    }
                }
            }

            // También consultar ClaseNoRealizada para hoy
            $cnrsHoy = \App\Models\ClaseNoRealizada::with(['asignatura', 'modulo', 'espacio'])
                ->whereDate('fecha_clase', $hoyStr)
                ->where(function ($q) {
                    $q->whereIn('estado', ['pendiente', 'recuperada', 'programada'])
                      ->orWhere('observaciones', 'LIKE', '%Reagendada%');
                })
                ->where(function ($q) use ($run, $runLimpio, $runSinDv) {
                    $q->where('run_profesor', $run)
                      ->orWhere('run_profesor', $runLimpio)
                      ->orWhere('run_profesor', $runSinDv)
                      ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runLimpio])
                      ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runSinDv]);
                    if ($runSinDv) {
                        $q->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') LIKE ?", [$runSinDv . '%']);
                    }
                    if ($runLimpio) {
                        $q->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') LIKE ?", [$runLimpio . '%']);
                    }
                })
                ->get();
            foreach ($cnrsHoy as $cnrItem) {
                if ($cnrItem->modulo && $cnrItem->asignatura && $cnrItem->espacio) {
                    $planificaciones->push((object) [
                        'id_asignatura' => $cnrItem->id_asignatura,
                        'id_espacio'    => $cnrItem->id_espacio,
                        'id_modulo'     => $cnrItem->id_modulo,
                        'asignatura'    => $cnrItem->asignatura,
                        'modulo'        => $cnrItem->modulo,
                        'espacio'       => $cnrItem->espacio,
                        'es_recuperacion' => true,
                    ]);
                }
            }



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
                    'nombre' => $profesor->name ?? $profesor->nombre_profesor,
                    'run' => $profesor->run_profesor,
                    'email' => $profesor->email ?? $profesor->email_profesor ?? null
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
                $this->enviarCorreoReserva($reservaNueva);

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

            // Buscar el espacio con soporte para alias y scopes
            $espacio = \App\Helpers\EspacioAliasHelper::buscarEspacio($request->input('id_espacio'));
            if (!$espacio) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Espacio no encontrado'
                ], 404);
            }

            $idEspacio = $espacio->id_espacio;
            $espaciosEquiv = \App\Helpers\EspacioAliasHelper::obtenerEquivalentes($idEspacio);

            // Obtener la reserva activa actual en este espacio
            $reservaActiva = Reserva::whereIn('id_espacio', $espaciosEquiv)
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
        app(\App\Services\ReservaService::class)->enviarCorreoReserva($reserva);
    }

    /**
     * Envía el correo de confirmación de devolución al profesor o solicitante en background tras responder al cliente.
     */
    private function enviarCorreoDevolucion(Reserva $reserva): void
    {
        dispatch(function () use ($reserva) {
            try {
                $reserva->load(['profesor', 'solicitante', 'espacio', 'asignatura']);
                $email = app(\App\Services\ReservaService::class)->resolverEmailReserva($reserva) ?: $this->resolverEmailReserva($reserva);
                if ($email) {
                    Mail::to($email)->send(new ConfirmacionDevolucion($reserva));
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("⚠️ No se pudo enviar correo de devolución para reserva {$reserva->id_reserva}: " . $e->getMessage());
            }
        })->afterResponse();
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
        if (Tenant::current()) {
            return;
        }

        // 1. Detectar sede desde parámetro de request o header
        $sede = request('sede_id') ?? request('sede') ?? request()->header('X-Sede-ID');
        if ($sede) {
            $tenant = Tenant::where('sede_id', strtoupper($sede))->orWhere('domain', strtolower($sede))->first();
            if ($tenant) {
                $tenant->makeCurrent();
                return;
            }
        }

        // 2. Detectar sede desde prefijo de id_espacio (CCP-, TH-, LA-, CH-, CT-)
        $idEspacio = request('id_espacio') ?? request('espacio');
        if ($idEspacio && preg_match('/^(CCP|TH|LA|CH|CT)(?:[-_]|\d)/i', $idEspacio, $mPref)) {
            $tenant = Tenant::where('sede_id', strtoupper($mPref[1]))->first();
            if ($tenant) {
                $tenant->makeCurrent();
                return;
            }
        }

        $tenantId = tenant_id() ?? (request()->hasSession() ? session('tenant_id') : null);
        if ($tenantId) {
            $tenant = Tenant::find($tenantId);
            if ($tenant) {
                $tenant->makeCurrent();
                return;
            }
        }

        $host = request()->getHost();
        $subdomain = explode('.', $host)[0] ?? $host;
        $tenant = Tenant::where('domain', $host)
            ->orWhere('domain', $subdomain)
            ->orWhere('domain', 'LIKE', '%' . $host . '%')
            ->first();

        if ($tenant) {
            $tenant->makeCurrent();
        }
    }


    /**
     * Limpiar el caché de estados de espacios para el tenant actual
     */
    private function limpiarCacheEstadosEspacios()
    {
        try {
            $tenantId = Tenant::current()?->id ?? 'default';
            \Illuminate\Support\Facades\Cache::forget("estados_espacios_{$tenantId}");
        } catch (\Exception $e) {
        }
    }

    /**
     * Normalizar el ID del espacio usando el helper centralizado
     */
    private function normalizeEspacioId(?string $idEspacio): ?string
    {
        return !empty($idEspacio) ? \App\Helpers\EspacioAliasHelper::normalizar($idEspacio) : $idEspacio;
    }
}
