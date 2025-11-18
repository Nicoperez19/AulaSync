{{-- 
    Componente de Monitoreo de Asistencia en Tiempo Real
    
    Uso:
    <x-attendance-monitor room-id="A101" />
    
    Props:
    - room-id: ID de la sala a monitorear (requerido)
    - show-list: Mostrar lista de asistentes (default: true)
    - auto-refresh: Cargar asistencias existentes al inicio (default: true)
--}}

@props([
    'roomId' => null,
    'showList' => true,
    'autoRefresh' => true
])

@if(!$roomId)
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded" role="alert">
        <strong class="font-bold">Error:</strong>
        <span class="block sm:inline">El atributo room-id es requerido para el componente attendance-monitor.</span>
    </div>
@else
<div 
    x-data="attendanceMonitorComponent('{{ $roomId }}', {{ $autoRefresh ? 'true' : 'false' }})"
    x-init="init()"
    class="attendance-monitor-container"
>
    {{-- Card de Ocupación --}}
    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-semibold text-gray-800">
                <i class="fas fa-users mr-2"></i>
                Ocupación: Sala {{ $roomId }}
            </h3>
            <div 
                class="px-3 py-1 rounded-full text-sm font-medium"
                x-bind:class="{
                    'bg-green-100 text-green-800': occupancy.percentage < 70,
                    'bg-yellow-100 text-yellow-800': occupancy.percentage >= 70 && occupancy.percentage < 90,
                    'bg-red-100 text-red-800': occupancy.percentage >= 90
                }"
            >
                <span x-text="`${occupancy.percentage.toFixed(0)}%`"></span>
            </div>
        </div>
        
        {{-- Contador Principal --}}
        <div class="text-center mb-4">
            <div class="text-5xl font-bold text-gray-800 mb-2">
                <span x-text="occupancy.current"></span>
                <span class="text-gray-400">/</span>
                <span x-text="occupancy.capacity"></span>
            </div>
            <p class="text-sm text-gray-600">Estudiantes presentes</p>
        </div>
        
        {{-- Barra de Progreso --}}
        <div class="w-full bg-gray-200 rounded-full h-6 overflow-hidden">
            <div 
                class="h-6 rounded-full transition-all duration-500 flex items-center justify-center text-white text-xs font-medium"
                x-bind:style="`width: ${occupancy.percentage}%`"
                x-bind:class="{
                    'bg-green-500': occupancy.percentage < 70,
                    'bg-yellow-500': occupancy.percentage >= 70 && occupancy.percentage < 90,
                    'bg-red-500': occupancy.percentage >= 90
                }"
            >
                <span x-show="occupancy.percentage > 10" x-text="`${occupancy.percentage.toFixed(1)}%`"></span>
            </div>
        </div>
        
        {{-- Información adicional --}}
        <div class="mt-4 flex justify-between text-sm text-gray-600">
            <div>
                <i class="fas fa-door-open mr-1"></i>
                <span>Espacios disponibles: </span>
                <span class="font-medium" x-text="occupancy.capacity - occupancy.current"></span>
            </div>
            <div x-show="loading">
                <i class="fas fa-spinner fa-spin mr-1"></i>
                <span>Actualizando...</span>
            </div>
        </div>
    </div>
    
    @if($showList)
    {{-- Lista de Asistencias --}}
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h4 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-clipboard-check mr-2"></i>
                Asistencias Registradas
            </h4>
            <button 
                @click="refreshAttendances()"
                class="text-blue-600 hover:text-blue-800 text-sm"
                x-bind:disabled="loading"
            >
                <i class="fas fa-sync-alt mr-1" x-bind:class="{ 'fa-spin': loading }"></i>
                Actualizar
            </button>
        </div>
        
        <div class="space-y-2 max-h-96 overflow-y-auto">
            <template x-for="(student, index) in students" :key="student.id">
                <div 
                    class="flex justify-between items-center p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors"
                    x-show="true"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform -translate-y-2"
                    x-transition:enter-end="opacity-100 transform translate-y-0"
                >
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                <span class="text-blue-600 font-semibold" x-text="(index + 1)"></span>
                            </div>
                        </div>
                        <div>
                            <p class="font-medium text-gray-800" x-text="student.student_name || student.nombre_asistente"></p>
                            <p class="text-xs text-gray-500" x-text="student.student_id || student.rut_asistente"></p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium text-gray-700" x-text="student.arrival_time || student.hora_llegada"></p>
                        <p class="text-xs text-gray-500">Hora de llegada</p>
                    </div>
                </div>
            </template>
            
            {{-- Estado vacío --}}
            <div 
                x-show="students.length === 0 && !loading" 
                class="text-center py-12"
            >
                <i class="fas fa-user-clock text-gray-300 text-5xl mb-4"></i>
                <p class="text-gray-500 font-medium">No hay asistencias registradas</p>
                <p class="text-gray-400 text-sm mt-1">Las asistencias aparecerán aquí en tiempo real</p>
            </div>
            
            {{-- Estado de carga --}}
            <div 
                x-show="loading && students.length === 0" 
                class="text-center py-12"
            >
                <i class="fas fa-spinner fa-spin text-blue-500 text-4xl mb-4"></i>
                <p class="text-gray-600">Cargando asistencias...</p>
            </div>
        </div>
        
        {{-- Resumen --}}
        <div 
            x-show="students.length > 0"
            class="mt-4 pt-4 border-t border-gray-200 text-sm text-gray-600"
        >
            <div class="flex justify-between">
                <span>Total de asistencias:</span>
                <span class="font-semibold" x-text="students.length"></span>
            </div>
        </div>
    </div>
    @endif
    
    {{-- Contenedor de Notificaciones --}}
    <div 
        id="attendance-notifications" 
        class="fixed top-4 right-4 z-50 space-y-2"
        style="max-width: 320px;"
    >
        <template x-for="notification in notifications" :key="notification.id">
            <div 
                x-show="notification.visible"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-x-8 scale-95"
                x-transition:enter-end="opacity-100 transform translate-x-0 scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0 transform translate-x-8"
                class="bg-green-500 text-white px-4 py-3 rounded-lg shadow-lg flex items-start space-x-3"
            >
                <i class="fas fa-check-circle text-xl mt-0.5"></i>
                <div class="flex-1">
                    <p class="font-medium text-sm" x-text="notification.message"></p>
                    <p class="text-xs opacity-90 mt-1" x-text="notification.time"></p>
                </div>
                <button 
                    @click="notification.visible = false"
                    class="text-white hover:text-gray-200 transition-colors"
                >
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </template>
    </div>
