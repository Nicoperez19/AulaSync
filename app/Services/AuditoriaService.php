<?php

namespace App\Services;

use App\Models\AuditoriaClase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuditoriaService
{
    /**
     * UUID del lote activo en caso de operaciones masivas
     */
    protected static ?string $loteActivo = null;

    /**
     * Inicia un nuevo lote de auditoría para operaciones masivas
     */
    public static function iniciarLote(string $accion = 'operacion_masiva'): string
    {
        static::$loteActivo = (string) Str::uuid();
        return static::$loteActivo;
    }

    /**
     * Cierra el lote activo actual
     */
    public static function cerrarLote(): void
    {
        static::$loteActivo = null;
    }

    /**
     * Obtiene el ID del lote activo o null
     */
    public static function getLoteActivo(): ?string
    {
        return static::$loteActivo;
    }

    /**
     * Registra un evento de auditoría para una clase o entidad
     */
    public function registrar(
        Model $entidad,
        string $accion,
        ?array $valoresAnteriores = null,
        ?array $valoresNuevos = null,
        ?string $motivo = null,
        ?string $observaciones = null,
        ?string $estadoAnterior = null,
        ?string $estadoNuevo = null,
        string $origen = 'web'
    ): AuditoriaClase {
        $user = auth()->user();
        $userNombre = $user ? $user->name : 'Sistema';
        $userRol = null;

        if ($user) {
            try {
                if (method_exists($user, 'getRoleNames')) {
                    $userRol = $user->getRoleNames()->first();
                } elseif (isset($user->rol)) {
                    $userRol = $user->rol;
                }
            } catch (\Throwable $e) {
                // Silencioso
            }
        }

        // Si no se pasaron explícitamente los estados, intentar inferirlos de los valores
        if ($estadoAnterior === null && isset($valoresAnteriores['estado'])) {
            $estadoAnterior = $valoresAnteriores['estado'];
        }
        if ($estadoNuevo === null && isset($valoresNuevos['estado'])) {
            $estadoNuevo = $valoresNuevos['estado'];
        }

        $ip = null;
        $userAgent = null;
        try {
            if (request()) {
                $ip = request()->ip();
                $userAgent = substr((string) request()->userAgent(), 0, 500);
            }
        } catch (\Throwable $e) {
            // Contexto CLI
        }

        $tenantId = null;
        if (class_exists(\App\Models\Tenant::class)) {
            $tenantId = \App\Models\Tenant::current()?->id;
        }

        $fechaClase = $entidad->fecha_clase ?? ($valoresNuevos['fecha_clase'] ?? ($valoresAnteriores['fecha_clase'] ?? null));
        if ($fechaClase instanceof \DateTimeInterface) {
            $fechaClase = $fechaClase->format('Y-m-d');
        } elseif (is_string($fechaClase) && !empty($fechaClase)) {
            try {
                $fechaClase = \Carbon\Carbon::parse($fechaClase)->format('Y-m-d');
            } catch (\Throwable $e) {
                // Mantener valor tal cual
            }
        }

        return AuditoriaClase::create([
            'tenant_id' => $tenantId,
            'user_id' => $user?->id,
            'user_nombre' => $userNombre,
            'user_rol' => $userRol,
            'accion' => $accion,
            'entidad_tipo' => get_class($entidad),
            'entidad_id' => $entidad->getKey(),
            'run_profesor' => $entidad->run_profesor ?? ($valoresNuevos['run_profesor'] ?? ($valoresAnteriores['run_profesor'] ?? null)),
            'id_asignatura' => $entidad->id_asignatura ?? ($valoresNuevos['id_asignatura'] ?? ($valoresAnteriores['id_asignatura'] ?? null)),
            'id_espacio' => $entidad->id_espacio ?? ($valoresNuevos['id_espacio'] ?? ($valoresAnteriores['id_espacio'] ?? null)),
            'id_modulo' => $entidad->id_modulo ?? ($valoresNuevos['id_modulo'] ?? ($valoresAnteriores['id_modulo'] ?? null)),
            'fecha_clase' => $fechaClase,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $estadoNuevo,
            'valores_anteriores' => $valoresAnteriores,
            'valores_nuevos' => $valoresNuevos,
            'motivo' => $motivo,
            'observaciones' => $observaciones,
            'lote_uuid' => static::$loteActivo,
            'origen' => $origen,
            'ip' => $ip,
            'user_agent' => $userAgent,
            'created_at' => now(),
        ]);
    }

    /**
     * Obtiene el historial cronológico de auditoría para una clase
     */
    public function obtenerHistorialClase($entidadId, $runProfesor = null, $fechaClase = null)
    {
        $query = AuditoriaClase::query()->with('user');

        if ($entidadId) {
            $query->where(function ($q) use ($entidadId, $runProfesor, $fechaClase) {
                $q->where('entidad_id', $entidadId);
                if ($runProfesor && $fechaClase) {
                    $q->orWhere(function ($sub) use ($runProfesor, $fechaClase) {
                        $sub->where('run_profesor', $runProfesor)
                            ->where('fecha_clase', $fechaClase);
                    });
                }
            });
        } elseif ($runProfesor && $fechaClase) {
            $query->where('run_profesor', $runProfesor)
                  ->where('fecha_clase', $fechaClase);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }
}
