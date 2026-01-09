# Testing Guide - Notification System for Missed Classes

This guide will help you test the notification system implementation for detecting missed classes and notifying supervisors/administrators.

## Prerequisites

1. **Database**: Ensure you have a MySQL database running and configured in `.env`
2. **Laravel Setup**: Application should be properly configured
3. **User Accounts**: You need accounts with Supervisor or Administrador roles to test notifications
4. **Scheduler**: Laravel task scheduler should be configured (for production use)

## Setup Steps

### 1. Run Migrations

```bash
php artisan migrate
```

This will create the `notificaciones` table with the following structure:
- `id`: Primary key
- `run_usuario`: User receiving the notification
- `tipo`: Type of notification (clase_no_realizada, clase_reagendada, etc.)
- `titulo`: Notification title
- `mensaje`: Notification message
- `url`: URL to redirect when clicked
- `leida`: Boolean indicating if notification was read
- `fecha_lectura`: Timestamp when notification was read
- `datos_adicionales`: JSON field for additional data

### 2. Verify Role Configuration

Ensure you have users with the following roles:
- **Supervisor**: Can view and manage notifications
- **Administrador**: Can view and manage notifications

```bash
# Check roles
php artisan tinker
>>> User::role(['Supervisor', 'Administrador'])->get();
```

### 3. Configure Laravel Scheduler (Production)

For production environments, add this to your crontab:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

The scheduled command `clases:detectar-no-realizadas` runs hourly at :05 past each hour.

## Testing the Notification System

### Test 1: Manual Command Execution

Test the detection command manually:

```bash
php artisan clases:detectar-no-realizadas
```

Expected output:
- Information about the detection process
- Number of missed classes detected
- Log entries in `storage/logs/clases-no-realizadas.log`

### Test 2: UI - Notification Bell Icon

1. **Login** as a user with Supervisor or Administrador role
2. **Check navbar** - You should see a bell icon next to "Acciones Rápidas" (if available)
3. **Click the bell** - Dropdown should show recent notifications
4. **Verify counter** - Badge should show number of unread notifications

### Test 3: Notification Creation Flow

#### Scenario A: Missed Class Detection

1. Create a class schedule in the system
2. Wait until after all scheduled modules for that class end
3. Ensure the professor doesn't register attendance
4. Run the detection command:
   ```bash
   php artisan clases:detectar-no-realizadas
   ```
5. Check notifications:
   ```bash
   php artisan tinker
   >>> Notificacion::all();
   ```
6. Login as Supervisor/Administrador and verify the notification appears

#### Scenario B: Class Rescheduling

1. Navigate to `/recuperacion-clases`
2. Select a pending recovery class
3. Click "Reagendar"
4. Fill in the rescheduling form:
   - New date
   - New module
   - New classroom (optional)
5. Submit the form
6. Verify notification is created and visible in the navbar

### Test 4: Rescheduled Classes Display

1. Create a rescheduled class entry with:
   - `fecha_reagendada`: Today's date
   - `id_modulo_reagendado`: Current module ID
   - `id_espacio_reagendado`: A classroom ID
   - `estado`: 'reagendada'

2. Navigate to `/modulos-actuales`

3. Verify that the classroom shows:
   - Blue icon with "Recuperación de clase"
   - Original date information
   - Professor and course details

### Test 5: Notification Actions

1. **Mark as Read**: Click on a notification
   - Should redirect to `/recuperacion-clases`
   - Notification should be marked as read
   - Counter should decrease

2. **Mark All as Read**: Click "Marcar todas como leídas"
   - All notifications should be marked as read
   - Counter should reset to 0

3. **View All**: Click "Ver todas las recuperaciones"
   - Should redirect to `/recuperacion-clases`

## Testing Database Queries

### Check Notifications

```bash
php artisan tinker
```

```php
// Get all notifications
Notificacion::all();

// Get unread notifications for a specific user
Notificacion::where('run_usuario', 'USER_RUN')->noLeidas()->get();

// Count unread notifications
Notificacion::contadorNoLeidas('USER_RUN');

// Get notifications by type
Notificacion::tipo('clase_no_realizada')->get();
```

### Check Rescheduled Classes

```php
// Get all rescheduled classes for today
RecuperacionClase::where('fecha_reagendada', today())
    ->where('estado', 'reagendada')
    ->get();
```

## Troubleshooting

### Issue: Notifications not appearing

**Check:**
1. User has Supervisor or Administrador role
2. Livewire component is loaded: `@livewire('notificaciones-dropdown')`
3. Browser console for JavaScript errors
4. Database has notification records

**Solution:**
```bash
# Check user roles
php artisan tinker
>>> User::find('USER_RUN')->roles;

# Check notifications in database
>>> Notificacion::where('run_usuario', 'USER_RUN')->get();
```

### Issue: Scheduled command not running

**Check:**
1. Cron is configured correctly
2. Laravel scheduler is working: `php artisan schedule:list`
3. Log files for errors: `storage/logs/clases-no-realizadas.log`

**Solution:**
```bash
# Test manually
php artisan clases:detectar-no-realizadas

# Check scheduler
php artisan schedule:run
```

### Issue: Rescheduled classes not showing

**Check:**
1. `fecha_reagendada` matches current date
2. `id_modulo_reagendado` matches current module format (e.g., 'LU.1', 'MA.2')
3. `estado` is 'reagendada'

**Solution:**
```bash
php artisan tinker
>>> RecuperacionClase::where('fecha_reagendada', today())->get();
```

## Expected Behavior

### Automatic Detection
- Command runs hourly at :05
- Detects classes where:
  - All scheduled modules have ended
  - Professor hasn't registered attendance
  - Not a holiday

### Notifications
- Created for Supervisor and Administrador roles only
- Show in real-time (refreshes every 2 minutes)
- Persist until marked as read
- Link directly to rescheduling interface

### Visual Indicators
- Bell icon with red badge showing unread count
- Blue "Recuperación de clase" badge in módulos actuales
- Original date displayed for context

## Security Considerations

- Notifications are user-specific (filtered by `run_usuario`)
- Role-based access control enforced (`@hasanyrole('Supervisor|Administrador')`)
- No sensitive data exposed in notifications
- SQL injection prevented through Eloquent ORM
- XSS protection via Blade templating

## Performance Notes

- Notifications query limited to 10 most recent
- Scheduled command runs with `withoutOverlapping()` to prevent concurrent executions
- Background execution enabled for non-blocking operation
- Indexes on `run_usuario`, `leida`, `tipo`, and `created_at` for query optimization

## Logs and Monitoring

Check these log files:
- `storage/logs/clases-no-realizadas.log` - Detection command output
- `storage/logs/laravel.log` - General application logs

Monitor:
- Number of notifications created per day
- Frequency of missed class detections
- User engagement with notifications (read rate)
