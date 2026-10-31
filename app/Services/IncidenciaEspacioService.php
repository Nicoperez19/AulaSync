<?php

namespace App\Services;

use App\Models\Espacio;
use App\Models\IncidenciaEspacio;
use App\Models\IncidenciaEspacioEvento;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Reporte de fallas e incidencias en espacios.
 *
 * Regla central: un espacio solo puede estar en "Mantenimiento" si existe al menos una
 * incidencia abierta con `bloquea_uso = true`. Así el estado siempre tiene un "por qué",
 * un "quién" y un "cuándo" (ver bitácora en incidencias_espacios_eventos).
 */
class IncidenciaEspacioService
{
    public const ESTADO_MANTENIMIENTO = 'Mantenimiento';
    public const ESTADO_LIBRE = 'Disponible';

    public function __construct(protected EspacioService $espacioService)
    {
    }

    /**
     * Reportar una nueva incidencia. Si `bloquea_uso` es true, el espacio pasa a Mantenimiento.
     *
     * @param  array{categoria:string,descripcion:string,severidad?:string,bloquea_uso?:bool}  $datos
     */
    public function reportar(string $idEspacio, array $datos, ?Authenticatable $usuario = null): array
    {
        $espacio = Espacio::where('id_espacio', $idEspacio)->first();

        if (! $espacio) {
            return $this->respuesta(false, 404, 'Espacio no encontrado');
        }

        $categoria = $datos['categoria'] ?? 'otro';
        $bloquea = array_key_exists('bloquea_uso', $datos)
            ? (bool) $datos['bloquea_uso']
            : (IncidenciaEspacio::CATEGORIAS[$categoria]['bloquea_por_defecto'] ?? false);

        try {
            $incidencia = DB::connection('tenant')->transaction(function () use ($espacio, $datos, $categoria, $bloquea, $usuario) {
                $actor = $this->actor($usuario);

                $incidencia = IncidenciaEspacio::create([
                    'id_espacio'            => $espacio->id_espacio,
                    'categoria'             => $categoria,
                    'severidad'             => $datos['severidad'] ?? 'media',
                    'descripcion'           => trim($datos['descripcion']),
                    'bloquea_uso'           => $bloquea,
                    'estado_espacio_previo' => $espacio->estado,
                    'estado'                => 'abierta',
                    'reportado_por_run'     => $actor['run'],
                    'reportado_por_nombre'  => $actor['nombre'],
                    'reportado_en'          => now(),
                ]);

                $this->registrarEvento($incidencia, 'creada', null, 'abierta', trim($datos['descripcion']), $actor);

                if ($bloquea && $espacio->estado !== self::ESTADO_MANTENIMIENTO) {
                    $resultado = $this->espacioService->cambiarEstado($espacio->id_espacio, self::ESTADO_MANTENIMIENTO);

                    if (! ($resultado['success'] ?? false)) {
                        // Revierte la transacción completa: no queda incidencia sin sala bloqueada.
                        throw new \RuntimeException($resultado['mensaje'] ?? 'No se pudo poner el espacio en mantenimiento');
                    }

                    $this->registrarEvento(
                        $incidencia,
                        'espacio_bloqueado',
                        null,
                        null,
                        "Espacio pasó de {$espacio->estado} a Mantenimiento",
                        $actor
                    );
                }

                return $incidencia;
            });
        } catch (\Throwable $e) {
            Log::error('Error al reportar incidencia de espacio: ' . $e->getMessage(), ['espacio' => $idEspacio]);

            return $this->respuesta(false, 500, 'No se pudo registrar la incidencia: ' . $e->getMessage());
        }

        $mensaje = $bloquea
            ? "Incidencia registrada. El espacio {$idEspacio} quedó en Mantenimiento."
            : "Incidencia registrada. El espacio {$idEspacio} sigue operativo.";

        return $this->respuesta(true, 201, $mensaje, ['incidencia' => $this->resumen($incidencia)]);
    }

