<?php

namespace App\Services;

use App\Models\Espacio;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class EspacioService
{
    /**
     * Obtener listado de espacios filtrados y estructurados.
     */
    public function getEspacios(array $filtros = []): array
    {
        $query = Espacio::with('piso')
            ->select('espacios.id_espacio', 'espacios.nombre_espacio', 'espacios.tipo_espacio', 'espacios.piso_id', 'espacios.puestos_disponibles', 'espacios.capacidad_maxima', 'espacios.estado')
            ->leftJoin('pisos', 'espacios.piso_id', '=', 'pisos.id')
            ->orderBy('espacios.estado', 'asc')
            ->orderBy('pisos.numero_piso', 'asc')
            ->orderBy('espacios.id_espacio', 'asc');

        if (!empty($filtros['estado'])) {
            $query->where('espacios.estado', $filtros['estado']);
        }

        if (!empty($filtros['piso'])) {
            $query->where('espacios.piso_id', $filtros['piso']);
        }

        $espaciosRaw = $query->get();

        return $espaciosRaw->map(function ($espacio) {
            $capacidadReal = ($espacio->capacidad_maxima && $espacio->capacidad_maxima > 0)
                ? $espacio->capacidad_maxima
                : ($espacio->puestos_disponibles ?? 0);

            return [
                'codigo' => $espacio->id_espacio,
                'nombre' => $espacio->nombre_espacio,
                'tipo' => $espacio->tipo_espacio,
                'piso' => $espacio->piso ? $espacio->piso->numero_piso : $espacio->piso_id,
                'capacidad' => $capacidadReal,
                'capacidad_maxima' => $capacidadReal,
                'estado' => $espacio->estado,
                'id_espacio' => $espacio->id_espacio,
                'nombre_espacio' => $espacio->nombre_espacio,
                'piso_id' => $espacio->piso_id,
            ];
        })->toArray();
    }

    /**
     * Cambiar el estado operativo de un espacio.
     */
    public function cambiarEstado(string $codigo, string $nuevoEstado): array
    {
        $espacio = Espacio::where('id_espacio', $codigo)->first();

        if (!$espacio) {
            return [
                'success' => false,
                'status' => 404,
                'mensaje' => 'Espacio no encontrado',
            ];
        }

        $estadoAnterior = $espacio->estado;

        if ($estadoAnterior === $nuevoEstado) {
            return [
                'success' => true,
                'status' => 200,
                'mensaje' => "Espacio ya se encontraba en estado {$nuevoEstado}",
                'espacio' => [
                    'codigo' => $espacio->id_espacio,
                    'nombre' => $espacio->nombre_espacio,
                    'estado_anterior' => $estadoAnterior,
                    'estado_nuevo' => $nuevoEstado,
                    'reservas_finalizadas' => [],
                ],
            ];
        }

        if (Schema::connection('tenant')->hasColumn('espacios', 'estado_espacio')) {
            $espacio->estado_espacio = $nuevoEstado;
        }

        $espacio->estado = $nuevoEstado;

        if (!$espacio->save()) {
            return [
                'success' => false,
                'status' => 500,
                'mensaje' => 'Error al guardar los cambios del espacio',
            ];
        }

        $this->limpiarCacheEstados();

        $reservasFinalizadas = [];
        if ($nuevoEstado === 'Disponible' && in_array($estadoAnterior, ['Ocupado', 'Reservado'])) {
            $reservasFinalizadas = $this->finalizarReservasActivasActuales($codigo);
        }

        $mensaje = "Estado del espacio {$codigo} cambiado de {$estadoAnterior} a {$nuevoEstado}";
        if (!empty($reservasFinalizadas)) {
            $cantidadReservas = count($reservasFinalizadas);
            $mensaje .= ". Se finalizaron automáticamente {$cantidadReservas} reserva(s) activa(s): " . implode(', ', $reservasFinalizadas);
        }

        return [
            'success' => true,
            'status' => 200,
            'mensaje' => $mensaje,
            'espacio' => [
                'codigo' => $espacio->id_espacio,
                'nombre' => $espacio->nombre_espacio,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => $nuevoEstado,
                'reservas_finalizadas' => $reservasFinalizadas,
            ],
        ];
    }

    /**
     * Liberar todos los espacios que no estén en mantención.
     */
    public function liberacionMasiva(): array
    {
        $espacios = Espacio::whereNotIn('estado', ['Mantención', 'Mantenimiento'])->get();
        $conteo = 0;

        foreach ($espacios as $espacio) {
            $estadoAnterior = $espacio->estado;

            $espacio->estado = 'Disponible';
            if (Schema::connection('tenant')->hasColumn('espacios', 'estado_espacio')) {
                $espacio->estado_espacio = 'Disponible';
            }
            $espacio->save();

            if ($estadoAnterior === 'Ocupado') {
                $this->finalizarReservasActivasActuales($espacio->id_espacio);
            }
            $conteo++;
        }

        $this->limpiarCacheEstados();

        return [
            'success' => true,
            'procesados' => $conteo,
            'mensaje' => "Se han procesado {$conteo} espacios. Todos han sido marcados como Disponibles.",
        ];
    }

    /**
     * Finalizar reservas activas al liberar un espacio.
     */
    public function finalizarReservasActivasActuales(string $codigoEspacio): array
    {
        try {
            $fechaActual = now()->format('Y-m-d');
            $horaActual = now()->format('H:i:s');

            $reservasActivas = Reserva::where('id_espacio', $codigoEspacio)
                ->where('estado', 'activa')
                ->orderBy('fecha_reserva')
                ->orderBy('hora')
                ->get();

            if ($reservasActivas->isEmpty()) {
                return [];
            }

            $reservasFinalizadas = [];

            foreach ($reservasActivas as $reserva) {
                try {
                    $fechaReserva = $reserva->fecha_reserva instanceof Carbon
                        ? $reserva->fecha_reserva->format('Y-m-d')
                        : Carbon::parse($reserva->fecha_reserva)->format('Y-m-d');

                    if ($fechaReserva === $fechaActual) {
                        $horaInicioReserva = $this->convertirHoraAMinutos($reserva->hora);
                        $horaActualEnMinutos = $this->convertirHoraAMinutos($horaActual);

                        if ($horaActualEnMinutos >= $horaInicioReserva) {
                            $motivo = 'FINALIZADA: El espacio fue liberado manualmente durante la clase';
                        } else {
                            $motivo = 'FINALIZADA: El espacio fue liberado manualmente (reserva futura no ejecutada)';
                        }
                    } else {
                        $motivo = 'FINALIZADA: El espacio fue liberado manualmente (reserva futura cancelada)';
                    }

                    $reserva->estado = 'finalizada';
                    $reserva->hora_salida = $horaActual;
                    $reserva->observaciones = ($reserva->observaciones ?? '') . " | {$motivo} el " . now()->format('d/m/Y H:i:s');
                    $reserva->save();

                    $reservasFinalizadas[] = $reserva->id_reserva;
                } catch (\Exception $e) {
                    Log::error("❌ Error al finalizar reserva {$reserva->id_reserva}: " . $e->getMessage());
                }
            }

            return $reservasFinalizadas;
        } catch (\Exception $e) {
            Log::error('❌ Error al finalizar reservas activas: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Liberar espacio si la reserva que termina corresponde a la actual.
     */
    public function liberarEspacioSiEsReservaActual($reserva): bool
    {
        try {
            $fechaActual = now()->format('Y-m-d');
            $horaActual = now()->format('H:i:s');
            $horaActualEnMinutos = $this->convertirHoraAMinutos($horaActual);

            $otrasReservasActivas = Reserva::where('id_espacio', $reserva->id_espacio)
                ->where('estado', 'activa')
                ->where('id_reserva', '!=', $reserva->id_reserva)
                ->where('fecha_reserva', $fechaActual)
                ->get();

            $hayReservaEnCurso = false;
            foreach ($otrasReservasActivas as $otraReserva) {
                $horaInicioOtra = $this->convertirHoraAMinutos($otraReserva->hora);
                $duracionEstimada = 60;
                if ($otraReserva->observaciones && preg_match('/Módulos: (\d+)-(\d+)/', $otraReserva->observaciones, $matches)) {
                    $modulosCount = (int) $matches[2] - (int) $matches[1] + 1;
                    $duracionEstimada = $modulosCount * 50;
                } elseif (is_numeric($otraReserva->modulos)) {
                    $duracionEstimada = (int) $otraReserva->modulos * 50;
                }

                $horaFinEstimada = $horaInicioOtra + $duracionEstimada;

                if ($horaActualEnMinutos >= $horaInicioOtra && $horaActualEnMinutos <= $horaFinEstimada) {
                    $hayReservaEnCurso = true;
                    break;
                }
            }

            $fechaReserva = $reserva->fecha_reserva instanceof Carbon
                ? $reserva->fecha_reserva->format('Y-m-d')
                : substr($reserva->fecha_reserva, 0, 10);

            if ($fechaReserva === $fechaActual && !$hayReservaEnCurso) {
                $espacio = Espacio::where('id_espacio', $reserva->id_espacio)->first();
                if ($espacio) {
                    $estadoActual = Schema::hasColumn('espacios', 'estado_espacio') ? $espacio->estado_espacio : $espacio->estado;

                    if ($estadoActual === 'Ocupado') {
                        if (Schema::hasColumn('espacios', 'estado_espacio')) {
                            $espacio->estado_espacio = 'Disponible';
                        } else {
                            $espacio->estado = 'Disponible';
                        }
                        $espacio->save();
                        return true;
                    }
                }
            }

            return false;
        } catch (\Exception $e) {
            Log::error('❌ Error al verificar liberación de espacio: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Ocupar espacio automáticamente si la reserva creada es del módulo actual.
     */
    public function ocuparEspacioSiEsReservaActual($reserva): bool
    {
        try {
            $fechaActual = now()->format('Y-m-d');
            $horaActual = now()->format('H:i:s');

            $fechaReserva = $reserva->fecha_reserva instanceof Carbon
                ? $reserva->fecha_reserva->format('Y-m-d')
                : $reserva->fecha_reserva;

            if ($fechaReserva !== $fechaActual) {
                return false;
            }

            $diaNormalizado = \App\Helpers\ModulosHelper::normalizarDia(\Carbon\Carbon::parse($fechaReserva)->locale('es')->isoFormat('dddd'));
            $horariosModulos = \App\Helpers\ModulosHelper::getHorariosModulos()[$diaNormalizado] ?? [];

            $moduloActual = null;
            foreach ($horariosModulos as $modulo => $horario) {
                if ($horaActual >= $horario['inicio'] && $horaActual <= $horario['fin']) {
                    $moduloActual = $modulo;
                    break;
                }
            }

            if (!$moduloActual) {
                return false;
            }

            $modulosReserva = $reserva->modulos;
            $moduloInicio = null;
            $moduloFin = null;

            if ($reserva->observaciones && preg_match('/Módulos: (\d+)-(\d+)/', $reserva->observaciones, $matches)) {
                $moduloInicio = (int) $matches[1];
                $moduloFin = (int) $matches[2];
            } elseif ($modulosReserva && preg_match('/(\d+)\s*-\s*(\d+)/', $modulosReserva, $matches)) {
                $moduloInicio = (int) $matches[1];
                $moduloFin = (int) $matches[2];
            } elseif (is_numeric($modulosReserva)) {
                $horaReserva = $reserva->hora;
                foreach ($horariosModulos as $modulo => $horario) {
                    if ($horaReserva >= $horario['inicio'] && $horaReserva <= $horario['fin']) {
                        $moduloInicio = $modulo;
                        $moduloFin = $modulo + (int) $modulosReserva - 1;
                        break;
                    }
                }
            }

            if (!$moduloInicio || !$moduloFin) {
                $horaReserva = $reserva->hora;
                foreach ($horariosModulos as $modulo => $horario) {
                    if ($horaReserva >= $horario['inicio'] && $horaReserva <= $horario['fin']) {
                        $moduloInicio = $modulo;
                        $moduloFin = is_numeric($modulosReserva) ? $modulo + (int) $modulosReserva - 1 : $modulo;
                        break;
                    }
                }
            }

            if ($moduloInicio && $moduloFin && $moduloActual >= $moduloInicio && $moduloActual <= $moduloFin) {
                $horaReserva = $reserva->hora;
                if ($horaActual >= $horaReserva) {
                    $espacio = Espacio::where('id_espacio', $reserva->id_espacio)->first();
                    if ($espacio) {
                        $estadoActual = Schema::hasColumn('espacios', 'estado_espacio') ? $espacio->estado_espacio : $espacio->estado;
                        if ($estadoActual === 'Disponible') {
                            if (Schema::hasColumn('espacios', 'estado_espacio')) {
                                $espacio->estado_espacio = 'Ocupado';
                            } else {
                                $espacio->estado = 'Ocupado';
                            }
                            $espacio->save();
                            return true;
                        }
                    }
                }
            }

            return false;
        } catch (\Exception $e) {
            Log::error('❌ Error al verificar ocupación de espacio: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Limpiar caché de estados de espacios.
     */
    public function limpiarCacheEstados(): void
    {
        try {
            Cache::forget('estados_espacios');
            Cache::forget('espacios_disponibles');
            Cache::forget('dashboard_espacios');
        } catch (\Exception $e) {
            Log::warning('⚠️ No se pudo limpiar el caché de estados: ' . $e->getMessage());
        }
    }

    /**
     * Convertir hora en formato H:i:s a minutos desde medianoche.
     */
    public function convertirHoraAMinutos(string $hora): int
    {
        $partes = explode(':', $hora);
        $horas = (int) ($partes[0] ?? 0);
        $minutos = (int) ($partes[1] ?? 0);

        return ($horas * 60) + $minutos;
    }
}
