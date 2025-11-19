<?php

use App\Livewire\ModulosActualesTable;
use App\Models\Modulo;
use Carbon\Carbon;

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "═════════════════════════════════════════════════════════════\n";
echo "DEBUG: LIVEWIRE MODULOS ACTUALES\n";
echo "═════════════════════════════════════════════════════════════\n\n";

// Instanciar el componente Livewire
$component = new ModulosActualesTable();

// Ejecutar mount
$component->mount();

echo "Estado del componente después de mount():\n";
echo "- Hora actual: {$component->horaActual}\n";
echo "- Fecha actual: {$component->fechaActual}\n";
echo "- Módulo actual: " . json_encode($component->moduloActual) . "\n";
echo "- Es feriado: " . ($component->esFeriado ? 'SÍ' : 'NO') . "\n";
echo "- Total pisos: " . count($component->pisos) . "\n";
echo "- Total espacios: " . count($component->getTodosLosEspacios()) . "\n\n";

if (count($component->getTodosLosEspacios()) > 0) {
    echo "Primeros 3 espacios:\n";
    $espacios = array_slice($component->getTodosLosEspacios(), 0, 3);
    foreach ($espacios as $idx => $espacio) {
        echo "\n  [{$idx}] Espacio: {$espacio['id_espacio']}\n";
        echo "      Estado: {$espacio['estado']}\n";
        echo "      Tiene clase: " . ($espacio['tiene_clase'] ? 'SÍ' : 'NO') . "\n";
        if ($espacio['tiene_clase'] && !empty($espacio['datos_clase'])) {
            echo "      - Asignatura: {$espacio['datos_clase']['nombre_asignatura']}\n";
            echo "      - Es colaborador: " . ($espacio['datos_clase']['es_colaborador'] ? 'SÍ' : 'NO') . "\n";
            echo "      - Profesor: {$espacio['datos_clase']['profesor']['name']}\n";
        }
    }
} else {
    echo "⚠ NO HAY ESPACIOS\n";
}

echo "\n═════════════════════════════════════════════════════════════\n";
