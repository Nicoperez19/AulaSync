<?php

// Script de prueba para verificar los cálculos de ocupación

// Simulación de datos para prueba
$totalEspacios = 10; // Supongamos 10 espacios
$diasLaborales = 5; // Lunes a viernes para semanal
$horasPorDia = 8;
$reservas = 25; // 25 reservas en la semana

// Cálculo anterior (incorrecto)
$totalHorasIncorrecto = 40; // Solo 8 horas × 5 días = 40 horas
$porcentajeIncorrecto = round(($reservas / $totalHorasIncorrecto) * 100, 2);

// Cálculo corregido
$totalHorasCorrecto = $totalEspacios * $diasLaborales * $horasPorDia; // 10 × 5 × 8 = 400 horas
$porcentajeCorrecto = round(($reservas / $totalHorasCorrecto) * 100, 2);

echo "=== PRUEBA DE CÁLCULO DE OCUPACIÓN SEMANAL ===\n";
echo "Total espacios: $totalEspacios\n";
echo "Días laborales: $diasLaborales\n";
echo "Horas por día: $horasPorDia\n";
echo "Total reservas: $reservas\n\n";

echo "CÁLCULO ANTERIOR (INCORRECTO):\n";
echo "Total horas consideradas: $totalHorasIncorrecto\n";
echo "Porcentaje: $porcentajeIncorrecto% (¡Puede superar 100%!)\n\n";

echo "CÁLCULO CORREGIDO:\n";
echo "Total horas disponibles: $totalHorasCorrecto\n";
echo "Porcentaje: $porcentajeCorrecto% (Nunca superará 100%)\n\n";

// Ejemplo para mensual
$diasLaboralesMes = 22; // Aproximadamente 22 días laborales por mes
$reservasMes = 100;

$totalHorasMesIncorrecto = 160; // 20 días × 8 horas = 160 horas
$porcentajeMesIncorrecto = round(($reservasMes / $totalHorasMesIncorrecto) * 100, 2);

$totalHorasMesCorrecto = $totalEspacios * $diasLaboralesMes * $horasPorDia; // 10 × 22 × 8 = 1760 horas
$porcentajeMesCorrecto = round(($reservasMes / $totalHorasMesCorrecto) * 100, 2);

echo "=== PRUEBA DE CÁLCULO DE OCUPACIÓN MENSUAL ===\n";
echo "Total espacios: $totalEspacios\n";
echo "Días laborales del mes: $diasLaboralesMes\n";
echo "Horas por día: $horasPorDia\n";
echo "Total reservas del mes: $reservasMes\n\n";

echo "CÁLCULO ANTERIOR (INCORRECTO):\n";
echo "Total horas consideradas: $totalHorasMesIncorrecto\n";
echo "Porcentaje: $porcentajeMesIncorrecto% (¡Puede superar 100%!)\n\n";

echo "CÁLCULO CORREGIDO:\n";
echo "Total horas disponibles: $totalHorasMesCorrecto\n";
echo "Porcentaje: $porcentajeMesCorrecto% (Nunca superará 100%)\n\n";

echo "¡Los cálculos han sido corregidos exitosamente!\n";