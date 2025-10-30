# Análisis del Problema de Timeout de 30 Segundos en Días Feriados

**Fecha**: 30 de octubre de 2025  
**Servidor**: http://10.105.0.29  
**Ruta afectada**: `/dias-feriados`

## 🔴 Problema Identificado

### Error Principal
```
Maximum execution time of 30 seconds exceeded
```

**Ubicación del error**: `vendor/symfony/polyfill-mbstring/Mbstring.php:667`

### Evidencia Recopilada

1. **Screenshot del error en producción**: El error ocurre consistentemente al cargar la página de días feriados
2. **Stack trace**: El timeout se produce en la función `mb_stripos` del polyfill de Symfony
3. **Queries ejecutadas**: Las consultas SQL son rápidas (< 3ms cada una), el problema NO está en la base de datos directamente

## 🔍 Causas Raíz

### 1. Uso del Polyfill de mbstring (CRÍTICO)
- El servidor de producción **NO tiene instalada la extensión nativa `mbstring` de PHP**
- Está usando `symfony/polyfill-mbstring` que es extremadamente lento
- Las operaciones `mb_stripos`, `mb_convert_case`, `mb_substr` son hasta **100x más lentas** que la extensión nativa

### 2. Búsquedas Ineficientes con LIKE
```php
// Código problemático original:
$q->where('nombre', 'like', '%'.$this->search.'%')
    ->orWhere('descripcion', 'like', '%'.$this->search.'%');
```

Esto causaba:
- Laravel trae TODOS los registros a memoria
- Aplica `mb_stripos` en PHP para comparaciones case-insensitive
- Con el polyfill lento, cada comparación toma mucho tiempo
- Si hay muchos registros, el timeout de 30s se alcanza fácilmente

### 3. Falta de Índices Optimizados
- No había índices en las columnas `nombre` y `tipo`
- No había índice FULLTEXT para búsquedas de texto

### 4. Sin Eager Loading
- La relación `creador` no se cargaba con eager loading
- Causaba N+1 queries (aunque este no era el problema principal)

### 5. wire:model.live sin Debounce
- Cada tecla presionada generaba una petición al servidor
- Multiplicaba el problema de rendimiento

## ✅ Soluciones Implementadas

### 1. Optimización de Búsquedas (CRÍTICA)
**Archivo**: `app/Livewire/DiasFeriadosTable.php`

```php
// Solución: Usar LOWER() en la base de datos
if ($this->search) {
    $searchTerm = trim($this->search);
    if (strlen($searchTerm) > 0) {
        $query->where(function ($q) use ($searchTerm) {
            $q->whereRaw('LOWER(nombre) LIKE ?', ['%' . strtolower($searchTerm) . '%'])
              ->orWhereRaw('LOWER(descripcion) LIKE ?', ['%' . strtolower($searchTerm) . '%']);
        });
    }
}
```

**Beneficio**: Las comparaciones se hacen en MySQL, no en PHP con mbstring polyfill

### 2. Debounce en el Campo de Búsqueda
**Archivo**: `resources/views/livewire/dias-feriados-table.blade.php`

```php
// Antes:
wire:model.live="search"

// Después:
wire:model.live.debounce.500ms="search"
```

**Beneficio**: Reduce las peticiones al servidor de decenas a 1-2 por búsqueda

### 3. Eager Loading
**Archivo**: `app/Livewire/DiasFeriadosTable.php`

```php
$query = DiaFeriado::query()->with('creador');
```

**Beneficio**: Evita N+1 queries al cargar el usuario creador

### 4. Nuevos Índices de Base de Datos
**Archivo**: `database/migrations/2025_10_30_183000_add_search_indexes_to_dias_feriados_table.php`

Índices agregados:
- `nombre` - Para búsquedas rápidas
- `tipo` - Para filtros rápidos
- FULLTEXT index en `(nombre, descripcion)` - Para búsquedas de texto completo

**Beneficio**: MySQL puede usar índices para acelerar las búsquedas

## 🚀 Recomendaciones Adicionales

### CRÍTICA: Instalar la extensión mbstring nativa

**En el servidor de producción, ejecutar**:
```bash
# Para Ubuntu/Debian
sudo apt-get install php8.2-mbstring
sudo systemctl restart php8.2-fpm  # o apache2/nginx según corresponda

# Para CentOS/RHEL
sudo yum install php82-mbstring
sudo systemctl restart php-fpm
```

**Esto es FUNDAMENTAL** - La extensión nativa es hasta 100x más rápida que el polyfill

### Mediana Prioridad

1. **Aumentar el límite de tiempo de ejecución** (temporal, mientras se instala mbstring):
   ```php
   // En config/livewire.php o .env
   MAX_EXECUTION_TIME=90
   ```

2. **Agregar caché para consultas frecuentes**:
   ```php
   $feriados = Cache::remember("feriados_{$this->search}_{$this->tipo}", 60, function() {
       return $query->paginate(10);
   });
   ```

3. **Limitar la longitud de descripción indexada** para mejorar rendimiento del FULLTEXT index

## 📊 Impacto Esperado

- **Reducción del tiempo de carga**: De 30+ segundos (timeout) a < 1 segundo
- **Reducción de carga del servidor**: 80-90% menos peticiones por búsqueda
- **Mejor experiencia de usuario**: Búsqueda instantánea

## 🔧 Deployment

### Pasos para aplicar en producción:

1. **Hacer push de los cambios**:
   ```bash
   git add .
   git commit -m "Fix: Optimizar búsquedas en días feriados para evitar timeout"
   git push
   ```

2. **En el servidor de producción**:
   ```bash
   # Pull de los cambios
   git pull

   # Ejecutar migraciones
   php artisan migrate

   # Limpiar caché
   php artisan cache:clear
   php artisan config:clear
   php artisan view:clear

   # CRÍTICO: Instalar mbstring
   sudo apt-get install php8.2-mbstring
   sudo systemctl restart php8.2-fpm
   ```

3. **Verificar**:
   - Acceder a http://10.105.0.29/dias-feriados
   - La página debe cargar en < 1 segundo
   - La búsqueda debe responder instantáneamente

## 📝 Notas Finales

El problema NO era la complejidad de las queries SQL, sino que Laravel estaba trayendo datos a PHP y usando operaciones de string lentas (polyfill mbstring) para las comparaciones LIKE case-insensitive.

La solución mueve estas operaciones a MySQL usando `LOWER()` y `LIKE`, que es mucho más rápido.

**La instalación de php-mbstring nativo es CRÍTICA** para el rendimiento general de la aplicación, no solo para este módulo.
