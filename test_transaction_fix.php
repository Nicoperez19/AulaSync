<?php
/**
 * Test script para verificar que la transacción de creación de reserva funciona
 * 
 * Este script:
 * 1. Obtiene un tenant activo
 * 2. Obtiene un solicitante activo en ese tenant
 * 3. Obtiene un espacio disponible en ese tenant
 * 4. Intenta crear una reserva
 * 5. Verifica que la reserva se guardó correctamente en BD
 */

require __DIR__ . '/vendor/autoload.php';

// Cargar Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Tenant;
use App\Models\Solicitante;
use App\Models\Espacio;
use App\Models\Reserva;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;

echo "=== TEST DE TRANSACCIÓN DE RESERVA ===\n\n";

try {
    // 1. Obtener primer tenant
    $tenant = Tenant::first();
    if (!$tenant) {
        echo "❌ No se encontraron tenants en la BD\n";
        exit(1);
    }
    echo "✅ Tenant encontrado: " . $tenant->name . " (ID: " . $tenant->id . ")\n\n";

    // 2. Configurar contexto del tenant
    Config::set('database.connections.tenant.database', $tenant->database);
    DB::purge('tenant');
    
    // 3. Obtener un solicitante en el tenant
    $solicitante = Solicitante::on('tenant')->where('activo', true)->first();
    if (!$solicitante) {
        echo "❌ No se encontraron solicitantes activos en el tenant\n";
        exit(1);
    }
    echo "✅ Solicitante encontrado: " . $solicitante->nombre . " (RUN: " . $solicitante->run_solicitante . ")\n\n";

    // 4. Obtener un espacio disponible
    $espacio = Espacio::on('tenant')->where('estado', 'Disponible')->first();
    if (!$espacio) {
        echo "❌ No se encontraron espacios disponibles en el tenant\n";
        exit(1);
    }
    echo "✅ Espacio encontrado: " . $espacio->nombre_espacio . " (ID: " . $espacio->id_espacio . ")\n\n";

    // 5. Crear una reserva usando transacción
    echo "Iniciando creación de reserva...\n";
    
    $reservaId = null;
    
    $respuesta = DB::connection('tenant')->transaction(function () use ($solicitante, $espacio, &$reservaId) {
        echo "  - Dentro de la transacción\n";
        
        $reserva = Reserva::on('tenant')->create([
            'id_reserva' => Reserva::generarIdUnico(),
            'hora' => '08:00:00',
            'fecha_reserva' => Carbon::today()->toDateString(),
            'id_espacio' => $espacio->id_espacio,
            'run_solicitante' => $solicitante->run_solicitante,
            'run_profesor' => null,
            'tipo_reserva' => 'espontanea',
            'estado' => 'activa',
            'hora_salida' => '09:30:00'
        ]);
        
        echo "  - Reserva creada en PHP: " . $reserva->id_reserva . "\n";
        $reservaId = $reserva->id_reserva;
        
        return [
            'success' => true,
            'id_reserva' => $reserva->id_reserva
        ];
    });
    
    echo "✅ Transacción completada\n\n";

    // 6. Verificar que la reserva se guardó en BD
    echo "Verificando que la reserva se guardó en BD...\n";
    $reservaEnBD = Reserva::on('tenant')->where('id_reserva', $reservaId)->first();
    
    if ($reservaEnBD) {
        echo "✅ ÉXITO: Reserva encontrada en BD\n";
        echo "   - ID: " . $reservaEnBD->id_reserva . "\n";
        echo "   - Estado: " . $reservaEnBD->estado . "\n";
        echo "   - Solicitante: " . $reservaEnBD->run_solicitante . "\n";
        echo "   - Espacio: " . $reservaEnBD->id_espacio . "\n";
        echo "   - Hora: " . $reservaEnBD->hora . " - " . $reservaEnBD->hora_salida . "\n";
    } else {
        echo "❌ FALLO: Reserva NO se encontró en BD después de la transacción\n";
        exit(1);
    }

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack: " . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n=== TEST COMPLETADO EXITOSAMENTE ===\n";
exit(0);
