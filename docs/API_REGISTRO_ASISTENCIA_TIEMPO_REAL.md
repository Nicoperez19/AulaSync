# API de Registro de Asistencia en Tiempo Real

## Descripción General

Sistema de registro de asistencia universitaria con actualización en tiempo real mediante broadcasting. Permite que un cliente externo (aplicación Tauri) escanee IDs de estudiantes y registre su asistencia, mientras el frontend de administración se actualiza automáticamente.

## Arquitectura

```
Cliente Tauri (Escáner)
    ↓ POST /api/attendance
Laravel API (Validación + Registro)
    ↓ Dispara Evento
Broadcasting Server (Pusher/Reverb/etc)
    ↓ Transmite a canal privado
Frontend Laravel (Echo.js)
    ↓ Actualiza UI en tiempo real
```

## Endpoints de API

### 1. Registrar Asistencia

**Endpoint:** `POST /api/attendance`

**Descripción:** Registra la asistencia de un estudiante en una clase activa.

**Headers:**
```http
Content-Type: application/json
Accept: application/json
```

**Request Body:**
```json
{
  "student_id": "12345678",
  "room_id": "A101",
  "student_name": "Juan Pérez" // Opcional
}
```

O alternativamente con `reservation_id`:
```json
{
  "student_id": "12345678",
  "reservation_id": "R20251118143001",
  "student_name": "Juan Pérez" // Opcional
}
```

**Validaciones:**
- ✅ `student_id`: Requerido, string, máximo 20 caracteres
- ✅ `room_id`: Requerido si no se proporciona `reservation_id`, debe existir en tabla espacios
- ✅ `reservation_id`: Requerido si no se proporciona `room_id`, debe existir en tabla reservas
- ✅ `student_name`: Opcional, string, máximo 255 caracteres

**Response Success (201):**
```json
{
  "success": true,
  "message": "Asistencia registrada exitosamente",
  "data": {
    "attendance": {
      "id": 1,
      "student_id": "12345678",
      "student_name": "Juan Pérez",
      "arrival_time": "14:30:00",
      "registered_at": "2025-11-18 14:30:15"
    },
    "reservation": {
      "id": "R20251118143001",
      "room_id": "A101",
      "room_name": "Sala A101",
      "date": "2025-11-18",
      "start_time": "14:30:00",
      "type": "clase",
      "instructor": {
        "type": "profesor",
        "name": "Dr. María González",
        "id": "87654321"
      }
    },
    "occupancy": {
      "current": 15,
      "capacity": 40
    },
    "subject": {
      "id": "INF101"
    }
  }
}
```

**Response Error - No hay reserva activa (404):**
```json
{
  "success": false,
  "message": "No hay una reserva activa en esta sala en este momento",
  "details": {
    "room_id": "A101",
    "current_time": "14:30:00",
    "current_date": "2025-11-18"
  }
}
```

**Response Error - Asistencia duplicada (409):**
```json
{
  "success": false,
  "message": "Este estudiante ya tiene registrada su asistencia para esta clase",
  "attendance": {
    "id": 1,
    "registered_at": "14:25:00",
    "created_at": "2025-11-18 14:25:30"
  }
}
```

**Response Error - Validación (422):**
```json
{
  "success": false,
  "message": "Error de validación",
  "errors": {
    "student_id": [
      "El ID del estudiante es obligatorio"
    ],
    "room_id": [
      "La sala especificada no existe"
    ]
  }
}
```

**Response Error - Error interno (500):**
```json
{
  "success": false,
  "message": "Error al registrar la asistencia",
  "error": "Mensaje de error detallado (solo en modo debug)"
}
```

### 2. Obtener Asistencias de una Reserva

**Endpoint:** `GET /api/attendance/reservation/{reservationId}`

**Descripción:** Obtiene el listado completo de asistencias registradas para una reserva específica.

**Response Success (200):**
```json
{
  "success": true,
  "data": {
    "reservation": {
      "id": "R20251118143001",
      "room_id": "A101",
      "room_name": "Sala A101",
      "date": "2025-11-18",
      "start_time": "14:30:00",
      "status": "activa"
    },
    "attendances": [
      {
        "id": 1,
        "student_id": "12345678",
        "student_name": "Juan Pérez",
        "arrival_time": "14:30:00",
        "registered_at": "2025-11-18 14:30:15",
        "subject": {
          "id": "INF101",
          "name": "Programación I"
        }
      },
      {
        "id": 2,
        "student_id": "87654321",
        "student_name": "María López",
        "arrival_time": "14:32:00",
        "registered_at": "2025-11-18 14:32:08",
        "subject": {
          "id": "INF101",
          "name": "Programación I"
        }
      }
    ],
    "total_attendances": 2,
    "capacity": 40
  }
}
```

