<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Modulo;
use Carbon\Carbon;

echo "=== Actualizando hora_programada y minutos_atraso en profesor_atrasos ===\n\n";

// Obtener todos los módulos con sus horas de inicio
$modulos = Modulo::all()->keyBy('id_modulo');

echo "Módulos cargados: " . $modulos->count() . "\n\n";

// Obtener registros de profesor_atrasos que tienen hora_programada NULL
$atrasos = DB::table('profesor_atrasos')
    ->whereNull('hora_programada')
    ->orWhere('minutos_atraso', 0)
    ->get();

echo "Registros a actualizar: " . $atrasos->count() . "\n\n";

$actualizados = 0;
$errores = 0;

foreach ($atrasos as $atraso) {
    echo "Procesando ID {$atraso->id}...\n";
    
    // Obtener el primer módulo (el que determina la hora de inicio)
    $modulosStr = $atraso->id_modulo;
    $primerModuloId = null;
    
    if (!empty($modulosStr)) {
        $modulosArray = explode(',', $modulosStr);
        $primerModuloId = trim($modulosArray[0]);
    }
    
    echo "  Primer módulo: {$primerModuloId}\n";
    
    $horaProgramada = null;
    
    if ($primerModuloId && isset($modulos[$primerModuloId])) {
        $horaProgramada = $modulos[$primerModuloId]->hora_inicio;
        echo "  Hora programada: {$horaProgramada}\n";
    } else {
        echo "  ⚠️  No se encontró el módulo {$primerModuloId}\n";
    }
    
    // Calcular minutos de atraso
    $minutosAtraso = 0;
    if ($horaProgramada && $atraso->hora_llegada) {
        $programada = Carbon::parse($horaProgramada);
        $llegada = Carbon::parse($atraso->hora_llegada);
        
        // Si llegó después de la hora programada
        if ($llegada->gt($programada)) {
            $minutosAtraso = $programada->diffInMinutes($llegada);
        }
        echo "  Hora llegada: {$atraso->hora_llegada}\n";
        echo "  Minutos atraso: {$minutosAtraso}\n";
    }
    
    // Actualizar el registro
    try {
        DB::table('profesor_atrasos')
            ->where('id', $atraso->id)
            ->update([
                'hora_programada' => $horaProgramada,
                'minutos_atraso' => $minutosAtraso,
                'updated_at' => now(),
            ]);
        
        $actualizados++;
        echo "  ✅ Actualizado\n";
    } catch (\Exception $e) {
        $errores++;
        echo "  ❌ Error: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "=== Resumen ===\n";
echo "Actualizados: {$actualizados}\n";
echo "Errores: {$errores}\n";
