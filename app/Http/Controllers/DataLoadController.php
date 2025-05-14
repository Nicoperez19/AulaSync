<?php

namespace App\Http\Controllers;

use App\Models\DataLoad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
            
            return response()->json([
                'message' => 'Archivo cargado exitosamente',
                'data' => $dataLoad
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al cargar el archivo: ' . $e->getMessage()
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
