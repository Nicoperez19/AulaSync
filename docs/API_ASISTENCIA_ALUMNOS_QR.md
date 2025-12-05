# API de Asistencia de Alumnos mediante Escáner QR

## Descripción General

Esta API permite registrar la asistencia de alumnos en salas. El escáner QR es simplemente el método de entrada de datos - escanea códigos QR que contienen el RUT del alumno y el ID del espacio, y envía estos datos a la API mediante peticiones POST estándar.

El sistema detecta automáticamente si hay una clase planificada (reserva activa) o si es una entrada espontánea.

## Lógica de Funcionamiento

### Clase Planificada (con reserva activa)
- El alumno escanea el QR de la sala
- El sistema detecta que hay una reserva activa (clase en curso)
- Se registra la asistencia automáticamente
- El alumno queda marcado como "presente" durante toda la clase
- **NO necesita marcar salida** - la asistencia se finaliza cuando termina la clase

### Entrada Espontánea (sin reserva)
- El alumno escanea el QR de la sala
- El sistema detecta que NO hay reserva activa
- Se registra una entrada espontánea
- **DEBE marcar salida** - escanear nuevamente el QR al retirarse
- Esto permite trackear el tiempo de permanencia en la sala

### Módulos Aledaños
- Si una clase abarca varios módulos consecutivos (ej: 2 módulos de 50 min cada uno), se considera una sola clase
- El alumno queda presente durante todos los módulos de la clase

---

## Endpoints

### 1. Marcar Entrada de Alumno

**Endpoint:** `POST /api/student-attendance/entrada`

**Descripción:** Registra la entrada de un alumno a una sala.

**Request Body:**
```json
{
    "rut_alumno": "12345678-9",
    "id_espacio": "SALA-101",
    "nombre_alumno": "Juan Pérez" // Opcional
}
```

**Respuesta Exitosa (Clase Planificada) - 201:**
```json
{
    "success": true,
    "message": "Asistencia registrada correctamente",
    "data": {
        "tipo": "planificada",
        "asistencia": {
            "id": 123,
            "rut_alumno": "123456789",
            "nombre_alumno": "Juan Pérez",
            "hora_entrada": "10:15",
            "hora_salida": null,
            "tipo": "planificada",
            "estado": "presente",
            "fecha": "2025-12-05",
            "espacio": "SALA-101",
            "reserva_id": "R2025120510150001",
            "asignatura": "Programación I"
        },
        "reserva": {
            "id": "R2025120510150001",
            "tipo": "clase",
            "profesor": "Prof. María González",
            "asignatura": "Programación I",
            "hora_fin_estimada": "11:30"
        },
        "espacio": {
            "id": "SALA-101",
            "nombre": "Sala 101 - Laboratorio"
        },
        "accion_requerida": "ninguna",
        "mensaje_usuario": "Tu asistencia quedó registrada para toda la clase"
    }
}
```

**Respuesta Exitosa (Entrada Espontánea) - 201:**
```json
{
    "success": true,
    "message": "Entrada espontánea registrada. Recuerda marcar tu salida al retirarte.",
    "data": {
        "tipo": "espontanea",
        "asistencia": {
            "id": 124,
            "rut_alumno": "123456789",
            "nombre_alumno": "Juan Pérez",
            "hora_entrada": "14:30",
            "hora_salida": null,
            "tipo": "espontanea",
            "estado": "presente",
            "fecha": "2025-12-05",
            "espacio": "SALA-101"
        },
        "espacio": {
            "id": "SALA-101",
            "nombre": "Sala 101 - Laboratorio"
        },
        "accion_requerida": "marcar_salida",
        "mensaje_usuario": "No hay clase programada. Debes escanear el QR nuevamente cuando te retires para registrar tu salida."
    }
}
```

**Error - Alumno ya presente en la misma sala - 409:**
```json
{
    "success": false,
    "message": "Ya tienes asistencia registrada en esta sala",
    "data": {
        "asistencia": { ... },
        "accion_requerida": "marcar_salida"
    }
}
```

