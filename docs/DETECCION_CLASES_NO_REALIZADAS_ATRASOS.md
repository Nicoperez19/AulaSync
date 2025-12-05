# Detección de Clases No Realizadas y Atrasos

## Resumen de Cambios

### 1. Nuevo Umbral de Detección: 15 minutos
- Anteriormente: 20 minutos después del inicio del módulo
- Nuevo: **15 minutos** después del inicio del módulo

### 2. Nuevo Concepto: Atrasos de Profesores
Se implementó el seguimiento de "atrasos", que ocurre cuando:
- El profesor llega **después de los 15 minutos** de gracia
- Pero **sí realiza** la clase

Esto permite diferenciar entre:
- ✅ **Clase realizada a tiempo**: Profesor registrado antes de los 15 min
- ⏰ **Clase con atraso**: Profesor registrado después de 15 min, pero sí dio la clase
- ❌ **Clase no realizada**: Profesor nunca se registró

### 3. Ejecución Automática
- El comando `clases:detectar-no-realizadas` ahora se ejecuta **cada 5 minutos**
- Horario: 08:00 a 23:00
- Días: Lunes a Sábado

### 4. Archivos Modificados/Creados

#### Nuevos:
- `database/migrations/2024_01_20_000001_create_profesor_atrasos_table.php` - Tabla para registrar atrasos
- `app/Models/ProfesorAtraso.php` - Modelo para atrasos de profesores

#### Modificados:
- `app/Console/Commands/DetectarClasesNoRealizadas.php` - Lógica de detección reescrita
- `app/Console/Kernel.php` - Programación actualizada (cada 5 min, lunes-sábado)
- `resources/views/layouts/plano_digital/show.blade.php` - Textos actualizados a "15+ min"

### 5. Uso del Comando

```bash
# Ejecutar detección normal
php artisan clases:detectar-no-realizadas

# Ver qué se detectaría sin registrar (modo prueba)
php artisan clases:detectar-no-realizadas --dry-run

# Forzar detección ignorando tiempo de gracia
php artisan clases:detectar-no-realizadas --force
```

### 6. Migración Requerida

Cuando la base de datos esté disponible:

```bash
php artisan migrate --path=database/migrations/2024_01_20_000001_create_profesor_atrasos_table.php
```

### 7. Estructura de la Tabla `profesor_atrasos`

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | bigint | ID auto-incremental |
| id_planificacion | bigint | Referencia a planificación |
| id_asignatura | bigint | Referencia a asignatura |
| id_espacio | bigint | Referencia a espacio |
| id_modulo | varchar(20) | Formato: LU.3, MA.5, etc. |
| run_profesor | varchar(20) | RUN del profesor |
| fecha | date | Fecha del atraso |
| hora_programada | time | Hora de inicio del módulo |
| hora_llegada | time | Hora real de llegada |
| minutos_atraso | int | Minutos de atraso |
| periodo | varchar(20) | Período académico |
| observaciones | text | Opcional |
| justificado | boolean | Si fue justificado |
| justificacion | text | Motivo de justificación |

### 8. Colores en Plano Digital

| Estado | Color | Descripción |
|--------|-------|-------------|
| Disponible | Verde (#059669) | Espacio libre |
| Ocupado | Rojo (#FF0000) | Con reserva activa |
| Reservado | Naranja (#F59E0B) | Clase programada o próxima |
| Clase no realizada | Negro (#1F2937) | 15+ min sin registro |
| Mantención | Gris (#6B7280) | En mantenimiento |
