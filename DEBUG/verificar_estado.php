<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Espacio;
use App\Models\Reserva;

echo "=== VERIFICACIÓN DEL ESTADO ===\n\n";

$espacio = Espacio::find('TH-LAB');
echo "Espacio TH-LAB:\n";
echo "  - Estado: {$espacio->estado}\n";
echo "  - Nombre: {$espacio->nombre_espacio}\n\n";

$reserva = Reserva::where('id_espacio', 'TH-LAB')
    ->where('estado', 'activa')
    ->first();

if ($reserva) {
    echo "Reserva activa:\n";
    echo "  - ID: {$reserva->id_reserva}\n";
    echo "  - Hora: {$reserva->hora}\n";
    echo "  - Fecha: {$reserva->fecha_reserva}\n";
    echo "  - Módulos: {$reserva->modulos}\n";
    echo "  - Estado: {$reserva->estado}\n";
    echo "  - Tipo: {$reserva->tipo_reserva}\n";
} else {
    echo "No hay reservas activas para TH-LAB\n";
}

echo "\n✅ Verificación completada\n";
