<?php

namespace App\Helpers;

class ModulosHelper
{
    /**
     * Obtener los horarios de módulos configurados para el sistema
     * Retorna un array con la estructura: ['lunes' => [1 => [...], 2 => [...], ...], ...]
     */
    public static function getHorariosModulos()
    {
        return [
            'lunes' => [
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
            ],
            'martes' => [
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
            ],
            'miercoles' => [
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
            ],
            'jueves' => [
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
            ],
            'viernes' => [
                1  => ['inicio' => '08:10:00', 'fin' => '09:00:00'],
                2  => ['inicio' => '09:10:00', 'fin' => '10:00:00'],
                3  => ['inicio' => '10:10:00', 'fin' => '11:00:00'],
                4  => ['inicio' => '11:10:00', 'fin' => '12:00:00'],
                5  => ['inicio' => '12:10:00', 'fin' => '13:00:00'],
                6  => ['inicio' => '13:10:00', 'fin' => '14:00:00'],
                7  => ['inicio' => '14:10:00', 'fin' => '15:00:00'],
                8  => ['inicio' => '15:10:00', 'fin' => '16:00:00'],
                9  => ['inicio' => '16:10:00', 'fin' => '17:00:00'],
                10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'],
                11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'],
                12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'],
                13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'],
                14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'],
                15 => ['inicio' => '22:10:00', 'fin' => '23:00:00'],
            ],
            // Sábado: solo 5 módulos (08:10 a 13:00)
            'sabado' => [
                1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'],
                2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'],
                3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'],
                4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'],
                5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'],
            ],
        ];
    }

    /**
     * Obtener solo los módulos como array simple (para vistas Blade/JSON)
     * Retorna: [['hora_inicio' => '08:10:00', 'hora_termino' => '09:00:00'], ...]
     */
    public static function getModulosSimples()
    {
        return [
            ['hora_inicio' => '08:10:00', 'hora_termino' => '09:00:00'],
            ['hora_inicio' => '09:10:00', 'hora_termino' => '10:00:00'],
            ['hora_inicio' => '10:10:00', 'hora_termino' => '11:00:00'],
            ['hora_inicio' => '11:10:00', 'hora_termino' => '12:00:00'],
            ['hora_inicio' => '12:10:00', 'hora_termino' => '13:00:00'],
            ['hora_inicio' => '13:10:00', 'hora_termino' => '14:00:00'],
            ['hora_inicio' => '14:10:00', 'hora_termino' => '15:00:00'],
            ['hora_inicio' => '15:10:00', 'hora_termino' => '16:00:00'],
            ['hora_inicio' => '16:10:00', 'hora_termino' => '17:00:00'],
            ['hora_inicio' => '17:10:00', 'hora_termino' => '18:00:00'],
            ['hora_inicio' => '18:10:00', 'hora_termino' => '19:00:00'],
            ['hora_inicio' => '19:10:00', 'hora_termino' => '20:00:00'],
            ['hora_inicio' => '20:10:00', 'hora_termino' => '21:00:00'],
            ['hora_inicio' => '21:10:00', 'hora_termino' => '22:00:00'],
            ['hora_inicio' => '22:10:00', 'hora_termino' => '23:00:00'],
        ];
    }

    /**
     * Obtener horario específico de un módulo en un día
     *
     * @param  string $dia          Nombre del día normalizado (lunes, martes…)
     * @param  int    $numeroModulo Número del módulo (1-15)
     * @return array|null           ['inicio' => 'HH:MM:SS', 'fin' => 'HH:MM:SS'] o null
     */
    public static function getHorarioModulo($dia, $numeroModulo)
    {
        $horarios = self::getHorariosModulos();
        return $horarios[self::normalizarDia($dia)][$numeroModulo] ?? null;
    }

    /**
     * Normalizar el nombre de un día a la clave usada en getHorariosModulos().
     * Acepta mayúsculas/minúsculas y días con o sin tildes.
     *
     * @param  string $dia  Ej: 'Lunes', 'miércoles', 'Sábado…
     * @return string       Ej: 'lunes', 'miercoles', 'sabado'
     */
    public static function normalizarDia(string $dia): string
    {
        $dia = strtolower(trim($dia));
        $map = [
            'lunes'      => 'lunes',
            'martes'     => 'martes',
            'miércoles'  => 'miercoles',
            'miercoles'  => 'miercoles',
            'jueves'     => 'jueves',
            'viernes'    => 'viernes',
            'sábado'     => 'sabado',
            'sabado'     => 'sabado',
            'domingo'    => 'domingo',
        ];
        return $map[$dia] ?? $dia;
    }

    /**
     * Obtener el número de módulo desde un id_modulo con formato "PREFIJO.NUMERO".
     * Ejemplo: 'LU.1' -> 1, 'MA.12' -> 12
     *
     * @param  string $idModulo  Ej: 'LU.1', 'MI.5'
     * @return int               Número del módulo (0 si no se puede parsear)
     */
    public static function getNumeroModulo(string $idModulo): int
    {
        $partes = explode('.', $idModulo);
        return isset($partes[1]) ? (int) $partes[1] : 0;
    }

    /**
     * Obtener los minutos de margen de ingreso según el módulo.
     *
     * Regla de negocio:
     *   - Módulo 1 (08:10): 40 minutos antes (puede ingresar desde las 07:30)
     *   - Cualquier otro módulo: 10 minutos antes del inicio
     *
     * @param  string $idModulo  Ej: 'LU.1', 'MI.3'
     * @return int               Minutos de tolerancia de ingreso
     */
    public static function getMargenIngresoMinutos(string $idModulo): int
    {
        return self::getNumeroModulo($idModulo) === 1 ? 40 : 10;
    }
}
