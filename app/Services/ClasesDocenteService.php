<?php

namespace App\Services;

use App\Helpers\ModulosHelper;
use App\Models\Asignatura;
use App\Models\Modulo;
use App\Models\Planificacion_Asignatura;
use App\Models\ProfesorColaborador;
use App\Models\Reserva;
use App\Models\SesionAsistencia;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ClasesDocenteService
{
    /**
     * Obtiene todas las clases (asignaturas regulares y colaboraciones/temporales)
     * asignadas a un docente, enriquecidas con la planificación de hoy y estado de ingreso a sala.
     *
     * @param string $runDocente
     * @return Collection
     */
    public function getClasesDocente(string $runDocente): Collection
    {
        $hoy = Carbon::today();
        $diaSemana = $this->obtenerDiaSemanaEspanol($hoy);

        // 1. Reservas activas del docente hoy (ingreso registrado a sala)
        $reservasActivas = Reserva::where('fecha_reserva', $hoy->format('Y-m-d'))
            ->where('estado', 'activa')
            ->whereNull('hora_salida')
            ->where('run_profesor', $runDocente)
            ->with(['espacio'])
            ->get();

        // 2. Asignaturas como titular
        $asignaturasTitular = Asignatura::where('run_profesor', $runDocente)
            ->with(['carrera', 'planificaciones.modulo', 'planificaciones.espacio', 'estudiantes'])
            ->get();

        // 3. Asignaturas como reemplazo
        $asignaturasReemplazo = Asignatura::where('run_profesor_reemplazo', $runDocente)
            ->where('run_profesor', '!=', $runDocente)
            ->with(['carrera', 'planificaciones.modulo', 'planificaciones.espacio', 'estudiantes'])
            ->get();

        // 4. Colaboraciones activas y vigentes
        $colaboraciones = ProfesorColaborador::where('run_profesor_colaborador', $runDocente)
            ->activosYVigentes($hoy)
            ->with(['asignatura.carrera', 'asignatura.estudiantes', 'planificaciones.modulo', 'planificaciones.espacio'])
            ->get();

        $clases = collect();

        // Procesar Titular
        foreach ($asignaturasTitular as $asig) {
            $clases->push($this->formatearClaseAsignatura($asig, 'titular', $diaSemana, $reservasActivas, $hoy));
        }

        // Procesar Reemplazo
        foreach ($asignaturasReemplazo as $asig) {
            $clases->push($this->formatearClaseAsignatura($asig, 'reemplazo', $diaSemana, $reservasActivas, $hoy));
        }

        // Procesar Colaboraciones (con o sin asignatura)
        foreach ($colaboraciones as $colab) {
            $clases->push($this->formatearClaseColaborador($colab, $diaSemana, $reservasActivas, $hoy));
        }

        return $clases;
    }

    /**
     * Formatea los datos de una asignatura regular para la vista del dashboard.
     */
    protected function formatearClaseAsignatura(Asignatura $asig, string $rol, string $diaSemana, Collection $reservasActivas, Carbon $hoy): array
    {
        // Buscar planificación para el día de hoy
        $planHoy = $asig->planificaciones->first(function ($p) use ($diaSemana) {
            return $p->modulo && strcasecmp(ModulosHelper::normalizarDia($p->modulo->dia), $diaSemana) === 0;
        });

        // Si hoy no tiene planificación, obtener el espacio habitual asignado al curso
        $planHabitual = $asig->planificaciones->first(function ($p) {
            return $p->espacio !== null;
        });

        $espacioPlanificado = $planHoy?->espacio ?? $planHabitual?->espacio;
        $moduloPlanificado = $planHoy?->modulo;

        // Verificar si el docente está en la sala (reserva activa para este espacio o asignatura)
        $reservaActiva = $reservasActivas->first(function ($r) use ($asig, $espacioPlanificado) {
            if ($r->id_asignatura === $asig->id_asignatura) {
                return true;
            }
            if ($espacioPlanificado && $r->id_espacio === $espacioPlanificado->id_espacio) {
                return true;
            }
            return false;
        });

        // Verificar si ya se pasó asistencia hoy para esta asignatura
        $sesionHoy = SesionAsistencia::where('id_asignatura', $asig->id_asignatura)
            ->where('fecha', $hoy->format('Y-m-d'))
            ->latest()
            ->first();

        return [
            'id' => $asig->id_asignatura,
            'id_asignatura' => $asig->id_asignatura,
            'id_colaborador' => null,
            'tipo' => 'asignatura',
            'rol' => $rol, // 'titular' o 'reemplazo'
            'codigo' => $asig->codigo_asignatura,
            'nombre' => $asig->nombre_asignatura,
            'seccion' => $asig->seccion,
            'periodo' => $asig->periodo,
            'carrera' => $asig->carrera?->nombre ?? ($asig->id_carrera ? 'Carrera ' . $asig->id_carrera : 'Sin carrera'),
            'id_carrera' => $asig->id_carrera,
            'area_academica' => $asig->carrera?->id_area_academica,
            'total_inscritos' => $asig->estudiantes->count(),
            'tiene_clase_hoy' => $planHoy !== null,
            'espacio_planificado' => $this->obtenerEspacioFormateado($espacioPlanificado),
            'espacio_id' => $espacioPlanificado?->id_espacio,
            'modulo_planificado' => $moduloPlanificado ? "Módulo {$moduloPlanificado->numero_modulo} ({$moduloPlanificado->hora_inicio} - {$moduloPlanificado->hora_termino})" : null,
            'en_sala' => $reservaActiva !== null,
            'reserva_activa' => $reservaActiva,
            'espacio_actual' => $this->obtenerEspacioFormateado($reservaActiva?->espacio),
            'asistencia_registrada' => $sesionHoy !== null,
            'sesion_hoy_id' => $sesionHoy?->id,
            'asistencia_presentes' => $sesionHoy?->totalPresentes() ?? 0,
        ];
    }

    /**
     * Formatea los datos de una colaboración o clase temporal.
     */
    protected function formatearClaseColaborador(ProfesorColaborador $colab, string $diaSemana, Collection $reservasActivas, Carbon $hoy): array
    {
        $planHoy = $colab->planificaciones->first(function ($p) use ($diaSemana) {
            return $p->modulo && strcasecmp(ModulosHelper::normalizarDia($p->modulo->dia), $diaSemana) === 0;
        });

        $planHabitual = $colab->planificaciones->first(function ($p) {
            return $p->espacio !== null;
        });

        $espacioPlanificado = $planHoy?->espacio ?? $planHabitual?->espacio;
        $moduloPlanificado = $planHoy?->modulo;

        $reservaActiva = $reservasActivas->first(function ($r) use ($colab, $espacioPlanificado) {
            if ($colab->id_asignatura && $r->id_asignatura === $colab->id_asignatura) {
                return true;
            }
            if ($espacioPlanificado && $r->id_espacio === $espacioPlanificado->id_espacio) {
                return true;
            }
            return false;
        });

        $sesionHoy = SesionAsistencia::where('id_profesor_colaborador', $colab->id)
            ->where('fecha', $hoy->format('Y-m-d'))
            ->latest()
            ->first();

        $nombre = $colab->nombre_asignatura;
        $totalInscritos = $colab->asignatura ? $colab->asignatura->estudiantes->count() : $colab->cantidad_inscritos;

        return [
            'id' => 'colab_' . $colab->id,
            'id_asignatura' => $colab->id_asignatura,
            'id_colaborador' => $colab->id,
            'tipo' => $colab->id_asignatura ? 'colaboracion' : 'temporal',
            'rol' => 'colaborador',
            'codigo' => $colab->asignatura?->codigo_asignatura ?? 'TEMP-' . $colab->id,
            'nombre' => $nombre,
            'seccion' => $colab->asignatura?->seccion ?? 'N/A',
            'periodo' => $colab->asignatura?->periodo ?? null,
            'carrera' => $colab->asignatura?->carrera?->nombre ?? ($colab->descripcion ?? 'Clase Temporal'),
            'id_carrera' => $colab->asignatura?->id_carrera,
            'area_academica' => $colab->asignatura?->carrera?->id_area_academica,
            'total_inscritos' => $totalInscritos,
            'tiene_clase_hoy' => $planHoy !== null,
            'espacio_planificado' => $this->obtenerEspacioFormateado($espacioPlanificado),
            'espacio_id' => $espacioPlanificado?->id_espacio,
            'modulo_planificado' => $moduloPlanificado ? "Módulo {$moduloPlanificado->numero_modulo} ({$moduloPlanificado->hora_inicio} - {$moduloPlanificado->hora_termino})" : null,
            'en_sala' => $reservaActiva !== null,
            'reserva_activa' => $reservaActiva,
            'espacio_actual' => $this->obtenerEspacioFormateado($reservaActiva?->espacio),
            'asistencia_registrada' => $sesionHoy !== null,
            'sesion_hoy_id' => $sesionHoy?->id,
            'asistencia_presentes' => $sesionHoy?->totalPresentes() ?? 0,
        ];
    }

    /**
     * Formatea el nombre visual del espacio con su código (ej: "TH-10 (Sala de Clases)").
     */
    protected function obtenerEspacioFormateado($espacio): ?string
    {
        if (!$espacio) {
            return null;
        }

        $id = trim((string) ($espacio->id_espacio ?? ''));
        $nombre = trim((string) ($espacio->nombre_espacio ?? ''));

        if ($id && $nombre && strcasecmp($id, $nombre) !== 0) {
            return "{$id} ({$nombre})";
        }

        return $nombre ?: ($id ?: null);
    }

    /**
     * Convierte la fecha de Carbon al nombre del día en español normalizado.
     */
    protected function obtenerDiaSemanaEspanol(Carbon $fecha): string
    {
        $dias = [
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miercoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sabado',
            7 => 'Domingo',
        ];

        return $dias[$fecha->dayOfWeekIso] ?? 'Lunes';
    }
}
