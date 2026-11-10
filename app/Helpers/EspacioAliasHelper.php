<?php

namespace App\Helpers;

use App\Models\Espacio;
use App\Models\Tenant;

class EspacioAliasHelper
{
    /**
     * Grupos de alias específicos conocidos para la sede Talcahuano (TH).
     */
    private static array $thAliasGrupos = [
        ['TH-L09', 'TH-L9', 'TH-30', 'TH30', '30', 'TH-LAB09', 'TH-LAB9', 'TH-LA09', 'TH-LA9', 'L09', 'L9'],
        ['TH-L08', 'TH-L8', 'THL08', 'THL8', 'TH-LA8', 'TH-LA08', 'THLA8', 'THLA08', 'TH-LAB8', 'TH-LAB08', 'L08', 'L8', 'LA8', 'LA08'],
        ['TH-LA10', 'TH-L10', 'TH-LAB10', 'THLA10', 'THL10', 'LA10', 'L10'],
    ];

    /**
     * Normaliza un identificador de espacio proveniente de la lectura QR o APIs externas
     * al identificador oficial de espacio en AulaSync para la sede correspondiente.
     *
     * @param string $espacioRaw Nombre o código de espacio leído (ej. "TH-30", "30", "TH01", "01")
     * @param string|null $sedeId Identificador de la sede (ej. "TH", "LA", "CH", "CT", "CCP")
     * @param array $contexto Datos adicionales de contexto
     * @return string
     */
    public static function normalizar(string $espacioRaw, ?string $sedeId = null, array $contexto = []): string
    {
        $espacioLimpio = trim($espacioRaw);
        if (empty($espacioLimpio)) {
            return '';
        }

        // Limpiar comillas de escáner en español, espacios y guiones bajos
        $espacioUpper = strtoupper(str_replace([' ', "'", '`', '´', '_'], ['', '-', '-', '-', '-'], $espacioLimpio));

        $sedeUpper = strtoupper(trim($sedeId ?? ''));
        if (empty($sedeUpper) && class_exists(Tenant::class)) {
            $sedeUpper = strtoupper(trim(Tenant::current()?->sede_id ?? Tenant::current()?->prefijo_espacios ?? ''));
        }

        // Corregir códigos donde el prefijo partió la letra inicial del aula (ej: THL-01 -> TH-L01, THC-1 -> TH-C1)
        if (preg_match('/^(CCP|TH|LA|CH|CT)([A-Z])-([A-Z0-9]+)$/', $espacioUpper, $mCorr)) {
            $espacioUpper = "{$mCorr[1]}-{$mCorr[2]}{$mCorr[3]}";
        }

        // Inferir sede si no se suministró y el string tiene prefijo conocido
        if (empty($sedeUpper) && preg_match('/^(CCP|TH|LA|CH|CT)-/', $espacioUpper, $mPref)) {
            $sedeUpper = $mPref[1];
        }

        // Asegurar prefijo de sede si se conoce la sede activa
        if (!empty($sedeUpper)) {
            if (!preg_match('/^(CCP|TH|LA|CH|CT)-/', $espacioUpper)) {
                // Caso compacto: ej. TH01 -> TH-01, LA216 -> LA-216, CH101 -> CH-101
                if (preg_match('/^' . $sedeUpper . '([0-9].*)$/', $espacioUpper, $mDigits)) {
                    $espacioUpper = "{$sedeUpper}-{$mDigits[1]}";
                } elseif (preg_match('/^' . $sedeUpper . 'L([0-9].*)$/', $espacioUpper, $mLabDigits)) {
                    $espacioUpper = "{$sedeUpper}-L{$mLabDigits[1]}";
                } else {
                    $espacioUpper = "{$sedeUpper}-{$espacioUpper}";
                }
            }
        }

        // Normalizaciones específicas para la sede Talcahuano (TH)
        if ($sedeUpper === 'TH' || str_starts_with($espacioUpper, 'TH-')) {
            return self::normalizarTalcahuano($espacioUpper, $contexto);
        }

        return $espacioUpper;
    }

