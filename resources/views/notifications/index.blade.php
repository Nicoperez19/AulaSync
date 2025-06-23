<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight" style="font-style: oblique;">
                Centro de Notificaciones
            </h2>
            <div class="flex items-center gap-2">
                <button id="mark-all-read" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 transition-colors">
                    Marcar todas como leídas
                </button>
                <button id="clear-all" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 transition-colors">
                    Limpiar todas
                </button>
            </div>
        </div>
    </x-slot>

    <div class="p-6">
        <!-- Estadísticas -->
        <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-4">
            <div class="p-6 bg-white rounded-lg shadow-md">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">No leídas</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $unreadCount }}</p>
                    </div>
                </div>
            </div>

            <div class="p-6 bg-white rounded-lg shadow-md">
                <div class="flex items-center">
                    <div class="p-3 bg-red-100 rounded-full">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Urgentes</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $urgentCount }}</p>
                    </div>
                </div>
            </div>

            <div class="p-6 bg-white rounded-lg shadow-md">
                <div class="flex items-center">
                    <div class="p-3 bg-yellow-100 rounded-full">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Devolución Llaves</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $statsByType['key_return'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="p-6 bg-white rounded-lg shadow-md">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-full">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 6v6m-4-6h8m-8 6h8"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Reservas</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $statsByType['reservation'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="p-6 mb-6 bg-white rounded-lg shadow-md">
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-gray-700">Tipo:</label>
                    <select id="filter-type" class="px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="all">Todos</option>
                        <option value="key_return">Devolución Llaves</option>
                        <option value="reservation">Reservas</option>
                        <option value="system">Sistema</option>
                        <option value="warning">Advertencias</option>
                        <option value="info">Información</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-gray-700">Prioridad:</label>
                    <select id="filter-priority" class="px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="all">Todas</option>
                        <option value="urgent">Urgente</option>
                        <option value="high">Alta</option>
                        <option value="medium">Media</option>
                        <option value="low">Baja</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-gray-700">Estado:</label>
                    <select id="filter-status" class="px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="all">Todos</option>
                        <option value="unread">No leídas</option>
                        <option value="read">Leídas</option>
                    </select>
                </div>

                <button id="apply-filters" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 transition-colors">
                    Aplicar Filtros
                </button>
            </div>
        </div>

        <!-- Lista de Notificaciones -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Notificaciones</h3>
            </div>

            <div id="notifications-container">
                @forelse($notifications as $notification)
                    <div class="notification-item p-6 border-b border-gray-200 hover:bg-gray-50 transition-colors {{ $notification->isRead() ? 'opacity-75' : '' }}" data-id="{{ $notification->id }}">
                        <div class="flex items-start justify-between">
                            <div class="flex items-start space-x-4">
                                <!-- Icono de tipo -->
                                <div class="flex-shrink-0">
                                    <div class="p-2 rounded-full {{ $notification->type_color }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            @if($notification->type_icon === 'key')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                            @elseif($notification->type_icon === 'calendar')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 6v6m-4-6h8m-8 6h8"></path>
                                            @elseif($notification->type_icon === 'cog')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            @elseif($notification->type_icon === 'exclamation-triangle')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                            @elseif($notification->type_icon === 'information-circle')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            @else
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                            @endif
                                        </svg>
                                    </div>
                                </div>

                                <!-- Contenido -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center space-x-2">
                                        <h4 class="text-sm font-medium text-gray-900 {{ $notification->isRead() ? '' : 'font-semibold' }}">
                                            {{ $notification->title }}
                                        </h4>
                                        
                                        <!-- Indicador de prioridad -->
                                        @if($notification->priority === 'urgent')
                                            <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-red-800 bg-red-100 rounded-full">
                                                Urgente
                                            </span>
                                        @elseif($notification->priority === 'high')
                                            <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-orange-800 bg-orange-100 rounded-full">
                                                Alta
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <p class="mt-1 text-sm text-gray-600">
                                        {{ $notification->message }}
                                    </p>
                                    
                                    <div class="flex items-center mt-2 space-x-4 text-xs text-gray-500">
                                        <span>{{ $notification->created_at->diffForHumans() }}</span>
                                        @if($notification->expires_at)
                                            <span>Expira: {{ $notification->expires_at->diffForHumans() }}</span>
                                        @endif
                                    </div>

                                    @if($notification->action_url && $notification->action_text)
                                        <div class="mt-3">
                                            <a href="{{ $notification->action_url }}" class="inline-flex items-center px-3 py-1 text-xs font-medium text-blue-600 bg-blue-100 rounded-md hover:bg-blue-200 transition-colors">
                                                {{ $notification->action_text }}
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Acciones -->
                            <div class="flex items-center space-x-2">
                                @if(!$notification->isRead())
                                    <button class="mark-read-btn p-1 text-gray-400 hover:text-blue-600 transition-colors" title="Marcar como leída">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </button>
                                @endif
                                
                                <button class="delete-notification-btn p-1 text-gray-400 hover:text-red-600 transition-colors" title="Eliminar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-12 text-center">
                        <svg class="mx-auto w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No hay notificaciones</h3>
                        <p class="mt-1 text-sm text-gray-500">No tienes notificaciones pendientes.</p>
                    </div>
                @endforelse
            </div>

            <!-- Paginación -->
            @if($notifications->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Marcar como leída
            document.querySelectorAll('.mark-read-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const notificationItem = this.closest('.notification-item');
                    const notificationId = notificationItem.dataset.id;
                    
                    fetch('/notifications/mark-read', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ notification_id: notificationId })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            notificationItem.classList.add('opacity-75');
                            this.remove();
                            updateNotificationCounts();
                        }
                    });
                });
            });

            // Eliminar notificación
            document.querySelectorAll('.delete-notification-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    if (confirm('¿Estás seguro de que quieres eliminar esta notificación?')) {
                        const notificationItem = this.closest('.notification-item');
                        const notificationId = notificationItem.dataset.id;
                        
                        fetch('/notifications/delete', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ notification_id: notificationId })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                notificationItem.remove();
                                updateNotificationCounts();
                            }
                        });
                    }
                });
            });

            // Marcar todas como leídas
            document.getElementById('mark-all-read').addEventListener('click', function() {
                fetch('/notifications/mark-all-read', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                });
            });

            // Limpiar todas
            document.getElementById('clear-all').addEventListener('click', function() {
                if (confirm('¿Estás seguro de que quieres eliminar todas las notificaciones? Esta acción no se puede deshacer.')) {
                    fetch('/notifications/clear-all', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        }
                    });
                }
            });

            // Aplicar filtros
            document.getElementById('apply-filters').addEventListener('click', function() {
                const type = document.getElementById('filter-type').value;
                const priority = document.getElementById('filter-priority').value;
                const status = document.getElementById('filter-status').value;
                
                const params = new URLSearchParams();
                if (type !== 'all') params.append('type', type);
                if (priority !== 'all') params.append('priority', priority);
                if (status !== 'all') params.append('status', status);
                
                window.location.href = '/notifications?' + params.toString();
            });

            function updateNotificationCounts() {
                fetch('/notifications/unread-count')
                    .then(response => response.json())
                    .then(data => {
                        // Actualizar contadores en la página si es necesario
                        console.log('Notificaciones no leídas:', data.unread_count);
                        console.log('Notificaciones urgentes:', data.urgent_count);
                    });
            }
        });
    </script>
    @endpush
</x-app-layout> 