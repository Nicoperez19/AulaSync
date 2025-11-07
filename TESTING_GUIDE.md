# Testing Guide - New Maintainers

This guide will help you test the new maintainers implementation.

## Prerequisites

1. **Database**: Ensure you have a MySQL database running and configured in `.env`
2. **Laravel Setup**: Application should be properly configured
3. **User Account**: You need an Administrador account to test

## Setup Steps

### 1. Run Migrations

```bash
php artisan migrate
```

This will create the following tables:
- `configuracion`
- `jefes_carrera`
- `asistentes_academicos`
- Updates `sedes` with `prefijo_sala` column

### 2. Seed Permissions

```bash
php artisan db:seed --class=NewMaintainersPermissionsSeeder
```

This creates and assigns the following permissions to Administrador role:
- `mantenedor de configuracion`
- `mantenedor de escuelas`
- `mantenedor de jefes de carrera`
- `mantenedor de asistentes academicos`

### 3. Seed Initial Configuration (Optional)

```bash
php artisan db:seed --class=ConfiguracionSeeder
```

This creates initial configuration entries including the logo placeholder.

## Testing Each Maintainer

### 1. Test Configuración del Sistema (Logo)

**URL**: `/configuracion`

**Test Create:**
1. Click "Agregar Configuración"
2. Fill in:
   - Clave: `test_config`
   - Valor: `test value`
   - Descripción: `Test configuration`
3. Click "Crear Configuración"
4. Verify success message and entry in table

**Test Upload Logo:**
1. Click edit on `logo_institucional` entry (if it exists)
2. Upload an image file (PNG, JPG)
3. Verify image preview appears
4. Save and verify logo is stored

**Test Search:**
1. Type in search box
2. Verify table filters correctly

**Test Delete:**
1. Click delete button on a test entry
2. Confirm deletion in SweetAlert dialog
3. Verify entry is removed

### 2. Test Sedes (Room Prefix)

**URL**: `/sedes`

**Test Prefix Field:**
1. Create new sede or edit existing
2. Find "Prefijo Código Sala" field
3. Enter prefix (e.g., "TH", "CT")
4. Save and verify field is stored
5. Check table shows prefix if displayed

### 3. Test Escuelas

**URL**: `/escuelas`

**Test Create:**
1. Click "Agregar Escuela"
2. Fill in:
   - ID Escuela: `ESC001`
   - Nombre Escuela: `Escuela de Prueba`
   - Select a Facultad
3. Save and verify entry appears
4. Verify career count shows correctly

**Test CRUD:**
- Create new school
- Edit school name
- Verify search works
- Delete test school

### 4. Test Jefes de Carrera

**URL**: `/jefes-carrera`

**Test Create:**
1. Click "Agregar Jefe de Carrera"
2. Fill in:
   - Nombre: `Juan Pérez`
   - Email: `jperez@test.cl`
   - Teléfono: `+56912345678`
   - Select a Carrera
3. Save and verify

**Test Validations:**
1. Try to create with duplicate email
2. Verify error message appears
3. Try to create without required fields
4. Verify validation messages

**Test Search:**
1. Search by name, email, or career
2. Verify filtering works

### 5. Test Asistentes Académicos

**URL**: `/asistentes-academicos`

**Test Create:**
1. Click "Agregar Asistente Académico"
2. Fill in:
   - Nombre: `María González`
   - Email: `mgonzalez@test.cl`
   - Teléfono: `+56987654321`
   - Select an Escuela
3. Save and verify

**Test One Per School:**
1. Try to create second assistant for same school
2. Verify validation or logic (depending on implementation)

## Common Issues and Solutions

### Issue: "Permission denied"
**Solution**: Ensure your user has Administrador role or specific permission

### Issue: "Table doesn't exist"
**Solution**: Run migrations: `php artisan migrate`

### Issue: "Permission not found"
**Solution**: Run permission seeder: `php artisan db:seed --class=NewMaintainersPermissionsSeeder`

### Issue: "Logo upload fails"
**Solution**: 
1. Check storage directory is writable
2. Run: `php artisan storage:link`
3. Verify `storage/app/public/images/logo` directory exists

### Issue: "Views not found"
**Solution**: Clear view cache: `php artisan view:clear`

### Issue: "Route not found"
**Solution**: 
1. Clear route cache: `php artisan route:clear`
2. Verify routes in `routes/web.php`

## Verification Checklist

After testing, verify:

- [ ] All 5 maintainers accessible
- [ ] Create operation works for all
- [ ] Edit operation works for all
- [ ] Delete operation works for all (with confirmation)
- [ ] Search functionality works
- [ ] Pagination works (if more than 10 records)
- [ ] Validations prevent invalid data
- [ ] Email uniqueness enforced
- [ ] SweetAlert dialogs appear
- [ ] Success/error messages display
- [ ] Logo upload works
- [ ] Sede prefix field works
- [ ] Relationships work (career-jefe, school-assistant)
- [ ] Non-admin users cannot access (if permission not assigned)

## Test Data Cleanup

After testing, clean up test data:

```sql
-- Clean test configurations
DELETE FROM configuracion WHERE clave LIKE 'test_%';

-- Clean test jefes
DELETE FROM jefes_carrera WHERE email LIKE '%@test.cl';

-- Clean test asistentes
DELETE FROM asistentes_academicos WHERE email LIKE '%@test.cl';

-- Clean test escuelas (be careful with this)
DELETE FROM area_academicas WHERE id_area_academica LIKE 'ESC%' AND tipo_area_academica = 'escuela';
```

## Performance Testing

If testing with large datasets:

1. Create 100+ records
2. Test search performance
3. Test pagination
4. Verify no N+1 query issues (check Laravel Debugbar if available)

## Security Testing

1. **Test without permissions**:
   - Create test user without permissions
   - Try to access each maintainer URL
   - Verify "Access Denied" or redirect to dashboard

2. **Test CSRF protection**:
   - Forms should fail without CSRF token
   - Verify `@csrf` directive in forms

3. **Test input validation**:
   - Try SQL injection in text fields
   - Try XSS in text fields
   - Verify proper escaping in views

## Screenshots for Documentation

Take screenshots of:
1. Each maintainer index page
2. Create modal/form for each
3. Edit page for each
4. Delete confirmation dialog
5. Search functionality
6. Logo upload form
7. Sede with prefix field

## Reporting Issues

If you find issues, report:
1. **What**: What operation failed
2. **Where**: URL and page
3. **How**: Steps to reproduce
4. **Expected**: What should happen
5. **Actual**: What actually happened
6. **Error**: Any error messages or logs

## Success Indicators

The implementation is successful if:
✅ All 5 maintainers work completely
✅ No PHP errors or exceptions
✅ All validations work
✅ Permissions control access
✅ UI is consistent and responsive
✅ Data persists correctly
✅ Relationships work properly
