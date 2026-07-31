<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SIA | Sistema de Información de Aulas') }}</title>

    <!-- Livewire & Fuentes -->
    @livewireStyles
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;300;400;500;700;900&display=swap" rel="stylesheet">

    <!-- Librerías Externas & Chart.js -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Estilos y JS compilados por Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    @stack('styles')
</head>

<body class="font-sans antialiased">
    <div x-data="mainState" x-on:resize.window="handleWindowResize" x-cloak>
        <div class="min-h-screen">
            <!-- Barra de Navegación -->
            <div class="fixed top-0 left-0 z-[100] w-full">
                <x-navbar />
            </div>

            <!-- Menú Lateral Sidebar con Animación Fluida -->
            <x-sidebar.sidebar />

            <!-- Contenido Principal -->
            <div class="flex flex-col min-h-screen pt-16 transition-all duration-300 ease-in-out bg-gray-100 dark:bg-dark-eval-2">
                @if (isset($header))
                    <header>
                        <div class="p-4 mt-4 sm:p-6">
                            {{ $header }}
                        </div>
                    </header>
                @endif

                <main class="flex-1 px-4 overflow-x-auto transition-all duration-300 ease-in-out sm:px-6" :class="{
                        'opacity-75 pointer-events-none': isSidebarOpen || isSidebarHovered,
                        'opacity-100': !(isSidebarOpen || isSidebarHovered)
                    }">
                    {{ $slot }}
                </main>

                <x-footer />
            </div>
        </div>
    </div>

    <!-- Componentes de Livewire y SweetAlert -->
    @livewireScripts
    <x-sweet-alert />

    <!-- Scripts Adicionales inyectados desde vistas hijas -->
    @stack('scripts')
</body>

</html>
