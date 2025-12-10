# Testing Multi-Tenancy Setup

This guide helps you test the multi-tenancy implementation in AulaSync.

## Prerequisites

- MySQL database server running
- PHP 8.1+
- Composer dependencies installed
- Laravel configured with database credentials

## Setup Steps

### 1. Configure Environment

Copy `.env.example` to `.env` and configure your database:

```bash
cp .env.example .env
```

Edit `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=aulasync
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 2. Run Landlord Migrations

First, create the main database and run landlord migrations:

```bash
php artisan migrate --path=database/migrations/landlord
```

This creates the `tenants` table in your main database.

### 3. Seed Base Data

If you have seeders for regiones, provincias, and comunas, run them:

```bash
php artisan db:seed --class=RegionesSeeder
php artisan db:seed --class=ProvinciasSeeder
php artisan db:seed --class=ComunasSeeder
```

### 4. Create Demo Tenants

Run the tenant demo seeder to create sample tenants:

```bash
php artisan db:seed --class=TenantDemoSeeder
```

This will:
- Create sample sedes (Maipú, Santiago, Talcahuano)
- Create tenant databases for each sede
- Run tenant migrations for each database
- Set up subdomain mappings

### 5. Configure Local Hosts

For local testing, add entries to your `/etc/hosts` (Linux/Mac) or `C:\Windows\System32\drivers\etc\hosts` (Windows):

```
127.0.0.1  maipu.localhost
127.0.0.1  santiago.localhost
127.0.0.1  talcahuano.localhost
```

### 6. Start the Development Server

```bash
php artisan serve
```

## Testing Tenant Isolation

### Test 1: Access Different Tenants

Open your browser and navigate to:
- http://maipu.localhost:8000
- http://santiago.localhost:8000
- http://talcahuano.localhost:8000

Each should load the application with a different tenant context.

### Test 2: Create Tenant-Specific Data

1. Access http://maipu.localhost:8000
2. Create some spaces (espacios) specific to Maipú
3. Access http://santiago.localhost:8000
4. Verify that Maipú's spaces are NOT visible
5. Create different spaces for Santiago

### Test 3: Verify Database Isolation

Check that each tenant has its own database:

```bash
mysql -u root -p
```

```sql
SHOW DATABASES LIKE 'tenant_%';
USE tenant_sedemaipu;
SHOW TABLES;
SELECT * FROM espacios;

USE tenant_sedesantiago;
SHOW TABLES;
SELECT * FROM espacios;
```

You should see different data in each tenant database.

### Test 4: Bulk Upload (Data Load)

1. Access http://maipu.localhost:8000/data-load
2. Upload an Excel file with semester planning data
3. Ensure the sede column in the Excel matches "Maipú"
4. Verify data is created only in the Maipú tenant database
5. Access http://santiago.localhost:8000/data-load
6. Verify that Maipú's data is NOT visible

## Creating Additional Tenants

To create a new tenant manually:

```bash
php artisan tenant:create SEDE_ID
```

Example:
```bash
php artisan tenant:create SEDE_VALPARAISO
# Enter subdomain: valparaiso
```

This will:
1. Find the sede with ID "SEDE_VALPARAISO"
2. Create a database named "tenant_sedevalparaiso"
3. Run all tenant migrations
4. Set up the tenant record

## Verifying Multi-Tenancy Features

### Feature Checklist

- [ ] Different subdomains load different tenant contexts
- [ ] Spaces (espacios) are isolated per tenant
- [ ] Professors (profesores) are scoped to their tenant
- [ ] Maps (mapas) and floors (pisos) are tenant-specific
- [ ] Semester planning (planificacion_asignaturas) is isolated
- [ ] Bulk uploads validate sede matching current tenant
- [ ] File uploads are stored in tenant-specific directories
- [ ] QR codes are generated per tenant
- [ ] Users cannot access data from other tenants

### Expected Behavior

✓ **Working correctly:**
- Each tenant has completely isolated data
- Switching subdomains switches to different tenant database
- Models automatically use tenant connection
- Bulk uploads filter by current tenant's sede

✗ **Should NOT happen:**
- Seeing data from another tenant
- Creating data in wrong tenant database
- Cross-tenant data access
- Shared spaces between tenants

## Troubleshooting

### "Tenant not found" Error

**Problem:** Accessing a subdomain results in "Tenant not found"

**Solution:**
1. Verify tenant exists: `SELECT * FROM tenants WHERE domain = 'subdomain';`
2. Ensure hosts file is configured correctly
3. Clear Laravel cache: `php artisan cache:clear`

### Database Connection Error

**Problem:** "Database does not exist" error

**Solution:**
1. Check if tenant database was created: `SHOW DATABASES;`
2. If missing, run: `php artisan tenant:create SEDE_ID` again
3. Verify database credentials in `.env`

### No Data Visible in Tenant

**Problem:** Tenant database is empty

**Solution:**
1. Check migrations ran: `php artisan tenants:artisan "migrate:status" --tenant=1`
2. Re-run migrations if needed: `php artisan tenants:artisan "migrate" --tenant=1`
3. Seed test data if available

### Wrong Tenant Data Showing

**Problem:** Seeing data from different tenant

**Solution:**
1. Clear cache: `php artisan cache:clear`
2. Restart server
3. Verify middleware is registered in `app/Http/Kernel.php`
4. Check tenant finder in `config/multitenancy.php`

## Advanced Testing

### Testing with PHPUnit

Create tests that verify tenant isolation:

```php
public function test_tenant_data_isolation()
{
    $tenant1 = Tenant::where('domain', 'maipu')->first();
    $tenant2 = Tenant::where('domain', 'santiago')->first();
    
    $tenant1->makeCurrent();
    $espacio1 = Espacio::create([...]);
    
    $tenant2->makeCurrent();
    $espacios = Espacio::all();
    
    $this->assertCount(0, $espacios);
}
```

### Performance Testing

Monitor database connection switching:
```bash
tail -f storage/logs/laravel.log
```

Check for:
- Database switching events
- Query performance per tenant
- Connection pool usage

## Cleanup

To remove test tenants:

```sql
DROP DATABASE tenant_sedemaipu;
DROP DATABASE tenant_sedesantiago;
DROP DATABASE tenant_sedetalcahuano;
DELETE FROM tenants WHERE domain IN ('maipu', 'santiago', 'talcahuano');
```

## Next Steps

After verifying multi-tenancy works:

1. Set up production domains (e.g., maipu.aulasync.com)
2. Configure DNS for subdomains
3. Set up SSL certificates for each subdomain
4. Implement tenant-specific backups
5. Add monitoring for each tenant database
6. Configure tenant-specific email templates (optional)

## Support

For issues or questions about multi-tenancy:
- Check MULTI_TENANCY.md for detailed architecture
- Review Spatie Laravel Multitenancy documentation
- Examine tenant logs in database
