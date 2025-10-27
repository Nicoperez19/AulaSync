# Ejemplos de Respuestas - API de Espacios

Este archivo contiene ejemplos reales de respuestas de la API de espacios para facilitar el desarrollo y testing.

## 1. GET /api/espacios (Respuesta Completa)

```json
{
  "success": true,
  "total": 42,
  "espacios": [
    {
      "id_espacio": "TH-LAB1",
      "nombre_espacio": "Laboratorio de Redes",
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
    },
    {
      "id_espacio": "TH-30",
      "nombre_espacio": "Taller de Soldadura",
      "tipo_espacio": "Taller",
      "estado": "Disponible",
      "puestos_disponibles": 5,
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
    },
    {
      "id_espacio": "TH-40",
      "nombre_espacio": "Sala de Clases",
      "tipo_espacio": "Sala de Clases",
      "estado": "Disponible",
      "puestos_disponibles": 40,
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
    },
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
    }
  ]
}
```

## 2. GET /api/espacios?tipo_espacio=Sala de Clases (Filtrado)

```json
{
  "success": true,
  "total": 25,
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
      "id_espacio": "TH-02",
      "nombre_espacio": "Sala de Clases",
      "tipo_espacio": "Sala de Clases",
      "estado": "Disponible",
      "puestos_disponibles": 37,
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
    }
  ]
}
```

## 3. GET /api/espacios?tipo_espacio=Laboratorio&estado=Disponible (Filtros Múltiples)

```json
{
  "success": true,
  "total": 4,
  "espacios": [
    {
      "id_espacio": "TH-LAB1",
      "nombre_espacio": "Laboratorio de Redes",
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
    },
    {
      "id_espacio": "TH-LAB2",
      "nombre_espacio": "Laboratorio de Física",
      "tipo_espacio": "Laboratorio",
      "estado": "Disponible",
      "puestos_disponibles": 12,
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

## 4. GET /api/tipos-espacios (Lista de Tipos)

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
      "tipo_espacio": "Laboratorio",
      "total_espacios": 5
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
      "total_espacios": 6
    }
  ]
}
```

## 5. GET /api/espacios/resumen (Resumen Estadístico)

```json
{
  "success": true,
  "resumen": [
    {
      "tipo": "Auditorio",
      "total": 1,
      "por_estado": {
        "Disponible": 1
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
      "tipo": "Sala de Clases",
      "total": 25,
      "por_estado": {
        "Disponible": 20,
        "Ocupado": 3,
        "Reservado": 2
      }
    },
    {
      "tipo": "Sala de Estudio",
      "total": 4,
      "por_estado": {
        "Disponible": 3,
        "Reservado": 1
      }
    },
    {
      "tipo": "Sala de Reuniones",
      "total": 1,
      "por_estado": {
        "Disponible": 1
      }
    },
    {
      "tipo": "Taller",
      "total": 6,
      "por_estado": {
        "Disponible": 5,
        "Ocupado": 1
      }
    }
  ]
}
```

## 6. Respuesta de Error (500)

```json
{
  "success": false,
  "message": "Error al obtener los espacios: [mensaje de error específico]"
}
```

## 7. Respuesta Vacía (Sin resultados con filtros)

```json
{
  "success": true,
  "total": 0,
  "espacios": []
}
```

## Notas sobre las Respuestas

### Campos de Espacio

- `id_espacio`: Identificador único del espacio (ej: "TH-01")
- `nombre_espacio`: Nombre descriptivo del espacio
- `tipo_espacio`: Tipo del espacio (ver tipos disponibles)
- `estado`: Estado actual (Disponible, Ocupado, Reservado)
- `puestos_disponibles`: Capacidad del espacio (puede ser null)

### Estructura de Relaciones

Cada espacio incluye información anidada de:
- **Piso**: ID y número de piso
- **Facultad**: ID y nombre de la facultad
- **Sede**: ID y nombre de la sede

### Campos Nullable

Los siguientes campos pueden ser `null`:
- `puestos_disponibles` - Algunos espacios no tienen puestos asignados
- Datos de relaciones si no están configuradas en la base de datos

## Uso de las Respuestas para Testing

### Validación de Estructura

```javascript
// Validar estructura de respuesta de espacios
const validarEstructuraEspacio = (espacio) => {
  return espacio.hasOwnProperty('id_espacio') &&
         espacio.hasOwnProperty('nombre_espacio') &&
         espacio.hasOwnProperty('tipo_espacio') &&
         espacio.hasOwnProperty('estado') &&
         espacio.hasOwnProperty('piso') &&
         espacio.hasOwnProperty('facultad') &&
         espacio.hasOwnProperty('sede');
};

// Validar respuesta de lista de espacios
const validarRespuestaEspacios = (data) => {
  return data.success === true &&
         typeof data.total === 'number' &&
         Array.isArray(data.espacios);
};
```

### Procesamiento de Datos

```javascript
// Agrupar espacios por tipo
const agruparPorTipo = (espacios) => {
  return espacios.reduce((acc, espacio) => {
    if (!acc[espacio.tipo_espacio]) {
      acc[espacio.tipo_espacio] = [];
    }
    acc[espacio.tipo_espacio].push(espacio);
    return acc;
  }, {});
};

// Contar espacios disponibles por piso
const contarDisponiblesPorPiso = (espacios) => {
  return espacios
    .filter(e => e.estado === 'Disponible')
    .reduce((acc, espacio) => {
      const piso = espacio.piso.numero_piso;
      acc[piso] = (acc[piso] || 0) + 1;
      return acc;
    }, {});
};
```

## Códigos de Estado HTTP

- `200 OK` - Solicitud exitosa
- `500 Internal Server Error` - Error del servidor
