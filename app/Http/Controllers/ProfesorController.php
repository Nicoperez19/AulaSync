<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class ProfesorController extends Controller
{
    public function getProfesor($run)
    {
        try {
            $profesor = User::where('run', $run)
                ->whereHas('roles', function($query) {
                    $query->where('name', 'Profesor');
                })
                ->first();

            if (!$profesor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profesor no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'profesor' => [
                    'name' => $profesor->name,
                    'email' => $profesor->email,
                    'run' => $profesor->run
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener información del profesor'
            ], 500);
        }
    }
} 