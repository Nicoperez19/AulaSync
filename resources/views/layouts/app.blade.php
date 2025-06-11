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
            <!-- Navbar -->
            <div class="fixed top-0 left-0 z-[100] w-full">
                <x-navbar />
            </div>

            <!-- Sidebar -->
            <x-sidebar.sidebar />

            <!-- Contenido principal -->
            <div class="flex flex-col min-h-screen pt-16 bg-cloud-light dark:bg-dark-eval-2 transition-all duration-300 ease-in-out">
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
</body>

</html>
