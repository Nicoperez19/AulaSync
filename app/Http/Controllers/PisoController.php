<?php
namespace App\Http\Controllers;

use App\Models\Piso;
use App\Models\Facultad;
use App\Models\Universidad;
use Illuminate\Http\Request;

class PisoController extends Controller
{
    public function index(Request $request)
    {
        $universidadId = $request->input('universidad');
        $facultades = Facultad::when($universidadId, function ($query, $universidadId) {
            return $query->where('id_universidad', $universidadId);
        })->withCount('pisos')->get();

        return view('layouts.floors.floors_index', [
            'universidades' => Universidad::all(),
            'facultades' => $facultades,
        ]);
    }

    public function agregarPiso(Request $request, $facultadId)
    {
        try {
            $facultad = Facultad::findOrFail($facultadId);

            $ultimoPiso = Piso::where('id_facultad', $facultad->id_facultad)
                ->orderBy('numero_piso', 'desc')
                ->first();

            $nuevoNumeroPiso = $ultimoPiso ? $ultimoPiso->numero_piso + 1 : 1;

            $piso = new Piso();
            $piso->numero_piso = $nuevoNumeroPiso;
            $piso->id_facultad = $facultad->id_facultad;
            $piso->save();

            return redirect()->route('floors.index', ['facultad' => $facultadId])
                ->with('success', 'Piso agregado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->route('floors.index', ['facultad' => $facultadId])
                ->with('error', 'Error al agregar el piso: ' . $e->getMessage());
        }
    }

    public function eliminarPiso(Request $request, $facultadId)
    {
        try {
            $facultad = Facultad::findOrFail($facultadId);

            $ultimoPiso = Piso::where('id_facultad', $facultadId)->latest('numero_piso')->first();

            if ($ultimoPiso) {
                $ultimoPiso->delete();
                return redirect()->route('floor.index', ['facultad' => $facultadId])
                    ->with('success', 'Último piso eliminado exitosamente.');
            }

            return redirect()->route('floors.index', ['facultad' => $facultadId])
                ->with('error', 'No hay pisos para eliminar en esta facultad.');

        } catch (\Exception $e) {
            return redirect()->route('floors.index', ['facultad' => $facultadId])
                ->with('error', 'Error al eliminar el piso: ' . $e->getMessage());
        }
    }
}
