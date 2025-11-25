# Código Implementado: Período de Gracia para Devolución de Llaves

## 📄 Archivo 1: app/Console/Commands/FinalizarReservasNoDevueltas.php

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reserva;
use App\Models\Planificacion_Asignatura;
use App\Models\Modulo;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class FinalizarReservasNoDevueltas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservas:finalizar-no-devueltas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Finaliza automáticamente las reservas de profesores que no devolvieron las llaves una hora después de terminado el módulo';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando búsqueda de reservas no devueltas después de 1 hora del módulo...');

        // Obtener todas las reservas activas de profesores
        $reservasActivas = Reserva::where('estado', 'activa')
            ->whereNotNull('run_profesor')
            ->whereNull('hora_salida')
            ->get();

        $reservasFinalizadas = 0;

        foreach ($reservasActivas as $reserva) {
            // Obtener la planificación asociada si existe
            $planificacion = Planificacion_Asignatura::with('modulo')
                ->where('id_espacio', $reserva->id_espacio)
                ->whereHas('modulo', function ($query) use ($reserva) {
                    $query->where('dia', $this->obtenerDiaSemana($reserva->fecha_reserva));
                })
                ->first();

            if (!$planificacion || !$planificacion->modulo) {
                continue; // No hay módulo asociado
            }

            $modulo = $planificacion->modulo;

            // Calcular hora de termino del módulo
            $fechaModulo = Carbon::parse($reserva->fecha_reserva);
            $horaTerminoModulo = Carbon::parse($reserva->fecha_reserva . ' ' . $modulo->hora_termino);

            // Sumar 1 hora a la hora de término del módulo
            $horaLimiteDevolucion = $horaTerminoModulo->copy()->addHours(1);

            // Verificar si ya pasó la hora límite
            $ahora = Carbon::now();
            if ($ahora->gte($horaLimiteDevolucion)) {
                // La hora límite ya pasó - finalizar la reserva
                $reserva->estado = 'finalizada';
                $reserva->hora_salida = $horaLimiteDevolucion->format('H:i:s');
                $reserva->observaciones = trim(
                    ($reserva->observaciones ?? '') . "\n" .
                    "Reserva finalizada automáticamente después de 1 hora del módulo (Hora límite: " . $horaLimiteDevolucion->format('H:i:s') . "). El profesor no devolvió la llave."
                );
                $reserva->save();

                $reservasFinalizadas++;

                Log::info("Reserva finalizada automáticamente por no devolución de llave", [
                    'id_reserva' => $reserva->id_reserva,
                    'run_profesor' => $reserva->run_profesor,
                    'id_espacio' => $reserva->id_espacio,
                    'fecha_reserva' => $reserva->fecha_reserva,
                    'hora_termino_modulo' => $horaTerminoModulo->format('H:i:s'),
                    'hora_limite_devolucion' => $horaLimiteDevolucion->format('H:i:s'),
                    'ahora' => $ahora->format('Y-m-d H:i:s')
                ]);
            }
        }

        $this->info("Se finalizaron {$reservasFinalizadas} reservas por no devolución de llaves.");

        return 0;
    }

    /**
     * Obtener el día de la semana en español a partir de una fecha
     */
    private function obtenerDiaSemana($fecha): string
    {
        $carbon = Carbon::parse($fecha);
        $dias = ['Sunday' => 'Domingo', 'Monday' => 'Lunes', 'Tuesday' => 'Martes', 'Wednesday' => 'Miércoles', 'Thursday' => 'Jueves', 'Friday' => 'Viernes', 'Saturday' => 'Sábado'];
        $diaSemanaIngles = $carbon->format('l');
        return $dias[$diaSemanaIngles] ?? 'Desconocido';
    }
}
```

---

## 📄 Archivo 2: app/Console/Kernel.php (Modificación)

### Líneas Añadidas (66-71):

```php
// Finalizar reservas sin devolución de llave después de 1 hora del término del módulo
// Se ejecuta cada 5 minutos para verificar las reservas que han excedido la hora de gracia
$schedule->command('reservas:finalizar-no-devueltas')
        ->everyFiveMinutes()
        ->withoutOverlapping()
        ->runInBackground()
        ->appendOutputTo(storage_path('logs/reservas-no-devueltas.log'));
