# Tests de API - Programación Semanal y Asistencia

Este documento contiene scripts de prueba para verificar el funcionamiento de las APIs.

## Tests con PowerShell

### 1. Test: Consultar Programación Semanal

```powershell
# Test básico de consulta de programación
$idEspacio = "A101"  # Cambiar por un ID válido de su sistema

try {
    $response = Invoke-RestMethod -Uri "http://localhost:8000/api/programacion-semanal/$idEspacio" -Method Get
    
    if ($response.success) {
        Write-Host "✓ Programación obtenida exitosamente" -ForegroundColor Green
        Write-Host "  Espacio: $($response.data.espacio.nombre)"
        Write-Host "  Período: $($response.data.periodo)"
        Write-Host "  Días con programación: $($response.data.programacion_semanal.PSObject.Properties.Name -join ', ')"
    } else {
        Write-Host "✗ Error: $($response.message)" -ForegroundColor Red
    }
} catch {
    Write-Host "✗ Error de conexión: $_" -ForegroundColor Red
}
```

### 2. Test: Registrar Asistencia (Caso Exitoso)

```powershell
# Test de registro exitoso
$datosAsistencia = @{
    id_reserva = "R202508211455301"  # Cambiar por un ID válido
    hora_termino = "10:00:00"
    lista_asistencia = @(
        @{
            rut = "12345678"
            nombre = "Juan Pérez García"
            hora_llegada = "08:15:00"
        },
        @{
            rut = "87654321"
            nombre = "María López Silva"
            hora_llegada = "08:10:00"
        }
    )
    contenido_visto = "Introducción a las derivadas y límites matemáticos"
} | ConvertTo-Json -Depth 10

try {
    $response = Invoke-RestMethod -Uri "http://localhost:8000/api/asistencia" -Method Post -Body $datosAsistencia -ContentType "application/json"
    
    if ($response.success) {
        Write-Host "✓ Asistencia registrada exitosamente" -ForegroundColor Green
        Write-Host "  Reserva: $($response.data.reserva.id)"
        Write-Host "  Total asistentes: $($response.data.total_asistentes)"
        Write-Host "  Espacio: $($response.data.reserva.espacio)"
    } else {
        Write-Host "✗ Error: $($response.message)" -ForegroundColor Red
    }
} catch {
    $errorDetails = $_.ErrorDetails.Message | ConvertFrom-Json
    Write-Host "✗ Error: $($errorDetails.message)" -ForegroundColor Red
    if ($errorDetails.errors) {
        Write-Host "  Errores de validación:" -ForegroundColor Yellow
        $errorDetails.errors.PSObject.Properties | ForEach-Object {
            Write-Host "    $($_.Name): $($_.Value -join ', ')" -ForegroundColor Yellow
        }
    }
}
```

### 3. Test: Validación - Datos Incompletos

```powershell
# Test de validación con datos incompletos
$datosInvalidos = @{
    id_reserva = "R202508211455301"
    # hora_termino faltante
    lista_asistencia = @()  # Lista vacía
} | ConvertTo-Json

try {
    $response = Invoke-RestMethod -Uri "http://localhost:8000/api/asistencia" -Method Post -Body $datosInvalidos -ContentType "application/json"
} catch {
    $errorDetails = $_.ErrorDetails.Message | ConvertFrom-Json
    Write-Host "✓ Validación funcionando correctamente" -ForegroundColor Green
    Write-Host "  Errores encontrados:" -ForegroundColor Yellow
    $errorDetails.errors.PSObject.Properties | ForEach-Object {
        Write-Host "    $($_.Name): $($_.Value -join ', ')" -ForegroundColor Yellow
    }
}
```

### 4. Test: Asistencia sin Contenido Visto

