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
    public static function obtenerEquivalentes(string $espacioId, ?string $sedeId = null): array
    {
        $espacioUpper = strtoupper(trim($espacioId));
        $sedeUpper    = strtoupper(trim($sedeId ?? ''));

        // ── Aliases para Talcahuano (TH) ────────────────────────────────────
        // TH-30 (nombre en programación académica) ↔ TH-L09 (id en AulaSync)
        if ($sedeUpper === 'TH' || str_starts_with($espacioUpper, 'TH-')) {
            if (in_array($espacioUpper, ['TH-30', 'TH30', 'TH-L09', 'L09', 'TH-LAB09', 'TH-LAB9'])) {
                return ['TH-L09', 'TH-30'];
            }
        }

        // ── Aliases para Chillán (CH) ─────────────────────────────────────
        // Agregar aquí equivalencias propias de CH cuando se conozcan.
        // Ejemplo futuro:
        // if ($sedeUpper === 'CH' || str_starts_with($espacioUpper, 'CH-')) {
        //     if (in_array($espacioUpper, ['CH-GIMN', 'CH-GYM'])) return ['CH-GIM'];
        // }

        // ── Aliases para Los Ángeles (LA) ────────────────────────────────────
        // Agregar aquí equivalencias propias de LA cuando se conozcan.

        // ── Aliases para Cañete (CT) ─────────────────────────────────────────
        // Agregar aquí equivalencias propias de CT cuando se conozcan.

        // Sin alias: retornar el espacio tal como viene
        return [$espacioId];
    }

    /**
     * Lógica de equivalencias para la sede Talcahuano (TH)
     */
    private static function normalizarTalcahuano(string $espacioUpper, array $contexto = []): string
    {
        // 1. Laboratorio de Termodinámica / Refrigeración y Climatización
        // En la universidad aparece como TH-30 o 30, pero en AulaSync es TH-L09.
        if (in_array($espacioUpper, ['TH-30', '30', 'TH30', 'TH-LAB09', 'TH-LAB9', 'LAB09', 'LAB9', 'L09'])) {
            return 'TH-L09';
        }

        return $espacioUpper;
    }
}
