#!/bin/bash
# Script de Testing: Validar implementación de período de gracia para devolución de llaves

echo "=================================="
echo "TESTING: Período de Gracia - Devolución de Llaves"
echo "=================================="
echo ""

# 1. Verificar que el comando existe
echo "1️⃣  Verificando que el comando esté registrado..."
php artisan list | grep "finalizar-no-devueltas" > /dev/null
if [ $? -eq 0 ]; then
    echo "✅ Comando encontrado en lista"
else
    echo "❌ Comando NO encontrado"
    exit 1
fi

# 2. Ver el schedule
echo ""
echo "2️⃣  Verificando configuración en scheduler..."
php artisan schedule:list | grep "finalizar-no-devueltas"
if [ $? -eq 0 ]; then
    echo "✅ Comando registrado en scheduler"
else
    echo "❌ Comando NO en scheduler"
    exit 1
fi

# 3. Ejecutar comando manualmente
echo ""
echo "3️⃣  Ejecutando comando manualmente..."
php artisan reservas:finalizar-no-devueltas

# 4. Verificar logs
echo ""
echo "4️⃣  Verificando archivo de logs..."
if [ -f "storage/logs/reservas-no-devueltas.log" ]; then
    echo "✅ Archivo de logs existe"
    echo "📋 Últimas 5 líneas:"
    tail -5 "storage/logs/reservas-no-devueltas.log"
else
    echo "⚠️  Archivo de logs no existe aún (se creará en primer uso)"
fi

# 5. Contar reservas activas
echo ""
echo "5️⃣  Estadísticas de base de datos..."
php artisan tinker --execute="
\$activas = App\Models\Reserva::where('estado', 'activa')->whereNotNull('run_profesor')->whereNull('hora_salida')->count();
\$finalizadas = App\Models\Reserva::where('estado', 'finalizada')->whereNotNull('run_profesor')->whereNotNull('hora_salida')->count();
echo 'Reservas activas sin devolver llave: ' . \$activas . PHP_EOL;
echo 'Reservas finalizadas: ' . \$finalizadas . PHP_EOL;
"

echo ""
echo "=================================="
echo "✅ TESTING COMPLETADO"
echo "=================================="
echo ""
echo "Para ejecutar el scheduler y ver acciones en tiempo real:"
echo "  php artisan schedule:work"
echo ""
echo "Para monitorear los logs:"
echo "  tail -f storage/logs/reservas-no-devueltas.log"
