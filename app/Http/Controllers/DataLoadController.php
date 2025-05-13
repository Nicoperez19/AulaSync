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
        return view('layouts.data.data');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'archivo' => 'required|file|mimes:xlsx,xls|max:10240', // Solo permite Excel, máximo 10MB
                'tipo_carga' => 'required|string'
            ]);

            $archivo = $request->file('archivo');
            $nombreOriginal = $archivo->getClientOriginalName();
            
            // Generar un nombre único para el archivo
            $extension = $archivo->getClientOriginalExtension();
            $nombreUnico = Str::uuid() . '.' . $extension;
            
            // Guardar el archivo en storage/app/excel_uploads
            $rutaArchivo = $archivo->storeAs('excel_uploads', $nombreUnico, 'local');
            
            if (!$rutaArchivo) {
                throw new \Exception('Error al guardar el archivo');
            }

            // Crear el registro en la base de datos
            $dataLoad = DataLoad::create([
                'nombre_archivo' => $nombreOriginal,
                'ruta_archivo' => $rutaArchivo,
                'tipo_carga' => $request->tipo_carga,
                'estado' => 'pendiente',
                'user_id' => Auth::id()
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Archivo recibido correctamente. Iniciando procesamiento...'
                ]);
            }

            return redirect()->route('data.index')
                ->with('success', 'Archivo recibido correctamente. Iniciando procesamiento...');
        } catch (\Exception $e) {
            Log::error('Error al procesar archivo: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al procesar el archivo: ' . $e->getMessage()
                ], 500);
            }

            return back()->withErrors(['error' => 'Error al procesar el archivo.']);
        }
    }

    public function destroy(DataLoad $dataLoad)
    {
        try {
            // Eliminar el archivo físico
            if (Storage::exists($dataLoad->ruta_archivo)) {
                Storage::delete($dataLoad->ruta_archivo);
            }
            
            // Eliminar el registro de la base de datos
            $dataLoad->delete();
            
            return redirect()->route('data.index')
                ->with('success', 'Registro de carga eliminado exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar registro de carga: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al eliminar el registro de carga.']);
        }
    }
}
