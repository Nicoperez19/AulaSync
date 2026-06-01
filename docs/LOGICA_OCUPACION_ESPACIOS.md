# Lógica de Ocupación de Espacios en SIA | Sistema de Informaci�n de Aulas

> **Última actualización:** 29 de octubre de 2025  
> **Contexto:** Implementación del endpoint `/api/reservas/activa/{id_espacio}`

---

## 📖 Resumen Ejecutivo

En SIA | Sistema de Informaci�n de Aulas, un espacio puede estar **OCUPADO** de **DOS formas diferentes**:

1. **Ocupación Formal:** Tiene una reserva activa registrada en el sistema
2. **Ocupación Manual:** El campo `estado` del espacio está marcado como "Ocupado" sin una reserva

Esta dualidad permite:
- ✅ Flexibilidad para uso espontáneo de espacios
- ✅ Control administrativo del estado de las salas
- ✅ Detección de ocupaciones sin reserva
- ✅ Mejor gestión de conflictos y disponibilidad

---

## 🔍 Modelo de Datos

### Tabla `espacios`

```sql
CREATE TABLE espacios (
    id_espacio VARCHAR PRIMARY KEY,  -- Ej: "TH-03", "TH-LAB1"
    nombre_espacio VARCHAR,
    tipo_espacio VARCHAR,
    estado VARCHAR,                   -- "Disponible", "Ocupado", "Reservado"
    puestos_disponibles INT,
    -- ... otros campos
);
```

**Estados posibles del campo `estado`:**
- `"Disponible"` - La sala está libre y puede reservarse
- `"Ocupado"` - La sala está en uso (puede tener o no reserva)
- `"Reservado"` - La sala tiene una reserva programada (futuro)

### Tabla `reservas`

```sql
CREATE TABLE reservas (
    id_reserva VARCHAR PRIMARY KEY,
    id_espacio VARCHAR,               -- FK a espacios
    estado VARCHAR,                   -- "activa", "finalizada", "cancelada"
    fecha_reserva DATE,
    hora TIME,
    hora_salida TIME,
    -- ... otros campos
);
```

**Estados posibles del campo `estado`:**
- `"activa"` - Reserva en curso ahora
- `"finalizada"` - Ya terminó
- `"cancelada"` - Fue cancelada
- `"programada"` - Está en el futuro

---

## 🎯 Definición: ¿Cuándo un Espacio está "Ocupado"?

### Condición Lógica

```javascript
const espacioOcupado = (reservaActiva !== null) || (espacio.estado === 'Ocupado');
```

Un espacio está ocupado si **SE CUMPLE AL MENOS UNA** de estas condiciones:

#### ✅ Condición 1: Tiene Reserva Activa

```sql
SELECT * FROM reservas 
WHERE id_espacio = 'TH-03'
  AND estado = 'activa'
  AND fecha_reserva = CURRENT_DATE
  AND hora <= CURRENT_TIME
  AND (hora_salida IS NULL OR hora_salida >= CURRENT_TIME)
```

Si esta query retorna resultados → **Espacio Ocupado** ✅

#### ✅ Condición 2: Estado Manual "Ocupado"

```sql
SELECT * FROM espacios 
WHERE id_espacio = 'TH-03'
  AND estado = 'Ocupado'
```

Si esta query retorna resultados → **Espacio Ocupado** ✅

---

## 📊 Escenarios Reales

### Escenario A: Clase Formal (Ocupación con Reserva)

**Contexto:**
- Profesor Juan tiene clase de Programación de 14:00 a 15:30
- Hizo su reserva mediante el sistema
- 35 estudiantes están registrados en asistencia

**Base de Datos:**
```
espacio TH-03:
  - estado = "Ocupado"
  - nombre_espacio = "Sala TH-03"
  
reserva R20251029140000:
  - id_espacio = "TH-03"
  - estado = "activa"
  - fecha_reserva = "2025-10-29"
  - hora = "14:00:00"
  - hora_salida = null
  - id_asignatura = "INF101"
```

