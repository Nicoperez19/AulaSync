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
        if ($sedeUpper === 'TH' || str_starts_with($espacioUpper, 'TH-') || str_starts_with($espacioUpper, 'TH')) {
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

        // Corrige códigos donde el prefijo de 3 letras partió la letra inicial del aula (ej: THL-01 -> TH-L01, THC-1 -> TH-C1)
        if (preg_match('/^(TH|LA|CH|CT)([A-Z])-([A-Z0-9]+)$/', $espacioUpper, $mCorr)) {
            $espacioCorregido = "{$mCorr[1]}-{$mCorr[2]}{$mCorr[3]}";
            $equivalentes[] = $espacioCorregido;
            $espacioUpper = $espacioCorregido;
        }

        // ── Aliases específicos para Talcahuano (TH) ────────────────────────
        if ($sedeUpper === 'TH' || str_starts_with($espacioUpper, 'TH-') || str_starts_with($espacioUpper, 'TH')) {
            // 1. TH-30 (nombre en programación académica) ↔ TH-L09 (id en AulaSync)
            if (in_array($espacioUpper, ['TH-30', '30', 'TH30', 'TH-L09', 'TH-L9', 'THL09', 'THL9', 'L09', 'L9', 'TH-LAB09', 'TH-LAB9', 'TH-LA09', 'TH-LA9'])) {
                $equivalentes = array_merge($equivalentes, ['TH-L09', 'TH-L9', 'TH-30', 'TH30', '30', 'TH-LAB09', 'TH-LAB9', 'TH-LA09', 'TH-LA9', 'L09', 'L9']);
            }

            // 2. TH-L08 ↔ TH-LA8 / TH-LA08 / TH-LAB08 / TH-LAB8 / TH-L8
            if (in_array($espacioUpper, ['TH-L08', 'TH-L8', 'THL08', 'THL8', 'TH-LA8', 'TH-LA08', 'THLA8', 'THLA08', 'TH-LAB8', 'TH-LAB08', 'L08', 'L8', 'LA8', 'LA08'])) {
                $equivalentes = array_merge($equivalentes, ['TH-L08', 'TH-L8', 'TH-LA8', 'TH-LA08', 'TH-LAB08', 'TH-LAB8', 'L08', 'L8', 'LA8', 'LA08']);
            }

            // 3. TH-LA10 ↔ TH-L10 / TH-LAB10
            if (in_array($espacioUpper, ['TH-LA10', 'TH-L10', 'TH-LAB10', 'THLA10', 'THL10', 'LA10', 'L10'])) {
                $equivalentes = array_merge($equivalentes, ['TH-LA10', 'TH-L10', 'TH-LAB10', 'LA10', 'L10']);
            }

            // 4. Laboratorios TH-L01..TH-L09 ↔ TH-LAB01..TH-LAB09 / TH-LA01..TH-LA09
            if (preg_match('/^TH-?(?:LAB|LA|L)0*([1-9])$/', $espacioUpper, $mLab)) {
                $n = (int) $mLab[1];
                $nPad = sprintf('%02d', $n);
                $equivalentes = array_merge($equivalentes, [
                    "TH-L{$nPad}", "TH-L{$n}", "THL{$nPad}", "THL{$n}",
                    "TH-LA{$nPad}", "TH-LA{$n}", "TH-LAB{$nPad}", "TH-LAB{$n}",
                    "L{$nPad}", "L{$n}",
                ]);
            }
        }

        // ── Equivalencias genéricas de formato y ceros a la izquierda para todas las sedes (LA, CT, CH, TH, CCP, etc.) ──
        // Resuelve inconsistencias como LA-1 ↔ LA-01, CT-02 ↔ CT-2, TH-L09 ↔ TH-L9
        if (preg_match('/^(CCP|TH|LA|CH|CT)[-_]?([A-Z]*)0*(\d+)([A-Z]*)$/', $espacioUpper, $matches)) {
            $prefijoSede = $matches[1];
            $prefijoTipo = $matches[2]; // Ej: 'L' para laboratorio, 'C' para clínica, 'SR' para sala reunión
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
        // Corregir formato partido como THL-01 -> TH-L01, THC-1 -> TH-C1
        if (preg_match('/^TH([A-Z])-([A-Z0-9]+)$/', $espacioUpper, $mCorr)) {
            $espacioUpper = "TH-{$mCorr[1]}{$mCorr[2]}";
        } elseif (preg_match('/^TH([A-Z0-9]+)$/', $espacioUpper, $mCompact)) {
            $espacioUpper = "TH-{$mCompact[1]}";
        }

        // 1. Laboratorio de Termodinámica / Refrigeración y Climatización
        // En la universidad aparece como TH-30 o 30, pero en AulaSync es TH-L09.
        if (in_array($espacioUpper, ['TH-30', '30', 'TH30', 'TH-LAB09', 'TH-LAB9', 'TH-LA09', 'TH-LA9', 'LAB09', 'LAB9', 'L09', 'TH-L9'])) {
            return 'TH-L09';
        }

        // 2. Laboratorio TH-L08 (a veces referido como TH-LA8 / TH-LA08 / TH-LAB8)
        if (in_array($espacioUpper, ['TH-LA8', 'TH-LA08', 'TH-LAB8', 'TH-LAB08', 'TH-L8', 'LA8', 'LA08', 'L08', 'L8'])) {
            return 'TH-L08';
        }

        // 3. Laboratorio TH-LA10
        if (in_array($espacioUpper, ['TH-L10', 'TH-LAB10', 'LA10', 'L10'])) {
            return 'TH-LA10';
        }

        // 4. Normalizar laboratorios TH-L1..TH-L9 o TH-LAB01..TH-LAB09 a TH-L01..TH-L09
        if (preg_match('/^TH-(?:LAB|LA|L)0*([1-9])$/', $espacioUpper, $mLab)) {
            return sprintf('TH-L%02d', (int) $mLab[1]);
        }

        return $espacioUpper;
    }
}
