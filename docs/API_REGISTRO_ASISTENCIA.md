# API de Registro de Asistencia

## Descripción General

Este endpoint permite registrar la asistencia de estudiantes a una clase y finalizar la reserva asociada. Está diseñado para ser consumido desde aplicaciones nativas externas (móviles, de escritorio, etc.).

## Endpoint

```
POST /api/asistencia
```

## Headers Requeridos

```
Content-Type: application/json
Accept: application/json
```

## Cambios Importantes en la Nueva Versión

### ✅ Cambios Implementados

1. **Asignatura en Asistencia**: Cada registro de asistencia ahora incluye la asignatura asociada
2. **Hora de Llegada Individual**: Cada estudiante tiene su propia hora de llegada registrada
3. **Finalización Automática**: La clase se finaliza automáticamente cuando:
   - Se envía `finalizar_ahora: true` (finaliza inmediatamente)
   - Se envía `finalizar_ahora: false` (se finaliza programadamente al término de la reserva)
4. **Eliminación de hora_termino**: Ya no se requiere ni se guarda hora_termino por estudiante
5. **Observaciones**: Campo `observaciones` reemplaza a `contenido_visto` para registrar notas por estudiante

### ❌ Campos Eliminados

- `hora_termino` (en request y en tabla de asistencias)
- `contenido_visto` (renombrado a `observaciones`)

## Request Body

### Estructura JSON

```json
{
  "id_reserva": "string (requerido)",
  "lista_asistencia": [
    {
      "rut": "string (requerido)",
      "nombre": "string (requerido)",
      "hora_llegada": "HH:MM:SS (requerido)",
      "observaciones": "string (opcional)"
    }
  ],
  "finalizar_ahora": "boolean (opcional, default: true)"
}
```

### Campos del Request

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| `id_reserva` | string | Sí | ID único de la reserva (ej: "R20251027145530123") |
| `lista_asistencia` | array | Sí | Array con mínimo 1 asistente |
| `lista_asistencia[].rut` | string | Sí | RUT del estudiante sin dígito verificador |
| `lista_asistencia[].nombre` | string | Sí | Nombre completo del estudiante |
| `lista_asistencia[].hora_llegada` | string | Sí | Hora de llegada formato HH:MM:SS (24 horas) |
| `lista_asistencia[].observaciones` | string | No | Observaciones sobre el estudiante (ej: "Llegó tarde", "Participó activamente") |
| `finalizar_ahora` | boolean | No | Si `true` finaliza la reserva inmediatamente. Si `false` se finaliza programadamente (default: `true`) |

## Ejemplos de Request

### Ejemplo 1: Finalizar Clase Inmediatamente (Uso Normal)

```bash
curl -X POST http://localhost:8000/api/asistencia \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "id_reserva": "R20251027145530123",
    "lista_asistencia": [
      {
        "rut": "12345678",
        "nombre": "Juan Pérez García",
        "hora_llegada": "14:55:00",
        "observaciones": "Llegó a tiempo"
      },
      {
        "rut": "87654321",
        "nombre": "María González López",
        "hora_llegada": "15:00:00",
        "observaciones": "Llegó 5 minutos tarde"
      },
      {
        "rut": "11223344",
        "nombre": "Pedro Martínez Silva",
        "hora_llegada": "14:50:00"
      }
    ],
    "finalizar_ahora": true
  }'
```

### Ejemplo 2: Registrar Asistencia sin Finalizar (Para toma de asistencia temprana)

```bash
curl -X POST http://localhost:8000/api/asistencia \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "id_reserva": "R20251027145530123",
    "lista_asistencia": [
      {
        "rut": "12345678",
        "nombre": "Juan Pérez García",
        "hora_llegada": "14:00:00"
      }
    ],
    "finalizar_ahora": false
  }'
```

### Ejemplo 3: JavaScript (Fetch API)

```javascript
const registrarAsistencia = async () => {
  try {
    const response = await fetch('/api/asistencia', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        id_reserva: 'R20251027145530123',
        lista_asistencia: [
          {
            rut: '12345678',
            nombre: 'Juan Pérez García',
            hora_llegada: '14:55:00',
            observaciones: 'Llegó a tiempo'
          },
          {
            rut: '87654321',
            nombre: 'María González López',
            hora_llegada: '15:00:00',
            observaciones: 'Llegó 5 minutos tarde'
          }
        ],
        finalizar_ahora: true
      })
    });

    const data = await response.json();
    
    if (data.success) {
      console.log('Asistencia registrada:', data.data);
    } else {
      console.error('Error:', data.message);
    }
  } catch (error) {
    console.error('Error de red:', error);
  }
};
```

### Ejemplo 4: Python (requests)

