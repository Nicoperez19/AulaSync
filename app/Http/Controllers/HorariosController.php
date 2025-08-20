<?php

namespace App\Http\Controllers;

use App\Models\Horario;
use App\Models\Profesor;
use App\Models\Planificacion_Asignatura;
use App\Models\Sede;
use App\Models\Asignatura;
use App\Models\Modulo;
use App\Models\Espacio;
use App\Models\Piso;
use App\Helpers\SemesterHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class HorariosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Obtener filtros de la request - usar año actual por defecto
        $semestreFiltro = $request->input('semestre');
        $anioFiltro = $request->input('anio', SemesterHelper::getCurrentAcademicYear());

        // Obtener todos los períodos únicos de los horarios para los filtros
        $periodosDisponibles = Horario::select('periodo')
            ->whereNotNull('periodo')
            ->where('periodo', '!=', '')
            ->distinct()
            ->pluck('periodo')
            ->sort()
            ->values();

        Log::info('Períodos disponibles en directorio de profesores:', [
            'periodos' => $periodosDisponibles->toArray()
        ]);

        // Separar años y semestres de los períodos
        $aniosDisponibles = [];
        $semestresDisponibles = [];

        foreach ($periodosDisponibles as $periodo) {
            if (preg_match('/^(\d{4})-(\d+)$/', $periodo, $matches)) {
                $anio = $matches[1];
                $semestre = $matches[2];

                if (!in_array($anio, $aniosDisponibles)) {
                    $aniosDisponibles[] = $anio;
                }

                if (!in_array($semestre, $semestresDisponibles)) {
                    $semestresDisponibles[] = $semestre;
                }
            }
        }

        // Ordenar arrays
        sort($aniosDisponibles);
        sort($semestresDisponibles);

        $query = Profesor::with(['areaAcademica', 'facultad']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('run_profesor', 'like', '%' . $search . '%');
            });
        }

        // Filtro por letra inicial del apellido
        if ($request->filled('letra') && $request->letra !== 'Todos') {
            $letra = $request->letra;
            // Quitar tildes y comparar solo la primera letra del apellido
            $query->whereRaw("UPPER(REPLACE(SUBSTRING_INDEX(name, ',', 1), 'Á', 'A')) LIKE ?", [strtoupper($letra) . '%']);
        }

        // Filtro por período (siempre usar año actual)
        if ($semestreFiltro) {
            $periodoFiltro = $anioFiltro . '-' . $semestreFiltro;
            $query->whereHas('horarios', function ($q) use ($periodoFiltro) {
                $q->where('periodo', $periodoFiltro);
            });
        } else {
            // Si no hay semestre seleccionado, filtrar por año actual
            $query->whereHas('horarios', function ($q) use ($anioFiltro) {
                $q->where('periodo', 'like', $anioFiltro . '-%');
            });
        }

        Log::info('Filtros aplicados en directorio de profesores:', [
            'semestreFiltro' => $semestreFiltro,
            'anioFiltro' => $anioFiltro,
            'periodoFiltro' => $periodoFiltro ?? null
        ]);

        $profesores = $query->orderBy('name')->paginate(27);

        // Determinar el período para los horarios (siempre usar año actual)
        if ($semestreFiltro) {
            $periodo = $anioFiltro . '-' . $semestreFiltro;
        } else {
            $semestre = SemesterHelper::getCurrentSemester();
            $periodo = $anioFiltro . '-' . $semestre;
        }

        Log::info('Período determinado para horarios en directorio de profesores:', [
            'anioActual' => $anioActual ?? null,
            'semestre' => $semestre ?? null,
            'periodo' => $periodo
        ]);

        $horarios = Horario::with(['profesor', 'planificaciones.asignatura', 'planificaciones.espacio'])
            ->where('periodo', $periodo)
            ->get();

        Log::info('Horarios encontrados en directorio de profesores:', [
            'total_horarios' => $horarios->count(),
            'periodo' => $periodo
        ]);

        // Formatear las horas de inicio y término de los módulos
        $horarios->each(function ($horario) {
            $horario->planificaciones->each(function ($planificacion) {
                if ($planificacion->modulo) {
                    $planificacion->modulo->hora_inicio = substr($planificacion->modulo->hora_inicio, 0, 5);
                    $planificacion->modulo->hora_termino = substr($planificacion->modulo->hora_termino, 0, 5);
                }
            });
        });

        $horarios = $horarios->groupBy('run_profesor');

        return view('layouts.schedules.schedules_index', compact(
            'profesores',
            'horarios',
            'semestresDisponibles',
            'semestreFiltro',
            'anioFiltro'
        ));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function getHorarioProfesor($run, Request $request)
    {
        try {
            Log::info('Solicitud de horario para profesor:', [
                'run' => $run,
                'semestreFiltro' => $request->input('semestre'),
                'anioFiltro' => $request->input('anio')
            ]);

            // Obtener el período de los filtros (usar año actual por defecto)
            $semestreFiltro = $request->input('semestre');
            $anioFiltro = $request->input('anio', SemesterHelper::getCurrentAcademicYear());

            if ($semestreFiltro) {
                $periodo = $anioFiltro . '-' . $semestreFiltro;
            } else {
                // Si no hay semestre seleccionado, usar el semestre actual
                $semestre = SemesterHelper::getCurrentSemester();
                $periodo = $anioFiltro . '-' . $semestre;
            }

            Log::info('Período determinado para horario de profesor:', [
                'periodo' => $periodo,
                'run' => $run
            ]);

            $horario = Horario::with(['profesor', 'planificaciones.asignatura', 'planificaciones.espacio'])
                ->where('run_profesor', $run)
                ->where('periodo', $periodo)
                ->first();

            // Si se encontró el horario, filtrar las planificaciones por período también
            if ($horario) {
                $totalAntes = $horario->planificaciones->count();

                // Filtrar planificaciones por período de la asignatura
                $horario->planificaciones = $horario->planificaciones->filter(function ($planificacion) use ($periodo) {
                    // Verificar que la asignatura tenga el período correcto
                    if (!$planificacion->asignatura) {
                        return false;
                    }

                    $asignaturaPeriodo = $planificacion->asignatura->periodo;
                    $coincide = $asignaturaPeriodo === $periodo;

                    // Log para debugging específico
                    if ($planificacion->asignatura->run_profesor == '10424736') {
                        Log::info('Verificando planificación:', [
                            'asignatura_codigo' => $planificacion->asignatura->codigo_asignatura,
                            'asignatura_periodo' => $asignaturaPeriodo,
                            'periodo_buscado' => $periodo,
                            'coincide' => $coincide
                        ]);
                    }

                    return $coincide;
                });

                $totalDespues = $horario->planificaciones->count();

                Log::info('Filtrado de planificaciones:', [
                    'periodo' => $periodo,
                    'planificaciones_antes_filtro' => $totalAntes,
                    'planificaciones_despues_filtro' => $totalDespues,
                    'planificaciones_filtradas' => $horario->planificaciones->pluck('asignatura.periodo', 'asignatura.codigo_asignatura')->toArray()
                ]);
            }

            Log::info('Búsqueda de horario:', [
                'run' => $run,
                'periodo' => $periodo,
                'horario_encontrado' => $horario ? 'Sí' : 'No',
                'total_planificaciones_antes_filtro' => $horario ? $horario->planificaciones->count() : 0
            ]);

            if (!$horario) {
                Log::warning('Horario no encontrado:', [
                    'run' => $run,
                    'periodo' => $periodo
                ]);

                // Verificar si el profesor existe
                $profesor = Profesor::where('run_profesor', $run)->first();
                if (!$profesor) {
                    return response()->json(['error' => 'Profesor no encontrado'], 404);
                }

                // Devolver respuesta informativa en lugar de error 404
                return response()->json([
                    'profesor' => $profesor,
                    'horario' => null,
                    'asignaturas' => collect([]),
                    'modulos' => Modulo::orderBy('hora_inicio')->get(),
                    'mensaje' => 'El profesor no tiene horario asignado para el período ' . $periodo
                ]);
            }

            // Obtener asignaturas filtradas por período
            $asignaturas = Asignatura::where('run_profesor', $run)
                ->where('periodo', $periodo)
                ->with(['planificaciones.espacio', 'planificaciones.modulo'])
                ->get();

            // Obtener todas las asignaturas del profesor para comparar
            $todasAsignaturas = Asignatura::where('run_profesor', $run)->get();

            $modulos = Modulo::orderBy('hora_inicio')->get();

            Log::info('Datos del horario:', [
                'periodo_buscado' => $periodo,
                'total_asignaturas_profesor' => $todasAsignaturas->count(),
                'asignaturas_filtradas' => $asignaturas->count(),
                'asignaturas_todas' => $todasAsignaturas->pluck('periodo', 'codigo_asignatura')->toArray(),
                'asignaturas_filtradas_detalle' => $asignaturas->pluck('periodo', 'codigo_asignatura')->toArray(),
                'horario' => $horario->toArray(),
                'modulos' => $modulos->toArray()
            ]);

            // Logging específico para debugging del modal
            if ($run == '10424736' || $run == '17844444') {
                Log::info('DEBUG MODAL - Profesor:', [
                    'run' => $run,
                    'periodo' => $periodo,
                    'total_planificaciones_horario' => $horario->planificaciones->count(),
                    'planificaciones_detalle' => $horario->planificaciones->map(function ($plan) {
                        return [
                            'asignatura_codigo' => $plan->asignatura->codigo_asignatura ?? 'N/A',
                            'asignatura_nombre' => $plan->asignatura->nombre_asignatura ?? 'N/A',
                            'asignatura_periodo' => $plan->asignatura->periodo ?? 'N/A',
                            'modulo' => $plan->modulo->id_modulo ?? 'N/A'
                        ];
                    })->toArray()
                ]);
            }

            return response()->json([
                'horario' => $horario,
                'asignaturas' => $asignaturas,
                'modulos' => $modulos,
                'periodo' => $periodo
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener horario:', [
                'error' => $e->getMessage(),
                'run' => $run
            ]);
            return response()->json(['error' => 'Error al obtener el horario'], 500);
        }
    }
    public function getHorariosEspacios(Request $request)
    {
        try {
            $id_espacio = $request->input('id_espacio');

            $query = Planificacion_Asignatura::with(['asignatura.profesor', 'modulo', 'espacio']);

            if ($id_espacio) {
                $query->where('id_espacio', $id_espacio);
            }

            $planificaciones = $query->get();

            // Agrupar por espacio
            $horariosPorEspacio = $planificaciones->groupBy('id_espacio')->map(function ($items) {
                return $items->map(function ($plan) {
                    return [
                        'asignatura' => $plan->asignatura->nombre_asignatura ?? '',
                        'profesor' => $plan->asignatura->profesor ? [
                            'name' => $plan->asignatura->profesor->name
                        ] : null,
                        'dia' => $plan->modulo->dia ?? '',
                        'hora_inicio' => $plan->modulo->hora_inicio ?? '',
                        'hora_termino' => $plan->modulo->hora_termino ?? '',
                        'espacio' => $plan->espacio->nombre_espacio ?? '',
                    ];
                });
            });

            return response()->json([
                'horarios' => $horariosPorEspacio
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener horarios de espacios:', [
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Error al obtener los horarios de los espacios'], 500);
        }
    }

    public function showEspacios(Request $request)
    {
        // Obtener filtros de la request - usar año actual por defecto
        $semestreFiltro = $request->input('semestre');
        $anioFiltro = $request->input('anio', SemesterHelper::getCurrentAcademicYear());

        // Obtener todos los períodos únicos de los horarios para los filtros
        $periodosDisponibles = Horario::select('periodo')
            ->whereNotNull('periodo')
            ->where('periodo', '!=', '')
            ->distinct()
            ->pluck('periodo')
            ->sort()
            ->values();

        Log::info('Períodos disponibles en la base de datos:', [
            'periodos' => $periodosDisponibles->toArray()
        ]);

        // Separar años y semestres de los períodos
        $aniosDisponibles = [];
        $semestresDisponibles = [];

        foreach ($periodosDisponibles as $periodo) {
            if (preg_match('/^(\d{4})-(\d+)$/', $periodo, $matches)) {
                $anio = $matches[1];
                $semestre = $matches[2];

                if (!in_array($anio, $aniosDisponibles)) {
                    $aniosDisponibles[] = $anio;
                }

                if (!in_array($semestre, $semestresDisponibles)) {
                    $semestresDisponibles[] = $semestre;
                }
            }
        }

        // Ordenar arrays
        sort($aniosDisponibles);
        sort($semestresDisponibles);

        // Determinar el período por defecto usando el helper
        $anioActual = SemesterHelper::getCurrentAcademicYear();
        $semestre = SemesterHelper::getCurrentSemester();
        $periodo = SemesterHelper::getCurrentPeriod();

        Log::info('Período determinado por el helper:', [
            'anioActual' => $anioActual,
            'semestre' => $semestre,
            'periodo' => $periodo
        ]);

        // Obtener todos los pisos con sus espacios, ordenados por número de piso
        $pisos = Piso::with([
            'espacios' => function ($q) {
                $q->orderBy('nombre_espacio');
            }
        ])->orderBy('numero_piso')->get();

        // Cargar horarios por defecto para el primer semestre 2025 o para los filtros seleccionados
        $horariosPorEspacio = collect([]);

        // Si no hay semestre seleccionado, usar el semestre actual
        if (!$semestreFiltro) {
            $semestreFiltro = SemesterHelper::getCurrentSemester();
        }

        Log::info('Período determinado en showEspacios:', [
            'semestreFiltro' => $semestreFiltro,
            'anioFiltro' => $anioFiltro,
            'periodo_por_defecto' => $periodo
        ]);

        if ($semestreFiltro) {
            $periodo = $anioFiltro . '-' . $semestreFiltro;

            Log::info('Período final para búsqueda:', [
                'periodo' => $periodo
            ]);

            // Cargar horarios solo para el período seleccionado
            $planificaciones = Planificacion_Asignatura::with(['asignatura.profesor', 'modulo', 'espacio', 'horario'])
                ->whereHas('horario', function ($q) use ($periodo) {
                    $q->where('periodo', $periodo);
                })
                ->get();

            Log::info('Planificaciones encontradas en showEspacios:', [
                'total_planificaciones' => $planificaciones->count(),
                'periodo' => $periodo
            ]);

            // Agrupar por espacio
            $horariosPorEspacio = $planificaciones->groupBy('id_espacio')->map(function ($items) {
                return $items->map(function ($plan) {
                    return [
                        'asignatura' => $plan->asignatura->nombre_asignatura ?? '',
                        'codigo_asignatura' => $plan->asignatura->codigo_asignatura ?? '',
                        'profesor' => $plan->asignatura->profesor ? [
                            'name' => $plan->asignatura->profesor->name
                        ] : null,
                        'dia' => $plan->modulo->dia ?? '',
                        'hora_inicio' => $plan->modulo->hora_inicio ?? '',
                        'hora_termino' => $plan->modulo->hora_termino ?? '',
                        'espacio' => $plan->espacio->nombre_espacio ?? '',
                        'periodo' => $plan->horario->periodo ?? '',
                    ];
                });
            });
        }

        return view('layouts.spacetime.spacetime_show', compact(
            'pisos',
            'horariosPorEspacio',
            'semestre',
            'anioActual',
            'semestresDisponibles',
            'semestreFiltro',
            'anioFiltro'
        ));
    }

    public function getHorariosPorPeriodo(Request $request)
    {
        try {
            $semestreFiltro = $request->input('semestre');
            $anioFiltro = $request->input('anio', SemesterHelper::getCurrentAcademicYear());

            if (!$semestreFiltro) {
                return response()->json(['error' => 'Se requiere semestre'], 400);
            }

            $periodo = $anioFiltro . '-' . $semestreFiltro;

            // Cargar horarios solo para el período seleccionado
            $planificaciones = Planificacion_Asignatura::with(['asignatura.profesor', 'modulo', 'espacio', 'horario'])
                ->whereHas('horario', function ($q) use ($periodo) {
                    $q->where('periodo', $periodo);
                })
                ->get();

            // Agrupar por espacio
            $horariosPorEspacio = $planificaciones->groupBy('id_espacio')->map(function ($items) {
                return $items->map(function ($plan) {
                    return [
                        'asignatura' => $plan->asignatura->nombre_asignatura ?? '',
                        'codigo_asignatura' => $plan->asignatura->codigo_asignatura ?? '',
                        'profesor' => $plan->asignatura->profesor ? [
                            'name' => $plan->asignatura->profesor->name
                        ] : null,
                        'dia' => $plan->modulo->dia ?? '',
                        'hora_inicio' => $plan->modulo->hora_inicio ?? '',
                        'hora_termino' => $plan->modulo->hora_termino ?? '',
                        'espacio' => $plan->espacio->nombre_espacio ?? '',
                        'periodo' => $plan->horario->periodo ?? '',
                    ];
                });
            });

            return response()->json([
                'horariosPorEspacio' => $horariosPorEspacio,
                'periodo' => $periodo
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener horarios por período:', [
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Error al obtener los horarios'], 500);
        }
    }

    public function exportHorarioEspacioPDF($idEspacio, Request $request)
    {
        try {
            Log::info('Iniciando exportación PDF para espacio:', ['id_espacio' => $idEspacio]);

            // Obtener el espacio con sus relaciones
            $espacio = Espacio::with(['piso.facultad'])->where('id_espacio', $idEspacio)->first();

            if (!$espacio) {
                Log::warning('Espacio no encontrado:', ['id_espacio' => $idEspacio]);
                return response()->json(['error' => 'Espacio no encontrado'], 404);
            }

            Log::info('Espacio encontrado:', ['espacio' => $espacio->toArray()]);

            // Obtener el período de los filtros o usar el actual
            $semestreFiltro = $request->input('semestre');
            $anioFiltro = $request->input('anio');

            if ($semestreFiltro && $anioFiltro) {
                $periodo = $anioFiltro . '-' . $semestreFiltro;
            } else {
                // Determinar el período actual usando el helper
                $anioActual = SemesterHelper::getCurrentAcademicYear();
                $semestre = SemesterHelper::getCurrentSemester();
                $periodo = SemesterHelper::getCurrentPeriod();
            }

            Log::info('Período determinado:', [
                'semestreFiltro' => $semestreFiltro,
                'anioFiltro' => $anioFiltro,
                'periodo' => $periodo
            ]);

            // Obtener las planificaciones del espacio
            $planificaciones = Planificacion_Asignatura::with(['asignatura.profesor', 'modulo'])
                ->where('id_espacio', $idEspacio)
                ->whereHas('horario', function ($q) use ($periodo) {
                    $q->where('periodo', $periodo);
                })
                ->get();

            Log::info('Planificaciones encontradas:', [
                'total_planificaciones' => $planificaciones->count(),
                'id_espacio' => $idEspacio,
                'periodo' => $periodo
            ]);

            // Formatear los horarios
            $horarios = $planificaciones->map(function ($plan) {
                return [
                    'asignatura' => $plan->asignatura->nombre_asignatura ?? '',
                    'codigo_asignatura' => $plan->asignatura->codigo_asignatura ?? '',
                    'profesor' => $plan->asignatura->profesor ? [
                        'name' => $plan->asignatura->profesor->name
                    ] : null,
                    'dia' => $plan->modulo->dia ?? '',
                    'hora_inicio' => $plan->modulo->hora_inicio ?? '',
                    'hora_termino' => $plan->modulo->hora_termino ?? '',
                ];
            })->toArray();

            Log::info('Horarios formateados:', [
                'total_horarios' => count($horarios)
            ]);

            // Obtener TODOS los módulos disponibles desde las 8:10 hasta el último horario
            $todosLosModulos = Modulo::orderBy('hora_inicio')->get();

            // Filtrar módulos que empiecen desde las 8:10 o después
            $modulosFiltrados = $todosLosModulos->filter(function ($modulo) {
                return $modulo->hora_inicio >= '08:10:00';
            });

            // Obtener módulos únicos y ordenarlos
            $modulosUnicos = $modulosFiltrados->map(function ($modulo) {
                return [
                    'hora_inicio' => $modulo->hora_inicio,
                    'hora_termino' => $modulo->hora_termino
                ];
            })->unique(function ($item) {
                return $item['hora_inicio'] . '-' . $item['hora_termino'];
            })->sortBy('hora_inicio')
                ->values()
                ->toArray();

            $fecha_generacion = Carbon::now()->format('d/m/Y H:i:s');
            // Fecha simple para mostrar en el encabezado del PDF
            $fecha = Carbon::now()->format('d/m/Y');

            // Preparar variables para la vista PDF (asegurar que existan)
            $moduloInicio = 1;
            $moduloFin = max(1, count($modulosUnicos));
            $modulosDia = $moduloFin - $moduloInicio + 1;

            // Preparar datos para la tabla de horarios (igual que en el modal)
            $datos = [];
            $diasSemana = ['LU', 'MA', 'MI', 'JU', 'VI', 'SA'];
            
            // Obtener módulos únicos ordenados (igual que en el modal)
            $modulosUnicos = $planificaciones->pluck('modulo.id_modulo')
                ->map(function($idModulo) {
                    return explode('.', $idModulo)[1] ?? '';
                })
                ->unique()
                ->sort(function($a, $b) {
                    return intval($a) - intval($b);
                })
                ->values();
            
            // Crear una tabla con módulos en filas y días en columnas
            foreach ($modulosUnicos as $modulo) {
                // Encontrar información del módulo
                $moduloInfo = $planificaciones->first(function($plan) use ($modulo) {
                    return explode('.', $plan->modulo->id_modulo)[1] == $modulo;
                });
                
                if (!$moduloInfo) continue;
                
                $horaInicio = substr($moduloInfo->modulo->hora_inicio, 0, 5);
                $horaTermino = substr($moduloInfo->modulo->hora_termino, 0, 5);
                $hora = $horaInicio . ' a ' . $horaTermino;
                
                $row = [
                    'hora' => $hora,
                    'modulo' => $modulo
                ];
                
                // Para cada día de la semana
                foreach ($diasSemana as $dia) {
                    // Filtrar planificaciones por día y módulo (igual que en el modal)
                    $planificacionesDia = $planificaciones->filter(function ($plan) use ($dia, $modulo) {
                        $planDia = explode('.', $plan->modulo->id_modulo)[0] ?? '';
                        $planModulo = explode('.', $plan->modulo->id_modulo)[1] ?? '';
                        return $planDia === $dia && $planModulo === $modulo;
                    });
                    
                    if ($planificacionesDia->count() > 0) {
                        // Mostrar información de las asignaturas (igual que en el modal)
                        $infoAsignaturas = $planificacionesDia->map(function ($plan) {
                            $asignatura = $plan->asignatura->nombre_asignatura ?? '';
                            $espacio = $plan->espacio->id_espacio ?? '';
                            $codigo = $plan->asignatura->codigo_asignatura ?? '';
                            return [
                                'asignatura' => $asignatura,
                                'espacio' => $espacio,
                                'codigo' => $codigo
                            ];
                        })->toArray();
                        
                        $row[$dia] = $infoAsignaturas;
                    } else {
                        $row[$dia] = null; // Libre
                    }
                }
                
                $datos[] = $row;
            }

            // Generar el PDF
            $pdf = Pdf::loadView('reportes.pdf.horarios-espacio', compact(
                'espacio',
                'horarios',
                'modulosUnicos',
                'fecha_generacion',
                'fecha',
                'moduloInicio',
                'moduloFin',
                'modulosDia',
                'datos'
            ));

            // Usar orientación horizontal para que quepan más columnas en la página
            try {
                $pdf->setPaper('a4', 'landscape');
            } catch (\Throwable $e) {
                // Algunos drivers/no configuraciones podrían no soportar setPaper; ignorar si falla
                Log::warning('No se pudo forzar orientación landscape en dompdf: ' . $e->getMessage());
            }

            // Formato del nombre: espacio_horario_2025_1.pdf
            $filename = $idEspacio . '_horario_' . str_replace('-', '_', $periodo) . '.pdf';

            Log::info('PDF generado exitosamente:', [
                'filename' => $filename,
                'total_horarios' => count($horarios),
                'total_modulos' => count($modulosUnicos)
            ]);

            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('Error al exportar horario de espacio a PDF:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'id_espacio' => $idEspacio
            ]);

            return response()->json([
                'error' => 'Error al generar el PDF: ' . $e->getMessage()
            ], 500);
        }
    }
}
