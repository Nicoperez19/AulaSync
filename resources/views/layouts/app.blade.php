<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'AulaSync') }}</title>

    <!-- Estilos de Livewire -->
    @livewireStyles

    <!-- Fuentes -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;300;400;500;700;900&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Estilos adicionales -->
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="font-sans antialiased">
    <div x-data="mainState" :class="{ dark: isDarkMode }" x-on:resize.window="handleWindowResize" x-cloak>
        <div class="min-h-screen text-gray-900 bg-gray-100 dark:bg-dark-eval-0 dark:text-gray-200">
            <!-- Navbar -->
            <div class="fixed top-0 left-0 z-[100] w-full">
                <x-navbar />
            </div>

            <!-- Sidebar -->
            <x-sidebar.sidebar />

            <!-- Contenido principal -->
            <div class="flex flex-col min-h-screen pt-16 bg-gray-100 dark:bg-dark-eval-2 transition-all duration-300 ease-in-out">
                <!-- Header -->
                <header>
                    <div class="p-4 mt-4 sm:p-6">
                        {{ $header }}
                    </div>
                </header>

                <!-- Main content -->
                <main class="flex-1 px-4 overflow-x-auto sm:px-6 transition-all duration-300 ease-in-out"
                    :class="{
                        'opacity-75 pointer-events-none': isSidebarOpen || isSidebarHovered,
                        'opacity-100': !(isSidebarOpen || isSidebarHovered)
                    }">
                    {{ $slot }}
                </main>

                <!-- Footer -->
                <x-footer />
            </div>
        </div>
    </div>

    <!-- Scripts -->
    @livewireScripts

    <!-- SweetAlert Component -->
    <x-sweet-alert />

    <!-- Alpine.js -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('mainState', () => ({
                isDarkMode: localStorage.getItem('dark') === 'true' ||
                    (!localStorage.getItem('dark') && window.matchMedia('(prefers-color-scheme: dark)')
                        .matches),
                isSidebarOpen: false,
                isSidebarHovered: false,
                scrollingDown: false,
                scrollingUp: false,
                lastScrollTop: 0,

                init() {
                    this.handleWindowResize()
                    window.addEventListener('scroll', this.handleScroll.bind(this))
                    window.addEventListener('resize', this.handleWindowResize.bind(this))
                },

                toggleTheme() {
                    this.isDarkMode = !this.isDarkMode
                    localStorage.setItem('dark', this.isDarkMode)
                },

                toggleSidebar() {
                    this.isSidebarOpen = !this.isSidebarOpen
                },

                handleSidebarHover(value) {
                    if (window.innerWidth < 1024) return
                    this.isSidebarHovered = value
                },

                handleWindowResize() {
                    if (window.innerWidth < 1024) {
                        this.isSidebarOpen = false
                    }
                },

                handleScroll() {
                    const st = window.pageYOffset || document.documentElement.scrollTop
                    this.scrollingDown = st > this.lastScrollTop
                    this.scrollingUp = st < this.lastScrollTop
                    if (st === 0) {
                        this.scrollingDown = false
                        this.scrollingUp = false
                    }
                    this.lastScrollTop = st <= 0 ? 0 : st
                }
            }))
        })
    </script>
    @if(auth()->check() && auth()->user()->hasRole('Administrador'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const KeyReturnToast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 6000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            let shownNotifications = new Set();
            const notificationBadge = document.getElementById('notification-badge');
            const notificationList = document.getElementById('notification-list');

            function fetchKeyReturnNotifications() {
                fetch('/api/notifications/key-returns')
                    .then(response => response.json())
                    .then(notifications => {
                        // Update badge
                        if (notifications.length > 0) {
                            notificationBadge.textContent = notifications.length;
                            notificationBadge.style.display = 'block';
                        } else {
                            notificationBadge.style.display = 'none';
                        }

                        // Update notification list
                        if (notificationList) {
                            if (notifications.length > 0) {
                                notificationList.innerHTML = ''; // Clear previous list
                                notifications.forEach(notification => {
                                    const notificationElement = document.createElement('div');
                                    notificationElement.className = 'p-2 border-b text-sm text-gray-700 dark:text-gray-200';
                                    notificationElement.innerHTML = `El profesor <b>${notification.profesor}</b> debe devolver las llaves de <b>${notification.espacio}</b>. (Fin: ${notification.hora_termino})`;
                                    notificationList.appendChild(notificationElement);
                                });
                            } else {
                                notificationList.innerHTML = '<p class="p-4 text-sm text-center text-gray-500">No hay notificaciones</p>';
                            }
                        }

                        // Show toast for new notifications
                        notifications.forEach(notification => {
                            const notificationId = `${notification.profesor}-${notification.espacio}-${notification.hora_termino}`;
                            if (!shownNotifications.has(notificationId)) {
                                KeyReturnToast.fire({
                                    icon: 'warning',
                                    title: 'Devolución de llaves pendiente',
                                    html: `El profesor <b>${notification.profesor}</b> debe devolver las llaves de la sala <b>${notification.espacio}</b>.<br>Su clase finaliza a las <b>${notification.hora_termino}</b>.`
                                });
                                shownNotifications.add(notificationId);
                            }
                        });
                    })
                    .catch(error => console.error('Error fetching key return notifications:', error));
            }

            fetchKeyReturnNotifications();
            setInterval(fetchKeyReturnNotifications, 60000); // Cada 60 segundos
        });
    </script>
    @endif
</body>

</html>