```python
import requests
import json
from datetime import datetime

url = 'http://localhost:8000/api/asistencia'
headers = {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
}

data = {
    'id_reserva': 'R20251027145530123',
    'lista_asistencia': [
        {
            'rut': '12345678',
            'nombre': 'Juan Pérez García',
            'hora_llegada': datetime.now().strftime('%H:%M:%S'),
            'observaciones': 'Llegó a tiempo'
        },
        {
            'rut': '87654321',
            'nombre': 'María González López',
            'hora_llegada': datetime.now().strftime('%H:%M:%S')
        }
    ],
    'finalizar_ahora': True
}

response = requests.post(url, headers=headers, data=json.dumps(data))
result = response.json()

if result['success']:
    print(f"Total asistentes: {result['data']['total_asistentes']}")
else:
    print(f"Error: {result['message']}")
```

## Respuestas

### Respuesta Exitosa (201 Created)

```json
{
  "success": true,
  "message": "Asistencia registrada y reserva finalizada exitosamente",
  "data": {
    "reserva": {
      "id": "R20251027145530123",
      "espacio": {
        "id": "TH-01",
        "nombre": "Sala de Clases",
        "estado": "Disponible"
      },
      "asignatura": {
        "id": "INF101",
        "codigo": "INF101",
        "nombre": "Programación I",
        "seccion": "A"
      },
      "fecha": "2025-10-27",
      "hora_inicio": "14:00:00",
      "hora_salida": "16:00:00",
      "estado": "finalizada",
      "profesor": {
        "run": "12345678",
        "nombre": "Dr. Carlos Rodríguez"
      }
    },
    "asistencias_registradas": [
      {
        "id": 1,
        "rut": "12345678",
        "nombre": "Juan Pérez García",
        "hora_llegada": "14:55:00",
        "observaciones": "Llegó a tiempo"
      },
      {
        "id": 2,
        "rut": "87654321",
        "nombre": "María González López",
        "hora_llegada": "15:00:00",
        "observaciones": "Llegó 5 minutos tarde"
      },
      {
        "id": 3,
        "rut": "11223344",
        "nombre": "Pedro Martínez Silva",
        "hora_llegada": "14:50:00",
        "observaciones": null
      }
    ],
    "total_asistentes": 3,
    "finalizada": true
  }
}
```

### Respuesta con Finalización Programada (201 Created)

```json
{
  "success": true,
  "message": "Asistencia registrada exitosamente. La reserva se finalizará automáticamente",
  "data": {
    "reserva": {
      "id": "R20251027145530123",
      "espacio": {
        "id": "TH-01",
        "nombre": "Sala de Clases",
        "estado": "Ocupado"
      },
      "asignatura": {
        "id": "INF101",
        "codigo": "INF101",
        "nombre": "Programación I",
        "seccion": "A"
      },
      "fecha": "2025-10-27",
      "hora_inicio": "14:00:00",
      "hora_salida": null,
      "estado": "activa",
      "profesor": {
        "run": "12345678",
        "nombre": "Dr. Carlos Rodríguez"
      }
    },
    "asistencias_registradas": [
      {
        "id": 1,
        "rut": "12345678",
        "nombre": "Juan Pérez García",
        "hora_llegada": "14:00:00",
        "observaciones": null
      }
    ],
    "total_asistentes": 1,
    "finalizada": false
  }
}
```

### Error de Validación (422 Unprocessable Entity)

```json
{
  "success": false,
  "message": "Errores de validación",
  "errors": {
    "id_reserva": [
      "El ID de reserva es obligatorio"
    ],
    "lista_asistencia": [
      "La lista de asistencia es obligatoria"
    ],
    "lista_asistencia.0.hora_llegada": [
      "La hora de llegada debe tener el formato HH:MM:SS (ej: 14:30:00)"
    ]
  }
}
```

### Reserva No Encontrada (404 Not Found)

```json
{
  "success": false,
  "message": "Reserva no encontrada"
}
```

### Reserva Ya Finalizada (400 Bad Request)

```json
{
  "success": false,
  "message": "La reserva ya ha sido finalizada",
  "data": {
    "reserva_id": "R20251027145530123",
    "estado": "finalizada",
    "hora_salida": "16:00:00"
  }
}
```

### Error del Servidor (500 Internal Server Error)

```json
{
  "success": false,
  "message": "Error al registrar la asistencia",
  "error": "Descripción del error técnico",
  "trace": "Stack trace (solo en modo debug)"
}
```

## Códigos de Estado HTTP

| Código | Descripción |
|--------|-------------|
| 201 | Asistencia registrada exitosamente |
| 400 | Solicitud incorrecta (ej: reserva ya finalizada) |
| 404 | Reserva no encontrada |
| 422 | Errores de validación en los datos |
| 500 | Error interno del servidor |

