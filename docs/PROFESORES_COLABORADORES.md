# Sistema de Profesores Colaboradores

## Descripción
Sistema para gestionar clases temporales de profesores colaboradores con asignaturas que tienen un período de vigencia definido.

## Características Principales

### 1. Gestión de Clases Temporales
- **Fecha de inicio y término**: Define el período exacto durante el cual la clase está activa
- **Asignatura temporal**: Permite crear asignaturas sin estar vinculadas a una asignatura permanente
- **Horarios flexibles**: Selecciona días y módulos específicos mediante un calendario visual

### 2. Vista de Módulos Actuales
- Las clases de profesores colaboradores aparecen en la vista `/modulos-actuales` mientras estén vigentes
- Automáticamente dejan de mostrarse cuando finaliza el período (fecha_termino)
- Se identifican visualmente con una etiqueta especial

### 3. Calendario Visual de Horarios
Similar a la vista de espacios (`/espacios?TH`), permite:
- Seleccionar múltiples días de la semana
- Elegir módulos específicos (del 1 al 15)
- Ver disponibilidad de espacios en tiempo real
- Evitar conflictos de horarios

## Uso

### Acceso al Mantenedor
```
Ruta: /profesores-colaboradores
Permisos: Administrador, Supervisor
```

### Crear Nueva Clase Temporal

1. **Acceder al mantenedor**
   - Ir a `/profesores-colaboradores`
   - Click en "Crear Profesor Colaborador"

2. **Datos Básicos**
   - Seleccionar profesor colaborador
   - Nombre de la asignatura temporal
   - Descripción (opcional)
   - Fecha de inicio
   - Fecha de término

3. **Selección de Horarios**
   - Usar el calendario visual para seleccionar días y módulos
   - El sistema muestra qué módulos están ocupados
   - Seleccionar el espacio para cada horario

4. **Guardar**
   - El sistema valida que no haya conflictos
   - Crea todas las planificaciones automáticamente

### Editar Clase Temporal

1. Acceder a la lista de profesores colaboradores
2. Click en "Editar" en la clase deseada
3. Modificar los datos necesarios
4. Actualizar horarios en el calendario visual
5. Guardar cambios

### Desactivar/Eliminar

- **Desactivar**: Cambia el estado a "inactivo" sin eliminar registros
- **Eliminar**: Elimina permanentemente la clase y todos sus horarios

## Automatización

### Comando Artisan
```bash
php artisan profesores-colaboradores:desactivar-vencidos
```

Este comando:
- Busca profesores colaboradores con `fecha_termino` < hoy
- Cambia su estado a "inactivo"
- Se puede programar en el cron para ejecutarse diariamente

### Programar en Task Scheduler

Editar `app/Console/Kernel.php`:
```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('profesores-colaboradores:desactivar-vencidos')
             ->daily()
             ->at('00:01');
}
```

## Estructura de Base de Datos

### Tabla: `profesores_colaboradores`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | bigint | ID autoincremental |
| run_profesor_colaborador | unsignedBigInteger | RUN del profesor |
| id_asignatura | string (nullable) | Asignatura existente (opcional) |
| nombre_asignatura_temporal | string | Nombre de la asignatura temporal |
| descripcion | text | Descripción adicional |
| fecha_inicio | date | Fecha de inicio de vigencia |
| fecha_termino | date | Fecha de término de vigencia |
| estado | enum | 'activo' o 'inactivo' |

### Tabla: `planificaciones_profesores_colaboradores`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | bigint | ID autoincremental |
| id_profesor_colaborador | bigint | FK a profesores_colaboradores |
| id_modulo | string | FK a modulos (ej: "LU.1") |
| id_espacio | string | FK a espacios |

## Scopes Útiles en Modelos

### ProfesorColaborador
```php
// Solo activos
ProfesorColaborador::activos()->get();

// Solo vigentes (fecha actual entre inicio y término)
ProfesorColaborador::vigentes()->get();

// Activos y vigentes
ProfesorColaborador::activosYVigentes()->get();

// Vencidos
ProfesorColaborador::vencidos()->get();
```

### PlanificacionProfesorColaborador
```php
// Por día específico
PlanificacionProfesorColaborador::porDia('lunes')->get();

// Solo vigentes
PlanificacionProfesorColaborador::vigentes()->get();
```

## API Endpoints

### GET /api/profesores-colaboradores/horarios-ocupados
Obtiene los módulos ocupados para un día específico

**Parámetros:**
- `fecha` (opcional): Fecha en formato Y-m-d (default: hoy)
- `id_espacio` (opcional): Filtrar por espacio específico

**Respuesta:**
```json
{
  "success": true,
  "ocupados": [
    {
      "id_modulo": "LU.1",
      "numero": 1,
      "hora_inicio": "08:10",
      "hora_termino": "09:00"
    }
  ],
  "dia": "lunes",
  "prefijo": "LU"
}
```

## Integración con Módulos Actuales

El componente `ModulosActualesTable` automáticamente:
1. Carga planificaciones de profesores colaboradores vigentes
2. Las muestra junto con las clases regulares
3. Las identifica con badge especial "TEMPORAL"
4. Excluye las vencidas (fecha_termino < hoy)

## Notas Importantes

- Las clases temporales NO se exportan a PDF de horarios regulares
- NO se consideran para estadísticas de ocupación de largo plazo
- Se pueden solapar con clases regulares (validar manualmente)
- Al eliminar un profesor colaborador, se eliminan todas sus planificaciones
- Al cambiar estado a "inactivo", NO se muestran en módulos actuales

## Mejoras Futuras Sugeridas

1. Validación automática de conflictos de horarios
2. Notificaciones antes del vencimiento
3. Historial de clases temporales
4. Estadísticas de uso de profesores colaboradores
5. Integración con sistema de asistencia
