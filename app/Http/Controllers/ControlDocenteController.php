<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ControlDocenteController extends Controller
{
    /**
     * Vista combinada de Ausencias de Profesores + Recuperación de Clases.
     * Agrupa ambas funcionalidades en una sola interfaz con tabs.
     */
    public function ausenciasRecuperacion()
    {
        return view('layouts.control-docente.ausencias-recuperacion');
    }
}
