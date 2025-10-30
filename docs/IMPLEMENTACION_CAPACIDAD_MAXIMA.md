# Implementación de Capacidad Máxima en Espacios

## Cambios Realizados

### 1. Base de Datos

#### Migración: `2025_10_30_190000_add_capacidad_maxima_to_espacios_table.php`

**Columna agregada:**
- `capacidad_maxima` (integer, NOT NULL, default: 0)

**Migración de datos:**
- Los valores actuales de `puestos_disponibles` se copiaron automáticamente a `capacidad_maxima`
- `puestos_disponibles` ahora representa los puestos actualmente disponibles
- `capacidad_maxima` representa la capacidad máxima del espacio

### 2. Modelo Espacio

**Archivo:** `app/Models/Espacio.php`

**Cambios:**
1. Agregado `capacidad_maxima` al array `$fillable`

2. **Nuevo Accessor: `capacidad_utilizada`**
   ```php
   public function getCapacidadUtilizadaAttribute()
   {
       return max(0, $this->capacidad_maxima - $this->puestos_disponibles);
   }
   ```
   - Calcula: `capacidad_maxima - puestos_disponibles`
   - Retorna siempre un valor >= 0

3. **Nuevo Accessor: `porcentaje_ocupacion`**
   ```php
   public function getPorcentajeOcupacionAttribute()
   {
       if ($this->capacidad_maxima == 0) return 0;
       return round(($this->capacidad_utilizada / $this->capacidad_maxima) * 100, 1);
   }
   ```
   - Calcula el porcentaje de ocupación
   - Redondea a 1 decimal

### 3. Componente Livewire: ModulosActualesTable

**Archivo:** `app/Livewire/ModulosActualesTable.php`

**Cambios:**
- Agregado `capacidad_maxima` al array de datos de espacios en el método `actualizarDatos()`
- Se incluye en ambos estados: con módulo activo y sin módulo activo

### 4. Vista: modulos-actuales-table.blade.php

**Archivo:** `resources/views/livewire/modulos-actuales-table.blade.php`

**Cambios en la tabla:**

1. **Header actualizado:**
   ```html
   <th>Modulo</th>    <!-- w-1/5 -->
   <th>Espacio</th>   <!-- w-1/12 -->
   <th>Clase</th>     <!-- w-5/12 -->
   <th>Capacidad</th> <!-- w-1/12 --> ← NUEVA COLUMNA
   <th>Status</th>    <!-- w-1/6 -->
   ```

2. **Nueva celda de Capacidad:**
   - Muestra: `capacidad_utilizada / capacidad_maxima`
   - **Barra de progreso visual** con colores:
     - 🟢 Verde: 0-49% ocupación
     - 🟡 Amarillo: 50-69% ocupación
     - 🟠 Naranja: 70-89% ocupación
     - 🔴 Rojo: 90-100% ocupación
   - Texto coloreado según ocupación
   - Muestra "N/A" si capacidad_maxima es 0

## Cálculo de Capacidad Utilizada

### Fórmula:
```
capacidad_utilizada = capacidad_maxima - puestos_disponibles
```

### Ejemplo:
- Capacidad Máxima: 40 personas
- Puestos Disponibles: 15 personas
- **Capacidad Utilizada: 25 personas** (40 - 15)
- **Porcentaje: 62.5%** (25 / 40 * 100)

## Visualización en la Interfaz

### Ejemplo de Celda de Capacidad:

**Alta ocupación (90%+):**
```
🔴 36/40
[████████████████████▓] 90%
```

**Media ocupación (50-69%):**
```
🟡 25/40
[████████████▓▓▓▓▓▓▓▓] 62.5%
```

**Baja ocupación (<50%):**
```
🟢 10/40
[█████▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓] 25%
```

## Uso en el Modelo

### Acceso directo a propiedades calculadas:

```php
$espacio = Espacio::find('TH-101');

// Acceder a capacidad utilizada
$utilizada = $espacio->capacidad_utilizada; // Ej: 25

// Acceder a porcentaje de ocupación
$porcentaje = $espacio->porcentaje_ocupacion; // Ej: 62.5
```

## Beneficios

1. ✅ **Visualización clara** de la ocupación de cada espacio
2. ✅ **Colores intuitivos** para identificar rápidamente espacios llenos
3. ✅ **Barra de progreso** visual para mejor comprensión
4. ✅ **Cálculo automático** de capacidad utilizada
5. ✅ **Datos históricos preservados** en capacidad_maxima
6. ✅ **Fácil mantenimiento** con accessors en el modelo

## Deployment

### Pasos para aplicar en producción:

```bash
# 1. Pull de los cambios
git pull

# 2. Ejecutar migraciones
php artisan migrate

# 3. Limpiar caché
php artisan cache:clear
php artisan view:clear

# 4. Verificar en la interfaz
# Acceder a la vista de módulos actuales y verificar la columna "Capacidad"
```

## Notas Importantes

- ⚠️ La columna `puestos_disponibles` ahora representa los puestos **actualmente disponibles**, no la capacidad total
- ⚠️ La capacidad máxima se debe actualizar manualmente si cambia físicamente el espacio
- ✅ El sistema calcula automáticamente la capacidad utilizada en tiempo real
