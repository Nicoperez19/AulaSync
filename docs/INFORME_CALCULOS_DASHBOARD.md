---
title: Informe Técnico - Cálculos del Sistema de Gestión de Espacios Académicos
author: Equipo de Desarrollo - AulaSync
date: 5 de Noviembre de 2025
---

# INFORME TÉCNICO: CÁLCULOS DEL SISTEMA DE GESTIÓN DE ESPACIOS ACADÉMICOS

**Institución:** Universidad Católica de la Santísima Concepción  
**Sede:** Talcahuano  
**Instituto:** Instituto Tecnológico  
**Destinatario:** Subdirector del Instituto Tecnológico  
**Asunto:** Descripción de Metodologías de Cálculo - Sistema AulaSync  
**Fecha:** 5 de Noviembre de 2025

---

## 1. INTRODUCCIÓN

El presente informe describe las metodologías de cálculo implementadas en el sistema de gestión de espacios académicos AulaSync. Cada indicador ha sido diseñado para proporcionar información precisa sobre la utilización de infraestructura disponible en la sede Talcahuano.

---

## 2. INDICADORES CALCULADOS

### 2.1 Ocupación Semanal

**Definición:** Porcentaje de utilización de salas de clase durante la semana en curso (lunes a domingo).

**Fórmula de Cálculo:**

```
Ocupación Semanal (%) = (Horas Ocupadas / Horas Totales Disponibles) × 100
```

**Componentes:**

- **Horas Totales Disponibles:** Número de espacios × 5 días laborales × 8 horas/día
- **Horas Ocupadas:** Sumatoria de reservas con estado activo o finalizado durante la semana

**Período:** Semana calendario actual (lunes a domingo)

**Fuente de Datos:** Tabla RESERVAS (campos: fecha_reserva, estado, hora, hora_salida)

---

### 2.2 Ocupación Mensual

**Definición:** Porcentaje de utilización acumulada durante el mes calendario actual.

**Fórmula de Cálculo:**

```
Ocupación Mensual (%) = (Horas Ocupadas / Horas Totales Disponibles) × 100
```

**Componentes:**

- **Horas Totales Disponibles:** Número de espacios × días laborales del mes × 8 horas/día
- **Días Laborales:** Únicamente lunes a viernes (excluyendo fines de semana)
- **Horas Ocupadas:** Sumatoria de reservas con estado activo o finalizado durante el mes

**Período:** Mes calendario actual

**Fuente de Datos:** Tabla RESERVAS

---

### 2.4 Horas Utilizadas

**Definición:** Comparativa entre horas de reserva registradas versus horas disponibles.

**Fórmula de Cálculo:**

```
Horas Utilizadas = Cantidad de Reservas Activas/Finalizadas del Día
Horas Disponibles = 40 horas (5 días × 8 horas)
```

**Período:** Día actual

**Fuente de Datos:** Tabla RESERVAS

---

### 2.5 Salas Ocupadas

**Definición:** Distribución de espacios según su estado operacional.

**Cálculo:**

- **Salas Ocupadas:** Espacios con estado = "Ocupado"
- **Salas Libres:** Espacios con estado = "Disponible"

**Período:** Estado instantáneo (tiempo actual)

**Fuente de Datos:** Tabla ESPACIOS

---

### 2.6 Uso por Día de la Semana

**Definición:** Cantidad de reservas registradas para cada día de la semana actual.

**Cálculo:** Para cada día (lunes a sábado):

```
Reservas del Día = COUNT(reservas) WHERE fecha_reserva = día AND estado IN ('activa', 'finalizada')
```

**Período:** Semana actual

**Fuente de Datos:** Tabla RESERVAS

---

### 2.7 Comparativa por Tipo de Espacio

**Definición:** Análisis de ocupación segregado por categoría de sala (Aula, Laboratorio, Auditorio, etc).

**Fórmula de Cálculo:**

```
Ocupación por Tipo (%) = (Reservas del Tipo / Total Espacios del Tipo) × 100
```

**Desglose:**

- **Por cada tipo de espacio:**
  - Total de espacios disponibles
  - Reservas activas en la semana
  - Porcentaje de utilización

**Período:** Semana actual

**Fuente de Datos:** Tablas ESPACIOS, RESERVAS

---

### 2.8 Evolución Semanal de Ocupación

**Definición:** Tendencia de utilización de espacios a lo largo de la semana.

**Cálculo:** Para cada día del mes:

```
Índice Diario = Cantidad de Reservas × Factor de Conversión
```

**Período:** Últimos 10 días del mes actual (limitado por rendimiento)

**Fuente de Datos:** Tabla RESERVAS

---

### 2.9 Reservas por Tipo de Espacio

**Definición:** Total acumulado de reservas agrupadas por categoría de sala.

