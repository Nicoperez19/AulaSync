# Plan Maestro de Proyecto y Roadmap a 6 Meses — AulaSync

> **Fecha de elaboración:** 19 de noviembre de 2026  
> **Alcance:** Consolidación de ramas, estabilización técnica, evolución funcional, Carta Gantt (24 semanas) y presupuesto en pesos chilenos (CLP) para **1 profesional Full-Stack**.

---

## 1. Diagnóstico del Repositorio y Estrategia de Ramas

El repositorio cuenta actualmente con **10 ramas** sincronizadas entre local y `origin`:

| Rama | Último Commit | Estado / Contenido Principal | Estrategia Recomendada |
| :--- | :--- | :--- | :--- |
| **`fix_limpieza`** | `2026-11-18` | **Rama más avanzada.** Limpieza de código muerto, modularización JS/CSS con Vite (80 módulos), eliminación de `@php` y `<script>` inline en vistas, optimización multi-tenant/sidebar/dashboard/plano digital y fix de detección por ventana horaria en Control Docente. | Usar como **fuente principal** para promover hacia `QA`, `DEV` y `main`. |
| **`fix_funcionando`** | `2026-11-17` | Rama productiva estable con la corrección de Control Docente (ventana horaria y asignatura). | Mantener como **respaldo estable** hasta certificar `fix_limpieza` en `QA`. |
| **`QA`** | `2026-11-12` | Mejoras de exportación Excel y PDF previas a la refactorización de `fix_limpieza`. | Mergear `fix_limpieza` → `QA` resolviendo posibles conflictos en reportes. |
| **`TCartes`** | `2026-11-09` | Avances de colaborador previos a `fix_funcionando`. | Auditar diff puntual contra `main`; rescatar cambios útiles y archivar. |
| **`Vlemus`** | `2026-10-31` | Desarrollo de "Sistema de Reporte". | Verificar cobertura en `fix_limpieza` (`ReportController` / reportes) y archivar. |
| **`DEV`** | `2026-10-26` | Desactualizada (último merge desde `fix_funcionando` en octubre). | Sincronizar desde `fix_limpieza` / `QA` como rama de integración continua. |
| **`main`** | `2026-10-26` | Desactualizada respecto a `fix_funcionando` y `fix_limpieza`. | Promover versión certificada al cierre del Mes 1 mediante tag `v2.0.0`. |
| **`NPerez`** | `2026-06-13` | Rama histórica de desarrollo (base documental actual). | Conservar documentación/planificación o integrar a `main` y limpiar. |
| **`Caguilar`** | `2025-08-29` | Rama histórica (`modulos-actuales-table`). | Archivar con tag o eliminar tras respaldo. |
| **`Layala`** | `2025-08-27` | Rama histórica (`feature: eliminar reserva ocupada`). | Archivar con tag o eliminar tras respaldo. |

### Flujo de Promoción Git Propuesto
1. `fix_limpieza` → merge a `QA` (pruebas integrales multi-sede).
2. Revisión de `TCartes` / `Vlemus` (cherry-pick selectivo si aplica).
3. `QA` → merge a `DEV` y luego a `main` con etiqueta semántica (`v2.0.0`).
4. Limpieza de ramas obsoletas (`Caguilar`, `Layala`, y posteriormente `TCartes`, `Vlemus` una vez auditadas).

---

## 2. Inventario Actual del Sistema

### Módulos Operativos Implementados
- **Multi-Tenancy por Sede:** `TenantMiddleware`, `TenantScope`, `TenantInitializationController` (aislamiento de BD central y bases de datos por sede).
- **Plano Digital Interactivo:** `PlanoDigitalController` con visualización en canvas, lector QR de entrada/salida, reservas espontáneas y programadas, mantención, vetos y gestión de salas de estudio.
- **Dashboard Ejecutivo:** `DashboardController` con módulos actuales en vivo, matriz de ocupación semanal (`OcupacionSemanalService`, exportación Excel/PDF) y gráfico de estado de clases.
- **Control Docente y Auditoría:** `ControlDocenteController`, `ClasesNoRealizadasController`, `AuditoriaClaseController`, comandos `DetectarClasesNoRealizadas`, `ActualizarHorasAtrasos` y reportes semanales/mensuales.
- **Gestión Académica y Operativa:** mantenedores de espacios, mapas, asignaturas, horarios, profesores, profesores colaboradores, feriados, carga masiva de datos/estudiantes, correos masivos, tickets de soporte y manual.

