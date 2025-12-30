<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Gestor de Aulas IT') }}</title>

    <!-- Estilos de Livewire -->
    @livewireStyles

    <!-- Fuentes -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;300;400;500;700;900&display=swap"
        rel="stylesheet">

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

    <!-- Custom Styles -->
    @stack('styles')
</head>

<body class="font-sans antialiased">
    <div x-data="mainState" x-on:resize.window="handleWindowResize" x-cloak>
        <div class="min-h-screen ">
            <!-- Navbar -->
            <div class="fixed top-0 left-0 z-[100] w-full">
                <x-navbar />
            </div>

            <!-- Sidebar -->
            <x-sidebar.sidebar />

            <!-- Contenido principal -->
            <div
                class="flex flex-col min-h-screen pt-16 transition-all duration-300 ease-in-out bg-gray-100 dark:bg-dark-eval-2">
                <!-- Header -->
                <header>
                    <div class="p-4 mt-4 sm:p-6">
                        {{ $header }}
                    </div>
                </header>

                <!-- Main content -->
                <main class="flex-1 px-4 overflow-x-auto transition-all duration-300 ease-in-out sm:px-6" :class="{
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
        // Función para mostrar SweetAlert cuando no hay mapas
        function mostrarSweetAlertNoMapas(event) {
            event.preventDefault();
            Swal.fire({
                title: 'No hay mapas disponibles',
                html: `
                    <div class="text-center">
                        <p class="mb-4">No se han encontrado mapas digitales en el sistema.</p>
                        <p class="text-sm text-gray-600">Hay que contactarse con el administrador para generar los mapas.</p>
                    </div>
                `,
                icon: 'warning',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#3B82F6'
            });
        }

        // Función para mostrar SweetAlert cuando no hay profesores
        function mostrarSweetAlertNoProfesores(event) {
            event.preventDefault();
            Swal.fire({
                title: 'No hay profesores disponibles',
                html: `
                    <div class="text-center">
                        <p class="mb-4">No se han cargado datos de profesores en el sistema.</p>
                        <p class="text-sm text-gray-600">Hay que contactarse con el administrador para cargar los datos de profesores.</p>
                    </div>
                `,
                icon: 'warning',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#3B82F6'
            });
        }

        // Función para mostrar SweetAlert cuando no hay espacios
        function mostrarSweetAlertNoEspacios(event) {
            event.preventDefault();
            Swal.fire({
                title: 'No hay espacios disponibles',
                html: `
                    <div class="text-center">
                        <p class="mb-4">No se han encontrado espacios registrados en el sistema.</p>
                        <p class="text-sm text-gray-600">Hay que contactarse con el administrador para registrar los espacios.</p>
                    </div>
                `,
                icon: 'warning',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#3B82F6'
            });
        }

        // Interceptor global para manejar expiración de sesión en peticiones AJAX
        document.addEventListener('DOMContentLoaded', function () {
            // Guardar la URL actual en localStorage para recuperarla después del login
            if (!localStorage.getItem('intended_url')) {
                localStorage.setItem('intended_url', window.location.href);
            }

            // Interceptar todas las peticiones fetch
            const originalFetch = window.fetch;
            window.fetch = function (...args) {
                return originalFetch.apply(this, args).then(response => {
                    // Si la respuesta es 401 (Unauthorized), verificar si es por expiración de sesión
                    if (response.status === 401) {
                        return response.clone().json().then(data => {
                            if (data.error === 'session_expired') {
                                // Guardar la URL actual antes de redirigir
                                localStorage.setItem('intended_url', window.location.href);

                                // Mostrar mensaje y redirigir al login
                                Swal.fire({
                                    title: 'Sesión Expirada',
                                    text: data.message || 'Tu sesión ha expirado por inactividad.',
                                    icon: 'warning',
                                    confirmButtonText: 'Ir al Login',
                                    allowOutsideClick: false,
                                    allowEscapeKey: false
                                }).then(() => {
                                    window.location.href = data.redirect || '/login';
                                });
                                return Promise.reject(new Error('Session expired'));
                            }
                            return response;
                        }).catch(() => {
                            // Si no es JSON, verificar si es una redirección de sesión expirada
                            if (response.headers.get('location') && response.headers.get('location').includes('login')) {
                                // Guardar la URL actual antes de redirigir
                                localStorage.setItem('intended_url', window.location.href);

                                Swal.fire({
                                    title: 'Sesión Expirada',
                                    text: 'Tu sesión ha expirado por inactividad.',
                                    icon: 'warning',
                                    confirmButtonText: 'Ir al Login',
                                    allowOutsideClick: false,
                                    allowEscapeKey: false
                                }).then(() => {
                                    window.location.href = '/login';
                                });
                                return Promise.reject(new Error('Session expired'));
                            }
                            return response;
                        });
                    }
                    return response;
                });
            };

            // Interceptar peticiones XMLHttpRequest (para compatibilidad)
            const originalXHROpen = XMLHttpRequest.prototype.open;
            const originalXHRSend = XMLHttpRequest.prototype.send;

            XMLHttpRequest.prototype.open = function (method, url, async, user, password) {
                this._url = url;
                return originalXHROpen.apply(this, arguments);
            };

            XMLHttpRequest.prototype.send = function (data) {
                const xhr = this;
                const originalOnReadyStateChange = xhr.onreadystatechange;

                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4 && xhr.status === 401) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.error === 'session_expired') {
                                // Guardar la URL actual antes de redirigir
                                localStorage.setItem('intended_url', window.location.href);

                                Swal.fire({
                                    title: 'Sesión Expirada',
                                    text: response.message || 'Tu sesión ha expirado por inactividad.',
                                    icon: 'warning',
                                    confirmButtonText: 'Ir al Login',
                                    allowOutsideClick: false,
                                    allowEscapeKey: false
                                }).then(() => {
                                    window.location.href = response.redirect || '/login';
                                });
                                return;
                            }
                        } catch (e) {
                            // Si no es JSON válido, verificar si es redirección
                            if (xhr.responseText.includes('login') || xhr.getResponseHeader('location')?.includes('login')) {
                                // Guardar la URL actual antes de redirigir
                                localStorage.setItem('intended_url', window.location.href);

                                Swal.fire({
                                    title: 'Sesión Expirada',
                                    text: 'Tu sesión ha expirado por inactividad.',
                                    icon: 'warning',
                                    confirmButtonText: 'Ir al Login',
                                    allowOutsideClick: false,
                                    allowEscapeKey: false
                                }).then(() => {
                                    window.location.href = '/login';
                                });
                                return;
                            }
                        }
                    }

                    if (originalOnReadyStateChange) {
                        originalOnReadyStateChange.apply(xhr, arguments);
                    }
                };

                return originalXHRSend.apply(this, arguments);
            };
        });

        // Alpine.js se carga a través de Vite en app.js
    </script>

    <!-- Custom Scripts -->
    @stack('scripts')
</body>

</html>