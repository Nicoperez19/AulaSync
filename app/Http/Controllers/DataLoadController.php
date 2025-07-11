<?php

namespace App\Http\Controllers;

use App\Models\DataLoad;
use App\Models\Profesor;
use App\Models\Asignatura;
use App\Models\Carrera;
use App\Models\Seccion;
use App\Models\Horario;
use App\Models\Planificacion_Asignatura;
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
        // Obtener filtros de la request
        $semestreFiltro = $request->input('semestre');
        $anioFiltro = $request->input('anio');
        
        // Obtener todos los períodos únicos de los horarios para los filtros
        $periodosDisponibles = Horario::select('periodo')
            ->whereNotNull('periodo')
            ->where('periodo', '!=', '')
            ->distinct()
            ->pluck('periodo')
            ->sort()
            ->values();
        
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
        
        // Construir la consulta base
        $query = DataLoad::latest();
        
        // Aplicar filtros si están presentes
        if ($semestreFiltro && $anioFiltro) {
            $periodoFiltro = $anioFiltro . '-' . $semestreFiltro;
            
            // Filtrar por DataLoads que tengan horarios con el período específico
            $query->whereHas('profesor.horarios', function($q) use ($periodoFiltro) {
                $q->where('periodo', $periodoFiltro);
            });
        } elseif ($anioFiltro) {
            // Filtrar solo por año
            $query->whereHas('profesor.horarios', function($q) use ($anioFiltro) {
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
        Log::info('Iniciando proceso de carga de archivo');

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240'
        ]);

        try {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $fileExtension = $file->getClientOriginalExtension();

            Log::info('Archivo recibido', [
                'nombre' => $fileName,
                'extension' => $fileExtension
            ]);

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

            Log::info('Registro de carga creado', ['id' => $dataLoad->id]);

            $rows = Excel::toArray([], $file)[0];
            Log::info('Archivo Excel leído', ['total_filas' => count($rows)]);
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

            foreach ($rows as $index => $row) {
                if ($index === 0) {
                    Log::info('Saltando encabezados del archivo');
                    continue;
                }

                try {
                    $sede = $row[7];
                    Log::info('Procesando fila', [
                        'numero_fila' => $index + 1,
                        'sede' => $sede
                    ]);

                    if (strtolower(trim($sede)) !== 'talcahuano') {
                        $skippedRows++;
                        continue;
                    }

                    $idCarrera = $row[17];
                    Log::info('Procesando registro de Talcahuano', [
                        'numero_fila' => $index + 1,
                        'id_carrera' => $idCarrera
                    ]);

                    if (!Carrera::find($idCarrera)) {
                        $errors[] = "Fila " . ($index + 1) . ": La carrera con ID " . $idCarrera . " no existe";
                        continue;
                    }

                    $run = $row[11];
                    $name = $row[12];
                    $email = $row[13];
                    $tipoProfesor = $row[16];
                    $existingProfesor = Profesor::where('run_profesor', $run)->first();
                    
                    if ($existingProfesor) {
                        $existingProfesor->update([
                            'name' => $name,
                            'email' => $email,
                            'id_carrera' => $idCarrera
                        ]);
                        Log::info('Profesor actualizado', ['run_profesor' => $run]);
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
                        Log::info('Nuevo profesor creado', ['run_profesor' => $run]);
                        $processedUsersCount++;
                    }

                    $idAsignatura = $row[0];
                    $codigoAsignatura = $row[1];
                    $nombreAsignatura = preg_replace('/^[a-z]{2}:\s*/i', '', $row[2]);

                    $numeroSeccion = $row[3];

                    $existingAsignatura = Asignatura::where('id_asignatura', $idAsignatura)->first();
                    if (!$existingAsignatura) {
                        $asignatura = Asignatura::create([
                            'id_asignatura' => $idAsignatura,
                            'codigo_asignatura' => $codigoAsignatura,
                            'nombre_asignatura' => $nombreAsignatura,
                            'run_profesor' => $run,
                            'id_carrera' => $idCarrera
                        ]);
                        Log::info('Nueva asignatura creada', ['id_asignatura' => $idAsignatura]);
                    } else {
                        $asignatura = $existingAsignatura;
                        Log::info('Asignatura ya existe', ['id_asignatura' => $idAsignatura]);
                    }

                    $existingSeccion = Seccion::where('id_asignatura', $idAsignatura)
                        ->where('numero', $numeroSeccion)
                        ->first();

                    if (!$existingSeccion) {
                        Seccion::create([
                            'numero' => $numeroSeccion,
                            'id_asignatura' => $idAsignatura
                        ]);
                        Log::info('Nueva sección creada', [
                            'id_asignatura' => $idAsignatura,
                            'numero' => $numeroSeccion
                        ]);
                    }
                    $processedAsignaturasCount++;

                    $semestre = $row[5]; // Columna F
                    $horarioProfesor = $row[20]; // Columna U

                    try {
                        $idHorario = 'HOR_' . $run;

                        $existingHorario = Horario::where('id_horario', $idHorario)->first();

                        if ($existingHorario) {
                            $horario = $existingHorario;
                            // Actualizar el período del horario existente
                            $horario->periodo = $semestre;
                            $horario->save();
                            Log::info('Horario existente actualizado', [
                                'id_horario' => $horario->id_horario,
                                'nombre' => $horario->nombre,
                                'periodo' => $semestre,
                                'run_profesor' => $run
                            ]);
                        } else {
                            $horario = new Horario();
                            $horario->id_horario = $idHorario;
                            $horario->nombre = "Horario de " . $name;
                            $horario->periodo = $semestre;
                            $horario->run_profesor = $run;

                            if (!$horario->save()) {
                                throw new \Exception("Error al guardar el horario");
                            }

                            if (!$horario->id_horario) {
                                throw new \Exception("El horario no se creó correctamente");
                            }

                            Log::info('Nuevo horario creado', [
                                'id_horario' => $horario->id_horario,
                                'nombre' => $horario->nombre,
                                'periodo' => $semestre,
                                'run_profesor' => $run
                            ]);
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
                                    $existingPlanificacion = Planificacion_Asignatura::where('id_asignatura', $idAsignatura)
                                        ->where('id_horario', $horario->id_horario)
                                        ->where('id_modulo', $dia . '.' . $modulo)
                                        ->where('id_espacio', $espacio)
                                        ->first();

                                    if (!$existingPlanificacion) {
                                        $planificacion = new Planificacion_Asignatura();
                                        $planificacion->id_asignatura = $idAsignatura;
                                        $planificacion->id_horario = $horario->id_horario;
                                        $planificacion->id_modulo = $dia . '.' . $modulo;
                                        $planificacion->id_espacio = $espacio;

                                        if (!$planificacion->save()) {
                                            throw new \Exception("Error al guardar la planificación");
                                        }

                                        Log::info('Nueva planificación creada', [
                                            'id_asignatura' => $idAsignatura,
                                            'id_horario' => $horario->id_horario,
                                            'id_modulo' => $dia . '.' . $modulo,
                                            'id_espacio' => $espacio
                                        ]);
                                        $processedHorariosCount++;
                                    } else {
                                        Log::info('Planificación ya existe', [
                                            'id_asignatura' => $idAsignatura,
                                            'id_horario' => $horario->id_horario,
                                            'id_modulo' => $dia . '.' . $modulo,
                                            'id_espacio' => $espacio
                                        ]);
                                    }
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        Log::error('Error al procesar horario: ' . $e->getMessage(), [
                            'fila' => $index + 1,
                            'run_profesor' => $run,
                            'semestre' => $semestre
                        ]);
                        $errors[] = "Fila " . ($index + 1) . ": Error al procesar horario - " . $e->getMessage();
                        continue;
                    }

                } catch (\Exception $e) {
                    $errorMsg = "Fila " . ($index + 1) . ": " . $e->getMessage();
                    Log::error($errorMsg);
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
                ],
                'swal' => [
                    'title' => '¡Éxito!',
                    'text' => $message,
                    'icon' => 'success'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error al procesar archivo: ' . $e->getMessage(), [
                'file' => $file->getClientOriginalName() ?? null,
                'exception' => $e
            ]);

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
            Log::error('Error al eliminar registro de carga: ' . $e->getMessage());
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
            Log::error('Error al obtener progreso: ' . $e->getMessage());
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