## Broadcasting en Tiempo Real

### Evento: AttendanceRegistered

Cuando se registra exitosamente una asistencia, se dispara el evento `AttendanceRegistered` que se transmite por el canal privado `room.{roomId}`.

**Canal:** `private-room.{roomId}`

**Nombre del evento:** `attendance.registered`

**Payload del evento:**
```json
{
  "room_id": "A101",
  "reservation_id": "R20251118143001",
  "attendance": {
    "id": 1,
    "student_id": "12345678",
    "student_name": "Juan Pérez",
    "arrival_time": "14:30:00",
    "registered_at": "2025-11-18T14:30:15+00:00"
  },
  "occupancy": {
    "current": 15,
    "capacity": 40,
    "percentage": 37.5
  },
  "instructor": {
    "type": "profesor",
    "name": "Dr. María González",
    "id": "87654321"
  },
  "timestamp": "2025-11-18T14:30:15+00:00"
}
```

### Autorización del Canal

El canal `room.{roomId}` es **privado** y requiere autenticación. Los usuarios autorizados son:

1. **Administradores del sistema** (roles: `admin`, `super-admin`)
2. **Profesores con reserva activa** en la sala
3. **Usuarios con permiso** `view-room-attendance`

La autorización se verifica en `routes/channels.php`.

## Implementación en Frontend con Laravel Echo

### 1. Instalación de Dependencias

```bash
pnpm add laravel-echo pusher-js
```

### 2. Configuración de Laravel Echo

**IMPORTANTE:** El sistema ahora usa **Laravel Reverb** (self-hosted) en lugar de Pusher. Para instrucciones completas de migración, ver [MIGRACION_PUSHER_A_REVERB.md](./MIGRACION_PUSHER_A_REVERB.md)

#### Configuración para Laravel Reverb (Recomendado)

En tu archivo `resources/js/bootstrap.js`:

```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST ?? '127.0.0.1',
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https',
    enabledTransports: ['ws', 'wss'],
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '',
        },
    },
});
```

#### Configuración Alternativa para Pusher (Legacy)

Si aún usas Pusher en lugar de Reverb:

```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
    wsHost: import.meta.env.VITE_PUSHER_HOST ?? `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
    wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
    wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    }
});
```

### 3. Suscripción al Canal y Escucha de Eventos

**Opción A: JavaScript Vanilla / Alpine.js**

```javascript
// En tu componente de dashboard o vista de sala
const roomId = 'A101'; // ID de la sala que quieres monitorear

// Suscribirse al canal privado de la sala
window.Echo.private(`room.${roomId}`)
    .listen('.attendance.registered', (event) => {
        console.log('Nueva asistencia registrada:', event);
        
        // Actualizar el contador de ocupación
        updateOccupancyCounter(event.occupancy.current, event.occupancy.capacity);
        
        // Actualizar porcentaje
        updateOccupancyPercentage(event.occupancy.percentage);
        
        // Agregar estudiante a la lista
        addStudentToList(event.attendance);
        
        // Mostrar notificación
        showNotification(`${event.attendance.student_name} ha registrado su asistencia`);
    })
    .error((error) => {
        console.error('Error en la suscripción al canal:', error);
    });

// Funciones auxiliares
function updateOccupancyCounter(current, capacity) {
    const counterElement = document.getElementById('occupancy-counter');
    if (counterElement) {
        counterElement.textContent = `${current} / ${capacity}`;
    }
}

function updateOccupancyPercentage(percentage) {
    const progressBar = document.getElementById('occupancy-progress');
    if (progressBar) {
        progressBar.style.width = `${percentage}%`;
        progressBar.setAttribute('aria-valuenow', percentage);
    }
}

function addStudentToList(attendance) {
    const listElement = document.getElementById('students-list');
    if (listElement) {
        const studentItem = document.createElement('div');
        studentItem.className = 'student-item';
        studentItem.innerHTML = `
            <div class="flex justify-between items-center p-2 border-b">
                <span class="font-medium">${attendance.student_name}</span>
                <span class="text-sm text-gray-500">${attendance.arrival_time}</span>
            </div>
        `;
        listElement.insertBefore(studentItem, listElement.firstChild);
    }
}

