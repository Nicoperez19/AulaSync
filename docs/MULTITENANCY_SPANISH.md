# Implementación de Multi-Tenancy (Multi-inquilino)

## Resumen
Esta implementación proporciona soporte multi-tenant para AulaSync basado en la detección de subdominios. Cada tenant (sede) tiene sus propios datos aislados para espacios, mapas, pisos, planificaciones y profesores.

## ¿Qué es Multi-Tenancy?
Multi-tenancy es un patrón de arquitectura donde una única instancia de la aplicación sirve a múltiples clientes (tenants). En el contexto de AulaSync, cada sede funciona como un tenant separado con sus propios datos.

## Características Principales

### 1. Identificación por Subdominio
Cada sede se accede a través de su propio subdominio:
- `principal.aulasync.com` → Sede Principal
- `campus-norte.aulasync.com` → Campus Norte
- `bellavista.aulasync.com` → Sede Bellavista

### 2. Aislamiento de Datos
Los datos se filtran automáticamente según el tenant actual:
- **Espacios**: Filtrados por prefijo (ej: espacios que comienzan con "SP" para Sede Principal)
- **Mapas**: Filtrados a través de la relación piso → facultad → sede
- **Pisos**: Filtrados a través de la relación facultad → sede
- **Profesores**: Filtrados por sede_id
- **Planificaciones**: Filtradas a través de la relación con espacios
- **Reservas**: Filtradas a través de espacios y profesores
- **Horarios**: Filtrados a través de profesores
- **Asignaturas**: Filtradas a través de profesores

### 3. Prefijo de Espacios
Cada sede tiene un prefijo único para sus espacios:
- Sede Principal: `SP`
- Campus Norte: `CN`
- Bellavista: `BV`

Este prefijo se usa para:
- Identificar automáticamente a qué sede pertenece un espacio
- Filtrar espacios en carga masiva de planificación
- Asegurar que cada sede solo vea sus propios espacios

### 4. Base de Datos Compartida o Separada
El sistema soporta dos modos:

**Modo Compartido (por defecto)**:
- Todos los tenants comparten la misma base de datos
- Los datos se filtran automáticamente usando scopes de Eloquent
- Más eficiente en recursos
- Recomendado para la mayoría de casos

**Modo Separado**:
- Cada tenant tiene su propia base de datos
- Mayor aislamiento y seguridad
- Requiere más recursos del servidor

## Configuración Inicial

### 1. Variables de Entorno
Agregar a tu archivo `.env`:

```env
MULTITENANCY_ENABLED=true
MULTITENANCY_SEPARATE_DATABASES=false
DB_TENANT_DATABASE="${DB_DATABASE}"
```

### 2. Ejecutar Migraciones
```bash
php artisan migrate
```

### 3. Crear Tenants Iniciales
```bash
# Crear un tenant por cada sede existente
php artisan db:seed --class=TenantSeeder
```

### 4. Crear Tenants Manualmente
```bash
# Crear un tenant para una sede específica
php artisan tenant:create principal --sede=SEDE001 --name="Sede Principal"

# Crear un tenant con prefijo personalizado
php artisan tenant:create campus-norte --sede=SEDE002 --prefix=CN
```

## Uso del Sistema

### Acceso por Subdominio
Los usuarios acceden al sistema usando el subdominio correspondiente a su sede:

```
http://principal.aulasync.local
http://campus-norte.aulasync.local
http://bellavista.aulasync.local
```

### Configuración de Hosts (Desarrollo Local)
Para desarrollo local, agregar al archivo `hosts`:

**Windows**: `C:\Windows\System32\drivers\etc\hosts`
**Linux/Mac**: `/etc/hosts`

```
127.0.0.1 principal.aulasync.local
127.0.0.1 campus-norte.aulasync.local
127.0.0.1 bellavista.aulasync.local
```

### Carga Masiva de Planificación
Al realizar carga masiva de planificación semestral:

1. El sistema identifica automáticamente el tenant actual
2. Filtra los espacios por el prefijo del tenant
3. Asigna solo los profesores de esa sede
4. Crea planificaciones solo para esa sede

**Ejemplo**:
- Si estás en `principal.aulasync.com`
- Y la Sede Principal tiene prefijo `SP`
- Solo se cargarán espacios que comiencen con `SP`
- Solo se asignarán profesores con `sede_id` de Sede Principal

## Comandos Artisan

### Listar Tenants
```bash
# Listar todos los tenants
php artisan tenant:list

# Listar solo tenants activos
php artisan tenant:list --active
```

### Crear Tenant
```bash
php artisan tenant:create {subdominio} [opciones]

Opciones:
  --name=       Nombre del tenant
  --sede=       ID de la sede a asociar
  --prefix=     Prefijo para espacios
  --database=   Nombre de la base de datos (opcional)
```

**Ejemplos**:
```bash
# Crear tenant para Sede Principal
php artisan tenant:create principal --sede=SEDE001 --prefix=SP

# Crear tenant con todos los parámetros
php artisan tenant:create campus-norte \
  --name="Campus Norte" \
  --sede=SEDE002 \
  --prefix=CN
```

## Funciones Helper

El sistema proporciona funciones helper para acceder al tenant actual:

