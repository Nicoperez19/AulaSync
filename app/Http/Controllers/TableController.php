<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Tenant;

class TableController extends Controller
{
    public function index()
    {
        $tenantActual = Tenant::current();
        $nombreSedeActual = $tenantActual?->sede?->nombre_sede
            ?? $tenantActual?->name
            ?? 'Sede';

        return view('layouts.table.index', compact('nombreSedeActual'));
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