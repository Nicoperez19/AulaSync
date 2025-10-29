# Guía Rápida - Registro de Asistencia API

## 🚀 Inicio Rápido (5 minutos)

### 1. Endpoint

```
POST /api/asistencia
```

### 2. Request Mínimo

```json
{
  "id_reserva": "R20251027145530123",
  "lista_asistencia": [
    {
      "rut": "12345678",
      "nombre": "Juan Pérez García",
      "hora_llegada": "14:55:00"
    }
  ]
}
```

### 3. Respuesta Exitosa (201)

```json
{
  "success": true,
  "message": "Asistencia registrada y reserva finalizada exitosamente",
  "data": {
    "total_asistentes": 1,
    "finalizada": true
  }
}
```

## 📋 Casos de Uso

### Caso 1: Finalizar Clase Inmediatamente

```bash
curl -X POST http://localhost:8000/api/asistencia \
  -H "Content-Type: application/json" \
  -d '{
    "id_reserva": "R123",
    "lista_asistencia": [{
      "rut": "12345678",
      "nombre": "Juan Pérez",
      "hora_llegada": "14:55:00"
    }],
    "finalizar_ahora": true
  }'
```

### Caso 2: Registrar Asistencia sin Finalizar

```bash
curl -X POST http://localhost:8000/api/asistencia \
  -H "Content-Type: application/json" \
  -d '{
    "id_reserva": "R123",
    "lista_asistencia": [{
      "rut": "12345678",
      "nombre": "Juan Pérez",
      "hora_llegada": "14:00:00"
    }],
    "finalizar_ahora": false
  }'
```

### Caso 3: Múltiples Estudiantes con Observaciones

```bash
curl -X POST http://localhost:8000/api/asistencia \
  -H "Content-Type: application/json" \
  -d '{
    "id_reserva": "R123",
    "lista_asistencia": [
      {
        "rut": "12345678",
        "nombre": "Juan Pérez",
        "hora_llegada": "14:55:00",
        "observaciones": "Llegó a tiempo"
      },
      {
        "rut": "87654321",
        "nombre": "María González",
        "hora_llegada": "15:05:00",
        "observaciones": "Llegó tarde"
      }
    ]
  }'
```

## ⚙️ Parámetros

### Requeridos

| Parámetro | Tipo | Ejemplo |
|-----------|------|---------|
| `id_reserva` | string | "R20251027145530123" |
| `lista_asistencia` | array | Ver ejemplos |
| `lista_asistencia[].rut` | string | "12345678" |
| `lista_asistencia[].nombre` | string | "Juan Pérez García" |
| `lista_asistencia[].hora_llegada` | string (HH:MM:SS) | "14:55:00" |

### Opcionales

| Parámetro | Tipo | Default | Descripción |
|-----------|------|---------|-------------|
| `lista_asistencia[].observaciones` | string | null | Notas del estudiante |
| `finalizar_ahora` | boolean | true | Si finalizar la reserva |

## 🔍 Validaciones

### ✅ Válido

```json
{
  "hora_llegada": "14:55:00"  // ✅ Formato correcto
}
```

### ❌ Inválido

```json
{
  "hora_llegada": "2:55 PM"   // ❌ No usar formato 12 horas
}
{
  "hora_llegada": "14:55"     // ❌ Falta segundos
}
{
  "hora_llegada": "25:00:00"  // ❌ Hora inválida
}
```

## 📱 Código para Apps Nativas

### JavaScript/React Native

```javascript
async function registrarAsistencia(idReserva, estudiantes) {
  try {
    const response = await fetch('/api/asistencia', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        id_reserva: idReserva,
        lista_asistencia: estudiantes.map(e => ({
          rut: e.rut,
          nombre: e.nombre,
          hora_llegada: new Date().toTimeString().slice(0, 8), // HH:MM:SS
          observaciones: e.observaciones || null
        })),
        finalizar_ahora: true
      })
    });

    const data = await response.json();
    
    if (!response.ok) {
      throw new Error(data.message);
    }
    
    return data;
  } catch (error) {
    console.error('Error:', error);
    throw error;
  }
}

// Uso
const resultado = await registrarAsistencia('R123', [
  { rut: '12345678', nombre: 'Juan Pérez' },
  { rut: '87654321', nombre: 'María González', observaciones: 'Llegó tarde' }
]);
```

### Python/Kivy

