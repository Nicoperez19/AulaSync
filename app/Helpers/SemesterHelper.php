<?php

namespace App\Helpers;

use App\Models\PeriodoAcademico;
use Carbon\Carbon;

class SemesterHelper
{
    /**
     * Obtiene el semestre actual basado en la fecha
     * Primero intenta usar los períodos académicos de la BD,
     * si no hay, usa la lógica por defecto (21 de julio)
     * 
     * @param Carbon|null $date
     * @return int
     */
    public static function getCurrentSemester($date = null)
    {
        $date = $date ?: Carbon::now();
        
        // Intentar obtener el período académico de la BD
        $periodo = PeriodoAcademico::obtenerPeriodoActual($date);
        if ($periodo) {
            return $periodo->semestre;
        }

        // Fallback: lógica por defecto
        $month = $date->month;
        $day = $date->day;

        // Segundo semestre comienza el 21 de julio
        if ($month >= 7 && $day >= 21) {
            return 2;
        } elseif ($month >= 8) {
            return 2;
        } else {
            return 1;
        }
    }

    /**
     * Obtiene el año académico actual
     * 
     * @param Carbon|null $date
     * @return int
     */
    public static function getCurrentAcademicYear($date = null)
    {
        $date = $date ?: Carbon::now();
        
        // Intentar obtener el período académico de la BD
        $periodo = PeriodoAcademico::obtenerPeriodoActual($date);
        if ($periodo) {
            return $periodo->anio;
        }

        // Fallback: lógica por defecto
        return $date->year;
    }

    /**
     * Obtiene el período actual en formato 'año-semestre'
     * 
     * @param Carbon|null $date
     * @return string
     */
    public static function getCurrentPeriod($date = null)
    {
        $year = self::getCurrentAcademicYear($date);
        $semester = self::getCurrentSemester($date);
        
        return $year . '-' . $semester;
    }

    /**
     * Verifica si una fecha está en el segundo semestre
     * 
     * @param Carbon|null $date
     * @return bool
     */
    public static function isSecondSemester($date = null)
    {
        return self::getCurrentSemester($date) === 2;
    }

    /**
     * Obtiene la fecha de inicio del segundo semestre para un año específico
     * 
     * @param int $year
     * @return Carbon
     */
    public static function getSecondSemesterStartDate($year)
    {
        // Intentar obtener de la BD
        $periodo = PeriodoAcademico::where('anio', $year)
            ->where('semestre', 2)
            ->where('activo', true)
            ->first();
        
        if ($periodo) {
            return $periodo->fecha_inicio;
        }
        
        return Carbon::create($year, 7, 21);
    }

    /**
     * Obtiene la fecha de fin del segundo semestre para un año específico
     * 
     * @param int $year
     * @return Carbon
     */
    public static function getSecondSemesterEndDate($year)
    {
        // Intentar obtener de la BD
        $periodo = PeriodoAcademico::where('anio', $year)
            ->where('semestre', 2)
            ->where('activo', true)
            ->first();
        
        if ($periodo) {
            return $periodo->fecha_fin;
        }
        
        return Carbon::create($year + 1, 2, 28); // Fin de febrero del año siguiente
    }

    /**
     * Verifica si hay un período académico activo para la fecha actual
     * 
     * @param Carbon|null $date
     * @return bool
     */
    public static function hayPeriodoActivo($date = null)
    {
        return PeriodoAcademico::estaEnPeriodoActivo($date);
    }

    /**
     * Obtiene el período académico actual
     * 
     * @param Carbon|null $date
     * @return PeriodoAcademico|null
     */
    public static function getPeriodoActual($date = null)
    {
        return PeriodoAcademico::obtenerPeriodoActual($date);
    }

    /**
     * Verifica si el período del horario es válido (no ha terminado)
     * 
     * @param string $periodoHorario Formato: "año-semestre" ej: "2025-2"
     * @return bool
     */
    public static function esPeriodoValido($periodoHorario)
    {
        if (!$periodoHorario) {
            return false;
        }

        $partes = explode('-', $periodoHorario);
        if (count($partes) !== 2) {
            return false;
        }

        $anio = (int) $partes[0];
        $semestre = (int) $partes[1];

        // Buscar el período en la BD
        $periodo = PeriodoAcademico::where('anio', $anio)
            ->where('semestre', $semestre)
            ->where('activo', true)
            ->first();

        if (!$periodo) {
            // Si no hay período configurado, verificar si es el período actual por defecto
            $periodoActual = self::getCurrentPeriod();
            return $periodoHorario === $periodoActual;
        }

        // El período es válido si no ha finalizado
        return !$periodo->haFinalizado();
    }

    /**
     * Verifica si se necesita nueva programación (el período anterior terminó)
     * 
     * @return bool
     */
    public static function necesitaNuevaProgramacion()
    {
        $periodoActual = PeriodoAcademico::obtenerPeriodoActual();
        
        // Si no hay período activo, se necesita configurar uno
        if (!$periodoActual) {
            return true;
        }

        return false;
    }

    /**
     * Obtiene información sobre el estado del período para mostrar al usuario
     * 
     * @return array
     */
    public static function getEstadoPeriodo()
    {
        $periodoActual = PeriodoAcademico::obtenerPeriodoActual();
        $hoy = Carbon::now();

        if (!$periodoActual) {
            // Buscar el último período que terminó
            $ultimoPeriodo = PeriodoAcademico::where('activo', true)
                ->where('fecha_fin', '<', $hoy)
                ->orderBy('fecha_fin', 'desc')
                ->first();

            if ($ultimoPeriodo) {
                return [
                    'activo' => false,
                    'mensaje' => "El {$ultimoPeriodo->nombre_completo} finalizó el {$ultimoPeriodo->fecha_fin->format('d/m/Y')}. Se requiere configurar un nuevo período académico.",
                    'requiere_accion' => true,
                    'ultimo_periodo' => $ultimoPeriodo,
                ];
            }

            return [
                'activo' => false,
                'mensaje' => 'No hay períodos académicos configurados. Configure uno para habilitar la planificación.',
                'requiere_accion' => true,
                'ultimo_periodo' => null,
            ];
        }

        // Verificar si estamos cerca del fin del período
        $diasRestantes = $hoy->diffInDays($periodoActual->fecha_fin, false);

        if ($diasRestantes <= 14 && $diasRestantes > 0) {
            return [
                'activo' => true,
                'mensaje' => "El {$periodoActual->nombre_completo} finaliza en {$diasRestantes} días ({$periodoActual->fecha_fin->format('d/m/Y')}).",
                'requiere_accion' => false,
                'alerta' => true,
                'periodo_actual' => $periodoActual,
            ];
        }

        return [
            'activo' => true,
            'mensaje' => "Período activo: {$periodoActual->nombre_completo}",
            'requiere_accion' => false,
            'alerta' => false,
            'periodo_actual' => $periodoActual,
        ];
    }
} 