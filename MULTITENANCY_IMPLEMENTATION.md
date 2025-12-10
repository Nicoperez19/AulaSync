# Multi-Tenancy Implementation

## Overview
This implementation provides multi-tenancy support for AulaSync based on subdomain detection. Each tenant (sede) has its own isolated data for spaces, maps, floors, schedules, and professors.

## Features
- **Subdomain-based tenant identification**: Each sede is accessed via its own subdomain (e.g., `principal.aulasync.com`)
- **Data isolation**: Tenant data is automatically filtered using Laravel's global scopes
- **Prefix-based space filtering**: Spaces are filtered by their prefix according to the tenant's configuration
- **Flexible database architecture**: Supports both shared database (with scoped data) and separate databases per tenant

## Architecture

### Components

1. **Tenant Model** (`App\Models\Tenant`)
   - Stores tenant configuration (name, domain, database, space prefix, sede reference)
   - Methods to set/get current tenant

2. **TenantMiddleware** (`App\Http\Middleware\TenantMiddleware`)
   - Identifies tenant from subdomain
   - Sets the current tenant for the request
   - Applied globally to all web requests

3. **BelongsToTenant Trait** (`App\Traits\BelongsToTenant`)
   - Applied to models that should be tenant-aware
   - Adds global scope to filter data by tenant
   - Automatically assigns tenant on model creation

4. **Tenant-Aware Models**
   - `Espacio`: Filtered by space prefix
   - `Mapa`: Filtered through piso → facultad → sede relationship
   - `Piso`: Filtered through facultad → sede relationship
   - `Planificacion_Asignatura`: Filtered through espacio relationship
   - `Profesor`: Filtered by sede_id
   - `Asignatura`: Filtered through profesor relationship
   - `Reserva`: Filtered through espacio and profesor relationships
   - `Horario`: Filtered through profesor relationship

5. **Artisan Commands**
   - `tenant:create`: Create a new tenant
   - `tenant:list`: List all tenants

## Configuration

### Environment Variables
Add to your `.env` file:

```env
MULTITENANCY_ENABLED=true
MULTITENANCY_SEPARATE_DATABASES=false
DB_TENANT_DATABASE="${DB_DATABASE}"
```

### Config File
Configuration is in `config/multitenancy.php`:
- Enable/disable multi-tenancy
- Configure tenant identification method
- Set database separation strategy
- Define tenant-aware models and tables

## Database Structure

### Tenants Table
```sql
- id: bigint (primary key)
- name: string (tenant name, e.g., "Sede Principal")
- domain: string (subdomain, e.g., "principal")
- database: string (tenant database name, nullable)
- prefijo_espacios: string (space prefix for this tenant)
- sede_id: string (foreign key to sedes table)
- is_active: boolean
- timestamps
```

### Modified Tables
The following tables have been modified to support multi-tenancy:
- `profesors`: Added `sede_id` column

## Usage

### Setting Up Tenants
1. Run migrations:
   ```bash
   php artisan migrate
   ```

2. Seed initial tenants (creates one tenant per sede):
   ```bash
   php artisan db:seed --class=TenantSeeder
   ```

3. Or create tenants manually using the command:
   ```bash
   php artisan tenant:create {domain} --name="Tenant Name" --sede={sede_id} --prefix={prefix}
   ```

4. For production, enable schema caching for better performance:
   ```bash
   php artisan schema:cache
   ```

### Managing Tenants

#### List all tenants
```bash
php artisan tenant:list
```

#### List only active tenants
```bash
php artisan tenant:list --active
```

#### Create a new tenant
```bash
# Create a tenant for a sede
php artisan tenant:create principal --sede=SEDE001 --name="Sede Principal"

# Create a tenant with custom prefix
php artisan tenant:create campus-norte --sede=SEDE002 --prefix=CN
```

### Accessing Tenants
Users access different tenants via subdomains:
- `principal.aulasync.com` → Tenant for "Sede Principal"
- `campus-norte.aulasync.com` → Tenant for "Campus Norte"

### Getting Current Tenant
```php
// In controllers or views
$tenant = tenant();
$tenantId = tenant_id();
$tenantDomain = tenant_domain();
$tenantPrefijo = tenant_prefijo();
```

### Creating Tenant-Aware Models
To make a model tenant-aware, add the `BelongsToTenant` trait:

```php
use App\Traits\BelongsToTenant;

class MyModel extends Model
{
    use BelongsToTenant;
    // ...
}
```

### Manual Tenant Scoping
If you need to bypass tenant scoping temporarily:

```php
// Get all records regardless of tenant
MyModel::withoutGlobalScope('tenant')->get();
```

## Data Isolation

### Shared Database Mode (Default)
All tenants share the same database, but data is filtered using:
1. **Space Prefix**: Models with `id_espacio` are filtered by the tenant's `prefijo_espacios`
2. **Sede Relationship**: Models are filtered through their relationship to `sedes` table
3. **Global Scopes**: Applied automatically via the `BelongsToTenant` trait

### Separate Database Mode
When `MULTITENANCY_SEPARATE_DATABASES=true`:
- Each tenant has its own database
- Database name follows pattern: `aulasync_{domain}`
- Connection switches automatically based on current tenant
- Requires manual database creation for each tenant

## Migration Strategy

When making changes to tenant-aware tables:
1. Create migration as usual
2. Run migration on all tenant databases (if using separate databases)
3. Or run once on shared database (if using shared mode)

## Bulk Data Loading
When performing bulk data loads (like semester schedule):
1. Data is automatically scoped to current tenant
2. Space IDs are prefixed with tenant's `prefijo_espacios`
3. Professors are filtered by `sede_id`
4. Planning is scoped through space relationships

## Localidad and Space Prefix
The implementation respects the locality and space prefix configuration:
- Each sede (tenant) has its `prefijo_sala` defined in the `sedes` table
- This prefix is used to filter and create spaces for that specific sede
- When loading schedules, the system uses the tenant's prefix to match spaces

## Security Considerations
- Tenant isolation is enforced at the application layer
- Always use the `BelongsToTenant` trait for tenant-aware models
- Avoid raw queries that bypass Eloquent's global scopes
- Validate that users can only access their tenant's data

## Helper Functions
```php
tenant()           // Get current tenant instance
tenant_id()        // Get current tenant ID
tenant_domain()    // Get current tenant domain
tenant_prefijo()   // Get current tenant space prefix
```

## Testing
When testing multi-tenancy features:
1. Create test tenants in your test database
2. Manually set the current tenant: `$tenant->makeCurrent()`
3. Verify data isolation between tenants
4. Test subdomain detection with different host values

## Troubleshooting

### Tenant Not Found
- Check subdomain configuration in hosts file or DNS
- Verify tenant exists and is active in `tenants` table
- Check middleware is registered in `Kernel.php`

### Data Not Filtered
- Ensure model has `BelongsToTenant` trait
- Verify tenant is set before querying
- Check relationships are properly defined

### Space Prefix Issues
- Verify `prefijo_sala` is set in `sedes` table
- Check `prefijo_espacios` matches in `tenants` table
- Ensure space IDs follow the prefix pattern
