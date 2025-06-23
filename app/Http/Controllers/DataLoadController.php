<?php

namespace App\Http\Controllers;

use App\Models\DataLoad;
use App\Models\User;
<<<<<<< HEAD
<<<<<<< HEAD
=======
use App\Models\Asignatura;
use App\Models\Carrera;
use App\Models\Seccion;
use App\Models\Horario;
use App\Models\Planificacion_Asignatura;
>>>>>>> Nperez
=======
>>>>>>> 6c05e560f5edb88b89bd0fe7d8d71ecb8386c841
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Maatwebsite\Excel\Facades\Excel;
<<<<<<< HEAD
<<<<<<< HEAD

class DataLoadController extends Controller
{
=======
use App\Services\QRService;

class DataLoadController extends Controller
{
    protected $qrService;

    public function __construct(QRService $qrService)
    {
        $this->qrService = $qrService;
    }

>>>>>>> Nperez
=======

class DataLoadController extends Controller
{
>>>>>>> 6c05e560f5edb88b89bd0fe7d8d71ecb8386c841
    public function index()
    {
        $dataLoads = DataLoad::latest()->paginate(10);
        return view('layouts.data.data_index', compact('dataLoads'));
    }

    public function show(DataLoad $dataLoad)
    {
        return view('layouts.data.data_show', compact('dataLoad'));
    }

    public function upload(Request $request)
    {
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> 6c05e560f5edb88b89bd0fe7d8d71ecb8386c841
        // Aumentar el tiempo máximo de ejecución a 5 minutos
        set_time_limit(300);
        
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240' // 10MB max
<<<<<<< HEAD
=======
        set_time_limit(300);
        Log::info('Iniciando proceso de carga de archivo');

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240'
>>>>>>> Nperez
=======
>>>>>>> 6c05e560f5edb88b89bd0fe7d8d71ecb8386c841
        ]);

