<?php

namespace App\Http\Controllers;

use App\Models\Espacio;
use App\Models\Facultad;
use App\Models\Piso;
use App\Models\Universidad;
use Illuminate\Http\Request;

class GestionEspacioPisoController extends Controller
{
    public function index(Request $request)
    {
        $universidadId = $request->input('universidad');
        $facultadId = $request->input('facultad');
        $pisoId = $request->input('piso');

        // Obtener universidades
        $universidades = Universidad::all();

        // Obtener facultades filtradas por universidad
        $facultades = Facultad::when($universidadId, function ($query, $universidadId) {
            return $query->where('id_universidad', $universidadId);
        })->get();

        // Obtener pisos de la facultad seleccionada
        $pisosDeFacultad = Piso::when($facultadId, function ($query, $facultadId) {
            return $query->where('id_facultad', $facultadId);
        })->get();

        // Filtrar espacios según los filtros aplicados
        $espaciosFiltrados = Espacio::query()
            ->when($universidadId, function ($query, $universidadId) {
                return $query->whereHas('piso.facultad.universidad', function ($subQuery) use ($universidadId) {
                    $subQuery->where('id_universidad', $universidadId);
                });
            })
            ->when($facultadId, function ($query, $facultadId) {
                return $query->whereHas('piso', function ($subQuery) use ($facultadId) {
                    $subQuery->where('id_facultad', $facultadId);
                });
            })
            ->when($pisoId, function ($query, $pisoId) {
                return $query->where('piso_id', $pisoId);
            })
            ->get();

        // Devolver vista con los datos necesarios
        return view('layouts.floors-spaces.floors-spaces_index', compact(
            'espaciosFiltrados', 'universidades', 'facultades', 'pisosDeFacultad'
        ));
    }

    // Obtener pisos por facultad mediante Ajax
    public function obtenerPisos($facultadId)
    {
        $pisos = Piso::where('id_facultad', $facultadId)->get();
        return response()->json($pisos);
    }
}
