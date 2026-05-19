<?php

namespace App\Traits;

use App\Models\Modulo;
use App\Models\Planificacion_Asignatura;
use Illuminate\Http\Request;
use App\Models\Espacio;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

trait QuickActionsHelperTrait
{
    private function liberarEspacioSiEsReservaActual($reserva)
    {
        try {
            // Obtener fecha y hora actual
            $fechaActual = now()->format('Y-m-d');
            $horaActual = now()->format('H:i:s');
            $horaActualEnMinutos = $this->convertirHoraAMinutos($horaActual);

            // Verificar si hay otras reservas activas en el mismo espacio que deban seguir ocupándolo
            $otrasReservasActivas = Reserva::where('id_espacio', $reserva->id_espacio)
                ->where('estado', 'activa')
                ->where('id_reserva', '!=', $reserva->id_reserva)  // Excluir la reserva que se está finalizando
                ->where('fecha_reserva', $fechaActual)
                ->get();

            // Verificar si alguna de estas reservas está actualmente en curso
            $hayReservaEnCurso = false;
            foreach ($otrasReservasActivas as $otraReserva) {
                $horaInicioOtra = $this->convertirHoraAMinutos($otraReserva->hora);

                // Estimar duración basada en módulos o asumir 1 hora
                $duracionEstimada = 60;  // minutos por defecto
                if ($otraReserva->observaciones && preg_match('/Módulos: (\d+)-(\d+)/', $otraReserva->observaciones, $matches)) {
                    $modulosCount = (int) $matches[2] - (int) $matches[1] + 1;
                    $duracionEstimada = $modulosCount * 50;  // 50 minutos por módulo
                } elseif (is_numeric($otraReserva->modulos)) {
                    $duracionEstimada = (int) $otraReserva->modulos * 50;
                }

                $horaFinEstimada = $horaInicioOtra + $duracionEstimada;

                // Si la hora actual está dentro del rango de esta reserva
                if ($horaActualEnMinutos >= $horaInicioOtra && $horaActualEnMinutos <= $horaFinEstimada) {
                    $hayReservaEnCurso = true;

                    break;
                }
            }

            // Solo liberar el espacio si:
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
            } else {
                $motivo = $fechaReserva !== $fechaActual ? 'no es del día actual' : 'hay otras reservas activas en curso';
            }

            return false;
        } catch (\Exception $e) {
            Log::error('❌ Error al verificar liberación de espacio: ' . $e->getMessage());
            return false;
        }
    }

    private function ocuparEspacioSiEsReservaActual($reserva)
    {
        try {
            // Obtener fecha y hora actual
            $fechaActual = now()->format('Y-m-d');
            $horaActual = now()->format('H:i:s');

            // Verificar si la reserva es del día actual
            // NOTA: fecha_reserva es un objeto Carbon (por el cast), por lo que necesitamos formatearlo
            $fechaReserva = $reserva->fecha_reserva instanceof Carbon
                ? $reserva->fecha_reserva->format('Y-m-d')
                : $reserva->fecha_reserva;

            if ($fechaReserva !== $fechaActual) {
                return false;
            }

            // Mapeo de módulos a horarios (mismo que el método de liberación)
            $horariosModulos = [
                1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'],
                2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'],
                3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'],
                4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'],
                5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'],
                6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'],
                7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'],
                8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'],
                9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'],
                10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'],
                11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'],
                12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'],
                13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'],
                14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'],
                15 => ['inicio' => '22:10:00', 'fin' => '23:00:00'],
            ];

            // Determinar módulo actual basado en la hora
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

            // Verificar si la reserva incluye el módulo actual
            // Para reservas recién creadas, usar la información de módulos
            $modulosReserva = $reserva->modulos;
            $moduloInicio = null;
            $moduloFin = null;

            // Primero intentar extraer de las observaciones que contienen "Módulos: X-Y"
            if ($reserva->observaciones && preg_match('/Módulos: (\d+)-(\d+)/', $reserva->observaciones, $matches)) {
                $moduloInicio = (int) $matches[1];
                $moduloFin = (int) $matches[2];
            } elseif ($modulosReserva && preg_match('/(\d+)\s*-\s*(\d+)/', $modulosReserva, $matches)) {
                // Si modulos tiene formato "X - Y"
                $moduloInicio = (int) $matches[1];
                $moduloFin = (int) $matches[2];
            } elseif (is_numeric($modulosReserva)) {
                // Si modulos es la duración, usar la hora de inicio para determinar módulos
                $horaReserva = $reserva->hora;
                foreach ($horariosModulos as $modulo => $horario) {
                    if ($horaReserva >= $horario['inicio'] && $horaReserva <= $horario['fin']) {
                        $moduloInicio = $modulo;
                        $moduloFin = $modulo + (int) $modulosReserva - 1;
                        break;
                    }
                }
            }

            // Si aún no se determinaron, usar la hora de la reserva
            if (!$moduloInicio || !$moduloFin) {
                $horaReserva = $reserva->hora;
                foreach ($horariosModulos as $modulo => $horario) {
                    if ($horaReserva >= $horario['inicio'] && $horaReserva <= $horario['fin']) {
                        $moduloInicio = $modulo;
                        // Asumir duración de 1 módulo si no se puede determinar
                        $moduloFin = is_numeric($modulosReserva) ? $modulo + (int) $modulosReserva - 1 : $modulo;
                        break;
                    }
                }
            }

            // Verificar si el módulo actual está dentro del rango de la reserva
            // IMPORTANTE: También verificar que la hora de inicio ya haya pasado
            if ($moduloInicio && $moduloFin && $moduloActual >= $moduloInicio && $moduloActual <= $moduloFin) {
                // Verificar que la hora de inicio de la reserva ya haya llegado o pasado
                $horaReserva = $reserva->hora;
                if ($horaActual >= $horaReserva) {
                    // Es una reserva actual - ocupar el espacio
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
                    } elseif ($espacio) {
                    }
                } else {
                }
            } else {
            }

            return false;
        } catch (\Exception $e) {
            Log::error('❌ Error al verificar ocupación de espacio: ' . $e->getMessage());
            return false;
        }
    }

    private function finalizarReservasActivasActuales($codigoEspacio)
    {
        try {
            // Obtener fecha y hora actual
            $fechaActual = now()->format('Y-m-d');
            $horaActual = now()->format('H:i:s');

            // 🔧 FIX: Buscar TODAS las reservas activas para este espacio (no solo de hoy)
            // Cuando se libera un espacio manualmente, se deben finalizar todas las reservas activas
            $reservasActivas = Reserva::where('id_espacio', $codigoEspacio)
                ->where('estado', 'activa')
                ->orderBy('fecha_reserva')
                ->orderBy('hora')
                ->get();

            if ($reservasActivas->isEmpty()) {
                return [];
            }

            $reservasFinalizadas = [];

            // 🔧 FIX: Finalizar TODAS las reservas activas del espacio
            // Ya que el espacio se está liberando manualmente, todas las reservas activas deben finalizarse
            foreach ($reservasActivas as $reserva) {
                try {
                    $fechaReserva = $reserva->fecha_reserva instanceof Carbon
                        ? $reserva->fecha_reserva->format('Y-m-d')
                        : Carbon::parse($reserva->fecha_reserva)->format('Y-m-d');

                    // Diferenciar el motivo según si la reserva es de hoy o futura
                    if ($fechaReserva === $fechaActual) {
                        // Reserva de hoy
                        $horaInicioReserva = $this->convertirHoraAMinutos($reserva->hora);
                        $horaActualEnMinutos = $this->convertirHoraAMinutos($horaActual);

                        if ($horaActualEnMinutos >= $horaInicioReserva) {
                            $motivo = 'FINALIZADA: El espacio fue liberado manualmente durante la clase';
                        } else {
                            $motivo = 'FINALIZADA: El espacio fue liberado manualmente (reserva futura no ejecutada)';
                        }
                    } else {
                        // Reserva futura
                        $motivo = 'FINALIZADA: El espacio fue liberado manualmente (reserva futura cancelada)';
                    }

                    $reserva->estado = 'finalizada';
                    $reserva->hora_salida = $horaActual;
                    $reserva->observaciones = ($reserva->observaciones ?? '') . " | {$motivo} el " . now()->format('d/m/Y H:i:s');
                    $reserva->save();

                    $reservasFinalizadas[] = $reserva->id_reserva;

                    Log::info("✅ Reserva {$reserva->id_reserva} finalizada al liberar espacio {$codigoEspacio}");
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

    private function procesarModulosYHorarios($reserva)
    {
        // Mapeo de módulos a horarios
        $horariosModulos = [
            1 => ['inicio' => '08:10', 'fin' => '09:00'],
            2 => ['inicio' => '09:10', 'fin' => '10:00'],
            3 => ['inicio' => '10:10', 'fin' => '11:00'],
            4 => ['inicio' => '11:10', 'fin' => '12:00'],
            5 => ['inicio' => '12:10', 'fin' => '13:00'],
            6 => ['inicio' => '13:10', 'fin' => '14:00'],
            7 => ['inicio' => '14:10', 'fin' => '15:00'],
            8 => ['inicio' => '15:10', 'fin' => '16:00'],
            9 => ['inicio' => '16:10', 'fin' => '17:00'],
            10 => ['inicio' => '17:10', 'fin' => '18:00'],
            11 => ['inicio' => '18:10', 'fin' => '19:00'],
            12 => ['inicio' => '19:10', 'fin' => '20:00'],
            13 => ['inicio' => '20:10', 'fin' => '21:00'],
            14 => ['inicio' => '21:10', 'fin' => '22:00'],
            15 => ['inicio' => '22:10', 'fin' => '23:00'],
        ];

        $moduloInicio = null;
        $moduloFin = null;
        $cantidadModulos = 1;

        // Intentar extraer de observaciones primero
        if ($reserva->observaciones && preg_match('/Módulos: (\d+)-(\d+)/', $reserva->observaciones, $matches)) {
            $moduloInicio = (int) $matches[1];
            $moduloFin = (int) $matches[2];
            $cantidadModulos = $moduloFin - $moduloInicio + 1;
        } elseif ($reserva->modulos && preg_match('/(\d+)\s*-\s*(\d+)/', $reserva->modulos, $matches)) {
            // Si modulos tiene formato "X - Y"
            $moduloInicio = (int) $matches[1];
            $moduloFin = (int) $matches[2];
            $cantidadModulos = $moduloFin - $moduloInicio + 1;
        } else {
            // Determinar por hora de inicio y duración en módulos
            $horaReserva = substr($reserva->hora, 0, 5);  // HH:MM
            foreach ($horariosModulos as $modulo => $horario) {
                if ($horaReserva >= $horario['inicio'] && $horaReserva <= $horario['fin']) {
                    $moduloInicio = $modulo;
                    $cantidadModulos = is_numeric($reserva->modulos) ? (int) $reserva->modulos : 1;
                    $moduloFin = $moduloInicio + $cantidadModulos - 1;
                    break;
                }
            }
        }

        // Construir información completa
        if ($moduloInicio && $moduloFin && isset($horariosModulos[$moduloInicio]) && isset($horariosModulos[$moduloFin])) {
            $horaInicio = $horariosModulos[$moduloInicio]['inicio'];
            $horaFin = $horariosModulos[$moduloFin]['fin'];

            return [
                'modulo_inicial' => $moduloInicio,
                'modulo_final' => $moduloFin,
                'cantidad_modulos' => $cantidadModulos,
                'hora_inicio' => $horaInicio,
                'hora_fin' => $horaFin,
                'rango_horario' => "{$horaInicio} - {$horaFin}",
                'texto_completo' => "Módulos {$moduloInicio}-{$moduloFin} ({$horaInicio} - {$horaFin}) • {$cantidadModulos} módulo" . ($cantidadModulos > 1 ? 's' : '')
            ];
        }

        // Fallback si no se puede determinar
        return [
            'modulo_inicial' => null,
            'modulo_final' => null,
            'cantidad_modulos' => $cantidadModulos,
            'hora_inicio' => substr($reserva->hora, 0, 5),
            'hora_fin' => 'Desconocido',
            'rango_horario' => substr($reserva->hora, 0, 5),
            'texto_completo' => 'Hora: ' . substr($reserva->hora, 0, 5) . " • Duración: {$cantidadModulos} módulo" . ($cantidadModulos > 1 ? 's' : '')
        ];
    }

    private function convertirHoraAMinutos($hora)
    {
        $partes = explode(':', $hora);
        $horas = (int) $partes[0];
        $minutos = (int) $partes[1];
        return ($horas * 60) + $minutos;
    }
}