**Error - Alumno presente en otra sala - 409:**
```json
{
    "success": false,
    "message": "Ya tienes asistencia activa en otra sala. Debes marcar salida primero.",
    "data": {
        "sala_actual": "Sala 102 - Biblioteca",
        "hora_entrada": "09:00"
    }
}
```

---

### 2. Marcar Salida de Alumno

**Endpoint:** `POST /api/student-attendance/salida`

**Descripción:** Registra la salida de un alumno de una sala.

**Request Body:**
```json
{
    "rut_alumno": "12345678-9",
    "id_espacio": "SALA-101"
}
```

**Respuesta Exitosa - 200:**
```json
{
    "success": true,
    "message": "Salida registrada correctamente",
    "data": {
        "asistencia": {
            "id": 124,
            "rut_alumno": "123456789",
            "nombre_alumno": "Juan Pérez",
            "hora_entrada": "14:30",
            "hora_salida": "16:45",
            "tipo": "espontanea",
            "estado": "finalizado",
            "fecha": "2025-12-05",
            "espacio": "SALA-101"
        },
        "duracion_minutos": 135,
        "mensaje_usuario": "Tu salida ha sido registrada. ¡Hasta pronto!"
    }
}
```

**Error - Clase aún en curso - 400:**
```json
{
    "success": false,
    "message": "La clase aún está en curso. Tu asistencia quedará registrada hasta que termine.",
    "data": {
        "tipo": "planificada",
        "hora_fin_estimada": "11:30"
    }
}
```

**Error - Sin entrada activa - 404:**
```json
{
    "success": false,
    "message": "No tienes una entrada activa en esta sala"
}
```

---

### 3. Toggle Entrada/Salida (Recomendado para Escáner)

**Endpoint:** `POST /api/student-attendance/toggle`

**Descripción:** Detecta automáticamente si el alumno debe registrar entrada o salida. Ideal para un flujo simple de "escanear y listo".

**Request Body:**
```json
{
    "rut_alumno": "12345678-9",
    "id_espacio": "SALA-101",
    "nombre_alumno": "Juan Pérez" // Opcional
}
```

**Lógica:**
1. Si NO tiene entrada activa → Registra entrada
2. Si tiene entrada activa espontánea → Registra salida
3. Si tiene entrada activa planificada → Informa que ya está registrado

---

### 4. Verificar Estado de Asistencia

**Endpoint:** `GET /api/student-attendance/estado/{rutAlumno}`

**Endpoint alternativo:** `GET /api/student-attendance/estado/{rutAlumno}/{idEspacio}`

**Descripción:** Verifica si un alumno tiene asistencia activa.

**Respuesta - Alumno presente - 200:**
```json
{
    "success": true,
    "presente": true,
    "data": {
        "asistencia": { ... },
        "espacio": {
            "id": "SALA-101",
            "nombre": "Sala 101 - Laboratorio"
        },
        "tipo": "planificada",
        "accion_requerida": "ninguna"
    }
}
```

**Respuesta - Alumno no presente - 200:**
```json
{
    "success": true,
    "presente": false,
    "message": "El alumno no tiene asistencia activa"
}
```

---

### 5. Historial de Asistencias de Alumno

**Endpoint:** `GET /api/student-attendance/historial/{rutAlumno}`

**Query Parameters:**
- `fecha_inicio` (opcional): Fecha inicio del rango (default: 30 días atrás)
- `fecha_fin` (opcional): Fecha fin del rango (default: hoy)
- `limite` (opcional): Cantidad máxima de registros (default: 50, max: 100)

**Ejemplo:** `GET /api/student-attendance/historial/12345678-9?fecha_inicio=2025-11-01&fecha_fin=2025-12-05&limite=20`

**Respuesta - 200:**
```json
{
    "success": true,
    "data": {
        "rut_alumno": "123456789",
        "periodo": {
            "inicio": "2025-11-01",
            "fin": "2025-12-05"
        },
        "total": 15,
        "asistencias": [
            {
                "id": 124,
                "rut_alumno": "123456789",
                "nombre_alumno": "Juan Pérez",
                "hora_entrada": "14:30",
                "hora_salida": "16:45",
                "tipo": "espontanea",
                "estado": "finalizado",
                "fecha": "2025-12-05",
                "espacio": "SALA-101",
                "asignatura": null,
                "duracion_minutos": 135
            },
            // ... más registros
        ]
    }
}
```

