<?php

namespace App\Http\Controllers;

use App\Models\JefeCarrera;
use App\Models\Carrera;
use Illuminate\Http\Request;

class JefeCarreraController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $carreras = Carrera::with('areaAcademica.facultad.sede.universidad')->get();
            return view('layouts.jefes_carrera.jefe_carrera_index', compact('carreras'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Hubo un problema al cargar los jefes de carrera.'])->withInput();
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
                'nombre' => 'required|string|max:100',
                'email' => 'required|email|unique:jefes_carrera,email',
                'telefono' => 'nullable|string|max:20',
                'id_carrera' => 'required|exists:carreras,id_carrera',
            ]);

            JefeCarrera::create($validatedData);

            return redirect()->route('jefes-carrera.index')->with('success', 'Jefe de carrera creado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Hubo un problema al crear el jefe de carrera. Por favor, intente nuevamente.'])->withInput();
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
            $jefeCarrera = JefeCarrera::with('carrera')->findOrFail($id);
            $carreras = Carrera::with('areaAcademica.facultad.sede.universidad')->get();
            return view('layouts.jefes_carrera.jefe_carrera_edit', compact('jefeCarrera', 'carreras'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('jefes-carrera.index')->withErrors(['error' => 'Jefe de carrera no encontrado.']);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al cargar el jefe de carrera: ' . $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $request->validate([
                'nombre' => 'required|string|max:100',
                'email' => 'required|email|unique:jefes_carrera,email,' . $id,
                'telefono' => 'nullable|string|max:20',
                'id_carrera' => 'required|exists:carreras,id_carrera',
            ]);

            $jefeCarrera = JefeCarrera::findOrFail($id);
            $jefeCarrera->update([
                'nombre' => $request->nombre,
                'email' => $request->email,
                'telefono' => $request->telefono,
                'id_carrera' => $request->id_carrera,
            ]);

            return redirect()->route('jefes-carrera.index')->with('success', 'Jefe de carrera actualizado exitosamente.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('jefes-carrera.index')->withErrors(['error' => 'Jefe de carrera no encontrado.']);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al actualizar el jefe de carrera: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $jefeCarrera = JefeCarrera::findOrFail($id);
            $jefeCarrera->delete();
            return redirect()->route('jefes-carrera.index')->with('success', 'Jefe de carrera eliminado exitosamente.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Jefe de carrera no encontrado.'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al borrar el jefe de carrera: ' . $e->getMessage()], 500);
        }
    }
}
