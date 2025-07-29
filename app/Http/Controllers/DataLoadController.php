<?php

namespace App\Http\Controllers;

use App\Models\DataLoad;
use App\Models\Profesor;
use App\Models\Asignatura;
use App\Models\Carrera;
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
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'semestre_selector' => 'required|in:1,2'
        ]);

        // Obtener el período seleccionado por el usuario
        $semestreSeleccionado = $request->input('semestre_selector');
        $anioActual = date('Y');
        $periodoSeleccionado = $anioActual . '-' . $semestreSeleccionado;

        Log::info('Período seleccionado por el usuario', [
            'semestre' => $semestreSeleccionado,
            'anio' => $anioActual,
            'periodo' => $periodoSeleccionado
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

                    $numeroSeccion = trim($row[3]); // Columna D
                    
                    // Validar que la sección sea un número de hasta 4 dígitos
                    if (!empty($numeroSeccion) && !preg_match('/^\d{1,4}$/', $numeroSeccion)) {
                        Log::warning('Sección inválida', [
                            'numero_fila' => $index + 1,
                            'seccion' => $numeroSeccion,
                            'id_asignatura' => $idAsignatura,
                            'mensaje' => 'La sección debe ser un número de 1 a 4 dígitos'
                        ]);
                        $errors[] = "Fila " . ($index + 1) . ": Sección inválida - debe ser un número de 1 a 4 dígitos (valor: " . $numeroSeccion . ")";
                        continue;
                    }
                    
                    // Si la sección está vacía, asignar un valor por defecto
                    if (empty($numeroSeccion)) {
                        $numeroSeccion = '1';
                        Log::info('Sección vacía, asignando valor por defecto', [
                            'numero_fila' => $index + 1,
                            'seccion' => $numeroSeccion,
                            'id_asignatura' => $idAsignatura
                        ]);
                    }
                    
                    Log::info('Procesando sección', [
                        'numero_fila' => $index + 1,
                        'seccion' => $numeroSeccion,
                        'id_asignatura' => $idAsignatura,
                        'tipo_dato' => gettype($numeroSeccion)
                    ]);

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
                        Log::info('Nueva asignatura creada', [
                            'id_asignatura' => $idAsignatura,
                            'seccion' => $numeroSeccion,
                            'seccion_guardada' => $asignatura->seccion
                        ]);
                        
                        // Verificar que la sección se guardó correctamente
                        $asignaturaVerificada = Asignatura::find($idAsignatura);
                        if ($asignaturaVerificada && $asignaturaVerificada->seccion !== $numeroSeccion) {
                            Log::error('Error: La sección no se guardó correctamente', [
                                'id_asignatura' => $idAsignatura,
                                'seccion_esperada' => $numeroSeccion,
                                'seccion_guardada' => $asignaturaVerificada->seccion
                            ]);
                        }
                    } else {
                        // Actualizar la sección si es diferente
                        if ($existingAsignatura->seccion != $numeroSeccion) {
                            $existingAsignatura->update(['seccion' => $numeroSeccion]);
                            Log::info('Sección actualizada', [
                                'id_asignatura' => $idAsignatura,
                                'seccion_anterior' => $existingAsignatura->getOriginal('seccion'),
                                'seccion_nueva' => $numeroSeccion,
                                'seccion_guardada' => $existingAsignatura->fresh()->seccion
                            ]);
                        }
                        
                        $asignatura = $existingAsignatura;
                        Log::info('Asignatura ya existe', [
                            'id_asignatura' => $idAsignatura,
                            'seccion_actual' => $asignatura->seccion,
                            'seccion_nueva' => $numeroSeccion
                        ]);
                    }

                  
                    $processedAsignaturasCount++;

                    // Procesar horario con período seleccionado por el usuario
                    $horarioProfesor = $row[20]; // Columna U
                    
                    // Usar el período seleccionado por el usuario
                    $periodo = $periodoSeleccionado;
                    
                    Log::info('Procesando horario con período seleccionado por el usuario:', [
                        'fila' => $index + 1,
                        'run_profesor' => $run,
                        'periodo_seleccionado' => $periodo,
                        'semestre_usuario' => $semestreSeleccionado,
                        'anio_actual' => $anioActual
                    ]);

                    try {
                        // Crear ID del horario con el período
                        $idHorario = 'HOR_' . $run . '_' . $periodo;

                        // Buscar horario existente con el nuevo formato
                        $existingHorario = Horario::where('id_horario', $idHorario)->first();
                        
                        // Si no existe con el nuevo formato, buscar con el formato antiguo
                        if (!$existingHorario) {
                            $oldIdHorario = 'HOR_' . $run;
                            $existingHorario = Horario::where('id_horario', $oldIdHorario)->first();
                            
                            if ($existingHorario) {
                                Log::info('Horario encontrado con formato antiguo, actualizando ID', [
                                    'id_antiguo' => $oldIdHorario,
                                    'id_nuevo' => $idHorario,
                                    'run_profesor' => $run
                                ]);
                                
                                // Actualizar el ID del horario existente
                                $existingHorario->id_horario = $idHorario;
                                $existingHorario->periodo = $periodo;
                                $existingHorario->save();
                                
                                // Actualizar todas las planificaciones asociadas
                                \App\Models\Planificacion_Asignatura::where('id_horario', $oldIdHorario)
                                    ->update(['id_horario' => $idHorario]);
                                    
                                Log::info('Horario y planificaciones actualizados al nuevo formato', [
                                    'id_antiguo' => $oldIdHorario,
                                    'id_nuevo' => $idHorario,
                                    'planificaciones_actualizadas' => \App\Models\Planificacion_Asignatura::where('id_horario', $idHorario)->count()
                                ]);
                            }
                        }

                        if ($existingHorario) {
                            $horario = $existingHorario;
                            // Actualizar el período del horario existente
                            $horario->periodo = $periodo;
                            $horario->save();
                            Log::info('Horario existente actualizado', [
                                'id_horario' => $horario->id_horario,
                                'nombre' => $horario->nombre,
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

                            Log::info('Nuevo horario creado', [
                                'id_horario' => $horario->id_horario,
                                'nombre' => $horario->nombre,
                                'periodo' => $periodo,
                                'run_profesor' => $run,
                                'formato_id' => 'HOR_RUN_PERIODO'
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
                                    
                                    // Verificar si el espacio existe en la base de datos
                                    $espacioExiste = \App\Models\Espacio::where('id_espacio', $espacio)->exists();
                                    
                                    if (!$espacioExiste) {
                                        Log::warning('Espacio no encontrado en la base de datos', [
                                            'id_espacio' => $espacio,
                                            'fila' => $index + 1,
                                            'run_profesor' => $run
                                        ]);
                                        continue; // Saltar esta planificación si el espacio no existe
                                    }
                                    
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

    /**
     * Busca información del semestre en múltiples columnas del Excel
     */
    private function buscarInformacionSemestre($row, $numeroFila, $runProfesor)
    {
        // Columnas donde buscar información del semestre (índices)
        $columnasSemestre = [
            5,   // Columna F (original)
            6,   // Columna G
            7,   // Columna H
            8,   // Columna I
            9,   // Columna J
            10,  // Columna K
            11,  // Columna L
            12,  // Columna M
            13,  // Columna N
            14,  // Columna O
            15,  // Columna P
            16,  // Columna Q
            17,  // Columna R
            18,  // Columna S
            19,  // Columna T
        ];

        $informacionEncontrada = [];

        foreach ($columnasSemestre as $indice) {
            if (isset($row[$indice])) {
                $valor = $row[$indice];
                
                // Log de cada columna revisada
                Log::info("Revisando columna para semestre:", [
                    'fila' => $numeroFila,
                    'run_profesor' => $runProfesor,
                    'indice_columna' => $indice,
                    'letra_columna' => $this->indiceALetra($indice),
                    'valor' => $valor,
                    'tipo' => gettype($valor),
                    'vacio' => empty($valor)
                ]);

                // Buscar patrones que indiquen semestre
                if ($this->esInformacionSemestre($valor)) {
                    $informacionEncontrada[] = [
                        'columna' => $indice,
                        'letra' => $this->indiceALetra($indice),
                        'valor' => $valor
                    ];
                }
            }
        }

        // Si encontramos información, usar la primera
        if (!empty($informacionEncontrada)) {
            $primerResultado = $informacionEncontrada[0];
            Log::info("Información de semestre encontrada:", [
                'fila' => $numeroFila,
                'run_profesor' => $runProfesor,
                'columna' => $primerResultado['letra'],
                'valor' => $primerResultado['valor']
            ]);
            return $primerResultado['valor'];
        }

        // Si no encontramos nada, usar el valor original de la columna F
        $valorOriginal = $row[5] ?? null;
        Log::warning("No se encontró información de semestre, usando valor original:", [
            'fila' => $numeroFila,
            'run_profesor' => $runProfesor,
            'valor_original_columna_F' => $valorOriginal
        ]);
        
        return $valorOriginal;
    }

    /**
     * Convierte índice numérico a letra de columna Excel
     */
    private function indiceALetra($indice)
    {
        $letra = '';
        while ($indice >= 0) {
            $letra = chr(65 + ($indice % 26)) . $letra;
            $indice = intval($indice / 26) - 1;
        }
        return $letra;
    }

    /**
     * Verifica si un valor contiene información de semestre
     */
    private function esInformacionSemestre($valor)
    {
        if (empty($valor) || is_null($valor)) {
            return false;
        }

        $valorString = (string) $valor;
        $valorString = trim($valorString);

        // Patrones que indican información de semestre
        $patrones = [
            '/^\d{4}-\d+$/',           // 2025-1, 2025-2
            '/^\d{4}\/\d+$/',          // 2025/1, 2025/2
            '/^\d{4}\.\d+$/',          // 2025.1, 2025.2
            '/^\d{4}\s*\d+$/',         // 2025 1, 2025 2
            '/^semestre\s*\d+$/i',     // Semestre 1, Semestre 2
            '/^s\s*\d+$/i',            // S 1, S 2
            '/^primer\s+semestre/i',   // Primer Semestre
            '/^segundo\s+semestre/i',  // Segundo Semestre
            '/^1er\s+semestre/i',      // 1er Semestre
            '/^2do\s+semestre/i',      // 2do Semestre
            '/^1$/i',                  // Solo 1
            '/^2$/i',                  // Solo 2
            '/^primer/i',              // Primer
            '/^segundo/i',             // Segundo
        ];

        foreach ($patrones as $patron) {
            if (preg_match($patron, $valorString)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Normaliza el período para asegurar formato YYYY-S
     */
    private function normalizarPeriodo($semestre)
    {
        // Log para debugging
        Log::info('Normalizando período:', [
            'valor_entrada' => $semestre,
            'tipo' => gettype($semestre),
            'vacio' => empty($semestre),
            'null' => is_null($semestre)
        ]);

        // Si está vacío o es null, usar período actual
        if (empty($semestre) || is_null($semestre)) {
            $anioActual = date('Y');
            $mesActual = date('n');
            $semestreActual = ($mesActual >= 3 && $mesActual <= 7) ? 1 : 2;
            
            Log::warning('Período vacío o null, usando período actual', [
                'valor_original' => $semestre,
                'periodo_por_defecto' => $anioActual . '-' . $semestreActual
            ]);
            
            return $anioActual . '-' . $semestreActual;
        }

        // Si ya está en formato YYYY-S, devolverlo tal como está
        if (preg_match('/^\d{4}-\d+$/', $semestre)) {
            Log::info('Período ya en formato correcto', ['periodo' => $semestre]);
            return $semestre;
        }

        // Si es solo un número (semestre), usar el año actual
        if (is_numeric($semestre) && $semestre >= 1 && $semestre <= 2) {
            $anioActual = date('Y');
            $resultado = $anioActual . '-' . $semestre;
            Log::info('Período normalizado desde número', [
                'numero_original' => $semestre,
                'periodo_resultado' => $resultado
            ]);
            return $resultado;
        }

        // Si es un string que contiene año y semestre en otro formato
        if (preg_match('/(\d{4})[^\d]*(\d+)/', $semestre, $matches)) {
            $resultado = $matches[1] . '-' . $matches[2];
            Log::info('Período extraído de string', [
                'string_original' => $semestre,
                'periodo_resultado' => $resultado
            ]);
            return $resultado;
        }

        // Si es texto que indica semestre (Primer, Segundo, etc.)
        $semestreString = strtolower(trim($semestre));
        if (in_array($semestreString, ['primer', '1er', '1', 'primero'])) {
            $anioActual = date('Y');
            $resultado = $anioActual . '-1';
            Log::info('Período extraído de texto primer semestre', [
                'texto_original' => $semestre,
                'periodo_resultado' => $resultado
            ]);
            return $resultado;
        }
        
        if (in_array($semestreString, ['segundo', '2do', '2', 'segundo'])) {
            $anioActual = date('Y');
            $resultado = $anioActual . '-2';
            Log::info('Período extraído de texto segundo semestre', [
                'texto_original' => $semestre,
                'periodo_resultado' => $resultado
            ]);
            return $resultado;
        }

        // Si contiene la palabra "semestre" seguida de un número
        if (preg_match('/semestre\s*(\d+)/i', $semestre, $matches)) {
            $anioActual = date('Y');
            $resultado = $anioActual . '-' . $matches[1];
            Log::info('Período extraído de texto con palabra semestre', [
                'texto_original' => $semestre,
                'periodo_resultado' => $resultado
            ]);
            return $resultado;
        }

        // Si no se puede determinar, usar el período actual
        $anioActual = date('Y');
        $mesActual = date('n');
        $semestreActual = ($mesActual >= 3 && $mesActual <= 7) ? 1 : 2;
        
        Log::warning('No se pudo normalizar el período, usando período actual', [
            'valor_original' => $semestre,
            'periodo_por_defecto' => $anioActual . '-' . $semestreActual
        ]);
        
        return $anioActual . '-' . $semestreActual;
    }
}
