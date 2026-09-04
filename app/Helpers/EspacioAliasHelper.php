<?php

namespace App\Helpers;

class EspacioAliasHelper
{
    /**
     * Normaliza un identificador de espacio proveniente de la programación externa (Excel/API)
     * al identificador oficial de espacio en AulaSync para la sede correspondiente.
     *
     * @param string $espacioRaw Nombre o código de espacio leído (ej. "TH-30", "TH-09", "30")
     * @param string|null $sedeId Identificador de la sede (ej. "TH", "LA", "CH", "CT")
     * @param array $contexto Datos adicionales de contexto: ['carrera' => ..., 'asignatura' => ..., 'tipo_bloque' => ...]
     * @return string
     */
    public static function normalizar(string $espacioRaw, ?string $sedeId = null, array $contexto = []): string
    {
        $espacioLimpio = trim($espacioRaw);
        $espacioUpper = strtoupper($espacioLimpio);
        $sedeUpper = strtoupper(trim($sedeId ?? ''));

        // Normalizaciones específicas para la sede Talcahuano (TH)
        if ($sedeUpper === 'TH' || str_starts_with($espacioUpper, 'TH-')) {
            return self::normalizarTalcahuano($espacioUpper, $contexto);
        }

        return $espacioLimpio;
    }

    /**
     * Devuelve la lista de identificadores equivalentes para un espacio dado.
     * Permite hacer búsquedas bidireccionales donde cualquiera de los alias sea reconocido.
     *
     * @param string $espacioId
     * @param string|null $sedeId
     * @return array
     */
    public static function obtenerEquivalentes(string $espacioId, ?string $sedeId = 'TH'): array
    {
        $espacioUpper = strtoupper(trim($espacioId));

        // Grupo Termodinámica / Refrigeración: TH-30 <-> TH-L09
        if (in_array($espacioUpper, ['TH-30', '30', 'TH-L09', 'L09', 'TH-LAB09', 'TH-LAB9'])) {
            return ['TH-L09', 'TH-30'];
        }

        // Grupo Taller de Construcción: TH-09 <-> TH-L08
        if (in_array($espacioUpper, ['TH-L08', 'L08', 'TH-LA8', 'LA8', 'TH-LAB08', 'TH-LAB8', 'TH-09', '09'])) {
            return ['TH-L08', 'TH-09', 'TH-LA8'];
        }

        return [$espacioId];
    }

    /**
     * Lógica de equivalencias para la sede Talcahuano (TH)
     */
    private static function normalizarTalcahuano(string $espacioUpper, array $contexto): string
    {
        // 1. Laboratorio de Termodinámica / Refrigeración y Climatización
        // En la universidad aparece como TH-30 o 30, pero en AulaSync es TH-L09.
        if (in_array($espacioUpper, ['TH-30', '30', 'TH-LAB09', 'TH-LAB9', 'LAB09', 'LAB9'])) {
            return 'TH-L09';
        }

        // 2. Laboratorio / Taller de Construcción
        // En AulaSync es TH-L08 (variantes históricas o alias LA8, LAB08, etc.)
        if (in_array($espacioUpper, ['TH-LA8', 'LA8', 'TH-LAB08', 'TH-LAB8', 'LAB08', 'LAB8'])) {
            return 'TH-L08';
        }

        // 3. Caso especial TH-09:
        // En la sede TH existen físicamente:
        //   - TH-09: Sala de Clases teórica (Piso 2)
        //   - TH-L08: Laboratorio / Taller de Construcción (Piso 1)
        // En la universidad, las clases de construcción vienen codificadas como TH-09.
        if (in_array($espacioUpper, ['TH-09', '09'])) {
            if (self::esAsignaturaDeConstruccion($contexto)) {
                return 'TH-L08';
            }
            return 'TH-09';
        }

        return $espacioUpper;
    }

    /**
     * Determina si el contexto de la clase corresponde al área de Construcción
     */
    private static function esAsignaturaDeConstruccion(array $contexto): bool
    {
        $carrera = strtoupper($contexto['carrera'] ?? '');
        $asignatura = strtoupper($contexto['asignatura'] ?? '');
        $tipoBloque = strtolower($contexto['tipo_bloque'] ?? '');

        // 1. Carrera de Construcción
        if (str_contains($carrera, 'CONSTRUCCI') || str_contains($carrera, 'OBRAS VIALES') || str_contains($carrera, 'EDIFICACI')) {
            // Si el bloque es explícitamente taller ("ta") o es una asignatura técnica:
            if ($tipoBloque === 'ta' || self::esAsignaturaPracticaConstruccion($asignatura)) {
                return true;
            }

            // Incluso si no dice "ta", si la asignatura es técnica de construcción se realiza en LAB08
            if (self::esAsignaturaTecnicaConstruccion($asignatura)) {
                return true;
            }
        }

        // 2. Por nombre directo de la asignatura (incluso si la carrera viniera incompleta)
        if (self::esAsignaturaPracticaConstruccion($asignatura)) {
            return true;
        }

        return false;
    }

    /**
     * Palabras clave de asignaturas prácticas de construcción
     */
    private static function esAsignaturaPracticaConstruccion(string $asignatura): bool
    {
        $keywords = [
            'HORMIGÓN',
            'HORMIGON',
            'ENFIERRADURA',
            'CONSTRUCCIONES DE MADERA',
            'MADERA',
            'EDIFICACIÓN',
            'EDIFICACION',
            'INSTALACIONES ELÉCTRICAS',
            'INSTALACIONES ELECTRICAS',
            'TALLER DE CONSTRUCCIÓN',
            'TALLER DE CONSTRUCCION',
            'MATERIALES DE CONSTRUCCIÓN',
            'TOPOGRAFÍA',
            'TOPOGRAFIA',
            'OBRAS CIVILES',
            'OBRAS MENORES',
            'SUPERVISIÓN Y CONTROL DE CALIDAD',
            'SUPERVISION Y CONTROL DE CALIDAD',
            'PREVENCIÓN DE RIESGOS EN LA CONSTRUCCIÓN',
        ];

        foreach ($keywords as $kw) {
            if (str_contains($asignatura, $kw)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Asignaturas de la malla de construcción que utilizan el taller
     */
    private static function esAsignaturaTecnicaConstruccion(string $asignatura): bool
    {
        $keywords = [
            'PRESUPUESTO',
            'CUBICACIÓN',
            'CUBICACION',
            'ESTÁTICA',
            'ESTATICA',
            'MODELAMIENTO',
            'BIM',
        ];

        foreach ($keywords as $kw) {
            if (str_contains($asignatura, $kw)) {
                return true;
            }
        }

        return false;
    }
}
