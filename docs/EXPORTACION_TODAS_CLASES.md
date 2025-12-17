# Exportación Completa de Clases - Implementación

## 📋 Resumen

Se ha implementado una nueva funcionalidad para exportar **todas las clases** (realizadas y no realizadas) en un archivo Excel. Esta exportación complementa la exportación existente de solo clases no realizadas.

## 🎯 Características

### Nueva Exportación: Todas las Clases

La nueva exportación incluye:

1. **Clases Realizadas**: Detectadas a través de los accesos (reservas) registrados por los profesores
   - Se verifica que el acceso esté dentro del horario del módulo (±30 minutos de margen)
   - Incluye hora de entrada y salida
   - Detecta atrasos automáticamente

2. **Clases No Realizadas**: Clases que no tienen acceso registrado
   - Estados: No Realizada, Justificada, Recuperada
   - Incluye motivo y observaciones

3. **Clases Planificadas**: Todas las clases que deberían haberse dado según la planificación

## 📊 Columnas del Excel

El archivo exportado incluye las siguientes columnas:

| Columna | Descripción |
|---------|-------------|
| Fecha | Fecha de la clase (formato dd/mm/YYYY) |
| Día | Día de la semana |
| Período | Período académico |
| Profesor | Nombre completo del profesor |
| RUN Profesor | RUN del profesor |
| Asignatura | Nombre de la asignatura |
| Código Asignatura | Código de la asignatura |
| Espacio | Identificador del espacio (sala) |
| Módulo | Número del módulo |
| Hora Inicio | Hora de inicio programada |
| Hora Fin | Hora de fin programada |
| **Estado** | **Realizada / No Realizada / Justificada / Recuperada / Planificada** |
| Hora Entrada | Hora real de entrada del profesor (si fue realizada) |
| Hora Salida | Hora real de salida del profesor (si fue realizada) |
| Motivo | Motivo de no realización (si aplica) |
| Observaciones | Observaciones adicionales (atrasos, etc.) |

## 🎨 Características Visuales

- **Código de colores por estado**:
  - 🟢 Verde claro: Clases Realizadas
  - 🔴 Rojo claro: Clases No Realizadas
  - 🟡 Amarillo claro: Clases Justificadas
  - 🔵 Azul claro: Clases Recuperadas
  - ⚪ Blanco: Clases Planificadas

- **Encabezado púrpura** para diferenciar de otras exportaciones
- **Bordes y formato** para mejor legibilidad
- **Ajuste automático de columnas**

## 🔧 Archivos Creados/Modificados

### 1. Nuevo Export (`app/Exports/TodasClasesExport.php`)

```php
class TodasClasesExport implements FromCollection, WithHeadings, WithMapping, 
                                    WithStyles, WithColumnWidths, WithTitle
```

**Lógica implementada**:
1. Obtiene todas las planificaciones del período
2. Genera fechas en el rango especificado
3. Crea una entrada por cada clase planificada
4. Compara con clases no realizadas registradas
5. Compara con accesos (reservas) para detectar clases realizadas
6. Calcula atrasos automáticamente (>15 minutos)
7. Ordena por fecha, espacio y módulo

### 2. Controlador Actualizado (`app/Http/Controllers/ClasesNoRealizadasController.php`)

**Nuevo método**:
```php
public function exportAllExcel(Request $request)
```

**Cambios**:
- Agregado import: `use App\Exports\TodasClasesExport;`
- Valida fecha_inicio, fecha_fin y periodo
- Genera nombre de archivo descriptivo
- Retorna descarga del Excel

### 3. Ruta Agregada (`routes/web.php`)

```php
Route::get('/clases-no-realizadas/export-all-excel', 
    [\App\Http\Controllers\ClasesNoRealizadasController::class, 'exportAllExcel'])
    ->name('clases-no-realizadas.export-all-excel');
```

### 4. Vista Actualizada (`resources/views/livewire/clases-no-realizadas-table.blade.php`)

