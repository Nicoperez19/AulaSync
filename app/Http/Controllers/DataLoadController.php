<?php

namespace App\Http\Controllers;

use App\Models\DataLoad;
use App\Models\Profesor;
use App\Models\Asignatura;
use App\Models\Carrera;
use App\Models\Horario;
use App\Models\Planificacion_Asignatura;
use App\Models\Espacio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\QRService;

class DataLoadController extends Controller
{
    protected $qrService;

    public function __construct(QRService $qrService)
    {
        $this->qrService = $qrService;
    }

    public function index(Request $request)
    {
        $semestreFiltro = $request->input('semestre');
        $anioFiltro = $request->input('anio');

        $periodosDisponibles = Horario::select('periodo')
            ->whereNotNull('periodo')
            ->where('periodo', '!=', '')
            ->distinct()
            ->pluck('periodo')
            ->sort()
            ->values();

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

        sort($aniosDisponibles);
        sort($semestresDisponibles);

        $query = DataLoad::latest();

        if ($semestreFiltro && $anioFiltro) {
            $periodoFiltro = $anioFiltro . '-' . $semestreFiltro;

            $query->whereHas('profesor.horarios', function ($q) use ($periodoFiltro) {
                $q->where('periodo', $periodoFiltro);
            });
        } elseif ($anioFiltro) {
            $query->whereHas('profesor.horarios', function ($q) use ($anioFiltro) {
                $q->where('periodo', 'like', $anioFiltro . '-%');
            });
        }

        $dataLoads = $query->paginate(10);

        return view('layouts.data.data_index', compact(
            'dataLoads',
            'aniosDisponibles',
            'semestresDisponibles',
            'semestreFiltro',
            'anioFiltro'
        ));
    }

