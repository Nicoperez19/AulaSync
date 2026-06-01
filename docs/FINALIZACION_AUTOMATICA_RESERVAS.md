# Finalización Automática de Reservas

## Descripción General

El sistema SIA | Sistema de Informaci�n de Aulas ahora cuenta con un mecanismo de **finalización automática de reservas** que libera las salas al término exacto de las clases programadas, sin necesidad de que el profesor devuelva manualmente las llaves.

## ¿Cómo Funciona?

### 1. Comando Automático

El sistema ejecuta el comando `reservas:finalizar-expiradas` **cada minuto** para verificar si hay reservas que deben finalizarse.

**Ubicación del comando:** `app/Console/Commands/FinalizarReservasExpiradas.php`

**Configuración del scheduler:** `app/Console/Kernel.php`

### 2. Criterios de Finalización

El comando finaliza automáticamente las reservas que cumplen **TODAS** estas condiciones:

- ✅ Estado: `activa`
- ✅ Fecha: Día actual
- ✅ Tipo: `programada` (clases con horario asignado)
- ✅ Tienen asignatura asociada (`id_asignatura` no nulo)
- ✅ La hora actual es **igual o posterior** a la hora de fin del último módulo de la clase

### 3. Proceso de Finalización

Cuando se detecta una reserva que debe finalizarse:

1. **Actualiza la reserva:**
   - Cambia `estado` de `activa` a `finalizada`
   - Establece `hora_salida` con la hora de fin de la clase programada
   - Agrega observación: `"Reserva finalizada automáticamente al término de la clase programada a las HH:MM:SS."`

2. **Libera el espacio:**
   - Cambia el estado del espacio de `Ocupado` a `Disponible`
   - Permite que otros usuarios reserven el espacio inmediatamente

3. **Registra el evento:**
   - Log en el sistema
   - Mensaje en consola con ✅ indicando éxito

## Ejemplos

### Ejemplo 1: Clase Simple de 1 Módulo

**Escenario:**
- Asignatura: Matemáticas
- Espacio: TH-A1
- Módulo: 3 (10:10 - 11:00)
- Reserva creada: 10:05
- Hora de fin programada: 11:00

**Timeline:**
- `10:05` → Profesor escanea QR y crea reserva
- `10:10` → Clase comienza
- `11:00` → 🔴 **Clase termina**
- `11:00` → ✅ **Sistema finaliza automáticamente la reserva**
- `11:00` → ✅ **Sala TH-A1 queda disponible**

### Ejemplo 2: Clase de Múltiples Módulos Consecutivos

**Escenario:**
- Asignatura: Programación
- Espacio: TH-C3
- Módulos: 5, 6, 7 (12:10 - 15:00)
- Reserva creada: 12:05
- Hora de fin programada: 15:00

**Timeline:**
- `12:05` → Profesor escanea QR y crea reserva
- `12:10` → Clase comienza (módulo 5)
- `13:00` → Termina módulo 5, continúa módulo 6
- `14:00` → Termina módulo 6, continúa módulo 7
- `15:00` → 🔴 **Termina módulo 7 (último módulo)**
- `15:00` → ✅ **Sistema finaliza automáticamente la reserva**
- `15:00` → ✅ **Sala TH-C3 queda disponible**

### Ejemplo 3: Reserva Espontánea (NO se finaliza automáticamente)

**Escenario:**
- Usuario: Solicitante externo
- Espacio: TH-B2
- Tipo de reserva: `espontanea`
- Hora de inicio: 14:30

**Comportamiento:**
- ❌ **NO se finaliza automáticamente**
- ⚠️ El solicitante debe devolver las llaves manualmente
- ⚠️ El espacio permanece ocupado hasta la devolución manual

## Ventajas

### Para Profesores
- ✅ No necesitan devolver llaves al término de clase
- ✅ No hay penalizaciones por olvidos
- ✅ Proceso más ágil entre clases

### Para Estudiantes
- ✅ Salas disponibles inmediatamente al terminar clase
- ✅ Menos esperas para usar espacios
- ✅ Mejor aprovechamiento de las instalaciones

### Para Administración
- ✅ Gestión automática de espacios
- ✅ Reducción de conflictos de horarios
- ✅ Datos precisos de ocupación real

## Configuración del Scheduler

