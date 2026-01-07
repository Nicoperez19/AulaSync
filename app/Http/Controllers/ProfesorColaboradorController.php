<?php

namespace App\Http\Controllers;

use App\Models\ProfesorColaborador;
use App\Models\PlanificacionProfesorColaborador;
use App\Models\Profesor;
use App\Models\Asignatura;
use App\Models\Espacio;
use App\Models\Modulo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProfesorColaboradorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ProfesorColaborador::with(['profesor', 'asignatura', 'planificaciones.modulo', 'planificaciones.espacio']);

        // Filtros
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('vigencia')) {
            if ($request->vigencia === 'vigentes') {
                $query->vigentes();
            } elseif ($request->vigencia === 'vencidos') {
                $query->vencidos();
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre_asignatura_temporal', 'like', "%{$search}%")
                  ->orWhereHas('profesor', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('asignatura', function($q2) use ($search) {
                      $q2->where('nombre_asignatura', 'like', "%{$search}%");
                  });
            });
        }

        $profesoresColaboradores = $query->orderBy('created_at', 'desc')->paginate(15);

        $profesores = Profesor::orderBy('name')->get();

        return view('layouts.clases_temporales.index', compact('profesoresColaboradores', 'profesores'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $profesores = Profesor::orderBy('name')->get();
        $asignaturas = Asignatura::with('carrera')->orderBy('nombre_asignatura')->get();
        $espacios = Espacio::with('piso')->orderBy('nombre_espacio')->get();

        return view('layouts.clases_temporales.create', compact('profesores', 'asignaturas', 'espacios'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate based on profesor_option
        $validationRules = [
            'nombre_asignatura_temporal' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'cantidad_inscritos' => 'required|integer|min:1',
            'tipo_clase' => 'required|in:temporal,reforzamiento,recuperacion',
            'fecha_inicio' => 'required|date',
            'fecha_termino' => 'required|date|after_or_equal:fecha_inicio',
            'id_asignatura' => 'nullable|exists:asignaturas,id_asignatura',
            'planificaciones' => 'required|json',
        ];

        if ($request->profesor_option === 'nuevo') {
            $validationRules['nuevo_run'] = 'required|unique:profesors,run_profesor';
            $validationRules['nuevo_nombre'] = 'required|string|max:255';
            $validationRules['nuevo_email'] = 'required|email|unique:profesors,email';
            $validationRules['nuevo_celular'] = 'nullable|string|max:20';
        } else {
            $validationRules['run_profesor_colaborador'] = 'required|exists:profesors,run_profesor';
        }

        $request->validate($validationRules);

        try {
            DB::beginTransaction();

            $runProfesor = null;

            // If creating new profesor
            if ($request->profesor_option === 'nuevo') {
                $nuevoProfesor = Profesor::create([
                    'run_profesor' => $request->nuevo_run,
                    'name' => $request->nuevo_nombre,
                    'email' => $request->nuevo_email,
                    'celular' => $request->nuevo_celular,
                    'password' => bcrypt('password123'), // Default password
                ]);
                $runProfesor = $nuevoProfesor->run_profesor;
            } else {
                $runProfesor = $request->run_profesor_colaborador;
            }

            // Crear el profesor colaborador
            $profesorColaborador = ProfesorColaborador::create([
                'run_profesor_colaborador' => $runProfesor,
                'id_asignatura' => $request->id_asignatura,
                'nombre_asignatura_temporal' => $request->nombre_asignatura_temporal,
                'descripcion' => $request->descripcion,
                'cantidad_inscritos' => $request->cantidad_inscritos,
                'tipo_clase' => $request->tipo_clase,
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_termino' => $request->fecha_termino,
                'estado' => 'activo',
            ]);

            // Decode planificaciones JSON
            $planificaciones = json_decode($request->planificaciones, true);

            // Crear las planificaciones
            foreach ($planificaciones as $planificacion) {
                PlanificacionProfesorColaborador::create([
                    'id_profesor_colaborador' => $profesorColaborador->id,
                    'id_modulo' => $planificacion['id_modulo'],
                    'id_espacio' => $planificacion['id_espacio'],
                ]);
            }

            DB::commit();

            return redirect()->route('clases-temporales.index')
                ->with('success', 'Profesor colaborador creado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear profesor colaborador: ' . $e->getMessage());
            
            return back()->withInput()
                ->with('error', 'Error al crear el profesor colaborador: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ProfesorColaborador $profesorColaborador)
    {
        $profesorColaborador->load(['profesor', 'asignatura', 'planificaciones.modulo', 'planificaciones.espacio.piso']);

        return view('layouts.clases_temporales.show', compact('profesorColaborador'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProfesorColaborador $profesorColaborador)
    {
        $profesorColaborador->load(['planificaciones.modulo', 'planificaciones.espacio']);
        $profesores = Profesor::orderBy('name')->get();
        $asignaturas = Asignatura::with('carrera')->orderBy('nombre_asignatura')->get();
        $espacios = Espacio::with('piso')->orderBy('nombre_espacio')->get();
        
        // Obtener módulos para pasar a la vista
        $modulos = [
            1 => '08:10 - 09:00', 2 => '09:10 - 10:00', 3 => '10:10 - 11:00',
            4 => '11:10 - 12:00', 5 => '12:10 - 13:00', 6 => '13:10 - 14:00',
            7 => '14:10 - 15:00', 8 => '15:10 - 16:00', 9 => '16:10 - 17:00',
            10 => '17:10 - 18:00', 11 => '18:10 - 19:00', 12 => '19:10 - 20:00',
            13 => '20:10 - 21:00', 14 => '21:10 - 22:00', 15 => '22:10 - 23:00',
        ];

        return view('layouts.clases_temporales.edit', compact('profesorColaborador', 'profesores', 'asignaturas', 'espacios', 'modulos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProfesorColaborador $profesorColaborador)
    {
        // Si planificaciones viene como JSON string, decodificar
        $planificaciones = $request->planificaciones;
        if (is_string($planificaciones)) {
            $planificaciones = json_decode($planificaciones, true);
        }

        $request->merge(['planificaciones' => $planificaciones]);

        $request->validate([
            'run_profesor_colaborador' => 'required|exists:profesors,run_profesor',
            'nombre_asignatura_temporal' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'tipo_clase' => 'required|in:temporal,reforzamiento,recuperacion',
            'fecha_inicio' => 'required|date',
            'fecha_termino' => 'required|date|after_or_equal:fecha_inicio',
            'id_asignatura' => 'nullable|exists:asignaturas,id_asignatura',
            'estado' => 'required|in:activo,inactivo',
            'planificaciones' => 'required|array|min:1',
            'planificaciones.*.id_modulo' => 'required|exists:modulos,id_modulo',
            'planificaciones.*.id_espacio' => 'required|exists:espacios,id_espacio',
        ]);

        try {
            DB::beginTransaction();

            // Actualizar el profesor colaborador
            $profesorColaborador->update([
                'run_profesor_colaborador' => $request->run_profesor_colaborador,
                'id_asignatura' => $request->id_asignatura,
                'nombre_asignatura_temporal' => $request->nombre_asignatura_temporal,
                'descripcion' => $request->descripcion,
                'tipo_clase' => $request->tipo_clase,
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_termino' => $request->fecha_termino,
                'estado' => $request->estado,
            ]);

            // Eliminar planificaciones existentes
            $profesorColaborador->planificaciones()->delete();

            // Crear nuevas planificaciones
            foreach ($request->planificaciones as $planificacion) {
                PlanificacionProfesorColaborador::create([
                    'id_profesor_colaborador' => $profesorColaborador->id,
                    'id_modulo' => $planificacion['id_modulo'],
                    'id_espacio' => $planificacion['id_espacio'],
                ]);
            }

            DB::commit();

            return redirect()->route('clases-temporales.index')
                ->with('success', 'Profesor colaborador actualizado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar profesor colaborador: ' . $e->getMessage());
            
            return back()->withInput()
                ->with('error', 'Error al actualizar el profesor colaborador: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProfesorColaborador $profesorColaborador)
    {
        try {
            $profesorColaborador->delete();

            return redirect()->route('clases-temporales.index')
                ->with('success', 'Profesor colaborador eliminado exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar profesor colaborador: ' . $e->getMessage());
            
            return back()->with('error', 'Error al eliminar el profesor colaborador: ' . $e->getMessage());
        }
    }

    /**
     * Obtener horarios ocupados para un día específico (API)
     */
    public function getHorariosOcupados(Request $request)
    {
        $fecha = $request->input('fecha', Carbon::today()->toDateString());
        $idEspacio = $request->input('id_espacio');

        try {
            $fechaCarbon = Carbon::parse($fecha);
            $dias = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
            $diaActual = $dias[$fechaCarbon->dayOfWeek];

            $diasPrefijos = [
                'lunes' => 'LU',
                'martes' => 'MA',
                'miercoles' => 'MI',
                'jueves' => 'JU',
                'viernes' => 'VI',
                'sabado' => 'SA',
            ];

            $prefijoDia = $diasPrefijos[$diaActual] ?? '';

            if (!$prefijoDia) {
                return response()->json([
                    'success' => true,
                    'ocupados' => [],
                    'message' => 'No hay clases programadas para este día'
                ]);
            }

            // Obtener módulos ocupados del espacio específico o todos
            $queryBase = Modulo::where('dia', $diaActual);
            
            if ($idEspacio) {
                $queryBase->where(function($q) use ($idEspacio, $prefijoDia) {
                    // Planificaciones normales
                    $q->whereHas('planificaciones', function($q2) use ($idEspacio) {
                        $q2->where('id_espacio', $idEspacio);
                    })
                    // Planificaciones de colaboradores
                    ->orWhereHas('planificacionesColaboradores', function($q2) use ($idEspacio) {
                        $q2->where('id_espacio', $idEspacio)
                           ->whereHas('profesorColaborador', function($q3) {
                               $q3->activosYVigentes();
                           });
                    });
                });
            }

            $modulosOcupados = $queryBase->get()->map(function($modulo) {
                $parts = explode('.', $modulo->id_modulo);
                return [
                    'id_modulo' => $modulo->id_modulo,
                    'numero' => isset($parts[1]) ? (int)$parts[1] : 0,
                    'hora_inicio' => substr($modulo->hora_inicio, 0, 5),
                    'hora_termino' => substr($modulo->hora_termino, 0, 5),
                ];
            });

            return response()->json([
                'success' => true,
                'ocupados' => $modulosOcupados,
                'dia' => $diaActual,
                'prefijo' => $prefijoDia
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener horarios ocupados: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener horarios: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available rooms for the selected schedule
     * POST /api/profesores-colaboradores/salas-disponibles
     */
    public function getSalasDisponibles(Request $request)
    {
        try {
            $modulos = $request->input('modulos', []);
            
            if (empty($modulos)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se especificaron módulos'
                ], 400);
            }

            // Get all spaces excluding "Sala de Reuniones" and "Sala de Estudio"
            $espacios = Espacio::with(['piso'])
                ->whereNotIn('tipo_espacio', ['Sala de Reuniones', 'Sala de Estudio'])
                ->orderBy('nombre_espacio')
                ->get();

            $salasConOcupacion = [];
            $salasDescartadas = [];

            foreach ($espacios as $espacio) {
                // Count total conflicts
                $conflictos = 0;
                $conflictosDetalle = [];
                
                foreach ($modulos as $idModulo) {
                    // Check planificaciones normales
                    $planificacionNormal = DB::connection('tenant')->table('planificacion_asignaturas as pa')
                        ->join('asignaturas as a', 'pa.id_asignatura', '=', 'a.id_asignatura')
                        ->join('modulos as m', 'pa.id_modulo', '=', 'm.id_modulo')
                        ->where('pa.id_modulo', $idModulo)
                        ->where('pa.id_espacio', $espacio->id_espacio)
                        ->select('a.nombre_asignatura', 'm.hora_inicio', 'm.hora_termino', 'm.dia')
                        ->first();

                    // Check planificaciones colaboradores
                    $planificacionColaborador = DB::connection('tenant')->table('planificaciones_profesores_colaboradores as ppc')
                        ->join('profesores_colaboradores as pc', 'ppc.id_profesor_colaborador', '=', 'pc.id')
                        ->join('modulos as m', 'ppc.id_modulo', '=', 'm.id_modulo')
                        ->where('ppc.id_modulo', $idModulo)
                        ->where('ppc.id_espacio', $espacio->id_espacio)
                        ->where('pc.estado', 'activo')
                        ->where('pc.fecha_inicio', '<=', now())
                        ->where('pc.fecha_termino', '>=', now())
                        ->select('pc.nombre_asignatura_temporal as nombre_asignatura', 'm.hora_inicio', 'm.hora_termino', 'm.dia')
                        ->first();

                    if ($planificacionNormal) {
                        $conflictos++;
                        $conflictosDetalle[] = [
                            'asignatura' => $planificacionNormal->nombre_asignatura,
                            'dia' => $planificacionNormal->dia,
                            'hora_inicio' => $planificacionNormal->hora_inicio,
                            'hora_fin' => $planificacionNormal->hora_termino,
                            'tipo' => 'Asignatura Regular'
                        ];
                    }

                    if ($planificacionColaborador) {
                        $conflictos++;
                        $conflictosDetalle[] = [
                            'asignatura' => $planificacionColaborador->nombre_asignatura,
                            'dia' => $planificacionColaborador->dia,
                            'hora_inicio' => $planificacionColaborador->hora_inicio,
                            'hora_fin' => $planificacionColaborador->hora_termino,
                            'tipo' => 'Temporal'
                        ];
                    }
                }

                // Calculate occupation percentages
                $totalPlanificaciones = DB::connection('tenant')->table('planificacion_asignaturas')
                    ->where('id_espacio', $espacio->id_espacio)
                    ->count();

                $totalColaboradores = DB::connection('tenant')->table('planificaciones_profesores_colaboradores as ppc')
                    ->join('profesores_colaboradores as pc', 'ppc.id_profesor_colaborador', '=', 'pc.id')
                    ->where('ppc.id_espacio', $espacio->id_espacio)
                    ->where('pc.estado', 'activo')
                    ->where('pc.fecha_inicio', '<=', now())
                    ->where('pc.fecha_termino', '>=', now())
                    ->count();

                $totalOcupaciones = $totalPlanificaciones + $totalColaboradores;
                
                // Total possible slots (5 days * 15 modules = 75)
                $totalPosibles = 75;
                $porcentajePlanificacion = $totalPosibles > 0 ? round(($totalOcupaciones / $totalPosibles) * 100, 1) : 0;
                
                // Real usage percentage (physical occupation)
                $capacidadMaxima = $espacio->capacidad_maxima > 0 ? $espacio->capacidad_maxima : ($espacio->puestos_disponibles ?? 1);
                $puestosOcupados = $capacidadMaxima - ($espacio->puestos_disponibles ?? 0);
                $porcentajeReal = $capacidadMaxima > 0 ? round(($puestosOcupados / $capacidadMaxima) * 100, 1) : 0;

                $salaInfo = [
                    'id_espacio' => $espacio->id_espacio,
                    'nombre_espacio' => $espacio->nombre_espacio,
                    'piso_nombre' => $espacio->piso ? 'Piso ' . $espacio->piso->numero_piso : null,
                    'tipo_espacio' => $espacio->tipo_espacio ?? 'N/A',
                    'capacidad_maxima' => $capacidadMaxima,
                    'porcentaje_planificacion' => $porcentajePlanificacion,
                    'porcentaje_real' => $porcentajeReal,
                    'ocupacion_total' => $totalOcupaciones,
                    'es_especifica' => in_array($espacio->tipo_espacio, ['Auditorio', 'Laboratorio', 'Taller'])
                ];

                // Only include if no conflicts
                if ($conflictos === 0) {
                    $salasConOcupacion[] = $salaInfo;
                } else {
                    $salaInfo['conflictos'] = $conflictosDetalle;
                    $salasDescartadas[] = $salaInfo;
                }
            }

            // Sort by occupation percentage (lower first)
            usort($salasConOcupacion, function($a, $b) {
                return $a['porcentaje_planificacion'] <=> $b['porcentaje_planificacion'];
            });

            return response()->json([
                'success' => true,
                'salas' => $salasConOcupacion,
                'salas_descartadas' => $salasDescartadas
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener salas disponibles: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener salas disponibles: ' . $e->getMessage()
            ], 500);
        }
    }
}