---

## 3. Brechas Técnicas y Deuda Pendiente

1. **Ausencia de Suite de Pruebas Automatizadas:**
   - La carpeta `tests/` solo contiene `CreatesApplication.php` y `TestCase.php` (0 pruebas unitarias o de integración).
   - Flujos críticos sin cobertura automatizada: detección de clases no realizadas (múltiples clases en el día), reservas simultáneas, escaneo QR y aislamiento `TenantScope`.
2. **Limpieza Residual en Capa de Servicios:**
   - Métodos sin invocación detectados en `AuditoriaService` (`getLoteActivo`), `CorreoAdministrativoService` (`getCorreoPorEspacio`, `getCorreoPorProfesor`, `limpiarTodoElCache`) y `OccupancyService` (`calcularModulosReales`, `obtenerSalasOcupadas`, entre otros).
3. **Operación y Rendimiento en Producción:**
   - Habilitar **OPcache** en PHP/Apache, pre-compilación (`php artisan optimize`) y despliegue sin `public/hot` (usando únicamente `public/build/`).
   - Asegurar ejecución real mediante **Cron + Supervisor** de los comandos programados (`DetectarClasesNoRealizadas`, `FinalizarReservasExpiradas`, `DesactivarProfesoresColaboradoresVencidos`, `GenerarSnapshotOcupacionSemanal`).
   - Implementar respaldos automáticos diarios de la BD central y todas las BDs tenant con retención y prueba de restauración.
4. **Seguridad y Trazabilidad:**
   - Rate-limiting en endpoints de escaneo QR y APIs públicas/internas.
   - Auditoría de permisos Spatie por sede y rotación periódica de tokens/credenciales de servicio.

---

## 4. Ideas Nuevas y Evolución Funcional (Meses 3 al 6)

1. **Plano Digital en Tiempo Real (WebSockets con Laravel Reverb):**
   - Reemplazar el polling HTTP cada 5 segundos (`/plano/{id}/bloques`) por eventos push en tiempo real cuando ocurre un escaneo QR, reserva o liberación de espacio.
2. **Modo Kiosco / PWA Resiliente para Tótems:**
   - Interfaz dedicada de pantalla completa para lectores QR con reconexión automática, heartbeat de estado y cola local ante micro-cortes de red.
3. **Portal Móvil de Autogestión (Salas de Estudio y Docentes):**
   - Reserva de salas de estudio desde el celular con QR dinámico temporal y check-in presencial.
   - Recordatorios automáticos a docentes (inicio/término de módulo y alerta temprana de atraso).
4. **Analítica Predictiva de Ocupación y Reasignación Inteligente:**
   - Detección de salas subutilizadas o sobrecargadas (capacidad física vs matrícula/asistencia real) con sugerencias automáticas de reasignación por bloque horario.
5. **Integración Institucional (SSO + Sincronización Académica):**
   - Autenticación única institucional (SAML / OAuth2 / Azure AD) y sincronización automatizada (API/ETL) de horarios, asignaturas y docentes desde el sistema académico central.

---

## 5. Carta Gantt Detallada (6 Meses / 24 Semanas)