```powershell
# Test sin campo contenido_visto (debe guardar "Sin información adicionada")
$datosSinContenido = @{
    id_reserva = "R202508211455302"
    hora_termino = "12:00:00"
    lista_asistencia = @(
        @{
            rut = "12345678"
            nombre = "Juan Pérez García"
            hora_llegada = "10:15:00"
        }
    )
} | ConvertTo-Json -Depth 10

try {
    $response = Invoke-RestMethod -Uri "http://localhost:8000/api/asistencia" -Method Post -Body $datosSinContenido -ContentType "application/json"
    
    if ($response.success) {
        Write-Host "✓ Asistencia registrada sin contenido visto" -ForegroundColor Green
        Write-Host "  Contenido guardado: $($response.data.contenido_visto)"
        
        if ($response.data.contenido_visto -eq "Sin información adicionada") {
            Write-Host "✓ Valor por defecto aplicado correctamente" -ForegroundColor Green
        }
    }
} catch {
    Write-Host "✗ Error: $_" -ForegroundColor Red
}
```

### 5. Test Suite Completo

```powershell
# Script de prueba completo
function Test-ProgramacionAPI {
    param(
        [string]$BaseUrl = "http://localhost:8000/api",
        [string]$IdEspacio = "A101",
        [string]$IdReserva = "R202508211455301"
    )
    
    Write-Host "`n========================================" -ForegroundColor Cyan
    Write-Host "INICIANDO TESTS DE API" -ForegroundColor Cyan
    Write-Host "========================================`n" -ForegroundColor Cyan
    
    $testsPasados = 0
    $testsFallidos = 0
    
    # Test 1: Consultar Programación
    Write-Host "[TEST 1] Consultar Programación Semanal" -ForegroundColor Cyan
    try {
        $response = Invoke-RestMethod -Uri "$BaseUrl/programacion-semanal/$IdEspacio" -Method Get
        if ($response.success) {
            Write-Host "  ✓ PASÓ" -ForegroundColor Green
            $testsPasados++
        } else {
            Write-Host "  ✗ FALLÓ: $($response.message)" -ForegroundColor Red
            $testsFallidos++
        }
    } catch {
        Write-Host "  ✗ FALLÓ: $_" -ForegroundColor Red
        $testsFallidos++
    }
    
    # Test 2: Registrar Asistencia Completa
    Write-Host "`n[TEST 2] Registrar Asistencia con Contenido" -ForegroundColor Cyan
    $datosCompletos = @{
        id_reserva = $IdReserva
        hora_termino = "10:00:00"
        lista_asistencia = @(@{
            rut = "12345678"
            nombre = "Test Usuario"
            hora_llegada = "08:15:00"
        })
        contenido_visto = "Test de contenido"
    } | ConvertTo-Json -Depth 10
    
    try {
        $response = Invoke-RestMethod -Uri "$BaseUrl/asistencia" -Method Post -Body $datosCompletos -ContentType "application/json"
        if ($response.success) {
            Write-Host "  ✓ PASÓ" -ForegroundColor Green
            $testsPasados++
        } else {
            Write-Host "  ✗ FALLÓ: $($response.message)" -ForegroundColor Red
            $testsFallidos++
        }
    } catch {
        Write-Host "  ✗ FALLÓ (esperado si la reserva no existe)" -ForegroundColor Yellow
    }
    
    # Test 3: Validación de Datos
    Write-Host "`n[TEST 3] Validación de Datos Incorrectos" -ForegroundColor Cyan
    $datosInvalidos = @{
        id_reserva = "invalido"
        hora_termino = "hora-incorrecta"
        lista_asistencia = @()
    } | ConvertTo-Json
    
    try {
        $response = Invoke-RestMethod -Uri "$BaseUrl/asistencia" -Method Post -Body $datosInvalidos -ContentType "application/json"
        Write-Host "  ✗ FALLÓ: Debería haber validado los datos" -ForegroundColor Red
        $testsFallidos++
    } catch {
        Write-Host "  ✓ PASÓ: Validación funcionando" -ForegroundColor Green
        $testsPasados++
    }
    
    # Resumen
    Write-Host "`n========================================" -ForegroundColor Cyan
    Write-Host "RESUMEN DE TESTS" -ForegroundColor Cyan
    Write-Host "========================================" -ForegroundColor Cyan
    Write-Host "Tests pasados: $testsPasados" -ForegroundColor Green
    Write-Host "Tests fallidos: $testsFallidos" -ForegroundColor Red
    Write-Host "Total: $($testsPasados + $testsFallidos)" -ForegroundColor Cyan
}

