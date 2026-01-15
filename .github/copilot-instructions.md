# AulaSync AI Coding Instructions

## Project Overview
**AulaSync** is a Laravel 10 + Livewire 3 + Vite-powered multi-tenant classroom management system. It manages academic spaces, reservations, scheduling, and attendance tracking for educational institutions.

**Key Stack:**
- Backend: Laravel 10.48, PHP 8.1+, Spatie Multitenancy
- Frontend: Livewire 3, Alpine.js, Tailwind CSS
- Persistence: MySQL with tenant-specific databases
- APIs: RESTful endpoints for mobile/external apps
- Build: Vite + Node.js with pnpm

---

## Critical Architectural Concepts

### 1. Multi-Tenancy Pattern (Session-Based)
- **Identification**: Via session (not subdomain/domain)
- **Isolation**: Separate databases per tenant (`aulasync_{domain}`)
- **Tenant Model**: `App\Models\Tenant` (linked to `Sede`)
- **Tenant-Aware Models**: Use `BelongsToTenant` trait → auto-scoped by `tenant_id`
- **Helper Functions**: Always use `tenant()` and `tenant_id()` from [TenantHelper.php](app/Helpers/TenantHelper.php)

**Pattern Example:**
```php
// In tenant-scoped models, queries auto-filter by tenant
$espacios = Espacio::where('facultad_id', $id)->get(); // Already tenant-scoped via trait
```

### 2. Data Model Architecture
- **Central Models** (shared): `User`, `Tenant`, `Sede`, `Region`, `Universidad`
- **Tenant-Scoped Models**: `Espacio`, `Reserva`, `Horario`, `Profesor`, `Asignatura`, etc.
- **Weak Keys**: Many models use custom IDs (e.g., `id_espacio`, `id_reserva`, `id_sede`) instead of standard `id`
- **Many-to-Many Relations**: Handled through pivot tables (e.g., `profesor_asignatura`)

**Key Entity Relationships:**
- `Tenant` → `Sede` (1:1) → `Espacio` (1:N) → `Reserva` (1:N) → `Asistencia` (1:N)
- `Profesor` → `Planificacion_Asignatura` → `Asignatura` + `Espacio` + `Horario`

### 3. Attendance/Reservation Flow
- **Reservation**: Book a space (`Espacio`) for a time slot with a class (`Planificacion_Asignatura`)
- **Attendance Registration**: Submit student presence data via API (`POST /api/attendance`)
- **Grace Period**: Late arrivals (configured per tenant) tracked separately
- **Key Return**: Track when instructors return classroom keys post-class

---

## Developer Workflows

### Run the Application
```bash
# Development
pnpm install          # Install Node dependencies (use pnpm, not npm)
php artisan sail:install
./vendor/bin/sail up  # Requires Docker

# Build frontend assets
pnpm run dev          # Watch mode
pnpm run build        # Production build

# Run tests
php artisan test      # PHPUnit (Feature + Unit)
```

### Database Management
```bash
# Migrations (multi-tenancy aware)
php artisan migrate:fresh       # Runs on central DB + all tenant DBs
php artisan migrate:reset       # Revert all migrations
php artisan tinker             # Interactive shell for testing queries

# Seeding
php artisan db:seed            # Seed central + tenant databases
```

### Custom Artisan Commands
```bash
# Tenant-specific operations
php artisan tenants:list       # List all tenants
php artisan tenants:seed       # Seed a tenant database
```

---

## Project-Specific Patterns

### Livewire Components (Blade Integration)
- Located in [app/Livewire](app/Livewire) with corresponding views in `resources/views/livewire`
- Use `WithPagination` trait for tables: `->paginate(10)` patterns
- Naming: `ClassName.php` → `class-name.blade.php` view
- Example: [ReservationsTable.php](app/Livewire/ReservationsTable.php) uses real-time search + pagination

### API Controllers (RestFul Design)
- All API endpoints in [app/Http/Controllers/Api/](app/Http/Controllers/Api/)
- Key controllers:
  - `AttendanceController` - Register student attendance (`POST /api/attendance`)
  - `ProgramacionSemanalController` - Weekly scheduling data
  - `StudentQRAttendanceController` - QR scanning for entry/exit
- **Response Format**: JSON with status codes; check docs/ for examples

### Services Layer (Business Logic)
- Located in [app/Services/](app/Services/)
- Examples: `QRService`, `CorreoMasivoService`, `LicenciaRecuperacionService`
- Used by controllers to handle complex operations
- **Never place business logic directly in controllers**

### Traits (Reusable Behavior)
- `BelongsToTenant` - Auto-scope queries to current tenant
- `SafeCacheTrait` - Wrapped caching with fallback handling
- `RedirectByRole` - Role-based redirect logic after authentication

---

## Key Integration Points & Data Flows

