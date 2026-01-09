# Script de Testing: Validar implementación de período de gracia para devolución de llaves
# Ejecutar con: powershell -ExecutionPolicy Bypass -File test_grace_period.ps1

Write-Host "==================================" -ForegroundColor Cyan
Write-Host "TESTING: Período de Gracia - Devolución de Llaves" -ForegroundColor Cyan
Write-Host "==================================" -ForegroundColor Cyan
Write-Host ""

# 1. Verificar que el comando existe
Write-Host "1️⃣  Verificando que el comando esté registrado..." -ForegroundColor Yellow
$commandList = & php artisan list 2>&1 | Select-String "finalizar-no-devueltas"
if ($commandList) {
    Write-Host "✅ Comando encontrado en lista" -ForegroundColor Green
}
else {
    Write-Host "❌ Comando NO encontrado" -ForegroundColor Red
    exit 1
}

# 2. Ver el schedule
Write-Host ""
Write-Host "2️⃣  Verificando configuración en scheduler..." -ForegroundColor Yellow
$scheduleList = & php artisan schedule:list 2>&1 | Select-String "finalizar-no-devueltas"
if ($scheduleList) {
    Write-Host "✅ Comando registrado en scheduler" -ForegroundColor Green
    Write-Host $scheduleList
}
else {
    Write-Host "❌ Comando NO en scheduler" -ForegroundColor Red
    exit 1
}

# 3. Ejecutar comando manualmente
Write-Host ""
Write-Host "3️⃣  Ejecutando comando manualmente..." -ForegroundColor Yellow
& php artisan reservas:finalizar-no-devueltas
Write-Host ""

# 4. Verificar logs
Write-Host "4️⃣  Verificando archivo de logs..." -ForegroundColor Yellow
$logPath = "storage/logs/reservas-no-devueltas.log"
if (Test-Path $logPath) {
    Write-Host "✅ Archivo de logs existe" -ForegroundColor Green
    Write-Host "📋 Últimas 5 líneas:" -ForegroundColor Cyan
    Get-Content $logPath -Tail 5
}
else {
    Write-Host "⚠️  Archivo de logs no existe aún (se creará en primer uso)" -ForegroundColor Yellow
}

# 5. Contar reservas activas
Write-Host ""
Write-Host "5️⃣  Estadísticas de base de datos..." -ForegroundColor Yellow
$tinkerScript = @'
$activas = App\Models\Reserva::where('estado', 'activa')->whereNotNull('run_profesor')->whereNull('hora_salida')->count();
$finalizadas = App\Models\Reserva::where('estado', 'finalizada')->whereNotNull('run_profesor')->whereNotNull('hora_salida')->count();
echo 'Reservas activas sin devolver llave: ' . $activas . PHP_EOL;
echo 'Reservas finalizadas: ' . $finalizadas . PHP_EOL;
'@

& php artisan tinker --execute=$tinkerScript
Write-Host ""

Write-Host "==================================" -ForegroundColor Cyan
Write-Host "✅ TESTING COMPLETADO" -ForegroundColor Green
Write-Host "==================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Para ejecutar el scheduler y ver acciones en tiempo real:" -ForegroundColor Cyan
Write-Host "  php artisan schedule:work" -ForegroundColor White
Write-Host ""
Write-Host "Para monitorear los logs:" -ForegroundColor Cyan
Write-Host "  Get-Content -Path storage/logs/reservas-no-devueltas.log -Tail 20 -Wait" -ForegroundColor White
Write-Host ""
