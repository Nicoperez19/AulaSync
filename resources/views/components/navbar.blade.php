<nav aria-label="secondary" x-data="{ open: false }"
    class="sticky top z-50 flex items-center justify-between px-3 py-2 bg-light-cloud-blue sm:px-6 dark:bg-dark-eval-1 shadow-[0_4px_6px_rgba(255,255,255,0.3)]">
    <div class="flex items-center gap-3">

        <!-- Botón Toggle -->
        <x-button type="button" icon-only sr-text="Toggle sidebar" class="bg-cloud-blue-500 dark:bg-dark-eval-1"
            x-on:click="isSidebarOpen = !isSidebarOpen">
            <x-icons.menu-fold-right x-show="!isSidebarOpen" aria-hidden="true" class="w-6 h-6 lg:block" />
            <x-icons.menu-fold-left x-show="isSidebarOpen" aria-hidden="true" class="w-6 h-6 lg:block" />
        </x-button>

        <!-- Logo -->
        <a href="{{ auth()->user()->hasRole('Usuario') ? route('espacios.show') : route('dashboard') }}" class="flex items-center">
            <x-application-logo-navbar />
        </a>
    </div>

    {{-- <div class="flex items-center gap-3">
        <x-button type="button" class="md:hidden" icon-only variant="secondary" sr-text="Toggle dark mode"
            x-on:click="toggleTheme">
            <x-heroicon-o-moon x-show="!isDarkMode" aria-hidden="true" class="w-6 h-6" />
            <x-heroicon-o-sun x-show="isDarkMode" aria-hidden="true" class="w-6 h-6" />
        </x-button>
    </div> --}}

    <div class="flex items-center gap-3">
        {{-- <x-button type="button" class="hidden md:inline-flex " icon-only variant="secondary" sr-text="Toggle dark mode"
            x-on:click="toggleTheme">
            <x-heroicon-o-moon x-show="!isDarkMode" aria-hidden="true" class="w-6 h-6" />
            <x-heroicon-o-sun x-show="isDarkMode" aria-hidden="true" class="w-6 h-6" />
        </x-button> --}}

   

        <x-dropdown align="right" width="48">
            <x-slot name="trigger">
                <button
                    class="flex items-center p-2 text-sm font-medium text-white transition duration-150 ease-in-out rounded-md hover:text-white focus:outline-none focus:ring focus:ring-white focus:ring-offset-1 focus:ring-offset-white dark:focus:ring-offset-dark-eval-1 dark:text-white dark:hover:text-white">
                    <div>{{ Auth::user()->name }}</div>

                    <div class="ml-1">
                        <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                </button>
            </x-slot>

            <x-slot name="content">
                <x-dropdown-link :href="route('profile.edit')">
                    {{ __('Perfil') }}
                </x-dropdown-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Cerrar Sesión') }}
                    </x-dropdown-link>
                </form>
            </x-slot>
        </x-dropdown>
    </div>
</nav>

<div
    class="fixed inset-x-0 bottom-0 flex items-center justify-between px-4 py-4 bg-light-cloud-blue sm:px-6 md:hidden dark:bg-dark-eval-1">
    <x-button type="button" icon-only variant="secondary" sr-text="Search">
        <x-heroicon-o-search aria-hidden="true" class="w-6 h-6" />
    </x-button>

    <a href="{{ auth()->user()->hasRole('Usuario') ? route('espacios.show') : route('dashboard') }}">
        <x-application-logo-navbar-bot aria-hidden="true" class="w-10 h-10" />
        <span class="sr-only">Dashboard</span>
    </a>

    <x-button type="button" icon-only variant="secondary" sr-text="Open main menu"
        x-on:click="isSidebarOpen = !isSidebarOpen">
        <x-heroicon-o-menu x-show="!isSidebarOpen" aria-hidden="true" class="w-6 h-6" />
        <x-heroicon-o-x x-show="isSidebarOpen" aria-hidden="true" class="w-6 h-6" />
    </x-button>
</div>

