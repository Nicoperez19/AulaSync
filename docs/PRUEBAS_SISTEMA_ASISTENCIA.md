# Guía de Pruebas Rápidas - Sistema de Asistencia

## 🧪 Pruebas del Endpoint de API

### 1. Prueba con Postman / cURL

#### Test 1: Registrar Asistencia Exitosa

**Request:**
```bash
curl -X POST http://localhost:8000/api/attendance \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "student_id": "12345678",
    "room_id": "A101",
    "student_name": "Juan Pérez"
  }'
```

**Resultado Esperado:** 201 Created
```json
{
  "success": true,
  "message": "Asistencia registrada exitosamente",
  "data": { ... }
}
```

---

#### Test 2: Error - Sala sin Reserva Activa

**Request:**
```bash
curl -X POST http://localhost:8000/api/attendance \
  -H "Content-Type: application/json" \
  -d '{
    "student_id": "12345678",
    "room_id": "SALA_SIN_RESERVA"
  }'
```

**Resultado Esperado:** 404 Not Found
```json
{
  "success": false,
  "message": "No hay una reserva activa en esta sala en este momento"
}
```

---

#### Test 3: Error - Asistencia Duplicada

**Request:** (Enviar dos veces el mismo student_id para la misma reserva)
```bash
curl -X POST http://localhost:8000/api/attendance \
  -H "Content-Type: application/json" \
  -d '{
    "student_id": "12345678",
    "room_id": "A101"
  }'
```

**Resultado Esperado (2da vez):** 409 Conflict
```json
{
  "success": false,
  "message": "Este estudiante ya tiene registrada su asistencia para esta clase"
}
```

---

#### Test 4: Error - Validación

**Request:**
```bash
curl -X POST http://localhost:8000/api/attendance \
  -H "Content-Type: application/json" \
  -d '{}'
```

**Resultado Esperado:** 422 Unprocessable Entity
```json
{
  "success": false,
  "message": "Error de validación",
  "errors": {
    "student_id": ["El ID del estudiante es obligatorio"],
    "room_id": ["Debe proporcionar room_id o reservation_id"]
  }
}
```

---

#### Test 5: Obtener Asistencias de una Reserva

**Request:**
```bash
curl -X GET http://localhost:8000/api/attendance/reservation/R20251118143001 \
  -H "Accept: application/json"
```

**Resultado Esperado:** 200 OK
```json
{
  "success": true,
  "data": {
    "reservation": { ... },
    "attendances": [ ... ],
    "total_attendances": 15
  }
}
```

---

## 🎯 Pruebas de Broadcasting

### Opción 1: Desde Laravel Tinker

```bash
php artisan tinker
```

```php
// Crear una asistencia de prueba
$attendance = new App\Models\Asistencia([
    'id' => 999,
    'rut_asistente' => '12345678',
    'nombre_asistente' => 'Test Student',
    'hora_llegada' => now()->format('H:i:s')
]);

// Disparar el evento manualmente
event(new App\Events\AttendanceRegistered(
    'A101', // roomId
    'R20251118143001', // reservationId
    $attendance,
    15, // currentOccupancy
    40, // roomCapacity
    [
        'type' => 'profesor',
        'name' => 'Dr. Test',
        'id' => '87654321'
    ]
));
```

**Verificar en el navegador:**
- Abrir la consola del navegador (F12)
- Deberías ver el evento recibido si estás suscrito al canal

---

### Opción 2: Verificar Estado de Pusher/Reverb

**En la consola del navegador:**
```javascript
// Verificar estado de conexión
Echo.connector.pusher.connection.state
// Debe retornar: "connected"

// Verificar canales activos
Echo.connector.pusher.allChannels()
// Debe incluir: "private-room.A101"
```

---

## 🔍 Pruebas de Autorización de Canal

### Test 1: Usuario Administrador

```bash
php artisan tinker
```

```php
$admin = App\Models\User::where('run', 'RUN_ADMIN')->first();
$espacio = App\Models\Espacio::find('A101');

// Simular autorización
$result = broadcast()->channel('room.A101', function ($user) use ($espacio) {
    return $user->hasRole('admin') ? [
        'id' => $user->run,
        'name' => $user->name,
        'role' => 'admin'
    ] : false;
}, $admin);

dump($result); // Debe retornar array con datos del usuario
```

---

### Test 2: Profesor con Reserva Activa

```php
$profesor = App\Models\User::where('run', 'RUN_PROFESOR')->first();

// Crear reserva activa de prueba
$reserva = App\Models\Reserva::create([
    'id_reserva' => 'TEST' . time(),
    'id_espacio' => 'A101',
    'run_profesor' => $profesor->run,
    'fecha_reserva' => now()->toDateString(),
    'hora' => now()->format('H:i:s'),
    'estado' => 'activa',
    'tipo_reserva' => 'clase'
]);

// El profesor debe poder acceder al canal room.A101
```

---

## 📊 Pruebas de Base de Datos

### Verificar Inserción de Asistencia

```sql
-- Ver últimas asistencias registradas
SELECT 
    a.id,
    a.rut_asistente,
    a.nombre_asistente,
    a.hora_llegada,
    r.id_reserva,
    e.nombre_espacio,
    r.fecha_reserva
FROM asistencias a
JOIN reservas r ON a.id_reserva = r.id_reserva
JOIN espacios e ON r.id_espacio = e.id_espacio
ORDER BY a.created_at DESC
LIMIT 10;
```

---

### Verificar Prevención de Duplicados

```sql
-- Buscar duplicados (NO debe haber resultados)
SELECT 
    rut_asistente, 
    id_reserva, 
    COUNT(*) as cantidad
FROM asistencias
GROUP BY rut_asistente, id_reserva
HAVING COUNT(*) > 1;
```

