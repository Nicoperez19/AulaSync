---
title: Resumen Ejecutivo - Cálculos del Sistema AulaSync
author: Equipo de Desarrollo
date: 5 de Noviembre de 2025
---

# RESUMEN EJECUTIVO

**Institución:** Instituto Tecnológico Universidad Católica de la Santísima Concepción  
**Destinatario:** Subdirector del Instituto Tecnológico - Sede Talcahuano  
**Asunto:** Metodologías de Cálculo - Sistema AulaSync

---

## INDICADORES MONITOREADOS (14 Métricas)

El sistema AulaSync calcula en tiempo real 14 indicadores para la gestión de espacios académicos:

1. **Ocupación Semanal (%)**
   - Se divide el total de reservas activas de la semana entre la capacidad total disponible (todos los espacios por 5 días considerando 8 horas diarias) para obtener un porcentaje de uso real de la infraestructura.
   
   $$\text{Ocupación Semanal} = \frac{\text{Reservas Activas Semana}}{\text{Espacios} \times 5 \text{ días} \times 8 \text{ horas}} \times 100\%$$

2. **Ocupación Mensual (%)**
   - Similar al anterior, pero considerando todo el mes calendario. Se suman todas las reservas del mes y se dividen entre la capacidad total disponible (espacios por días hábiles del mes considerando 8 horas).
   
   $$\text{Ocupación Mensual} = \frac{\text{Reservas Activas Mes}}{\text{Espacios} \times \text{Días Laborales} \times 8 \text{ horas}} \times 100\%$$

3. **Horas Utilizadas**
   - Muestra cuántas reservas se registraron hoy y las compara con las 40 horas disponibles en una jornada laboral (5 días por 8 horas). Permite ver si el día está siendo bien aprovechado.
   
   $$\text{Horas Utilizadas} = \frac{\text{Reservas Hoy}}{40 \text{ horas disponibles}} \times 100\%$$

4. **Salas Ocupadas/Desocupadas**
   - Contabiliza instantáneamente cuántos espacios están actualmente ocupados (en uso) y cuántos están disponibles (libres). Refleja el estado en tiempo real de la infraestructura.

5. **Uso por Día**
   - Cuenta cuántas reservas existen para cada día de la semana (lunes a sábado). Ayuda a identificar patrones de demanda por día específico.

6. **Comparativa por Tipo**
   - Analiza la ocupación separando por categoría de espacio (Aulas, Laboratorios, Auditorios, etc.). Calcula el porcentaje de uso para cada tipo.
   
   $$\text{Ocupación por Tipo} = \frac{\text{Reservas del Tipo}}{\text{Espacios Totales del Tipo}} \times 100\%$$

7. **Evolución Semanal**
   - Muestra la tendencia de ocupación durante los últimos 10 días. Permite ver si el uso va aumentando, disminuyendo o se mantiene estable.

8. **Reservas por Tipo**
   - Suma el total de reservas registradas para cada categoría de espacio. Útil para entender dónde se concentra la demanda.

9. **Reservas No Utilizadas**
   - Identifica las reservas de salas de clases que fueron programadas pero donde los profesores no asistieron (no-show). Importante para evaluar la confiabilidad del sistema de reservas y detectar incumplimientos de profesores.

10. **Horarios en Ejecución**
    - Cuenta cuántas clases o actividades están siendo dictadas en este momento. Se verifica que el horario actual coincida con el inicio y término de cada reserva.

11. **Promedio de Duración**
    - Calcula el tiempo promedio que dura cada reserva. Se obtiene del promedio de diferencia entre la hora de término y la hora de inicio de todas las reservas.
    
    $$\text{Promedio Duración} = \text{AVG}(\text{hora}_\text{término} - \text{hora}_\text{inicio})$$

