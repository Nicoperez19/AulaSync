<?php

namespace App\Helpers;

use Carbon\Carbon;

class SemesterHelper
{
    /**
     * Obtiene el semestre actual basado en la fecha
     * Segundo semestre comienza el 21 de julio
     * 
     * @param Carbon|null $date
     * @return int
     */
    public static function getCurrentSemester($date = null)
    {
        $date = $date ?: Carbon::now();
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
        $month = $date->month;
        $day = $date->day;

        // Si estamos en el segundo semestre (después del 21 de julio), 
        // el año académico es el año actual
        if ($month >= 7 && $day >= 21) {
            return $date->year;
        } elseif ($month >= 8) {
            return $date->year;
        } else {
            // Si estamos en el primer semestre, el año académico es el año actual
            return $date->year;
        }
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
        return Carbon::create($year + 1, 2, 28); // Fin de febrero del año siguiente
    }
} 