    /**
     * Cambiar el estado de una incidencia (en proceso, resuelta, descartada, reabrir).
     * Al cerrar la última incidencia bloqueante, el espacio vuelve a Disponible automáticamente.
     */
    public function cambiarEstado(int $idIncidencia, string $nuevoEstado, ?string $nota = null, ?Authenticatable $usuario = null): array
    {
        $incidencia = IncidenciaEspacio::find($idIncidencia);

        if (! $incidencia) {
            return $this->respuesta(false, 404, 'Incidencia no encontrada');
        }

        if (! array_key_exists($nuevoEstado, IncidenciaEspacio::ESTADOS)) {
            return $this->respuesta(false, 422, 'Estado de incidencia no válido');
        }

        $anterior = $incidencia->estado;

        if ($anterior === $nuevoEstado) {
            return $this->respuesta(true, 200, 'La incidencia ya se encontraba en ese estado', ['incidencia' => $this->resumen($incidencia)]);
        }

        $cierra = in_array($nuevoEstado, ['resuelta', 'descartada'], true);
        $nota = $nota !== null ? trim($nota) : null;

        if ($cierra && mb_strlen((string) $nota) < 3) {
            return $this->respuesta(false, 422, 'Debes indicar cómo se resolvió o por qué se descarta la incidencia.');
        }

        try {
            $mensajeExtra = DB::connection('tenant')->transaction(function () use ($incidencia, $anterior, $nuevoEstado, $cierra, $nota, $usuario) {
                $actor = $this->actor($usuario);
                $extra = '';

                $cambios = ['estado' => $nuevoEstado];

                if ($cierra) {
                    $cambios += [
                        'resolucion'          => $nota,
                        'resuelto_por_run'    => $actor['run'],
                        'resuelto_por_nombre' => $actor['nombre'],
                        'resuelto_en'         => now(),
                    ];
                } elseif (in_array($anterior, ['resuelta', 'descartada'], true)) {
                    // Reapertura: se limpia el cierre anterior (queda registrado en la bitácora).
                    $cambios += [
                        'resolucion'          => null,
                        'resuelto_por_run'    => null,
                        'resuelto_por_nombre' => null,
                        'resuelto_en'         => null,
                    ];
                }

                if ($nuevoEstado === 'en_proceso') {
                    $cambios['asignado_a'] = $actor['nombre'] ?? $incidencia->asignado_a;
                }

                $incidencia->update($cambios);

                $this->registrarEvento($incidencia, 'estado_cambiado', $anterior, $nuevoEstado, $nota, $actor);

                $espacio = Espacio::where('id_espacio', $incidencia->id_espacio)->first();

                if ($espacio && $incidencia->bloquea_uso) {
                    if ($cierra) {
                        $quedanBloqueantes = IncidenciaEspacio::where('id_espacio', $incidencia->id_espacio)
                            ->abiertas()->bloqueantes()->exists();

                        if (! $quedanBloqueantes && $espacio->estado === self::ESTADO_MANTENIMIENTO) {
                            $res = $this->espacioService->cambiarEstado($espacio->id_espacio, self::ESTADO_LIBRE);
                            if (! ($res['success'] ?? false)) {
                                throw new \RuntimeException($res['mensaje'] ?? 'No se pudo liberar el espacio');
                            }
                            $this->registrarEvento($incidencia, 'espacio_liberado', null, null, 'Sin incidencias bloqueantes abiertas: espacio vuelve a Disponible', $actor);
                            $extra = " El espacio {$espacio->id_espacio} volvió a Disponible.";
                        } elseif ($quedanBloqueantes) {
                            $extra = " El espacio {$espacio->id_espacio} sigue en Mantenimiento por otras incidencias abiertas.";
                        }
                    } elseif (in_array($anterior, ['resuelta', 'descartada'], true) && $espacio->estado !== self::ESTADO_MANTENIMIENTO) {
                        $res = $this->espacioService->cambiarEstado($espacio->id_espacio, self::ESTADO_MANTENIMIENTO);
                        if (! ($res['success'] ?? false)) {
                            throw new \RuntimeException($res['mensaje'] ?? 'No se pudo volver a bloquear el espacio');
                        }
                        $this->registrarEvento($incidencia, 'espacio_bloqueado', null, null, 'Incidencia reabierta: espacio vuelve a Mantenimiento', $actor);
                        $extra = " El espacio {$espacio->id_espacio} volvió a Mantenimiento.";
                    }
                }

                return $extra;
            });
        } catch (\Throwable $e) {
            Log::error('Error al cambiar estado de incidencia: ' . $e->getMessage(), ['incidencia' => $idIncidencia]);

            return $this->respuesta(false, 500, 'No se pudo actualizar la incidencia: ' . $e->getMessage());
        }

        $incidencia->refresh();

        return $this->respuesta(true, 200, 'Incidencia actualizada.' . $mensajeExtra, ['incidencia' => $this->resumen($incidencia)]);
    }

