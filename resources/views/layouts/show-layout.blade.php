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
            <!-- Contenido principal -->
            <div class="flex flex-col min-h-screen bg-cloud-light dark:bg-dark-eval-2 transition-all duration-300 ease-in-out">


                <!-- Main content -->
                <main class="flex-1 px-4 overflow-x-auto sm:px-6 transition-all duration-300 ease-in-out">
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

                init() {
                    this.handleWindowResize()
                    window.addEventListener('resize', this.handleWindowResize.bind(this))
                },

                toggleTheme() {
                    this.isDarkMode = !this.isDarkMode
                    localStorage.setItem('dark', this.isDarkMode)
                },

                handleWindowResize() {
                    // Manejo de redimensionamiento si es necesario
                }
            }))
        })
    </script>
</body>

</html> 