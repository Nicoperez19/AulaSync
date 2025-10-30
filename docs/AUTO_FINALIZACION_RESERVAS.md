# Auto-Finalización de Reservas en AulaSync

> **Última actualización:** 30 de octubre de 2025  
> **Contexto:** Implementación de la finalización automática de reservas cuando termina la clase

---

## 📖 Resumen Ejecutivo

El sistema AulaSync ahora finaliza automáticamente las reservas de clase cuando:
- Ha terminado el último módulo programado para la clase
- Han pasado 10 minutos adicionales de tiempo de gracia
- El profesor no ha devuelto la llave del espacio

Esta funcionalidad garantiza que:
- ✅ Los espacios se liberen automáticamente aunque el profesor no devuelva la llave
- ✅ Se mantenga un registro de cuándo y por qué se finalizó automáticamente
- ✅ Se actualice correctamente la observación si el profesor devuelve la llave tarde
- ✅ Los espacios aparezcan como disponibles en la vista de módulos actuales

---

## 🔍 Funcionamiento

### Proceso Automático

El comando `reservas:finalizar-expiradas` se ejecuta cada 5 minutos y realiza lo siguiente:

1. **Busca reservas activas de tipo 'clase'** del día actual
2. **Calcula el tiempo transcurrido** desde el fin del último módulo programado
3. **Si han pasado más de 10 minutos**:
   - Finaliza la reserva (cambia estado a 'finalizada')
   - Registra la hora de salida como la hora actual
   - Agrega una observación explicativa
   - Libera el espacio (cambia estado a 'Disponible')

### Observaciones Registradas

#### Cuando se finaliza automáticamente:
```
Reserva finalizó automáticamente por excederse en el tiempo y el profesor no ha devuelto la llave. 
Finalización automática a las HH:MM:SS, X minutos después del término programado.
```

#### Cuando el profesor devuelve la llave tarde:
```
[Observación anterior]
Profesor finalizó la clase más tarde y devolvió llave de acceso a las HH:MM:SS.
```

---

## ⚙️ Configuración

### Comando Schedule

El comando está configurado en `app/Console/Kernel.php`:

```php
// Finalizar reservas expiradas cada 5 minutos
$schedule->command('reservas:finalizar-expiradas')
        ->everyFiveMinutes()
        ->withoutOverlapping()
        ->runInBackground();
```

### Tiempo de Gracia

El tiempo de gracia está configurado en **10 minutos** después del fin del último módulo. Este valor está definido en el comando y puede ajustarse si es necesario.

---

## 📊 Escenarios de Uso

### Escenario 1: Finalización Automática Normal

**Situación:**
- Clase de Programación I programada de 14:10 a 16:00 (módulos 7-8)
- Profesor registró entrada a las 14:15
- Profesor no devolvió la llave al terminar la clase
- Son las 16:11 (11 minutos después del fin)

**Resultado:**
- Sistema finaliza automáticamente la reserva a las 16:11
- Espacio TH-03 cambia a estado "Disponible"
- Se registra en observaciones: "Reserva finalizó automáticamente por excederse en el tiempo y el profesor no ha devuelto la llave. Finalización automática a las 16:11:00, 11 minutos después del término programado."

### Escenario 2: Devolución Tardía

**Situación:**
- Continuando el Escenario 1
- Profesor llega a las 16:20 a devolver la llave
- Sistema ya había finalizado la reserva automáticamente

**Resultado:**
- Sistema detecta que la reserva fue auto-finalizada
- Actualiza la observación añadiendo: "Profesor finalizó la clase más tarde y devolvió llave de acceso a las 16:20:00."
- El espacio ya estaba disponible y permanece disponible

### Escenario 3: Devolución a Tiempo

**Situación:**
- Clase termina a las 16:00
- Profesor devuelve la llave a las 16:05 (dentro del tiempo de gracia)

**Resultado:**
- Reserva se finaliza normalmente por la devolución del profesor
- No se agrega ninguna observación especial
- Espacio cambia a "Disponible" normalmente

---

