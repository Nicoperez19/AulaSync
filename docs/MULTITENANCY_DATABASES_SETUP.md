# Guía de Configuración Multi-Tenancy - Bases de Datos Separadas

## Arquitectura

### Database Central (`aulasync`)
Contiene datos compartidos y de catálogo:
- **Universidades** - Catálogo de instituciones
- **Sedes** - Todas las sedes del sistema
- **Campus** - Todos los campus
- **Facultades** - Todas las facultades
- **Áreas Académicas** - Todas las áreas
- **Carreras** - Todas las carreras
- **Tenants** - Configuración de tenants
- **Usuarios** - Usuarios administrativos centrales
- **Roles y Permisos** - Sistema de autenticación
- **Datos de Chile** - Regiones, provincias, comunas
- **Módulos** - Configuración de horarios
- **Días Feriados** - Calendario nacional

### Databases por Tenant
Cada sede tiene su propia database con datos operativos:
- `aulasync_th` → Talcahuano
- `aulasync_ct` → Cañete
- `aulasync_ch` → Chillán
- `aulasync_la` → Los Ángeles
- `aulasync_ccp` → Concepción

**Contienen solo**:
- **Pisos** - Específicos de la sede
- **Espacios** - Específicos de la sede
- **Reservas** - De la sede
- **Profesores** - De la sede
- **Asignaturas** - De la sede
- **Asistencias** - De la sede
- **Planificaciones** - De la sede

## Configuración Inicial

### 1. Verificar configuración en `.env`

```env
MULTITENANCY_ENABLED=true
MULTITENANCY_SEPARATE_DATABASES=true
```

### 2. Crear y sembrar la database central

```bash
# Migrar la database principal
php artisan migrate

# Sembrar datos centralizados (sedes, tenants, roles, etc.)
php artisan db:seed --class=CentralDatabaseSeeder
```

### 3. Configurar databases de tenants

```bash
# Crear databases, ejecutar migraciones y seeders para cada tenant
php artisan tenants:setup --seed

# O solo crear databases y migrar (sin datos)
php artisan tenants:setup

# Para recrear todo desde cero (⚠️ ELIMINA TODOS LOS DATOS)
php artisan tenants:setup --fresh --seed
```

## Estructura de Seeders

### Seeders Centralizados (CentralDatabaseSeeder)
Ejecutar en la database principal `aulasync`:
- `RoleSeeder` - Roles del sistema
- `UserSeeder` - Usuarios administradores
- `AdministracionChileSeeder` - Regiones, provincias, comunas
- `UniversidadSeeder` - Universidades
- `SedeSeeder` - Todas las sedes
- `TenantSeeder` - Configuración de tenants
- `ModulosSeeder` - Módulos horarios
- `TiposCorreosMasivosSeeder` - Tipos de correos
- `DiasFeriadosSeeder` - Feriados nacionales

### Seeders por Tenant (TenantDatabaseSeeder)
Ejecutar en cada database de tenant (filtrados por sede):
- `CampusSeeder` - Campus de la sede
- `FacultadSeeder` - Facultades de la sede
- `AreaAcademicaSeeder` - Áreas académicas de la sede
- `PisoSeeder` - Pisos de la sede
- `CarreraSeeder` - Carreras de la sede
- `EspacioSeeder` - Espacios de la sede

## Comandos Útiles

```bash
# Ver tenants configurados
php artisan tinker
>>> Tenant::all()

# Ejecutar seeder específico para un tenant
php artisan db:seed --class=TenantDatabaseSeeder --database=tenant

# Refrescar migraciones en database de tenant específica
php artisan migrate:fresh --database=tenant
```

## Flujo de Trabajo

1. **Desarrollo local**:
   - Los cambios se realizan en la database central y tenants locales
   - Cada desarrollador tiene sus propias databases

2. **Agregar una nueva sede**:
   - Agregar en `SedeSeeder`
   - Ejecutar `php artisan db:seed --class=SedeSeeder`
   - Ejecutar `php artisan db:seed --class=TenantSeeder`
   - Ejecutar `php artisan tenants:setup --seed`

3. **Actualizar estructura de datos**:
   - Crear migración: `php artisan make:migration nombre_migracion`
   - Migrar en central: `php artisan migrate`
   - Migrar en todos los tenants: `php artisan tenants:setup`

## Importante

- ✅ **Usuarios**: Cada sede solo puede ver/usar usuarios de su propia database
- ✅ **Espacios**: Cada sede solo ve sus propios espacios
- ✅ **Reservas**: Aisladas por sede
- ✅ **Sedes**: Información compartida para permitir enlaces entre sistemas
- ❌ **No compartido**: Credenciales, reservas, profesores, estudiantes

## Acceso Multi-Sede

Aunque las databases están separadas, la tabla `sedes` está centralizada para permitir:
- Ver qué otras sedes existen
- Redirigir al sistema de otra sede
- NO permite usar credenciales de una sede en otra

## Testing

```bash
# Test en database central
php artisan test

# Test en contexto de tenant específico
# (Por implementar - requiere configurar TestCase para tenants)
```
