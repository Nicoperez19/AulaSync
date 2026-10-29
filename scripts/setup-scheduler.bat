@echo off
:: Debe ejecutarse como Administrador
echo Creando tarea programada para Laravel Scheduler (AulaSync)...
schtasks /create /tn "AulaSync Laravel Scheduler" ^
    /tr "\"C:\xampp\php\php.exe\" \"d:\Proyectos\AulaSync\artisan\" schedule:run >> \"d:\Proyectos\AulaSync\storage\logs\scheduler.log\" 2>&1" ^
    /sc MINUTE /mo 1 ^
    /ru SYSTEM ^
    /f
if %ERRORLEVEL% == 0 (
    echo.
    echo [OK] Tarea creada exitosamente.
    echo      Nombre: "AulaSync Laravel Scheduler"
    echo      Se ejecutara cada minuto usando SYSTEM.
    echo      Los logs van a: d:\Proyectos\AulaSync\storage\logs\scheduler.log
) else (
    echo.
    echo [ERROR] No se pudo crear la tarea. Asegurate de ejecutar como Administrador.
)
pause
