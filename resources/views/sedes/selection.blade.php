<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'AulaSync') }} - Selección de Sede</title>

    <!-- Fuentes -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;300;400;500;700;900&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .sede-card {
            transition: all 0.3s ease;
        }
        .sede-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        }
        .sede-logo {
            width: 120px;
            height: 120px;
            object-fit: contain;
        }
    </style>
</head>
<body class="font-sans antialiased bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <header class="bg-light-cloud-blue shadow-md">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex items-center justify-center">
                    <img src="{{ asset('images/aulasync-logo.png') }}" alt="AulaSync" class="h-12 sm:h-16">
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-grow container mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="text-center mb-12">
                <h1 class="text-3xl sm:text-4xl font-bold text-gray-800 mb-4">
                    <i class="fas fa-building mr-3 text-blue-600"></i>
                    Seleccione su Sede
                </h1>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Elija la sede a la que desea acceder para continuar con su sesión en el sistema AulaSync.
                </p>
            </div>

            @if(session('error'))
                <div class="max-w-md mx-auto mb-8">
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded" role="alert">
                        <p class="font-bold">Error</p>
                        <p>{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            @if($sedes->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-6xl mx-auto">
                    @foreach($sedes as $sede)
                        <a href="{{ route('sedes.redirect', $sede->id_sede) }}" class="sede-card block bg-white rounded-xl shadow-lg overflow-hidden">
                            <div class="p-6">
                                <!-- Logo de la sede -->
                                <div class="flex justify-center mb-4">
                                    @if($sede->logo)
                                        <img src="{{ asset('storage/sedes/logos/' . $sede->logo) }}" 
                                             alt="{{ $sede->nombre_sede }}" 
                                             class="sede-logo rounded-lg">
                                    @else
                                        <div class="sede-logo bg-gray-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-building text-4xl text-gray-400"></i>
                                        </div>
                                    @endif
                                </div>

                                <!-- Información de la sede -->
                                <h2 class="text-xl font-bold text-gray-800 text-center mb-2">
                                    {{ $sede->nombre_sede }}
                                </h2>

                                @if($sede->universidad)
                                    <p class="text-sm text-gray-500 text-center mb-3">
                                        {{ $sede->universidad->nombre_universidad }}
                                    </p>
                                @endif

                                @if($sede->comuna)
                                    <div class="flex items-center justify-center text-gray-500 text-sm">
                                        <i class="fas fa-map-marker-alt mr-2"></i>
                                        <span>{{ $sede->comuna->nombre_comuna }}</span>
                                    </div>
                                @endif

                                <!-- Estado del tenant -->
                                <div class="mt-4 flex justify-center">
                                    @if($sede->tenant && $sede->tenant->is_active)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Disponible
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-600">
                                            <i class="fas fa-minus-circle mr-1"></i>
                                            No disponible
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Botón de acceso -->
                            <div class="bg-blue-600 px-6 py-4 text-center">
                                <span class="text-white font-semibold">
                                    <i class="fas fa-arrow-right mr-2"></i>
                                    Acceder a esta sede
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-gray-100 mb-6">
                        <i class="fas fa-building text-4xl text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-600 mb-2">No hay sedes disponibles</h3>
                    <p class="text-gray-500">Por favor, contacte al administrador del sistema.</p>
                </div>
            @endif
        </main>

        <!-- Footer -->
        <footer class="bg-gray-800 text-white py-6">
            <div class="container mx-auto px-4 text-center">
                <p class="text-gray-400">
                    &copy; {{ date('Y') }} AulaSync - Sistema de Gestión de Espacios Académicos
                </p>
            </div>
        </footer>
    </div>
</body>
</html>
