<?php

namespace App\Services;

use App\Http\Controllers\EspacioController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Unifica la regla: no tratar el espacio como libre para reserva espontánea
 * si la malla académica no deja módulos (p. ej. clase en el módulo actual/siguiente),
 * salvo que quien escanea sea el docente con clase en curso o en los próximos 15 min.
 */
class ReservaEspontaneaMallaService
{
    /**
     * @return array{permitida: bool, mensaje: ?string, clase: ?array}
     */
    public function evaluarAntesNuevaReserva(string $runUsuario, string $idEspacio): array
    {
        $runNormalizado = $this->normalizarRunSoloDigitos($runUsuario);
        $subRequest = Request::create('/api/espacio/modulos-disponibles', 'GET', [
            'hora_actual' => Carbon::now()->format('H:i:s'),
            'dia_actual' => ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'][(int) Carbon::now()->dayOfWeek],
        ]);

        $modulosResp = app(EspacioController::class)->modulosDisponibles($subRequest, $idEspacio);
        $modulosData = json_decode($modulosResp->getContent(), true);

        if (!($modulosData['success'] ?? false)) {
            return ['permitida' => true, 'mensaje' => null, 'clase' => null];
        }

        if (($modulosData['max_modulos'] ?? 0) > 0) {
            return ['permitida' => true, 'mensaje' => null, 'clase' => null];
        }

        if ($this->usuarioTieneProgramacionAnticipadaEnEspacio($runNormalizado, $idEspacio)) {
            return ['permitida' => true, 'mensaje' => null, 'clase' => null];
        }

        $claseInfo = $modulosData['proxima_clase'] ?? ($modulosData['clases_proximas'][0] ?? null);
        $detalleAsignatura = is_array($claseInfo) ? ($claseInfo['asignatura'] ?? null) : null;
        $detalleProfesor = is_array($claseInfo) ? ($claseInfo['profesor'] ?? null) : null;
        $mensaje = 'Este espacio tiene clase académica programada en este horario o en el módulo siguiente; no está disponible para reserva espontánea de módulos.';
        if ($detalleAsignatura) {
            $mensaje .= ' Asignatura: ' . $detalleAsignatura . '.';
        }
        if ($detalleProfesor) {
            $mensaje .= ' Docente: ' . $detalleProfesor . '.';
        }

        return [
            'permitida' => false,
            'mensaje' => $mensaje,
            'clase' => is_array($claseInfo) ? $claseInfo : null,
        ];
    }

    private function normalizarRunSoloDigitos(?string $run): string
    {
        if ($run === null || $run === '') {
            return '';
        }
        if (str_contains($run, 'run=')) {
            if (preg_match('/[?&]run=([^&]+)/i', $run, $m)) {
                $run = $m[1];
            }
        }
        if (str_contains($run, '-')) {
            $run = explode('-', $run, 2)[0];
        }

        return preg_replace('/[^0-9]/', '', $run) ?? '';
    }

    /**
     * True si el usuario tiene clase en este espacio ahora o en los próximos 15 minutos
     * (alineado con /api/verificar-programacion/{espacio}/{usuario}).
     */
    public function usuarioTieneProgramacionAnticipadaEnEspacio(string $runUsuario, string $idEspacio): bool
    {
        if ($runUsuario === '') {
            return false;
        }

        $horaActual = Carbon::now();
        $dias = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
        $diaActual = $dias[(int) $horaActual->dayOfWeek];
        $horaActualStr = $horaActual->format('H:i:s');
        $horaConAnticipacion = $horaActual->copy()->addMinutes(15)->format('H:i:s');
        $runLimpio = (int) $runUsuario;

        $programacion = DB::table('planificacion_asignaturas as pa')
            ->join('horarios as h', 'pa.id_horario', '=', 'h.id_horario')
            ->join('modulos as m', 'pa.id_modulo', '=', 'm.id_modulo')
            ->where('pa.id_espacio', $idEspacio)
            ->where(function ($query) use ($runUsuario, $runLimpio) {
                $query->where('h.run_profesor', $runUsuario)
                    ->orWhere('h.run_profesor', $runLimpio)
                    ->orWhere(DB::raw('CAST(h.run_profesor AS CHAR)'), $runUsuario);
            })
            ->where('m.dia', $diaActual)
            ->where(function ($query) use ($horaActualStr, $horaConAnticipacion) {
                $query->where(function ($q) use ($horaActualStr) {
                    $q->where('m.hora_inicio', '<=', $horaActualStr)
                        ->where('m.hora_termino', '>=', $horaActualStr);
                })->orWhere(function ($q) use ($horaActualStr, $horaConAnticipacion) {
                    $q->where('m.hora_inicio', '>', $horaActualStr)
                        ->where('m.hora_inicio', '<=', $horaConAnticipacion);
                });
            })
            ->select('pa.id_modulo')
            ->first();

        return $programacion !== null;
    }
}
