<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Localiza planificación docente en un espacio para el instante dado:
 * - Clases regulares (planificacion_asignaturas + horarios), por prefijo id_modulo (LU, MI, …).
 * - Clases de profesor colaborador (planificaciones_profesores_colaboradores), mismo criterio temporal.
 * Evita depender solo de m.dia (tilde / sedes).
 */
class ProgramacionActualEnEspacioService
{
    public function buscarProgramacionProfesor(string $idEspacio, string $runEscaneado, ?Carbon $momento = null): ?object
    {
        $momento = $momento ?? Carbon::now();
        $horaActual = $momento->format('H:i:s');
        $horaConAnticipacion = $momento->copy()->addMinutes(15)->format('H:i:s');

        $diaAscii = strtolower(Str::ascii($momento->locale('es')->isoFormat('dddd')));
        $codigoDia = $this->diaAsciiAPrefijoModulo($diaAscii);
        if ($codigoDia === null) {
            return null;
        }

        $runNumerico = (int) preg_replace('/\D/', '', $runEscaneado);
        if ($runNumerico <= 0) {
            return null;
        }

        $regular = $this->buscarPlanificacionRegular($idEspacio, $runNumerico, $codigoDia, $horaActual, $horaConAnticipacion);
        if ($regular !== null) {
            return $regular;
        }

        return $this->buscarPlanificacionColaborador($idEspacio, $runNumerico, $codigoDia, $horaActual, $horaConAnticipacion, $momento);
    }

    private function buscarPlanificacionRegular(
        string $idEspacio,
        int $runNumerico,
        string $codigoDia,
        string $horaActual,
        string $horaConAnticipacion
    ): ?object {
        return DB::table('planificacion_asignaturas as pa')
            ->join('horarios as h', 'pa.id_horario', '=', 'h.id_horario')
            ->join('modulos as m', 'pa.id_modulo', '=', 'm.id_modulo')
            ->where('pa.id_espacio', $idEspacio)
            ->where('pa.id_modulo', 'like', $codigoDia . '.%')
            ->where('h.run_profesor', $runNumerico)
            ->where(function ($query) use ($horaActual, $horaConAnticipacion) {
                $query->where(function ($q) use ($horaActual) {
                    $q->where('m.hora_inicio', '<=', $horaActual)
                        ->where('m.hora_termino', '>=', $horaActual);
                })->orWhere(function ($q) use ($horaActual, $horaConAnticipacion) {
                    $q->where('m.hora_inicio', '>', $horaActual)
                        ->where('m.hora_inicio', '<=', $horaConAnticipacion);
                });
            })
            ->select(
                'pa.id_modulo',
                'm.hora_inicio',
                'm.hora_termino',
                'pa.id_asignatura',
                DB::raw("'regular' as fuente_programacion")
            )
            ->first();
    }

    private function buscarPlanificacionColaborador(
        string $idEspacio,
        int $runNumerico,
        string $codigoDia,
        string $horaActual,
        string $horaConAnticipacion,
        Carbon $momento
    ): ?object {
        $hoy = $momento->toDateString();

        return DB::table('planificaciones_profesores_colaboradores as ppc')
            ->join('profesores_colaboradores as pc', 'ppc.id_profesor_colaborador', '=', 'pc.id')
            ->join('modulos as m', 'ppc.id_modulo', '=', 'm.id_modulo')
            ->where('ppc.id_espacio', $idEspacio)
            ->where('ppc.id_modulo', 'like', $codigoDia . '.%')
            ->where('pc.run_profesor_colaborador', $runNumerico)
            ->where('pc.estado', 'activo')
            ->whereDate('pc.fecha_inicio', '<=', $hoy)
            ->whereDate('pc.fecha_termino', '>=', $hoy)
            ->where(function ($query) use ($horaActual, $horaConAnticipacion) {
                $query->where(function ($q) use ($horaActual) {
                    $q->where('m.hora_inicio', '<=', $horaActual)
                        ->where('m.hora_termino', '>=', $horaActual);
                })->orWhere(function ($q) use ($horaActual, $horaConAnticipacion) {
                    $q->where('m.hora_inicio', '>', $horaActual)
                        ->where('m.hora_inicio', '<=', $horaConAnticipacion);
                });
            })
            ->select(
                'm.id_modulo',
                'm.hora_inicio',
                'm.hora_termino',
                'pc.id_asignatura as id_asignatura',
                DB::raw("'colaborador' as fuente_programacion")
            )
            ->first();
    }

    private function diaAsciiAPrefijoModulo(string $diaAscii): ?string
    {
        return match ($diaAscii) {
            'lunes' => 'LU',
            'martes' => 'MA',
            'miercoles' => 'MI',
            'jueves' => 'JU',
            'viernes' => 'VI',
            'sabado' => 'SA',
            'domingo' => 'DO',
            default => null,
        };
    }
}
