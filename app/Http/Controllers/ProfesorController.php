<?php

namespace App\Http\Controllers;

use App\Models\Profesor;
use App\Models\Universidad;
use App\Models\Facultad;
use App\Models\Carrera;
use App\Models\Reserva;
use App\Models\Asignatura;
use App\Models\AreaAcademica;
use App\Models\Tenant;
use Illuminate\Http\Request;
use App\Models\Espacio;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class ProfesorController extends Controller
{
    /**
     * Crear reserva para profesor
     */
    public function index()
    {
        try {
            $universidades = Universidad::all();
            $facultades = Facultad::with('sede.universidad')->get();
            $carreras = Carrera::with('areaAcademica.facultad.sede.universidad')->get();
            $areasAcademicas = AreaAcademica::with('facultad.sede.universidad')->get();

            return view('layouts.professor.professor_index', compact('universidades', 'facultades', 'carreras', 'areasAcademicas'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Hubo un problema al cargar los profesores.'])->withInput();
        }
    }
    /**
     * Crear reserva para profesor
     */
    public function crearReservaProfesor(Request $request)
    {
        try {
            // Establecer contexto del tenant desde el request
            $this->establecerContextoTenant();

            $request->validate([
                'run_profesor' => 'required|string',
                'id_espacio' => 'required|string'
            ]);

            $runProfesor = $request->input('run_profesor');
            $idEspacio = $request->input('id_espacio');

            // Verificar que el espacio existe
            $espacio = Espacio::where('id_espacio', $idEspacio)->first();
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

            // Validar horario académico
            $hora = (int)now()->format('H');
            $minutos = (int)now()->format('i');
            $horaEnMinutos = $hora * 60 + $minutos;

            $inicioAcademico = 8 * 60 + 10; // 08:10
            $finAcademico = 23 * 60; // 23:00

            if ($horaEnMinutos < $inicioAcademico || $horaEnMinutos >= $finAcademico) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'No se pueden crear reservas fuera del horario académico (08:10 - 23:00).'
                ], 400);
            }

            // Verificar si el profesor existe
            $profesor = Profesor::where('run_profesor', $runProfesor)->first();
            if (!$profesor) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Profesor no encontrado'
                ], 404);
            }

            // Verificar si ya tiene una reserva activa
            $reservaExistente = Reserva::where('run_profesor', $runProfesor)
                ->where('estado', 'activa')
                ->whereNull('hora_salida')
                ->first();

            if ($reservaExistente) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Ya tienes una reserva activa en otro espacio'
                ], 400);
            }

            // Obtener la asignatura principal del profesor (primera asignatura activa)
            $asignatura = $profesor->asignaturas()->first();

            // Crear la reserva
            $reserva = new Reserva();
            $reserva->id_reserva = Reserva::generarIdUnico();
            $reserva->run_profesor = $runProfesor;
            $reserva->id_espacio = $espacio->id_espacio;
            $reserva->id_asignatura = $asignatura ? $asignatura->id_asignatura : null;
            $reserva->fecha_reserva = $fechaActual;
            $reserva->hora = $horaActual;
            $reserva->estado = 'activa';
            $reserva->save();

            // Cambiar estado del espacio
            $espacio->estado = 'Ocupado';
            $espacio->save();

            // Limpiar registros incorrectos de clases no realizadas (mover a atrasos)
            \App\Models\ClaseNoRealizada::limpiarRegistrosIncorrectos(
                $espacio->id_espacio,
                $fechaActual,
                $horaActual,
                $profesor->run_profesor
            );

            return response()->json([
                'success' => true,
                'mensaje' => 'Reserva creada exitosamente para el profesor',
                'reserva' => [
                    'id' => $reserva->id_reserva,
                    'profesor' => $profesor->name,
                    'run_profesor' => $profesor->run_profesor,
                    'espacio' => $espacio->nombre_espacio,
                    'fecha' => $fechaActual,
                    'hora_inicio' => $horaActual
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error de validación al crear reserva de profesor: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'mensaje' => 'Error de validación en los datos enviados',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al crear reserva de profesor: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al crear reserva: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'run_profesor' => 'required|integer|digits_between:7,8|unique:profesors,run_profesor',
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:profesors,email',
                'celular' => 'nullable|string|regex:/^9\d{8}$/',
                'direccion' => 'nullable|string|max:255',
                'fecha_nacimiento' => 'nullable|date',
                'anio_ingreso' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
                'tipo_profesor' => 'required|in:Profesor Colaborador,Profesor Responsable,Ayudante',
                'id_universidad' => 'nullable|exists:universidades,id_universidad',
                'id_facultad' => 'nullable|exists:facultades,id_facultad',
                'id_carrera' => 'nullable|exists:carreras,id_carrera',
                'id_area_academica' => 'nullable|exists:area_academicas,id_area_academica',
            ]);

            Profesor::create($validatedData);

            return redirect()->route('professors.index')->with('success', 'Profesor creado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Hubo un problema al crear el profesor. Por favor, intente nuevamente.'])->withInput();
        }
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
        try {
            $profesor = Profesor::findOrFail($id);
            $universidades = Universidad::all();
            $facultades = Facultad::with('sede.universidad')->get();
            $carreras = Carrera::with('areaAcademica.facultad.sede.universidad')->get();
            $areasAcademicas = AreaAcademica::with('facultad.sede.universidad')->get();
            
            // Cargar clases temporales donde es colaborador
            $clasesTemporales = $profesor->clasesTemporalesColaborador()
                ->with(['asignatura', 'planificaciones.modulo'])
                ->orderBy('fecha_termino', 'desc')
                ->get();

            return view('layouts.professor.professor_edit', compact('profesor', 'universidades', 'facultades', 'carreras', 'areasAcademicas', 'clasesTemporales'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('professors.index')->withErrors(['error' => 'Profesor no encontrado.']);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al cargar el profesor: ' . $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $request->validate([
                'run_profesor' => 'required|string',
                'id_espacio' => 'required|string'
            ]);

            $runProfesor = $request->input('run_profesor');
            $idEspacio = $request->input('id_espacio');

            // Verificar que el espacio existe
            $espacio = Espacio::where('id_espacio', $idEspacio)->first();
            if (!$espacio) {
                return redirect()->route('professors.index')->withErrors(['error' => 'Espacio no encontrado.']);
            }

            // Aquí iría la lógica de actualización del profesor
            return redirect()->route('professors.index')->with('success', 'Profesor actualizado exitosamente.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('professors.index')->withErrors(['error' => 'Profesor no encontrado.']);
        } catch (\Exception $e) {
            return redirect()->route('professors.index')->withErrors(['error' => 'Error al actualizar el profesor.']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $profesor = Profesor::findOrFail($id);
            $profesor->delete();
            return redirect()->route('professors.index')->with('success', 'Profesor eliminado exitosamente.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Profesor no encontrado.'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al borrar el profesor: ' . $e->getMessage()], 500);
        }
    }

    /**
     * API method for getting profesor information (existing method)
     */
    public function getProfesor($run)
    {
        try {
            $profesor = Profesor::where('run_profesor', $run)->first();
            if (!$profesor) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Profesor no encontrado'
                ], 404);
            }

            // Aquí deberías obtener el espacio, por ejemplo por id_espacio (ajusta según tu lógica):
            $espacio = Espacio::where('id_espacio', $profesor->id_espacio ?? null)->first();
            if (!$espacio) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Espacio no encontrado'
                ], 404);
            }

            if ($espacio->estado !== 'Disponible') {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'El espacio no está disponible'
                ], 400);
            }

            $horaActual = now()->format('H:i:s');
            $fechaActual = now()->format('Y-m-d');

            // Validar horario académico
            $hora = (int)now()->format('H');
            $minutos = (int)now()->format('i');
            $horaEnMinutos = $hora * 60 + $minutos;

            $inicioAcademico = 8 * 60 + 10; // 08:10
            $finAcademico = 23 * 60; // 23:00

            if ($horaEnMinutos < $inicioAcademico || $horaEnMinutos >= $finAcademico) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'No se pueden crear reservas fuera del horario académico (08:10 - 23:00).'
                ], 400);
            }

            // Verificar si ya tiene una reserva activa
            $reservaExistente = Reserva::where('run_profesor', $run)
                ->where('estado', 'activa')
                ->whereNull('hora_salida')
                ->first();

            if ($reservaExistente) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Ya tienes una reserva activa en otro espacio'
                ], 400);
            }

            // Obtener profesor y su asignatura principal
            $profesor = Profesor::where('run_profesor', $run)->first();
            $asignatura = $profesor ? $profesor->asignaturas()->first() : null;

            // Crear la reserva
            $reserva = new Reserva();
            $reserva->id_reserva = Reserva::generarIdUnico();
            $reserva->run_profesor = $run;
            $reserva->id_espacio = $espacio->id_espacio;
            $reserva->id_asignatura = $asignatura ? $asignatura->id_asignatura : null;
            $reserva->fecha_reserva = $fechaActual;
            $reserva->hora = $horaActual;
            $reserva->estado = 'activa';
            $reserva->save();

            // Cambiar estado del espacio
            $espacio->estado = 'Ocupado';
            $espacio->save();

            // Limpiar registros incorrectos de clases no realizadas (mover a atrasos)
            \App\Models\ClaseNoRealizada::limpiarRegistrosIncorrectos(
                $espacio->id_espacio,
                $fechaActual,
                $horaActual,
                $profesor->run_profesor
            );

            return response()->json([
                'success' => true,
                'mensaje' => 'Reserva creada exitosamente para el profesor',
                'reserva' => [
                    'id' => $reserva->id_reserva,
                    'profesor' => $profesor->name,
                    'run_profesor' => $profesor->run_profesor,
                    'espacio' => $espacio->nombre_espacio,
                    'fecha' => $fechaActual,
                    'hora_inicio' => $horaActual
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error de validación al crear reserva de profesor: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'mensaje' => 'Error de validación en los datos enviados',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al crear reserva de profesor: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al crear reserva: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener asignaturas de un profesor
     * Asegura que SIEMPRE se retornen las asignaturas con eager loading
     */
    public function getAsignaturasProfesor($run)
    {
        try {            // Establecer contexto del tenant desde el request
            $this->establecerContextoTenant();
            Log::info('📚 Buscando asignaturas para profesor: ' . $run);

            // Buscar profesor con eager loading de asignaturas
            // IMPORTANTE: Incluir run_profesor en el select para que funcione el eager loading
            $profesor = Profesor::with(['asignaturas' => function($query) {
                $query->select('asignaturas.id_asignatura', 'asignaturas.codigo_asignatura', 'asignaturas.nombre_asignatura', 'asignaturas.run_profesor')
                      ->orderBy('asignaturas.codigo_asignatura');
            }])->where('run_profesor', $run)->first();

            if (!$profesor) {
                Log::warning('⚠️ Profesor no encontrado: ' . $run);
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Profesor no encontrado'
                ], 404);
            }

            // Obtener asignaturas - usar la relación cargada
            $asignaturas = $profesor->asignaturas;

            Log::info('✅ Asignaturas encontradas: ' . $asignaturas->count() . ' para profesor ' . $run);

            return response()->json([
                'success' => true,
                'asignaturas' => $asignaturas,
                'count' => $asignaturas->count(),
                'profesor' => [
                    'run' => $profesor->run_profesor,
                    'nombre' => $profesor->name
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Error al obtener asignaturas del profesor: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al obtener asignaturas: ' . $e->getMessage(),
                'asignaturas' => []
            ], 500);
        }
    }

    /**
     * Establece el contexto del tenant desde el request
     */
    private function establecerContextoTenant()
    {
        $tenantId = tenant_id() ?? null;
        
        if (!$tenantId) {
            $host = request()->getHost();
            $tenant = Tenant::where('domain', $host)
                ->orWhere('domain', 'LIKE', '%' . $host . '%')
                ->first();
            
            if ($tenant) {
                Config::set('database.connections.tenant.database', $tenant->database);
                DB::purge('tenant');
                Log::info('Tenant establecido en ProfesorController', [
                    'tenant' => $tenant->name,
                    'database' => $tenant->database,
                    'host' => $host
                ]);
            }
        }
    }
}
