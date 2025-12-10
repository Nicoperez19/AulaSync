# Multi-Tenancy Quick Reference Guide

## Quick Start

### Setup (First Time)
```bash
# 1. Run migrations
php artisan migrate

# 2. Create tenants from existing sedes
php artisan db:seed --class=TenantSeeder

# 3. List tenants
php artisan tenant:list
```

### Development Environment Setup
Add to your `hosts` file:
```
127.0.0.1 principal.aulasync.local
127.0.0.1 campus-norte.aulasync.local
```

Update `.env`:
```env
APP_URL=http://aulasync.local
MULTITENANCY_ENABLED=true
```

## Common Tasks

### Create a New Tenant
```bash
php artisan tenant:create my-campus --sede=SEDE001 --prefix=MC
```

### Check Current Tenant in Code
```php
$tenant = tenant();
if ($tenant) {
    echo "Current tenant: " . $tenant->name;
    echo "Space prefix: " . $tenant->prefijo_espacios;
}
```

### Make a Model Tenant-Aware
```php
use App\Traits\BelongsToTenant;

class MyModel extends Model
{
    use BelongsToTenant;
}
```

### Query Without Tenant Filter
```php
// Get all records (from all tenants)
MyModel::withoutGlobalScope('tenant')->get();
```

## Helper Functions

```php
tenant()          // Get current tenant instance
tenant_id()       // Get current tenant ID
tenant_domain()   // Get current tenant domain/subdomain
tenant_prefijo()  // Get current tenant space prefix
```

## Model Relationships & Filtering

### Direct Filtering (sede_id)
- `Profesor` → Filtered by `sede_id`

### Space Prefix Filtering
- `Espacio` → Filtered by `id_espacio LIKE 'prefix%'`

### Relationship-Based Filtering
- `Piso` → via `facultad.sede_id`
- `Mapa` → via `piso.facultad.sede_id`
- `Planificacion_Asignatura` → via `espacio` or `profesor`
- `Reserva` → via `espacio` or `profesor`
- `Horario` → via `profesor`
- `Asignatura` → via `profesor`

## Configuration Files

### `config/multitenancy.php`
Main configuration for multi-tenancy system

### `.env`
```env
MULTITENANCY_ENABLED=true
MULTITENANCY_SEPARATE_DATABASES=false
DB_TENANT_DATABASE="${DB_DATABASE}"
```

## Database Tables

### `tenants`
Stores tenant configuration
- `domain` → subdomain (unique)
- `name` → display name
- `prefijo_espacios` → space prefix
- `sede_id` → linked sede
- `database` → tenant database (nullable)
- `is_active` → active status

### Modified Tables
- `profesors` → Added `sede_id` column

## Middleware

### Global Middleware
`TenantMiddleware` is applied globally and:
1. Extracts subdomain from request
2. Finds matching tenant
3. Sets tenant as current
4. Filters all subsequent queries

### Route Middleware (Optional)
```php
Route::middleware('tenant')->group(function () {
    // Routes that require tenant
});
```

## Testing

### Unit Tests
```php
$tenant = Tenant::create([...]);
$tenant->makeCurrent();

// Now all queries are scoped to this tenant
```

### Feature Tests
```bash
php artisan test --filter=MultiTenancy
```

## Troubleshooting

### Issue: Tenant not found
**Solution**: Check that subdomain matches `domain` in `tenants` table

### Issue: Data not filtered
**Solution**: Ensure model has `BelongsToTenant` trait

### Issue: Space prefix not working
**Solution**: Verify `prefijo_espacios` is set in tenant and spaces follow prefix pattern

## Best Practices

1. ✅ Always use Eloquent (avoid raw SQL)
2. ✅ Use helper functions for tenant access
3. ✅ Test with multiple tenants
4. ✅ Document tenant-specific logic
5. ❌ Don't share data between tenants
6. ❌ Don't hardcode tenant IDs
7. ❌ Don't bypass tenant scopes unless necessary

## Examples

### Creating Records
```php
// Records are automatically scoped to current tenant
$espacio = Espacio::create([
    'id_espacio' => 'SALA101',  // Will be prefixed if needed
    'nombre_espacio' => 'Sala 101',
    // ... other fields
]);
```

### Querying Records
```php
// Only returns spaces for current tenant
$espacios = Espacio::all();

// Filter within current tenant
$espacios = Espacio::where('tipo_espacio', 'Sala de Clases')->get();
```

### Bulk Operations
```php
// Import data - automatically scoped to current tenant
foreach ($rows as $row) {
    Planificacion_Asignatura::create([...]);
}
```

## URLs & Access

### Production
```
https://principal.aulasync.com
https://campus-norte.aulasync.com
```

### Development
```
http://principal.aulasync.local
http://campus-norte.aulasync.local
```

## Support

- 📖 Full Documentation: `MULTITENANCY_IMPLEMENTATION.md`
- 🇪🇸 Spanish Guide: `MULTITENANCY_SPANISH.md`
- 💻 Code: `app/Traits/BelongsToTenant.php`
- 🔧 Middleware: `app/Http/Middleware/TenantMiddleware.php`
