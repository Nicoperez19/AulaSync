# Mantenedor de Días Feriados y Sin Actividades Universitarias

## Descripción

Este mantenedor permite gestionar días feriados y periodos sin actividad universitaria para que no se consideren las clases durante esos periodos. Las clases no realizadas durante estos días quedan automáticamente justificadas.

## Características

1. **Gestión de Feriados**: Permite agregar, editar y eliminar días feriados o periodos sin actividades.

2. **Tipos de Periodos**:
   - **Feriado**: Días festivos oficiales
   - **Semana de Reajuste**: Periodos de reajuste académico
   - **Suspensión de Actividades**: Otros periodos sin actividad universitaria

3. **Auto-justificación**: Las ausencias de profesores durante estos periodos se justifican automáticamente.

4. **Visualización en Tablero Académico**: Cuando es un día feriado, el tablero académico muestra un mensaje especial indicando que no hay actividades.

## Acceso al Mantenedor

1. Ingresar al sistema con un usuario con rol **Administrador**
2. En el menú lateral, ir a **Mantenedores** > **Días Feriados**

## Uso

### Agregar un Nuevo Feriado

1. Hacer clic en el botón **"Agregar Feriado"**
2. Completar el formulario:
   - **Nombre**: Nombre descriptivo del feriado (ej: "Día del Trabajador")
   - **Tipo**: Seleccionar el tipo de periodo
   - **Fecha Inicio**: Fecha de inicio del periodo
   - **Fecha Fin**: Fecha de término del periodo
   - **Descripción**: (Opcional) Descripción adicional
   - **Activo**: Marcar si el feriado está activo
3. Hacer clic en **"Guardar"**

### Editar un Feriado

1. Hacer clic en el ícono de edición (lápiz) en la fila del feriado
2. Modificar los campos necesarios
3. Hacer clic en **"Actualizar"**

### Activar/Desactivar un Feriado

- Hacer clic en el badge de estado (Activo/Inactivo) para cambiar el estado del feriado

### Eliminar un Feriado

1. Hacer clic en el ícono de eliminar (papelera) en la fila del feriado
2. Confirmar la eliminación

## Comportamiento del Sistema

### Durante un Día Feriado

1. **Tablero Académico**: 
   - Se muestra un banner amarillo con el nombre del feriado
   - Todos los espacios aparecen como "Semana de Reajuste Académico"
   - No se procesan clases ni reservas normales

2. **Clases No Realizadas**:
   - Las clases programadas para ese día se registran automáticamente como **justificadas**
   - El motivo será el nombre del feriado
   - Las observaciones incluyen la descripción del feriado

3. **Profesores**:
   - No se consideran inasistencias durante el periodo
   - No hay obligación de reagendar clases

## Permisos

- Solo usuarios con el permiso **"mantenedor de feriados"** pueden acceder
- Por defecto, solo el rol **Administrador** tiene este permiso

## Ejemplos de Uso

### Ejemplo 1: Feriado de un día
```
Nombre: Día del Trabajador
Tipo: Feriado
Fecha Inicio: 01/05/2025
Fecha Fin: 01/05/2025
Descripción: Feriado nacional
Estado: Activo
```

### Ejemplo 2: Semana de Reajuste
```
Nombre: Semana de Reajuste Académico
Tipo: Semana de Reajuste
Fecha Inicio: 15/07/2025
Fecha Fin: 19/07/2025
Descripción: Periodo de reajuste entre semestres
Estado: Activo
```

### Ejemplo 3: Suspensión de Actividades
```
Nombre: Receso de Fiestas Patrias
Tipo: Suspensión de Actividades
Fecha Inicio: 18/09/2025
Fecha Fin: 20/09/2025
Descripción: Receso por fiestas patrias
Estado: Activo
```

## Notas Técnicas

- Los feriados se validan por rango de fechas
- Un feriado puede abarcar múltiples días (fecha_inicio <= fecha <= fecha_fin)
- Solo los feriados con estado **Activo** afectan el sistema
- La verificación de feriados se realiza automáticamente en tiempo real

## Migración y Datos Iniciales

La tabla `dias_feriados` se crea con la migración `2025_10_30_103535_create_dias_feriados_table.php`

Para ejecutar la migración y cargar los feriados legales de Chile:
```bash
php artisan migrate
php artisan db:seed --class=DatabaseSeeder
```

O para cargar solo los feriados:
```bash
php artisan db:seed --class=DiasFeriadosSeeder
```

### Feriados Precargados

El sistema incluye por defecto los siguientes feriados legales de Chile para 2025:
- Año Nuevo (1 de enero)
- Viernes Santo y Sábado Santo (18-19 de abril)
- Día del Trabajador (1 de mayo)
- Día de las Glorias Navales (21 de mayo)
- San Pedro y San Pablo (29 de junio)
- Día de la Virgen del Carmen (16 de julio)
- Asunción de la Virgen (15 de agosto)
- Día de la Independencia Nacional (18 de septiembre)
- Día de las Glorias del Ejército (19 de septiembre)
- Encuentro de Dos Mundos (12 de octubre)
- Día de las Iglesias Evangélicas y Protestantes (31 de octubre)
- Día de Todos los Santos (1 de noviembre)
- Inmaculada Concepción (8 de diciembre)
- Navidad (25 de diciembre)

Además, incluye recesos académicos típicos:
- Receso de Invierno (14-25 de julio)
- Receso Fiestas Patrias (15-22 de septiembre)

Estos datos se pueden actualizar anualmente ejecutando el seeder nuevamente.

## Permisos en Base de Datos

Para agregar el permiso manualmente:
```bash
php artisan db:seed --class=RoleSeeder
```

O ejecutar en la consola de Laravel:
```php
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

$permission = Permission::firstOrCreate(['name' => 'mantenedor de feriados']);
$roleAdmin = Role::findByName('Administrador');
$roleAdmin->givePermissionTo($permission);
```
