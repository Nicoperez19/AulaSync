<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $conn = Schema::connection('tenant');

        // 1. Asegurar registro del feriado 12/10 y Semana Ajuste Académico 13/10 al 17/10 en todas las sedes
        if ($conn->hasTable('dias_feriados')) {
            $existeFeriado12 = DB::connection('tenant')
                ->table('dias_feriados')
                ->whereDate('fecha_inicio', '<=', '2026-10-12')
                ->whereDate('fecha_fin', '>=', '2026-10-12')
                ->exists();

            if (!$existeFeriado12) {
                DB::connection('tenant')->table('dias_feriados')->insert([
                    'fecha_inicio' => '2026-10-12',
                    'fecha_fin' => '2026-10-12',
                    'nombre' => 'Encuentro de Dos Mundos',
                    'descripcion' => 'Feriado legal',
                    'tipo' => 'feriado',
                    'activo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                DB::connection('tenant')
                    ->table('dias_feriados')
                    ->whereDate('fecha_inicio', '<=', '2026-10-12')
                    ->whereDate('fecha_fin', '>=', '2026-10-12')
                    ->update(['activo' => true, 'updated_at' => now()]);
            }

            $existeSemanaAjuste = DB::connection('tenant')
                ->table('dias_feriados')
                ->whereDate('fecha_inicio', '<=', '2026-10-13')
                ->whereDate('fecha_fin', '>=', '2026-10-16')
                ->exists();

            if (!$existeSemanaAjuste) {
                DB::connection('tenant')->table('dias_feriados')->insert([
                    'fecha_inicio' => '2026-10-13',
                    'fecha_fin' => '2026-10-17',
                    'nombre' => 'Semana Ajuste Académico',
                    'descripcion' => 'Semana de receso / ajuste académico con horario flexible',
                    'tipo' => 'feriado',
                    'activo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                DB::connection('tenant')
                    ->table('dias_feriados')
                    ->whereDate('fecha_inicio', '<=', '2026-10-13')
                    ->whereDate('fecha_fin', '>=', '2026-10-16')
                    ->update(['activo' => true, 'updated_at' => now()]);
            }
        }

        // 2. Limpiar registros de clases_no_realizadas con estado 'no_realizada' generados durante la semana de receso
        if ($conn->hasTable('clases_no_realizadas')) {
            DB::connection('tenant')
                ->table('clases_no_realizadas')
                ->whereBetween('fecha_clase', ['2026-10-12', '2026-10-17'])
                ->where('estado', 'no_realizada')
                ->delete();
        }

        // 3. Limpiar snapshots antiguos de ocupación semanal para la semana del 2026-10-12
        if ($conn->hasTable('ocupacion_semanal_snapshots')) {
            DB::connection('tenant')
                ->table('ocupacion_semanal_snapshots')
                ->whereDate('semana_inicio', '2026-10-12')
                ->delete();
        }
    }

    public function down(): void
    {
        // No revertir datos de sincronización
    }
};
