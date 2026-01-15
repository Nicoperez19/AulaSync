<?php

namespace App\Http\Controllers;

use App\Models\Profesor;
use App\Models\Carrera;
use App\Models\Asignatura;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use Spatie\Permission\Models\Role;



use Illuminate\Http\Request;

class AsignaturaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $asignaturas = Asignatura::with('profesor', 'carrera')->paginate(10);
        $profesores = Profesor::all();       
        $carreras = Carrera::all();

        return view('layouts.subjects.subject_index', compact('asignaturas', 'profesores', 'carreras'));
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
            $request->validate([
                'id_asignatura' => 'required|string|max:20|unique:tenant.asignaturas,id_asignatura',
                'codigo_asignatura' => 'required|string|max:100',
                'nombre_asignatura' => 'required|string|max:100',
                'seccion' => 'required|string|max:50',
                'horas_directas' => 'nullable|integer|min:0',
                'horas_indirectas' => 'nullable|integer|min:0',
                'area_conocimiento' => 'nullable|string|max:100',
                'periodo' => 'nullable|string|max:20',
                'run_profesor' => 'required|exists:tenant.profesors,run_profesor',
                'run_profesor_reemplazo' => 'nullable|exists:tenant.profesors,run_profesor',
                'id_carrera' => 'required|exists:carreras,id_carrera',
            ]);

            Asignatura::create([
                'id_asignatura' => $request->id_asignatura,
                'codigo_asignatura' => $request->codigo_asignatura,
                'nombre_asignatura' => $request->nombre_asignatura,
                'seccion' => $request->seccion,
                'horas_directas' => $request->horas_directas,
                'horas_indirectas' => $request->horas_indirectas,
                'area_conocimiento' => $request->area_conocimiento,
                'periodo' => $request->periodo,
                'run_profesor' => $request->run_profesor,
                'run_profesor_reemplazo' => $request->run_profesor_reemplazo,
                'id_carrera' => $request->id_carrera,
            ]);

            return redirect()->route('asignaturas.index')->with('success', 'Asignatura creada exitosamente.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al crear la asignatura: ' . $e->getMessage()]);
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
            $asignatura = Asignatura::findOrFail($id);
            $profesores = Profesor::all();
            $carreras = Carrera::all();
            
            return view('layouts.subjects.subject_edit', compact('asignatura', 'profesores', 'carreras'));
        } catch (ModelNotFoundException $e) {
            return redirect()->route('asignaturas.index')->withErrors(['error' => 'Asignatura no encontrada.']);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al cargar la asignatura: ' . $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $request->validate([
                'id_asignatura' => 'required|string|max:20|unique:tenant.asignaturas,id_asignatura,' . $id . ',id_asignatura',
                'codigo_asignatura' => 'required|string|max:100',
                'nombre_asignatura' => 'required|string|max:100',
                'seccion' => 'required|string|max:50',
                'horas_directas' => 'nullable|integer|min:0',
                'horas_indirectas' => 'nullable|integer|min:0',
                'area_conocimiento' => 'nullable|string|max:100',
                'periodo' => 'nullable|string|max:20',
                'run_profesor' => 'required|exists:tenant.profesors,run_profesor',
                'run_profesor_reemplazo' => 'nullable|exists:tenant.profesors,run_profesor',
                'id_carrera' => 'required|exists:carreras,id_carrera',
            ]);

            $asignatura = Asignatura::findOrFail($id);
            $asignatura->update([
                'id_asignatura' => $request->id_asignatura,
                'codigo_asignatura' => $request->codigo_asignatura,
                'nombre_asignatura' => $request->nombre_asignatura,
                'seccion' => $request->seccion,
                'horas_directas' => $request->horas_directas,
                'horas_indirectas' => $request->horas_indirectas,
                'area_conocimiento' => $request->area_conocimiento,
                'periodo' => $request->periodo,
                'run_profesor' => $request->run_profesor,
                'run_profesor_reemplazo' => $request->run_profesor_reemplazo,
                'id_carrera' => $request->id_carrera,
            ]);

            return redirect()->route('asignaturas.index')->with('success', 'Asignatura actualizada exitosamente.');
        } catch (ModelNotFoundException $e) {
            return redirect()->route('asignaturas.index')->withErrors(['error' => 'Asignatura no encontrada.']);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al actualizar la asignatura: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $asignaturas = Asignatura::findOrFail($id);
            $asignaturas->delete();
            return redirect()->route('asignaturas.index')->with('success', 'Asignatura eliminado exitosamente.');

        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Asignatura no encontrado.'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al borrar la asignatura: ' . $e->getMessage()], 500);
        }

    }
}
