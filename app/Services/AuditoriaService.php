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
     * Obtiene el contexto compartido (usuario, rol, tenant, ip, user-agent) una sola vez.
     */
    protected function obtenerContextoAuditoria(): array
    {
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

        return [
            'tenant_id' => $tenantId,
            'user_id' => $user?->id,
            'user_nombre' => $userNombre,
            'user_rol' => $userRol,
            'ip' => $ip,
            'user_agent' => $userAgent,
        ];
    }

    /**
     * Normaliza una fecha para almacenamiento en auditoría.
     */
    protected function normalizarFechaAuditoria($fechaClase): ?string
    {
        if ($fechaClase instanceof \DateTimeInterface) {
            return $fechaClase->format('Y-m-d');
        }
        if (is_string($fechaClase) && !empty($fechaClase)) {
            try {
                return \Carbon\Carbon::parse($fechaClase)->format('Y-m-d');
            } catch (\Throwable $e) {
                return $fechaClase;
            }
        }
        return null;
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
        $ctx = $this->obtenerContextoAuditoria();

        // Si no se pasaron explícitamente los estados, intentar inferirlos de los valores
        if ($estadoAnterior === null && isset($valoresAnteriores['estado'])) {
            $estadoAnterior = $valoresAnteriores['estado'];
        }
        if ($estadoNuevo === null && isset($valoresNuevos['estado'])) {
            $estadoNuevo = $valoresNuevos['estado'];
        }

        $fechaClase = $this->normalizarFechaAuditoria(
            $entidad->fecha_clase ?? ($valoresNuevos['fecha_clase'] ?? ($valoresAnteriores['fecha_clase'] ?? null))
        );

        return AuditoriaClase::create([
            'tenant_id' => $ctx['tenant_id'],
            'user_id' => $ctx['user_id'],
            'user_nombre' => $ctx['user_nombre'],
            'user_rol' => $ctx['user_rol'],
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
            'ip' => $ctx['ip'],
            'user_agent' => $ctx['user_agent'],
            'created_at' => now(),
        ]);
    }

    /**
     * Inserta en bloque múltiples eventos de auditoría (operaciones masivas).
     *
     * @param array<int, array<string, mixed>> $items
     */
    public function registrarLote(array $items): void
    {
        if (empty($items)) {
            return;
        }

        $ctx = $this->obtenerContextoAuditoria();
        $nowStr = now()->format('Y-m-d H:i:s');
        $loteUuid = static::$loteActivo ?? (string) Str::uuid();
        $rows = [];

        foreach ($items as $item) {
            /** @var Model $entidad */
            $entidad = $item['entidad'];
            $valoresAnteriores = $item['valores_anteriores'] ?? null;
            $valoresNuevos = $item['valores_nuevos'] ?? null;

            $fechaClase = $this->normalizarFechaAuditoria(
                $entidad->fecha_clase ?? ($valoresNuevos['fecha_clase'] ?? ($valoresAnteriores['fecha_clase'] ?? null))
            );

            $rows[] = [
                'tenant_id' => $ctx['tenant_id'],
                'user_id' => $ctx['user_id'],
                'user_nombre' => $ctx['user_nombre'],
                'user_rol' => $ctx['user_rol'],
                'accion' => $item['accion'],
                'entidad_tipo' => get_class($entidad),
                'entidad_id' => $entidad->getKey(),
                'run_profesor' => $entidad->run_profesor ?? ($valoresNuevos['run_profesor'] ?? ($valoresAnteriores['run_profesor'] ?? null)),
                'id_asignatura' => $entidad->id_asignatura ?? ($valoresNuevos['id_asignatura'] ?? ($valoresAnteriores['id_asignatura'] ?? null)),
                'id_espacio' => $entidad->id_espacio ?? ($valoresNuevos['id_espacio'] ?? ($valoresAnteriores['id_espacio'] ?? null)),
                'id_modulo' => $entidad->id_modulo ?? ($valoresNuevos['id_modulo'] ?? ($valoresAnteriores['id_modulo'] ?? null)),
                'fecha_clase' => $fechaClase,
                'estado_anterior' => $item['estado_anterior'] ?? ($valoresAnteriores['estado'] ?? null),
                'estado_nuevo' => $item['estado_nuevo'] ?? ($valoresNuevos['estado'] ?? null),
                'valores_anteriores' => $valoresAnteriores !== null ? json_encode($valoresAnteriores) : null,
                'valores_nuevos' => $valoresNuevos !== null ? json_encode($valoresNuevos) : null,
                'motivo' => $item['motivo'] ?? null,
                'observaciones' => $item['observaciones'] ?? null,
                'lote_uuid' => $loteUuid,
                'origen' => $item['origen'] ?? 'web',
                'ip' => $ctx['ip'],
                'user_agent' => $ctx['user_agent'],
                'created_at' => $nowStr,
            ];
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            AuditoriaClase::insert($chunk);
        }
    }

    /**
     * Obtiene el historial cronológico de auditoría para una clase
     */
    public function obtenerHistorialClase($entidadId, $runProfesor = null, $fechaClase = null)
    {
        if (!$entidadId && (!$runProfesor || !$fechaClase)) {
            return collect();
        }

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
