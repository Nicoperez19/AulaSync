# Ejemplos de Prueba - API de Espacios

## Requisitos Previos

Asegúrate de que el servidor Laravel esté corriendo:

```bash
php artisan serve
```

O si estás usando Sail:

```bash
./sail up
```

## Pruebas con cURL

### 1. Listar todos los espacios

```bash
curl -X GET http://localhost:8000/api/espacios
```

### 2. Listar espacios filtrados por tipo

```bash
# Salas de Clases
curl -X GET "http://localhost:8000/api/espacios?tipo_espacio=Sala de Clases"

# Laboratorios
curl -X GET "http://localhost:8000/api/espacios?tipo_espacio=Laboratorio"

# Talleres
curl -X GET "http://localhost:8000/api/espacios?tipo_espacio=Taller"

# Salas de Estudio
curl -X GET "http://localhost:8000/api/espacios?tipo_espacio=Sala de Estudio"
```

### 3. Listar espacios filtrados por estado

```bash
# Espacios disponibles
curl -X GET "http://localhost:8000/api/espacios?estado=Disponible"

# Espacios ocupados
curl -X GET "http://localhost:8000/api/espacios?estado=Ocupado"

# Espacios reservados
curl -X GET "http://localhost:8000/api/espacios?estado=Reservado"
```

### 4. Combinar filtros

```bash
# Salas de Clases disponibles
curl -X GET "http://localhost:8000/api/espacios?tipo_espacio=Sala de Clases&estado=Disponible"

# Laboratorios en el piso 1
curl -X GET "http://localhost:8000/api/espacios?tipo_espacio=Laboratorio&piso_id=1"

# Salas de Estudio disponibles en el piso 2
curl -X GET "http://localhost:8000/api/espacios?tipo_espacio=Sala de Estudio&estado=Disponible&piso_id=2"
```

### 5. Listar todos los tipos de espacios

```bash
curl -X GET http://localhost:8000/api/tipos-espacios
```

### 6. Obtener resumen de espacios

```bash
curl -X GET http://localhost:8000/api/espacios/resumen
```

## Pruebas con HTTPie

Si prefieres usar HTTPie (más legible):

```bash
# Instalar HTTPie: pip install httpie

# Listar todos los espacios
http GET http://localhost:8000/api/espacios

# Listar con filtros
http GET http://localhost:8000/api/espacios tipo_espacio=="Sala de Clases" estado==Disponible

# Tipos de espacios
http GET http://localhost:8000/api/tipos-espacios

# Resumen
http GET http://localhost:8000/api/espacios/resumen
```

## Pruebas con Postman

1. Crear una nueva colección llamada "AulaSync - Espacios API"
2. Agregar las siguientes requests:

### Request 1: Listar Todos los Espacios
- **Método:** GET
- **URL:** `http://localhost:8000/api/espacios`
- **Headers:** 
  - Accept: application/json

### Request 2: Listar Espacios Filtrados
- **Método:** GET
- **URL:** `http://localhost:8000/api/espacios`
- **Params:**
  - tipo_espacio: Sala de Clases
  - estado: Disponible

### Request 3: Listar Tipos de Espacios
- **Método:** GET
- **URL:** `http://localhost:8000/api/tipos-espacios`
- **Headers:**
  - Accept: application/json

### Request 4: Resumen de Espacios
- **Método:** GET
- **URL:** `http://localhost:8000/api/espacios/resumen`
- **Headers:**
  - Accept: application/json

## Pruebas con JavaScript (Navegador)

Abre la consola del navegador en `http://localhost:8000` y ejecuta:

```javascript
// Listar todos los espacios
fetch('/api/espacios')
  .then(res => res.json())
  .then(data => {
    console.log('Total de espacios:', data.total);
    console.table(data.espacios);
  });

// Listar salas de clases disponibles
fetch('/api/espacios?tipo_espacio=Sala de Clases&estado=Disponible')
  .then(res => res.json())
  .then(data => {
    console.log('Salas de clases disponibles:', data.total);
    console.table(data.espacios);
  });

// Listar tipos de espacios
fetch('/api/tipos-espacios')
  .then(res => res.json())
  .then(data => {
    console.log('Tipos de espacios:');
    console.table(data.tipos_espacios);
  });

// Obtener resumen
fetch('/api/espacios/resumen')
  .then(res => res.json())
  .then(data => {
    console.log('Resumen de espacios:');
    console.table(data.resumen);
  });
```

## Resultados Esperados

### Espacios (ejemplo)

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
    }
  ]
}
```

### Tipos de Espacios (ejemplo)

```json
{
  "success": true,
  "total_tipos": 8,
  "tipos_espacios": [
    {
      "tipo_espacio": "Sala de Clases",
      "total_espacios": 25
    },
    {
      "tipo_espacio": "Laboratorio",
      "total_espacios": 5
    },
    {
      "tipo_espacio": "Taller",
      "total_espacios": 1
    }
  ]
}
```

### Resumen (ejemplo)

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
    }
  ]
}
```

## Verificación del Estado del Sistema

Puedes verificar que las rutas estén registradas correctamente con:

```bash
php artisan route:list --path=api/espacios
php artisan route:list --path=api/tipos
```

## Notas

- Si recibes un error 404, verifica que el servidor esté corriendo
- Si recibes un error 500, revisa los logs en `storage/logs/laravel.log`
- Asegúrate de que la base de datos tenga datos de prueba (seeders ejecutados)
