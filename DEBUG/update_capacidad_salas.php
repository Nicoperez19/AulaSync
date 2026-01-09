<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Actualizar capacidad de las salas de estudio
$updates = [
    'TH-C1' => 4,
    'TH-C2' => 4,
    'TH-C3' => 4,
    'TH-C4' => 4,
    'TH-C5' => 6,
];

foreach ($updates as $id_espacio => $capacidad) {
    DB::table('espacios')
        ->where('id_espacio', $id_espacio)
        ->update(['capacidad_maxima' => $capacidad]);
    
    echo "✅ Sala $id_espacio actualizada con capacidad: $capacidad\n";
}

echo "\n🎉 Todas las salas de estudio actualizadas correctamente!\n";