function showNotification(message) {
    // Implementar tu sistema de notificaciones
    // Ejemplo con toast/alert simple
    const notification = document.createElement('div');
    notification.className = 'notification bg-green-500 text-white p-4 rounded mb-2';
    notification.textContent = message;
    document.getElementById('notifications-container')?.appendChild(notification);
    
    setTimeout(() => notification.remove(), 5000);
}

// Desuscribirse al salir de la página
window.addEventListener('beforeunload', () => {
    window.Echo.leave(`room.${roomId}`);
});
```

**Opción B: Con Alpine.js (Más reactivo)**

```html
<div x-data="attendanceMonitor('A101')" class="attendance-panel">
    <!-- Contador de Ocupación -->
    <div class="occupancy-card">
        <h3 class="text-lg font-semibold mb-2">Ocupación Actual</h3>
        <div class="text-4xl font-bold" x-text="`${occupancy.current} / ${occupancy.capacity}`"></div>
        
        <!-- Barra de progreso -->
        <div class="w-full bg-gray-200 rounded-full h-4 mt-4">
            <div 
                class="bg-blue-600 h-4 rounded-full transition-all duration-300"
                :style="`width: ${occupancy.percentage}%`"
                x-bind:class="{
                    'bg-green-500': occupancy.percentage < 70,
                    'bg-yellow-500': occupancy.percentage >= 70 && occupancy.percentage < 90,
                    'bg-red-500': occupancy.percentage >= 90
                }"
            ></div>
        </div>
        <p class="text-sm text-gray-600 mt-2" x-text="`${occupancy.percentage}% de capacidad`"></p>
    </div>
    
    <!-- Lista de Asistencias -->
    <div class="students-list mt-6">
        <h3 class="text-lg font-semibold mb-4">Asistencias Registradas</h3>
        <div class="space-y-2">
            <template x-for="student in students" :key="student.id">
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                    <div>
                        <p class="font-medium" x-text="student.student_name"></p>
                        <p class="text-xs text-gray-500" x-text="student.student_id"></p>
                    </div>
                    <span class="text-sm text-gray-600" x-text="student.arrival_time"></span>
                </div>
            </template>
        </div>
    </div>
    
    <!-- Notificaciones -->
    <div id="notifications-container" class="fixed top-4 right-4 z-50 space-y-2">
        <template x-for="notification in notifications" :key="notification.id">
            <div 
                x-show="notification.visible"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-x-4"
                x-transition:enter-end="opacity-100 transform translate-x-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg"
                x-text="notification.message"
            ></div>
        </template>
    </div>
</div>

