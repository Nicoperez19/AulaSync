# Notification System Implementation Summary

## Overview

This implementation adds a comprehensive notification system to detect when professors miss their scheduled classes and notify supervisors and administrators. The system works independently of the UI and includes automated detection, notifications, and visual indicators.

## Problem Statement

**Original Issue:** When a professor doesn't attend their class, the system should:
1. Detect the absence automatically (not dependent on the módulos-actuales view being open)
2. Notify supervisors and administrators
3. Allow them to reschedule the class
4. Display rescheduled classes in the módulos actuales view

## Solution Architecture

### 1. Database Layer

#### New Table: `notificaciones`
- Stores notifications for users
- Fields: id, run_usuario, tipo, titulo, mensaje, url, leida, fecha_lectura, datos_adicionales
- Indexed for performance on run_usuario, leida, tipo, created_at

#### Existing Tables Used
- `clases_no_realizadas` - Stores detected missed classes
- `recuperacion_clases` - Stores rescheduling information
- `users` - User management with roles

### 2. Backend Components

#### A. Notificacion Model (`app/Models/Notificacion.php`)
**Purpose:** Manage notification data and relationships

**Key Methods:**
- `crearNotificacionClaseNoRealizada($claseNoRealizada)` - Creates notifications when a class is missed
- `crearNotificacionClaseReagendada($recuperacion)` - Creates notifications when a class is rescheduled
- `contadorNoLeidas($runUsuario)` - Returns count of unread notifications
- `marcarTodasComoLeidas($runUsuario)` - Marks all notifications as read

**Scopes:**
- `noLeidas()` - Filter unread notifications
- `leidas()` - Filter read notifications
- `tipo($tipo)` - Filter by notification type
- `recientes($dias)` - Filter recent notifications

#### B. DetectarClasesNoRealizadas Command (`app/Console/Commands/DetectarClasesNoRealizadas.php`)
**Purpose:** Automatically detect missed classes without UI dependency

**Execution:** Scheduled to run hourly at :05 past each hour

**Detection Logic:**
1. Gets current day and time
2. Finds all scheduled classes for today
3. For each class:
   - Checks if all scheduled modules have ended
   - Verifies if professor registered attendance
   - If no attendance registered, marks as missed
   - Creates notification for supervisors/administrators

**Features:**
- Skips weekends automatically
- Respects holidays (via DiaFeriado model)
- Prevents duplicate entries
- Logs all detections

#### C. User Model Update (`app/Models/User.php`)
**Added Relationship:**
```php
public function notificaciones()
{
    return $this->hasMany(Notificacion::class, 'run_usuario', 'run');
}
```

### 3. Frontend Components

#### A. NotificacionesDropdown Livewire Component

**File:** `app/Livewire/NotificacionesDropdown.php`

**Features:**
- Shows notification bell icon with unread count badge
- Displays dropdown with recent notifications
- Auto-refreshes every 2 minutes
- Click notification to navigate and mark as read
- "Mark all as read" functionality

**View:** `resources/views/livewire/notificaciones-dropdown.blade.php`

**UI Elements:**
- Bell icon with animated badge
- Dropdown with notification list
- Icon indicators by notification type
- Timestamp (relative time)
- Empty state message

#### B. Navbar Integration

**File:** `resources/views/components/navbar.blade.php`

**Changes:**
- Added `@livewire('notificaciones-dropdown')` for Supervisor/Administrador roles
- Positioned between "Acciones Rápidas" and user dropdown

#### C. ModulosActualesTable Updates

**File:** `app/Livewire/ModulosActualesTable.php`

**New Logic:**
- Checks for rescheduled classes (`RecuperacionClase`) for current module
- Adds `es_recuperacion` flag to space data
- Includes original date in class data

**View:** `resources/views/livewire/modulos-actuales-table.blade.php`

**Visual Changes:**
- Blue icon with "Recuperación de clase" text
- Displays original class date
- Maintains all existing class information

#### D. RecuperacionClasesTable Update

**File:** `app/Livewire/RecuperacionClasesTable.php`

**Changes:**
- Added notification creation when class is rescheduled
- Calls `Notificacion::crearNotificacionClaseReagendada($recuperacion)` after successful rescheduling

### 4. Task Scheduling

**File:** `app/Console/Kernel.php`

**Scheduled Task:**
```php
$schedule->command('clases:detectar-no-realizadas')
    ->hourly()
    ->at('05')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/clases-no-realizadas.log'));
```

**Configuration:**
- Runs every hour at :05 minutes
- Prevents overlapping executions
- Runs in background
- Logs to dedicated file

## Data Flow

### Scenario 1: Missed Class Detection

```
1. Scheduled Command Runs (Hourly at :05)
   ↓
2. DetectarClasesNoRealizadas checks all classes
   ↓
3. Identifies classes with no attendance after end time
   ↓
4. Creates ClaseNoRealizada record
   ↓
5. Notificacion::crearNotificacionClaseNoRealizada()
   ↓
6. Creates notification for each Supervisor/Administrador
   ↓
7. Notification appears in navbar bell icon
   ↓
8. User clicks notification → Redirects to /recuperacion-clases
```

### Scenario 2: Class Rescheduling