**Cambios**:
- Botón verde renombrado: "Exportar No Realizadas" (antes "Exportar Excel")
- Nuevo botón púrpura: "Exportar Todas las Clases"
- Icono de Excel
- Pasa filtros de fecha y período

## 📝 Uso

### Desde la Interfaz Web

1. Ir a **Clases No Realizadas** (`/clases-no-realizadas`)
2. Aplicar filtros opcionales:
   - Fecha inicio
   - Fecha fin
   - Período
3. Hacer clic en **"Exportar Todas las Clases"** (botón púrpura)
4. Se descargará un archivo Excel con todas las clases

### Formato del Nombre de Archivo

- Con fechas: `Todas_Las_Clases_01-12-2024_a_31-12-2024.xlsx`
- Con período: `Todas_Las_Clases_Periodo_2024-2.xlsx`
- Sin filtros: `Todas_Las_Clases_12-12-2024.xlsx`

## 🔍 Lógica de Detección de Clases Realizadas

La clase se considera **Realizada** si:
1. Existe una reserva (acceso) del profesor en la fecha
2. El espacio de la reserva coincide con el espacio planificado
3. La hora de entrada está dentro del rango:
   - **Desde**: 30 minutos antes del inicio del módulo
   - **Hasta**: Hora de fin del módulo

**Ejemplo**:
- Módulo programado: 08:10 - 10:00
- Rango válido de entrada: 07:40 - 10:00
- Si entró a las 08:25 → Clase Realizada (atraso de 15 min)
- Si entró a las 07:50 → Clase Realizada (sin atraso)
- Si entró a las 10:15 → No se detecta como esa clase

## 🎯 Diferencias con la Exportación Anterior

| Característica | Clases No Realizadas | Todas las Clases |
|---------------|---------------------|------------------|
| Clases incluidas | Solo no realizadas | Todas (realizadas y no realizadas) |
| Detecta realizadas | ❌ No | ✅ Sí (por accesos) |
| Hora entrada/salida | ❌ No | ✅ Sí |
| Detecta atrasos | ❌ No | ✅ Sí |
| Filtro por estado | ✅ Sí | ❌ No (incluye todos) |
| Color del botón | 🟢 Verde | 🟣 Púrpura |
| Tamaño archivo | Menor | Mayor |

## 📊 Casos de Uso

### 1. Auditoría Completa
Obtener un reporte completo de todas las clases del mes para auditorías administrativas.

### 2. Análisis de Cumplimiento
Comparar clases planificadas vs. realizadas para medir porcentaje de cumplimiento.

### 3. Detección de Patrones
Identificar profesores con alta tasa de atrasos o clases no realizadas.

### 4. Reportes Ejecutivos
Generar reportes para dirección con estadísticas completas del período.

## ⚙️ Requisitos

- PHP 8.x
- Laravel 10.x
- Maatwebsite/Excel ^3.1
- Base de datos con:
  - `planificacion_asignaturas`
  - `clases_no_realizadas`
  - `reservas`
  - `modulos`

## 🔒 Permisos

La ruta requiere:
- Autenticación (`auth` middleware)
- Permiso `reportes` (`permission:reportes` middleware)

Roles con acceso:
- Administrador
- Supervisor

## 🐛 Consideraciones

1. **Rendimiento**: Para períodos largos (>3 meses), la exportación puede tardar algunos segundos
2. **Memoria**: El archivo puede ser grande si hay muchas planificaciones
3. **Precisión**: Depende de que los profesores registren correctamente sus accesos
4. **Zona Horaria**: Usa la zona horaria configurada en `config/app.php`

## 📚 Referencias

- Exportación original: `app/Exports/ClasesNoRealizadasExport.php`
- Modelo de accesos: `app/Models/Reserva.php`
- Modelo de planificación: `app/Models/Planificacion_Asignatura.php`
- Documentación de Maatwebsite/Excel: https://docs.laravel-excel.com/

---

**Fecha de implementación**: 12 de diciembre de 2025  
**Versión**: 1.0.0
