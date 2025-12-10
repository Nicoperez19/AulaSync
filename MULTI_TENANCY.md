# Multi-Tenancy Documentation

## Overview

AulaSync now supports multi-tenancy based on subdomains using Spatie Laravel Multitenancy. Each sede (campus) operates as an independent tenant with its own database and isolated data.

## Architecture

### Landlord Database
The main database (`aulasync` by default) contains:
- Core organizational data: regiones, provincias, comunas, universidades, sedes
- Tenant registry (tenants table)
- Users and authentication data (shared across tenants)

### Tenant Databases
Each tenant (sede) has its own database containing:
- Facultades (faculties)
- Campus locations
- Áreas académicas (academic areas)
- Carreras (programs)
- Pisos (floors)
- Espacios (spaces) - with sede-specific prefixes
- Mapas (maps) - digital floor plans
- Profesores (professors) - sede-specific
- Asignaturas (subjects)
- Horarios (schedules)
- Planificación de asignaturas (semester planning)
- Reservas (reservations)
- Módulos (modules)
- Data loads

## Setup

### 1. Initial Migration

Run landlord migrations first:
```bash
php artisan migrate --path=database/migrations/landlord
```

This creates the `tenants` table in your main database.

### 2. Creating a Tenant

For each sede, create a tenant using the artisan command:

```bash
php artisan tenant:create {sede_id}
```

This command will:
1. Prompt for a subdomain (e.g., "maipu" for maipu.aulasync.com)
2. Create a new database (e.g., `tenant_maipu`)
3. Run all tenant migrations
4. Link the tenant to the sede

Example:
```bash
php artisan tenant:create sede_maipu
# Enter subdomain: maipu
```

### 3. Accessing Tenants

Tenants are identified by subdomain:
- `maipu.aulasync.com` → accesses the Maipú sede tenant
- `santiago.aulasync.com` → accesses the Santiago sede tenant
- `www.aulasync.com` or `aulasync.com` → landlord/admin interface

## Configuration

### Environment Variables

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=aulasync      # Landlord database
DB_USERNAME=root
DB_PASSWORD=
```

### Multitenancy Config

The configuration is in `config/multitenancy.php`:
- **Tenant Finder**: Uses `DomainTenantFinder` to identify tenants by subdomain
- **Switch Tasks**: Database switching and cache prefixing enabled
- **Tenant Model**: Custom `App\Models\Tenant` with sede relationship

## Development

### Local Development

For local development, you can use different approaches:

1. **Subdomain routing** (recommended for production-like testing):
   - Edit `/etc/hosts`:
     ```
     127.0.0.1  maipu.localhost
     127.0.0.1  santiago.localhost
     ```
   - Access via `http://maipu.localhost:8000`

2. **Testing with domain matching**:
   The `DomainTenantFinder` will match the full host for local development.

### Running Tenant-Specific Commands

Execute artisan commands for a specific tenant:
```bash
php artisan tenants:artisan "migrate" --tenant=1
php artisan tenants:artisan "db:seed" --tenant=1
```

## Data Import

### Bulk Uploads

When performing bulk uploads (carga masiva) for semester planning:
1. The system automatically detects the current tenant from the subdomain
2. Data is imported into the tenant-specific database
3. Space prefixes are validated against the tenant's sede configuration
4. Professors are scoped to the current tenant

Example workflow:
1. Access `maipu.aulasync.com/data-load`
2. Upload Excel file with planning data
3. System validates space prefixes match Maipú's convention
4. Data is stored in `tenant_maipu` database

## Models and Tenant Awareness

### Tenant-Scoped Models

The following models use the `tenant` connection:
- Espacio
- Mapa
- Piso
- Planificacion_Asignatura
- Profesor
- Asignatura
- Horario
- Facultad
- Campus
- Carrera
- AreaAcademica
- Bloque
- Modulo
- Reserva
- Solicitante
- DataLoad
- User

### Landlord Models

These models remain in the landlord database:
- Sede
- Universidad
- Region
- Provincia
- Comuna
- Tenant

## File Storage

Files (QR codes, maps, uploads) are automatically stored in tenant-specific directories:
```
storage/app/public/tenant-1/
storage/app/public/tenant-2/
```

## Security Considerations

1. **Data Isolation**: Each tenant's data is completely isolated in separate databases
2. **Connection Switching**: The middleware automatically switches database connections based on subdomain
3. **No Cross-Tenant Access**: Users cannot access data from other tenants
4. **Prefix Validation**: Space prefixes are validated to match the tenant's sede

## Troubleshooting

### Tenant Not Found
- Verify the subdomain matches a tenant's `domain` field
- Check that the tenant exists: `SELECT * FROM tenants;`
- Ensure DNS/hosts file is configured correctly

### Database Connection Errors
- Verify tenant database exists: `SHOW DATABASES LIKE 'tenant_%';`
- Check database credentials in `.env`
- Ensure MySQL user has permissions to create databases

### Migration Issues
- Run landlord migrations first
- Then create tenants which auto-run tenant migrations
- To re-run tenant migrations: `php artisan tenants:artisan "migrate:fresh" --tenant=1`

## Testing

When testing multi-tenancy:
1. Create test tenants in your test database
2. Use subdomain routing in tests
3. Clean up tenant databases after tests

## Future Enhancements

Potential improvements:
- Tenant-specific branding/themes
- Cross-tenant reporting (for administrators)
- Tenant usage analytics
- Automated backups per tenant
- Tenant-specific email templates
