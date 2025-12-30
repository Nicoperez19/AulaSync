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

    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Scripts de Admin Panel para funciones de acciones rápidas -->
    <script src="{{ asset('js/admin-panel.js') }}"></script>

    <!-- Estilos adicionales -->
    <style>
        [x-cloak] {
            display: none !important;
        }
        
        /* Estilos para autocompletado */
        #autocomplete-results {
            z-index: 1000;
            top: 100%;
            left: 0;
            right: 0;
        }
        
        #autocomplete-results .hover\:bg-gray-100:hover {
            background-color: #f3f4f6;
        }
        
        /* Animación suave para el autocompletado */
        #autocomplete-results {
            transition: opacity 0.2s ease-in-out;
        }
        
        #autocomplete-results.hidden {
            opacity: 0;
            pointer-events: none;
        }
        
        #autocomplete-results:not(.hidden) {
            opacity: 1;
            pointer-events: auto;
        }
    </style>
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
            <div class="flex flex-col min-h-screen pt-16 transition-all duration-300 ease-in-out bg-gray-100 dark:bg-dark-eval-2">
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

    <!-- Scripts de Livewire -->
    @livewireScripts

    <!-- Override de funciones para Quick Actions (debe ir después de admin-panel.js) -->
    <script>
        // Esperamos a que el DOM esté listo y admin-panel.js se haya cargado
        document.addEventListener('DOMContentLoaded', function() {
            // Configurar token CSRF para fetch
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            console.log('🔧 Aplicando overrides de Quick Actions...');
            
            // Override de la función cargarEspaciosDisponibles
            window.cargarEspaciosDisponibles = async function() {
                console.log('🔄 Cargando espacios disponibles (Quick Actions Override)...');
                
                try {
                    const response = await fetch('/quick-actions/api/espacios', {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    });
                    
                    console.log('📡 Response status:', response.status);
                    
                    if (!response.ok) {
                        const errorText = await response.text();
                        console.error('❌ Response error:', errorText);
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    const data = await response.json();
                    console.log('📦 Data recibida:', data);
                    
                    const select = document.getElementById('espacio-reserva');
                    if (select) {
                        if (data.success && data.espacios && data.espacios.length > 0) {
                            select.innerHTML = '<option value="">Seleccione un espacio</option>';
                            
                            data.espacios.forEach(espacio => {
                                const option = document.createElement('option');
                                option.value = espacio.codigo;
                                option.textContent = `${espacio.codigo} - ${espacio.nombre} (Piso ${espacio.piso})`;
                                select.appendChild(option);
                            });
                            
                            window.espaciosDisponibles = data.espacios;
                            console.log(`✅ Cargados ${data.espacios.length} espacios`);
                        } else {
                            select.innerHTML = '<option value="">No hay espacios disponibles</option>';
                            console.warn('⚠️ No se encontraron espacios:', data);
                        }
                    } else {
                        console.error('❌ No se encontró el elemento select con ID: espacio-reserva');
                    }
                } catch (error) {
                    console.error('❌ Error al cargar espacios:', error);
                    const select = document.getElementById('espacio-reserva');
                    if (select) {
                        select.innerHTML = '<option value="">Error de conexión</option>';
                    }
                    
                    // Mostrar alerta al usuario
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de conexión',
                            text: 'No se pudieron cargar los espacios. Verifique su conexión.',
                            timer: 3000
                        });
                    }
                }
            };
            
            console.log('✅ Overrides de Quick Actions aplicados');
        });
    </script>

    <!-- Scripts adicionales -->
    @stack('scripts')
</body>
</html>
