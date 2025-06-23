<?php

namespace App\Http\Controllers;

use App\Models\Facultad;
use App\Models\Universidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FacultadController extends Controller
{
    public function index()
    {
        
    }

    public function store(Request $request)
    {
       
    }

    public function edit($id)
    {
       
    }

    public function update(Request $request, $id)
    {
        
    }

    public function destroy($id)
    {
        try {
            $facultad = Facultad::findOrFail($id);

            if ($facultad->logo_facultad && file_exists(public_path('images/logo_facultad/' . $facultad->logo_facultad))) {
                unlink(public_path('images/logo_facultad/' . $facultad->logo_facultad));
            }

            $facultad->delete();

            return redirect()->route('faculties.index')->with('success', 'Facultad eliminada exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar facultad: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->route('faculties.index')->with('error', 'Ocurrió un error al eliminar la facultad. Intenta de nuevo.');
        }
    }
}
