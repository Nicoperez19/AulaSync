<?php

namespace App\Http\Controllers;

use App\Models\Comuna;
use App\Models\Universidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UniversidadController extends Controller
{
    public function index()
    {
        $comunas = Comuna::all();
        return view('layouts.university.university_index', compact('comunas'));
    }

    public function store(Request $request)
    {
        try {
            Log::info('Intentando validar datos de universidad:', $request->all());
    
            $validatedData = $request->validate([
                'id_universidad' => 'required|string|max:255',
                'nombre_universidad' => 'required|string|max:255',
                'direccion_universidad' => 'required|string|max:255',
                'telefono_universidad' => 'required|regex:/^[0-9+]+$/|max:15',
                'imagen_logo' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
                'comunas_id' => 'required|exists:comunas,id',
            ]);
    
            Log::info('Datos validados correctamente:', $validatedData);
    
            $imagenNombre = null;
    
            if ($request->hasFile('imagen_logo')) {
                $file = $request->file('imagen_logo');
                $imagenNombre = str_replace(' ', '_', strtolower($validatedData['nombre_universidad'])) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('images/logo_universidad'), $imagenNombre);
                Log::info("Imagen subida: " . $imagenNombre);
            }
    
            Universidad::create([
                'id_universidad' => $validatedData['id_universidad'],
                'nombre_universidad' => $validatedData['nombre_universidad'],
                'direccion_universidad' => $validatedData['direccion_universidad'],
                'telefono_universidad' => $validatedData['telefono_universidad'],
                'imagen_logo' => $imagenNombre,
                'comunas_id' => $validatedData['comunas_id'],
            ]);
    
            Log::info('Universidad creada correctamente.');
    
            return redirect()->route('universities.index')->with('success', 'Universidad creada exitosamente.');
        } catch (\Illuminate\Validation\ValidationException $ve) {
            Log::error('Error de validación:', $ve->errors());
            return redirect()->back()->withErrors($ve->validator)->withInput();
        } catch (\Exception $e) {
            Log::error('Error general al crear universidad:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Ocurrió un error al crear la universidad. Revisa los logs.');
        }
    }
    
    public function edit($id)
    {
        $universidad = Universidad::findOrFail($id);
        $comunas = Comuna::orderBy('nombre_comuna', 'asc')->get();
        return view('layouts.university.university_edit', compact('universidad', 'comunas'));
    }

    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'id_universidad' => 'required|string|max:255',
                'nombre_universidad' => 'required|string|max:255',
                'direccion_universidad' => 'required|string|max:255',
                'telefono_universidad' => 'required|regex:/^[0-9+]+$/|max:15',
                'imagen_logo' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
                'comunas_id' => 'required|exists:comunas,id',
            ]);

            $universidad = Universidad::findOrFail($id);

            $imagenNombre = $universidad->imagen_logo;

            if ($request->hasFile('imagen_logo')) {
                if ($imagenNombre && file_exists(public_path('images/logo_universidad/' . $imagenNombre))) {
                    unlink(public_path('images/logo_universidad/' . $imagenNombre));
                }
                $file = $request->file('imagen_logo');
                $imagenNombre = str_replace(' ', '_', strtolower($validatedData['nombre_universidad'])) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('images/logo_universidad'), $imagenNombre);
            }

            $universidad->update([
                'id_universidad' => $validatedData['id_universidad'],
                'nombre_universidad' => $validatedData['nombre_universidad'],
                'direccion_universidad' => $validatedData['direccion_universidad'],
                'telefono_universidad' => $validatedData['telefono_universidad'],
                'imagen_logo' => $imagenNombre,
                'comunas_id' => $validatedData['comunas_id'],
            ]);

            return redirect()->route('universities.index')->with('success', 'Universidad actualizada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Ocurrió un error al actualizar la universidad. Inténtalo de nuevo.');
        }
    }


    public function destroy($id)
    {
        $universidad = Universidad::findOrFail($id);

        $universidad->delete();

        return redirect()->route('universities.index')->with('success', 'Universidad eliminada exitosamente.');
    }
}