---

### Verificar Conteo de Ocupación

```sql
-- Contar asistencias por reserva
SELECT 
    r.id_reserva,
    r.id_espacio,
    e.nombre_espacio,
    e.puestos_disponibles as capacidad,
    COUNT(a.id) as asistencias,
    ROUND((COUNT(a.id) / e.puestos_disponibles) * 100, 2) as porcentaje_ocupacion
FROM reservas r
LEFT JOIN asistencias a ON r.id_reserva = a.id_reserva
JOIN espacios e ON r.id_espacio = e.id_espacio
WHERE r.estado = 'activa'
  AND r.fecha_reserva = CURDATE()
GROUP BY r.id_reserva, r.id_espacio, e.nombre_espacio, e.puestos_disponibles;
```

---

## 🖥️ Pruebas de Frontend (Laravel Echo)

### Test 1: Verificar Conexión de Echo

**Abrir consola del navegador:**
```javascript
// Verificar que Echo esté inicializado
console.log(window.Echo);
// Debe mostrar objeto Echo con configuración

// Verificar conexión
console.log(Echo.connector.pusher.connection.state);
// Debe mostrar: "connected"
```

---

### Test 2: Suscribirse Manualmente a un Canal

```javascript
// Suscribirse al canal de una sala
const channel = Echo.private('room.A101')
    .listen('.attendance.registered', (event) => {
        console.log('Evento recibido:', event);
        alert(`Nueva asistencia: ${event.attendance.student_name}`);
    });

// Verificar suscripción
console.log(Echo.connector.pusher.allChannels());
// Debe incluir: "private-room.A101"
```

---

### Test 3: Componente Blade

**Agregar en una vista de prueba:**
```blade
<!-- resources/views/test-attendance.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Test de Asistencia</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-attendance-monitor room-id="A101" />
        </div>
    </div>
</x-app-layout>
```

**Acceder:** `http://localhost:8000/test-attendance`

---

## 📝 Checklist de Verificación

### Backend
- [ ] Controlador `AttendanceController` existe en `app/Http/Controllers/Api/`
- [ ] Evento `AttendanceRegistered` existe en `app/Events/`
- [ ] Rutas agregadas en `routes/api.php`
- [ ] Canal configurado en `routes/channels.php`
- [ ] Modelo `Asistencia` tiene relaciones correctas

### Base de Datos
- [ ] Tabla `asistencias` existe y tiene estructura correcta
- [ ] Foreign keys configuradas (id_reserva, id_asignatura)
- [ ] Índices creados para optimización
- [ ] Existe al menos una reserva activa para pruebas

### Broadcasting
- [ ] Variables de entorno configuradas (PUSHER_*)
- [ ] `BROADCAST_DRIVER` configurado en `.env`
- [ ] Pusher/Reverb funcionando correctamente
- [ ] Ruta `/broadcasting/auth` accesible

### Frontend
- [ ] Laravel Echo instalado (`pnpm list laravel-echo`)
- [ ] Pusher.js instalado (`pnpm list pusher-js`)
- [ ] Echo configurado en `resources/js/bootstrap.js`
- [ ] Assets compilados (`pnpm run build`)
- [ ] Componente `attendance-monitor.blade.php` accesible

---

## 🚨 Troubleshooting Común

### Error: "Canal no autorizado"
**Causa:** Usuario no tiene permisos o no está autenticado
**Solución:** 
```php
// Verificar en routes/channels.php
// Asegurarse de que el usuario tenga el rol correcto
$user->hasRole('admin') // true
```

---

### Error: "Echo is not defined"
**Causa:** Laravel Echo no está cargado
**Solución:**
```bash
# Instalar dependencias
pnpm add laravel-echo pusher-js

# Compilar assets
pnpm run build
```

---

### Error: "Connection refused"
**Causa:** Pusher/Reverb no está configurado o no corre
**Solución:**
```bash
# Si usas Reverb
php artisan reverb:start

# Verificar logs
tail -f storage/logs/laravel.log
```

---

### Error: "Reserva no encontrada"
**Causa:** No hay reservas activas para la sala
**Solución:**
```php
// Crear reserva de prueba en tinker
$reserva = App\Models\Reserva::create([
    'id_reserva' => 'TEST' . time(),
    'id_espacio' => 'A101',
    'run_profesor' => 12345678,
    'fecha_reserva' => now()->toDateString(),
    'hora' => now()->format('H:i:s'),
    'estado' => 'activa',
    'tipo_reserva' => 'clase',
    'modulos' => 2
]);
```

---

## 📞 Comandos Útiles para Debugging

```bash
# Ver logs en tiempo real
tail -f storage/logs/laravel.log

# Limpiar caché
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Verificar rutas
php artisan route:list | grep attendance

# Verificar eventos
php artisan event:list

# Probar broadcasting
php artisan tinker
>>> event(new App\Events\AttendanceRegistered(...))
```

---

## ✅ Resultado Esperado Final

Al completar todas las pruebas, deberías poder:

1. ✅ Registrar asistencia desde API externa (Tauri)
2. ✅ Ver actualización en tiempo real en el frontend
3. ✅ Contador de ocupación se actualiza automáticamente
4. ✅ Listado de asistentes se actualiza sin refresh
5. ✅ Notificaciones aparecen al registrar nueva asistencia
6. ✅ Prevención de duplicados funciona correctamente
7. ✅ Validaciones retornan errores apropiados
8. ✅ Broadcasting funciona en canal privado
9. ✅ Autorización de canal funciona según roles
10. ✅ Base de datos mantiene integridad de datos

---

**¡Sistema listo y probado!** 🎉