## Validaciones

### Formato de Hora

- Debe ser formato 24 horas: `HH:MM:SS`
- Ejemplos válidos: `14:30:00`, `09:05:00`, `23:59:59`
- Ejemplos inválidos: `2:30 PM`, `14:30`, `25:00:00`

### RUT

- Solo números, sin guión ni dígito verificador
- Ejemplo válido: `12345678`
- Ejemplo inválido: `12.345.678-9`

## Comportamiento del Sistema

### Cuando `finalizar_ahora: true` (Default)

1. Registra todas las asistencias con sus horas de llegada
2. Vincula cada asistencia con la asignatura de la reserva
3. Marca la reserva como `finalizada`
4. Registra la hora actual como `hora_salida` de la reserva
5. Cambia el estado del espacio a `Disponible`

### Cuando `finalizar_ahora: false`

1. Registra todas las asistencias con sus horas de llegada
2. Vincula cada asistencia con la asignatura de la reserva
3. La reserva permanece `activa`
4. El espacio permanece `Ocupado`
5. Un proceso programado finalizará la reserva a la hora correspondiente

## Integración con App Nativa

### Flujo Recomendado

1. **Inicio de Clase**: Profesor escanea QR o selecciona reserva activa
2. **Toma de Asistencia**: 
   - App muestra lista de estudiantes inscritos
   - Profesor marca presentes
   - App registra hora de llegada automáticamente
3. **Durante la Clase**: 
   - Puede agregar observaciones a estudiantes que lleguen tarde
   - `finalizar_ahora: false` para no cerrar la clase aún
4. **Fin de Clase**:
   - Profesor presiona "Finalizar Clase"
   - App envía `finalizar_ahora: true`
   - Sistema libera el espacio

### Consideraciones de Seguridad

- Validar que la reserva pertenezca al profesor autenticado
- Implementar rate limiting para prevenir abuso
- Validar formato de RUT en la app antes de enviar
- Manejar errores de red y reintentos

### Manejo de Errores en App Nativa

```javascript
// Ejemplo de manejo robusto de errores
async function registrarAsistenciaConReintentos(data, maxReintentos = 3) {
  for (let intento = 1; intento <= maxReintentos; intento++) {
    try {
      const response = await fetch('/api/asistencia', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify(data)
      });

      const result = await response.json();

      if (response.ok) {
        return { success: true, data: result.data };
      }

      // Error de validación o lógica de negocio
      if (response.status === 422 || response.status === 400) {
        return { success: false, error: result.message, errors: result.errors };
      }

      // Error del servidor, reintentar
      if (intento < maxReintentos) {
        await new Promise(resolve => setTimeout(resolve, 1000 * intento));
        continue;
      }

      return { success: false, error: 'Error del servidor después de varios intentos' };

    } catch (error) {
      // Error de red
      if (intento < maxReintentos) {
        await new Promise(resolve => setTimeout(resolve, 1000 * intento));
        continue;
      }
      return { success: false, error: 'Error de conexión', details: error.message };
    }
  }
}
```

## Testing

### Test con Postman

1. Crear nueva colección "AulaSync - Asistencia"
2. Agregar request POST con la URL
3. Configurar Headers
4. Copiar el JSON de ejemplo en Body > raw > JSON
5. Ejecutar y verificar respuesta 201

### Test con cURL

```bash
# Test básico
curl -X POST http://localhost:8000/api/asistencia \
  -H "Content-Type: application/json" \
  -d @asistencia-test.json

# Con salida formateada
curl -X POST http://localhost:8000/api/asistencia \
  -H "Content-Type: application/json" \
  -d @asistencia-test.json | json_pp
```

## Migración desde Versión Anterior

### Cambios en el Request

**Antes:**
```json
{
  "id_reserva": "R123",
  "hora_termino": "16:00:00",
  "contenido_visto": "Capítulo 5",
  "lista_asistencia": [...]
}
```

**Ahora:**
```json
{
  "id_reserva": "R123",
  "finalizar_ahora": true,
  "lista_asistencia": [
    {
      "observaciones": "Participó activamente",
      ...
    }
  ]
}
```

### Actualización de Base de Datos

Ejecutar migración:
```bash
php artisan migrate
```

Esto actualizará la tabla `asistencias`:
- ✅ Agrega: `id_asignatura`
- ✅ Renombra: `contenido_visto` → `observaciones`
- ✅ Elimina: `hora_termino`

## Soporte y Contacto

Para problemas o preguntas sobre este endpoint:
- Revisar logs en `storage/logs/laravel.log`
- Verificar estado de la base de datos
- Consultar documentación adicional en `docs/`
