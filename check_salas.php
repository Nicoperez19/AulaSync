<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$salas = DB::table('espacios')
    ->where('tipo_espacio', 'Sala de Estudio')
    ->select('id_espacio', 'nombre_espacio', 'piso_id', 'capacidad_maxima')
    ->get();

echo "=== SALAS DE ESTUDIO ENCONTRADAS ===\n\n";

if ($salas->isEmpty()) {
    echo "No hay salas de estudio en la base de datos.\n";
} else {
    foreach ($salas as $sala) {
        echo "ID: {$sala->id_espacio}\n";
        echo "Nombre: {$sala->nombre_espacio}\n";
        echo "Piso ID: {$sala->piso_id}\n";
        echo "Capacidad: {$sala->capacidad_maxima}\n";
        echo "---\n";
    }
}
