<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reserva;
use App\Models\Espacio;
use App\Models\Profesor;
use App\Models\Solicitante;
use Carbon\Carbon;

class QuickActionsController extends Controller
{
    /**
     * Mostrar menú de acciones rápidas
     */
    public function index()
    {
        return view('layouts.quick_actions.index');
    }

    /**
     * Mostrar formulario para crear reserva
     */
    public function crearReserva()
    {
        return view('layouts.quick_actions.crear-reserva');
    }

    /**
     * Mostrar gestor de reservas
     */
    public function gestionarReservas()
    {
        return view('layouts.quick_actions.gestionar-reservas');
    }

    /**
     * Mostrar gestor de espacios
     */
    public function gestionarEspacios()
    {
        return view('layouts.quick_actions.gestionar-espacios');
    }

    /**
     * Obtener datos para el dashboard
     */
    public function getDashboardData()
    {
        try {
            $estadisticas = [
                'reservas_hoy' => Reserva::whereDate('fecha_reserva', today())->count(),
                'reservas_semana' => Reserva::whereBetween('fecha_reserva', [
                    now()->startOfWeek(), 
                    now()->endOfWeek()
                ])->count(),
                'espacios_ocupados' => Espacio::where('estado', 'Ocupado')->count(),
                'espacios_disponibles' => Espacio::where('estado', 'Disponible')->count(),
            ];

            // Reservas recientes
            $reservas_recientes = Reserva::with(['espacio', 'profesor', 'solicitante'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            // Espacios por estado
            $espacios_por_estado = Espacio::selectRaw('estado, COUNT(*) as cantidad')
                ->groupBy('estado')
                ->get();

            return response()->json([
                'success' => true,
                'estadisticas' => $estadisticas,
                'reservas_recientes' => $reservas_recientes,
                'espacios_por_estado' => $espacios_por_estado
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener datos del dashboard: ' . $e->getMessage()
            ], 500);
        }
    }
}
