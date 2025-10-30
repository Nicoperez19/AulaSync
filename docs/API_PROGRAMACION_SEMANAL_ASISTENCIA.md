# API de Programación Semanal y Asistencia

Esta documentación describe los endpoints para consultar la programación semanal por sala y registrar la asistencia de clases.

## Endpoints

### 1. Consultar Programación Semanal por Sala (GET)

**Endpoint:** `GET /api/programacion-semanal/{id_espacio}`

**Descripción:** Obtiene la programación semanal completa de una sala de clases específica.

**Parámetros:**
- `id_espacio` (path, requerido): ID del espacio/sala a consultar

**Respuesta exitosa (200):**

```json
{
  "success": true,
  "data": {
    "espacio": {
      "id": "A101",
      "nombre": "Sala A101",
      "tipo": "Aula",
      "capacidad_maxima": 40
    },
    "periodo": "2024-2",
    "programacion_semanal": {
      "lunes": [
        {
          "modulo": 1,
          "cantidad_modulos": 2,
          "modulo_fin": 2,
          "hora_inicio": "08:10:00",
          "hora_termino": "09:30:00",
          "profesor_a_cargo": {
            "run": "12345678",
            "nombre": "Juan Pérez García",
            "email": "juan.perez@example.com"
          },
          "asignatura": {
            "codigo": "MAT101",
            "nombre": "Matemáticas I",
            "seccion": "001",
            "carrera": {
              "id": "ING-INFO",
              "nombre": "Ingeniería en Informática"
            }
          }
        }
      ],
      "martes": [
        {
          "modulo": 2,
          "cantidad_modulos": 1,
          "modulo_fin": 2,
          "hora_inicio": "09:10:00",
          "hora_termino": "10:00:00",
          "profesor_a_cargo": {
            "run": "87654321",
            "nombre": "María López Silva",
            "email": "maria.lopez@example.com"
          },
          "asignatura": {
            "codigo": "FIS201",
            "nombre": "Física II",
            "seccion": "002",
            "carrera": {
              "id": "ING-CIVIL",
              "nombre": "Ingeniería Civil"
            }
          }
        }
      ]
    }
  }
}
```

**Respuesta de error (404):**

```json
{
  "success": false,
  "message": "Espacio no encontrado"
}
```

**Respuesta de error (500):**

```json
{
  "success": false,
  "message": "Error al obtener la programación semanal",
  "error": "Detalle del error"
}
```

**Ejemplo de uso:**

```bash
# Con curl
curl -X GET http://localhost:8000/api/programacion-semanal/A101

# Con PowerShell
Invoke-RestMethod -Uri "http://localhost:8000/api/programacion-semanal/A101" -Method Get
```

---

### 2. Registrar Asistencia (POST)

**Endpoint:** `POST /api/asistencia`

**Descripción:** Registra la asistencia de una clase, finalizando la reserva y guardando la lista de asistentes.

**Headers:**
- `Content-Type: application/json`

**Body (JSON):**

```json
{
  "id_reserva": "R202508211455301",
  "hora_termino": "10:00:00",
  "lista_asistencia": [
    {
      "rut": "12345678",
      "nombre": "Juan Pérez García",
      "hora_llegada": "08:15:00"
    },
    {
      "rut": "87654321",
      "nombre": "María López Silva",
      "hora_llegada": "08:10:00"
    }
  ],
  "contenido_visto": "Introducción a las derivadas y sus aplicaciones"
}
```

**Parámetros del body:**
- `id_reserva` (string, requerido): ID de la reserva de la clase
- `hora_termino` (string HH:MM:SS, requerido): Hora de término de la clase
- `lista_asistencia` (array, requerido): Lista de asistentes (mínimo 1)
  - `rut` (string, requerido): RUT sin dígito verificador
  - `nombre` (string, requerido): Nombre completo del asistente
  - `hora_llegada` (string HH:MM:SS, requerido): Hora de llegada del asistente
- `contenido_visto` (string, opcional): Contenido visto en la clase. Si es null o no se envía, se guardará como "Sin información adicionada"

**Respuesta exitosa (201):**