    /**
     * Agregar un comentario a la bitácora de una incidencia.
     */
    public function comentar(int $idIncidencia, string $texto, ?Authenticatable $usuario = null): array
    {
        $incidencia = IncidenciaEspacio::find($idIncidencia);

        if (! $incidencia) {
            return $this->respuesta(false, 404, 'Incidencia no encontrada');
        }

        $this->registrarEvento($incidencia, 'comentario', null, null, trim($texto), $this->actor($usuario));

        return $this->respuesta(true, 201, 'Comentario agregado.');
    }

    /**
     * Llamado por EspacioService cuando un espacio sale de Mantenimiento por la vía manual
     * ("Liberar"). Cierra las incidencias bloqueantes abiertas dejando constancia en la bitácora.
     */
    public function cerrarBloqueantesAlLiberar(string $idEspacio, ?string $nota = null, ?Authenticatable $usuario = null): int
    {
        $actor = $this->actor($usuario);
        $nota = $nota !== null && trim($nota) !== '' ? trim($nota) : 'Espacio liberado manualmente (sin detalle de resolución).';

        $incidencias = IncidenciaEspacio::where('id_espacio', $idEspacio)->abiertas()->bloqueantes()->get();

        foreach ($incidencias as $incidencia) {
            $anterior = $incidencia->estado;

            $incidencia->update([
                'estado'              => 'resuelta',
                'resolucion'          => $nota,
                'resuelto_por_run'    => $actor['run'],
                'resuelto_por_nombre' => $actor['nombre'],
                'resuelto_en'         => now(),
            ]);

            $this->registrarEvento($incidencia, 'estado_cambiado', $anterior, 'resuelta', $nota, $actor);
            $this->registrarEvento($incidencia, 'espacio_liberado', null, null, 'Espacio liberado manualmente', $actor);
        }

        return $incidencias->count();
    }

    /**
     * ¿El espacio tiene al menos una incidencia bloqueante abierta?
     */
    public function tieneBloqueanteAbierta(string $idEspacio): bool
    {
        return IncidenciaEspacio::where('id_espacio', $idEspacio)->abiertas()->bloqueantes()->exists();
    }

    /**
     * Listado paginado con filtros.
     *
     * @param  array{estado?:string,categoria?:string,severidad?:string,espacio?:string,desde?:string,hasta?:string,buscar?:string}  $filtros
     */
    public function listar(array $filtros = [], int $porPagina = 15): LengthAwarePaginator
    {
        $query = IncidenciaEspacio::with('espacio:id_espacio,nombre_espacio,tipo_espacio,estado')
            ->orderByRaw("FIELD(estado, 'abierta', 'en_proceso', 'resuelta', 'descartada')")
            ->orderByRaw("FIELD(severidad, 'critica', 'alta', 'media', 'baja')")
            ->orderByDesc('reportado_en');

        if (! empty($filtros['estado'])) {
            $filtros['estado'] === 'pendientes'
                ? $query->abiertas()
                : $query->where('estado', $filtros['estado']);
        }
        if (! empty($filtros['categoria'])) {
            $query->where('categoria', $filtros['categoria']);
        }
        if (! empty($filtros['severidad'])) {
            $query->where('severidad', $filtros['severidad']);
        }
        if (! empty($filtros['espacio'])) {
            $query->where('id_espacio', $filtros['espacio']);
        }
        if (! empty($filtros['desde'])) {
            $query->whereDate('reportado_en', '>=', $filtros['desde']);
        }
        if (! empty($filtros['hasta'])) {
            $query->whereDate('reportado_en', '<=', $filtros['hasta']);
        }
        if (! empty($filtros['buscar'])) {
            $term = '%' . $filtros['buscar'] . '%';
            $query->where(function ($q) use ($term) {
                $q->where('descripcion', 'like', $term)
                    ->orWhere('id_espacio', 'like', $term)
                    ->orWhere('reportado_por_nombre', 'like', $term);
            });
        }

        return $query->paginate($porPagina)->withQueryString();
    }

