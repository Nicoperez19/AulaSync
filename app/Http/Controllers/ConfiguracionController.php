<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

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
            // Determine the actual key value
            $clave = $request->clave === 'other' ? $request->clave_custom : $request->clave;

            // Special handling for logo upload
            if ($clave === 'logo_institucional' && $request->hasFile('logo')) {
                $request->validate([
                    'clave' => 'required|string',
                    'id_sede' => 'required|exists:sedes,id_sede',
                    'clave_custom' => 'nullable|string|max:100|unique:configuracion,clave',
                    'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                    'descripcion' => 'nullable|string|max:255',
                ]);

                // Construir la clave con el ID de sede
                $claveCompleta = "logo_institucional_{$request->id_sede}";

                // Verificar si ya existe un logo para esta sede
                $existingLogo = Configuracion::where('clave', $claveCompleta)->first();
                if ($existingLogo) {
                    return redirect()->back()->withErrors(['error' => "Ya existe un logo para la sede {$request->id_sede}. Por favor, edítelo en lugar de crear uno nuevo."])->withInput();
                }

                // Upload logo
                $logoName = time() . '_' . $request->id_sede . '.' . $request->logo->extension();
                $request->logo->storeAs('images/logo', $logoName, 'public');
                
                Configuracion::create([
                    'clave' => $claveCompleta,
                    'valor' => $logoName,
                    'descripcion' => $request->descripcion ?? "Logo institucional de la sede {$request->id_sede}",
                ]);

                // Clear logo cache for this sede
                Cache::forget("logo_institucional_path_{$request->id_sede}");
            } 
            else {
                $request->validate([
                    'clave' => 'required|string',
                    'clave_custom' => 'nullable|string|max:100|unique:configuracion,clave',
                    'valor' => 'required|string',
                    'descripcion' => 'nullable|string|max:255',
                ]);

                Configuracion::create([
                    'clave' => $clave,
                    'valor' => $request->valor,
                    'descripcion' => $request->descripcion,
                ]);
            }

            return redirect()->route('configuracion.index')->with('success', 'Configuración creada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Hubo un problema al crear la configuración. Por favor, intente nuevamente. ' . $e->getMessage()])->withInput();
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
            if (str_starts_with($configuracion->clave, 'logo_institucional_')) {
                if ($request->hasFile('logo')) {
                    $request->validate([
                        'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                        'descripcion' => 'nullable|string|max:255',
                    ]);

                    // Delete old logo if exists
                    if ($configuracion->valor && Storage::disk('public')->exists('images/logo/' . $configuracion->valor)) {
                        Storage::disk('public')->delete('images/logo/' . $configuracion->valor);
                    }

                    // Extract sede ID from clave
                    $idSede = str_replace('logo_institucional_', '', $configuracion->clave);

                    // Upload new logo
                    $logoName = time() . '_' . $idSede . '.' . $request->logo->extension();
                    $request->logo->storeAs('images/logo', $logoName, 'public');
                    
                    $configuracion->update([
                        'valor' => $logoName,
                        'descripcion' => $request->descripcion,
                    ]);

                    // Clear logo cache for this sede
                    Cache::forget("logo_institucional_path_{$idSede}");
                } else {
                    // Only update description if no new logo is uploaded
                    $request->validate([
                        'descripcion' => 'nullable|string|max:255',
                    ]);

                    $configuracion->update([
                        'descripcion' => $request->descripcion,
                    ]);
                }
            } 
            // Special handling for administrative email
            else if (str_starts_with($configuracion->clave, 'correo_administrativo_')) {
                $request->validate([
                    'valor' => 'required|email|max:255',
                    'nombre_remitente' => 'nullable|string|max:255',
                    'descripcion' => 'nullable|string|max:255',
                ]);

                // Extract sede ID from clave
                $idSede = str_replace('logo_institucional_', '', $configuracion->clave);

                // If new logo is uploaded
                if ($request->hasFile('logo')) {
                    // Delete old logo file
                    if ($configuracion->valor && Storage::disk('public')->exists('images/logo/' . $configuracion->valor)) {
                        Storage::disk('public')->delete('images/logo/' . $configuracion->valor);
                    }

                    // Upload new logo
                    $logoName = time() . '_' . $idSede . '.' . $request->logo->extension();
                    $request->logo->storeAs('images/logo', $logoName, 'public');
                    
                    $configuracion->update([
                        'valor' => $logoName,
                        'descripcion' => $request->descripcion,
                    ]);
                } else {
                    $configuracion->update([
                        'descripcion' => $request->descripcion,
                    ]);
                }

                // Clear logo cache for this sede
                Cache::forget("logo_institucional_path_{$idSede}");
            }
            else {
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
            return back()->withErrors(['error' => 'Error al actualizar la configuración: ' . $e->getMessage()]);
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
            if (str_starts_with($configuracion->clave, 'logo_institucional_') && $configuracion->valor) {
                if (Storage::disk('public')->exists('images/logo/' . $configuracion->valor)) {
                    Storage::disk('public')->delete('images/logo/' . $configuracion->valor);
                }
                // Extract sede ID and clear cache
                $idSede = str_replace('logo_institucional_', '', $configuracion->clave);
                Cache::forget("logo_institucional_path_{$idSede}");
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
