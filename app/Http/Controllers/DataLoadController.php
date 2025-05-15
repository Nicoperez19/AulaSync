<?php

namespace App\Http\Controllers;

use App\Models\DataLoad;
use App\Models\User;
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
        // Aumentar el tiempo máximo de ejecución a 5 minutos
        set_time_limit(300);
        
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240' // 10MB max
        ]);

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