# Ejecutar suite de tests
Test-ProgramacionAPI
```

## Tests con cURL (Bash)

### 1. Test: Consultar Programación Semanal

```bash
#!/bin/bash

echo "Test: Consultar Programación Semanal"
curl -X GET http://localhost:8000/api/programacion-semanal/A101 \
  -H "Accept: application/json" \
  | jq '.'
```

### 2. Test: Registrar Asistencia

```bash
#!/bin/bash

echo "Test: Registrar Asistencia"
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
    "contenido_visto": "Test de contenido"
  }' \
  | jq '.'
```

### 3. Script de Tests Completo (Bash)

```bash
#!/bin/bash

# Colores para la salida
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

BASE_URL="http://localhost:8000/api"
ID_ESPACIO="A101"
ID_RESERVA="R202508211455301"

echo -e "${CYAN}========================================"
echo "INICIANDO TESTS DE API"
echo -e "========================================${NC}\n"

# Test 1: Consultar Programación
echo -e "${CYAN}[TEST 1] Consultar Programación Semanal${NC}"
response=$(curl -s -X GET "$BASE_URL/programacion-semanal/$ID_ESPACIO")
if echo "$response" | jq -e '.success == true' > /dev/null 2>&1; then
    echo -e "  ${GREEN}✓ PASÓ${NC}"
else
    echo -e "  ${RED}✗ FALLÓ${NC}"
fi

# Test 2: Registrar Asistencia
echo -e "\n${CYAN}[TEST 2] Registrar Asistencia${NC}"
response=$(curl -s -X POST "$BASE_URL/asistencia" \
  -H "Content-Type: application/json" \
  -d '{
    "id_reserva": "'$ID_RESERVA'",
    "hora_termino": "10:00:00",
    "lista_asistencia": [{
      "rut": "12345678",
      "nombre": "Test Usuario",
      "hora_llegada": "08:15:00"
    }],
    "contenido_visto": "Test"
  }')

if echo "$response" | jq -e '.success == true' > /dev/null 2>&1; then
    echo -e "  ${GREEN}✓ PASÓ${NC}"
else
    echo -e "  ${YELLOW}✗ FALLÓ (esperado si no existe la reserva)${NC}"
fi

# Test 3: Validación
echo -e "\n${CYAN}[TEST 3] Validación de Datos${NC}"
response=$(curl -s -X POST "$BASE_URL/asistencia" \
  -H "Content-Type: application/json" \
  -d '{
    "id_reserva": "invalido",
    "lista_asistencia": []
  }')

if echo "$response" | jq -e '.errors' > /dev/null 2>&1; then
    echo -e "  ${GREEN}✓ PASÓ: Validación funcionando${NC}"
else
    echo -e "  ${RED}✗ FALLÓ: No validó correctamente${NC}"
fi

