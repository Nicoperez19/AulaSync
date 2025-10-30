# API - Obtener Reserva Activa por Espacio

## Descripción

Este endpoint permite consultar la reserva activa de un espacio específico en tiempo real, devolviendo toda la información relacionada como profesor, asignatura, asistencia, etc.

### ⚠️ IMPORTANTE: Lógica de Ocupación de Espacios

Un espacio se considera **OCUPADO** cuando se cumple **alguna** de estas condiciones:

#### 1. Tiene una Reserva Activa Formal (Caso más común)
- Existe una reserva con `estado = 'activa'`
- La fecha de la reserva es HOY
- La hora actual está dentro del rango de la reserva
- **Respuesta:** El endpoint retorna toda la información de la reserva

#### 2. Estado Manual "Ocupado" (Sin reserva formal)
- El campo `espacio.estado = 'Ocupado'` en la base de datos
- Alguien está usando el espacio sin una reserva formal
- **Respuesta:** El endpoint retorna `reserva_activa: null` pero `espacio.ocupado: true`
- Incluye una nota explicativa del motivo

### Casos de Respuesta

| Situación | `reserva_activa` | `espacio.ocupado` | Mensaje |
|-----------|------------------|-------------------|---------|
| Reserva activa formal | `{...datos}` | `true` | "Reserva activa encontrada - El espacio está ocupado" |
| Estado manual "Ocupado" | `null` | `true` | "El espacio está ocupado pero no tiene una reserva formal activa" |
| Espacio disponible | `null` | `false` | "El espacio está disponible, no hay reserva activa..." |

## Endpoint

```
GET /api/reservas/activa/{id_espacio}
```

## Casos de Uso Detallados

### 📊 Escenario 1: Reserva Activa Formal (Más Común)

**Situación:** Un profesor tiene clase reservada de 14:00 a 15:30

```
- Reserva en DB: estado="activa", fecha=HOY, hora="14:00:00"
- Espacio en DB: estado="Ocupado"
- Hora actual: 14:45
```

**Respuesta API:**
- `reserva_activa`: Objeto completo con todos los datos
- `espacio.ocupado`: `true`
- `espacio.estado`: `"Ocupado"`
- **Mensaje:** "Reserva activa encontrada - El espacio está ocupado"

**Uso típico:** App móvil muestra "OCUPADO ⛔" con información del profesor y asignatura

---

### 🚪 Escenario 2: Ocupación Manual (Sin Reserva)

**Situación:** Alguien entró a la sala sin reservar formalmente

```
- Reserva en DB: No existe O está finalizada
- Espacio en DB: estado="Ocupado" (cambiado manualmente o por otro sistema)
- Hora actual: Cualquiera
```

**Respuesta API:**
- `reserva_activa`: `null`
- `espacio.ocupado`: `true`
- `espacio.estado`: `"Ocupado"`
- **Mensaje:** "El espacio está ocupado pero no tiene una reserva formal activa"
- `nota`: "El espacio puede estar siendo usado sin una reserva formal"

**Uso típico:** App muestra "OCUPADO ⛔" pero sin información de reserva. Permite a personal verificar si es legítimo.

---

### ✅ Escenario 3: Espacio Disponible

**Situación:** Sala libre y sin uso

```
- Reserva en DB: No existe O no está en horario activo
- Espacio en DB: estado="Disponible"
- Hora actual: Cualquiera
```

**Respuesta API:**
- `reserva_activa`: `null`
- `espacio.ocupado`: `false`
- `espacio.estado`: `"Disponible"`
- **Mensaje:** "El espacio está disponible, no hay reserva activa..."

**Uso típico:** App muestra "DISPONIBLE ✅" y permite hacer nueva reserva

---

### ⏰ Escenario 4: Entre Reservas

**Situación:** La clase terminó pero hay otra reserva más tarde

```
- Reserva anterior: estado="finalizada", hora_salida="13:30"
- Reserva próxima: estado="programada", hora="16:00"
- Espacio en DB: estado="Disponible"
- Hora actual: 15:00
```

**Respuesta API:**
- `reserva_activa`: `null` (porque no hay reserva ACTIVA ahora)
- `espacio.ocupado`: `false`
- **Mensaje:** "El espacio está disponible..."