Para que el sistema funcione, el **Laravel Scheduler** debe estar configurado en el servidor:

### Cron Job Requerido

Agregar al crontab del servidor:

```bash
* * * * * cd /ruta/al/proyecto && php artisan schedule:run >> /dev/null 2>&1
```

Este cron ejecuta el scheduler de Laravel cada minuto, que a su vez ejecuta todos los comandos programados.

### Verificar que el Scheduler Esté Funcionando

```bash
php artisan schedule:list
```

Debería mostrar:
```
┌─────────────────────────────────────────────┬──────────────┐
│ Command                                      │ Interval     │
├─────────────────────────────────────────────┼──────────────┤
│ reservas:finalizar-expiradas                 │ Every minute │
│ espacios:actualizar-estado                   │ */5 * * * *  │
│ ...                                          │ ...          │
└─────────────────────────────────────────────┴──────────────┘
```

## Pruebas Manuales

### Ejecutar el Comando Manualmente

```bash
php artisan reservas:finalizar-expiradas
```

**Salida esperada:**
```
=== FINALIZANDO RESERVAS EXPIRADAS ===
Fecha: 2025-11-13, Hora: 15:05:30, Día: miercoles, Período: 2025-2
Total de reservas activas de clase: 3
⏱️  Reserva RES-001 terminará en 25 minutos (a las 15:30:00)
✅ Reserva RES-002 finalizada automáticamente al término de clase
✅ Espacio TH-A1 liberado automáticamente
⏱️  Reserva RES-003 terminará en 5 minutos (a las 15:10:00)

=== RESUMEN ===
Reservas finalizadas: 1
Reservas sin finalizar: 2
Total procesadas: 3
```

### Verificar Logs

```bash
tail -f storage/logs/laravel.log | grep "reservas:finalizar"
```

## Consideraciones Importantes

### ⚠️ Reservas Espontáneas

Las reservas de tipo `espontanea` (sin clase programada) **NO se finalizan automáticamente** porque no tienen un horario de fin definido. El usuario debe devolverlas manualmente.

### ⚠️ Horarios de Módulos

El sistema usa horarios predefinidos para cada módulo del día. Si cambian los horarios académicos, debe actualizarse el array `$horariosModulos` en el comando.

### ⚠️ Días Festivos

El sistema no considera días festivos o feriados. Se recomienda implementar validación adicional si es necesario.

## Resolución de Problemas

### Problema: Las reservas no se finalizan automáticamente

**Soluciones:**

1. **Verificar que el cron job esté configurado:**
   ```bash
   crontab -l
   ```

2. **Verificar que el comando se ejecute:**
   ```bash
   php artisan schedule:list
   ```

3. **Ejecutar manualmente para ver errores:**
   ```bash
   php artisan reservas:finalizar-expiradas -v
   ```

4. **Revisar logs:**
   ```bash
   tail -100 storage/logs/laravel.log
   ```

### Problema: El espacio no se libera

**Verificar:**
- Que la reserva sea de tipo `programada`
- Que tenga `id_asignatura` asignado
- Que la hora actual sea posterior al fin de clase
- Que el estado del espacio sea `Ocupado`

## Monitoreo

### Dashboard de Reservas

El sistema registra en la tabla `reservas`:
- `estado = 'finalizada'`
- `hora_salida` con la hora exacta de fin de clase
- `observaciones` con nota de finalización automática

### Queries Útiles para Monitoreo

```sql
-- Reservas finalizadas automáticamente hoy
SELECT id_reserva, id_espacio, hora, hora_salida, observaciones
FROM reservas
WHERE fecha_reserva = CURDATE()
  AND estado = 'finalizada'
  AND observaciones LIKE '%finalizada automáticamente%';

-- Espacios actualmente disponibles después de finalización automática
SELECT e.id_espacio, e.nombre_espacio, e.estado, r.hora_salida
FROM espacios e
LEFT JOIN reservas r ON e.id_espacio = r.id_espacio
WHERE e.estado = 'Disponible'
  AND r.fecha_reserva = CURDATE()
  AND r.observaciones LIKE '%finalizada automáticamente%'
ORDER BY r.hora_salida DESC;
```

## Historial de Cambios

| Fecha | Versión | Cambio |
|-------|---------|--------|
| 2025-11-13 | 1.0.0 | Implementación inicial de finalización automática al término de clase |

