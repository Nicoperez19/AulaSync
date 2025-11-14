<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Espacio;
use App\Models\Reserva;
use App\Models\Profesor;
use Illuminate\Support\Facades\Log;

echo "=== VERIFICANDO ENDPOINT INFORMACIÓN DETALLADA ===\n\n";

$espacioId = 'TH-LAB';

echo "Consultando espacio: {$espacioId}\n\n";

// Simular la lógica del controlador
$espacio = Espacio::where('id_espacio', $espacioId)->first();

if (!$espacio) {
    echo "❌ ERROR: Espacio no encontrado\n";
    exit(1);
}

echo "✅ Espacio encontrado:\n";
echo "   ID: {$espacio->id_espacio}\n";
echo "   Nombre: {$espacio->nombre_espacio}\n";
echo "   Estado: {$espacio->estado}\n\n";

$horaActual = now()->format('H:i:s');
$fechaActual = now()->format('Y-m-d');

echo "Fecha actual: {$fechaActual}\n";
echo "Hora actual: {$horaActual}\n\n";

if (in_array($espacio->estado, ['Ocupado', 'ocupado'])) {
    echo "🔍 Buscando reserva activa...\n";
    
    $reservaActiva = Reserva::where('id_espacio', $espacioId)
        ->where('fecha_reserva', $fechaActual)
        ->where('estado', 'activa')
        ->first();
    
    if ($reservaActiva) {
        echo "✅ Reserva activa encontrada:\n";
        echo "   ID: {$reservaActiva->id_reserva}\n";
        echo "   Estado: {$reservaActiva->estado}\n";
        echo "   Tipo: {$reservaActiva->tipo_reserva}\n";
        echo "   Hora inicio: {$reservaActiva->hora}\n";
        echo "   Hora salida: {$reservaActiva->hora_salida}\n";
        echo "   Run profesor: {$reservaActiva->run_profesor}\n";
        echo "   Run solicitante: {$reservaActiva->run_solicitante}\n\n";
        
        if ($reservaActiva->run_profesor) {
            echo "🔍 Buscando información del profesor...\n";
            $profesor = Profesor::where('run_profesor', $reservaActiva->run_profesor)->first();
            
            if ($profesor) {
                echo "✅ Profesor encontrado:\n";
                echo "   Nombre: {$profesor->name}\n";
                echo "   RUN: {$profesor->run_profesor}\n";
            } else {
                echo "❌ Profesor NO encontrado en la BD\n";
                echo "   Verificando si existe en tabla profesors...\n";
                $count = \DB::table('profesors')->where('run_profesor', $reservaActiva->run_profesor)->count();
                echo "   Cantidad de registros: {$count}\n";
            }
        }
    } else {
        echo "⚠️  No se encontró reserva activa\n";
    }
} else {
    echo "ℹ️  El espacio está disponible\n";
}

echo "\n✅ Verificación completada\n";
