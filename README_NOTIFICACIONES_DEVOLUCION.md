# Sistema de Notificaciones de Devolución de Llaves

## Descripción

El sistema de notificaciones de devolución de llaves ha sido mejorado para que **solo genere notificaciones cuando el espacio esté realmente ocupado** y no solo basado en la planificación de clases. Esto asegura que las notificaciones solo aparezcan cuando realmente se necesite devolver las llaves.

## Cambios Implementados

### 1. Modificación del Método `getKeyReturnNotifications`

**Archivo:** `app/Http/Controllers/DashboardController.php`

**Cambio:** Se agregó una condición adicional para verificar que el espacio esté realmente ocupado:

```php
->whereHas('espacio', function ($query) {
    // Solo incluir espacios que estén realmente ocupados
    $query->where('estado', 'Ocupado');
})
```

**Antes:** Las notificaciones se generaban para todas las planificaciones que terminaban en los próximos 10 minutos, independientemente del estado del espacio.

**Después:** Las notificaciones solo se generan para planificaciones de espacios que están marcados como "Ocupado" en la base de datos.

### 2. Comando de Actualización de Estados Mejorado

**Archivo:** `app/Console/Commands/ActualizarEstadoEspacios.php`

**Mejoras:**
- Verificación completa de todos los espacios
- Análisis de planificaciones activas y reservas
- Detección de inconsistencias en estados
- Información detallada de cambios realizados

**Uso:**
```bash
php artisan espacios:actualizar-estado
```

### 3. Comando de Prueba de Notificaciones

**Archivo:** `app/Console/Commands/TestQRGeneration.php`

**Funcionalidad:** Prueba específica del sistema de notificaciones con diferentes escenarios.

**Uso:**
```bash
php artisan test:notificaciones-devolucion-llaves
```

### 4. Programación Automática

**Archivo:** `app/Console/Kernel.php`

**Configuración:** El comando de actualización de estados se ejecuta automáticamente cada 5 minutos.

## Cómo Funciona el Sistema

### Flujo de Notificaciones

1. **Verificación de Planificaciones:** El sistema busca planificaciones que terminan en los próximos 10 minutos.

2. **Verificación de Estado:** Solo considera espacios que están marcados como "Ocupado" en la base de datos.

3. **Generación de Notificaciones:** Si el espacio está ocupado, se genera la notificación para el profesor correspondiente.

### Estados de Espacios

- **Ocupado:** El espacio tiene una reserva activa o está siendo utilizado
- **Disponible:** El espacio está libre y puede ser reservado
- **Reservado:** El espacio tiene una reserva programada

### Ejemplo de Notificación

```
Devolución de llaves pendiente
El profesor JIMENEZ TOLEDO, PATRICIA ELENA debe devolver las llaves de la sala Taller de Párvulos.
Su clase finaliza a las 16:00.
```

## Comandos Disponibles

### 1. Actualizar Estados de Espacios
```bash
php artisan espacios:actualizar-estado
```
- Actualiza el estado de todos los espacios basado en reservas y planificaciones
- Detecta y corrige inconsistencias
- Muestra un resumen detallado de los cambios

### 2. Probar Notificaciones
```bash
php artisan test:notificaciones-devolucion-llaves
```
- Prueba el sistema de notificaciones
- Muestra información detallada de espacios ocupados y disponibles
- Identifica posibles inconsistencias

### 3. Probar Estados de Espacios
```bash
php artisan test:estados-espacios
```
- Prueba completa del sistema de estados
- Incluye verificación de notificaciones de devolución de llaves
- Muestra información detallada de planificaciones y reservas

## Configuración del Cron Job

Para que la actualización automática funcione, asegúrate de que el cron job esté configurado:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## Monitoreo y Debugging

### Logs
Los comandos generan logs detallados que pueden ser consultados para debugging:

```bash
tail -f storage/logs/laravel.log
```

### Verificación Manual
Para verificar el estado actual del sistema:

```bash
# Verificar estados de espacios
php artisan test:estados-espacios

# Verificar notificaciones específicas
php artisan test:notificaciones-devolucion-llaves

# Actualizar estados manualmente
php artisan espacios:actualizar-estado
```

## Casos de Uso

### Caso 1: Clase Programada pero Sin Ingreso Registrado
- **Planificación:** Existe una clase programada para las 14:00-16:00
- **Estado del Espacio:** Disponible (no se registró ingreso)
- **Resultado:** No se genera notificación de devolución de llaves

### Caso 2: Clase con Ingreso Registrado
- **Planificación:** Existe una clase programada para las 14:00-16:00
- **Estado del Espacio:** Ocupado (se registró ingreso)
- **Resultado:** Se genera notificación de devolución de llaves a las 15:50

### Caso 3: Reserva Espontánea
- **Reserva:** Usuario reservó el espacio para uso libre
- **Estado del Espacio:** Ocupado
- **Resultado:** Se genera notificación cuando la reserva está por terminar

## Beneficios del Nuevo Sistema

1. **Precisión:** Solo notifica cuando realmente se necesita devolver llaves
2. **Eficiencia:** Reduce notificaciones innecesarias
3. **Consistencia:** Mantiene sincronizados los estados de espacios
4. **Transparencia:** Proporciona información detallada del sistema
5. **Automatización:** Actualización automática de estados

## Troubleshooting

### Problema: Notificaciones no aparecen
**Solución:** Verificar que el espacio esté marcado como "Ocupado"

### Problema: Estados inconsistentes
**Solución:** Ejecutar `php artisan espacios:actualizar-estado`

### Problema: Notificaciones duplicadas
**Solución:** El sistema ya incluye protección contra duplicados

## Contacto

Para reportar problemas o solicitar mejoras, contactar al equipo de desarrollo. 