```python
import requests
from datetime import datetime

def registrar_asistencia(id_reserva, estudiantes, finalizar=True):
    url = 'http://localhost:8000/api/asistencia'
    
    payload = {
        'id_reserva': id_reserva,
        'lista_asistencia': [
            {
                'rut': est['rut'],
                'nombre': est['nombre'],
                'hora_llegada': datetime.now().strftime('%H:%M:%S'),
                'observaciones': est.get('observaciones')
            }
            for est in estudiantes
        ],
        'finalizar_ahora': finalizar
    }
    
    response = requests.post(url, json=payload)
    
    if response.status_code == 201:
        return response.json()
    else:
        raise Exception(response.json().get('message'))

# Uso
resultado = registrar_asistencia(
    'R123',
    [
        {'rut': '12345678', 'nombre': 'Juan Pérez'},
        {'rut': '87654321', 'nombre': 'María González', 'observaciones': 'Llegó tarde'}
    ]
)
print(f"Asistentes registrados: {resultado['data']['total_asistentes']}")
```

### Flutter/Dart

```dart
import 'dart:convert';
import 'package:http/http.dart' as http;

Future<Map<String, dynamic>> registrarAsistencia(
  String idReserva,
  List<Map<String, String>> estudiantes,
  {bool finalizarAhora = true}
) async {
  final url = Uri.parse('http://localhost:8000/api/asistencia');
  
  final response = await http.post(
    url,
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
    body: jsonEncode({
      'id_reserva': idReserva,
      'lista_asistencia': estudiantes.map((e) => {
        'rut': e['rut'],
        'nombre': e['nombre'],
        'hora_llegada': TimeOfDay.now().toString(),
        'observaciones': e['observaciones'],
      }).toList(),
      'finalizar_ahora': finalizarAhora,
    }),
  );

  if (response.statusCode == 201) {
    return jsonDecode(response.body);
  } else {
    throw Exception(jsonDecode(response.body)['message']);
  }
}

// Uso
final resultado = await registrarAsistencia(
  'R123',
  [
    {'rut': '12345678', 'nombre': 'Juan Pérez'},
    {'rut': '87654321', 'nombre': 'María González', 'observaciones': 'Llegó tarde'},
  ],
);
print('Total: ${resultado['data']['total_asistentes']}');
```

## ⚠️ Errores Comunes

### Error 422: Validación

```json
{
  "success": false,
  "message": "Errores de validación",
  "errors": {
    "lista_asistencia.0.hora_llegada": [
      "La hora de llegada debe tener el formato HH:MM:SS"
    ]
  }
}
```

**Solución:** Verificar formato de hora (HH:MM:SS)

### Error 404: Reserva No Encontrada

```json
{
  "success": false,
  "message": "Reserva no encontrada"
}
```

**Solución:** Verificar que `id_reserva` exista en la base de datos

### Error 400: Reserva Ya Finalizada

```json
{
  "success": false,
  "message": "La reserva ya ha sido finalizada"
}
```

**Solución:** No se puede registrar asistencia en reservas finalizadas

## 🧪 Testing Rápido

### 1. Con archivo JSON

```bash
# Usar ejemplos incluidos
curl -X POST http://localhost:8000/api/asistencia \
  -H "Content-Type: application/json" \
  -d @docs/ejemplos/asistencia-simple.json
```

### 2. Inline

```bash
curl -X POST http://localhost:8000/api/asistencia \
  -H "Content-Type: application/json" \
  -d '{"id_reserva":"R123","lista_asistencia":[{"rut":"12345678","nombre":"Juan Pérez","hora_llegada":"14:55:00"}]}'
```

### 3. Con Postman

1. Importar colección desde `docs/postman/`
2. Seleccionar "Registrar Asistencia"
3. Modificar `id_reserva` con una reserva activa
4. Enviar request
5. Verificar respuesta 201

## 📊 Respuesta Completa

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
      }
    ],
    "total_asistentes": 1,
    "finalizada": true
  }
}
```

## 💡 Tips

1. **Hora actual:** Usa la función nativa del lenguaje para obtener la hora actual
2. **Validación previa:** Valida formato de hora antes de enviar
3. **Manejo de errores:** Implementa reintentos para errores de red
4. **UX:** Muestra loading mientras se procesa
5. **Offline:** Guarda datos localmente si no hay conexión
6. **Feedback:** Muestra mensaje de éxito/error al usuario

## 📚 Documentación Completa

Para más detalles, consultar:
- `docs/API_REGISTRO_ASISTENCIA.md` - Documentación completa
- `docs/RESUMEN_CAMBIOS_ASISTENCIA.md` - Resumen de cambios
- `docs/ejemplos/` - Ejemplos JSON listos para usar
