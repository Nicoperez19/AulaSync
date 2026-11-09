# Manual Técnico y Arquitectura del Sistema: AulaSync (v2.0)

> **Documento:** Manual Técnico Centralizado y Referencia de Arquitectura  
> **Sistema:**  Sistema de Información de Aulas (SIA)  
> **Versión:** 2.0  
> **Audiencia:** Desarrolladores, Administradores de Sistemas (DevOps), Soporte Técnico Especializado.

---

## Tabla de Contenidos

1. [Arquitectura General y Multitenancy](#1-arquitectura-general-y-multitenancy)
2. [Servidor de WebSockets y Tiempo Real (Laravel Reverb)](#2-servidor-de-websockets-y-tiempo-real-laravel-reverb)
3. [APIs REST de Asistencia y Espacios](#3-apis-rest-de-asistencia-y-espacios)
   - [3.1 Control de Asistencia y Apertura de Espacios (Docentes)](#31-control-de-asistencia-y-apertura-de-espacios-docentes)
   - [3.2 Asistencia de Alumnos por Escáner QR](#32-asistencia-de-alumnos-por-escáner-qr)
   - [3.3 Endpoints de Espacios y Disponibilidad](#33-endpoints-de-espacios-y-disponibilidad)
4. [Mecanismos Automatizados y Tareas Programadas (Scheduler)](#4-mecanismos-automatizados-y-tareas-programadas-scheduler)
   - [4.1 Finalización Automática de Reservas y Período de Gracia](#41-finalización-automática-de-reservas-y-período-de-gracia)
   - [4.2 Tareas Programadas en Cron/Kernel](#42-tareas-programadas-en-cronkernel)
5. [Sistema de Notificaciones y Correos Masivos](#5-sistema-de-notificaciones-y-correos-masivos)
   - [5.1 Configuración de Servidores SMTP y Colas](#51-configuración-de-servidores-smtp-y-colas)
   - [5.2 Tipos de Correos y Destinatarios](#52-tipos-de-correos-y-destinatarios)
   - [5.3 Variables Dinámicas de Plantillas](#53-variables-dinámicas-de-plantillas)
6. [Lógica de Negocio y Métricas del Dashboard](#6-lógica-de-negocio-y-métricas-del-dashboard)
   - [6.1 Estados Operativos de Espacios](#61-estados-operativos-de-espacios)
   - [6.2 Fórmulas de Cálculo de Ocupación](#62-fórmulas-de-cálculo-de-ocupación)
   - [6.3 Detección de Clases No Realizadas y Atrasos](#63-detección-de-clases-no-realizadas-y-atrasos)
   - [6.4 Optimización de Memoria en Exportaciones Excel](#64-optimización-de-memoria-en-exportaciones-excel)
7. [Mantenimiento, Comandos de Consola y Base de Datos](#7-mantenimiento-comandos-de-consola-y-base-de-datos)

---

## 1. Arquitectura General y Multitenancy

AulaSync opera bajo una arquitectura **multi-tenant (multi-inquilino)** basada en la detección del subdominio de cada sede. Esto permite aislar los registros operativos entre sedes, manteniendo una base de código común.

### 1.1 Esquema de Conexiones de Base de Datos
* **Base de datos central (`system`):** Gestiona los tenants (sedes), usuarios globales, accesos generales y metadatos de configuración.
* **Base de datos de sede (`tenant`):** Aloja los espacios, planificaciones académicas, módulos horarios, registros de asistencia, reservas y mapas de la sede respectiva.

### 1.2 Detección y Resolución de Sedes
1. El middleware `IdentifyTenant` detecta el host o subdominio de la petición entrante (por ejemplo: `chillan.aulasync.local` o `canete.aulasync.local`).
2. Se resuelve el modelo `Tenant` correspondiente.
3. Se conmuta dinámicamente la conexión `tenant` de Laravel para apuntar a la base de datos de dicha sede antes de ejecutar cualquier controlador o consulta Eloquent.

### 1.3 Asilamiento y Prefijos de Espacio
Cada espacio físico posee un prefijo alfanumérico que identifica su sede (ejemplo: `CH-` para Chillán, `CA-` para Cañete). Todas las relaciones de mapas, pisos, facultades y carreras operan contextualizadas a la sede activa.

---

## 2. Servidor de WebSockets y Tiempo Real (Laravel Reverb)

La actualización del plano interactivo y la sincronización de estados de salas operan mediante **Laravel Reverb**, reemplazando dependencias de servicios externos tipo Pusher.

### 2.1 Configuración de Entorno (.env)
```env
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=aulasync-app
REVERB_APP_KEY=aulasync-key-reverb
REVERB_APP_SECRET=aulasync-secret-reverb
REVERB_HOST="127.0.0.1"
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

### 2.2 Ejecución del Servicio
En entornos de producción o desarrollo local, el servidor de WebSockets se ejecuta mediante:
```bash
php artisan reverb:start --host=0.0.0.0 --port=8080
```
Se recomienda supervisarlo con un daemon (Systemd o Supervisor en Linux / Servicio de Windows).

### 2.3 Canales y Eventos
* **Canal Público/Presencia:** `espacios-sede.{sedeId}`
* **Evento emitido:** `EspacioEstadoCambiado` (notifica cambio de disponibilidad, apertura por docente, cierre forzado o reserva espontánea).

---

## 3. APIs REST de Asistencia y Espacios

### 3.1 Control de Asistencia y Apertura de Espacios (Docentes)

Gestiona la apertura y devolución de llaves en recepción mediante el escaneo de la cédula de identidad del docente y el código QR de la sala.

#### Endpoints Principales
* **Verificar Asignación de Sala:**
  ```http
  GET /api/verificar-espacio/{profesorId}/{espacioId}?dia=Lunes&hora=08:30:00
  ```
  Verifica si el docente tiene una clase regular planificada en dicho espacio y bloque.

* **Registrar Apertura / Entrada:**
  ```http
  POST /api/registrar-entrada-clase
  POST /api/registrar-uso-espacio
  Content-Type: application/json

  {
    "run": "12345678-9",
    "id_espacio": 15,
    "tipo_uso": "clase_programada"
  }
  ```

* **Registrar Salida / Devolución de Llave:**
  ```http
  POST /api/registrar-salida-clase
  Content-Type: application/json

  {
    "run": "12345678-9",
    "id_espacio": 15
  }
  ```

* **Liberar Sala Retenida y Registrar Nuevo Uso:**
  ```http
  POST /api/liberar-y-registrar-uso
  Content-Type: application/json

  {
    "run_nuevo_profesor": "18999888-7",
    "id_espacio": 15,
    "motivo": "Docente anterior no devolvió llave a tiempo"
  }
  ```
  Permite al recepcionista liberar de inmediato un espacio bloqueado por un turno anterior y registrar la apertura para el nuevo docente sin inconsistencias.

---

### 3.2 Asistencia de Alumnos por Escáner QR

Permite registrar automáticamente la asistencia estudiantil en clases y salas de estudio.

#### Modos de Funcionamiento
1. **Clase Planificada (Con Reserva Activa):**
   * El alumno escanea el QR de la sala.
   * El sistema detecta la clase en curso y registra la asistencia inmediatamente.
   * No requiere marcar salida obligatoria; se consolida al término del bloque horario.
2. **Entrada Espontánea / Sala de Estudio:**
   * Al no haber clase formal, el sistema registra entrada espontánea.
   * El alumno debe volver a escanear al retirarse para computar el tiempo de permanencia.

#### Endpoints
* **Marcar Entrada:** `POST /api/student-attendance/entrada` (Payload: `rut_alumno`, `id_espacio`).
* **Marcar Salida:** `POST /api/student-attendance/salida` (Payload: `rut_alumno`, `id_espacio`).
* **Acción Alternada (Toggle automático):** `POST /api/student-attendance/toggle` (Detecta si el alumno está dentro para registrar salida o fuera para entrada).
* **Consultar Alumnos Presentes:** `GET /api/student-attendance/espacio/{idEspacio}/presentes`.
* **Historial de Alumno:** `GET /api/student-attendance/historial/{rutAlumno}`.

---

### 3.3 Endpoints de Espacios y Disponibilidad

* **Módulos Disponibles de un Espacio:** `GET /api/espacio/{idEspacio}/modulos-disponibles`.
* **Verificar Conflictos de Planificación:** `POST /quick-actions/api/verificar-conflictos`.
* **Búsqueda Dinámica de Personas:** `GET /quick-actions/api/buscar-personas?q=...`.
* **Búsqueda Dinámica de Asignaturas:** `GET /quick-actions/api/buscar-asignaturas?q=...`.

---

## 4. Mecanismos Automatizados y Tareas Programadas (Scheduler)

### 4.1 Finalización Automática de Reservas y Período de Gracia

Para evitar que las salas queden bloqueadas permanentemente cuando un docente olvida registrar la devolución de llaves, el sistema ejecuta el comando de auto-finalización.

#### Reglas de Expiración
1. Se evalúa la hora de término del último módulo asociado a la reserva.
2. Se añade un **período de gracia** configurable (por defecto 10 minutos para reservas cortas o hasta 60 minutos según política de sede).
3. Si la reserva continúa en estado `activa` o `en_curso` y ha superado dicho margen, el sistema:
   * Cambia el estado de la reserva a `finalizada_automatica`.
   * Registra en auditoría la hora efectiva de cierre.
   * Libera el espacio en el plano digital (`estado = 'Disponible'`).
   * Emite el evento WebSocket para actualizar las interfaces visuales en tiempo real.

### 4.2 Tareas Programadas en Cron/Kernel

El programador de Laravel (`app/Console/Kernel.php`) debe configurarse en el servidor para ejecutarse cada minuto:

```bash
# Configuración en Crontab de Linux
* * * * * cd /ruta/al/proyecto && php artisan schedule:run >> /dev/null 2>&1
```

#### Comandos Registrados
* `reservas:finalizar-expiradas`: Corre cada minuto para auditar y cerrar reservas vencidas.
* `asistencia:verificar-clases-no-realizadas`: Corre al finalizar cada bloque académico para marcar ausencias docentes no justificadas.
* `correos:procesar-masivos`: Procesa la cola de envíos masivos programados.

---

## 5. Sistema de Notificaciones y Correos Masivos

### 5.1 Configuración de Servidores SMTP y Colas

El envío de notificaciones y reportes se delega al sistema de colas para no bloquear las solicitudes HTTP de los usuarios.

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=notificaciones@institucion.edu
MAIL_PASSWORD=token_de_aplicacion_seguro
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="no-reply@institucion.edu"
MAIL_FROM_NAME="AulaSync - Sistema de Aulas"

QUEUE_CONNECTION=database
```

Para procesar las colas en segundo plano:
```bash
php artisan queue:work --tries=3 --timeout=90
```

### 5.2 Tipos de Correos y Destinatarios
Administrable desde la ruta `/correos-masivos` (Livewire):
* **Sistema (Predefinidos):** Alertas de inasistencias a directores, confirmación de reserva manual a solicitantes, informe semanal de clases no realizadas.
* **Personalizados (Custom):** Comunicados masivos a docentes por corte de suministro o actividades extraordinarias.
* **Categorías de Destinatarios:** Docentes, Asistentes Académicos, Jefes de Carrera, Correos externos institucionales.

### 5.3 Variables Dinámicas de Plantillas
Las plantillas admiten marcadores que se sustituyen en tiempo de compilación:
* `{nombre_destinatario}`: Nombre completo de la persona.
* `{nombre_asignatura}`: Nombre de la materia o actividad.
* `{codigo_espacio}` / `{nombre_espacio}`: Identificador de la sala o laboratorio.
* `{fecha_reserva}` / `{hora_inicio}` / `{hora_termino}`: Coordenadas temporales.
* `{motivo}`: Justificación de ausencia o reserva.
* `{enlace_comprobante}`: URL firmada de descarga del comprobante PDF.

---

## 6. Lógica de Negocio y Métricas del Dashboard

### 6.1 Estados Operativos de Espacios
1. **Disponible:** Libre para uso, sin reservas ni clases activas en el módulo actual.
2. **Ocupado:** Con clase programada en curso o reserva manual vigente (llave entregada).
3. **Reservado:** Espacio programado para el bloque actual, a la espera de que el docente registre su entrada.
4. **Mantenimiento:** Espacio bloqueado administrativamente por reparaciones o condiciones técnicas.

### 6.2 Fórmulas de Cálculo de Ocupación

#### Porcentaje de Ocupación Semanal
$$\text{Ocupación (\%)} = \frac{\text{Total de Bloques-Horas Utilizados}}{\text{Total de Bloques-Horas Operativos Disponibles}} \times 100$$

* **Jornada Diurna:** Comprende los módulos matutinos y vespertinos tempranos (generalmente módulos 1 a 14).
* **Jornada Vespertina:** Comprende los módulos nocturnos (módulos 15 en adelante).

### 6.3 Detección de Clases No Realizadas y Atrasos
* **Atraso:** Se marca si la entrada del docente se produce posterior a los primeros 15 minutos de iniciado el módulo.
* **Clase No Realizada:** Se clasifica automáticamente cuando:
  1. Existe una clase planificada en el horario oficial para esa fecha y módulo.
  2. No existe registro de entrada docente antes de finalizar el módulo o su período de tolerancia.
  3. No existe una licencia o ausencia registrada previamente que justifique la sesión.

### 6.4 Optimización de Memoria en Exportaciones Excel
Las exportaciones de registros históricos de asistencia y clases no realizadas emplean `FastExcel` o generadores basados en streams (`yield` / `chunk(500)`), garantizando un consumo constante inferior a 64 MB de RAM incluso con planillas superiores a 50.000 filas.

---

## 7. Mantenimiento, Comandos de Consola y Base de Datos

### 7.1 Limpieza y Reinicio de Horarios Semestrales
Si se requiere recargar la planificación de un semestre por modificaciones curriculares:

```bash
# Limpiar horarios y planificaciones de un período específico:
php artisan schedules:clear 2026-1
```

*Nota: La carga masiva vía web (`DataLoadController`) incluye esta limpieza previa de forma atómica para el período seleccionado.*

### 7.2 Migraciones y Ejecución en Tenants
Para aplicar migraciones sobre todas las bases de datos de sedes:
```bash
php artisan tenants:artisan "migrate"
```

### 7.3 Sincronización del Calendario de Feriados
El sistema consume la API pública de feriados de Chile (`https://apis.digital.gob.cl/fl/feriados/{año}`).  
Para reiniciar o vaciar feriados en todos los tenants mediante `php artisan tinker`:

```php
foreach (\App\Models\Tenant::all() as $tenant) {
    config(['database.connections.tenant.database' => $tenant->database]);
    app('db')->purge('tenant');
    \DB::connection('tenant')->table('dias_feriados')->truncate();
    echo "✓ Feriados limpiados para: {$tenant->name}\n";
}
```
Posteriormente, reimportar desde la interfaz de administración en `/dias-feriados`.
