<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Acciones Rápidas - Gestor de Aulas IT')</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Perfect Scrollbar -->
    <script src="https://cdn.jsdelivr.net/npm/perfect-scrollbar@1.5.0/dist/perfect-scrollbar.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/perfect-scrollbar@1.5.0/css/perfect-scrollbar.css">
    
    <!-- Admin Panel Scripts -->
    <script src="{{ asset('js/admin-panel.js') }}"></script>
    
    <!-- Styles adicionales -->
    @stack('styles')
    
    <style>
        [x-cloak] {
            display: none !important;
        }
        
        /* Scrollbar personalizado */
        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }
    </style>
</head>
<body class="font-sans antialiased bg-gray-50">
    <div x-data="{
        isSidebarOpen: false,
        isSidebarHovered: false,
        isDarkMode: localStorage.getItem('dark') === 'true',
        
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
        }
    }" x-on:resize.window="handleWindowResize" x-cloak>
        <div class="min-h-screen">
            <!-- Navbar -->
            <div class="fixed top-0 left-0 z-[100] w-full">
                <x-navbar />
            </div>

            <!-- Sidebar -->
            <x-sidebar.sidebar />

            <!-- Main Content Area -->
            <div class="flex flex-col min-h-screen pt-16 transition-all duration-300 ease-in-out bg-gray-100 dark:bg-dark-eval-2"
                 :class="{
                     'opacity-75 pointer-events-none': isSidebarOpen || isSidebarHovered,
                     'opacity-100': !(isSidebarOpen || isSidebarHovered)
                 }">

                <!-- Header -->
                <header>
                    <div class="p-4 mt-4 sm:p-6">
                        <!-- Breadcrumb -->
                        <nav class="flex mb-6" aria-label="Breadcrumb">
                            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                                <li class="inline-flex items-center">
                                    <a href="{{ route('dashboard') }}" 
                                       class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                                        </svg>
                                        Dashboard
                                    </a>
                                </li>
                                <li>
                                    <div class="flex items-center">
                                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Acciones Rápidas</span>
                                    </div>
                                </li>
                            </ol>
                        </nav>
                    </div>
                </header>

                <!-- Main content -->
                <main class="flex-1 px-4 overflow-x-auto transition-all duration-300 ease-in-out sm:px-6">
                    @yield('content')
                </main>

                <!-- Footer -->
                <x-footer />
            </div>
        </div>
    </div>

    <!-- Scripts adicionales -->
    @stack('scripts')
</body>
</html>
