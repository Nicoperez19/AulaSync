<?php

namespace App\Http\Controllers;

use App\Models\DataLoad;
use App\Models\User;
use App\Models\Asignatura;
use App\Models\Carrera;
use App\Models\Seccion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Maatwebsite\Excel\Facades\Excel;

class DataLoadController extends Controller
{
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
        set_time_limit(300);
        
        Log::info('Iniciando proceso de carga de archivo');
        
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240' // 10MB max
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

            // Leer el archivo Excel
            $rows = Excel::toArray([], $file)[0];
            Log::info('Archivo Excel leído', ['total_filas' => count($rows)]);
            
            // Obtener el rol de Profesor
            $role = Role::findByName('Profesor');
            
            // Contadores de registros procesados
            $processedUsersCount = 0;
            $processedAsignaturasCount = 0;
            $errors = [];
            $skippedRows = 0;
            
            // Procesar cada fila
            foreach ($rows as $index => $row) {
                // Saltar la primera fila (encabezados)
                if ($index === 0) {
                    Log::info('Saltando encabezados del archivo');
                    continue;
                }
                
                try {
                    // Verificar si la sede es Talcahuano (columna H)
                    $sede = $row[7]; // Columna H (SEDE)
                    Log::info('Procesando fila', [
                        'numero_fila' => $index + 1,
                        'sede' => $sede
                    ]);

                    if (strtolower(trim($sede)) !== 'talcahuano') {
                        $skippedRows++;
                        Log::info('Fila omitida - No es Talcahuano', [
                            'numero_fila' => $index + 1,
                            'sede' => $sede
                        ]);
                        continue; // Saltar si no es Talcahuano
                    }

                    // Si llegamos aquí, es porque la sede es Talcahuano
                    // Obtener el ID de la carrera (columna R - UA)
                    $idCarrera = $row[17]; // Columna R (UA)
                    Log::info('Procesando registro de Talcahuano', [
                        'numero_fila' => $index + 1,
                        'id_carrera' => $idCarrera
                    ]);

                    // Verificar si la carrera existe
                    $carrera = Carrera::find($idCarrera);
                    if (!$carrera) {
                        $errorMsg = "Fila " . ($index + 1) . ": La carrera con ID " . $idCarrera . " no existe";
                        $errors[] = $errorMsg;
                        Log::warning($errorMsg);
                        continue;
                    }

                    // Procesar usuario (columnas L y M)
                    $run = $row[11]; // Columna L (PROF_RESP)
                    $name = $row[12]; // Columna M (NOM_PROF_RESP)
                    
                    Log::info('Procesando usuario', [
                        'numero_fila' => $index + 1,
                        'run' => $run,
                        'nombre' => $name
                    ]);
                    
                    // Verificar si el usuario ya existe
                    $existingUser = User::where('run', $run)->first();
                    
                    if ($existingUser) {
                        // Si existe, actualizar nombre y carrera
                        $existingUser->update([
                            'name' => $name,
                            'id_carrera' => $idCarrera
                        ]);
                        Log::info('Usuario actualizado', ['run' => $run]);
                    } else {
                        // Si no existe, crear nuevo usuario
                        $user = User::create([
                            'run' => $run,
                            'password' => Hash::make($run), // Password igual al RUN
                            'name' => $name,
                            'tipo_profesor' => 'Profesor',
                            'id_carrera' => $idCarrera
                        ]);
                        
                        // Asignar rol de Profesor
                        $user->assignRole($role);
                        Log::info('Nuevo usuario creado', ['run' => $run]);
                    }
                    
                    $processedUsersCount++;

                    // Procesar asignatura
                    $idAsignatura = $row[0]; // Columna A (ID_CURSO)
                    $codigoAsignatura = $row[1]; // Columna B (COD_RAMO)
                    $nombreAsignatura = $row[2]; // Columna C (RAMO_NOMBRE)
                    $numeroSeccion = $row[3]; // Columna D (Número de sección)
                    
                    Log::info('Procesando asignatura', [
                        'numero_fila' => $index + 1,
                        'id_asignatura' => $idAsignatura,
                        'codigo' => $codigoAsignatura,
                        'seccion' => $numeroSeccion
                    ]);
                    
                    // Verificar si la asignatura ya existe
                    $existingAsignatura = Asignatura::where('id_asignatura', $idAsignatura)->first();
                    $asignatura = null;
                    
                    if (!$existingAsignatura) {
                        // Crear nueva asignatura
                        $asignatura = Asignatura::create([
                            'id_asignatura' => $idAsignatura,
                            'codigo_asignatura' => $codigoAsignatura,
                            'nombre_asignatura' => $nombreAsignatura,
                            'run' => $run, // Asociar con el profesor
                            'id_carrera' => $idCarrera // Usar el ID de carrera de la columna R
                        ]);
                        
                        Log::info('Nueva asignatura creada', ['id_asignatura' => $idAsignatura]);
                        $processedAsignaturasCount++;
                    } else {
                        $asignatura = $existingAsignatura;
                        Log::info('Asignatura ya existe', ['id_asignatura' => $idAsignatura]);
                    }

                    // Procesar sección
                    if ($asignatura) {
                        // Verificar si la sección ya existe para esta asignatura
                        $existingSeccion = Seccion::where('id_asignatura', $idAsignatura)
                            ->where('numero', $numeroSeccion)
                            ->first();

                        if (!$existingSeccion) {
                            // Crear nueva sección
                            Seccion::create([
                                'numero' => $numeroSeccion,
                                'id_asignatura' => $idAsignatura
                            ]);
                            Log::info('Nueva sección creada', [
                                'id_asignatura' => $idAsignatura,
                                'numero' => $numeroSeccion
                            ]);
                        } else {
                            Log::info('Sección ya existe', [
                                'id_asignatura' => $idAsignatura,
                                'numero' => $numeroSeccion
                            ]);
                        }
                    }
                    
                } catch (\Exception $e) {
                    $errorMsg = "Fila " . ($index + 1) . ": " . $e->getMessage();
                    Log::error($errorMsg);
                    $errors[] = $errorMsg;
                    continue;
                }
            }
            
            Log::info('Proceso de carga completado', [
                'usuarios_procesados' => $processedUsersCount,
                'asignaturas_procesadas' => $processedAsignaturasCount,
                'filas_omitidas' => $skippedRows,
                'errores' => count($errors)
            ]);
            
            // Actualizar el estado y registros cargados
            $dataLoad->update([
                'estado' => 'completado',
                'registros_cargados' => $processedUsersCount + $processedAsignaturasCount
            ]);
            
            $message = 'Archivo procesado exitosamente. Se procesaron ' . $processedUsersCount . ' usuarios y ' . $processedAsignaturasCount . ' asignaturas.';
            if (!empty($errors)) {
                $message .= ' Se encontraron ' . count($errors) . ' errores: ' . implode(', ', $errors);
            }
            
            return response()->json([
                'message' => $message,
                'data' => $dataLoad,
                'errors' => $errors
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al procesar archivo: ' . $e->getMessage(), [
                'file' => $fileName ?? null,
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
}
