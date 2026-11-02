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
     * @param string|null $espacioId
     * @param string|null $sedeId
     * @return array
     */
    public static function obtenerEquivalentes(?string $espacioId, ?string $sedeId = null): array
    {
        if (empty($espacioId)) {
            return [];
        }
        $espacioId = trim($espacioId);
        $espacioUpper = strtoupper($espacioId);
        $sedeUpper    = strtoupper(trim($sedeId ?? ''));

        $equivalentes = [$espacioId, $espacioUpper];

        // ── Aliases específicos para Talcahuano (TH) ────────────────────────
        // TH-30 (nombre en programación académica) ↔ TH-L09 (id en AulaSync)
        if ($sedeUpper === 'TH' || str_starts_with($espacioUpper, 'TH-') || str_starts_with($espacioUpper, 'TH')) {
            if (in_array($espacioUpper, ['TH-30', '30', 'TH30', 'TH-L09', 'L09', 'TH-LAB09', 'TH-LAB9'])) {
                $equivalentes = array_merge($equivalentes, ['TH-L09', 'TH-30', 'TH30', 'TH-LAB09', 'TH-LAB9']);
            }
        }

        // ── Equivalencias genéricas de formato y ceros a la izquierda para todas las sedes (LA, CT, CH, TH, CCP, etc.) ──
        // Resuelve inconsistencias como LA-1 ↔ LA-01, CT-02 ↔ CT-2, TH-L09 ↔ TH-L9
        if (preg_match('/^([A-Z]+)[-_]?([A-Z]*)0*(\d+)([A-Z]*)$/', $espacioUpper, $matches)) {
            $prefijoSede = $matches[1];
            $prefijoTipo = $matches[2]; // Ej: 'L' para laboratorio
            $numero = (int)$matches[3];
            $sufijo = $matches[4];

            $numSinCero = (string)$numero;
            $numConCero = sprintf('%02d', $numero);

            $formas = [
                "{$prefijoSede}-{$prefijoTipo}{$numSinCero}{$sufijo}",
                "{$prefijoSede}-{$prefijoTipo}{$numConCero}{$sufijo}",
                "{$prefijoSede}{$prefijoTipo}{$numSinCero}{$sufijo}",
                "{$prefijoSede}{$prefijoTipo}{$numConCero}{$sufijo}",
            ];
            $equivalentes = array_merge($equivalentes, $formas);
        }

        return array_values(array_unique(array_filter($equivalentes)));
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
