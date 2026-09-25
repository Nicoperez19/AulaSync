<?php

namespace App\Http\Controllers;

use App\Http\Requests\CambiarEstadoEspacioRequest;
use App\Http\Requests\CrearReservaRapidaRequest;
use App\Http\Requests\ProcesarEscaneoSalaRequest;
use App\Models\Espacio;
use App\Models\Reserva;
use App\Services\EspacioService;
use App\Services\ReservaService;
use App\Services\SalaEstudioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QuickActionsController extends Controller
{
    protected ReservaService $reservaService;
    protected EspacioService $espacioService;
    protected SalaEstudioService $salaEstudioService;

    public function __construct(
        ReservaService $reservaService,
        EspacioService $espacioService,
        SalaEstudioService $salaEstudioService
    ) {
        $this->middleware('can:acciones rapidas');
        $this->reservaService = $reservaService;
        $this->espacioService = $espacioService;
        $this->salaEstudioService = $salaEstudioService;
    }

    /**
     * Mostrar menú de acciones rápidas.
     */
    public function index()
    {
        return view('acciones-rapidas.index');
    }

    /**
     * Mostrar formulario para crear reserva.
     */
    public function crearReserva()
    {
        return view('acciones-rapidas.crear-reserva');
    }

    /**
     * Mostrar gestor de reservas.
     */
    public function gestionarReservas()
    {
        return view('acciones-rapidas.gestionar-reservas');
    }

    /**
     * Mostrar gestor de espacios.
     */
    public function gestionarEspacios()
    {
        return view('acciones-rapidas.gestionar-espacios');
    }

    /**
     * Mostrar gestor de salas de estudio.
     */
    public function gestionarSalasEstudio()
    {
        return view('acciones-rapidas.gestionar-salas-estudio');
    }

    /**
     * Mostrar formulario de edición de reserva.
     */
    public function editarReserva($id)
    {
        try {
            $reserva = Reserva::where('id_reserva', $id)
                ->with(['espacio', 'profesor', 'solicitante'])
                ->first();

            if (!$reserva) {
                return redirect()
                    ->route('quick-actions.gestionar-reservas')
                    ->with('error', 'Reserva no encontrada');
            }

            if ($reserva->estado !== 'activa' && $reserva->estado !== 'programada') {
                return redirect()
                    ->route('quick-actions.gestionar-reservas')
                    ->with('error', 'Solo se pueden editar reservas activas o programadas');
            }

            $espacios = Espacio::select('id_espacio', 'nombre_espacio')
                ->orderBy('id_espacio')
                ->get();

            return view('acciones-rapidas.editar-reserva', compact('reserva', 'espacios'));
        } catch (\Exception $e) {
            Log::error('Error al cargar formulario de edición de reserva: ' . $e->getMessage());

            return redirect()
                ->route('quick-actions.gestionar-reservas')
                ->with('error', 'Error al cargar la reserva: ' . $e->getMessage());
        }
    }

    /**
     * Obtener datos resumidos para el dashboard.
     */
    public function getDashboardData(): JsonResponse
    {
        try {
            $fechaHoy = today()->format('Y-m-d');

            $reservas_hoy = Reserva::whereDate('fecha_reserva', $fechaHoy)
                ->whereIn('estado', ['activa', 'programada'])
                ->count();

            $espacios_mantencion = Espacio::whereIn('estado', ['Mantenimiento', 'Mantención'])->count();

            $espaciosConReservaActiva = Reserva::where('fecha_reserva', $fechaHoy)
                ->where('estado', 'activa')
                ->distinct('id_espacio')
                ->pluck('id_espacio');

            $espacios_ocupados = $espaciosConReservaActiva->count();
            $total_espacios = Espacio::count();
            $espacios_libres = max(0, $total_espacios - $espacios_ocupados - $espacios_mantencion);

            return response()->json([
                'success' => true,
                'reservas_hoy' => $reservas_hoy,
                'espacios_libres' => $espacios_libres,
                'espacios_ocupados' => $espacios_ocupados,
                'espacios_mantencion' => $espacios_mantencion,
                'timestamp' => now()->format('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener datos del dashboard: ' . $e->getMessage(),
                'reservas_hoy' => 0,
                'espacios_libres' => 0,
                'espacios_ocupados' => 0,
                'espacios_mantencion' => 0,
            ], 500);
        }
    }

    /**
     * Obtener espacios para gestión con filtros.
     */
    public function getEspacios(Request $request): JsonResponse
    {
        try {
            $espacios = $this->espacioService->getEspacios($request->all());

            return response()->json([
                'success' => true,
                'espacios' => $espacios,
                'data' => $espacios,
                'count' => count($espacios),
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener espacios en QuickActions: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al cargar espacios: ' . $e->getMessage(),
                'espacios' => [],
                'error_details' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cambiar el estado operativo de un espacio.
     */
    public function cambiarEstadoEspacio(CambiarEstadoEspacioRequest $request, $codigo): JsonResponse
    {
        try {
            $resultado = $this->espacioService->cambiarEstado($codigo, $request->estado);

            return response()->json($resultado, $resultado['status'] ?? 200);
        } catch (\Exception $e) {
            Log::error('❌ Error al cambiar estado de espacio: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'mensaje' => 'Error interno del servidor: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Liberar todos los espacios ocupados (Acción Masiva).
     */
    public function liberacionMasiva(Request $request): JsonResponse
    {
        try {
            $resultado = $this->espacioService->liberacionMasiva();

            return response()->json($resultado);
        } catch (\Exception $e) {
            Log::error('❌ Error en liberación masiva: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'mensaje' => 'Error al realizar la liberación masiva: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener reservas con relaciones ansiosas.
     */
    public function getReservas(Request $request): JsonResponse
    {
        try {
            $resultado = $this->reservaService->getReservas($request->all());

            return response()->json($resultado);
        } catch (\Exception $e) {
            Log::error('Error al obtener reservas: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener reservas: ' . $e->getMessage(),
                'reservas' => [],
                'total' => 0,
            ], 500);
        }
    }

    /**
     * Autocompletado de personas (profesores y solicitantes).
     */
    public function buscarPersonas(Request $request): JsonResponse
    {
        try {
            $termino = $request->get('q', '');
            $personas = $this->reservaService->buscarPersonas($termino);

            return response()->json([
                'success' => true,
                'personas' => $personas,
                'count' => count($personas),
                'termino_buscado' => $termino,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al buscar personas: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al buscar personas: ' . $e->getMessage(),
                'personas' => [],
                'error_details' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Buscar asignaturas por código o nombre.
     */
    public function buscarAsignaturas(Request $request): JsonResponse
    {
        try {
            $termino = $request->input('q', '');
            if (strlen($termino) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'El término de búsqueda debe tener al menos 2 caracteres',
                    'asignaturas' => [],
                ]);
            }

            $asignaturas = $this->reservaService->buscarAsignaturas($termino);

            return response()->json([
                'success' => true,
                'asignaturas' => $asignaturas,
                'count' => count($asignaturas),
                'termino_buscado' => $termino,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al buscar asignaturas: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al buscar asignaturas: ' . $e->getMessage(),
                'asignaturas' => [],
                'error_details' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verificar conflictos de horarios antes de reservar.
     */
    public function verificarConflictos(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'espacio' => 'required|string',
                'fecha' => 'required|date',
                'modulo_inicial' => 'required|integer|min:1|max:16',
                'modulo_final' => 'required|integer|min:1|max:16',
            ]);

            $resultado = $this->reservaService->verificarConflictos($request->all());

            return response()->json(array_merge(['success' => true], $resultado));
        } catch (\Exception $e) {
            Log::error('Error al verificar conflictos: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'mensaje' => 'Error al verificar conflictos: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Crear una reserva (puntual o recurrente).
     */
    public function procesarCrearReserva(CrearReservaRapidaRequest $request): JsonResponse
    {
        try {
            $resultado = $this->reservaService->crearReserva($request->validated(), $request->user());

            return response()->json($resultado, $resultado['status'] ?? 200);
        } catch (\Exception $e) {
            Log::error('❌ Error general al crear reserva: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'mensaje' => 'Error interno del servidor: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cambiar estado de una reserva.
     */
    public function cambiarEstadoReserva(Request $request, $id): JsonResponse
    {
        try {
            $request->validate([
                'estado' => 'required|in:activa,programada,finalizada,cancelada',
            ]);

            $resultado = $this->reservaService->cambiarEstadoReserva((string) $id, $request->estado);

            return response()->json($resultado, $resultado['status'] ?? 200);
        } catch (\Exception $e) {
            Log::error('❌ Error al cambiar estado de reserva: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'mensaje' => 'Error interno del servidor: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Actualizar datos de una reserva existente.
     */
    public function actualizarReserva(Request $request, $id): JsonResponse
    {
        try {
            $request->validate([
                'id_espacio' => 'required|string',
                'fecha' => 'required|date',
                'hora' => 'required',
                'modulos' => 'required|integer|min:1',
                'modulo_inicio' => 'nullable|integer',
                'modulo_fin' => 'nullable|integer',
                'observaciones' => 'nullable|string',
                'nuevo_run_profesor' => 'nullable|string',
            ]);

            $resultado = $this->reservaService->actualizarReserva((string) $id, $request->all(), $request->user());

            return response()->json($resultado, $resultado['status'] ?? 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Datos inválidos: ' . collect($e->errors())->flatten()->implode(', '),
            ], 422);
        } catch (\Exception $e) {
            Log::error('❌ Error al actualizar reserva: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'mensaje' => 'Error interno del servidor: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener reservas de salas de estudio en JSON con datos enriquecidos.
     */
    public function getReservasSalasEstudio(Request $request): JsonResponse
    {
        try {
            $resultado = $this->salaEstudioService->getReservas($request->all());

            return response()->json(array_merge(['success' => true], $resultado));
        } catch (\Exception $e) {
            Log::error('Error en getReservasSalasEstudio: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'reservas' => [],
            ], 500);
        }
    }

    /**
     * Procesar escaneo de carnet para salas de estudio.
     */
    public function procesarEscaneoSalaEstudio(ProcesarEscaneoSalaRequest $request): JsonResponse
    {
        try {
            $resultado = $this->salaEstudioService->procesarEscaneo($request->validated());

            return response()->json($resultado, $resultado['status'] ?? 200);
        } catch (\Exception $e) {
            Log::error('Error en procesarEscaneoSalaEstudio: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar escaneo: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verificar en segundo plano reservas activas de salas de estudio para notificaciones.
     */
    public function verificarNotificacionesSalasEstudio(): JsonResponse
    {
        try {
            $resultado = $this->salaEstudioService->verificarNotificaciones();

            return response()->json(array_merge(['success' => true], $resultado));
        } catch (\Exception $e) {
            Log::error('Error en verificarNotificacionesSalasEstudio: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