echo -e "\n${CYAN}========================================${NC}"
echo -e "${CYAN}TESTS COMPLETADOS${NC}"
echo -e "${CYAN}========================================${NC}"
```

## Tests con Postman/Insomnia

### Colección de Postman

```json
{
  "info": {
    "name": "AulaSync - Programación y Asistencia API",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "Consultar Programación Semanal",
      "request": {
        "method": "GET",
        "header": [],
        "url": {
          "raw": "{{base_url}}/api/programacion-semanal/{{id_espacio}}",
          "host": ["{{base_url}}"],
          "path": ["api", "programacion-semanal", "{{id_espacio}}"]
        }
      }
    },
    {
      "name": "Registrar Asistencia - Completa",
      "request": {
        "method": "POST",
        "header": [
          {
            "key": "Content-Type",
            "value": "application/json"
          }
        ],
        "body": {
          "mode": "raw",
          "raw": "{\n  \"id_reserva\": \"{{id_reserva}}\",\n  \"hora_termino\": \"10:00:00\",\n  \"lista_asistencia\": [\n    {\n      \"rut\": \"12345678\",\n      \"nombre\": \"Juan Pérez García\",\n      \"hora_llegada\": \"08:15:00\"\n    },\n    {\n      \"rut\": \"87654321\",\n      \"nombre\": \"María López Silva\",\n      \"hora_llegada\": \"08:10:00\"\n    }\n  ],\n  \"contenido_visto\": \"Introducción a las derivadas\"\n}"
        },
        "url": {
          "raw": "{{base_url}}/api/asistencia",
          "host": ["{{base_url}}"],
          "path": ["api", "asistencia"]
        }
      }
    },
    {
      "name": "Registrar Asistencia - Sin Contenido",
      "request": {
        "method": "POST",
        "header": [
          {
            "key": "Content-Type",
            "value": "application/json"
          }
        ],
        "body": {
          "mode": "raw",
          "raw": "{\n  \"id_reserva\": \"{{id_reserva}}\",\n  \"hora_termino\": \"10:00:00\",\n  \"lista_asistencia\": [\n    {\n      \"rut\": \"12345678\",\n      \"nombre\": \"Juan Pérez García\",\n      \"hora_llegada\": \"08:15:00\"\n    }\n  ]\n}"
        },
        "url": {
          "raw": "{{base_url}}/api/asistencia",
          "host": ["{{base_url}}"],
          "path": ["api", "asistencia"]
        }
      }
    },
    {
      "name": "Test Validación - Datos Inválidos",
      "request": {
        "method": "POST",
        "header": [
          {
            "key": "Content-Type",
            "value": "application/json"
          }
        ],
        "body": {
          "mode": "raw",
          "raw": "{\n  \"id_reserva\": \"invalido\",\n  \"hora_termino\": \"hora-incorrecta\",\n  \"lista_asistencia\": []\n}"
        },
        "url": {
          "raw": "{{base_url}}/api/asistencia",
          "host": ["{{base_url}}"],
          "path": ["api", "asistencia"]
        }
      }
    }
  ],
  "variable": [
    {
      "key": "base_url",
      "value": "http://localhost:8000"
    },
    {
      "key": "id_espacio",
      "value": "A101"
    },
    {
      "key": "id_reserva",
      "value": "R202508211455301"
    }
  ]
}
```

## Verificación de Base de Datos

### Script para verificar datos guardados

```sql
-- Verificar asistencias registradas
SELECT 
    a.id,
    a.id_reserva,
    a.rut_asistente,
    a.nombre_asistente,
    a.hora_llegada,
    a.hora_termino,
    a.contenido_visto,
    r.id_espacio,
    r.fecha_reserva,
    r.estado
FROM asistencias a
JOIN reservas r ON a.id_reserva = r.id_reserva
ORDER BY a.created_at DESC
LIMIT 10;

-- Contar asistentes por reserva
SELECT 
    id_reserva,
    COUNT(*) as total_asistentes,
    MIN(hora_llegada) as primera_llegada,
    MAX(hora_llegada) as ultima_llegada
FROM asistencias
GROUP BY id_reserva;

-- Verificar contenido visto por defecto
SELECT 
    id,
    id_reserva,
    contenido_visto,
    CASE 
        WHEN contenido_visto = 'Sin información adicionada' THEN 'Valor por defecto'
        ELSE 'Con contenido'
    END as tipo_contenido
FROM asistencias
ORDER BY created_at DESC;
```

## Checklist de Pruebas

- [ ] GET `/api/programacion-semanal/{id_espacio}` devuelve programación correctamente
- [ ] GET con espacio inexistente devuelve error 404
- [ ] POST `/api/asistencia` con datos completos registra correctamente
- [ ] POST sin `contenido_visto` guarda "Sin información adicionada"
- [ ] POST con `contenido_visto` null guarda "Sin información adicionada"
- [ ] POST con lista de asistencia vacía devuelve error 422
- [ ] POST con formato de hora inválido devuelve error 422
- [ ] POST con reserva inexistente devuelve error 404
- [ ] Reserva cambia a estado "finalizada" después de registrar asistencia
- [ ] Espacio cambia a "Disponible" después de registrar asistencia
- [ ] Múltiples asistentes se registran correctamente
- [ ] Transacción se revierte en caso de error
