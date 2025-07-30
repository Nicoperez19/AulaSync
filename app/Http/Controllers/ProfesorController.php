<?php

namespace App\Http\Controllers;

use App\Models\Profesor;
use App\Models\Universidad;
use App\Models\Facultad;
use App\Models\Carrera;
use App\Models\AreaAcademica;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProfesorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $profesores = Profesor::with('universidad', 'facultad', 'carrera', 'areaAcademica')->get();
            $universidades = Universidad::all();
            $facultades = Facultad::with('sede.universidad')->get();
            $carreras = Carrera::with('areaAcademica.facultad.sede.universidad')->get();
            $areasAcademicas = AreaAcademica::with('facultad.sede.universidad')->get();
            
            return view('layouts.professor.professor_index', compact('profesores', 'universidades', 'facultades', 'carreras', 'areasAcademicas'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Hubo un problema al cargar los profesores.'])->withInput();
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
                'run_profesor' => 'required|integer|digits_between:7,8|unique:profesors,run_profesor',
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:profesors,email',
                'celular' => 'nullable|string|regex:/^9\d{8}$/',
                'direccion' => 'nullable|string|max:255',
                'fecha_nacimiento' => 'nullable|date',
                'anio_ingreso' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
                'tipo_profesor' => 'required|in:Profesor Colaborador,Profesor Responsable,Ayudante',
                'id_universidad' => 'nullable|exists:universidades,id_universidad',
                'id_facultad' => 'nullable|exists:facultades,id_facultad',
                'id_carrera' => 'nullable|exists:carreras,id_carrera',
                'id_area_academica' => 'nullable|exists:area_academicas,id_area_academica',
            ]);

            Profesor::create($validatedData);

            return redirect()->route('professors.index')->with('success', 'Profesor creado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Hubo un problema al crear el profesor. Por favor, intente nuevamente.'])->withInput();
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
            $profesor = Profesor::findOrFail($id);
            $universidades = Universidad::all();
            $facultades = Facultad::with('sede.universidad')->get();
            $carreras = Carrera::with('areaAcademica.facultad.sede.universidad')->get();
            $areasAcademicas = AreaAcademica::with('facultad.sede.universidad')->get();
            
            return view('layouts.professor.professor_edit', compact('profesor', 'universidades', 'facultades', 'carreras', 'areasAcademicas'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('professors.index')->withErrors(['error' => 'Profesor no encontrado.']);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al cargar el profesor: ' . $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $request->validate([
                'run_profesor' => 'required|integer|digits_between:7,8|unique:profesors,run_profesor,' . $id . ',run_profesor',
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:profesors,email,' . $id . ',run_profesor',
                'celular' => 'nullable|string|regex:/^9\d{8}$/',
                'direccion' => 'nullable|string|max:255',
                'fecha_nacimiento' => 'nullable|date',
                'anio_ingreso' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
                'tipo_profesor' => 'required|in:Profesor Colaborador,Profesor Responsable,Ayudante',
                'id_universidad' => 'nullable|exists:universidades,id_universidad',
                'id_facultad' => 'nullable|exists:facultades,id_facultad',
                'id_carrera' => 'nullable|exists:carreras,id_carrera',
                'id_area_academica' => 'nullable|exists:area_academicas,id_area_academica',
            ]);

            $profesor = Profesor::findOrFail($id);
            $profesor->update([
                'run_profesor' => $request->run_profesor,
                'name' => $request->name,
                'email' => $request->email,
                'celular' => $request->celular,
                'direccion' => $request->direccion,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'anio_ingreso' => $request->anio_ingreso,
                'tipo_profesor' => $request->tipo_profesor,
                'id_universidad' => $request->id_universidad,
                'id_facultad' => $request->id_facultad,
                'id_carrera' => $request->id_carrera,
                'id_area_academica' => $request->id_area_academica,
            ]);

            return redirect()->route('professors.index')->with('success', 'Profesor actualizado exitosamente.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('professors.index')->withErrors(['error' => 'Profesor no encontrado.']);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al actualizar el profesor: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $profesor = Profesor::findOrFail($id);
            $profesor->delete();
            return redirect()->route('professors.index')->with('success', 'Profesor eliminado exitosamente.');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Profesor no encontrado.'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al borrar el profesor: ' . $e->getMessage()], 500);
        }
    }

    /**
     * API method for getting profesor information (existing method)
     */
    public function getProfesor($run)
    {
        try {
            $profesor = Profesor::where('run_profesor', $run)->first();

            if (!$profesor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profesor no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'profesor' => [
                    'name' => $profesor->name,
                    'email' => $profesor->email,
                    'run_profesor' => $profesor->run_profesor
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener información del profesor'
            ], 500);
        }
    }
} 