        try {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $fileExtension = $file->getClientOriginalExtension();
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> 6c05e560f5edb88b89bd0fe7d8d71ecb8386c841
            
            $uniqueFileName = date('Y-m-d_His') . '_' . Auth::user()->run . '_' . Str::random(10) . '.' . $fileExtension;
            
            $path = $file->storeAs('datos_subidos', $uniqueFileName, 'public');
            
<<<<<<< HEAD
=======

            Log::info('Archivo recibido', [
                'nombre' => $fileName,
                'extension' => $fileExtension
            ]);

            $uniqueFileName = date('Y-m-d_His') . '_' . Auth::user()->run . '_' . Str::random(10) . '.' . $fileExtension;
            $path = $file->storeAs('datos_subidos', $uniqueFileName, 'public');

>>>>>>> Nperez
=======
>>>>>>> 6c05e560f5edb88b89bd0fe7d8d71ecb8386c841
            $dataLoad = DataLoad::create([
                'nombre_archivo' => $fileName,
                'ruta_archivo' => $path,
                'tipo_carga' => $fileExtension,
                'registros_cargados' => 0,
                'estado' => 'pendiente',
                'user_run' => Auth::user()->run
            ]);

<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> 6c05e560f5edb88b89bd0fe7d8d71ecb8386c841
            // Leer el archivo Excel
            $rows = Excel::toArray([], $file)[0];
            
            // Obtener el rol de Profesor
            $role = Role::findByName('Profesor');
            
            // Contador de registros procesados
            $processedCount = 0;
            
            // Procesar cada fila
            foreach ($rows as $index => $row) {
                // Saltar la primera fila (encabezados)
                if ($index === 0) continue;
                
                try {
                    // Obtener solo RUN y nombre
                    $run = $row[11]; // Columna L (PROF_RESP)
                    $name = $row[12]; // Columna M (NOM_PROF_RESP)
                    
                    // Verificar si el usuario ya existe
                    $existingUser = User::where('run', $run)->first();
                    
                    if ($existingUser) {
                        // Si existe, actualizar solo el nombre
                        $existingUser->update([
                            'name' => $name
                        ]);
                    } else {
                        // Si no existe, crear nuevo usuario
                        $user = User::create([
                            'run' => $run,
                            'password' => Hash::make($run), // Password igual al RUN
                            'name' => $name,
                            'tipo_profesor' => 'Profesor'
                        ]);
                        
                        // Asignar rol de Profesor
                        $user->assignRole($role);
                    }
                    
                    $processedCount++;
                    
                } catch (\Exception $e) {
                    Log::error('Error al procesar fila ' . ($index + 1) . ': ' . $e->getMessage());
                    continue;
                }
            }
            
            // Actualizar el estado y registros cargados
            $dataLoad->update([
                'estado' => 'completado',
                'registros_cargados' => $processedCount
            ]);
            
            return response()->json([
                'message' => 'Archivo procesado exitosamente. Se procesaron ' . $processedCount . ' registros.',
                'data' => $dataLoad
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al procesar archivo: ' . $e->getMessage(), [
                'file' => $fileName ?? null,
                'exception' => $e
            ]);
            
<<<<<<< HEAD
=======
            Log::info('Registro de carga creado', ['id' => $dataLoad->id]);

            $rows = Excel::toArray([], $file)[0];
            Log::info('Archivo Excel leído', ['total_filas' => count($rows)]);

            $role = Role::findByName('Profesor');
            $processedUsersCount = 0;
            $processedAsignaturasCount = 0;
            $processedHorariosCount = 0;
            $errors = [];
            $skippedRows = 0;
            $totalRows = count($rows) - 1; // Restamos 1 por la fila de encabezados
            $currentRow = 0;

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

                $currentRow++;
                // Actualizar el progreso cada fila
                $progress = ($currentRow / $totalRows) * 100;
                $dataLoad->update([
                    'estado' => 'procesando',
                    'registros_cargados' => round($progress)
                ]);

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
                    $existingUser = User::where('run', $run)->first();
                    
                    if ($existingUser) {
                        $existingUser->update([
                            'name' => $name,
                            'email' => $email,
                            'id_carrera' => $idCarrera
                        ]);
                        Log::info('Usuario actualizado', ['run' => $run]);
                    } else {
                        $user = User::create([
                            'run' => $run,
                            'password' => Hash::make($run),
                            'name' => $name,
                            'email' => $email,
                            'tipo_profesor' => 'Profesor',
                            'id_carrera' => $idCarrera
                        ]);
                        $user->assignRole($role);
                        Log::info('Nuevo usuario creado', ['run' => $run]);
                    }
                    $processedUsersCount++;

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
                            'run' => $run,
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
                            Log::info('Usando horario existente', [
                                'id_horario' => $horario->id_horario,
                                'nombre' => $horario->nombre,
                                'periodo' => $semestre,
                                'run' => $run
                            ]);
                        } else {
                            $horario = new Horario();
                            $horario->id_horario = $idHorario;
                            $horario->nombre = "Horario de " . $name;
                            $horario->periodo = $semestre;
                            $horario->run = $run;

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
                                'run' => $run
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
                            'run' => $run,
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

            $message = 'Archivo procesado exitosamente. Se procesaron ' . $processedUsersCount . ' usuarios, ' .
                $processedAsignaturasCount . ' asignaturas y ' . $processedHorariosCount . ' horarios.';
            if (!empty($errors)) {
                $message .= ' Se encontraron ' . count($errors) . ' errores: ' . implode(', ', $errors);
            }

            return response()->json([
                'message' => $message,
                'data' => [
                    'nombre_archivo' => $dataLoad,
                    'usuarios_procesados' => $processedUsersCount,
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

>>>>>>> Nperez
=======
>>>>>>> 6c05e560f5edb88b89bd0fe7d8d71ecb8386c841
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
<<<<<<< HEAD
<<<<<<< HEAD
=======

    public function getProgress(DataLoad $dataLoad)
    {
        return response()->json([
            'estado' => $dataLoad->estado,
            'progreso' => $dataLoad->registros_cargados
        ]);
    }
>>>>>>> Nperez
=======
>>>>>>> 6c05e560f5edb88b89bd0fe7d8d71ecb8386c841
}