**Importante:** Este endpoint NO muestra reservas futuras, solo la activa en este momento

---

## Parámetros de URL

| Parámetro | Tipo | Requerido | Descripción |
|-----------|------|-----------|-------------|
| `id_espacio` | string | Sí | ID del espacio (ej: TH-03, TH-LAB1, TH-AUD) |

## Headers

```
Accept: application/json
```

## Ejemplos de Request

### Ejemplo 1: Consultar reserva activa de sala TH-03

```bash
curl -X GET http://localhost:8000/api/reservas/activa/TH-03 \
  -H "Accept: application/json"
```

### Ejemplo 2: Consultar laboratorio

```bash
curl -X GET http://localhost:8000/api/reservas/activa/TH-LAB1 \
  -H "Accept: application/json"
```

### Ejemplo 3: JavaScript (Fetch API)

```javascript
async function obtenerReservaActiva(idEspacio) {
  try {
    const response = await fetch(`/api/reservas/activa/${idEspacio}`, {
      method: 'GET',
      headers: {
        'Accept': 'application/json'
      }
    });

    const data = await response.json();
    
    if (data.success && data.data.reserva_activa) {
      console.log('Reserva activa:', data.data.reserva);
      console.log('Profesor:', data.data.usuario_reserva.nombre);
      console.log('Asignatura:', data.data.asignatura?.nombre);
      console.log('Asistentes:', data.data.asistencia.total_registrados);
    } else {
      console.log('No hay reserva activa');
    }
    
    return data;
  } catch (error) {
    console.error('Error:', error);
    throw error;
  }
}

// Uso
obtenerReservaActiva('TH-03');
```

### Ejemplo 4: Python (requests)

```python
import requests

def obtener_reserva_activa(id_espacio):
    url = f'http://localhost:8000/api/reservas/activa/{id_espacio}'
    headers = {'Accept': 'application/json'}
    
    response = requests.get(url, headers=headers)
    data = response.json()
    
    if data['success'] and data['data'].get('reserva_activa'):
        reserva = data['data']['reserva']
        print(f"Reserva ID: {reserva['id']}")
        print(f"Profesor: {data['data']['usuario_reserva']['nombre']}")
        if data['data']['asignatura']:
            print(f"Asignatura: {data['data']['asignatura']['nombre']}")
        print(f"Asistentes: {data['data']['asistencia']['total_registrados']}")
    else:
        print('No hay reserva activa')
    
    return data

# Uso
resultado = obtener_reserva_activa('TH-03')
```

## Respuestas

### Respuesta Exitosa - CON Reserva Activa (200 OK)

```json
{
  "success": true,
  "message": "Reserva activa encontrada",
  "data": {
    "reserva": {
      "id": "R20251029145530123",
      "tipo": "clase",
      "estado": "activa",
      "fecha": "2025-10-29",
      "hora_inicio": "14:00:00",
      "hora_salida": null,
      "duracion_minutos": 45,
      "modulos": "M1,M2",
      "observaciones": null,
      "creada_el": "2025-10-29 13:55:00"
    },
    "espacio": {
      "id": "TH-03",
      "nombre": "Sala de Clases",
      "tipo": "Sala de Clases",
      "estado": "Ocupado",
      "puestos_disponibles": 35
    },
    "usuario_reserva": {
      "tipo": "profesor",
      "run": "12345678",
      "nombre": "Dr. Carlos Rodríguez Pérez",
      "email": "carlos.rodriguez@ucsc.cl",
      "celular": "+56912345678"
    },
    "asignatura": {
      "id": "INF101",
      "codigo": "INF101",
      "nombre": "Programación I",
      "seccion": "A",
      "profesor_titular": {
        "run": "12345678",
        "nombre": "Dr. Carlos Rodríguez Pérez"
      }
    },
    "asistencia": {
      "total_registrados": 3,
      "estudiantes": [
        {
          "id": 1,
          "rut": "19876543",
          "nombre": "Juan Pérez García",
          "hora_llegada": "14:00:00",
          "observaciones": "Llegó a tiempo"
        },
        {
          "id": 2,
          "rut": "19876544",
          "nombre": "María González López",
          "hora_llegada": "14:05:00",
          "observaciones": "Llegó 5 minutos tarde"
        },
        {
          "id": 3,
          "rut": "19876545",
          "nombre": "Pedro Martínez Silva",
          "hora_llegada": "14:00:00",
          "observaciones": null
        }
      ]
    },
    "fecha_consulta": "2025-10-29",
    "hora_consulta": "14:45:30"
  }
}
```