---

### 6. Alumnos Presentes en un Espacio

**Endpoint:** `GET /api/student-attendance/espacio/{idEspacio}/presentes`

**Descripción:** Obtiene la lista de alumnos actualmente presentes en un espacio.

**Respuesta - 200:**
```json
{
    "success": true,
    "data": {
        "espacio": {
            "id": "SALA-101",
            "nombre": "Sala 101 - Laboratorio",
            "capacidad": 30
        },
        "ocupacion_actual": 12,
        "alumnos_presentes": [
            {
                "rut": "123456789",
                "nombre": "Juan Pérez",
                "hora_entrada": "10:15",
                "tipo_entrada": "planificada"
            },
            {
                "rut": "987654321",
                "nombre": "María González",
                "hora_entrada": "10:18",
                "tipo_entrada": "planificada"
            }
            // ... más alumnos
        ]
    }
}
```

---

## Códigos de Estado HTTP

| Código | Descripción |
|--------|-------------|
| 200 | Operación exitosa |
| 201 | Recurso creado (asistencia registrada) |
| 400 | Error de lógica de negocio (ej: clase en curso) |
| 404 | Recurso no encontrado |
| 409 | Conflicto (ej: ya tiene asistencia) |
| 422 | Error de validación |
| 500 | Error interno del servidor |

---

## Integración con Escáner QR

### Flujo Recomendado

1. **Escaneo del QR del alumno** → Obtener `rut_alumno`
2. **Escaneo del QR de la sala** → Obtener `id_espacio`
3. **Llamar al endpoint toggle:**
   ```javascript
   const response = await fetch('/api/student-attendance/toggle', {
       method: 'POST',
       headers: { 'Content-Type': 'application/json' },
       body: JSON.stringify({
           rut_alumno: rutEscaneado,
           id_espacio: espacioEscaneado
       })
   });
   const result = await response.json();
   ```
4. **Mostrar mensaje según respuesta:**
   - `tipo: "planificada"` → "Asistencia registrada para la clase"
   - `tipo: "espontanea"` + entrada → "Entrada registrada. Recuerda marcar salida"
   - `tipo: "espontanea"` + salida → "Salida registrada"

### Ejemplo de Código del Escáner

```javascript
async function procesarEscaneo(rutAlumno, idEspacio) {
    try {
        const response = await fetch('/api/student-attendance/toggle', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                rut_alumno: rutAlumno,
                id_espacio: idEspacio
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Mostrar mensaje de éxito
            mostrarMensaje(result.data.mensaje_usuario, 'success');
            
            // Si necesita marcar salida, recordárselo
            if (result.data.accion_requerida === 'marcar_salida') {
                mostrarAlerta('Recuerda escanear al salir');
            }
        } else {
            // Mostrar mensaje de error
            mostrarMensaje(result.message, 'error');
        }
    } catch (error) {
        mostrarMensaje('Error de conexión', 'error');
    }
}
```

---

## Consideraciones de Base de Datos

La migración agrega los siguientes campos a la tabla `asistencias`:

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `hora_salida` | TIME nullable | Hora de salida del alumno |
| `tipo_entrada` | ENUM | 'planificada' o 'espontanea' |
| `estado` | ENUM | 'presente' o 'finalizado' |
| `id_espacio` | VARCHAR(20) nullable | FK a espacios |

### Ejecutar Migración

```bash
php artisan migrate
```

---

## Notas Importantes

1. **RUT:** Se normaliza automáticamente (quita puntos, guión y dígito verificador)
2. **Un alumno solo puede estar presente en una sala a la vez**
3. **Las asistencias planificadas no requieren marcar salida**
4. **Las asistencias espontáneas sí requieren marcar salida**
5. **El endpoint `/toggle` es el más versátil para el escáner**
