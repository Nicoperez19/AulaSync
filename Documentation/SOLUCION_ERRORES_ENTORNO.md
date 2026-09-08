# Guía de Solución de Errores Comunes de Entorno (XAMPP & Laravel)

Este documento detalla la causa y los procedimientos paso a paso para solucionar dos de los problemas más frecuentes al levantar el entorno de desarrollo local en Windows.

---

## 1. Error de MySQL / MariaDB en XAMPP (Crash & Corrupción de Logs)

### Síntomas
* Al pulsar **Start** en MySQL dentro del panel de XAMPP, el botón se pone rojo/amarillo y se detiene de inmediato.
* El archivo `mysql_error.log` muestra errores como:
  * `Cannot find checkpoint record at LSN (...)`
  * `[ERROR] mysqld.exe: Aria recovery failed. Please run aria_chk -r on all Aria tables and delete all aria_log.######## files`
  * `[ERROR] Plugin 'Aria' registration as a STORAGE ENGINE failed.`
  * `[ERROR] Could not open mysql.plugin table. Some plugins may be not loaded`
  * `InnoDB: Page [...] log sequence number is in the future!`

### Causa
Ocurre cuando la computadora se apaga de golpe, se suspende bruscamente o se cierra el proceso de XAMPP sin haber detenido MySQL previamente. Los archivos de registro de transacciones de **Aria** e **InnoDB** quedan corruptos o desincronizados.

---

### Solución A: Limpieza rápida de logs corruptos (Intentar primero)

1. Ve a la carpeta: `C:\xampp\mysql\data\`
2. **Elimina** únicamente los siguientes archivos (no toques las carpetas ni otros archivos):
   * Todos los que comiencen por `aria_log.` (ej. `aria_log.00000001`).
   * El archivo `aria_log_control`.
   * Los archivos `ib_logfile0` e `ib_logfile1`.
3. Vuelve a XAMPP Control Panel y pulsa **Start** en MySQL.

---

### Solución B: Reparación definitiva de XAMPP (Sin perder tus bases de datos)

Si la Solución A no funciona, aplica el método estándar de recuperación:

1. **Detén MySQL** en el panel de XAMPP.
2. Abre el explorador de Windows y navega a `C:\xampp\mysql\`.
3. Cambia el nombre de la carpeta `data` a `data_vieja`.
4. Haz una copia de la carpeta `backup` y nómbrala `data` *(esto crea una carpeta `data` limpia con la estructura de fábrica)*.
5. Entra a tu carpeta `data_vieja` y copia a la nueva carpeta `data`:
   * Todas las **carpetas de tus bases de datos** personales (como `aulasync` u otros proyectos que tengas).  
     > [!WARNING]
     > **NO copies** las carpetas del sistema: `mysql`, `performance_schema`, ni `phpmyadmin`.
   * El archivo **`ibdata1`** (pégalo en la nueva `data` y confirma el reemplazo).
6. Abre el panel de XAMPP y presiona **Start** en MySQL. Iniciará correctamente y mantendrás todas tus tablas y datos.

---

## 2. Error de Composer / Laravel: `package:discover` & `ViewServiceProvider`

### Síntomas
* Al ejecutar `composer install` o `composer dump-autoload`:
  ```text
  Illuminate\View\Compilers\Compiler::__construct(..., "", "php")
  Script @php artisan package:discover --ansi handling the post-autoload-dump event returned with error code 1
  ```
* Al ejecutar `php artisan`:
  ```text
  Fatal error: Uncaught Error: Failed opening required '.../vendor/autoload.php' in artisan on line 18
  ```

### Causa
1. Al clonar un repositorio limpio de Git, la carpeta `vendor/` no viene incluida y deben instalarse las dependencias (`composer install`).
2. Al terminar de descargar paquetes, Composer ejecuta `php artisan package:discover`.
3. Si la función `realpath(storage_path('framework/views'))` en `config/view.php` se evalúa cuando las carpetas de `storage/framework/` aún no existen físicamente en disco, `realpath()` devuelve `false` (cadena vacía `""`), haciendo fallar el compilador de vistas Blade.

---

### Solución

1. **Crear las carpetas requeridas de almacenamiento:**  
   Ejecuta en PowerShell dentro de la raíz del proyecto:
   ```powershell
   New-Item -ItemType Directory -Force -Path "storage/framework/views", "storage/framework/sessions", "storage/framework/cache/data", "bootstrap/cache"
   ```

2. **Configuración en `config/view.php`:**  
   Asegúrate de que la clave `'compiled'` utilice `storage_path` directamente sin `realpath`:
   ```php
   'compiled' => env(
       'VIEW_COMPILED_PATH',
       storage_path('framework/views')
   ),
   ```

3. **Reconstruir el autoloader:**
   ```bash
   composer dump-autoload
   ```

4. **Correr las migraciones:**
   ```bash
   php artisan migrate:central --fresh
   ```
