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

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Quill Editor -->
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>

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
    <div x-data="mainState" x-on:resize.window="handleWindowResize" x-cloak>
        <div class="min-h-screen">
            <!-- Navbar -->
            <div class="fixed top-0 left-0 z-[100] w-full">
                <x-navbar />
            </div>

            <!-- Sidebar -->
            <x-sidebar.sidebar />

            <!-- Contenido principal -->
            <div class="flex flex-col min-h-screen pt-16 transition-all duration-300 ease-in-out bg-gray-100 dark:bg-dark-eval-2">
                
                <!-- Main content -->
                <main class="flex-1 px-4 overflow-x-auto transition-all duration-300 ease-in-out sm:px-6 mt-4" :class="{
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

    <!-- Scripts adicionales -->
    <script>
        // Función para mostrar SweetAlert cuando no hay mapas
        function mostrarSweetAlertNoMapas(event) {
            event.preventDefault();
            Swal.fire({
                title: 'No hay mapas disponibles',
                html: `
                    <div class="text-center">
                        <p class="mb-4">No se han encontrado mapas digitales en el sistema.</p>
                        <p class="text-sm text-gray-600">Por favor pongase en contacto con el administrador del sistema.</p>
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
                        <p class="mb-4">No se han encontrado profesores en el sistema.</p>
                        <p class="text-sm text-gray-600">Por favor, contacte al administrador para cargar los datos de profesores.</p>
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
                        <p class="mb-4">No se han encontrado espacios en el sistema.</p>
                        <p class="text-sm text-gray-600">Por favor, contacte al administrador para cargar los espacios.</p>
                    </div>
                `,
                icon: 'warning',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#3B82F6'
            });
        }

        document.addEventListener('alpine:init', () => {
            Alpine.data('mainState', () => ({
                isSidebarOpen: window.innerWidth > 1024,
                isSidebarHovered: false,
                handleSidebarHover(hovered) {
                    if (window.innerWidth > 1024) {
                        this.isSidebarHovered = hovered;
                    }
                },
                handleWindowResize() {
                    if (window.innerWidth > 1024) {
                        this.isSidebarOpen = true;
                    } else {
                        this.isSidebarOpen = false;
                    }
                }
            }));
        });
    </script>
</body>

</html>