12. **Porcentaje No-Show**
    - Divide el total de reservas donde los profesores no asistieron entre el total de reservas para obtener un porcentaje. Indicador de la tasa de incumplimiento de profesores en asistencia a salas reservadas.
    
    $$\text{Porcentaje No-Show} = \frac{\text{Reservas No Utilizadas}}{\text{Total Reservas}} \times 100\%$$

13. **No-Show por Tipo**
    - Desglosa las no-asistencias de profesores por categoría de espacio. Muestra cuál tipo de sala tiene mayor tasa de profesores que no se presentan a usar la reserva.
    
    $$\text{No-Show por Tipo} = \frac{\text{No-Show del Tipo}}{\text{Total No-Show}} \times 100\%$$

14. **Total Reservas Hoy**
    - Cuenta simplemente cuántas reservas se registraron en la fecha actual, independientemente de su estado.

---

## MEJORAS COMPROMETIDAS

### Mejoras Críticas (Afectan Precisión de Datos)

1. **Optimización del cálculo de Ocupación**
   - **Situación Actual:** La ocupación se cuenta por número de reservas, no por horas reales utilizadas. Una reserva de 8 horas se cuenta como 1 unidad, lo que subestima el uso real.
   - **Compromiso:** Implementaremos un cálculo de ocupación basado en horas reales utilizadas (TIMESTAMPDIFF), tras aprobación de esta propuesta. Esto proporcionará métricas más precisas.

2. **Corrección de Horario de Operación de Salas**
   - **Situación Actual:** El sistema asume 8 horas diarias de operación por sala (estándar 8:00-16:00), pero las salas operan realmente desde 8:10 hasta 23:00, lo que equivale a aproximadamente 14.8 horas diarias. Esta diferencia genera una imprecisión significativa en todos los cálculos de ocupación.
   - **Compromiso:** Actualizaremos los parámetros de tiempo de operación a los horarios reales (8:10-23:00) en todas las fórmulas de ocupación, mejorando sustancialmente la precisión del cálculo estadístico.

3. **Sincronización de Estado de Espacios**
   - **Situación Actual:** El estado de un espacio (Ocupado/Disponible) puede desincronizarse con la ocupación real si hay interrupciones.
   - **Compromiso:** Implementaremos una actualización automática del estado cuando finaliza una reserva, asegurando integridad en tiempo real.

### Mejoras de Rendimiento

4. **Optimización de Base de Datos**
   - **Situación Actual:** Las consultas de ocupación pueden ser lentas en periodos de alto volumen de reservas.
   - **Compromiso:** Crearemos índices en campos críticos (fecha_reserva, estado, tipo_espacio) para mejorar velocidad de consultas.

5. **Estandarización de Fórmula de Evolución**
   - **Situación Actual:** La fórmula de evolución multiplica el número de reservas por 12.5 para "convertir" a porcentaje. Este factor es arbitrario (asume un máximo de 8 reservas por día) e inconsistente con otros indicadores que calculan porcentajes de forma real basados en capacidad disponible.
   - **Compromiso:** Implementaremos un cálculo de porcentaje real alineado con las fórmulas de ocupación semanal y mensual, tras aprobación ejecutiva.

---

## Prioridad de Implementación

| Orden | Acción | Impacto |
|-------|--------|---------|
| 1 | Corrección de horario de operación (8:10-23:00) | Mejora precisión |
| 2 | Cambiar cálculo de ocupación a horas reales | Mejora precisión |
| 3 | Automatizar estado de espacios | Mejora integridad en tiempo real |
| 4 | Crear índices de BD | Mejora rendimiento |
| 5 | Homologar fórmula de evolución | Mejora consistencia |

---

## CONCLUSIÓN

El sistema es operativo con 14 indicadores funcionales. Las recomendaciones se enfocan exclusivamente en corregir cálculos existentes para mejorar precisión y rendimiento, sin requerir nuevas funcionalidades.

---

**Documento Preparado por:** Equipo de Desarrollo AulaSync  
**Fecha:** 5 de Noviembre de 2025
