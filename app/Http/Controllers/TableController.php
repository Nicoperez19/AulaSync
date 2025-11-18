<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TableController extends Controller
{
    public function index()
    {
        return view('layouts.table.index');
    }

    public function actualizarDatos()
    {
        // Este endpoint puede ser usado para verificar el estado del servidor
        // El componente Livewire maneja la actualización de datos directamente
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toIso8601String()
        ]);
    }
} 