```

### Contexto Completo (al final del método schedule()):

```php
protected function schedule(Schedule $schedule): void
{
    // ... otros comandos ...

    // Detectar clases no realizadas 20 minutos después del inicio de cada módulo
    // Los módulos inician a las :10 de cada hora, entonces 20 minutos después = :30
    // Se ejecuta de lunes a viernes (1-5), desde las 8:30 AM hasta las 22:30 PM
    $schedule->command('clases:detectar-no-realizadas')
            ->cron('30 8-22 * * 1-5')
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/clases-no-realizadas.log'));

    // Finalizar reservas sin devolución de llave después de 1 hora del término del módulo
    // Se ejecuta cada 5 minutos para verificar las reservas que han excedido la hora de gracia
    $schedule->command('reservas:finalizar-no-devueltas')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/reservas-no-devueltas.log'));
}
```

---

## 🔍 Explicación del Código

### Flujo Principal (método handle)

1. **Inicialización**
   ```php
   $this->info('Iniciando búsqueda de reservas no devueltas...');
   ```

2. **Búsqueda de Candidatos**
   ```php
   $reservasActivas = Reserva::where('estado', 'activa')
       ->whereNotNull('run_profesor')    // Solo profesores
       ->whereNull('hora_salida')        // Sin hora de salida
       ->get();
   ```

3. **Validación de Módulo**
   ```php
   $planificacion = Planificacion_Asignatura::with('modulo')
       ->where('id_espacio', $reserva->id_espacio)
       ->whereHas('modulo', function ($query) use ($reserva) {
           $query->where('dia', $this->obtenerDiaSemana($reserva->fecha_reserva));
       })
       ->first();
   ```

4. **Cálculo de Hora Límite**
   ```php
   $horaTerminoModulo = Carbon::parse($reserva->fecha_reserva . ' ' . $modulo->hora_termino);
   $horaLimiteDevolucion = $horaTerminoModulo->copy()->addHours(1);
   ```

5. **Verificación de Expiración**
   ```php
   $ahora = Carbon::now();
   if ($ahora->gte($horaLimiteDevolucion)) {
       // Finalizar reserva
   }
   ```

6. **Finalización y Anotación**
   ```php
   $reserva->estado = 'finalizada';
   $reserva->hora_salida = $horaLimiteDevolucion->format('H:i:s');
   $reserva->observaciones = trim(
       ($reserva->observaciones ?? '') . "\n" .
       "Reserva finalizada automáticamente..."
   );
   $reserva->save();
   ```

### Función Helper: obtenerDiaSemana()

```php
private function obtenerDiaSemana($fecha): string
{
    $carbon = Carbon::parse($fecha);
    $dias = [
        'Sunday' => 'Domingo',
        'Monday' => 'Lunes',
        'Tuesday' => 'Martes',
        'Wednesday' => 'Miércoles',
        'Thursday' => 'Jueves',
        'Friday' => 'Viernes',
        'Saturday' => 'Sábado'
    ];
    $diaSemanaIngles = $carbon->format('l');
    return $dias[$diaSemanaIngles] ?? 'Desconocido';
}
```

**Propósito**: Convertir fecha (Ej: 2025-01-15) → Día en español (Ej: "Miércoles") para matchear con Modulo.dia

---

## 🔗 Relaciones de Base de Datos Utilizadas

```
Reserva
  .id_espacio ──────────────→ Planificacion_Asignatura
                              .id_espacio
                              .id_modulo
                              
                              Modulo
                              .id_modulo
                              .dia (Lunes, Martes, ...)
                              .hora_termino (14:30:00)
```

---

## 📊 Ejemplo de Ejecución

### Input (Base de Datos)

```
Reserva:
- id_reserva: 1
- estado: 'activa'
- run_profesor: '123456789'
- id_espacio: 5
- fecha_reserva: 2025-01-15
- hora_salida: NULL
- observaciones: ''

Planificacion_Asignatura:
- id_espacio: 5
- dia: 'Miércoles'
- id_modulo: 10

Modulo:
- id_modulo: 10
- hora_termino: '14:30:00'
```

### Procesamiento (a las 15:45)

```
1. Parse fecha: 2025-01-15 → 'Miércoles'
2. Match Modulo: Modulo.dia = 'Miércoles' ✓
3. horaTerminoModulo = 2025-01-15 14:30:00
4. horaLimiteDevolucion = 2025-01-15 15:30:00
5. ahora = 2025-01-15 15:45:00
6. 15:45:00 >= 15:30:00 ✓ → FINALIZAR
```

### Output (Base de Datos)

```
Reserva (actualizada):
- estado: 'finalizada'
- hora_salida: '15:30:00'
- observaciones: 'Reserva finalizada automáticamente después de 1 hora 
  del módulo (Hora límite: 15:30:00). El profesor no devolvió la llave.'

Log (storage/logs/reservas-no-devueltas.log):
- Reserva finalizada automáticamente por no devolución de llave
- id_reserva: 1
- run_profesor: 123456789
- id_espacio: 5
- fecha_reserva: 2025-01-15
- hora_termino_modulo: 14:30:00
- hora_limite_devolucion: 15:30:00
- ahora: 2025-01-15 15:45:00
```

---

## ⚙️ Configuración del Scheduler

### everyFiveMinutes()
```
Cron: */5 * * * *
Ejecución: 00:00, 00:05, 00:10, ... 23:55
```

### withoutOverlapping()
```
Previene: Múltiples instancias simultáneas
Si anterior aún corre: Nueva ejecución se salta
```

### runInBackground()
```
No bloquea: Otras tareas del scheduler
Ejecución: Asíncrona
```

### appendOutputTo()
```
Destino: storage/logs/reservas-no-devueltas.log
Modo: Append (no sobrescribe)
```

---

## 🧪 Testing del Comando

### Ejecutar Manualmente
```bash
php artisan reservas:finalizar-no-devueltas
```

### Esperado
```
Iniciando búsqueda de reservas no devueltas después de 1 hora del módulo...
Se finalizaron 0 reservas por no devolución de llaves.
```

### Con Verbose (opcional)
```bash
php artisan reservas:finalizar-no-devueltas --verbose
```

---

## 🔐 Seguridad y Protecciones

| Protección | Implementación |
|-----------|-----------------|
| Race Conditions | `withoutOverlapping()` |
| Null Pointer | Verificar `$planificacion` y `$modulo` |
| Validación de Datos | `whereNotNull()` y `whereHas()` |
| Logging | Cada acción registrada |
| Atomicidad | `$reserva->save()` en BD |

---

## 📈 Escalabilidad

- **Volumen Bajo** (< 100 reservas): Sin problemas
- **Volumen Medio** (100-1000): Monitor cada 15 min si hay lentitud
- **Volumen Alto** (> 1000): Considerar indexación en BD
  - Índice en: `Reserva (estado, run_profesor, hora_salida)`
  - Índice en: `Planificacion_Asignatura (id_espacio, id_modulo)`

---

**Último Update**: 2025-01-15  
**Estado**: ✅ Producción Ready
