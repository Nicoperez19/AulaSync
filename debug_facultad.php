<?php
// Script rápido para ver qué facultad tiene TH-03
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Espacio, App\Models\Tenant;

// Cambiar a tenant Talcahuano (ID=5)
$tenant = Tenant::find(5);
if ($tenant) {
    $tenant->makeCurrent();
    
    $espacio = Espacio::find('TH-03');
    if ($espacio) {
        echo "Espacio: TH-03\n";
        echo "Piso ID: " . $espacio->piso_id . "\n";
        echo "ID Facultad del Piso: " . ($espacio->piso->id_facultad ?? 'NULL') . "\n";
        echo "Nombre Facultad: " . ($espacio->piso->facultad->nombre_facultad ?? 'NULL') . "\n";
    } else {
        echo "No encontró TH-03\n";
    }
    
    // Ver todas las facultades
    echo "\n=== FACULTADES ===\n";
    $facultades = \DB::table('facultads')->get();
    foreach ($facultades as $fac) {
        echo "ID: " . $fac->id_facultad . " | Nombre: " . $fac->nombre_facultad . "\n";
    }
} else {
    echo "No encontró tenant 5\n";
}