## 🎯 Integración con Vistas

### Vista de Módulos Actuales

La vista `livewire/modulos-actuales-table.blade.php` se actualiza automáticamente cada 60 segundos y muestra:

- **Antes de la auto-finalización** (durante el tiempo de gracia):
  - Estado: "Ocupado" (si el profesor registró entrada)
  - O "Clase por iniciar" (si el profesor no registró entrada)

- **Después de la auto-finalización**:
  - Estado: "Disponible"
  - El espacio aparece libre para nuevas reservas

### Componente Livewire

El componente `ModulosActualesTable` detecta automáticamente:
- Reservas activas
- Clases finalizadas
- Espacios disponibles

No requiere cambios adicionales para soportar la auto-finalización.

---

## 🛠️ Archivos Modificados

### Nuevos Archivos

1. **app/Console/Commands/FinalizarReservasExpiradas.php**
   - Comando principal que implementa la lógica de auto-finalización
   - Verifica reservas expiradas y las finaliza con observaciones apropiadas

### Archivos Modificados

1. **app/Console/Kernel.php**
   - Añadido schedule para ejecutar el comando cada 5 minutos

2. **app/Http/Controllers/PlanoDigitalController.php**
   - Método `devolverEspacio()` actualizado para detectar y actualizar reservas auto-finalizadas

3. **app/Http/Controllers/Api/ApiReservaController.php**
   - Método `registrarSalidaClase()` actualizado para detectar y actualizar reservas auto-finalizadas

---

## 🔍 Logs y Monitoreo

### Logs del Sistema

El comando registra información en el log de Laravel:

```php
Log::info("Reserva auto-finalizada {id} actualizada: profesor devolvió llave tarde");
Log::error("Error al finalizar reserva {id}: " . $exception->getMessage());
```

### Ejecución Manual

Para ejecutar el comando manualmente (útil para pruebas):

```bash
php artisan reservas:finalizar-expiradas
```

### Salida del Comando

```
=== FINALIZANDO RESERVAS EXPIRADAS ===
Fecha: 2025-10-30, Hora: 16:15:00, Día: jueves, Período: 2025-2
Total de reservas activas de clase: 5
Reserva R20251030141500 finalizada automáticamente (11 minutos de retraso)
Espacio TH-03 liberado
Reserva R20251030100000 aún tiene 8 minutos de gracia

=== RESUMEN ===
Reservas finalizadas: 1
Reservas sin finalizar: 4
Total procesadas: 5
```

---

## ⚠️ Consideraciones Importantes

### 1. Tiempo de Gracia

Los 10 minutos de gracia permiten que los profesores:
- Terminen actividades finales con estudiantes
- Ordenen el espacio antes de salir
- Caminen hasta el punto de devolución de llaves

### 2. Solo Reservas de Clase

El comando solo procesa reservas con:
- `tipo_reserva = 'clase'`
- `estado = 'activa'`
- `run_profesor` no nulo

Las reservas espontáneas o de solicitantes no se finalizan automáticamente.

### 3. Basado en Planificación

La hora de finalización se calcula basándose en:
- La planificación de la asignatura (tabla `planificacion_asignaturas`)
- Los horarios de módulos definidos en el sistema
- El período académico actual

### 4. Sincronización con Espacios

El comando también actualiza el estado del espacio:
- Solo marca como "Disponible" si el espacio estaba "Ocupado"
- Esto garantiza consistencia entre reservas y estados de espacios

---

## 🔗 Referencias

- [Modelo Reserva](../app/Models/Reserva.php)
- [Comando FinalizarReservasExpiradas](../app/Console/Commands/FinalizarReservasExpiradas.php)
- [ModulosActualesTable](../app/Livewire/ModulosActualesTable.php)
- [LOGICA_OCUPACION_ESPACIOS.md](LOGICA_OCUPACION_ESPACIOS.md)

---

## 📝 Historial de Cambios

| Fecha | Cambio | Autor |
|-------|--------|-------|
| 2025-10-30 | Implementación inicial de auto-finalización de reservas | Sistema |
