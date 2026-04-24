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