**Cálculo:** Para cada tipo de espacio:

```
Total Reservas = COUNT(reservas) WHERE tipo_espacio = categoría AND estado = 'activa'
```

**Período:** Semana actual

**Fuente de Datos:** Tablas ESPACIOS, RESERVAS

---

### 2.10 Reservas Canceladas (No Utilizadas)

**Definición:** Listado de reservas que no fueron utilizadas durante la semana.

**Criterio:** Reservas con estado = "finalizada"

**Información Registrada:**
- Usuario responsable
- Espacio asignado
- Hora programada

**Período:** Semana actual

**Fuente de Datos:** Tabla RESERVAS

---

### 2.11 Horarios Agrupados por Módulo Actual

**Definición:** Planificaciones de clase en ejecución en el presente módulo horario.

**Cálculo:** Búsqueda de:

```
Planificaciones WHERE
  - Día Actual = Día Planificado
  - Hora Actual ENTRE Hora Inicio Y Hora Término
  - Estado = Activo
```

**Información Registrada:**
- Espacio ocupado
- Asignatura en dictación
- Profesor responsable
- Horario

**Período:** Módulo horario actual

**Fuente de Datos:** Tablas PLANIFICACION_ASIGNATURAS, MODULOS, ESPACIOS

---

### 2.12 Promedio de Duración de Reserva

**Definición:** Tiempo promedio que permanecen activas las reservas.

**Fórmula de Cálculo:**

```
Promedio (minutos) = SUMA(Duración de Cada Reserva) / Cantidad de Reservas Finalizadas

Donde: Duración = Hora Salida - Hora Entrada
```

**Período:** Todas las reservas finalizadas del sistema

**Fuente de Datos:** Tabla RESERVAS (campos: hora, hora_salida)

---

### 2.13 Porcentaje No-Show

**Definición:** Porcentaje de reservas que fueron programadas pero no utilizadas.

**Fórmula de Cálculo:**

```
Porcentaje No-Show (%) = (Reservas No Utilizadas / Total Reservas) × 100
```

**Criterio de No-Show:** Reservas finalizadas sin registro de entrada (campo hora = null)

**Período:** Todas las reservas del sistema anteriores a la fecha actual

**Fuente de Datos:** Tabla RESERVAS

---

### 2.14 Canceladas por Tipo de Sala

**Definición:** Distribución de reservas no utilizadas por categoría de espacio.

**Cálculo:** Para cada tipo de espacio:

```
No-Show del Tipo = COUNT(reservas) WHERE tipo_espacio = categoría AND estado = 'finalizada'
```

**Período:** Todas las reservas del sistema

**Fuente de Datos:** Tablas ESPACIOS, RESERVAS

---

## 3. CONSIDERACIONES TÉCNICAS

### 3.1 Períodos de Análisis

- **Semanal:** De lunes a domingo de la semana calendario en curso
- **Diaria:** 24 horas del día actual (divididas en módulos horarios)
- **Mensual:** Mes calendario completo, considerando solo días laborales (lunes a viernes)

### 3.2 Estados de Reserva

El sistema reconoce los siguientes estados:

- **Activa:** Reserva vigente, en uso o próxima a utilizarse
- **Finalizada:** Reserva concluida, independientemente de su utilización real

### 3.3 Horas Laborales

Se considera una jornada de 8 horas laborales diarias, distribuida en módulos horarios de 50 minutos cada uno.

### 3.4 Filtros de Datos

Todos los cálculos respetan la estructura organizacional:

- Filtro por Sede: Talcahuano
- Filtro por Instituto: Instituto Tecnológico
- Filtro por Piso: Configurable por usuario
- Filtro por Período Académico: Período actual del sistema

---

## 4. RECOMENDACIONES DE MEJORA EN CÁLCULOS

### 4.1 Corrección de Ocupación Semanal y Mensual (Prioridad Alta)

**Problema Detectado:** Los cálculos de ocupación cuentan la cantidad de reservas en lugar de horas reales ocupadas.

**Impacto:** Un espacio con una reserva de 8 horas se cuenta como 1 unidad, distorsionando el porcentaje real de ocupación.

**Recomendación:** Modificar la fórmula para sumar las horas reales de cada reserva:

```
Horas Ocupadas = SUMA(TIMESTAMPDIFF(HOUR, hora_entrada, hora_salida))
  WHERE fecha_reserva ENTRE rango
  AND estado IN ('activa', 'finalizada')
```

**Beneficio:** Proporcionará una medida precisa del uso real de infraestructura.

---

### 4.2 Corrección de Horas Utilizadas (Prioridad Media)

**Problema Detectado:** El valor de "Horas Disponibles" está hardcodeado en 40 (5 × 8), sin considerar variaciones en la jornada académica.

