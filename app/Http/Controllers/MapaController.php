<?php

namespace App\Http\Controllers;

use App\Models\Mapa;
use Illuminate\Http\Request;

class MapaController extends Controller
{
    public function getBloques($mapaId)
    {
        $mapa = Mapa::findOrFail($mapaId);
        $bloques = $this->obtenerBloques($mapa);
        return response()->json($bloques);
    }
} 