**Consulta a las 14:45:**
```bash
GET /api/reservas/activa/TH-03
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Reserva activa encontrada - El espacio está ocupado",
  "data": {
    "espacio": {
      "id": "TH-03",
      "estado": "Ocupado",
      "ocupado": true  // ← IMPORTANTE
    },
    "reserva_activa": {
      "id": "R20251029140000",
      "tipo": "clase",
      // ... datos completos de la reserva
    },
    "asignatura": { /* INF101 */ },
    "asistencia": { /* 35 estudiantes */ }
  }
}
```

**Interpretación:**
- ✅ `reserva_activa` tiene datos → Hay reserva formal
- ✅ `espacio.ocupado = true` → El espacio está en uso
- ✅ App puede mostrar: "OCUPADO - Clase de Programación I"

---

### Escenario B: Ocupación Sin Reserva (Estado Manual)

**Contexto:**
- Personal de aseo entró a limpiar la sala
- No hicieron reserva formal
- Guardia de seguridad marcó manualmente: estado = "Ocupado"

**Base de Datos:**
```
espacio TH-03:
  - estado = "Ocupado"  ← Cambiado manualmente
  - nombre_espacio = "Sala TH-03"
  
reserva:
  - No existe ninguna reserva activa para TH-03 hoy
```

**Consulta a las 10:00:**
```bash
GET /api/reservas/activa/TH-03
```

**Respuesta:**
```json
{
  "success": true,
  "message": "El espacio está ocupado pero no tiene una reserva formal activa",
  "data": {
    "espacio": {
      "id": "TH-03",
      "estado": "Ocupado",
      "ocupado": true  // ← IMPORTANTE
    },
    "reserva_activa": null,  // ← No hay reserva
    "nota": "El espacio puede estar siendo usado sin una reserva formal"
  }
}
```

**Interpretación:**
- ❌ `reserva_activa = null` → NO hay reserva formal
- ✅ `espacio.ocupado = true` → Pero sí está ocupado
- ⚠️ App puede mostrar: "OCUPADO (sin reserva) - Verificar con personal"

---

### Escenario C: Espacio Disponible

**Contexto:**
- La sala está completamente libre
- No hay nadie dentro
- Sin reservas activas

**Base de Datos:**
```
espacio TH-03:
  - estado = "Disponible"
  - nombre_espacio = "Sala TH-03"
  
reserva:
  - No hay reservas activas
```

**Consulta a las 12:00:**
```bash
GET /api/reservas/activa/TH-03
```

**Respuesta:**
```json
{
  "success": true,
  "message": "El espacio está disponible, no hay reserva activa en este momento",
  "data": {
    "espacio": {
      "id": "TH-03",
      "estado": "Disponible",
      "ocupado": false  // ← IMPORTANTE
    },
    "reserva_activa": null
  }
}
```

**Interpretación:**
- ❌ `reserva_activa = null` → NO hay reserva
- ❌ `espacio.ocupado = false` → NO está ocupado
- ✅ App puede mostrar: "DISPONIBLE ✅ - Puedes reservar"

---

### Escenario D: Entre Clases (Gap)

**Contexto:**
- Clase anterior terminó a las 13:30
- Próxima clase comienza a las 16:00
- Estado se actualizó automáticamente a "Disponible"

**Base de Datos:**
```
espacio TH-03:
  - estado = "Disponible"
  
reserva anterior:
  - estado = "finalizada"
  - hora_salida = "13:30:00"
  
reserva próxima:
  - estado = "programada"  (no "activa" todavía)
  - hora = "16:00:00"
```

**Consulta a las 15:00:**
```bash
GET /api/reservas/activa/TH-03
```

**Respuesta:**
```json
{
  "success": true,
  "message": "El espacio está disponible...",
  "data": {
    "espacio": {
      "ocupado": false
    },
    "reserva_activa": null
  }
}
```

**Interpretación:**
- ⚠️ Aunque hay una reserva a las 16:00, NO es "activa" todavía
- ✅ El endpoint solo reporta reservas ACTIVAS EN ESTE MOMENTO
- ✅ El espacio está disponible para uso temporal (reserva espontánea)

---

## 💡 Casos de Uso de Integración

### 1. App Móvil de Estudiantes

