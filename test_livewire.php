<?php

// Archivo de prueba temporal para verificar Livewire
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';

use App\Models\ClaseNoRealizada;

// Verificar que los datos existen
$clases = ClaseNoRealizada::with('profesor')->get();
echo "Total clases: " . $clases->count() . PHP_EOL;

foreach($clases as $clase) {
    echo "ID: {$clase->id}, Profesor: " . ($clase->profesor->name ?? 'N/A') . ", Estado: {$clase->estado}" . PHP_EOL;
}
