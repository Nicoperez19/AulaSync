# Guía de Solución - Error "Trying to access array offset on value of type null"

## 🚨 Problema
Error que aparece en el servidor (no en desarrollo local): `Trying to access array offset on value of type null` en la línea 79 del archivo `/var/www/AulaSync/resources/views/livewire/modulos-actuales-table.blade.php`.

## 🔍 Causa
El error ocurre porque:
1. En el servidor hay datos `null` o vacíos que no se manejan correctamente
2. Las consultas pueden fallar o retornar resultados diferentes
3. No se validan adecuadamente los arrays antes de acceder a sus elementos

## ✅ Soluciones Implementadas

### 1. **Validaciones en la Vista Blade**
Se agregaron operadores de coalescencia nula (`??`) y validaciones `is_array()`:

```php
<!-- Antes (problemático) -->
{{ $espacio['datos_clase']['nombre_asignatura'] }}

<!-- Después (seguro) -->
{{ $espacio['datos_clase']['nombre_asignatura'] ?? 'Sin asignatura' }}
```

### 2. **Validaciones en el Componente Livewire**
Se agregaron validaciones de valores por defecto en la construcción de arrays:

```php
'datos_clase' => $datosClase ?? null,
'modulo' => [
    'numero' => $this->moduloActual['numero'] ?? '--',
    'inicio' => $this->moduloActual['inicio'] ?? '--:--',
    'fin' => $this->moduloActual['fin'] ?? '--:--'
],
```

### 3. **Try-Catch en Métodos Críticos**
Se agregó manejo de errores en `actualizarDatos()` y `actualizarAutomaticamente()`:

```php
public function actualizarDatos()
{
    try {
        set_time_limit(120);
        // ... lógica principal
    } catch (\Exception $e) {
        Log::error('Error en actualizarDatos: ' . $e->getMessage());
        // Valores por defecto seguros
        $this->espacios = [];
    }
}
```

### 4. **Middleware de Tiempo de Ejecución**
Creado middleware personalizado `ExtendExecutionTime` que:
- Extiende tiempo de ejecución a 180 segundos para rutas Livewire
- Aumenta memoria límite a 512MB
- Aplica configuraciones específicas para rutas problemáticas

### 5. **Optimizaciones de Performance**
- Uso de `keyBy()` para búsquedas O(1) en lugar de O(n)
- Pre-carga de datos para evitar consultas N+1
- Reducción de frecuencia de actualización automática a 60 segundos

## 🛠️ Comandos de Verificación y Reparación

### Verificar Datos Problemáticos
```bash
php artisan app:verificar-datos-tabla
```

### Reparar Automáticamente
```bash
php artisan app:verificar-datos-tabla --fix
```

### Optimizar Base de Datos
```bash
php artisan app:optimize-db
```

## 🚀 Pasos para Aplicar la Solución

### 1. En el Servidor de Producción:
```bash
# Navegar al directorio del proyecto
cd /var/www/AulaSync

# Actualizar archivos (si usas Git)
git pull origin main

# Verificar y reparar datos
php artisan app:verificar-datos-tabla --fix

# Optimizar base de datos
php artisan app:optimize-db

# Limpiar caché
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### 2. Configurar Tareas Programadas (Opcional):
```bash
# Editar crontab
crontab -e

# Agregar estas líneas:
# Optimizar DB cada 6 horas
0 */6 * * * cd /var/www/AulaSync && php artisan app:optimize-db >/dev/null 2>&1

# Verificar datos cada día a las 3 AM
0 3 * * * cd /var/www/AulaSync && php artisan app:verificar-datos-tabla --fix >/dev/null 2>&1
```

## 📝 Archivos Modificados

### Principales:
- `resources/views/livewire/modulos-actuales-table.blade.php` - Validaciones en vista
- `app/Livewire/ModulosActualesTable.php` - Optimizaciones y try-catch
- `routes/web.php` - Aplicación del middleware
- `app/Http/Kernel.php` - Registro del middleware

### Nuevos:
- `app/Http/Middleware/ExtendExecutionTime.php` - Middleware personalizado
- `app/Console/Commands/VerificarDatosTabla.php` - Comando de verificación
- `app/Console/Commands/OptimizeDatabase.php` - Comando de optimización

## 🔧 Configuraciones Adicionales

### Para Servidores Apache (.htaccess)
```apache
# Aumentar tiempo de ejecución para rutas específicas
<LocationMatch "/(modulos-actuales|livewire)">
    php_value max_execution_time 180
    php_value max_input_time 180
    php_value memory_limit 512M
</LocationMatch>
```

### Para Servidores Nginx
```nginx
location ~* ^/(modulos-actuales|livewire) {
    fastcgi_read_timeout 180s;
    fastcgi_send_timeout 180s;
}
```

## 📊 Monitoreo

### Logs a Revisar:
```bash
# Logs de Laravel
tail -f storage/logs/laravel.log

# Logs de errores del servidor web
tail -f /var/log/apache2/error.log  # Apache
tail -f /var/log/nginx/error.log    # Nginx
```

### Comando de Debug:
```bash
# Ver configuración PHP actual
php -i | grep -E "(max_execution_time|memory_limit)"

# Verificar que el middleware esté funcionando
php artisan route:list | grep modulos-actuales
```

## ⚠️ Notas Importantes

1. **Diferencias entre Entornos**: El error aparece solo en servidor porque:
   - Los datos en producción pueden ser diferentes
   - La configuración PHP puede ser más restrictiva
   - El volumen de datos es mayor

2. **Performance**: Las optimizaciones pueden tardar unos minutos en aplicarse completamente la primera vez.

3. **Monitoring**: Se recomienda monitorear los logs después de aplicar los cambios.

## 🆘 Si el Error Persiste

### Verificar:
1. **Configuración PHP**: `php -i | grep max_execution_time`
2. **Permisos**: Archivos y carpetas deben tener permisos correctos
3. **Base de Datos**: Verificar conexiones y datos

### Debug Adicional:
```php
// Agregar temporalmente en ModulosActualesTable.php
Log::info('Datos del espacio:', $espacio);
Log::info('Módulo actual:', $this->moduloActual);
```

### Contacto de Emergencia:
Si el error persiste, revisar los logs específicos y aplicar el modo de mantenimiento:
```bash
php artisan down --message="Mantenimiento en curso"
# ... aplicar correcciones
php artisan up
```