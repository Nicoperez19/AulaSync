<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Carrera;
use App\Models\Asignatura;



use Illuminate\Http\Request;

class AsignaturaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $asignaturas = Asignatura::with('profesor', 'carrera')->paginate(10); // Asegúrate de cargar la relación 'usuario'
        $usuarios = User::all();
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
        //
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
