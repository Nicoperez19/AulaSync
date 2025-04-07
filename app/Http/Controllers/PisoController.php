<?php

namespace App\Http\Controllers;

use App\Models\Espacio;
use App\Models\Piso;
use App\Models\Facultad;
use App\Models\Universidad;

use Illuminate\Http\Request;

class PisoController extends Controller
{

    public function index(Request $request)
    {
        $universidadId = $request->input('universidad');
        $facultadId = $request->input('facultad');
        $pisoId = $request->input('piso');

        $universidades = Universidad::all();
        $facultades = Facultad::when($universidadId, function ($query, $universidadId) {
            $query->where('universidad_id', $universidadId);
        })->get();

        $pisos = Piso::when($facultadId, function ($query, $facultadId) {
            $query->where('facultad_id', $facultadId);
        })->get();

        $espaciosFiltrados = Piso::with('espacios', 'facultad', 'facultad.universidad')
            ->when($pisoId, function ($query, $pisoId) {
                $query->where('id', $pisoId);
            })
            ->when($facultadId, function ($query, $facultadId) {
                $query->where('facultad_id', $facultadId);
            })
            ->when($universidadId, function ($query, $universidadId) {
                $query->whereHas('facultad.universidad', function ($subQuery) use ($universidadId) {
                    $subQuery->where('id', $universidadId);
                });
            })
            ->get()
            ->flatMap(function ($piso) {
                return $piso->espacios->map(function ($espacio) use ($piso) {
                    $espacio->piso = $piso;
                    $espacio->facultad = $piso->facultad;
                    $espacio->universidad = $piso->facultad->universidad;
                    return $espacio;
                });
            });

            return view('layouts.floors-spaces.floors-spaces_index', compact('espaciosFiltrados', 'universidades', 'facultades', 'pisos'));    }
}