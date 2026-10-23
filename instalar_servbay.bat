@echo off
chcp 65001 >nul
cd /d "%~dp0"
echo ==============================================
echo   AulaSync - instalacion para ServBay
echo ==============================================

if exist .env copy /Y .env .env.backup >nul
copy /Y .env.servbay .env >nul
echo [1/7] .env configurado para ServBay (respaldo en .env.backup)

if not exist vendor (
    echo [2/7] Instalando dependencias PHP...
    call composer install --no-interaction
) else (
    echo [2/7] Dependencias PHP ya instaladas
)

echo [3/7] Creando bases de datos...
php servbay_crear_bases.php
if errorlevel 1 goto error

php artisan config:clear
echo [4/7] Migrando base central...
php artisan migrate --path=database/migrations/central --force
if errorlevel 1 goto error

echo [5/7] Cargando datos centrales (usuarios, roles, sedes)...
php artisan db:seed --class=CentralDatabaseSeeder --force
if errorlevel 1 goto error

echo [6/7] Configurando las 5 sedes (migraciones + datos)...
echo y| php artisan tenants:setup --fresh --seed
if errorlevel 1 goto error

echo [7/7] Compilando estilos (Tailwind)...
if not exist node_modules call npm install
call npm run build
php artisan view:clear
php artisan cache:clear

echo.
echo ==============================================
echo   LISTO. Abre https://aulasync.local.cl
echo   Usuario: 19716146   Contrasena: password
echo ==============================================
pause
exit /b 0

:error
echo.
echo [ERROR] Algo fallo. Revisa el mensaje de arriba.
pause
exit /b 1