```php
// Obtener instancia del tenant actual
$tenant = tenant();

// Obtener ID del tenant actual
$id = tenant_id();

// Obtener subdominio del tenant actual
$domain = tenant_domain();

// Obtener prefijo de espacios del tenant actual
$prefix = tenant_prefijo();
```

## Implementación en Código

### Hacer un Modelo Tenant-Aware
Para que un modelo sea consciente del tenant, agregar el trait `BelongsToTenant`:

```php
use App\Traits\BelongsToTenant;

class MiModelo extends Model
{
    use BelongsToTenant;
    // ...
}
```

### Bypass del Filtrado de Tenant (Usar con Precaución)
Si necesitas acceder a datos de todos los tenants:

```php
// Obtener todos los registros sin filtro de tenant
MiModelo::withoutGlobalScope('tenant')->get();
```

## Estructura de Base de Datos

### Tabla `tenants`
```sql
CREATE TABLE tenants (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255),              -- Nombre del tenant
    domain VARCHAR(255) UNIQUE,     -- Subdominio
    database VARCHAR(255) NULL,     -- Base de datos del tenant
    prefijo_espacios VARCHAR(255),  -- Prefijo de espacios
    sede_id VARCHAR(20),            -- FK a sedes
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Tabla `profesors` (Modificada)
Se agregó el campo `sede_id` para filtrado directo por sede.

## Flujo de Trabajo

### 1. Usuario Accede al Sistema
```
Usuario → principal.aulasync.com
    ↓
Middleware TenantMiddleware detecta subdominio "principal"
    ↓
Busca tenant con domain = "principal"
    ↓
Establece tenant como actual
    ↓
Todas las consultas se filtran automáticamente
```

### 2. Carga Masiva de Datos
```
Usuario sube archivo Excel desde campus-norte.aulasync.com
    ↓
Sistema detecta tenant "campus-norte"
    ↓
Filtra espacios por prefijo "CN"
    ↓
Filtra profesores por sede_id del tenant
    ↓
Crea planificaciones solo para ese tenant
```

## Seguridad

### Aislamiento de Datos
- Los datos se filtran a nivel de aplicación usando Eloquent
- Cada consulta incluye automáticamente el filtro de tenant
- No es posible acceder a datos de otro tenant sin bypass explícito

### Recomendaciones
1. **No usar consultas SQL raw**: Siempre usar Eloquent para aprovechar los scopes
2. **Validar permisos**: Aunque los datos están aislados, validar permisos de usuario
3. **Logs de auditoría**: Registrar accesos y modificaciones por tenant
4. **Backups separados**: Considerar backups por tenant para recuperación selectiva

## Solución de Problemas

### Tenant No Encontrado
**Síntoma**: Error 404 "Tenant no encontrado"

**Soluciones**:
1. Verificar que el subdominio esté configurado en DNS o archivo hosts
2. Verificar que el tenant existe en la base de datos
3. Verificar que el tenant esté activo (`is_active = true`)

### Datos No Filtrados Correctamente
**Síntoma**: Se ven datos de otras sedes

**Soluciones**:
1. Verificar que el modelo tenga el trait `BelongsToTenant`
2. Verificar que el tenant esté establecido antes de consultar
3. Verificar las relaciones del modelo (sede, facultad, piso, etc.)

### Problemas con Prefijo de Espacios
**Síntoma**: Espacios no se filtran por prefijo

**Soluciones**:
1. Verificar que `prefijo_sala` esté definido en la tabla `sedes`
2. Verificar que `prefijo_espacios` esté sincronizado en la tabla `tenants`
3. Verificar que los IDs de espacios sigan el patrón del prefijo

## Mantenimiento

### Agregar Nueva Sede
```bash
# 1. Crear la sede en la base de datos
# 2. Crear el tenant
php artisan tenant:create {subdominio} --sede={id_sede} --prefix={prefijo}

# 3. Verificar
php artisan tenant:list
```

### Migrar Datos Entre Tenants
No recomendado. El diseño asume que los datos pertenecen exclusivamente a un tenant.

### Actualizar Prefijo de Tenant
```php
$tenant = Tenant::where('domain', 'principal')->first();
$tenant->prefijo_espacios = 'NuevoPrefijo';
$tenant->save();
```

## Preguntas Frecuentes

**¿Puedo tener múltiples sedes en un mismo tenant?**
No. Cada tenant representa una sede única.

**¿Los usuarios pueden acceder a múltiples tenants?**
Depende de la implementación de autenticación. El sistema permite usuarios globales o por tenant.

**¿Qué pasa si cambio el subdominio?**
El tenant se identifica por subdominio. Cambiar el subdominio requiere actualizar la configuración DNS y el campo `domain` en la tabla `tenants`.

**¿Puedo desactivar multi-tenancy?**
Sí, establecer `MULTITENANCY_ENABLED=false` en `.env`, pero esto desactivará el filtrado automático.

## Soporte Técnico

Para más información, consultar:
- `MULTITENANCY_IMPLEMENTATION.md` (documentación técnica en inglés)
- Código fuente en `app/Traits/BelongsToTenant.php`
- Middleware en `app/Http/Middleware/TenantMiddleware.php`