<script>
function attendanceMonitor(roomId) {
    return {
        roomId: roomId,
        occupancy: {
            current: 0,
            capacity: 0,
            percentage: 0
        },
        students: [],
        notifications: [],
        notificationId: 0,
        
        init() {
            // Suscribirse al canal de la sala
            window.Echo.private(`room.${this.roomId}`)
                .listen('.attendance.registered', (event) => {
                    console.log('Evento recibido:', event);
                    this.handleAttendanceRegistered(event);
                })
                .error((error) => {
                    console.error('Error en canal:', error);
                });
            
            // Cargar asistencias existentes (opcional)
            this.loadExistingAttendances();
        },
        
        handleAttendanceRegistered(event) {
            // Actualizar ocupación
            this.occupancy = {
                current: event.occupancy.current,
                capacity: event.occupancy.capacity,
                percentage: event.occupancy.percentage
            };
            
            // Agregar estudiante a la lista (al inicio)
            this.students.unshift({
                id: event.attendance.id,
                student_id: event.attendance.student_id,
                student_name: event.attendance.student_name,
                arrival_time: event.attendance.arrival_time
            });
            
            // Mostrar notificación
            this.showNotification(`${event.attendance.student_name} registró su asistencia`);
            
            // Reproducir sonido (opcional)
            // this.playNotificationSound();
        },
        
        showNotification(message) {
            const id = ++this.notificationId;
            const notification = {
                id: id,
                message: message,
                visible: true
            };
            
            this.notifications.push(notification);
            
            // Auto-ocultar después de 5 segundos
            setTimeout(() => {
                const index = this.notifications.findIndex(n => n.id === id);
                if (index !== -1) {
                    this.notifications[index].visible = false;
                    // Remover después de la animación
                    setTimeout(() => {
                        this.notifications = this.notifications.filter(n => n.id !== id);
                    }, 300);
                }
            }, 5000);
        },
        
        async loadExistingAttendances() {
            try {
                // Obtener reserva activa actual
                const response = await fetch(`/api/reserva-activa/${this.roomId}`);
                if (response.ok) {
                    const data = await response.json();
                    if (data.reserva_activa) {
                        // Cargar asistencias de esta reserva
                        const attendanceResponse = await fetch(
                            `/api/attendance/reservation/${data.reserva_activa.id_reserva}`
                        );
                        if (attendanceResponse.ok) {
                            const attendanceData = await attendanceResponse.json();
                            if (attendanceData.success) {
                                this.students = attendanceData.data.attendances;
                                this.occupancy = {
                                    current: attendanceData.data.total_attendances,
                                    capacity: attendanceData.data.capacity,
                                    percentage: attendanceData.data.capacity 
                                        ? (attendanceData.data.total_attendances / attendanceData.data.capacity * 100).toFixed(2)
                                        : 0
                                };
                            }
                        }
                    }
                }
            } catch (error) {
                console.error('Error cargando asistencias existentes:', error);
            }
        }
    };
}
</script>
```

**Opción C: Con Livewire**

```php
// app/Livewire/AttendanceMonitor.php
<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Reserva;
use App\Models\Asistencia;
use App\Models\Espacio;

class AttendanceMonitor extends Component
{
    public $roomId;
    public $currentOccupancy = 0;
    public $capacity = 0;
    public $attendances = [];
    
    protected $listeners = ['echo-private:room.{roomId},attendance.registered' => 'handleAttendanceRegistered'];
    
    public function mount($roomId)
    {
        $this->roomId = $roomId;
        $this->loadData();
    }
    
    public function loadData()
    {
        $espacio = Espacio::find($this->roomId);
        $this->capacity = $espacio->puestos_disponibles ?? 0;
        
        $reservaActiva = Reserva::where('id_espacio', $this->roomId)
            ->where('estado', 'activa')
            ->where('fecha_reserva', now()->toDateString())
            ->first();
        
        if ($reservaActiva) {
            $this->attendances = Asistencia::where('id_reserva', $reservaActiva->id_reserva)
                ->orderBy('hora_llegada', 'desc')
                ->get()
                ->toArray();
            
            $this->currentOccupancy = count($this->attendances);
        }
    }
    
    public function handleAttendanceRegistered($event)
    {
        $this->currentOccupancy = $event['occupancy']['current'];
        
        array_unshift($this->attendances, [
            'id' => $event['attendance']['id'],
            'rut_asistente' => $event['attendance']['student_id'],
            'nombre_asistente' => $event['attendance']['student_name'],
            'hora_llegada' => $event['attendance']['arrival_time'],
        ]);
        
        $this->dispatch('attendance-registered', name: $event['attendance']['student_name']);
    }
    
    public function render()
    {
        $percentage = $this->capacity > 0 
            ? round(($this->currentOccupancy / $this->capacity) * 100, 2)
            : 0;
        
        return view('livewire.attendance-monitor', [
            'percentage' => $percentage
        ]);
    }
}
```

```blade
{{-- resources/views/livewire/attendance-monitor.blade.php --}}
<div>
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h3 class="text-xl font-semibold mb-4">Ocupación: Sala {{ $roomId }}</h3>
        
        <div class="text-4xl font-bold mb-4">
            {{ $currentOccupancy }} / {{ $capacity }}
        </div>
        
        <div class="w-full bg-gray-200 rounded-full h-4">
            <div 
                class="h-4 rounded-full transition-all duration-500 
                    @if($percentage < 70) bg-green-500 
                    @elseif($percentage < 90) bg-yellow-500 
                    @else bg-red-500 
                    @endif"
                style="width: {{ $percentage }}%"
            ></div>
        </div>
        
        <p class="text-sm text-gray-600 mt-2">{{ $percentage }}% de capacidad</p>
    </div>
    
    <div class="mt-6 bg-white rounded-lg shadow-lg p-6">
        <h4 class="text-lg font-semibold mb-4">Asistencias Registradas</h4>
        
        <div class="space-y-2">
            @forelse($attendances as $attendance)
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded" wire:key="attendance-{{ $attendance['id'] }}">
                    <div>
                        <p class="font-medium">{{ $attendance['nombre_asistente'] }}</p>
                        <p class="text-xs text-gray-500">{{ $attendance['rut_asistente'] }}</p>
                    </div>
                    <span class="text-sm text-gray-600">{{ $attendance['hora_llegada'] }}</span>
                </div>
            @empty
                <p class="text-gray-500 text-center py-4">No hay asistencias registradas</p>
            @endforelse
        </div>
    </div>
