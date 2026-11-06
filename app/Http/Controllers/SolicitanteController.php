<?php

namespace App\Http\Controllers;

use App\Models\Solicitante;
use App\Models\Tenant;
use App\Models\Visitante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

use App\Traits\RunNormalizer;

/**
 * Controlador para manejar operaciones de solicitantes
 *
 * Los solicitantes son usuarios externos que pueden reservar espacios
 * pero no son profesores registrados en el sistema.
 */
class SolicitanteController extends Controller
{
    use RunNormalizer;

    /**
     * Verificar si un solicitante existe en la base de datos
     */
    public function verificarSolicitante($run)
    {
        // Obtener y configurar el tenant
        $tenantId = tenant_id();
        if (!$tenantId) {
            return response()->json([
                'verificado' => false,
                'mensaje' => 'No se encontró contexto de tenant'
            ], 400);
        }

        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            return response()->json([
                'verificado' => false,
                'mensaje' => 'Tenant no encontrado'
            ], 400);
        }

        // CONFIGURAR EXPLÍCITAMENTE LA CONEXIÓN TENANT
        Config::set('database.connections.tenant.database', $tenant->database);
        DB::purge('tenant');

        // Normalizar RUN
        $run = $this->normalizeRun($run);

        try {
            $solicitante = Solicitante::on('tenant')
                ->where('run_solicitante', $run)
                ->where('activo', true)
                ->first();


            if ($solicitante) {
                return response()->json([
                    'verificado' => true,
                    'tipo_usuario' => 'solicitante_registrado',
                    'solicitante' => [
                        'run_solicitante' => $solicitante->run_solicitante,
                        'nombre' => $solicitante->nombre,
                        'correo' => $solicitante->correo,
                        'telefono' => $solicitante->telefono,
                        'tipo_solicitante' => $solicitante->tipo_solicitante
                    ],
                    'mensaje' => 'Solicitante verificado correctamente'
                ]);
            }

            // Si no está registrado, retornar información para registro
            return response()->json([
                'verificado' => false,
                'tipo_usuario' => 'solicitante_nuevo',
                'run_escaneado' => $run,
                'mensaje' => 'Solicitante no registrado. Se requiere registro previo.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al verificar solicitante: ' . $e->getMessage());
            return response()->json([
                'verificado' => false,
                'mensaje' => 'Error al verificar solicitante: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Registrar un nuevo solicitante
     */
    public function registrarSolicitante(Request $request)
    {
        // Obtener y configurar el tenant
        $tenantId = tenant_id();
        if (!$tenantId) {
            return response()->json([
                'success' => false,
                'mensaje' => 'No se encontró contexto de tenant'
            ], 400);
        }

        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Tenant no encontrado'
            ], 400);
        }

        // CONFIGURAR EXPLÍCITAMENTE LA CONEXIÓN TENANT
        Config::set('database.connections.tenant.database', $tenant->database);
        DB::purge('tenant');

        try {
            $request->validate([
                'run_solicitante' => 'required|string',
                'nombre' => 'required|string|max:255',
                'correo' => 'required|email',
                'telefono' => 'required|string|max:20',
                'tipo_solicitante' => 'required|in:estudiante,personal,visitante,otro'
            ]);

            // Normalizar RUN
            $runNormalizado = $this->normalizeRun($request->run_solicitante);

            $solicitante = Solicitante::on('tenant')->updateOrCreate(
                ['run_solicitante' => $runNormalizado],
                [
                    'nombre' => $request->nombre,
                    'correo' => $request->correo,
                    'telefono' => $request->telefono,
                    'tipo_solicitante' => $request->tipo_solicitante,
                    'activo' => true,
                    'fecha_registro' => now()
                ]
            );


            // Registrar también como visitante para que aparezca en el mantenedor
            try {
                Visitante::create([
                    'run_solicitante' => $request->run_solicitante,
                    'nombre' => $request->nombre,
                    'correo' => $request->correo,
                    'telefono' => $request->telefono,
                    'tipo_solicitante' => $request->tipo_solicitante,
                    'activo' => true,
                    'fecha_registro' => now(),
                ]);
            } catch (\Exception $e) {
                // Si falla al crear el visitante, continuamos igual porque ya tenemos el solicitante
                Log::warning('No se pudo registrar como visitante: ' . $e->getMessage());
            }



            return response()->json([
                'success' => true,
                'mensaje' => 'Solicitante registrado exitosamente',
                'solicitante' => [
                    'run_solicitante' => $solicitante->run_solicitante,
                    'nombre' => $solicitante->nombre,
                    'correo' => $solicitante->correo,
                    'telefono' => $solicitante->telefono,
                    'tipo_solicitante' => $solicitante->tipo_solicitante,
                    'institucion_origen' => $solicitante->institucion_origen
                ]
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error de validación: ' . $e->getMessage(),
                'errores' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al registrar solicitante: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al registrar solicitante: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear una reserva para un solicitante
     */
    public function crearReservaSolicitante(Request $request)
    {
        return response()->json([
            'success' => false,
            'mensaje' => 'No se permite tomar reservas temporales desde el sistema de sala de profesores. Por favor, solicite la reserva con una Asistente.'
        ], 403);
    }

    /**
     * Verificar si un RUN existe en la tabla solicitantes
     */
    public function verificarExistencia($run)
    {
        try {
            $this->establecerContextoTenant();

            $runLimpio = preg_replace('/[^0-9]/', '', $run);

            $existe = Solicitante::on('tenant')
                ->where('run_solicitante', $run)
                ->orWhere('run_solicitante', $runLimpio)
                ->exists();

            return response()->json([
                'existe' => $existe
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'existe' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Establece el contexto del tenant desde el request
     */
    private function establecerContextoTenant()
    {
        try {
            $tenantId = tenant_id() ?? null;



            if (!$tenantId) {
                // Si no hay tenant establecido, buscarlo por dominio
                $host = request()->getHost();
                $tenant = Tenant::where('domain', $host)
                    ->orWhere('domain', 'LIKE', '%' . $host . '%')
                    ->first();

                if ($tenant) {
                    $tenantId = $tenant->id;
                    // CONFIGURAR EXPLÍCITAMENTE LA CONEXIÓN TENANT
                    Config::set('database.connections.tenant.database', $tenant->database);
                    DB::purge('tenant');

                } else {

                    return;
                }
            } else {
                // Si el tenant ya estaba establecido, asegurar que la conexión está bien configurada
                $tenant = Tenant::find($tenantId);
                if ($tenant) {
                    // CONFIGURAR EXPLÍCITAMENTE LA CONEXIÓN TENANT
                    Config::set('database.connections.tenant.database', $tenant->database);
                    DB::purge('tenant');


                    // VERIFICACIÓN DE CONEXIÓN
                    try {
                        DB::connection('tenant')->getPdo();

                    } catch (\Exception $e) {
                        Log::error('Error al verificar conexión tenant: ' . $e->getMessage());
                        throw $e;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error en establecerContextoTenant', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
