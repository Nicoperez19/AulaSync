# API de Espacios y Tipos de Espacios

## Descripción

Esta documentación describe las nuevas rutas de la API para listar espacios y tipos de espacios del sistema AulaSync.

## Rutas Disponibles

### 1. Listar Todos los Espacios

**Endpoint:** `GET /api/espacios`

**Descripción:** Obtiene un listado completo de todos los espacios (salas) del sistema con sus detalles.

**Parámetros opcionales (query params):**
- `tipo_espacio` - Filtra por tipo de espacio (ej: "Sala de Clases", "Laboratorio", etc.)
- `estado` - Filtra por estado (ej: "Disponible", "Ocupado", "Reservado")
- `piso_id` - Filtra por ID de piso

**Ejemplo de request:**
```bash
GET /api/espacios
GET /api/espacios?tipo_espacio=Sala de Clases
GET /api/espacios?estado=Disponible
GET /api/espacios?piso_id=1&tipo_espacio=Laboratorio
```

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "total": 42,
  "espacios": [
    {
      "id_espacio": "TH-01",
      "nombre_espacio": "Sala de Clases",
      "tipo_espacio": "Sala de Clases",
      "estado": "Disponible",
      "puestos_disponibles": 35,
      "piso": {
        "id": 2,
        "numero_piso": 2
      },
      "facultad": {
        "id_facultad": "IT_TH",
        "nombre_facultad": "Facultad de Ingeniería"
      },
      "sede": {
        "id_sede": "TH",
        "nombre_sede": "Talcahuano"
      }
    },
    {
      "id_espacio": "TH-LAB1",
      "nombre_espacio": "Laboratorio de Física",
      "tipo_espacio": "Laboratorio",
      "estado": "Disponible",
      "puestos_disponibles": 10,
      "piso": {
        "id": 1,
        "numero_piso": 1
      },
      "facultad": {
        "id_facultad": "IT_TH",
        "nombre_facultad": "Facultad de Ingeniería"
      },
      "sede": {
        "id_sede": "TH",
        "nombre_sede": "Talcahuano"
      }
    }
  ]
}
```

**Respuesta de error (500):**
```json
{
  "success": false,
  "message": "Error al obtener los espacios: [mensaje de error]"
}
```

---

### 2. Listar Tipos de Espacios

**Endpoint:** `GET /api/tipos-espacios`

**Descripción:** Obtiene un listado de todos los tipos de espacios disponibles en el sistema con el conteo de espacios por cada tipo.

**Ejemplo de request:**
```bash
GET /api/tipos-espacios
```

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "total_tipos": 8,
  "tipos_espacios": [
    {
      "tipo_espacio": "Auditorio",
      "total_espacios": 1
    },
    {
      "tipo_espacio": "Biblioteca",
      "total_espacios": 2
    },
    {
      "tipo_espacio": "Laboratorio",
      "total_espacios": 5
    },
    {
      "tipo_espacio": "Oficinas",
      "total_espacios": 3
    },
    {
      "tipo_espacio": "Sala de Clases",
      "total_espacios": 25
    },
    {
      "tipo_espacio": "Sala de Estudio",
      "total_espacios": 4
    },
    {
      "tipo_espacio": "Sala de Reuniones",
      "total_espacios": 1
    },
    {
      "tipo_espacio": "Taller",
      "total_espacios": 1
    }
  ]
}
```

**Respuesta de error (500):**
```json
{
  "success": false,
  "message": "Error al obtener los tipos de espacios: [mensaje de error]"
}
```

---

### 3. Resumen de Espacios

**Endpoint:** `GET /api/espacios/resumen`

**Descripción:** Obtiene un resumen estadístico de espacios agrupados por tipo y estado.

