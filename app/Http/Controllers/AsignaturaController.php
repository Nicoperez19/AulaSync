<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Carrera;
use App\Models\Asignatura;
use Spatie\Permission\Models\Role;



use Illuminate\Http\Request;

class AsignaturaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
<<<<<<< HEAD
        $asignaturas = Asignatura::with('profesor', 'carrera')->paginate(10);
=======
        $asignaturas = Asignatura::with('user', 'carrera')->paginate(10);
>>>>>>> Nperez
        $usuarios = User::whereHas('roles', function ($query) {
            $query->where('name', 'Profesor');
        })->get();        
        $carreras = Carrera::all();

        return view('layouts.subjects.subject_index', compact('asignaturas', 'usuarios', 'carreras'));
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
<<<<<<< HEAD
        //
=======
        try {
            $request->validate([
                'id_asignatura' => 'required|string|max:20|unique:asignaturas,id_asignatura',
                'nombre_asignatura' => 'required|string|max:100',
                'horas_directas' => 'required|integer|min:0',
                'horas_indirectas' => 'required|integer|min:0',
                'area_conocimiento' => 'required|string|max:100',
                'periodo' => 'required|string|max:20',
                'run' => 'required|exists:users,run',
                'id_carrera' => 'required|exists:carreras,id_carrera',
            ]);

            Asignatura::create([
                'id_asignatura' => $request->id_asignatura,
                'codigo_asignatura' => $request->id_asignatura, // Usar el mismo valor como código
                'nombre_asignatura' => $request->nombre_asignatura,
                'horas_directas' => $request->horas_directas,
                'horas_indirectas' => $request->horas_indirectas,
                'area_conocimiento' => $request->area_conocimiento,
                'periodo' => $request->periodo,
                'run' => $request->run,
                'id_carrera' => $request->id_carrera,
            ]);

            return redirect()->route('asignaturas.index')->with('success', 'Asignatura creada exitosamente.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al crear la asignatura: ' . $e->getMessage()]);
        }
>>>>>>> Nperez
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
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

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Asignatura no encontrado.'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al borrar la asignatura: ' . $e->getMessage()], 500);
        }

    }
}
