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
use App\Imports\DataImport;

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
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240'
        ]);

        try {
            $file = $request->file('file');
            $dataLoad = $this->createDataLoadRecord($file);
            $rows = $this->importExcelFile($file);
            
            $result = $this->processExcelData($rows, $dataLoad);

            return response()->json([
                'message' => 'EL ARCHIVO SE PROCESÓ EXITOSAMENTE',
                'data' => [
                    'nombre_archivo' => $dataLoad,
                ],
                'swal' => [
                    'title' => '¡Éxito!',
                    'text' => 'El archivo fue procesado correctamente.',
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

    protected function createDataLoadRecord($file)
    {
        $fileName = $file->getClientOriginalName();
        $fileExtension = $file->getClientOriginalExtension();
        $uniqueFileName = date('Y-m-d_His') . '_' . Auth::user()->run . '_' . Str::random(10) . '.' . $fileExtension;
        $path = $file->storeAs('datos_subidos', $uniqueFileName, 'public');

        Log::info('Archivo recibido', [
            'nombre' => $fileName,
            'extension' => $fileExtension
        ]);

        $dataLoad = DataLoad::create([
            'nombre_archivo' => $fileName,
            'ruta_archivo' => $path,
            'tipo_carga' => $fileExtension,
            'registros_cargados' => 0,
            'estado' => 'pendiente',
            'user_run' => Auth::user()->run
        ]);

        Log::info('Registro de carga creado', ['id' => $dataLoad->id]);
        return $dataLoad;
    }

    protected function importExcelFile($file)
    {
        $import = new DataImport();
        Excel::import($import, $file);
        $rows = $import->getData();
        Log::info('Archivo Excel leído', ['total_filas' => count($rows)]);
        return $rows;
    }

    protected function processExcelData($rows, DataLoad $dataLoad)
    {
        $role = Role::findByName('Profesor');
        $processedUsersCount = 0;
        $processedAsignaturasCount = 0;
        $errors = [];
        $skippedRows = 0;

        foreach ($rows as $index => $row) {
            if ($index === 0) {
                Log::info('Saltando encabezados del archivo');
                continue;
            }

            try {
                if (!$this->isValidSede($row[7])) {
                    $skippedRows++;
                    continue;
                }

                $idCarrera = $row[17];
                if (!$this->isValidCarrera($idCarrera)) {
                    $errors[] = "Fila " . ($index + 1) . ": La carrera con ID " . $idCarrera . " no existe";
                    continue;
                }

                $run = $row[11];
                $name = $row[12];
                $this->processUser($run, $name, $idCarrera, $role);
                $processedUsersCount++;

                $this->processAsignatura($row, $run, $idCarrera);
                $processedAsignaturasCount++;

            } catch (\Exception $e) {
                $errorMsg = "Fila " . ($index + 1) . ": " . $e->getMessage();
                Log::error($errorMsg);
                $errors[] = $errorMsg;
            }
        }

        $this->updateDataLoadStatus($dataLoad, $processedUsersCount, $processedAsignaturasCount);
        return [
            'usuarios_procesados' => $processedUsersCount,
            'asignaturas_procesadas' => $processedAsignaturasCount,
            'filas_omitidas' => $skippedRows,
            'errores' => count($errors)
        ];
    }

    protected function isValidSede($sede)
    {
        if (strtolower(trim($sede)) !== 'talcahuano') {
            Log::info('Fila omitida - No es Talcahuano', ['sede' => $sede]);
            return false;
        }
        return true;
    }

    protected function isValidCarrera($idCarrera)
    {
        return Carrera::find($idCarrera) !== null;
    }

    protected function processUser($run, $name, $idCarrera, $role)
    {
        $existingUser = User::where('run', $run)->first();

        if ($existingUser) {
            $existingUser->update([
                'name' => $name,
                'id_carrera' => $idCarrera
            ]);
            Log::info('Usuario actualizado', ['run' => $run]);
        } else {
            $user = User::create([
                'run' => $run,
                'password' => Hash::make($run),
                'name' => $name,
                'tipo_profesor' => 'Profesor',
                'id_carrera' => $idCarrera
            ]);
            $user->assignRole($role);
            Log::info('Nuevo usuario creado', ['run' => $run]);
        }
    }

    protected function processAsignatura($row, $run, $idCarrera)
    {
        $idAsignatura = $row[0];
        $codigoAsignatura = $row[1];
        $nombreAsignatura = $row[2];
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

        $this->processSeccion($asignatura, $numeroSeccion);
    }

    protected function processSeccion($asignatura, $numeroSeccion)
    {
        $existingSeccion = Seccion::where('id_asignatura', $asignatura->id_asignatura)
            ->where('numero', $numeroSeccion)
            ->first();

        if (!$existingSeccion) {
            Seccion::create([
                'numero' => $numeroSeccion,
                'id_asignatura' => $asignatura->id_asignatura
            ]);
            Log::info('Nueva sección creada', [
                'id_asignatura' => $asignatura->id_asignatura,
                'numero' => $numeroSeccion
            ]);
        } else {
            Log::info('Sección ya existe', [
                'id_asignatura' => $asignatura->id_asignatura,
                'numero' => $numeroSeccion
            ]);
        }
    }

    protected function updateDataLoadStatus($dataLoad, $processedUsersCount, $processedAsignaturasCount)
    {
        $dataLoad->update([
            'estado' => 'completado',
            'registros_cargados' => $processedUsersCount + $processedAsignaturasCount
        ]);
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