    public function upload(Request $request)
    {
        set_time_limit(300);

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'semestre_selector' => 'required|in:1,2'
        ]);

        $semestreSeleccionado = $request->input('semestre_selector');
        $anioActual = date('Y');
        $periodoSeleccionado = $anioActual . '-' . $semestreSeleccionado;

        try {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $fileExtension = $file->getClientOriginalExtension();

            $uniqueFileName = date('Y-m-d_His') . '_' . Auth::user()->run . '_' . Str::random(10) . '.' . $fileExtension;
            $path = $file->storeAs('datos_subidos', $uniqueFileName, 'public');

            $dataLoad = DataLoad::create([
                'nombre_archivo' => $fileName,
                'ruta_archivo' => $path,
                'tipo_carga' => $fileExtension,
                'registros_cargados' => 0,
                'estado' => 'pendiente',
                'user_run' => Auth::user()->run
            ]);

            $rows = Excel::toArray([], $file)[0];
            $processedUsersCount = 0;
            $processedAsignaturasCount = 0;
            $processedHorariosCount = 0;
            $errors = [];
            $skippedRows = 0;

            // Actualizar estado inicial
            $dataLoad->update([
                'estado' => 'procesando',
                'registros_cargados' => 0
            ]);

            // LIMPIEZA PREVIA: Eliminar planificaciones del período seleccionado
            Log::info('Iniciando limpieza previa del período: ' . $periodoSeleccionado);
            $horariosDelPeriodo = Horario::where('periodo', $periodoSeleccionado)->pluck('id_horario');
            $planificacionesEliminadas = Planificacion_Asignatura::whereIn('id_horario', $horariosDelPeriodo)->delete();
            Log::info('Planificaciones eliminadas del período ' . $periodoSeleccionado . ': ' . $planificacionesEliminadas);

            // Obtener la sede actual del tenant
            $tenant = \App\Models\Tenant::current();
            $sedeActual = $tenant ? \App\Models\Sede::find($tenant->sede_id) : null;
            $nombreSedeActual = $sedeActual ? strtolower(trim($sedeActual->nombre_sede)) : null;
            
            // Obtener la primera facultad de la sede para crear carreras automáticamente si es necesario
            $facultadDeLaSede = $sedeActual ? \App\Models\Facultad::where('id_sede', $sedeActual->id_sede)->first() : null;
            
            // Si no existe facultad, crear una genérica para la sede
            if ($sedeActual && !$facultadDeLaSede) {
                try {
                    $facultadDeLaSede = \App\Models\Facultad::create([
                        'id_facultad' => $sedeActual->id_sede . '_FAC',
                        'nombre_facultad' => 'Facultad de ' . $sedeActual->nombre_sede,
                        'id_sede' => $sedeActual->id_sede,
                        'id_universidad' => $sedeActual->id_universidad,
                    ]);
                    Log::info('Facultad genérica creada: ' . $facultadDeLaSede->id_facultad);
                } catch (\Exception $e) {
                    Log::error('No se pudo crear facultad genérica: ' . $e->getMessage());
                }
            }
            
            Log::info('Procesando carga masiva para sede: ' . ($nombreSedeActual ?? 'no definida'));
            Log::info('Sede ID del tenant: ' . ($tenant ? $tenant->sede_id : 'no hay tenant'));
            Log::info('Nombre de sede en DB: ' . ($sedeActual ? $sedeActual->nombre_sede : 'no encontrada'));
            Log::info('Facultad de la sede: ' . ($facultadDeLaSede ? $facultadDeLaSede->id_facultad : 'no encontrada'));

            foreach ($rows as $index => $row) {
                if ($index === 0) {
                    continue; // Saltar encabezados
                }

                try {
                    $sede = $row[7];

                    // Log de la primera fila para debug
                    if ($index === 1) {
                        Log::info('Primera fila - Sede del Excel: "' . $sede . '" (length: ' . strlen($sede) . ')');
                        Log::info('Comparando con sede actual: "' . ($nombreSedeActual ?? 'null') . '"');
                        Log::info('Coincide: ' . (strtolower(trim($sede)) === $nombreSedeActual ? 'SI' : 'NO'));
                    }

                    // Filtrar por sede actual del tenant (dinámico)
                    if ($nombreSedeActual && strtolower(trim($sede)) !== $nombreSedeActual) {
                        $skippedRows++;
                        continue;
                    }

                    $idCarrera = $row[17];

                    // Durante la inicialización o si la carrera no existe, intentar crearla o usar una genérica
                    $carrera = Carrera::find($idCarrera);
                    if (!$carrera) {
                        // Intentar crear una carrera genérica para esta sede
                        $nombreCarrera = isset($row[18]) && !empty($row[18]) ? $row[18] : 'Carrera ' . $idCarrera;
                        
                        // Necesitamos un área académica genérica para esta facultad
                        if ($facultadDeLaSede) {
                            $areaAcademicaGenerica = \App\Models\AreaAcademica::where('id_facultad', $facultadDeLaSede->id_facultad)->first();
                            
                            if (!$areaAcademicaGenerica) {
                                // Crear un área académica genérica para esta facultad
                                try {
                                    $areaAcademicaGenerica = \App\Models\AreaAcademica::create([
                                        'id_area_academica' => $facultadDeLaSede->id_facultad . '_AA',
                                        'nombre_area_academica' => 'Área Académica de ' . $facultadDeLaSede->nombre_facultad,
                                        'tipo_area_academica' => 'escuela',
                                        'id_facultad' => $facultadDeLaSede->id_facultad,
                                    ]);
                                    Log::info("Área académica genérica creada: " . $areaAcademicaGenerica->id_area_academica);
                                } catch (\Exception $e) {
                                    Log::warning("Error al crear área académica: " . $e->getMessage());
                                }
                            }
                        }
                        
                        try {
                            $carrera = Carrera::create([
                                'id_carrera' => $idCarrera,
                                'nombre' => $nombreCarrera,
                                'id_area_academica' => isset($areaAcademicaGenerica) ? $areaAcademicaGenerica->id_area_academica : null,
                            ]);
                            Log::info("Carrera creada automáticamente: " . $idCarrera . " - " . $nombreCarrera);
                        } catch (\Exception $e) {
                            $errors[] = "Fila " . ($index + 1) . ": No se pudo crear la carrera " . $idCarrera;
                            Log::warning("Error al crear carrera " . $idCarrera . ": " . $e->getMessage());
                            continue;
                        }
                    }

                    $run = $row[11];
                    $name = $row[12];
                    $email = $row[13];
                    $tipoProfesor = $row[16];
                    $existingProfesor = Profesor::where('run_profesor', $run)->first();

                    if ($existingProfesor) {
                        // ACTUALIZACIÓN COMPLETA del profesor
                        $existingProfesor->update([
                            'name' => $name,
                            'email' => $email,
                            'id_carrera' => $idCarrera,
                            'tipo_profesor' => $tipoProfesor // También actualizar tipo
                        ]);
                        $processedUsersCount++;
                    } else {
                        // Crear nuevo profesor
                        $profesor = Profesor::create([
                            'run_profesor' => $run,
                            'name' => $name,
                            'email' => $email,
                            'id_carrera' => $idCarrera,
                            'tipo_profesor' => $tipoProfesor
                        ]);
                        $processedUsersCount++;
                    }

                    $idAsignatura = $row[0];
                    $codigoAsignatura = $row[1];
                    $nombreAsignatura = preg_replace('/^[a-z]{2}:\s*/i', '', $row[2]);

                    $numeroSeccion = trim($row[3]); // Columna D
                    $inscritos = isset($row[9]) ? (int)$row[9] : null; // Columna J - Inscritos

                    // Validar que la sección sea un número de hasta 4 dígitos
                    if (!empty($numeroSeccion) && !preg_match('/^\d{1,4}$/', $numeroSeccion)) {
                        $errors[] = "Fila " . ($index + 1) . ": Sección inválida - debe ser un número de 1 a 4 dígitos (valor: " . $numeroSeccion . ")";
                        continue;
                    }

                    // Si la sección está vacía, asignar un valor por defecto
                    if (empty($numeroSeccion)) {
                        $numeroSeccion = '1';
                    }

                    $existingAsignatura = Asignatura::where('id_asignatura', $idAsignatura)->first();
                    if (!$existingAsignatura) {
                        $asignatura = Asignatura::create([
                            'id_asignatura' => $idAsignatura,
                            'codigo_asignatura' => $codigoAsignatura,
                            'nombre_asignatura' => $nombreAsignatura,
                            'seccion' => $numeroSeccion,
                            'run_profesor' => $run,
                            'id_carrera' => $idCarrera
                        ]);
                    } else {
                        // ACTUALIZACIÓN COMPLETA de la asignatura
                        $existingAsignatura->update([
                            'codigo_asignatura' => $codigoAsignatura,
                            'nombre_asignatura' => $nombreAsignatura,
                            'seccion' => $numeroSeccion,
                            'run_profesor' => $run,
                            'id_carrera' => $idCarrera
                        ]);
                        $asignatura = $existingAsignatura;
                    }

                    $processedAsignaturasCount++;

                    $horarioProfesor = $row[20];
                    $periodo = $periodoSeleccionado;

                    try {
                        $idHorario = 'HOR_' . $run . '_' . $periodo;

                        $existingHorario = Horario::where('id_horario', $idHorario)->first();

                        if (!$existingHorario) {
                            $oldIdHorario = 'HOR_' . $run;
                            $existingHorario = Horario::where('id_horario', $oldIdHorario)->first();

                            if ($existingHorario) {
                                // Migrar horario existente al nuevo formato
                                $existingHorario->id_horario = $idHorario;
                                $existingHorario->periodo = $periodo;
                                $existingHorario->save();

                                // Actualizar planificaciones asociadas
                                Planificacion_Asignatura::where('id_horario', $oldIdHorario)
                                    ->update(['id_horario' => $idHorario]);
                            }
                        }

                        if ($existingHorario) {
                            $horario = $existingHorario;
                            // ACTUALIZACIÓN COMPLETA del horario
                            $horario->update([
                                'nombre' => "Horario de " . $name,
                                'periodo' => $periodo,
                                'run_profesor' => $run
                            ]);
                        } else {
                            $horario = new Horario();
                            $horario->id_horario = $idHorario;
                            $horario->nombre = "Horario de " . $name;
                            $horario->periodo = $periodo;
                            $horario->run_profesor = $run;

                            if (!$horario->save()) {
                                throw new \Exception("Error al guardar el horario");
                            }

                            if (!$horario->id_horario) {
                                throw new \Exception("El horario no se creó correctamente");
                            }
                        }

                        if ($horario && $horario->id_horario && !empty($horarioProfesor)) {
                            $horarioProfesorNormalizado = preg_replace('/(?<!-)\s*([a-z]{2}:\s*)/i', ' - $1', $horarioProfesor);

                            // Divide por guiones
                            $horarios = explode(' - ', $horarioProfesorNormalizado);
                            foreach ($horarios as $horarioStr) {
                                if (preg_match('/^[a-záéíóúñ]{2,}:$/u', trim($horarioStr))) {
                                    continue;
                                }
                                preg_match('/([A-Za-z]+)\.(\d+)\/G:(\d+)\s*\(([^)]+)\)/', $horarioStr, $matches);

                                if (count($matches) === 5) {
                                    $dia = $matches[1];
                                    $modulo = $matches[2];
                                    $grupo = $matches[3];
                                    $espacio = preg_replace('/^[a-z]{2}:\s*/i', '', $matches[4]);

                                    $espacioExiste = Espacio::where('id_espacio', $espacio)->exists();

                                    if (!$espacioExiste) {
                                        continue; // Saltar esta planificación si el espacio no existe
                                    }

                                    // CREAR planificación (ya se hizo limpieza previa)
                                        $planificacion = new Planificacion_Asignatura();
                                        $planificacion->id_asignatura = $idAsignatura;
                                        $planificacion->id_horario = $horario->id_horario;
                                        $planificacion->id_modulo = $dia . '.' . $modulo;
                                        $planificacion->id_espacio = $espacio;
                                        $planificacion->inscritos = $inscritos;

                                        if (!$planificacion->save()) {
                                            throw new \Exception("Error al guardar la planificación");
                                        }

                                        $processedHorariosCount++;
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        $errors[] = "Fila " . ($index + 1) . ": Error al procesar horario - " . $e->getMessage();
                        continue;
                    }

                } catch (\Exception $e) {
                    $errorMsg = "Fila " . ($index + 1) . ": " . $e->getMessage();
                    $errors[] = $errorMsg;
                }
            }

            $dataLoad->update([
                'estado' => 'completado',
                'registros_cargados' => $processedUsersCount + $processedAsignaturasCount + $processedHorariosCount
            ]);

            $message = 'Archivo procesado exitosamente. Se procesaron ' . $processedUsersCount . ' profesores, ' .
                $processedAsignaturasCount . ' asignaturas y ' . $processedHorariosCount . ' horarios.';
            if (!empty($errors)) {
                $message .= ' Se encontraron ' . count($errors) . ' errores: ' . implode(', ', $errors);
            }

            return response()->json([
                'message' => $message,
                'data' => [
                    'nombre_archivo' => $dataLoad,
                    'profesores_procesados' => $processedUsersCount,
                    'asignaturas_procesadas' => $processedAsignaturasCount,
                    'horarios_procesados' => $processedHorariosCount,
                    'filas_omitidas' => $skippedRows,
                    'errores' => $errors
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al procesar el archivo: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(DataLoad $dataLoad)
    {
        try {
            if (Storage::exists($dataLoad->ruta_archivo)) {
                Storage::delete($dataLoad->ruta_archivo);
            }

            $dataLoad->delete();

            return redirect()->route('data.index')
                ->with('success', 'Registro de carga eliminado exitosamente.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al eliminar el registro de carga.']);
        }
    }

    public function detalleJson($id)
    {
        $dataLoad = DataLoad::with('user')->findOrFail($id);

        // Calcula el tamaño del archivo en MB
        $tamano = null;
        if ($dataLoad->ruta_archivo && \Storage::disk('public')->exists($dataLoad->ruta_archivo)) {
            $tamano = round(\Storage::disk('public')->size($dataLoad->ruta_archivo) / 1024 / 1024, 1) . ' MB';
        }

        return response()->json([
            'id' => $dataLoad->id,
            'nombre_archivo' => $dataLoad->nombre_archivo,
            'estado' => $dataLoad->estado,
            'registros_cargados' => $dataLoad->registros_cargados,
            'tamano' => $tamano,
            'tipo_carga' => $dataLoad->tipo_carga,
            'ruta_archivo' => $dataLoad->ruta_archivo,
            'usuario_nombre' => $dataLoad->user->name ?? '',
            'usuario_run' => $dataLoad->user->run ?? '',
            'fecha_carga' => $dataLoad->created_at ? $dataLoad->created_at->format('d/m/Y H:i:s') : '',
            'fecha_actualizacion' => $dataLoad->updated_at ? $dataLoad->updated_at->format('d/m/Y H:i:s') : '',
            'url_descarga' => $dataLoad->ruta_archivo ? \Storage::disk('public')->url($dataLoad->ruta_archivo) : '',
        ]);
    }

    public function download($id)
    {
        $dataLoad = DataLoad::findOrFail($id);

        if (!$dataLoad->ruta_archivo || !Storage::disk('public')->exists($dataLoad->ruta_archivo)) {
            return back()->withErrors(['error' => 'El archivo no existe o ha sido eliminado.']);
        }

        return Storage::disk('public')->download($dataLoad->ruta_archivo, $dataLoad->nombre_archivo);
    }

    public function progress($id)
    {
        try {
            $dataLoad = DataLoad::findOrFail($id);

            return response()->json([
                'estado' => $dataLoad->estado,
                'registros_cargados' => $dataLoad->registros_cargados,
                'mensaje' => $this->getEstadoMensaje($dataLoad->estado)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'estado' => 'error',
                'mensaje' => 'Error al obtener el progreso'
            ], 500);
        }
    }

    private function getEstadoMensaje($estado)
    {
        switch ($estado) {
            case 'pendiente':
                return 'Archivo en cola de procesamiento';
            case 'procesando':
                return 'Procesando archivo...';
            case 'completado':
                return 'Procesamiento completado exitosamente';
            case 'error':
                return 'Error en el procesamiento';
            default:
                return 'Estado desconocido';
        }
    }
}