</div>
```

### 4. Configuración de Variables de Entorno

Asegúrate de tener configuradas las variables de broadcasting en tu `.env`:

```env
BROADCAST_DRIVER=pusher

PUSHER_APP_ID=your-app-id
PUSHER_APP_KEY=your-app-key
PUSHER_APP_SECRET=your-app-secret
PUSHER_APP_CLUSTER=mt1

VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

**Nota:** Si usas Laravel Reverb en lugar de Pusher, ajusta la configuración según la documentación de Reverb.

## Ejemplos de Uso

### Desde Cliente Tauri (Rust/JavaScript)

```javascript
// En tu aplicación Tauri
async function registerAttendance(studentId, roomId) {
    try {
        const response = await fetch('https://tu-dominio.com/api/attendance', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                student_id: studentId,
                room_id: roomId
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            console.log('Asistencia registrada:', data.data);
            // Mostrar confirmación visual/sonora al usuario
            showSuccessFeedback(data.data.attendance.student_name);
        } else {
            console.error('Error:', data.message);
            showErrorFeedback(data.message);
        }
    } catch (error) {
        console.error('Error de red:', error);
        showErrorFeedback('Error de conexión');
    }
}

// Cuando el escáner lee un código
function onBarcodeScanned(barcode) {
    const studentId = extractStudentId(barcode);
    const currentRoomId = getCurrentRoomId(); // Obtener de configuración o QR de sala
    
    registerAttendance(studentId, currentRoomId);
}
```

## Notas Importantes

### Seguridad

1. **Autenticación**: Los endpoints pueden ser protegidos con Sanctum si se requiere autenticación del cliente Tauri.
2. **Autorización de Canal**: El canal `room.{roomId}` es privado y requiere autenticación Laravel.
3. **Rate Limiting**: Considera agregar throttling a los endpoints para prevenir abuso.

### Performance

1. **Índices de Base de Datos**: Ya están configurados en las migraciones para optimizar consultas.
2. **Caché**: Considera cachear información de salas y capacidades si el volumen es alto.
3. **Queue**: El broadcasting ya está preparado para usar colas si es necesario.

### Monitoreo

1. **Logs**: Todos los registros y errores se guardan en los logs de Laravel.
2. **Métricas**: Considera agregar métricas para monitorear:
   - Tiempo de respuesta del endpoint
   - Tasa de éxito/error
   - Asistencias por hora/día
   - Ocupación promedio de salas

## Troubleshooting

### El evento no se transmite

1. Verifica que `BROADCAST_DRIVER` esté configurado correctamente en `.env`
2. Ejecuta `php artisan config:cache`
3. Verifica las credenciales de Pusher/Reverb
4. Revisa los logs de Laravel: `tail -f storage/logs/laravel.log`

### Error de autorización en el canal

1. Asegúrate de que el usuario esté autenticado
2. Verifica que el usuario tenga los permisos correctos
3. Revisa la lógica en `routes/channels.php`
4. Verifica que el CSRF token esté presente en las peticiones de autorización

### Asistencias duplicadas

El sistema ya previene duplicados, pero si ocurre:
1. Verifica que las transacciones de base de datos estén funcionando
2. Revisa que no haya múltiples instancias del escáner registrando simultáneamente
3. Considera agregar un delay o debounce en el cliente

## Próximos Pasos

1. **Implementar Rate Limiting**: Agregar throttling específico para el endpoint
2. **Autenticación para Cliente Tauri**: Configurar Sanctum para tokens de API
3. **Reportes de Asistencia**: Crear endpoints para exportar reportes
4. **Notificaciones Push**: Agregar notificaciones push para móviles
5. **Dashboard de Métricas**: Crear dashboard con estadísticas en tiempo real