```
1. Supervisor/Administrador navigates to /recuperacion-clases
   ↓
2. Selects pending class and clicks "Reagendar"
   ↓
3. Fills rescheduling form (date, module, classroom)
   ↓
4. Submits form → RecuperacionClasesTable::reagendar()
   ↓
5. Updates RecuperacionClase record with new data
   ↓
6. Notificacion::crearNotificacionClaseReagendada()
   ↓
7. Creates notification for all Supervisor/Administrador
   ↓
8. Notification appears showing successful rescheduling
```

### Scenario 3: Viewing Rescheduled Class

```
1. RecuperacionClase exists with:
   - fecha_reagendada = Today
   - id_modulo_reagendado = Current module
   - estado = 'reagendada'
   ↓
2. ModulosActualesTable::actualizarDatos() runs
   ↓
3. Checks for rescheduled classes in current module
   ↓
4. Sets es_recuperacion flag
   ↓
5. View displays blue "Recuperación de clase" badge
   ↓
6. Shows original date and class details
```

## Security Features

1. **Role-Based Access Control**
   - Only Supervisor and Administrador roles see notifications
   - Enforced at component level: `@hasanyrole('Supervisor|Administrador')`

2. **User Isolation**
   - Notifications filtered by `run_usuario`
   - No cross-user data exposure

3. **SQL Injection Prevention**
   - Eloquent ORM used throughout
   - Parameterized queries

4. **XSS Protection**
   - Blade templating auto-escapes output
   - No raw HTML in notifications

5. **CSRF Protection**
   - Laravel's built-in CSRF protection active
   - Livewire handles CSRF tokens automatically

## Performance Optimizations

1. **Database Indexes**
   - `run_usuario`, `leida` (composite)
   - `tipo`
   - `created_at`

2. **Query Optimization**
   - Limited to 10 most recent notifications
   - Eager loading of relationships
   - Grouped queries in ModulosActualesTable

3. **Scheduled Command**
   - `withoutOverlapping()` prevents concurrent runs
   - `runInBackground()` for non-blocking execution
   - Runs at :05 to avoid peak :00 minute load

4. **Frontend**
   - Auto-refresh every 2 minutes (not every second)
   - Dropdown only loads on demand

## Files Modified

### New Files
1. `database/migrations/2025_11_18_000001_create_notificaciones_table.php`
2. `app/Models/Notificacion.php`
3. `app/Console/Commands/DetectarClasesNoRealizadas.php`
4. `app/Livewire/NotificacionesDropdown.php`
5. `resources/views/livewire/notificaciones-dropdown.blade.php`
6. `NOTIFICATION_SYSTEM_TESTING.md`

### Modified Files
1. `app/Console/Kernel.php` - Added scheduled task
2. `app/Models/User.php` - Added notificaciones relationship
3. `app/Livewire/ModulosActualesTable.php` - Added rescheduled class detection
4. `app/Livewire/RecuperacionClasesTable.php` - Added notification on rescheduling
5. `resources/views/components/navbar.blade.php` - Added notification dropdown
6. `resources/views/livewire/modulos-actuales-table.blade.php` - Added recovery class display

## Configuration Requirements

### Environment Variables
No new environment variables required. Uses existing database connection.

### Permissions
Requires existing permissions:
- `gestionar recuperacion clases` - For accessing rescheduling interface
- Supervisor or Administrador role - For viewing notifications

### Cron Job (Production)
```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

## Testing Checklist

- [x] Migration runs successfully
- [x] Command detects missed classes correctly
- [x] Notifications created for correct users only
- [x] Notification bell appears for Supervisor/Administrador
- [x] Clicking notification marks as read and redirects
- [x] Rescheduled classes display in módulos actuales
- [x] Visual indicator shows correctly
- [x] Code passes Pint formatting
- [x] No security vulnerabilities detected

## Future Enhancements

Possible improvements for future iterations:

1. **Push Notifications**
   - Browser push notifications
   - Email notifications
   - SMS for critical cases

2. **Analytics Dashboard**
   - Trend analysis of missed classes
   - Professor attendance statistics
   - Most affected courses/departments

3. **Notification Preferences**
   - User-configurable notification types
   - Frequency settings
   - Delivery method preferences

4. **Mobile App Integration**
   - Mobile-friendly notification interface
   - Push notifications to mobile devices

5. **Advanced Filtering**
   - Filter by course, professor, date range
   - Search within notifications
   - Archive old notifications

## Maintenance Notes

### Regular Tasks
1. **Log Rotation:** Monitor `storage/logs/clases-no-realizadas.log` size
2. **Database Cleanup:** Periodically archive old notifications
3. **Performance Monitoring:** Check query performance on notificaciones table

### Troubleshooting
See `NOTIFICATION_SYSTEM_TESTING.md` for detailed troubleshooting guide.

## Documentation

- User Guide: See navbar bell icon tooltip
- Testing Guide: `NOTIFICATION_SYSTEM_TESTING.md`
- API Documentation: PHPDoc comments in all classes
- Database Schema: See migration file

## Credits

Implemented by: GitHub Copilot
Requirements from: Issue - "Cuando un profesor no asiste a su clase se notifica a los usuarios supervisor y administrador para reagendar"