</div>

<script>
function attendanceMonitorComponent(roomId, autoRefresh) {
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
        loading: false,
        echoChannel: null,
        
        init() {
            console.log(`Inicializando monitor de asistencia para sala: ${this.roomId}`);
            
            // Suscribirse al canal de Echo
            this.subscribeToChannel();
            
            // Cargar asistencias existentes si está habilitado
            if (autoRefresh) {
                this.refreshAttendances();
            }
        },
        
        subscribeToChannel() {
            if (!window.Echo) {
                console.error('Laravel Echo no está configurado');
                return;
            }
            
            this.echoChannel = window.Echo.private(`room.${this.roomId}`)
                .listen('.attendance.registered', (event) => {
                    console.log('Evento de asistencia recibido:', event);
                    this.handleAttendanceRegistered(event);
                })
                .error((error) => {
                    console.error('Error en la suscripción al canal:', error);
                });
            
            console.log(`Suscrito al canal: private-room.${this.roomId}`);
        },
        
        handleAttendanceRegistered(event) {
            // Actualizar ocupación
            this.occupancy = {
                current: event.occupancy.current,
                capacity: event.occupancy.capacity || this.occupancy.capacity,
                percentage: event.occupancy.percentage || 0
            };
            
            // Agregar estudiante a la lista (al inicio)
            const newStudent = {
                id: event.attendance.id,
                student_id: event.attendance.student_id,
                student_name: event.attendance.student_name,
                arrival_time: event.attendance.arrival_time,
                rut_asistente: event.attendance.student_id,
                nombre_asistente: event.attendance.student_name,
                hora_llegada: event.attendance.arrival_time
            };
            
            this.students.unshift(newStudent);
            
            // Mostrar notificación
            this.showNotification(
                `${event.attendance.student_name} registró su asistencia`,
                event.attendance.arrival_time
            );
            
            // Reproducir sonido (opcional)
            this.playNotificationSound();
        },
        
        showNotification(message, time) {
            const id = ++this.notificationId;
            const notification = {
                id: id,
                message: message,
                time: time,
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
        
        async refreshAttendances() {
            this.loading = true;
            
            try {
                // Obtener reserva activa actual
                const reservaResponse = await fetch(`/api/reserva-activa/${this.roomId}`);
                
                if (!reservaResponse.ok) {
                    console.warn('No hay reserva activa para esta sala');
                    this.students = [];
                    this.occupancy = { current: 0, capacity: 0, percentage: 0 };
                    return;
                }
                
                const reservaData = await reservaResponse.json();
                
                if (!reservaData.reserva_activa) {
                    console.warn('No hay reserva activa');
                    this.students = [];
                    return;
                }
                
                // Cargar asistencias de esta reserva
                const attendanceResponse = await fetch(
                    `/api/attendance/reservation/${reservaData.reserva_activa.id_reserva}`
                );
                
                if (attendanceResponse.ok) {
                    const attendanceData = await attendanceResponse.json();
                    
                    if (attendanceData.success) {
                        this.students = attendanceData.data.attendances || [];
                        this.occupancy = {
                            current: attendanceData.data.total_attendances || 0,
                            capacity: attendanceData.data.capacity || 0,
                            percentage: attendanceData.data.capacity 
                                ? ((attendanceData.data.total_attendances / attendanceData.data.capacity) * 100)
                                : 0
                        };
                    }
                }
            } catch (error) {
                console.error('Error cargando asistencias:', error);
            } finally {
                this.loading = false;
            }
        },
        
        playNotificationSound() {
            // Reproducir un sonido de notificación (opcional)
            // Puedes agregar un archivo de audio o usar la Web Audio API
            try {
                const audio = new Audio('/sounds/notification.mp3');
                audio.volume = 0.5;
                audio.play().catch(e => console.log('No se pudo reproducir el sonido:', e));
            } catch (e) {
                // Ignorar errores de audio
            }
        },
        
        destroy() {
            // Limpiar suscripción al salir
            if (this.echoChannel) {
                window.Echo.leave(`room.${this.roomId}`);
            }
        }
    };
}
</script>
@endif