    /**
     * Indicadores para el encabezado del tablero.
     */
    public function estadisticas(): array
    {
        $abiertas = IncidenciaEspacio::abiertas()->count();
        $criticas = IncidenciaEspacio::abiertas()->whereIn('severidad', ['alta', 'critica'])->count();
        $salasBloqueadas = IncidenciaEspacio::abiertas()->bloqueantes()->distinct('id_espacio')->count('id_espacio');

        $desde = now()->subDays(90);

        $porCategoria = IncidenciaEspacio::where('reportado_en', '>=', $desde)
            ->select('categoria', DB::raw('COUNT(*) as total'))
            ->groupBy('categoria')
            ->orderByDesc('total')
            ->limit(5)
            ->pluck('total', 'categoria')
            ->all();

        $espaciosReincidentes = IncidenciaEspacio::where('reportado_en', '>=', $desde)
            ->select('id_espacio', DB::raw('COUNT(*) as total'))
            ->groupBy('id_espacio')
            ->having('total', '>=', 2)
            ->orderByDesc('total')
            ->limit(5)
            ->pluck('total', 'id_espacio')
            ->all();

        $tiempoMedioHoras = IncidenciaEspacio::where('estado', 'resuelta')
            ->where('resuelto_en', '>=', $desde)
            ->whereNotNull('resuelto_en')
            ->select(DB::raw('AVG(TIMESTAMPDIFF(MINUTE, reportado_en, resuelto_en)) as minutos'))
            ->value('minutos');

        return [
            'abiertas'              => $abiertas,
            'graves_abiertas'       => $criticas,
            'salas_bloqueadas'      => $salasBloqueadas,
            'por_categoria_90d'     => $porCategoria,
            'reincidentes_90d'      => $espaciosReincidentes,
            'tiempo_medio_horas'    => $tiempoMedioHoras !== null ? round($tiempoMedioHoras / 60, 1) : null,
        ];
    }

    /**
     * Detalle + bitácora para el modal de la vista.
     */
    public function detalle(int $idIncidencia): ?array
    {
        $incidencia = IncidenciaEspacio::with(['eventos', 'espacio:id_espacio,nombre_espacio,estado'])->find($idIncidencia);

        if (! $incidencia) {
            return null;
        }

        return $this->resumen($incidencia) + [
            'eventos' => $incidencia->eventos->map(fn ($e) => [
                'tipo'            => $e->tipo,
                'estado_anterior' => $e->estado_anterior,
                'estado_nuevo'    => $e->estado_nuevo,
                'detalle'         => $e->detalle,
                'usuario'         => $e->usuario_nombre ?? 'Sistema',
                'fecha'           => $e->created_at?->format('d-m-Y H:i'),
            ])->values()->all(),
        ];
    }

    // ---------------------------------------------------------------------------------------

    protected function resumen(IncidenciaEspacio $i): array
    {
        return [
            'id'              => $i->id,
            'id_espacio'      => $i->id_espacio,
            'espacio'         => $i->espacio?->nombre_espacio,
            'estado_espacio'  => $i->espacio?->estado,
            'categoria'       => $i->categoria,
            'categoria_label' => $i->categoria_label,
            'severidad'       => $i->severidad,
            'severidad_label' => $i->severidad_label,
            'descripcion'     => $i->descripcion,
            'bloquea_uso'     => $i->bloquea_uso,
            'estado'          => $i->estado,
            'estado_label'    => $i->estado_label,
            'reportado_por'   => $i->reportado_por_nombre,
            'reportado_en'    => $i->reportado_en?->format('d-m-Y H:i'),
            'asignado_a'      => $i->asignado_a,
            'resolucion'      => $i->resolucion,
            'resuelto_por'    => $i->resuelto_por_nombre,
            'resuelto_en'     => $i->resuelto_en?->format('d-m-Y H:i'),
            'horas_abierta'   => $i->horas_abierta,
        ];
    }

    protected function registrarEvento(IncidenciaEspacio $incidencia, string $tipo, ?string $anterior, ?string $nuevo, ?string $detalle, array $actor): void
    {
        IncidenciaEspacioEvento::create([
            'incidencia_id'   => $incidencia->id,
            'tipo'            => $tipo,
            'estado_anterior' => $anterior,
            'estado_nuevo'    => $nuevo,
            'detalle'         => $detalle,
            'usuario_run'     => $actor['run'],
            'usuario_nombre'  => $actor['nombre'],
        ]);
    }

    /** @return array{run:?string,nombre:?string} */
    protected function actor(?Authenticatable $usuario = null): array
    {
        $usuario = $usuario ?? Auth::user();

        return [
            'run'    => $usuario?->run !== null ? (string) $usuario->run : null,
            'nombre' => $usuario?->name,
        ];
    }

    protected function respuesta(bool $ok, int $status, string $mensaje, array $extra = []): array
    {
        return ['success' => $ok, 'status' => $status, 'mensaje' => $mensaje] + $extra;
    }
}
