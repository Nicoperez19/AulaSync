<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $conn = Schema::connection('tenant');

        // 1. Asegurar registro de feriados legales de Agosto y Septiembre (2025 y 2026) en todas las sedes
        if ($conn->hasTable('dias_feriados')) {
            $feriados = [
                // 2026
                [
                    'fecha_inicio' => '2026-08-15',
                    'fecha_fin'    => '2026-08-15',
                    'nombre'       => 'Asunción de la Virgen',
                    'descripcion'  => 'Feriado legal',
                    'tipo'         => 'feriado',
                ],
                [
                    'fecha_inicio' => '2026-09-18',
                    'fecha_fin'    => '2026-09-19',
                    'nombre'       => 'Fiestas Patrias',
                    'descripcion'  => 'Independencia Nacional y Día de las Glorias del Ejército',
                    'tipo'         => 'feriado',
                ],
                // 2025
                [
                    'fecha_inicio' => '2025-08-15',
                    'fecha_fin'    => '2025-08-15',
                    'nombre'       => 'Asunción de la Virgen',
                    'descripcion'  => 'Feriado legal',
                    'tipo'         => 'feriado',
                ],
                [
                    'fecha_inicio' => '2025-09-18',
                    'fecha_fin'    => '2025-09-19',
                    'nombre'       => 'Fiestas Patrias',
                    'descripcion'  => 'Independencia Nacional y Día de las Glorias del Ejército',
                    'tipo'         => 'feriado',
                ],
            ];

            foreach ($feriados as $f) {
                $existe = DB::connection('tenant')
                    ->table('dias_feriados')
                    ->whereDate('fecha_inicio', '<=', $f['fecha_fin'])
                    ->whereDate('fecha_fin', '>=', $f['fecha_inicio'])
                    ->exists();

                if (!$existe) {
                    DB::connection('tenant')->table('dias_feriados')->insert([
                        'fecha_inicio' => $f['fecha_inicio'],
                        'fecha_fin'    => $f['fecha_fin'],
                        'nombre'       => $f['nombre'],
                        'descripcion'  => $f['descripcion'],
                        'tipo'         => $f['tipo'],
                        'activo'       => true,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);
                } else {
                    DB::connection('tenant')
                        ->table('dias_feriados')
                        ->whereDate('fecha_inicio', '<=', $f['fecha_fin'])
                        ->whereDate('fecha_fin', '>=', $f['fecha_inicio'])
                        ->update(['activo' => true, 'updated_at' => now()]);
                }
            }
        }

        // 2. Limpiar registros espurios de clases_no_realizadas con estado 'no_realizada' generados en feriados
        if ($conn->hasTable('clases_no_realizadas')) {
            $fechasLimpieza = [
                '2026-08-15', '2026-09-18', '2026-09-19',
                '2025-08-15', '2025-09-18', '2025-09-19',
            ];

            DB::connection('tenant')
                ->table('clases_no_realizadas')
                ->whereIn('fecha_clase', $fechasLimpieza)
                ->where('estado', 'no_realizada')
                ->delete();
        }
    }

    public function down(): void
    {
        // No revertir datos de sincronización histórica
    }
};
