<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SIA') }} - Selección de Sede</title>

    <!-- Fuentes institucionales -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;300;400;500;700;900&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Build Assets Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-[#F1EFF2] dark:bg-[#191919] min-h-screen flex flex-col justify-between text-gray-800 dark:text-gray-200">
    <!-- Navbar Institucional Rojo UCSC (solo color y botón de cerrar sesión) -->
    <nav class="shrink-0 flex items-center justify-end px-4 sm:px-8 py-3 bg-[#D2091E] shadow-[0_4px_6px_rgba(255,255,255,0.3)]">
        <a href="{{ route('logout') }}" 
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
           class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-semibold text-white bg-black/20 hover:bg-black/35 border border-white/20 transition-all duration-200 shadow-sm cursor-pointer" 
           title="Cerrar sesión">
            <i class="fas fa-sign-out-alt text-xs"></i>
            <span>Cerrar Sesión</span>
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
            @csrf
        </form>
    </nav>

    <!-- Main Content Adaptado y Centrado -->
    <main class="flex-1 flex flex-col justify-center container mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10 max-w-5xl">
        <!-- Encabezado de Selección con presencia y proporciones adecuadas -->
        <div class="text-center mb-6 sm:mb-8 shrink-0">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-[#950101]/10 text-[#950101] mb-3 shadow-xs">
                <i class="fas fa-building text-2xl"></i>
            </div>
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-gray-900 tracking-tight dark:text-white">
                Seleccione su Sede
            </h1>
            <p class="text-sm sm:text-base text-gray-600 max-w-xl mx-auto mt-2 dark:text-gray-400">
                Elija la sede a la que desea acceder para continuar con su sesión en la plataforma <strong class="text-[#950101] font-semibold dark:text-red-400">SIA</strong>.
            </p>
        </div>

        <!-- Notificaciones de sesión -->
        @if(session('info'))
            <div class="max-w-4xl mx-auto w-full mb-4 shrink-0">
                <div class="bg-blue-50 border-l-4 border-blue-600 text-blue-800 p-3.5 rounded-xl shadow-xs dark:bg-blue-900/30 dark:border-blue-500 dark:text-blue-200" role="alert">
                    <div class="flex items-center">
                        <i class="fas fa-info-circle mr-2 text-blue-600 dark:text-blue-400 text-sm"></i>
                        <p class="text-xs sm:text-sm font-medium">{{ session('info') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="max-w-4xl mx-auto w-full mb-4 shrink-0">
                <div class="bg-red-50 border-l-4 border-red-600 text-red-800 p-3.5 rounded-xl shadow-xs dark:bg-red-900/30 dark:border-red-500 dark:text-red-200" role="alert">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-2 text-red-600 dark:text-red-400 text-sm"></i>
                        <div>
                            <p class="font-bold text-xs sm:text-sm">Error</p>
                            <p class="text-xs sm:text-sm">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Grid de Sedes: 2 columnas en desktop para aprovechar el espacio armoniosamente sin provocar scroll -->
        @if($sedes->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5 sm:gap-4 max-w-4xl mx-auto w-full">
                @foreach($sedes as $sede)
                    <a href="{{ route('sedes.redirect', $sede->id_sede) }}"
                        class="group relative flex items-center justify-between p-4 sm:p-4.5 bg-white rounded-2xl border border-gray-200/90 shadow-xs hover:shadow-lg hover:border-[#950101] hover:-translate-y-0.5 transition-all duration-200 dark:bg-gray-800 dark:border-gray-700 dark:hover:border-red-500 cursor-pointer @if($loop->last && $loop->odd) md:col-span-2 md:w-[calc(50%-0.5rem)] md:mx-auto @endif">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <!-- Icono institucional sede -->
                            <div class="w-11 h-11 rounded-xl bg-red-50 group-hover:bg-[#950101] flex items-center justify-center text-[#950101] group-hover:text-white transition-all duration-200 shrink-0 shadow-2xs dark:bg-gray-700 dark:text-red-400 dark:group-hover:bg-[#950101] dark:group-hover:text-white">
                                <i class="fas fa-university text-lg"></i>
                            </div>
                            
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <h2 class="text-base font-bold text-gray-900 group-hover:text-[#950101] transition-colors dark:text-white dark:group-hover:text-red-400 truncate">
                                        {{ $sede->nombre_sede }}
                                    </h2>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-gray-100 text-gray-600 group-hover:bg-red-100 group-hover:text-red-800 transition-colors dark:bg-gray-700 dark:text-gray-300 dark:group-hover:bg-red-900/50 dark:group-hover:text-red-300 shrink-0">
                                        {{ $sede->id_sede }}
                                    </span>
                                </div>

                                @if($sede->universidad)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate mt-0.5">
                                        Instituto Tecnológico {{ $sede->universidad->nombre_universidad }}
                                    </p>
                                @endif

                                @if($sede->comuna)
                                    <p class="text-xs text-gray-400 dark:text-gray-500 flex items-center gap-1.5 mt-0.5 font-medium truncate">
                                        <i class="fas fa-map-marker-alt text-[10px] text-[#950101]"></i>
                                        {{ $sede->comuna->nombre_comuna }}
                                    </p>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Flecha selectora -->
                        <div class="w-8 h-8 rounded-full bg-gray-50 group-hover:bg-[#950101] group-hover:text-white flex items-center justify-center text-gray-400 transition-all duration-200 shrink-0 ml-3 shadow-2xs dark:bg-gray-700 dark:text-gray-300 dark:group-hover:bg-[#950101] dark:group-hover:text-white">
                            <i class="fas fa-arrow-right text-xs"></i>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="text-center py-8 max-w-md mx-auto bg-white rounded-2xl border border-gray-200 shadow-sm p-8 dark:bg-gray-800 dark:border-gray-700">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gray-100 mb-4 text-gray-400 dark:bg-gray-700 dark:text-gray-300">
                    <i class="fas fa-building text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2 dark:text-white">No hay sedes disponibles</h3>
                <p class="text-sm text-gray-500 mb-6 dark:text-gray-400">Su cuenta no cuenta con sedes asignadas o activas actualmente. Por favor, contacte al administrador del sistema.</p>
                <a href="{{ route('logout') }}" 
                   class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-xs text-sm font-semibold rounded-lg text-gray-700 bg-white hover:bg-gray-50 hover:text-red-700 transition dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
                    <i class="fas fa-arrow-left mr-2 text-xs text-gray-400"></i>
                    Cerrar Sesión
                </a>
            </div>
        @endif
    </main>

    <!-- Footer Estandarizado SIA -->
    <footer class="shrink-0 py-3 sm:py-4 text-center text-xs text-gray-500 border-t border-gray-200/80 bg-white/60 backdrop-blur-sm dark:bg-gray-900/60 dark:border-gray-800 dark:text-gray-400">
        <div class="container mx-auto px-4">
            &copy; {{ date('Y') }} SIA &bull; Universidad Católica de la Santísima Concepción. Todos los derechos reservados.
        </div>
    </footer>
</body>

</html>