### Respuesta Exitosa - SIN Reserva Activa (200 OK)

**Caso 1: Espacio Disponible**

```json
{
  "success": true,
  "message": "El espacio está disponible, no hay reserva activa en este momento",
  "data": {
    "espacio": {
      "id": "TH-03",
      "nombre": "Sala de Clases",
      "tipo": "Sala de Clases",
      "estado": "Disponible",
      "puestos_disponibles": 35,
      "ocupado": false
    },
    "reserva_activa": null,
    "fecha_consulta": "2025-10-29",
    "hora_consulta": "12:30:00",
    "nota": null
  }
}
```

**Caso 2: Espacio Ocupado Sin Reserva Formal**

```json
{
  "success": true,
  "message": "El espacio está ocupado pero no tiene una reserva formal activa",
  "data": {
    "espacio": {
      "id": "TH-03",
      "nombre": "Sala de Clases",
      "tipo": "Sala de Clases",
      "estado": "Ocupado",
      "puestos_disponibles": 35,
      "ocupado": true
    },
    "reserva_activa": null,
    "fecha_consulta": "2025-10-29",
    "hora_consulta": "12:30:00",
    "nota": "El espacio puede estar siendo usado sin una reserva formal"
  }
}
```

### Respuesta Exitosa - Reserva con Solicitante (No Profesor)

```json
{
  "success": true,
  "message": "Reserva activa encontrada",
  "data": {
    "reserva": {
      "id": "R20251029100000456",
      "tipo": "espontanea",
      "estado": "activa",
      "fecha": "2025-10-29",
      "hora_inicio": "10:00:00",
      "hora_salida": null,
      "duracion_minutos": 150,
      "modulos": null,
      "observaciones": "Reunión de coordinación",
      "creada_el": "2025-10-29 09:50:00"
    },
    "espacio": {
      "id": "TH-SR1",
      "nombre": "Sala de Reuniones",
      "tipo": "Sala de Reuniones",
      "estado": "Ocupado",
      "puestos_disponibles": 13
    },
    "usuario_reserva": {
      "tipo": "solicitante",
      "run": "11223344",
      "nombre": "Ana Martínez Torres",
      "email": "ana.martinez@ucsc.cl",
      "telefono": "+56987654321"
    },
    "asignatura": null,
    "asistencia": {
      "total_registrados": 0,
      "estudiantes": []
    },
    "fecha_consulta": "2025-10-29",
    "hora_consulta": "12:30:00"
  }
}
```

### Error - Espacio No Encontrado (404 Not Found)

```json
{
  "success": false,
  "message": "Espacio no encontrado",
  "id_espacio": "TH-999"
}
```

### Error del Servidor (500 Internal Server Error)

```json
{
  "success": false,
  "message": "Error al obtener la reserva activa",
  "error": "Descripción del error técnico",
  "trace": "Stack trace (solo en modo debug)"
}
```

## Códigos de Estado HTTP

| Código | Descripción |
|--------|-------------|
| 200 | Consulta exitosa (con o sin reserva activa) |
| 404 | Espacio no encontrado |
| 500 | Error interno del servidor |

## Campos de la Respuesta

### Objeto `reserva`

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | string | ID único de la reserva |
| `tipo` | string | Tipo: "clase", "espontanea", "directa" |
| `estado` | string | Estado: "activa", "finalizada" |
| `fecha` | string | Fecha de la reserva (YYYY-MM-DD) |
| `hora_inicio` | string | Hora de inicio (HH:MM:SS) |
| `hora_salida` | string\|null | Hora de salida (HH:MM:SS) o null si sigue activa |
| `duracion_minutos` | number | Duración en minutos desde inicio |
| `modulos` | string\|null | Módulos de la clase (ej: "M1,M2") |
| `observaciones` | string\|null | Observaciones de la reserva |
| `creada_el` | string | Timestamp de creación |