**Ejemplo de request:**
```bash
GET /api/espacios/resumen
```

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "resumen": [
    {
      "tipo": "Sala de Clases",
      "total": 25,
      "por_estado": {
        "Disponible": 20,
        "Ocupado": 3,
        "Reservado": 2
      }
    },
    {
      "tipo": "Laboratorio",
      "total": 5,
      "por_estado": {
        "Disponible": 4,
        "Ocupado": 1
      }
    },
    {
      "tipo": "Sala de Estudio",
      "total": 4,
      "por_estado": {
        "Disponible": 3,
        "Reservado": 1
      }
    }
  ]
}
```

**Respuesta de error (500):**
```json
{
  "success": false,
  "message": "Error al obtener el resumen de espacios: [mensaje de error]"
}
```

---

## Tipos de Espacios Disponibles

Según la migración del sistema, los tipos de espacios válidos son:

1. **Sala de Clases** - Salas tradicionales para impartir clases
2. **Laboratorio** - Espacios equipados para prácticas de laboratorio
3. **Biblioteca** - Áreas de biblioteca
4. **Sala de Reuniones** - Espacios para reuniones
5. **Oficinas** - Oficinas administrativas o de profesores
6. **Taller** - Talleres especializados
7. **Auditorio** - Auditorios para eventos grandes
8. **Sala de Estudio** - Espacios para estudio individual o grupal

## Estados de Espacios

Los estados posibles para un espacio son:

1. **Disponible** - El espacio está libre y puede ser reservado
2. **Ocupado** - El espacio está actualmente en uso
3. **Reservado** - El espacio tiene una reserva activa

## Ejemplos de Uso

### Ejemplo con cURL

```bash
# Listar todos los espacios
curl -X GET http://localhost/api/espacios

# Listar solo salas de clases disponibles
curl -X GET "http://localhost/api/espacios?tipo_espacio=Sala de Clases&estado=Disponible"

# Listar tipos de espacios
curl -X GET http://localhost/api/tipos-espacios

# Obtener resumen
curl -X GET http://localhost/api/espacios/resumen
```

### Ejemplo con JavaScript (Fetch API)

```javascript
// Listar todos los espacios
fetch('/api/espacios')
  .then(response => response.json())
  .then(data => {
    console.log('Total de espacios:', data.total);
    console.log('Espacios:', data.espacios);
  });

// Listar espacios filtrados
fetch('/api/espacios?tipo_espacio=Laboratorio&estado=Disponible')
  .then(response => response.json())
  .then(data => {
    console.log('Laboratorios disponibles:', data.espacios);
  });

// Listar tipos de espacios
fetch('/api/tipos-espacios')
  .then(response => response.json())
  .then(data => {
    console.log('Tipos de espacios:', data.tipos_espacios);
  });

// Obtener resumen
fetch('/api/espacios/resumen')
  .then(response => response.json())
  .then(data => {
    console.log('Resumen de espacios:', data.resumen);
  });
```

### Ejemplo con Python (requests)

```python
import requests

# Listar todos los espacios
response = requests.get('http://localhost/api/espacios')
data = response.json()
print(f"Total de espacios: {data['total']}")

# Listar espacios filtrados
params = {'tipo_espacio': 'Sala de Clases', 'estado': 'Disponible'}
response = requests.get('http://localhost/api/espacios', params=params)
data = response.json()
print(f"Salas de clases disponibles: {len(data['espacios'])}")

# Listar tipos de espacios
response = requests.get('http://localhost/api/tipos-espacios')
data = response.json()
for tipo in data['tipos_espacios']:
    print(f"{tipo['tipo_espacio']}: {tipo['total_espacios']} espacios")
```

## Notas Adicionales

- Todas las respuestas incluyen el campo `success` para indicar si la operación fue exitosa
- Los campos opcionales pueden ser `null` si no hay datos disponibles
- Las relaciones con piso, facultad y sede se incluyen automáticamente en la respuesta de espacios
- Los filtros son opcionales y se pueden combinar
- La API maneja automáticamente los errores y devuelve códigos HTTP apropiados

## Controlador

El controlador que maneja estas rutas se encuentra en:
`app/Http/Controllers/Api/EspacioApiController.php`

## Rutas

Las rutas están definidas en:
`routes/api.php`
