<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SIA | Sistema de Información de Aulas') }} - Selección de Sede</title>

    <!-- Fuentes -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;300;400;500;700;900&display=swap"
        rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Build Assets -->
    @if (config('app.env') === 'production' && file_exists(public_path('build/manifest.json')))
        @php
            $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
            $cssFile = $manifest['resources/css/app.css']['file'] ?? null;
            $jsFile = $manifest['resources/js/app.js']['file'] ?? null;
            $jsCss = $manifest['resources/js/app.js']['css'][0] ?? null;
        @endphp
        <link rel="stylesheet" href="{{ asset("build/$jsCss") }}" />
        @if ($cssFile)
            <link rel="stylesheet" href="{{ asset("build/$cssFile") }}" />
        @endif
        @if ($jsFile)
            <script type="module" src="{{ asset("build/$jsFile") }}"></script>
        @endif
    @else
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <style>
        .sede-item {
            transition: all 0.2s ease;
        }

        .sede-item:hover {
            background-color: #f3f4f6;
        }
    </style>
</head>

<body class="font-sans min-h-screen bg-gray-50">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <header class="bg-white border-b border-gray-200 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="font-bold text-gray-800 text-base sm:text-lg">SIA</span>
                        <span class="text-gray-500 text-xs sm:text-sm hidden sm:inline">| Sistema de Información de Aulas</span>
                    </div>
                    <div>
                        <a href="{{ route('logout') }}" 
                           class="inline-flex items-center px-3 py-1.5 rounded-md text-xs sm:text-sm font-medium text-gray-600 hover:text-red-600 hover:bg-red-50 transition" 
                           title="Cerrar sesión y volver al login">
                            <i class="fas fa-sign-out-alt mr-1.5"></i>
                            Cerrar Sesión
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-grow container mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="text-center mb-10">
                <h1 class="text-3xl sm:text-4xl font-bold text-gray-800 mb-4 flex items-center justify-center">
                    <span class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-blue-100 text-blue-600 mr-3">
                        <i class="fas fa-building text-2xl"></i>
                    </span>
                    Seleccione su Sede
                </h1>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Elija la sede a la que desea acceder para continuar con su sesión en el Sistema de Información de
                    Aulas (SIA).
                </p>
            </div>

            @if(session('info'))
                <div class="max-w-md mx-auto mb-6">
                    <div class="bg-blue-50 border-l-4 border-blue-500 text-blue-700 p-4 rounded shadow-sm" role="alert">
                        <div class="flex items-center">
                            <i class="fas fa-info-circle mr-2"></i>
                            <p>{{ session('info') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="max-w-md mx-auto mb-8">
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded" role="alert">
                        <p class="font-bold">Error</p>
                        <p>{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            @if($sedes->count() > 0)
                <div class="max-w-2xl mx-auto">
                    <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 divide-y divide-gray-100">
                        @foreach($sedes as $sede)
                            <a href="{{ route('sedes.redirect', $sede->id_sede) }}"
                                class="sede-item block px-6 py-4 hover:no-underline">
                                <div class="flex items-center justify-between">
                                    <div class="flex-grow">
                                        <h2 class="text-lg font-semibold text-gray-800">
                                            {{ $sede->nombre_sede }}
                                        </h2>
                                        @if($sede->universidad)
                                            <p class="text-sm text-gray-600">
                                                Instituto Tecnológico {{ $sede->universidad->nombre_universidad }}
                                            </p>
                                        @endif
                                        @if($sede->comuna)
                                            <p class="text-sm text-gray-500">
                                                <i class="fas fa-map-marker-alt mr-1"></i>{{ $sede->comuna->nombre_comuna }}
                                            </p>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <i class="fas fa-chevron-right text-gray-400 text-xl"></i>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="text-center py-12 max-w-md mx-auto">
                    <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-gray-100 mb-6">
                        <i class="fas fa-building text-4xl text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-600 mb-2">No hay sedes disponibles</h3>
                    <p class="text-gray-500 mb-6">Por favor, contacte al administrador del sistema.</p>
                    <a href="{{ route('logout') }}" 
                       class="inline-flex items-center justify-center px-5 py-2.5 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 hover:text-red-600 transition">
                        <i class="fas fa-arrow-left mr-2 text-gray-400"></i>
                        Volver al Login
                    </a>
                </div>
            @endif
        </main>

        <!-- Footer -->
        <footer class="bg-gray-800 text-white py-6">
            <div class="container mx-auto px-4 text-center">
                <p class="text-gray-400">
                    &copy; {{ date('Y') }} SIA | Sistema de Información de Aulas.
                </p>
            </div>
        </footer>
    </div>
</body>

</html>
