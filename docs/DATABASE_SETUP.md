# AulaManager - Configuración de Bases de Datos Multi-Tenant

## Arquitectura de Bases de Datos

El sistema usa una arquitectura multi-tenant con bases de datos separadas:

| Base de Datos | Propósito | Contenedor Docker |
|---------------|-----------|-------------------|
| `aulamanager_central` | BD central (usuarios, tenants, configuración global) | `db_central` |
| `aulamanager_th` | Tenant Talca | `db_th` |
| `aulamanager_ch` | Tenant Chillán | `db_ch` |
| `aulamanager_la` | Tenant Los Ángeles | `db_la` |
| `aulamanager_ct` | Tenant Curicó | `db_ct` |

## Credenciales

Un solo usuario para todas las operaciones:

```
Usuario: aulamanager
Password: aulamanager_secret
```

No se requieren credenciales administrativas (root) porque:
- Las bases de datos se crean automáticamente por Docker
- El comando `tenants:setup` solo ejecuta migraciones y seeders

## Operaciones del Comando `tenants:setup`

### ¿Qué hace el comando?

```bash
php artisan tenants:setup [--seed] [--fresh]
```

1. **Lista los tenants** configurados en la BD central
2. **Para cada tenant:**
   - Configura la conexión `tenant` con la BD del tenant
   - Ejecuta migraciones desde `database/migrations/tenant/`
   - (Opcional) Ejecuta seeders si se usa `--seed`

### ¿Bajo qué usuario se ejecutan las operaciones?

| Operación | Conexión Laravel | Usuario BD | Archivo de Config |
|-----------|------------------|------------|-------------------|
| Leer tenants | `mysql` (default) | `aulamanager` | config/database.php → mysql |
| Migrar tenant | `tenant` | `aulamanager` | config/database.php → tenant |
| Seeders tenant | `tenant` | `aulamanager` | config/database.php → tenant |

### Código relevante

**Archivo:** `app/Console/Commands/SetupTenantDatabases.php`

```php
// Línea ~88: Configura la conexión para el tenant
config(['database.connections.tenant.database' => $dbName]);
app('db')->purge('tenant');

// Línea ~100: Ejecuta migraciones
Artisan::call('migrate', [
    '--database' => 'tenant',
    '--path' => 'database/migrations/tenant',
    '--force' => true,
]);

// Línea ~118: Ejecuta seeders (si --seed)
Artisan::call('db:seed', [
    '--class' => 'TenantDatabaseSeeder',
    '--database' => 'tenant',
    '--force' => true,
]);
```

## Uso con Docker

### Iniciar todos los contenedores

```bash
docker-compose up -d
```

Esto inicia:
- 5 contenedores MariaDB (1 central + 4 tenants)
- 1 contenedor PHP-FPM (aplicación)
- 1 contenedor Nginx (servidor web)

### Ejecutar migraciones

```bash
# BD Central
docker-compose exec app php artisan migrate --database=mysql --path=database/migrations/central

# Todos los tenants
docker-compose exec app php artisan tenants:setup --seed
```

### Verificar conexiones

```bash
# Probar conexión a BD central
docker-compose exec app php artisan tinker --execute="DB::connection()->getPdo(); echo 'Central OK';"

# Probar conexión a tenant específico
docker-compose exec app php artisan tinker --execute="
    config(['database.connections.tenant.host' => 'db_th', 'database.connections.tenant.database' => 'aulamanager_th']);
    DB::connection('tenant')->getPdo();
    echo 'Tenant TH OK';
"
```

## Configuración de Tenants en BD

Para Docker con contenedores separados, cada tenant debe tener configurado su `database_host`:

```sql
UPDATE tenants SET 
    database = 'aulamanager_th',
    database_host = 'db_th'
WHERE domain = 'talca';

UPDATE tenants SET 
    database = 'aulamanager_ch',
    database_host = 'db_ch'
WHERE domain = 'chillan';

UPDATE tenants SET 
    database = 'aulamanager_la',
    database_host = 'db_la'
WHERE domain = 'losangeles';

UPDATE tenants SET 
    database = 'aulamanager_ct',
    database_host = 'db_ct'
WHERE domain = 'curico';
```

## Variables de Entorno (.env)

```env
# Base de datos central
DB_CONNECTION=mysql
DB_HOST=db_central        # Para Docker
DB_HOST=localhost         # Para desarrollo local
DB_PORT=3306
DB_DATABASE=aulamanager_central
DB_USERNAME=aulamanager
DB_PASSWORD=aulamanager_secret

# Multi-tenancy
MULTITENANCY_ENABLED=true
MULTITENANCY_SEPARATE_DATABASES=true
```

## Diferencias con la configuración anterior

| Aspecto | Antes | Ahora |
|---------|-------|-------|
| Motor BD | MySQL 8.0 | MariaDB 10.11 |
| Contenedores BD | 1 (todas las BDs) | 5 (1 por BD) |
| Usuarios BD | 3 (root, aulasync, gestoraulasit) | 1 (aulamanager) |
| Creación de BDs | Por comando `tenants:setup` | Por Docker automáticamente |
| Nombre central | `gestoraulasit` | `aulamanager_central` |
| Prefijo tenants | `aulasync_` | `aulamanager_` |