### Objeto `espacio`

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | string | ID del espacio |
| `nombre` | string | Nombre del espacio |
| `tipo` | string | Tipo de espacio |
| `estado` | string | Estado actual: "Disponible", "Ocupado", "Reservado" |
| `puestos_disponibles` | number\|null | Capacidad del espacio |
| `ocupado` | boolean | **true** si tiene reserva activa O si estado="Ocupado", **false** si está disponible |

### Objeto `usuario_reserva`

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `tipo` | string | "profesor" o "solicitante" |
| `run` | string | RUT del usuario |
| `nombre` | string | Nombre completo |
| `email` | string\|null | Correo electrónico |
| `celular` o `telefono` | string\|null | Teléfono de contacto |

### Objeto `asignatura` (puede ser null)

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | string | ID de la asignatura |
| `codigo` | string | Código de la asignatura |
| `nombre` | string | Nombre de la asignatura |
| `seccion` | string\|null | Sección |
| `profesor_titular` | object\|null | Datos del profesor titular |

### Objeto `asistencia`

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `total_registrados` | number | Total de estudiantes con asistencia |
| `estudiantes` | array | Lista de estudiantes con asistencia |

## Lógica de Búsqueda

El endpoint busca una reserva que cumpla TODAS estas condiciones:

1. ✅ `id_espacio` coincide
2. ✅ `estado` = 'activa'
3. ✅ `fecha_reserva` = fecha actual
4. ✅ `hora` <= hora actual (ya comenzó)
5. ✅ `hora_salida` es NULL O >= hora actual (no ha terminado)

**Ordenamiento:** Si hay múltiples coincidencias, devuelve la más reciente (por hora de inicio DESC)

## Casos de Uso

### Caso 1: Pantalla de Espacio en Tiempo Real

```javascript
// Actualizar cada 30 segundos
setInterval(async () => {
  const data = await obtenerReservaActiva('TH-03');
  
  if (data.data.reserva_activa) {
    mostrarReservaActiva(data.data);
  } else {
    mostrarEspacioDisponible(data.data.espacio);
  }
}, 30000);
```

### Caso 2: Validar Acceso a Espacio

```javascript
async function validarAccesoEspacio(idEspacio, runUsuario) {
  const data = await obtenerReservaActiva(idEspacio);
  
  if (!data.data.reserva_activa) {
    return { permitido: false, razon: 'No hay reserva activa' };
  }
  
  const runReserva = data.data.usuario_reserva.run;
  
  if (runReserva === runUsuario) {
    return { permitido: true };
  } else {
    return { 
      permitido: false, 
      razon: 'Reserva pertenece a otro usuario',
      usuario: data.data.usuario_reserva.nombre
    };
  }
}
```

### Caso 3: Dashboard de Espacios

```javascript
async function cargarEstadoEspacios() {
  const espacios = ['TH-01', 'TH-02', 'TH-03', 'TH-LAB1'];
  
  const promises = espacios.map(id => obtenerReservaActiva(id));
  const resultados = await Promise.all(promises);
  
  const ocupados = resultados.filter(r => r.data.reserva_activa).length;
  const disponibles = espacios.length - ocupados;
  
  console.log(`Espacios ocupados: ${ocupados}`);
  console.log(`Espacios disponibles: ${disponibles}`);
  
  return resultados;
}
```

### Caso 4: Notificaciones de Finalización

```javascript
async function verificarFinalizacionProxima(idEspacio, minutosAntes = 10) {
  const data = await obtenerReservaActiva(idEspacio);
  
  if (!data.data.reserva_activa) return null;
  
  const duracion = data.data.reserva.duracion_minutos;
  const tiempoRestante = 90 - duracion; // Asumiendo clase de 90 minutos
  
  if (tiempoRestante <= minutosAntes && tiempoRestante > 0) {
    return {
      alerta: true,
      mensaje: `La clase termina en ${tiempoRestante} minutos`,
      profesor: data.data.usuario_reserva.nombre
    };
  }
  
  return null;
}
```

## Comparación con Otros Endpoints

### vs `/api/reserva-activa/{id}` (Si existe)

