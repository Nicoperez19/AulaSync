<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ConfiguracionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $configuraciones = Configuracion::all();
            return view('layouts.configuracion.configuracion_index', compact('configuraciones'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Hubo un problema al cargar las configuraciones.'])->withInput();
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'clave' => 'required|string|max:100|unique:configuracion,clave',
                'valor' => 'required|string',
                'descripcion' => 'nullable|string|max:255',
            ]);

            Configuracion::create($validatedData);

            return redirect()->route('configuracion.index')->with('success', 'Configuración creada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Hubo un problema al crear la configuración. Por favor, intente nuevamente.'])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $configuracion = Configuracion::findOrFail($id);
            return view('layouts.configuracion.configuracion_edit', compact('configuracion'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('configuracion.index')->withErrors(['error' => 'Configuración no encontrada.']);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al cargar la configuración: ' . $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $configuracion = Configuracion::findOrFail($id);

            // Special handling for logo upload
            if ($configuracion->clave === 'logo_institucional') {
                // Only validate if a file is uploaded
                if ($request->hasFile('logo')) {
                    $request->validate([
                        'logo' => 'image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
                    ]);

                    // Delete old logo if exists
                    if ($configuracion->valor && Storage::disk('public')->exists('images/logo/' . $configuracion->valor)) {
                        Storage::disk('public')->delete('images/logo/' . $configuracion->valor);
                    }

                    // Upload new logo
                    $logoName = time() . '.' . $request->logo->extension();
                    $request->logo->storeAs('images/logo', $logoName, 'public');
                    
                    $configuracion->update(['valor' => $logoName]);
                }
                
                // Update description if provided
                if ($request->has('descripcion')) {
                    $configuracion->update(['descripcion' => $request->descripcion]);
                }
            } else {
                $request->validate([
                    'valor' => 'required|string',
                    'descripcion' => 'nullable|string|max:255',
                ]);

                $configuracion->update([
                    'valor' => $request->valor,
                    'descripcion' => $request->descripcion,
                ]);
            }

            return redirect()->route('configuracion.index')->with('success', 'Configuración actualizada exitosamente.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('configuracion.index')->withErrors(['error' => 'Configuración no encontrada.']);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al actualizar la configuración: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $configuracion = Configuracion::findOrFail($id);
            
            // Delete logo file if it's a logo configuration
            if ($configuracion->clave === 'logo_institucional' && $configuracion->valor) {
                if (Storage::disk('public')->exists('images/logo/' . $configuracion->valor)) {
                    Storage::disk('public')->delete('images/logo/' . $configuracion->valor);
                }
            }
            
            $configuracion->delete();
            return redirect()->route('configuracion.index')->with('success', 'Configuración eliminada exitosamente.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Configuración no encontrada.'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al borrar la configuración: ' . $e->getMessage()], 500);
        }
    }
}
