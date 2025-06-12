<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReservaController extends Controller
{
    public function getReservaActiva($id)
    {
        try {
            // Primero verificamos el estado del espacio
            $espacio = \App\Models\Espacio::where('id_espacio', $id)->first();
            
            if (!$espacio) {
                return response()->json([
                    'success' => false,
                    'message' => 'Espacio no encontrado'
                ], 404);
            }

            // Si el espacio está ocupado pero no hay reserva activa
            if ($espacio->estado === 'Ocupado') {
                // Buscar la última reserva activa para este espacio
                $ultimaReserva = Reserva::where('id_espacio', $id)
                    ->where('estado', 'activa')
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($ultimaReserva) {
                    return response()->json([
                        'success' => true,
                        'reserva' => [
                            'id' => $ultimaReserva->id_reserva,
                            'profesor_nombre' => $ultimaReserva->user->name,
                            'profesor_email' => $ultimaReserva->user->email,
                            'hora_inicio' => $ultimaReserva->hora,
                            'hora_termino' => $ultimaReserva->hora_salida,
                            'fecha' => $ultimaReserva->fecha_reserva,
                            'espacio_nombre' => $espacio->nombre_espacio,
                            'tipo_reserva' => 'Ocupación sin reserva',
                            'estado_espacio' => 'Ocupado'
                        ]
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'reserva' => [
                        'id' => null,
                        'profesor_nombre' => 'Sin información',
                        'profesor_email' => 'Sin información',
                        'hora_inicio' => null,
                        'hora_termino' => null,
                        'fecha' => null,
                        'espacio_nombre' => $espacio->nombre_espacio,
                        'tipo_reserva' => 'Ocupación sin reserva',
                        'estado_espacio' => 'Ocupado'
                    ]
                ]);
            }

            // Si no está ocupado, buscamos reservas activas
            $reserva = Reserva::where('id_espacio', $id)
                ->where('fecha', Carbon::today())
                ->where('hora_inicio', '<=', Carbon::now()->format('H:i:s'))
                ->where('hora_termino', '>=', Carbon::now()->format('H:i:s'))
                ->with(['usuario', 'espacio'])
                ->first();

            if (!$reserva) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay reserva activa para este espacio'
                ]);
            }

            return response()->json([
                'success' => true,
                'reserva' => [
                    'id' => $reserva->id_reserva,
                    'profesor_nombre' => $reserva->usuario->name,
                    'profesor_email' => $reserva->usuario->email,
                    'hora_inicio' => $reserva->hora_inicio,
                    'hora_termino' => $reserva->hora_termino,
                    'fecha' => $reserva->fecha,
                    'espacio_nombre' => $reserva->espacio->nombre_espacio,
                    'tipo_reserva' => $reserva->tipo_reserva,
                    'estado_espacio' => $espacio->estado
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al obtener reserva activa: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la reserva activa'
            ], 500);
        }
    }
} 