**Recomendación:** Calcular dinámicamente en función del número de espacios y días laborales:

```
Horas Disponibles = Cantidad de Espacios × Días Laborales × 8
```

**Beneficio:** Indicador más realista de uso relativo.

---

### 4.3 Clarificación de Estado de Reserva (Prioridad Alta)

**Problema Detectado:** El estado "finalizada" agrupa tanto reservas utilizadas como no utilizadas, afectando indicadores de "No-Show" y "Canceladas".

**Recomendación:** En la base de datos, agregar un campo adicional para diferenciar:

- **Utilizada:** Reserva completada, con registro de hora de entrada y salida
- **No-Show:** Reserva programada sin ingreso registrado
- **Cancelada:** Cancelada antes de ejecutarse

**Beneficio:** Permitirá cálculos más precisos en indicadores relacionados.

---

### 4.4 Validación del Estado del Espacio (Prioridad Alta)

**Problema Detectado:** El estado del espacio (Ocupado/Disponible) se actualizaba manualmente, causando posibles inconsistencias.

**Recomendación:** Implementar actualización automática mediante eventos:

- Al crear reserva activa → Estado "Ocupado"
- Al finalizar reserva → Estado "Disponible"
- Sistema de rollback en caso de error

**Beneficio:** Los indicadores "Salas Ocupadas" y "Salas Desocupadas" reflejarán la realidad en tiempo real.

---

### 4.5 Optimización de Fórmula de Evolución Semanal (Prioridad Media)

**Problema Detectado:** Se aplica un factor de conversión artificial (×12.5) para convertir reservas a porcentaje.

**Recomendación:** Utilizar fórmula consistente con otros indicadores:

```
Ocupación Diaria (%) = (Horas Ocupadas / Horas Disponibles) × 100
```

**Beneficio:** Consistencia en la interpretación de indicadores en todo el sistema.

---

### 4.6 Creación de Índices de Base de Datos (Prioridad Media)

**Problema Detectado:** Consultas frecuentes sin optimización pueden ralentizar el sistema en períodos de alto volumen.

**Recomendación:** Crear índices en columnas críticas:

```sql
ALTER TABLE reservas ADD INDEX idx_fecha_estado (fecha_reserva, estado);
ALTER TABLE reservas ADD INDEX idx_espacio_estado (id_espacio, estado);
ALTER TABLE planificacion_asignaturas ADD INDEX idx_periodo (periodo);
```

**Beneficio:** Mejora de rendimiento de 30-40% en consultas de ocupación.

---

## 5. RESUMEN DE CAMBIOS RECOMENDADOS

| Recomendación | Prioridad | Impacto |
|---------------|-----------|---------|
| Corrección de fórmula de ocupación (horas reales) | Alta | Precisión en medición de uso |
| Clarificación de estados de reserva | Alta | Confiabilidad de indicadores derivados |
| Validación automática de estado de espacio | Alta | Integridad de datos en tiempo real |
| Corrección de "Horas Disponibles" | Media | Mejor relación de ocupación |
| Optimización de fórmula de evolución | Media | Consistencia en toda la plataforma |
| Creación de índices en BD | Media | Rendimiento de sistema |

---

## 6. CONCLUSIONES

El sistema AulaSync implementa 14 indicadores de gestión de espacios académicos con funcionamiento operativo. Los cálculos actuales proporcionan visibilidad sobre la utilización de infraestructura.

Las mejoras recomendadas están orientadas exclusivamente a corregir y optimizar las metodologías de cálculo existentes, mejorando la precisión de los datos y la confiabilidad de los indicadores sin requerir nuevas funcionalidades.

La implementación de estas correcciones permitirá:

- Medición más precisa del uso real de espacios
- Mayor consistencia entre indicadores relacionados
- Mejor rendimiento del sistema
- Datos más confiables para la toma de decisiones administrativas

---

## 7. ANEXOS

### Anexo A: Mapeo de Tablas Utilizadas

| Tabla | Descripción | Campos Relevantes |
|-------|-------------|-------------------|
| RESERVAS | Registro de reservas de espacios | id_reserva, fecha_reserva, hora, hora_salida, estado, id_espacio |
| ESPACIOS | Catálogo de espacios académicos | id_espacio, nombre_espacio, tipo_espacio, estado |
| PLANIFICACION_ASIGNATURAS | Asignaciones de clase | id_modulo, id_espacio, id_asignatura, periodo |
| MODULOS | Bloques horarios | id_modulo, dia, hora_inicio, hora_termino |
| PROFESORES | Docentes del instituto | run_profesor, name, email |

---

**Documento Preparado por:** Equipo de Desarrollo AulaSync  
**Revisión:** 2.0  
**Fecha de Emisión:** 5 de Noviembre de 2025  
**Última Actualización:** 5 de Noviembre de 2025
