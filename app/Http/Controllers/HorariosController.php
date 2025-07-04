<?php

namespace App\Http\Controllers;

use App\Models\Horario;
use App\Models\User;
use App\Models\Planificacion_Asignatura;
use App\Models\Sede;
use App\Models\Asignatura;
use App\Models\Modulo;
use App\Models\Espacio;
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
        $query = User::role('Profesor')->with(['areaAcademica', 'facultad']);
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('run', 'like', '%' . $search . '%');
            });
        }
        // Filtro por letra inicial del apellido
        if ($request->filled('letra') && $request->letra !== 'Todos') {
            $letra = $request->letra;
            // Quitar tildes y comparar solo la primera letra del apellido
            $query->whereRaw("UPPER(REPLACE(SUBSTRING_INDEX(name, ',', 1), 'Á', 'A')) LIKE ?", [strtoupper($letra) . '%']);
        }
        $profesores = $query->orderBy('name')->paginate(27);
        $horarios = Horario::with(['docente', 'planificaciones.asignatura', 'planificaciones.espacio'])->get();
        
        // Formatear las horas de inicio y término de los módulos
        $horarios->each(function ($horario) {
            $horario->planificaciones->each(function ($planificacion) {
                if ($planificacion->modulo) {
                    $planificacion->modulo->hora_inicio = substr($planificacion->modulo->hora_inicio, 0, 5);
                    $planificacion->modulo->hora_termino = substr($planificacion->modulo->hora_termino, 0, 5);
                }
            });
        });
        
        $horarios = $horarios->groupBy('run');
        return view('layouts.schedules.schedules_index', compact('profesores', 'horarios'));
    }

    public function mostrarHorarios(Request $request)
    {
        $sedes = Sede::with(['universidad', 'facultades.pisos.mapas'])->get();
        return view('layouts.spacetime.spacetime_index', compact('sedes'));

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

    public function getHorarioProfesor($run)
    {
        try {
            $horario = Horario::with(['docente', 'planificaciones.asignatura', 'planificaciones.espacio'])
                ->where('run', $run)
                ->first();

            if (!$horario) {
                return response()->json(['error' => 'Horario no encontrado'], 404);
            }

            $asignaturas = Asignatura::where('run', $run)
                ->with(['planificaciones.espacio', 'planificaciones.modulo'])
                ->get();

            $modulos = Modulo::orderBy('hora_inicio')->get();

            Log::info('Datos del horario:', [
                'horario' => $horario->toArray(),
                'asignaturas' => $asignaturas->toArray(),
                'modulos' => $modulos->toArray()
            ]);

            return response()->json([
                'horario' => $horario,
                'asignaturas' => $asignaturas,
                'modulos' => $modulos
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

            $query = Planificacion_Asignatura::with(['asignatura.user', 'modulo', 'espacio']);

            if ($id_espacio) {
                $query->where('id_espacio', $id_espacio);
            }

            $planificaciones = $query->get();

            // Agrupar por espacio
            $horariosPorEspacio = $planificaciones->groupBy('id_espacio')->map(function ($items) {
                return $items->map(function ($plan) {
                    return [
                        'asignatura' => $plan->asignatura->nombre_asignatura ?? '',
                        'user' => $plan->asignatura->user ? [
                            'name' => $plan->asignatura->user->name
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

    public function showEspacios()
    {
        // Obtener todos los pisos con sus espacios, ordenados por número de piso
        $pisos = \App\Models\Piso::with(['espacios' => function($q) {
            $q->orderBy('nombre_espacio');
        }])->orderBy('numero_piso')->get();

        // Pre-cargar todos los horarios de todos los espacios con la relación horario
        $planificaciones = Planificacion_Asignatura::with(['asignatura.user', 'modulo', 'espacio', 'horario'])->get();

        // Agrupar por espacio
        $horariosPorEspacio = $planificaciones->groupBy('id_espacio')->map(function ($items) {
            return $items->map(function ($plan) {
                return [
                    'asignatura' => $plan->asignatura->nombre_asignatura ?? '',
                    'codigo_asignatura' => $plan->asignatura->codigo_asignatura ?? '',
                    'user' => $plan->asignatura->user ? [
                        'name' => $plan->asignatura->user->name
                    ] : null,
                    'dia' => $plan->modulo->dia ?? '',
                    'hora_inicio' => $plan->modulo->hora_inicio ?? '',
                    'hora_termino' => $plan->modulo->hora_termino ?? '',
                    'espacio' => $plan->espacio->nombre_espacio ?? '',
                    'periodo' => $plan->horario->periodo ?? '',
                ];
            });
        });

        // Obtener el período más común de los horarios existentes
        $periodos = $planificaciones->pluck('horario.periodo')->filter()->unique()->values();
        $periodoActual = $periodos->first() ?? '2025-1'; // Valor por defecto si no hay horarios
        
        // Extraer año y semestre del período (formato: "2025-1")
        $partesPeriodo = explode('-', $periodoActual);
        $anioActual = $partesPeriodo[0] ?? date('Y');
        $semestre = $partesPeriodo[1] ?? 1;

        return view('layouts.spacetime.spacetime_show', compact('pisos', 'horariosPorEspacio', 'semestre', 'anioActual'));
    }

    public function exportHorarioEspacioPDF($idEspacio)
    {
        try {
            // Obtener el espacio con sus relaciones
            $espacio = Espacio::with(['piso.facultad'])->where('id_espacio', $idEspacio)->first();
            
            if (!$espacio) {
                return response()->json(['error' => 'Espacio no encontrado'], 404);
            }

            // Obtener las planificaciones del espacio
            $planificaciones = Planificacion_Asignatura::with(['asignatura.user', 'modulo'])
                ->where('id_espacio', $idEspacio)
                ->get();

            // Formatear los horarios
            $horarios = $planificaciones->map(function ($plan) {
                return [
                    'asignatura' => $plan->asignatura->nombre_asignatura ?? '',
                    'codigo_asignatura' => $plan->asignatura->codigo_asignatura ?? '',
                    'user' => $plan->asignatura->user ? [
                        'name' => $plan->asignatura->user->name
                    ] : null,
                    'dia' => $plan->modulo->dia ?? '',
                    'hora_inicio' => $plan->modulo->hora_inicio ?? '',
                    'hora_termino' => $plan->modulo->hora_termino ?? '',
                ];
            })->toArray();

            // Obtener TODOS los módulos disponibles desde las 8:10 hasta el último horario
            $todosLosModulos = Modulo::orderBy('hora_inicio')->get();
            
            // Filtrar módulos que empiecen desde las 8:10 o después
            $modulosFiltrados = $todosLosModulos->filter(function($modulo) {
                return $modulo->hora_inicio >= '08:10:00';
            });

            // Obtener módulos únicos y ordenarlos
            $modulosUnicos = $modulosFiltrados->map(function($modulo) {
                return [
                    'hora_inicio' => $modulo->hora_inicio,
                    'hora_termino' => $modulo->hora_termino
                ];
            })->unique(function($item) {
                return $item['hora_inicio'] . '-' . $item['hora_termino'];
            })->sortBy('hora_inicio')
            ->values()
            ->toArray();

            $fecha_generacion = Carbon::now()->format('d/m/Y H:i:s');

            // Determinar el semestre actual
            $mesActual = Carbon::now()->month;
            $anioActual = Carbon::now()->year;
            $semestre = ($mesActual >= 3 && $mesActual <= 7) ? 1 : 2; // Marzo-Julio = Semestre 1, Agosto-Febrero = Semestre 2
            $periodo = $anioActual . '_' . $semestre;

            // Generar el PDF
            $pdf = Pdf::loadView('reporteria.pdf.horarios-espacio', compact(
                'espacio', 
                'horarios', 
                'modulosUnicos', 
                'fecha_generacion'
            ));

            // Formato del nombre: espacio_horario_2025_1.pdf
            $filename = $idEspacio . '_horario_' . $periodo . '.pdf';
            
            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('Error al exportar horario de espacio a PDF:', [
                'error' => $e->getMessage(),
                'id_espacio' => $idEspacio
            ]);
            
            return response()->json(['error' => 'Error al generar el PDF'], 500);
        }
    }
}
