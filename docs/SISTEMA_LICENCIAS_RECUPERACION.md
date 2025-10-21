# 🔗 Sistema de Licencias y Recuperación de Clases

## Flujo Automático de Generación

Este sistema vincula automáticamente las **licencias de profesores** con las **clases que deben recuperarse**.

## 📋 Cómo Funciona

### 1. Creación de Licencia

Cuando se crea una licencia de profesor con `genera_recuperacion = true`:

1. **Se buscan automáticamente** todas las planificaciones del profesor
2. **Se filtran** las clases que caen dentro del período de la licencia
3. **Se crean registros** en `recuperacion_clases` para cada clase afectada
4. Cada clase queda con estado `pendiente`

### 2. Vinculación Licencia-Recuperación

```
┌─────────────────────────┐
│  Licencia Profesor      │
│  - ID: 1                │
│  - Profesor: Juan Pérez │
│  - Fecha: 01-15 Oct     │
│  - Motivo: Médica       │
└───────────┬─────────────┘
            │
            │ genera_recuperacion = true
            │
            ▼
┌───────────────────────────────────────┐
│  Clases a Recuperar (Automático)     │
├───────────────────────────────────────┤
│  ✓ Clase 1 - 03 Oct - Matemáticas    │
│  ✓ Clase 2 - 05 Oct - Matemáticas    │
│  ✓ Clase 3 - 10 Oct - Física         │
│  ✓ Clase 4 - 12 Oct - Física         │
└───────────────────────────────────────┘
```

### 3. Estados de las Clases

- **Pendiente**: Clase aún no reagendada
- **Reagendada**: Se asignó nueva fecha y módulo
- **Obviada**: Se decidió no recuperar esta clase
- **Realizada**: La clase de recuperación ya se llevó a cabo

### 4. Gestión de Recuperaciones

El usuario autorizado puede:

1. **Ver todas las clases** generadas automáticamente
2. **Reagendar manualmente** cada clase
3. **Notificar al profesor** por correo electrónico
4. **Obviar** clases que no se recuperarán
5. **Marcar como realizadas** las que ya se dictaron

## 🔄 Actualizaciones Automáticas

### Al Editar una Licencia

- **Cambio de fechas**: Se regeneran las clases (solo las pendientes)
- **Desactivar recuperación**: Se eliminan las clases pendientes
- **Cambio a estado "cancelada"**: Se eliminan las clases pendientes

### Al Eliminar una Licencia

- Todas las clases de recuperación asociadas se eliminan automáticamente (CASCADE)

## 📊 Visualización

### En el Módulo de Licencias

Cada licencia muestra:
- Total de clases generadas
- Contador de pendientes
- Contador de reagendadas
- Contador de realizadas

### En el Módulo de Recuperación

Cada clase muestra:
- Información de la licencia asociada
- Motivo y período de la licencia
- Estado actual de la recuperación

## 🎯 Algoritmo de Generación

```php
Para cada licencia con genera_recuperacion = true:
    1. Obtener horarios del profesor
    2. Para cada horario:
        a. Obtener planificaciones de asignaturas
        b. Para cada planificación:
            - Identificar día de la semana
            - Generar fechas entre fecha_inicio y fecha_fin
            - Crear recuperación para cada fecha
    3. Guardar todas las recuperaciones
```

## 💡 Ejemplo Práctico

**Profesor:** María González  
**Licencia:** 10-20 de Octubre 2025  
**Motivo:** Licencia médica  

**Horario del Profesor:**
- Lunes 10:00-11:00: Cálculo I
- Miércoles 14:00-15:00: Álgebra
- Viernes 09:00-10:00: Cálculo I

**Clases Generadas Automáticamente:**
1. Lunes 11 Oct - Cálculo I - Módulo 3
2. Miércoles 13 Oct - Álgebra - Módulo 6  
3. Viernes 15 Oct - Cálculo I - Módulo 2
4. Lunes 18 Oct - Cálculo I - Módulo 3
5. Miércoles 20 Oct - Álgebra - Módulo 6

**Total: 5 clases pendientes de recuperar**

## 🔧 Comando Manual

Si necesitas generar recuperaciones para licencias existentes:

```bash
# Solo licencias activas
php artisan licencias:generar-recuperaciones

# Todas las licencias (activas y finalizadas)
php artisan licencias:generar-recuperaciones --all
```

## 🚀 Ventajas del Sistema

✅ **Automático**: No hay que crear recuperaciones manualmente  
✅ **Vinculado**: Siempre se sabe qué licencia generó cada recuperación  
✅ **Trazable**: Historial completo de estados  
✅ **Inteligente**: Solo regenera clases pendientes al editar  
✅ **Notificaciones**: Correo automático al profesor  
✅ **Flexible**: Se puede obviar o reagendar según necesidad

## 📌 Notas Importantes

1. Las clases solo se generan si `genera_recuperacion = true`
2. Solo se eliminan automáticamente las clases en estado `pendiente`
3. Las clases `reagendadas` o `realizadas` no se tocan al editar la licencia
4. El DELETE CASCADE elimina todas las recuperaciones al borrar una licencia
5. El sistema usa Observers de Laravel para automatizar todo el proceso

## 🔒 Permisos Requeridos

- `gestionar licencias profesores`: Crear/editar/eliminar licencias
- `gestionar recuperacion clases`: Reagendar y gestionar recuperaciones

Solo los usuarios con estos permisos verán los módulos en el sidebar.
