<?php
require_once 'vendor/autoload.php';

// Crear una instancia del componente
$componente = new \App\Livewire\ModulosActualesTable();

// Probar la función
$nombre1 = 'AGÜERO PALMA, JORGE CRISTIAN';
$nombre2 = 'MEDINA AGUAYO, ERNESTO MIJAIL';

echo "Nombre original: " . $nombre1 . PHP_EOL;
echo "Nombre formateado: " . $componente->getNombreCompleto($nombre1) . PHP_EOL . PHP_EOL;

echo "Nombre original: " . $nombre2 . PHP_EOL;
echo "Nombre formateado: " . $componente->getNombreCompleto($nombre2) . PHP_EOL;
