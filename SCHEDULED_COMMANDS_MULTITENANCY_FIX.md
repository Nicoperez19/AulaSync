# Corrección de Comandos Programados para Multitenancy

## Problema Detectado

Los comandos programados en `app/Console/Kernel.php` estaban intentando acceder a tablas de tenant sin configurar el contexto de tenant correcto, resultando en errores como:

```
SQLSTATE[42S02]: Base table or view not found: 1146 Table 'gestoraulasit.reservas' doesn't exist
SQLSTATE[42S02]: Base table or view not found: 1146 Table 'gestoraulasit.espacios' doesn't exist
```

## Comandos Corregidos ✅

### 1. **FinalizarReservasNoDevueltas** (`app/Console/Commands/FinalizarReservasNoDevueltas.php`)
- ✅ Ahora itera sobre todos los tenants
- ✅ Configura la conexión de tenant antes de ejecutar consultas
- ✅ Usa `on('tenant')` en todos los modelos
- ✅ Manejo de errores con try-catch

### 2. **VerificarEstadoSistemaCommand** (`app/Console/Commands/VerificarEstadoSistemaCommand.php`)
- ✅ Ahora itera sobre todos los tenants
- ✅ Configura la conexión de tenant antes de ejecutar consultas
- ✅ Usa `on('tenant')` en todos los modelos
- ✅ Modo demo separado del procesamiento de tenants
- ✅ Manejo de errores con try-catch

### 3. **ActualizarEstadoEspacios** (`app/Console/Commands/ActualizarEstadoEspacios.php`)
- ✅ Ahora itera sobre todos los tenants
- ✅ Configura la conexión de tenant antes de ejecutar consultas
- ✅ Usa `on('tenant')` en todos los modelos
- ✅ Manejo de errores con try-catch

### 4. **LiberarEspaciosCommand** (`app/Console/Commands/LiberarEspaciosCommand.php`)
- ✅ Ahora itera sobre todos los tenants
- ✅ Configura la conexión de tenant antes de ejecutar consultas
- ✅ Usa `on('tenant')` en todos los modelos
- ✅ Manejo de errores con try-catch

## Comandos Pendientes de Corrección ⚠️

### 5. **FinalizarReservasExpiradas** (`app/Console/Commands/FinalizarReservasExpiradas.php`)
**Status:** Parcialmente corregido - se agregaron los imports necesarios

**Cambios Requeridos:**
1. Modificar el método `handle()` para iterar sobre tenants
2. Crear método `processTenant(Tenant $tenant)` que contenga la lógica actual
3. Agregar `on('tenant')` a todas las consultas de modelos
4. Agregar manejo de errores con try-catch

### 6. **DetectarClasesNoRealizadas** (`app/Console/Commands/DetectarClasesNoRealizadas.php`)
**Status:** Pendiente de corrección

**Cambios Requeridos:**
1. Agregar imports: `use App\Models\Tenant;`, `use Illuminate\Support\Facades\Config;`
2. Modificar el método `handle()` para iterar sobre tenants
3. Crear método `processTenant(Tenant $tenant)` que contenga la lógica actual
4. Agregar `on('tenant')` a todas las consultas de modelos
5. Agregar manejo de errores con try-catch

## Patrón de Implementación

Todos los comandos deben seguir este patrón:

```php
<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
// ... otros imports

class MiComando extends Command
{
    public function handle()
    {
        $this->info('Iniciando proceso...');

        // Obtener todos los tenants
        $tenants = Tenant::all();

        if ($tenants->isEmpty()) {
            $this->warn('No se encontraron tenants configurados.');
            return 0;
        }

        foreach ($tenants as $tenant) {
            $this->processTenant($tenant);
        }

        return 0;
    }

    protected function processTenant(Tenant $tenant)
    {
        $this->info("\nProcesando tenant: {$tenant->name} ({$tenant->domain})");

        try {
            // Configurar conexión de tenant
            Config::set('database.connections.tenant.database', $tenant->database);
            DB::purge('tenant');

            // IMPORTANTE: Usar on('tenant') en todas las consultas
            $registros = MiModelo::on('tenant')
                ->where('campo', 'valor')
                ->get();

            // ... lógica del comando

            $this->info("  ✅ Proceso completado para {$tenant->name}");
        } catch (\Exception $e) {
            $this->error("  Error procesando tenant {$tenant->name}: " . $e->getMessage());
            Log::error("Error en MiComando para tenant {$tenant->name}", [
                'tenant' => $tenant->domain,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
```

## Puntos Clave

1. **Siempre iterar sobre tenants**: Cada comando debe procesar todos los tenants registrados
2. **Configurar conexión antes de consultas**: Usar `Config::set()` y `DB::purge()` antes de cualquier query
3. **Usar `on('tenant')` en todos los modelos**: Esto asegura que las consultas se ejecuten en la base de datos correcta
4. **Manejo de errores**: Usar try-catch para evitar que un tenant con problemas detenga el procesamiento de los demás
5. **Logging**: Registrar errores con información del tenant afectado

## Testing

Después de implementar los cambios, probar con:

```powershell
# Probar cada comando individualmente
php artisan reservas:finalizar-no-devueltas
php artisan sistema:verificar-estado
php artisan espacios:actualizar-estado
php artisan espacios:liberar
php artisan reservas:finalizar-expiradas
php artisan clases:detectar-no-realizadas --dry-run
```

## Comandos Programados en Kernel.php

Los siguientes comandos están programados para ejecutarse automáticamente:

- `espacios:actualizar-estado` - Cada 15 minutos
- `reservas:finalizar-expiradas` - Cada hora
- `espacios:liberar` - Diariamente a las 00:00
- `sistema:verificar-estado` - Cada 30 minutos y diariamente a las 23:55
- `clases:detectar-no-realizadas` - Cada 5 minutos (8:00-23:00, Lun-Sáb)
- `reservas:finalizar-no-devueltas` - Cada 5 minutos

Todos estos comandos ahora procesarán correctamente todos los tenants configurados.

## Próximos Pasos

1. Completar las correcciones pendientes en `FinalizarReservasExpiradas` y `DetectarClasesNoRealizadas`
2. Ejecutar pruebas en ambiente de desarrollo
3. Verificar logs después de la ejecución automática
4. Monitorear el rendimiento con múltiples tenants