| Mes | Semanas | Hito / Entregable Principal | Actividades Específicas | KPI / Criterio de Éxito |
| :--- | :---: | :--- | :--- | :--- |
| **Mes 1** | S1–S2 | **Consolidación Git y Limpieza Final** | Auditar `TCartes`/`Vlemus`, mergear `fix_limpieza` → `QA` → `DEV` → `main`, limpiar métodos muertos de Services y archivar ramas viejas. | 1 rama productiva (`main`) unificada y etiquetada (`v2.0.0`). |
| **Mes 1** | S3–S4 | **Hardening de Servidor y Automatización** | Configurar OPcache, `php artisan optimize`, despliegue con `npm run build` sin `public/hot`, Cron + Supervisor para scheduler. | Tiempo de respuesta promedio `< 300ms` en rutas cacheadas; 100% de cron jobs activos. |
| **Mes 2** | S5–S6 | **Suite Base de Pruebas (PHPUnit)** | Escribir tests Feature/Unit para Control Docente, Reservas, QR entrada/salida y aislamiento `TenantScope`. | >= 70% de cobertura en flujos críticos; CI pasando en cada push. |
| **Mes 2** | S7–S8 | **Seguridad API/QR y Backups** | Rate-limiting en rutas QR/API, auditoría de roles/permisos por sede, scripts de backup diario central + tenants con prueba de restore. | 0 endpoints críticos sin throttle; backup automatizado verificado. |
| **Mes 3** | S9–S10 | **Tiempo Real con Laravel Reverb** | Implementar broadcasting de cambios de estado en Plano Digital eliminando polling de 5s. | Latencia de actualización visual `< 1s`; reducción > 80% de tráfico HTTP de polling. |
| **Mes 3** | S11–S12 | **Índices BD y Modo Kiosco PWA** | Crear índices compuestos en tablas tenant (`reservas`, `planificaciones_asignaturas`, `clases_no_realizadas`) + modo kiosco resiliente. | Consultas críticas `< 20ms`; operación estable en tótems QR. |
| **Mes 4** | S13–S14 | **Portal Móvil Salas de Estudio** | Flujo responsive de reserva estudiantil con QR dinámico temporal y validación de asistencia. | Reserva y check-in móvil completado en `< 30s`. |
| **Mes 4** | S15–S16 | **Alertas y Recordatorios Docentes** | Notificaciones programadas de inicio/término de módulo, registro pendiente de salida y recuperación de clases. | Reducción medible de salidas no registradas. |
| **Mes 5** | S17–S18 | **Analítica de Subutilización** | Motor estadístico de uso real vs capacidad instalada por franja horaria, tipo de espacio y escuela. | Tablero analítico operativo con detección de brechas de capacidad. |
| **Mes 5** | S19–S20 | **Sugerencias y Reportes Multi-Sede** | Recomendador de reasignación de salas + comparativas ejecutivas descargables (Excel/PDF) entre sedes. | Exportación consolidada multi-sede en `< 5s`. |
| **Mes 6** | S21–S22 | **Integración SSO / ETL Académico** | Conector de autenticación institucional y sincronización programada de horarios/asignaturas/docentes. | Sincronización automática sin carga manual de CSV/Excel. |
| **Mes 6** | S23–S24 | **Pruebas de Carga y Cierre** | Pruebas de estrés simulando concurrencia multi-sede, documentación técnica/operativa final y marcha blanca. | Estabilidad comprobada bajo carga pico; entrega formal cerrada. |

### Vista Visual del Cronograma (Semanas 1 a 24)

| Actividad | S1–S2 | S3–S4 | S5–S6 | S7–S8 | S9–S10 | S11–S12 | S13–S14 | S15–S16 | S17–S18 | S19–S20 | S21–S22 | S23–S24 |
| :--- | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: |
| Unificación Git y Limpieza | ██ | | | | | | | | | | | |
| Deploy OPcache + Cron/Supervisor | | ██ | | | | | | | | | | |
| Tests Automatizados (PHPUnit) | | | ██ | | | | | | | | | |
| Seguridad API/QR + Backups | | | | ██ | | | | | | | | |
| WebSockets Laravel Reverb | | | | | ██ | | | | | | | |
| Índices MySQL + Modo Kiosco PWA | | | | | | ██ | | | | | | |
| Portal Móvil Salas de Estudio | | | | | | | ██ | | | | | |
| Notificaciones / Alertas Docentes | | | | | | | | ██ | | | | |
| Analítica Predictiva de Espacios | | | | | | | | | ██ | | | |
| Reportes Ejecutivos Multi-Sede | | | | | | | | | | ██ | | |
| Integración SSO / ETL Académico | | | | | | | | | | | ██ | |
| Pruebas de Carga y Marcha Blanca | | | | | | | | | | | | ██ |

