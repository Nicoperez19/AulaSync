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
            // Estadísticas básicas
            $reservas_hoy = Reserva::whereDate('fecha_reserva', today())->count();
            $espacios_libres = Espacio::where('estado', 'Disponible')->count();
            $espacios_ocupados = Espacio::where('estado', 'Ocupado')->count();
            $espacios_mantencion = Espacio::where('estado', 'Mantenimiento')->count();

            return response()->json([
                'success' => true,
                'reservas_hoy' => $reservas_hoy,
                'espacios_libres' => $espacios_libres,
                'espacios_ocupados' => $espacios_ocupados,
                'espacios_mantencion' => $espacios_mantencion,
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener datos del dashboard: ' . $e->getMessage(),
                'reservas_hoy' => 0,
                'espacios_libres' => 0,
                'espacios_ocupados' => 0,
                'espacios_mantencion' => 0
            ], 500);
        }
    }

    /**
     * Obtener espacios para gestión
     */
    public function getEspacios(Request $request)
    {
        try {
            $query = Espacio::select('codigo', 'nombre', 'tipo', 'piso', 'capacidad', 'estado')
                ->orderBy('codigo');

            // Aplicar filtros si existen
            if ($request->has('estado') && $request->estado) {
                $query->where('estado', $request->estado);
            }

            if ($request->has('piso') && $request->piso) {
                $query->where('piso', $request->piso);
            }

            $espacios = $query->get();

            return response()->json([
                'success' => true,
                'data' => $espacios
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar espacios: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener reservas para gestión
     */
    public function getReservas(Request $request)
    {
        try {
            $query = Reserva::with(['espacio', 'profesor', 'solicitante'])
                ->orderBy('fecha_reserva', 'desc')
                ->orderBy('id_modulo');

            // Aplicar filtros si existen
            if ($request->has('estado') && $request->estado) {
                $query->where('estado_reserva', $request->estado);
            }

            if ($request->has('fecha') && $request->fecha) {
                $query->whereDate('fecha_reserva', $request->fecha);
            }

            $reservas = $query->get()->map(function ($reserva) {
                return [
                    'id' => $reserva->id,
                    'nombre_responsable' => $reserva->solicitante ? $reserva->solicitante->nombre_completo : 'N/A',
                    'run_responsable' => $reserva->solicitante ? $reserva->solicitante->run : 'N/A',
                    'codigo_espacio' => $reserva->espacio ? $reserva->espacio->codigo : 'N/A',
                    'fecha' => $reserva->fecha_reserva,
                    'modulo_inicial' => $reserva->id_modulo,
                    'modulo_final' => $reserva->id_modulo + ($reserva->duracion_modulos ?? 1) - 1,
                    'estado' => strtolower($reserva->estado_reserva ?? 'activa')
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $reservas
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar reservas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambiar estado de un espacio
     */
    public function cambiarEstadoEspacio(Request $request, $codigo)
    {
        try {
            $request->validate([
                'estado' => 'required|in:Disponible,Ocupado,Mantenimiento'
            ]);

            $espacio = Espacio::where('codigo', $codigo)->firstOrFail();
            $espacio->estado = $request->estado;
            $espacio->save();

            return response()->json([
                'success' => true,
                'message' => 'Estado del espacio actualizado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar estado del espacio: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambiar estado de una reserva
     */
    public function cambiarEstadoReserva(Request $request, $id)
    {
        try {
            $request->validate([
                'estado' => 'required|in:activa,finalizada,cancelada'
            ]);

            $reserva = Reserva::findOrFail($id);
            $reserva->estado_reserva = ucfirst($request->estado);
            $reserva->save();

            return response()->json([
                'success' => true,
                'message' => 'Estado de la reserva actualizado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar estado de la reserva: ' . $e->getMessage()
            ], 500);
        }
    }
}
