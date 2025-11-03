# Ban System for AulaSync

## Overview
This feature implements a comprehensive ban/unban system that allows administrators to temporarily prevent students (solicitantes) from making room reservations.

## Features

### 1. Ban Management Interface
- **Location**: `/bans` (Administrators only)
- **Capabilities**:
  - View all bans (active, scheduled, and expired)
  - Create new bans with reason and duration
  - Edit existing bans
  - Unban users with a single click
  - Delete ban records

### 2. Ban Validation
Banned users are prevented from making reservations through:
- **Digital Plane (Plano Digital)**: QR code scanning system
- **Manual Reservations**: Admin panel reservation creation
- **API Endpoints**: Direct API calls for reservation creation

### 3. User Experience
When a banned user attempts to make a reservation, they see:
- A distinctive **black background modal** with red accents
- The **reason** for the ban
- The **duration** remaining (days or end date/time)
- The **exact end date and time** of the ban

## Database Schema

### `bans` Table
```sql
- id (bigint, primary key)
- run_solicitante (string, foreign key to solicitantes)
- razon (text) - Reason for the ban
- fecha_inicio (datetime) - Start date/time of ban
- fecha_fin (datetime) - End date/time of ban
- activo (boolean) - Active status
- created_at (timestamp)
- updated_at (timestamp)
```

## Installation

### 1. Run Migration
```bash
php artisan migrate
```

### 2. Clear Cache (if needed)
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

## Usage

### Creating a Ban
1. Navigate to `/bans`
2. Click "Nuevo Baneo" (New Ban)
3. Select the solicitante
4. Enter the reason (minimum 10 characters)
5. Set start and end dates/times
6. Click "Crear Baneo"

### Editing a Ban
1. Navigate to `/bans`
2. Click "Editar" on the desired ban
3. Modify the reason, dates, or active status
4. Click "Actualizar Baneo"

### Unbanning a User
1. Navigate to `/bans`
2. Click "Desbanear" on the active ban
3. Confirm the action

### Checking if a User is Banned
```php
use App\Models\Ban;

// Check if a user is currently banned
if (Ban::estaBaneado($run_solicitante)) {
    // User is banned
}

// Get the active ban details
$ban = Ban::obtenerBanVigente($run_solicitante);
if ($ban) {
    echo "Reason: " . $ban->razon;
    echo "Days remaining: " . $ban->diasRestantes();
}
```

## API Response Format

When a banned user attempts to create a reservation via API:

```json
{
    "success": false,
    "tipo": "baneado",
    "mensaje": "No puedes realizar reservas",
    "ban": {
        "razon": "Reason for the ban",
        "duracion": "5 día(s) restante(s)",
        "fecha_fin": "10/11/2025 19:00"
    }
}
```

## Testing

Run the ban feature tests:
```bash
php artisan test --filter BanTest
```

The test suite includes:
- Ban creation
- Ban detection for active users
- Expired ban handling
- Remaining days calculation

## Model Methods

### Ban Model
- `estaVigente()` - Check if ban is currently active
- `diasRestantes()` - Get remaining days of ban
- `estaBaneado($run)` - Static: Check if user is banned
- `obtenerBanVigente($run)` - Static: Get active ban for user

### Scopes
- `activos()` - Get all active bans
- `vigentes()` - Get bans that are active AND within date range

## Security

- Only **Administrators** can access the ban management interface
- Ban validation occurs at multiple layers:
  - Controller level
  - API level
  - Frontend level
- All API endpoints return proper HTTP status codes (403 for banned users)

## Files Modified/Created

### Created:
- `app/Models/Ban.php` - Ban model
- `app/Http/Controllers/BanController.php` - Ban management controller
- `database/migrations/2025_11_03_170005_create_bans_table.php` - Database migration
- `database/factories/SolicitanteFactory.php` - Testing factory
- `resources/views/layouts/bans/index.blade.php` - Ban list view
- `resources/views/layouts/bans/create.blade.php` - Create ban view
- `resources/views/layouts/bans/edit.blade.php` - Edit ban view
- `tests/Feature/BanTest.php` - Feature tests

### Modified:
- `routes/web.php` - Added ban routes
- `app/Http/Controllers/PlanoDigitalController.php` - Added ban validation
- `app/Http/Controllers/ReservasController.php` - Added ban validation
- `app/Http/Controllers/SolicitanteController.php` - Added ban validation
- `resources/views/layouts/plano_digital/show.blade.php` - Added ban modal UI

## Future Enhancements

Possible improvements:
1. Email notifications when a user is banned/unbanned
2. Ban history/audit log
3. Bulk ban operations
4. Ban templates for common reasons
5. Automatic ban expiration notifications
6. Integration with user profiles to show ban history

## Support

For issues or questions, please contact the development team or create an issue in the repository.
