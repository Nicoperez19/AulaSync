<?php

namespace App\Http\Controllers;

class ControlDocenteController extends Controller
{
    /**
     * Vista combinada de Ausencias de Profesores + Recuperación de Clases.
     * Agrupa ambas funcionalidades en una sola interfaz con tabs.
     */
    public function ausenciasRecuperacion()
    {
        return view('control-docente.ausencias-recuperacion');
    }
}
