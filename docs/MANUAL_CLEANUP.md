# Mantenimiento de Datos: Limpieza de Horarios

Este documento describe los métodos para realizar una limpieza manual de los horarios y planificaciones cargados en el sistema. Esto es útil en caso de que una carga masiva haya fallado o se desee reiniciar un semestre desde cero.

## 1. Comando Artisan (Recomendado)

Se ha creado un comando personalizado para facilitar la limpieza de un período específico.

### Uso:
```bash
php artisan schedules:clear {periodo}
```

### Ejemplo:
Para limpiar el primer semestre de 2026:
```bash
php artisan schedules:clear 2026-1
```

El comando pedirá una confirmación antes de proceder.

---

## 2. Comando vía Tinker (Manual)

Si no se desea usar el comando Artisan, se puede ejecutar directamente en `php artisan tinker`:

```php
$periodo = "2026-1";
$ids = App\Models\Horario::where("periodo", $periodo)->pluck("id_horario");
App\Models\Planificacion_Asignatura::whereIn("id_horario", $ids)->delete();
App\Models\Horario::where("periodo", $periodo)->delete();
```

---

## 3. Limpieza Automática durante la Carga

El controlador `DataLoadController` realiza esta limpieza automáticamente cada vez que se inicia una carga masiva para un período seleccionado. Por lo tanto, **no es estrictamente necesario ejecutar estos comandos manualmente** antes de subir un nuevo archivo Excel, ya que el sistema lo hará por ti para garantizar una carga limpia.

---

## 4. Limpieza de Días Feriados (Multitenant)

Si deseas vaciar por completo la tabla de feriados (`dias_feriados`) en todas las bases de datos de los tenants (por ejemplo, para realizar una carga limpia desde la API de feriados sin registros duplicados o antiguos), puedes ejecutar el siguiente script a través de Laravel Tinker.

### Pasos:
1. Abre la consola interactiva de Laravel:
   ```bash
   php artisan tinker
   ```
2. Pega y ejecuta el siguiente código:
   ```php
   foreach (\App\Models\Tenant::all() as $tenant) {
       // Configurar la conexión para el tenant actual
       config(['database.connections.tenant.database' => $tenant->database]);
       app('db')->purge('tenant');
       
       // Vaciar la tabla dias_feriados
       \DB::connection('tenant')->table('dias_feriados')->truncate();
       
       echo "✓ Tabla de feriados vaciada para: {$tenant->name}\n";
   }
   ```
   
> [!IMPORTANT]
> Este proceso vacía por completo los feriados en todos los inquilinos de la aplicación. Una vez ejecutado, deberás ir al panel de administración o usar el botón de importación para recargar los días feriados correspondientes utilizando la API de feriados de Chile.