```json
{
  "success": true,
  "message": "Asistencia registrada exitosamente",
  "data": {
    "reserva": {
      "id": "R202508211455301",
      "espacio": "Sala A101",
      "fecha": "2024-08-21",
      "hora_inicio": "08:10:00",
      "hora_termino": "10:00:00",
      "profesor": "Juan Pérez García"
    },
    "asistencias_registradas": [
      {
        "id": 1,
        "rut": "12345678",
        "nombre": "Juan Pérez García",
        "hora_llegada": "08:15:00",
        "hora_termino": "10:00:00",
        "contenido_visto": "Introducción a las derivadas y sus aplicaciones"
      },
      {
        "id": 2,
        "rut": "87654321",
        "nombre": "María López Silva",
        "hora_llegada": "08:10:00",
        "hora_termino": "10:00:00",
        "contenido_visto": "Introducción a las derivadas y sus aplicaciones"
      }
    ],
    "total_asistentes": 2,
    "contenido_visto": "Introducción a las derivadas y sus aplicaciones"
  }
}
```

**Respuesta de error - Validación (422):**

```json
{
  "success": false,
  "message": "Errores de validación",
  "errors": {
    "id_reserva": ["El ID de reserva es obligatorio"],
    "hora_termino": ["La hora de término debe tener el formato HH:MM:SS"],
    "lista_asistencia": ["La lista de asistencia es obligatoria"]
  }
}
```

**Respuesta de error - Reserva no encontrada (404):**

```json
{
  "success": false,
  "message": "Reserva no encontrada"
}
```

**Respuesta de error (500):**

```json
{
  "success": false,
  "message": "Error al registrar la asistencia",
  "error": "Detalle del error"
}
```

**Ejemplo de uso:**

```bash
# Con curl
curl -X POST http://localhost:8000/api/asistencia \
  -H "Content-Type: application/json" \
  -d '{
    "id_reserva": "R202508211455301",
    "hora_termino": "10:00:00",
    "lista_asistencia": [
      {
        "rut": "12345678",
        "nombre": "Juan Pérez García",
        "hora_llegada": "08:15:00"
      }
    ],
    "contenido_visto": "Introducción a las derivadas"
  }'

# Con PowerShell
$body = @{
    id_reserva = "R202508211455301"
    hora_termino = "10:00:00"
    lista_asistencia = @(
        @{
            rut = "12345678"
            nombre = "Juan Pérez García"
            hora_llegada = "08:15:00"
        }
    )
    contenido_visto = "Introducción a las derivadas"
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost:8000/api/asistencia" -Method Post -Body $body -ContentType "application/json"
```

## Comportamiento del Sistema

### Registro de Asistencia

Al registrar la asistencia, el sistema realiza las siguientes acciones:

1. **Valida los datos** recibidos en el request
2. **Verifica la existencia** de la reserva
3. **Actualiza la reserva**:
   - Cambia el estado a "finalizada"
   - Registra la hora de salida
4. **Registra cada asistencia** en la base de datos
5. **Actualiza el estado del espacio** a "Disponible"
6. Si `contenido_visto` es null o no se envía, se guarda como "Sin información adicionada"

### Transacciones

El registro de asistencia se realiza dentro de una transacción de base de datos. Si ocurre algún error durante el proceso, todos los cambios se revierten automáticamente.

## Estructura de la Base de Datos

### Tabla: asistencias

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | bigint | ID autoincremental |
| id_reserva | string | ID de la reserva (FK) |
| rut_asistente | string | RUT sin dígito verificador |
| nombre_asistente | string | Nombre completo del asistente |
| hora_llegada | time | Hora de llegada |
| hora_termino | time | Hora de término de la clase |
| contenido_visto | text | Contenido visto en la clase (nullable) |
| created_at | timestamp | Fecha de creación |
| updated_at | timestamp | Fecha de actualización |

## Códigos de Estado HTTP

- `200 OK`: Solicitud exitosa (GET)
- `201 Created`: Recurso creado exitosamente (POST)
- `404 Not Found`: Recurso no encontrado
- `422 Unprocessable Entity`: Error de validación
- `500 Internal Server Error`: Error interno del servidor

## Notas Importantes

### Programación Semanal

1. El endpoint retorna la **capacidad_maxima** del espacio para conocer el aforo de la sala
2. La información de **carrera** está incluida en cada asignatura cuando está disponible
3. Si una asignatura no tiene carrera asignada, el campo `carrera` será `null`
4. Los módulos consecutivos de la misma asignatura y profesor se agrupan automáticamente
5. Se incluyen los campos `modulo` (inicio), `modulo_fin` y `cantidad_modulos` para cada bloque

### Registro de Asistencia

1. El formato de hora debe ser **HH:MM:SS** (24 horas)
2. El RUT debe enviarse **sin dígito verificador**
3. Debe haber al menos **un asistente** en la lista
4. El campo `contenido_visto` es opcional
5. Al registrar la asistencia, la reserva se marca automáticamente como "finalizada"
6. El espacio se libera automáticamente al finalizar la clase
