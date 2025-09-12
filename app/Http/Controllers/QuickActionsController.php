<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Models\Reserva;
use App\Models\Espacio;
use App\Models\Profesor;
use App\Models\Solicitante;
use Carbon\Carbon;

class QuickActionsController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:acciones rapidas');
    }

    /**
     * Mostrar menú de acciones rápidas
     */
    public function index()
    {
        return view('quick_actions.index');
    }

    /**
     * Mostrar formulario para crear reserva
     */
    public function crearReserva()
    {
        return view('quick_actions.crear-reserva');
    }

    /**
     * Mostrar gestor de reservas
     */
    public function gestionarReservas()
    {
        return view('quick_actions.gestionar-reservas');
    }

    /**
     * Mostrar gestor de espacios
     */
    public function gestionarEspacios()
    {
        return view('quick_actions.gestionar-espacios');
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
            $query = Espacio::with('piso')
                ->select('id_espacio', 'nombre_espacio', 'tipo_espacio', 'piso_id', 'puestos_disponibles', 'estado')
                ->orderBy('id_espacio');

            // Aplicar filtros si existen
            if ($request->has('estado') && $request->estado) {
                $query->where('estado', $request->estado);
            }

            if ($request->has('piso') && $request->piso) {
                $query->where('piso_id', $request->piso);
            }

            $espaciosRaw = $query->get();
            
            // Transformar los datos para mantener compatibilidad con el frontend
            $espacios = $espaciosRaw->map(function ($espacio) {
                return [
                    'codigo' => $espacio->id_espacio,
                    'nombre' => $espacio->nombre_espacio,
                    'tipo' => $espacio->tipo_espacio,
                    'piso' => $espacio->piso ? $espacio->piso->numero_piso : $espacio->piso_id,
                    'capacidad' => $espacio->puestos_disponibles,
                    'estado' => $espacio->estado,
                    // Campos originales por si se necesitan
                    'id_espacio' => $espacio->id_espacio,
                    'nombre_espacio' => $espacio->nombre_espacio,
                    'piso_id' => $espacio->piso_id
                ];
            });

            return response()->json([
                'success' => true,
                'espacios' => $espacios,
                'data' => $espacios,
                'count' => $espacios->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener espacios en QuickActions: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar espacios: ' . $e->getMessage(),
                'espacios' => [],
                'error_details' => $e->getMessage()
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
                ->orderBy('hora');

            // Aplicar filtros si existen
            if ($request->has('estado') && $request->estado) {
                $query->where('estado', $request->estado);
            }

            if ($request->has('fecha') && $request->fecha) {
                $query->whereDate('fecha_reserva', $request->fecha);
            }

            $reservasRaw = $query->get();
            
            $reservas = $reservasRaw->map(function ($reserva) {
                // Determinar el responsable (profesor o solicitante)
                $nombreResponsable = 'N/A';
                $runResponsable = 'N/A';
                
                if ($reserva->profesor) {
                    $nombreResponsable = $reserva->profesor->name ?? ($reserva->profesor->nombre_completo ?? 'N/A');
                    $runResponsable = $reserva->profesor->run_profesor ?? 'N/A';
                } elseif ($reserva->solicitante) {
                    $nombreResponsable = $reserva->solicitante->nombre ?? ($reserva->solicitante->nombre_completo ?? 'N/A');
                    $runResponsable = $reserva->solicitante->run_solicitante ?? 'N/A';
                }
                
                return [
                    'id' => $reserva->id_reserva,
                    'nombre_responsable' => $nombreResponsable,
                    'run_responsable' => $runResponsable,
                    'codigo_espacio' => $reserva->espacio ? $reserva->espacio->id_espacio : 'N/A',
                    'nombre_espacio' => $reserva->espacio ? $reserva->espacio->nombre_espacio : 'N/A',
                    'fecha' => $reserva->fecha_reserva,
                    'hora' => $reserva->hora,
                    'modulos' => $reserva->modulos,
                    'tipo_reserva' => $reserva->tipo_reserva,
                    'estado' => strtolower($reserva->estado ?? 'activa'),
                    'observaciones' => $reserva->observaciones
                ];
            });

            return response()->json([
                'success' => true,
                'reservas' => $reservas,  // Añadido para consistencia con JavaScript
                'data' => $reservas       // Mantenemos 'data' por compatibilidad
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener reservas en QuickActions: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar reservas: ' . $e->getMessage(),
                'reservas' => []
            ], 500);
        }
    }

    /**
     * Buscar personas por RUN para autocompletado
     */
    public function buscarPersonas(Request $request)
    {
        try {
            $termino = $request->get('q', '');
            
            if (strlen($termino) < 2) {
                return response()->json([
                    'success' => true,
                    'personas' => []
                ]);
            }
            
            $personas = [];
            
            // Buscar en profesores (tabla: profesors)
            $profesores = Profesor::where('run_profesor', 'LIKE', '%' . $termino . '%')
                ->orWhere('name', 'LIKE', '%' . $termino . '%')
                ->limit(10)
                ->get();
                
            foreach ($profesores as $profesor) {
                $personas[] = [
                    'run' => $profesor->run_profesor,
                    'nombre' => $profesor->name,
                    'email' => $profesor->email ?? '',
                    'telefono' => $profesor->celular ?? '',
                    'tipo' => 'profesor', 
                    'display' => $profesor->run_profesor . ' - ' . $profesor->name . ' (Profesor)'
                ];
            }
            
            // Buscar en solicitantes (tabla: solicitantes)
            $solicitantes = Solicitante::where('run_solicitante', 'LIKE', '%' . $termino . '%')
                ->orWhere('nombre', 'LIKE', '%' . $termino . '%')
                ->where('activo', true)
                ->limit(10)
                ->get();
                
            foreach ($solicitantes as $solicitante) {
                $personas[] = [
                    'run' => $solicitante->run_solicitante,
                    'nombre' => $solicitante->nombre,
                    'email' => $solicitante->correo ?? '',
                    'telefono' => $solicitante->telefono ?? '',
                    'tipo' => 'solicitante',
                    'display' => $solicitante->run_solicitante . ' - ' . $solicitante->nombre . ' (Solicitante)'
                ];
            }
            
            return response()->json([
                'success' => true,
                'personas' => $personas,
                'count' => count($personas),
                'termino_buscado' => $termino
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al buscar personas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar personas: ' . $e->getMessage(),
                'personas' => [],
                'error_details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Procesar creación de nueva reserva 
     */
    public function procesarCrearReserva(Request $request)
    {
        try {
            Log::info('📝 Iniciando creación de reserva desde Quick Actions', $request->all());
            
            // Validar datos básicos
            $request->validate([
                'nombre' => 'required|string|max:255',
                'run' => 'required|string|max:20',
                'correo' => 'required|email|max:255',
                'tipo' => 'required|in:profesor,solicitante',
                'espacio' => 'required|string',
                'fecha' => 'required|date',
                'modulo_inicial' => 'required|integer|min:1|max:12',
                'modulo_final' => 'required|integer|min:1|max:12',
                'observaciones' => 'nullable|string|max:500'
            ]);

            // Verificar que el módulo inicial sea menor o igual al final
            if ($request->modulo_inicial > $request->modulo_final) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'El módulo inicial no puede ser mayor al módulo final'
                ], 400);
            }

            // Verificar que el espacio existe
            $espacio = Espacio::where('id_espacio', $request->espacio)->first();
            if (!$espacio) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'El espacio seleccionado no existe'
                ], 400);
            }

            // Generar ID único para la reserva
            $idReserva = 'RES-' . strtoupper(uniqid());

            // Mapeo de módulos a horarios
            $horariosModulos = [
                1 => '08:00:00', 2 => '08:45:00', 3 => '09:45:00', 4 => '10:30:00',
                5 => '11:30:00', 6 => '12:15:00', 7 => '14:00:00', 8 => '14:45:00',
                9 => '15:45:00', 10 => '16:30:00', 11 => '17:30:00', 12 => '18:15:00'
            ];

            // Calcular hora de inicio basada en el módulo inicial
            $horaInicio = $horariosModulos[$request->modulo_inicial] ?? '08:00:00';

            // Preparar observaciones con información de creación manual
            $usuario = auth()->user();
            $rangoModulos = "Módulos: " . $request->modulo_inicial . "-" . $request->modulo_final . " | ";
            $observacionesAutomaticas = "RESERVA CREADA MANUALMENTE por " . ($usuario->name ?? 'Administrador') . " el " . now()->format('d/m/Y H:i:s') . " | " . $rangoModulos;
            $observacionesCompletas = $observacionesAutomaticas . ($request->observaciones ?? '');

            // Preparar datos de la reserva
            // Campo modulos es unsignedSmallInteger - calculamos duración en módulos
            $duracionModulos = $request->modulo_final - $request->modulo_inicial + 1;
            
            $datosReserva = [
                'id_reserva' => $idReserva,
                'fecha_reserva' => $request->fecha,
                'id_espacio' => $request->espacio,
                'modulos' => $duracionModulos,
                'hora' => $horaInicio,
                'tipo_reserva' => $request->tipo === 'profesor' ? 'clase' : 'espontanea',
                'estado' => 'activa',
                'observaciones' => $observacionesCompletas,
                'created_at' => now(),
                'updated_at' => now()
            ];

            // Asignar responsable según el tipo
            if ($request->tipo === 'profesor') {
                // Buscar o crear profesor
                $profesor = Profesor::where('run_profesor', $request->run)->first();
                if (!$profesor) {
                    // Crear nuevo profesor básico
                    $profesor = Profesor::create([
                        'run_profesor' => $request->run,
                        'name' => $request->nombre,
                        'email' => $request->correo,
                        'celular' => $request->telefono ?? null,
                        'tipo_profesor' => 'Invitado'
                    ]);
                }
                $datosReserva['run_profesor'] = $request->run;
            } else {
                // Buscar o crear solicitante
                $solicitante = Solicitante::where('run_solicitante', $request->run)->first();
                if (!$solicitante) {
                    // Crear nuevo solicitante
                    $solicitante = Solicitante::create([
                        'run_solicitante' => $request->run,
                        'nombre' => $request->nombre,
                        'correo' => $request->correo,
                        'telefono' => $request->telefono ?? null,
                        'tipo_solicitante' => 'visitante',
                        'activo' => true,
                        'fecha_registro' => now()
                    ]);
                }
                $datosReserva['run_solicitante'] = $request->run;
            }

            // Crear la reserva
            $reserva = Reserva::create($datosReserva);

            Log::info('✅ Reserva creada exitosamente', ['id_reserva' => $idReserva]);

            return response()->json([
                'success' => true,
                'mensaje' => 'Reserva creada exitosamente',
                'id_reserva' => $idReserva,
                'datos' => [
                    'responsable' => $request->nombre,
                    'espacio' => $espacio->nombre_espacio,
                    'fecha' => $request->fecha,
                    'modulos' => $request->modulo_inicial . ' - ' . $request->modulo_final,
                    'hora' => $horaInicio,
                    'tipo' => $request->tipo === 'profesor' ? 'Académica' : 'Externa',
                    'creado_por' => $usuario->name ?? 'Administrador'
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('❌ Error de validación en creación de reserva', $e->errors());
            return response()->json([
                'success' => false,
                'mensaje' => 'Datos inválidos: ' . collect($e->errors())->flatten()->implode(', '),
                'errores' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('❌ Error al crear reserva en Quick Actions: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'mensaje' => 'Error interno del servidor: ' . $e->getMessage(),
                'error_details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambiar estado de un espacio
     */
    public function cambiarEstadoEspacio(Request $request, $codigo)
    {
        try {
            Log::info('🔄 Cambiando estado de espacio', [
                'codigo' => $codigo,
                'nuevo_estado' => $request->estado
            ]);

            // Validar el estado
            $request->validate([
                'estado' => 'required|in:Disponible,Ocupado,Mantenimiento'
            ]);

            // Buscar el espacio solo por id_espacio
            $espacio = Espacio::where('id_espacio', $codigo)->first();
            
            if (!$espacio) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Espacio no encontrado'
                ], 404);
            }

            // Actualizar estado - verificamos qué campo usar
            $estadoAnterior = $espacio->estado_espacio ?? $espacio->estado ?? 'Disponible';
            
            if (Schema::hasColumn('espacios', 'estado_espacio')) {
                $espacio->estado_espacio = $request->estado;
            } else {
                $espacio->estado = $request->estado;
            }
            
            $espacio->save();

            Log::info('✅ Estado de espacio actualizado', [
                'codigo' => $codigo,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => $request->estado
            ]);

            return response()->json([
                'success' => true,
                'mensaje' => "Estado del espacio {$codigo} cambiado de {$estadoAnterior} a {$request->estado}",
                'espacio' => [
                    'codigo' => $espacio->id_espacio,
                    'nombre' => $espacio->nombre_espacio,
                    'estado_anterior' => $estadoAnterior,
                    'estado_nuevo' => $request->estado
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Estado inválido: ' . collect($e->errors())->flatten()->implode(', ')
            ], 422);
        } catch (\Exception $e) {
            Log::error('❌ Error al cambiar estado de espacio: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'mensaje' => 'Error interno del servidor: ' . $e->getMessage()
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