---

## 6. Presupuesto Detallado en Pesos Chilenos (CLP) — 1 Profesional

Base de cálculo para **1 Desarrollador Full-Stack (Laravel + Livewire + Vite/JS + MySQL + DevOps)** con tarifa de referencia de **\$18.000 CLP/hora**.

### Escenario A: Dedicación Full-Time (160 hrs/mes — 960 hrs en 6 meses)

| Mes | Paquete de Actividades | Horas | Valor Hora | Total Mes (CLP) |
| :--- | :--- | :---: | :---: | :---: |
| **Mes 1** | Unificación Git, limpieza Services, OPcache, Vite build y Cron/Supervisor | 160 | \$18.000 | **\$2.880.000** |
| **Mes 2** | Suite de pruebas automatizadas, hardening de seguridad API/QR y backups BD | 160 | \$18.000 | **\$2.880.000** |
| **Mes 3** | Laravel Reverb (tiempo real), índices compuestos MySQL y Modo Kiosco PWA | 160 | \$18.000 | **\$2.880.000** |
| **Mes 4** | Portal móvil Salas de Estudio (QR dinámico) y notificaciones/alertas docentes | 160 | \$18.000 | **\$2.880.000** |
| **Mes 5** | Analítica predictiva de ocupación, sugerencias de reasignación y reportes multi-sede | 160 | \$18.000 | **\$2.880.000** |
| **Mes 6** | Integración SSO/ETL académico, pruebas de estrés, documentación y marcha blanca | 160 | \$18.000 | **\$2.880.000** |
| **Total Mano de Obra (Escenario Full-Time)** | | **960 hrs** | | **\$17.280.000** |

### Escenario B: Dedicación Part-Time (80 hrs/mes — 480 hrs en 6 meses)
Recomendado si se prioriza estabilización + los 2 o 3 módulos nuevos de mayor impacto institucional:
- **Costo mensual:** **\$1.440.000 CLP/mes**
- **Total 6 meses:** **\$8.640.000 CLP**

### Costos Adicionales Referenciales (Infraestructura y Contingencia)

| Ítem | Detalle | Mensual (CLP) | 6 Meses (CLP) |
| :--- | :--- | :---: | :---: |
| **VPS / Cloud Producción** *(si no es on-premise)* | 4 vCPU, 8 GB RAM, SSD NVMe + snapshots | \$45.000 – \$75.000 | \$270.000 – \$450.000 |
| **Respaldo Externo (S3 / Object Storage)** | Backups diarios cifrados central + tenants | \$10.000 – \$20.000 | \$60.000 – \$120.000 |
| **Fondo de Contingencia (10% Escenario A)** | Ajustes de integración institucional / hardware QR | — | **\$1.728.000** |

> **Nota tributaria (Chile):** Si la contratación es mediante **boleta de honorarios**, definir explícitamente si los valores acordados son **líquidos** o **brutos**, considerando la tasa de retención legal vigente del SII.

---

## 7. Matriz de Riesgos y Mitigación

| Riesgo | Impacto | Probabilidad | Plan de Mitigación |
| :--- | :---: | :---: | :--- |
| Conflictos al consolidar `TCartes` / `Vlemus` sobre `fix_limpieza` | Medio | Media | Auditar diffs archivo por archivo en rama temporal antes de mergear a `QA`. |
| Caída temporal de red en tótems de lectura QR | Alto | Media | Implementar Modo Kiosco PWA con caché local, reconexión automática y alertas de estado. |
| Regresiones en lógica de Control Docente / Horarios | Alto | Baja | Congelar reglas en tests automatizados de PHPUnit desde el Mes 2 antes de nuevas features. |
| Dependencia de terceros para acceso SSO / API académica | Medio | Alta | Desacoplar conector mediante interfaz/adaptador y mantener carga Excel/CSV como fallback. |
