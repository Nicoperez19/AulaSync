<?php

use App\Models\ClaseNoRealizada;
use App\Models\Planificacion_Asignatura;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

// This script fixes the run_profesor in clases_no_realizadas table
// by pulling the correct professor from the horario associated with the planning.

$tenants = Tenant::all();

foreach ($tenants as $tenant) {
    echo "Processing tenant: {$tenant->name}\n";
    
    // Switch to tenant DB
    Config::set('database.connections.tenant.database', $tenant->database);
    DB::purge('tenant');
    
    $registros = DB::connection('tenant')->table('clases_no_realizadas')->get();
    $updatedCount = 0;
    
    foreach ($registros as $registro) {
        // Take the first module from the potentially comma-separated list
        $modulos = explode(',', $registro->id_modulo);
        $primerModulo = trim($modulos[0]);
        
        // Find the planning for this record using the first module
        $planificacion = DB::connection('tenant')->table('planificacion_asignaturas')
            ->join('horarios', 'planificacion_asignaturas.id_horario', '=', 'horarios.id_horario')
            ->where('planificacion_asignaturas.id_asignatura', $registro->id_asignatura)
            ->where('planificacion_asignaturas.id_espacio', $registro->id_espacio)
            ->where('planificacion_asignaturas.id_modulo', $primerModulo)
            ->select('horarios.run_profesor')
            ->first();
            
        if ($planificacion && $planificacion->run_profesor && $planificacion->run_profesor !== $registro->run_profesor) {
            echo "  Updating record {$registro->id}: {$registro->run_profesor} -> {$planificacion->run_profesor} (Asignatura: {$registro->id_asignatura}, Modulo: {$primerModulo})\n";
            DB::connection('tenant')->table('clases_no_realizadas')
                ->where('id', $registro->id)
                ->update(['run_profesor' => $planificacion->run_profesor]);
            $updatedCount++;
        }
    }
    
    echo "  Finished. Updated $updatedCount records.\n";
}