```javascript
async function verificarDisponibilidadSala(idEspacio) {
  const response = await fetch(`/api/reservas/activa/${idEspacio}`);
  const data = await response.json();
  
  if (!data.success) {
    return { disponible: false, error: 'Espacio no encontrado' };
  }
  
  const espacio = data.data.espacio;
  
  // Caso 1: Espacio disponible
  if (!espacio.ocupado) {
    return {
      disponible: true,
      mensaje: '✅ Puedes usar esta sala',
      accion: 'PERMITIR_RESERVA'
    };
  }
  
  // Caso 2: Ocupado con reserva formal
  if (data.data.reserva_activa) {
    const reserva = data.data.reserva_activa;
    return {
      disponible: false,
      mensaje: `⛔ Clase de ${reserva.asignatura?.nombre}`,
      profesor: reserva.usuario_reserva?.nombre,
      horaFin: reserva.hora_salida,
      accion: 'MOSTRAR_INFO_CLASE'
    };
  }
  
  // Caso 3: Ocupado sin reserva
  return {
    disponible: false,
    mensaje: '⚠️ Ocupado (sin reserva formal)',
    nota: 'Verificar con personal',
    accion: 'CONTACTAR_ADMINISTRACION'
  };
}
```

### 2. Pantalla Digital en Entrada de Sala

```javascript
function mostrarEstadoEnPantalla(idEspacio) {
  fetch(`/api/reservas/activa/${idEspacio}`)
    .then(r => r.json())
    .then(data => {
      const espacio = data.data.espacio;
      const reserva = data.data.reserva_activa;
      
      if (!espacio.ocupado) {
        // Mostrar pantalla VERDE
        mostrar({
          color: 'verde',
          texto: 'DISPONIBLE',
          icono: '✅'
        });
      } else if (reserva) {
        // Mostrar pantalla ROJA con info
        mostrar({
          color: 'rojo',
          texto: 'EN CLASE',
          asignatura: reserva.asignatura.nombre,
          profesor: reserva.usuario_reserva.nombre,
          horaFin: reserva.hora_salida,
          icono: '📚'
        });
      } else {
        // Mostrar pantalla NARANJA
        mostrar({
          color: 'naranja',
          texto: 'OCUPADO',
          nota: 'Uso temporal',
          icono: '⚠️'
        });
      }
    });
}
```

### 3. Sistema de Reportes/Analítica

```sql
-- Obtener todas las salas que están ocupadas SIN reserva formal
SELECT e.id_espacio, e.nombre_espacio, e.estado
FROM espacios e
LEFT JOIN reservas r ON r.id_espacio = e.id_espacio 
  AND r.estado = 'activa'
  AND r.fecha_reserva = CURRENT_DATE
WHERE e.estado = 'Ocupado'
  AND r.id_reserva IS NULL;
  
-- Esto identifica ocupaciones "irregulares" que requieren verificación
```

---

## ⚙️ Gestión del Estado

### ¿Quién/Qué cambia el estado del espacio?

#### Automático (Sistema)

1. **Al iniciar reserva:**
   ```php
   $espacio->estado = 'Ocupado';
   $espacio->save();
   ```

2. **Al finalizar reserva:**
   ```php
   $espacio->estado = 'Disponible';
   $espacio->save();
   ```

#### Manual (Usuarios)

1. **Por personal administrativo** (via interfaz web)
2. **Por guardias de seguridad** (via app móvil)
3. **Por sistema de control de acceso** (integración con puertas)

---

## 🚨 Consideraciones Importantes

### 1. Sincronización

El campo `espacio.estado` DEBE estar sincronizado con las reservas:
- ✅ Si hay reserva activa → estado debería ser "Ocupado"
- ⚠️ Si estado es "Ocupado" pero no hay reserva → Verificar motivo

### 2. Limpieza de Estados

Implementar un cron job que verifique:
```php
// Espacios marcados como "Ocupado" sin reserva activa hace más de 2 horas
Espacio::where('estado', 'Ocupado')
    ->whereDoesntHave('reservas', function($q) {
        $q->where('estado', 'activa')
          ->where('fecha_reserva', today());
    })
    ->update(['estado' => 'Disponible']);
```

### 3. Prioridad de la Información

