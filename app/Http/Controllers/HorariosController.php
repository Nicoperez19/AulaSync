<?php

namespace App\Http\Controllers;

use App\Models\Horario;
use App\Models\User;
use App\Models\Planificacion_Asignatura;
use App\Models\Asignatura;
use App\Models\Modulo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HorariosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $profesores = User::role('Profesor')->get();
        $horarios = Horario::with(['docente', 'planificaciones.asignatura', 'planificaciones.espacio'])->get();
        $horarios = $horarios->groupBy('run');
        return view('layouts.schedules.schedules_index', compact('profesores', 'horarios'));
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
        //
    }

    public function getHorarioProfesor($run)
    {
        try {
            $horario = Horario::with(['docente', 'planificaciones.asignatura', 'planificaciones.espacio'])
                ->where('run', $run)
                ->first();

            if (!$horario) {
                return response()->json(['error' => 'Horario no encontrado'], 404);
            }

            // Obtener todas las asignaturas que imparte el profesor
            $asignaturas = Asignatura::where('run', $run)
                ->with(['planificaciones.espacio', 'planificaciones.modulo'])
                ->get();

            // Obtener todos los módulos
            $modulos = Modulo::orderBy('hora_inicio')->get();

            // Log para depuración
            Log::info('Datos del horario:', [
                'horario' => $horario->toArray(),
                'asignaturas' => $asignaturas->toArray(),
                'modulos' => $modulos->toArray()
            ]);

            return response()->json([
                'horario' => $horario,
                'asignaturas' => $asignaturas,
                'modulos' => $modulos
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener horario:', [
                'error' => $e->getMessage(),
                'run' => $run
            ]);
            return response()->json(['error' => 'Error al obtener el horario'], 500);
        }
    }
}