    /**
     * Busca un modelo Espacio en la base de datos resolviendo automáticamente alias,
     * formatos con/sin ceros a la izquierda y prefijos de sede, sin restricciones de scope global.
     *
     * @param string|null $idEspacio
     * @param string|null $sedeId
     * @return Espacio|null
     */
    public static function buscarEspacio(?string $idEspacio, ?string $sedeId = null): ?Espacio
    {
        if (empty($idEspacio)) {
            return null;
        }

        // Si el identificador incluye prefijo de sede explícito (CCP-, TH-, LA-, CH-, CT-)
        // y el tenant actual no coincide, conmutar al tenant correspondiente para consultar la BD correcta
        if (preg_match('/^(CCP|TH|LA|CH|CT)[-_]/i', $idEspacio, $mPref)) {
            $sedeTarget = strtoupper($mPref[1]);
            $currentSede = strtoupper(Tenant::current()?->sede_id ?? '');
            if ($currentSede !== $sedeTarget && class_exists(Tenant::class)) {
                $targetTenant = Tenant::where('sede_id', $sedeTarget)->first();
                if ($targetTenant) {
                    $targetTenant->makeCurrent();
                    $sedeId = $sedeTarget;
                }
            }
        }

        $idNormalizado = self::normalizar($idEspacio, $sedeId);
        $equivalentes = self::obtenerEquivalentes($idNormalizado, $sedeId);
        $limpioLookup = str_replace(['-', ' '], '', $idNormalizado);

        return Espacio::withoutGlobalScopes()
            ->where(function ($q) use ($idNormalizado, $equivalentes, $limpioLookup) {
                $q->where('id_espacio', $idNormalizado)
                  ->orWhereIn('id_espacio', $equivalentes)
                  ->orWhereRaw("REPLACE(REPLACE(id_espacio, '-', ''), ' ', '') = ?", [$limpioLookup]);
            })
            ->first();
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
        if (empty($sedeUpper) && class_exists(Tenant::class)) {
            $sedeUpper = strtoupper(trim(Tenant::current()?->sede_id ?? Tenant::current()?->prefijo_espacios ?? ''));
        }

        $equivalentes = [$espacioId, $espacioUpper];

        // Incluir la forma normalizada canónica si difiere
        $normalizado = self::normalizar($espacioId, $sedeUpper);
        if ($normalizado) {
            $equivalentes[] = $normalizado;
        }

        // Si no tiene prefijo conocido pero conocemos la sede, agregar variante con prefijo
        if (!empty($sedeUpper) && !preg_match('/^(CCP|TH|LA|CH|CT)-/', $espacioUpper)) {
            $equivalentes[] = "{$sedeUpper}-{$espacioUpper}";
            if (preg_match('/^' . $sedeUpper . '([A-Z0-9]+)$/', $espacioUpper, $mP)) {
                $equivalentes[] = "{$sedeUpper}-{$mP[1]}";
            }
        }

        // Si tiene prefijo conocido, agregar forma sin prefijo y forma compacta (para todas las sedes)
        if (preg_match('/^(CCP|TH|LA|CH|CT)-([A-Z0-9]+)$/', $espacioUpper, $mP)) {
            $equivalentes[] = $mP[2];
            $equivalentes[] = "{$mP[1]}{$mP[2]}";
        }

        // Aliases específicos para Talcahuano (TH)
        if ($sedeUpper === 'TH' || str_starts_with($espacioUpper, 'TH-') || str_starts_with($espacioUpper, 'TH')) {
            foreach (self::$thAliasGrupos as $grupo) {
                if (count(array_intersect($equivalentes, $grupo)) > 0) {
                    $equivalentes = array_merge($equivalentes, $grupo);
                }
            }

            // Laboratorios TH-L01..TH-L09
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

        // Equivalencias genéricas de formato y ceros a la izquierda para todas las sedes
        foreach (array_values($equivalentes) as $eq) {
            $eqUpper = strtoupper($eq);
            if (preg_match('/^(CCP|TH|LA|CH|CT)[-_]?([A-Z]*)0*(\d+)([A-Z]*)$/', $eqUpper, $matches)) {
                $prefijoSede = $matches[1];
                $prefijoTipo = $matches[2];
                $numero = (int)$matches[3];
                $sufijo = $matches[4];

                $numSinCero = (string)$numero;
                $numConCero = sprintf('%02d', $numero);

                $formas = [
                    "{$prefijoSede}-{$prefijoTipo}{$numSinCero}{$sufijo}",
                    "{$prefijoSede}-{$prefijoTipo}{$numConCero}{$sufijo}",
                    "{$prefijoSede}{$prefijoTipo}{$numSinCero}{$sufijo}",
                    "{$prefijoSede}{$prefijoTipo}{$numConCero}{$sufijo}",
                    "{$prefijoTipo}{$numSinCero}{$sufijo}",
                    "{$prefijoTipo}{$numConCero}{$sufijo}",
                ];
                $equivalentes = array_merge($equivalentes, $formas);
            }
        }

        return array_values(array_unique(array_filter($equivalentes)));
    }

    /**
     * Lógica de equivalencias específicas para la sede Talcahuano (TH)
     */
    private static function normalizarTalcahuano(string $espacioUpper, array $contexto = []): string
    {
        // 1. Laboratorio de Termodinámica / Refrigeración y Climatización (TH-30 -> TH-L09)
        if (in_array($espacioUpper, ['TH-30', '30', 'TH30', 'TH-LAB09', 'TH-LAB9', 'TH-LA09', 'TH-LA9', 'LAB09', 'LAB9', 'L09', 'TH-L9'])) {
            return 'TH-L09';
        }

        // 2. Laboratorio TH-L08 (TH-LA8 / TH-LA08 / TH-LAB8 -> TH-L08)
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
