<?php

namespace App\Http\Controllers;

use App\Models\Visitante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VisitanteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('layouts.visitantes.visitantes_index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'run_solicitante' => 'required|string|max:255|unique:visitantes,run_solicitante',
            'nombre' => 'required|string|max:255',
            'correo' => 'required|email|max:255',
            'telefono' => 'nullable|string|max:255',
            'tipo_solicitante' => 'required|in:estudiante,personal,visitante,otro',
            'activo' => 'required|boolean',
        ], [
            'run_solicitante.required' => 'El RUN del solicitante es obligatorio.',
            'run_solicitante.unique' => 'Este RUN ya está registrado.',
            'nombre.required' => 'El nombre es obligatorio.',
            'correo.required' => 'El correo es obligatorio.',
            'correo.email' => 'Debe ser un correo válido.',
            'tipo_solicitante.required' => 'El tipo de solicitante es obligatorio.',
            'tipo_solicitante.in' => 'El tipo de solicitante no es válido.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            Visitante::create([
                'run_solicitante' => $request->run_solicitante,
                'nombre' => $request->nombre,
                'correo' => $request->correo,
                'telefono' => $request->telefono,
                'tipo_solicitante' => $request->tipo_solicitante,
                'activo' => $request->activo,
                'fecha_registro' => now(),
            ]);

            return redirect()->route('visitantes.index')
                ->with('success', 'Visitante creado exitosamente.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al crear el visitante: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $visitante = Visitante::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'run_solicitante' => 'required|string|max:255|unique:visitantes,run_solicitante,' . $id,
            'nombre' => 'required|string|max:255',
            'correo' => 'required|email|max:255',
            'telefono' => 'nullable|string|max:255',
            'tipo_solicitante' => 'required|in:estudiante,personal,visitante,otro',
            'activo' => 'required|boolean',
        ], [
            'run_solicitante.required' => 'El RUN del solicitante es obligatorio.',
            'run_solicitante.unique' => 'Este RUN ya está registrado.',
            'nombre.required' => 'El nombre es obligatorio.',
            'correo.required' => 'El correo es obligatorio.',
            'correo.email' => 'Debe ser un correo válido.',
            'tipo_solicitante.required' => 'El tipo de solicitante es obligatorio.',
            'tipo_solicitante.in' => 'El tipo de solicitante no es válido.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $visitante->update([
                'run_solicitante' => $request->run_solicitante,
                'nombre' => $request->nombre,
                'correo' => $request->correo,
                'telefono' => $request->telefono,
                'tipo_solicitante' => $request->tipo_solicitante,
                'activo' => $request->activo,
            ]);

            return redirect()->route('visitantes.index')
                ->with('success', 'Visitante actualizado exitosamente.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al actualizar el visitante: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $visitante = Visitante::findOrFail($id);
            $visitante->delete();

            return redirect()->route('visitantes.index')
                ->with('success', 'Visitante eliminado exitosamente.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al eliminar el visitante: ' . $e->getMessage());
        }
    }
}