<script>
    // Horarios de módulos copiados de show.blade.php
    const horariosModulos = {
        lunes: {
            1: { inicio: '08:10:00', fin: '09:00:00' },
            2: { inicio: '09:10:00', fin: '10:00:00' },
            3: { inicio: '10:10:00', fin: '11:00:00' },
            4: { inicio: '11:10:00', fin: '12:00:00' },
            5: { inicio: '12:10:00', fin: '13:00:00' },
            6: { inicio: '13:10:00', fin: '14:00:00' },
            7: { inicio: '14:10:00', fin: '15:00:00' },
            8: { inicio: '15:10:00', fin: '16:00:00' },
            9: { inicio: '16:10:00', fin: '17:00:00' },
            10: { inicio: '17:10:00', fin: '18:00:00' },
            11: { inicio: '18:10:00', fin: '19:00:00' },
            12: { inicio: '19:10:00', fin: '20:00:00' },
            13: { inicio: '20:10:00', fin: '21:00:00' },
            14: { inicio: '21:10:00', fin: '22:00:00' },
            15: { inicio: '22:10:00', fin: '23:00:00' }
        },
        martes: {
            1: { inicio: '08:10:00', fin: '09:00:00' },
            2: { inicio: '09:10:00', fin: '10:00:00' },
            3: { inicio: '10:10:00', fin: '11:00:00' },
            4: { inicio: '11:10:00', fin: '12:00:00' },
            5: { inicio: '12:10:00', fin: '13:00:00' },
            6: { inicio: '13:10:00', fin: '14:00:00' },
            7: { inicio: '14:10:00', fin: '15:00:00' },
            8: { inicio: '15:10:00', fin: '16:00:00' },
            9: { inicio: '16:10:00', fin: '17:00:00' },
            10: { inicio: '17:10:00', fin: '18:00:00' },
            11: { inicio: '18:10:00', fin: '19:00:00' },
            12: { inicio: '19:10:00', fin: '20:00:00' },
            13: { inicio: '20:10:00', fin: '21:00:00' },
            14: { inicio: '21:10:00', fin: '22:00:00' },
            15: { inicio: '22:10:00', fin: '23:00:00' }
        },
        miercoles: {
            1: { inicio: '08:10:00', fin: '09:00:00' },
            2: { inicio: '09:10:00', fin: '10:00:00' },
            3: { inicio: '10:10:00', fin: '11:00:00' },
            4: { inicio: '11:10:00', fin: '12:00:00' },
            5: { inicio: '12:10:00', fin: '13:00:00' },
            6: { inicio: '13:10:00', fin: '14:00:00' },
            7: { inicio: '14:10:00', fin: '15:00:00' },
            8: { inicio: '15:10:00', fin: '16:00:00' },
            9: { inicio: '16:10:00', fin: '17:00:00' },
            10: { inicio: '17:10:00', fin: '18:00:00' },
            11: { inicio: '18:10:00', fin: '19:00:00' },
            12: { inicio: '19:10:00', fin: '20:00:00' },
            13: { inicio: '20:10:00', fin: '21:00:00' },
            14: { inicio: '21:10:00', fin: '22:00:00' },
            15: { inicio: '22:10:00', fin: '23:00:00' }
        },
        jueves: {
            1: { inicio: '08:10:00', fin: '09:00:00' },
            2: { inicio: '09:10:00', fin: '10:00:00' },
            3: { inicio: '10:10:00', fin: '11:00:00' },
            4: { inicio: '11:10:00', fin: '12:00:00' },
            5: { inicio: '12:10:00', fin: '13:00:00' },
            6: { inicio: '13:10:00', fin: '14:00:00' },
            7: { inicio: '14:10:00', fin: '15:00:00' },
            8: { inicio: '15:10:00', fin: '16:00:00' },
            9: { inicio: '16:10:00', fin: '17:00:00' },
            10: { inicio: '17:10:00', fin: '18:00:00' },
            11: { inicio: '18:10:00', fin: '19:00:00' },
            12: { inicio: '19:10:00', fin: '20:00:00' },
            13: { inicio: '20:10:00', fin: '21:00:00' },
            14: { inicio: '21:10:00', fin: '22:00:00' },
            15: { inicio: '22:10:00', fin: '23:00:00' }
        },
        viernes: {
            1: { inicio: '08:10:00', fin: '09:00:00' },
            2: { inicio: '09:10:00', fin: '10:00:00' },
            3: { inicio: '10:10:00', fin: '11:00:00' },
            4: { inicio: '11:10:00', fin: '12:00:00' },
            5: { inicio: '12:10:00', fin: '13:00:00' },
            6: { inicio: '13:10:00', fin: '14:00:00' },
            7: { inicio: '14:10:00', fin: '15:00:00' },
            8: { inicio: '15:10:00', fin: '16:00:00' },
            9: { inicio: '16:10:00', fin: '17:00:00' },
            10: { inicio: '17:10:00', fin: '18:00:00' },
            11: { inicio: '18:10:00', fin: '19:00:00' },
            12: { inicio: '19:10:00', fin: '20:00:00' },
            13: { inicio: '20:10:00', fin: '21:00:00' },
            14: { inicio: '21:10:00', fin: '22:00:00' },
            15: { inicio: '22:10:00', fin: '23:00:00' }
        }
    };

    // Variables para el sistema de notificaciones
    let notificationCheckInterval;
    let lastNotifiedModules = new Set();
    let notificationCount = 0;

    // Función para obtener el día actual
    function obtenerDiaActual() {
        const dias = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
        return dias[new Date().getDay()];
    }

    // Función para formatear hora
    function formatearHora(horaCompleta) {
        return horaCompleta.slice(0, 5);
    }

    // Función para obtener el módulo actual
    function determinarModulo(horaActual) {
        const diaActual = obtenerDiaActual();
        if (!horariosModulos[diaActual]) return null;

        for (let modulo = 1; modulo <= 15; modulo++) {
            const horario = horariosModulos[diaActual][modulo];
            if (horario && horaActual >= horario.inicio && horaActual <= horario.fin) {
                return modulo;
            }
        }
        return null;
    }

    // Función para obtener el próximo módulo
    function obtenerProximoModulo() {
        const diaActual = obtenerDiaActual();
        if (!horariosModulos[diaActual]) return null;

        const ahora = new Date();
        const horaActual = ahora.toLocaleTimeString('es-ES', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });

        for (let modulo = 1; modulo <= 15; modulo++) {
            const horario = horariosModulos[diaActual][modulo];
            if (horario && horaActual < horario.inicio) {
                return { modulo, horario };
            }
        }
        return null;
    }

    // Función para crear una notificación
    function crearNotificacion(mensaje, tipo = 'info') {
        const notificationList = document.getElementById('notification-list');
        const notificationBadge = document.getElementById('notification-badge');
        
        if (!notificationList || !notificationBadge) return;

        // Crear elemento de notificación
        const notification = document.createElement('div');
        notification.className = `p-3 rounded-lg border-l-4 ${
            tipo === 'warning' ? 'bg-yellow-50 border-yellow-400 text-yellow-800' :
            tipo === 'info' ? 'bg-blue-50 border-blue-400 text-blue-800' :
            'bg-gray-50 border-gray-400 text-gray-800'
        }`;
        
        notification.innerHTML = `
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <i class="fas ${tipo === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle'}"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium">${mensaje}</p>
                    <p class="text-xs mt-1">${new Date().toLocaleTimeString('es-ES')}</p>
                </div>
            </div>
        `;

        // Agregar al inicio de la lista
        notificationList.insertBefore(notification, notificationList.firstChild);

        // Actualizar contador
        notificationCount++;
        notificationBadge.textContent = notificationCount;
        notificationBadge.style.display = 'block';

        // Limitar a 10 notificaciones
        const notifications = notificationList.querySelectorAll('div');
        if (notifications.length > 10) {
            notificationList.removeChild(notifications[notifications.length - 1]);
        }

        // Mostrar notificación del navegador si está permitido
        if (Notification.permission === 'granted') {
            new Notification('AulaSync - Notificación', {
                body: mensaje,
                icon: '/favicon.ico',
                tag: 'modulo-notification'
            });
        }

        // Reproducir sonido para notificaciones importantes
        if (tipo === 'warning') {
            reproducirSonidoNotificacion();
        }
    }

    // Función para reproducir sonido de notificación
    function reproducirSonidoNotificacion() {
        try {
            // Crear un audio context para generar un beep simple
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            
            oscillator.frequency.setValueAtTime(800, audioContext.currentTime);
            oscillator.frequency.setValueAtTime(600, audioContext.currentTime + 0.1);
            
            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.2);
            
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.2);
        } catch (error) {
            console.log('No se pudo reproducir sonido de notificación');
        }
    }

    // Función para solicitar permisos de notificación
    function solicitarPermisosNotificacion() {
        if (Notification.permission === 'default') {
            Notification.requestPermission().then(permission => {
                if (permission === 'granted') {
                    console.log('Permisos de notificación concedidos');
                }
            });
        }
    }

    // Función para limpiar todas las notificaciones
    function limpiarNotificaciones() {
        const notificationList = document.getElementById('notification-list');
        const notificationBadge = document.getElementById('notification-badge');
        
        if (notificationList) {
            notificationList.innerHTML = '<p class="p-4 text-sm text-center text-gray-500">No hay notificaciones</p>';
        }
        
        if (notificationBadge) {
            notificationCount = 0;
            notificationBadge.style.display = 'none';
        }
    }

    // Función para marcar notificación como leída
    function marcarComoLeida(notificationElement) {
        notificationElement.style.opacity = '0.6';
        notificationElement.style.backgroundColor = '#f3f4f6';
    }

    // Función para verificar módulos próximos
    function verificarModulosProximos() {
        const proximoModulo = obtenerProximoModulo();
        if (!proximoModulo) return;

        const { modulo, horario } = proximoModulo;
        const ahora = new Date();
        const horaActual = ahora.toLocaleTimeString('es-ES', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });

        // Calcular minutos hasta el inicio del módulo
        const [horaInicioHora, horaInicioMin] = horario.inicio.split(':').map(Number);
        const [horaActualHora, horaActualMin] = horaActual.split(':').map(Number);
        
        const minutosHastaInicio = (horaInicioHora * 60 + horaInicioMin) - (horaActualHora * 60 + horaActualMin);

        // Crear clave única para este módulo
        const moduloKey = `${obtenerDiaActual()}_${modulo}`;

        // Notificar si faltan 5 minutos y no se ha notificado antes
        if (minutosHastaInicio <= 5 && minutosHastaInicio > 0 && !lastNotifiedModules.has(moduloKey)) {
            const mensaje = `Va a comenzar el módulo ${modulo} en ${minutosHastaInicio} minutos (${formatearHora(horario.inicio)} - ${formatearHora(horario.fin)})`;
            crearNotificacion(mensaje, 'warning');
            lastNotifiedModules.add(moduloKey);
        }

        // Notificar si faltan 1 minuto
        if (minutosHastaInicio <= 1 && minutosHastaInicio > 0 && !lastNotifiedModules.has(`${moduloKey}_1min`)) {
            const mensaje = `¡El módulo ${modulo} comenzará en 1 minuto!`;
            crearNotificacion(mensaje, 'warning');
            lastNotifiedModules.add(`${moduloKey}_1min`);
        }
    }

    // Función para limpiar notificaciones antiguas al cambiar de día
    function limpiarNotificacionesAntiguas() {
        const hoy = new Date().toDateString();
        if (window.lastNotificationDate !== hoy) {
            lastNotifiedModules.clear();
            window.lastNotificationDate = hoy;
        }
    }

    // Inicializar sistema de notificaciones
    document.addEventListener('DOMContentLoaded', function() {
        // Solicitar permisos de notificación
        solicitarPermisosNotificacion();
        
        // Verificar cada minuto
        notificationCheckInterval = setInterval(() => {
            limpiarNotificacionesAntiguas();
            verificarModulosProximos();
        }, 60000); // 60 segundos

        // Verificar inmediatamente al cargar
        verificarModulosProximos();
    });

    // Limpiar intervalo cuando se desmonte
    window.addEventListener('beforeunload', function() {
        if (notificationCheckInterval) {
            clearInterval(notificationCheckInterval);
        }
    });
</script>