### Reservation → Attendance Pipeline
1. **Create Reservation**: `Reserva` record with `Espacio`, `Profesor`, `Horario`, `Asignatura`
2. **Register Attendance**: POST to `/api/attendance` with student RUT, timestamp, status
3. **Track Grace Period**: Store late arrivals separately if within configured threshold
4. **Finalize Class**: Optional endpoint to mark class as complete, trigger key return notification

### Email System
- **Service**: `CorreoMasivoService` handles bulk sending
- **Templates**: `PlantillaCorreo` model stores HTML/text templates with variable placeholders
- **Variables**: Resolved via `DestinatarioCorreo` + `PlantillaCorreo` relationship
- **Gmail Config**: Set in [config/gmail.php](config/gmail.php) and environment variables

### QR Code Generation
- **Service**: `QRService` generates PNG codes for spaces
- **Storage**: Saved to `storage/app/public/qrcodes/`
- **Usage**: Scanned by apps to identify spaces and trigger attendance workflows

---

## Common Development Tasks

### Adding a New Endpoint
1. Create controller in `app/Http/Controllers/Api/`
2. Define route in [routes/api.php](routes/api.php)
3. Use `Request` validation classes in `app/Http/Requests/`
4. Return JSON response (examples in `docs/` folder)
5. Document in `docs/` with cURL/Postman examples

### Adding a Tenant-Scoped Feature
1. Create model in `app/Models/`
2. Add `use BelongsToTenant` trait
3. Ensure migration includes `tenant_id` foreign key
4. Queries automatically scoped—no manual filtering needed

### Testing API Endpoints
- Use examples in `docs/PRUEBAS_API_ESPACIOS.md` and `docs/TESTS_API_PROGRAMACION_ASISTENCIA.md`
- Create feature tests in `tests/Feature/`
- PHPUnit config in [phpunit.xml](phpunit.xml) uses separate test environment

---

## Documentation Essentials

### Start Here
- **README Index**: [docs/README.md](docs/README.md) - Central navigation for all docs
- **Attendance API**: [docs/GUIA_RAPIDA_ASISTENCIA.md](docs/GUIA_RAPIDA_ASISTENCIA.md) - 5-minute start
- **Spaces API**: [docs/API_ESPACIOS_Y_TIPOS.md](docs/API_ESPACIOS_Y_TIPOS.md) - Full API reference

### By Topic
- **Spaces/Rooms**: `docs/API_ESPACIOS_Y_TIPOS.md`, `docs/LOGICA_OCUPACION_ESPACIOS.md`
- **Attendance**: `docs/API_REGISTRO_ASISTENCIA.md`, `docs/RESUMEN_CAMBIOS_ASISTENCIA.md`
- **Email**: `docs/CORREOS_MASIVOS_GUIA.md`, `docs/PLANTILLAS_CORREOS_GUIA.md`
- **Licenses/Recovery**: `docs/SISTEMA_LICENCIAS_RECUPERACION.md`

---

## Debugging Tips

### Multi-Tenancy Issues
- Verify `app('tenant')` returns non-null in request context
- Check `tenant_id` foreign key constraints in migrations
- Review `config/multitenancy.php` for identification method

### Database Queries
- Use `php artisan tinker` with `auth()->loginAs(User::first())` to test scoped queries
- Enable `DB::enableQueryLog()` to debug N+1 problems in Livewire components

### Frontend Asset Compilation
- Run `pnpm run build` for production assets
- Vite manifest issues? Check `public/build/manifest.json` existence
- Clear cache: `php artisan cache:clear && php artisan config:clear`

---

## Environment & Configuration

**Essential `.env` Variables:**
```
DB_CONNECTION=mysql
DB_HOST=mysql
DB_DATABASE=aulasync (central)
MULTITENANCY_ENABLED=true
MULTITENANCY_SEPARATE_DATABASES=true
MULTITENANCY_IDENTIFICATION=session
```

**Local Development:**
- Use Laravel Sail for Docker consistency
- Vite runs on `http://localhost:5173` in dev mode
- Sanctum token auth for API requests

---

## Conventions & Standards

- **Naming**: Spanish model/table names (e.g., `Espacio`, `Reserva`, `Profesor`)
- **Localization**: Interface in Spanish; use Laravel translation files in `lang/es/`
- **Date Handling**: Use Carbon throughout; timestamps in UTC
- **Validation**: Form requests in `app/Http/Requests/` (not inline in controllers)
- **Error Handling**: Log all exceptions; API returns consistent error JSON

---

## When Stuck
1. **Architecture question?** → Check `docs/MULTITENANCY_QUICK_REFERENCE.md`
2. **API usage?** → See `docs/` folder with cURL/JSON examples
3. **Livewire component?** → Review `app/Livewire/*.php` for similar patterns
4. **Database/tenant?** → Use `php artisan tinker` to inspect data structure
5. **Build issue?** → Verify `pnpm install` + Docker containers running + `pnpm run build`