Cuando `espacio.ocupado = true`:
1. **Primero:** Verificar si hay `reserva_activa`
2. **Si hay reserva:** Usar esa información (es más confiable)
3. **Si NO hay reserva:** Mostrar alerta de ocupación sin reserva

---

## 📱 Ejemplos de UI/UX

### Card de Estado en App Móvil

```
┌─────────────────────────────┐
│  Sala TH-03                 │
│  📍 Piso 1 - Edificio Torre │
├─────────────────────────────┤
│  🟢 DISPONIBLE              │
│  40 puestos                 │
│                             │
│  [  Reservar Ahora  ]       │
└─────────────────────────────┘
```

```
┌─────────────────────────────┐
│  Sala TH-03                 │
│  📍 Piso 1 - Edificio Torre │
├─────────────────────────────┤
│  🔴 OCUPADO                 │
│  Programación I - Sección A │
│  Prof. Carlos Rodríguez     │
│  Hasta: 15:30               │
│                             │
│  [  Ver Detalles  ]         │
└─────────────────────────────┘
```

```
┌─────────────────────────────┐
│  Sala TH-03                 │
│  📍 Piso 1 - Edificio Torre │
├─────────────────────────────┤
│  🟠 OCUPADO                 │
│  ⚠️ Sin reserva formal      │
│                             │
│  [  Reportar a Admin  ]     │
└─────────────────────────────┘
```

---

## 🔗 Referencias

- [API_RESERVA_ACTIVA_ESPACIO.md](API_RESERVA_ACTIVA_ESPACIO.md) - Documentación completa del endpoint
- [ProgramacionSemanalController.php](../app/Http/Controllers/Api/ProgramacionSemanalController.php) - Implementación del código
- [Espacio.php](../app/Models/Espacio.php) - Modelo de datos

---

## 🔧 Manejo de Estados Inconsistentes en Plano Digital

### Problema: Botón "Desocupar" No Funciona

**Escenario:**
- El espacio tiene `estado = "Ocupado"` en la base de datos
- PERO la API `/api/espacio/{id}/informacion-detallada` responde con `tipo_ocupacion: 'libre'`
- Esto puede suceder cuando:
  - Hay una reserva vencida que no fue finalizada correctamente
  - El campo `estado` no se sincronizó al terminar una reserva
  - Hubo un error en el proceso de devolución

**Solución Implementada (30-Oct-2025):**

En el archivo `resources/views/layouts/plano_digital/show.blade.php`, función `renderizarInformacionLibre()`:

1. **Detección del estado inconsistente:**
   ```javascript
   const espacioDisponible = indicator && (
       indicator.estado === 'Disponible' ||
       indicator.estado === 'disponible' ||
       indicator.estado === '#059669' ||
       indicator.estado === '#10b981'
   );
   
   if (!espacioDisponible) {
       // API dice "libre" pero indicator dice "Ocupado"
       // ¡Estado inconsistente detectado!
   }
   ```

2. **Mantener botón desocupar visible:**
   ```javascript
   const btnsDesocupar = document.querySelectorAll('.btn-desocupar[data-tipo="espacio"]');
   btnsDesocupar.forEach(btn => {
       btn.classList.remove('hidden');
   });
   ```

3. **Configurar desocupación forzosa:**
   ```javascript
   const runValue = data.run_profesor || data.run_solicitante || `FORCE_${indicator.id}`;
   ```

4. **Mostrar mensaje claro al usuario:**
   - "Estado inconsistente"
   - "El espacio requiere desocupación manual"

5. **Retorno temprano para evitar que el botón se oculte:**
   ```javascript
   return; // Salir antes de que el código posterior oculte el botón
   ```

**Resultado:**
- ✅ Los administradores siempre pueden desocupar espacios atascados
- ✅ El sistema muestra feedback claro sobre el problema
- ✅ La desocupación forzosa funciona con identificador `FORCE_{space_id}`
- ✅ Compatibilidad con casos normales se mantiene

---

## 📝 Historial de Cambios

| Fecha | Cambio | Autor |
|-------|--------|-------|
| 2025-10-30 | Fix: Botón desocupar siempre visible para estados inconsistentes | GitHub Copilot |
| 2025-10-29 | Creación del documento y lógica dual de ocupación | Sistema |

