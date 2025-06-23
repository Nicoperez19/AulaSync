<div x-data="notificationCenter()" x-init="init()" class="relative">
    <!-- Botón de notificaciones -->
    <button @click="toggleDropdown()" class="relative p-2 text-gray-600 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded-md transition-colors">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
        </svg>
        
        <!-- Contador de notificaciones -->
        <span x-show="unreadCount > 0" x-text="unreadCount" class="absolute -top-1 -right-1 inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-500 rounded-full"></span>
        
        <!-- Indicador de urgentes -->
        <span x-show="urgentCount > 0" class="absolute -top-1 -right-1 inline-flex items-center justify-center w-3 h-3 bg-orange-500 rounded-full animate-pulse"></span>
    </button>

    <!-- Dropdown de notificaciones -->
    <div x-show="isOpen" @click.away="isOpen = false" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50">
        <div class="py-2">
            <!-- Header -->
            <div class="px-4 py-2 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-medium text-gray-900">Notificaciones</h3>
                    <div class="flex items-center space-x-2">
                        <button @click="markAllAsRead()" class="text-xs text-blue-600 hover:text-blue-800">Marcar todas</button>
                        <a href="/notifications" class="text-xs text-gray-600 hover:text-gray-800">Ver todas</a>
                    </div>
                </div>
            </div>

            <!-- Lista de notificaciones -->
            <div class="max-h-96 overflow-y-auto">
                <template x-if="notifications.length === 0">
                    <div class="px-4 py-8 text-center">
                        <svg class="mx-auto w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">No hay notificaciones</p>
                    </div>
                </template>

                <template x-for="notification in notifications" :key="notification.id">
                    <div class="px-4 py-3 hover:bg-gray-50 transition-colors border-b border-gray-100 last:border-b-0">
                        <div class="flex items-start space-x-3">
                            <!-- Icono -->
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center" :class="getTypeColor(notification.type)">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getTypeIcon(notification.type)"></path>
                                    </svg>
                                </div>
                            </div>

                            <!-- Contenido -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-2">
                                    <p class="text-sm font-medium text-gray-900" x-text="notification.title"></p>
                                    <span x-show="notification.priority === 'urgent'" class="inline-flex items-center px-1.5 py-0.5 text-xs font-medium text-red-800 bg-red-100 rounded-full">Urgente</span>
                                </div>
                                <p class="mt-1 text-sm text-gray-600" x-text="notification.message"></p>
                                <p class="mt-1 text-xs text-gray-500" x-text="formatTime(notification.created_at)"></p>
                                
                                <!-- Botón de acción -->
                                <div x-show="notification.action_url && notification.action_text" class="mt-2">
                                    <a :href="notification.action_url" class="inline-flex items-center px-2 py-1 text-xs font-medium text-blue-600 bg-blue-100 rounded hover:bg-blue-200 transition-colors">
                                        <span x-text="notification.action_text"></span>
                                    </a>
                                </div>
                            </div>

                            <!-- Acciones -->
                            <div class="flex-shrink-0">
                                <button @click="markAsRead(notification.id)" class="text-gray-400 hover:text-blue-600 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Footer -->
            <div class="px-4 py-2 border-t border-gray-200">
                <a href="/notifications" class="block text-center text-sm text-blue-600 hover:text-blue-800">
                    Ver todas las notificaciones
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function notificationCenter() {
    return {
        isOpen: false,
        notifications: [],
        unreadCount: 0,
        urgentCount: 0,
        pollingInterval: null,

        init() {
            this.loadNotifications();
            this.startPolling();
        },

        toggleDropdown() {
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                this.loadNotifications();
            }
        },

        async loadNotifications() {
            try {
                const response = await fetch('/notifications/recent');
                const data = await response.json();
                this.notifications = data;
            } catch (error) {
                console.error('Error loading notifications:', error);
            }
        },

        async updateCounts() {
            try {
                const response = await fetch('/notifications/unread-count');
                const data = await response.json();
                this.unreadCount = data.unread_count;
                this.urgentCount = data.urgent_count;
            } catch (error) {
                console.error('Error updating counts:', error);
            }
        },

        async markAsRead(notificationId) {
            try {
                const response = await fetch('/notifications/mark-read', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ notification_id: notificationId })
                });

                if (response.ok) {
                    this.loadNotifications();
                    this.updateCounts();
                }
            } catch (error) {
                console.error('Error marking notification as read:', error);
            }
        },

        async markAllAsRead() {
            try {
                const response = await fetch('/notifications/mark-all-read', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (response.ok) {
                    this.loadNotifications();
                    this.updateCounts();
                }
            } catch (error) {
                console.error('Error marking all notifications as read:', error);
            }
        },

        startPolling() {
            this.pollingInterval = setInterval(() => {
                this.updateCounts();
            }, 30000); // Actualizar cada 30 segundos
        },

        stopPolling() {
            if (this.pollingInterval) {
                clearInterval(this.pollingInterval);
            }
        },

        getTypeColor(type) {
            const colors = {
                'key_return': 'bg-yellow-100 text-yellow-600',
                'reservation': 'bg-blue-100 text-blue-600',
                'system': 'bg-gray-100 text-gray-600',
                'warning': 'bg-orange-100 text-orange-600',
                'info': 'bg-green-100 text-green-600'
            };
            return colors[type] || 'bg-gray-100 text-gray-600';
        },

        getTypeIcon(type) {
            const icons = {
                'key_return': 'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z',
                'reservation': 'M8 7V3a4 4 0 118 0v4m-4 6v6m-4-6h8m-8 6h8',
                'system': 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
                'warning': 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z',
                'info': 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
            };
            return icons[type] || 'M15 17h5l-5 5v-5z M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z';
        },

        formatTime(timestamp) {
            const date = new Date(timestamp);
            const now = new Date();
            const diffInMinutes = Math.floor((now - date) / (1000 * 60));
            
            if (diffInMinutes < 1) return 'Ahora mismo';
            if (diffInMinutes < 60) return `Hace ${diffInMinutes} min`;
            if (diffInMinutes < 1440) return `Hace ${Math.floor(diffInMinutes / 60)}h`;
            return `Hace ${Math.floor(diffInMinutes / 1440)}d`;
        }
    }
}
</script> 