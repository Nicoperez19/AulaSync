<?php
require __DIR__ . '/vendor/autoload.php';
use Carbon\Carbon;

$horaSalidaReal = Carbon::parse('12:49:09');
$horaMinimaSalida = Carbon::parse('12:50:00');

$minutosAntes = $horaSalidaReal->diffInMinutes($horaMinimaSalida);
echo "diffInMinutes(absolute=true): " . $minutosAntes . "\n";

$minutosAntesAbsFalse = $horaSalidaReal->diffInMinutes($horaMinimaSalida, false);
echo "diffInMinutes(absolute=false): " . $minutosAntesAbsFalse . "\n";

$diferenciaSegundos = $horaSalidaReal->diffInSeconds($horaMinimaSalida);
echo "diffInSeconds: " . $diferenciaSegundos . "\n";

$minutosAntesConSegundos = ceil($diferenciaSegundos / 60);
echo "minutosAntesConSegundos (ceil): " . $minutosAntesConSegundos . "\n";
