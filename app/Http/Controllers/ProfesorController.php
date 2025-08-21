<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Profesor;
use App\Models\Espacio;
use App\Models\Reserva;
use Illuminate\Support\Facades\Log;

class ProfesorController extends Controller
{
    /**
     * Crear reserva para profesor
     */
    public function crearReservaProfesor(Request $request)
    {
        try {
            $request->validate([
                'run_profesor' => 'required|string',
                'id_espacio' => 'required|string'
            ]);

            $runProfesor = $request->input('run_profesor');
            $idEspacio = $request->input('id_espacio');

            // Verificar que el espacio existe
            $espacio = Espacio::where('id_espacio', $idEspacio)->first();
            if (!$espacio) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Espacio no encontrado'
                ], 404);
            }

            // Verificar que el espacio esté disponible
            if ($espacio->estado !== 'Disponible') {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'El espacio no está disponible'
                ], 400);
            }

            $horaActual = now()->format('H:i:s');
            $fechaActual = now()->format('Y-m-d');
            
            // Validar horario académico
            $hora = (int)now()->format('H');
            $minutos = (int)now()->format('i');
            $horaEnMinutos = $hora * 60 + $minutos;
            
            $inicioAcademico = 8 * 60 + 10; // 08:10
            $finAcademico = 23 * 60; // 23:00
            
            if ($horaEnMinutos < $inicioAcademico || $horaEnMinutos >= $finAcademico) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'No se pueden crear reservas fuera del horario académico (08:10 - 23:00).'
                ], 400);
            }

            // Verificar si el profesor existe
            $profesor = Profesor::where('run_profesor', $runProfesor)->first();
            if (!$profesor) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Profesor no encontrado'
                ], 404);
            }

            // Verificar si ya tiene una reserva activa
            $reservaExistente = Reserva::where('run_profesor', $runProfesor)
                ->where('estado', 'activa')
                ->whereNull('hora_salida')
                ->first();

            if ($reservaExistente) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Ya tienes una reserva activa en otro espacio'
                ], 400);
            }

            // Crear la reserva
            $reserva = new Reserva();
            $reserva->id_reserva = 'R' . str_pad(Reserva::count() + 1, 3, '0', STR_PAD_LEFT);
            $reserva->run_profesor = $runProfesor;
            $reserva->id_espacio = $espacio->id_espacio;
            $reserva->fecha_reserva = $fechaActual;
            $reserva->hora = $horaActual;
            $reserva->estado = 'activa';
            $reserva->save();

            // Cambiar estado del espacio
            $espacio->estado = 'Ocupado';
            $espacio->save();

            return response()->json([
                'success' => true,
                'mensaje' => 'Reserva creada exitosamente para el profesor',
                'reserva' => [
                    'id' => $reserva->id_reserva,
                    'profesor' => $profesor->name,
                    'run_profesor' => $profesor->run_profesor,
                    'espacio' => $espacio->nombre_espacio,
                    'fecha' => $fechaActual,
                    'hora_inicio' => $horaActual
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error de validación al crear reserva de profesor: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'mensaje' => 'Error de validación en los datos enviados',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al crear reserva de profesor: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al crear reserva: ' . $e->getMessage()
            ], 500);
        }
    }
} 