| Característica | `/api/reservas/activa/{id_espacio}` | `/api/reserva-activa/{id}` |
|----------------|-------------------------------------|----------------------------|
| Parámetro | ID del **espacio** | ID de la **reserva** |
| Búsqueda | Por espacio en tiempo real | Por ID específico de reserva |
| Uso | Consultar estado actual de un espacio | Consultar una reserva específica |
| Respuesta sin datos | Espacio disponible (200) | Error 404 |

## Integración con Apps Nativas

### Flutter/Dart

```dart
Future<Map<String, dynamic>?> obtenerReservaActiva(String idEspacio) async {
  final url = Uri.parse('http://localhost:8000/api/reservas/activa/$idEspacio');
  
  final response = await http.get(
    url,
    headers: {'Accept': 'application/json'},
  );

  if (response.statusCode == 200) {
    final data = jsonDecode(response.body);
    
    if (data['success'] && data['data']['reserva_activa'] != null) {
      return data['data'];
    }
  }
  
  return null;
}
```

### React Native

```javascript
import { useState, useEffect } from 'react';

function useReservaActiva(idEspacio, intervalo = 30000) {
  const [reserva, setReserva] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchReserva = async () => {
      try {
        const response = await fetch(`/api/reservas/activa/${idEspacio}`);
        const data = await response.json();
        
        if (data.success && data.data.reserva_activa) {
          setReserva(data.data);
        } else {
          setReserva(null);
        }
      } catch (error) {
        console.error('Error:', error);
      } finally {
        setLoading(false);
      }
    };

    fetchReserva();
    const interval = setInterval(fetchReserva, intervalo);

    return () => clearInterval(interval);
  }, [idEspacio, intervalo]);

  return { reserva, loading };
}

// Uso en componente
function EspacioScreen({ idEspacio }) {
  const { reserva, loading } = useReservaActiva(idEspacio);

  if (loading) return <Loading />;
  
  if (reserva) {
    return <ReservaActiva data={reserva} />;
  }
  
  return <EspacioDisponible />;
}
```

## Tips y Mejores Prácticas

1. **Polling:** Consulta cada 30-60 segundos para mantener datos actualizados
2. **Caché:** Guarda la respuesta temporalmente para reducir llamadas
3. **Validación:** Siempre verifica `success` y `reserva_activa` antes de usar los datos
4. **Manejo de Null:** Asignatura puede ser null en reservas espontáneas
5. **Timezone:** Todas las horas están en zona horaria del servidor
6. **Performance:** Si consultas múltiples espacios, considera un endpoint batch

## Testing

### Test Manual con cURL

```bash
# Test 1: Espacio con reserva activa
curl -X GET http://localhost:8000/api/reservas/activa/TH-03

# Test 2: Espacio sin reserva
curl -X GET http://localhost:8000/api/reservas/activa/TH-99

# Test 3: Espacio inexistente
curl -X GET http://localhost:8000/api/reservas/activa/INVALIDO

# Test 4: Con formato bonito (usando jq)
curl -X GET http://localhost:8000/api/reservas/activa/TH-03 | jq
```

### Test Automatizado (PHPUnit/Pest)

```php
test('obtiene reserva activa correctamente', function () {
    $espacio = Espacio::factory()->create(['id_espacio' => 'TH-TEST']);
    $reserva = Reserva::factory()->create([
        'id_espacio' => 'TH-TEST',
        'estado' => 'activa',
        'fecha_reserva' => now()->format('Y-m-d'),
        'hora' => now()->subMinutes(30)->format('H:i:s'),
        'hora_salida' => null
    ]);

    $response = $this->getJson('/api/reservas/activa/TH-TEST');

    $response->assertStatus(200)
             ->assertJson([
                 'success' => true,
                 'data' => [
                     'reserva' => [
                         'id' => $reserva->id_reserva
                     ]
                 ]
             ]);
});

test('retorna null cuando no hay reserva activa', function () {
    $espacio = Espacio::factory()->create(['id_espacio' => 'TH-TEST']);

    $response = $this->getJson('/api/reservas/activa/TH-TEST');

    $response->assertStatus(200)
             ->assertJson([
                 'success' => true,
                 'data' => [
                     'reserva_activa' => null
                 ]
             ]);
});
```

## Soporte

Para problemas o consultas:
- Revisar logs: `storage/logs/laravel.log`
- Verificar estado de base de datos
- Consultar documentación de modelos: `app/Models/Reserva.php`
