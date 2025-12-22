<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Gestor de Aulas') }} - Selección de Sede</title>

    <!-- Fuentes -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;300;400;500;700;900&display=swap" rel="stylesheet">

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
<body class="font-sans min-h-screen">
    <div class="min-h-screen flex flex-col">
        <!-- Header
        <header class="shadow-md">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex items-center justify-center">
                    <img src="" alt="AulaSync" class="h-12 sm:h-16">
                </div>
            </div>
        </header>
-->
        <!-- Main Content -->
        <main class="flex-grow container mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="text-center mb-12">
                <h1 class="text-3xl sm:text-4xl font-bold text-gray-800 mb-4">
                    <i class="fas fa-building mr-3 text-blue-600"></i>
                    Seleccione su Sede
                </h1>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Elija la sede a la que desea acceder para continuar con su sesión en el sistema Gestor de Aulas.
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
                <div class="max-w-2xl mx-auto">
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        @foreach($sedes as $sede)
                            <a href="{{ route('sedes.redirect', $sede->id_sede) }}" class="sede-item block border-b last:border-b-0 px-6 py-4 hover:no-underline">
                                <div class="flex items-center justify-between">
                                    <div class="flex-grow">
                                        <h2 class="text-lg font-semibold text-gray-800">
                                            {{ $sede->nombre_sede }}
                                        </h2>
                                        @if($sede->universidad)
                                            <p class="text-sm text-gray-600">
                                                Instituto Tecnólogico {{ $sede->universidad->nombre_universidad }}
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
                    &copy; {{ date('Y') }} Gestor de Aulas.
                </p>
            </div>
        </footer>
    </div>
</body>
</html>
