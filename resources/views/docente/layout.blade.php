<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Portal Docente') | {{ config('app.name', 'AulaSync') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
    @stack('styles')
</head>
<body class="h-full flex flex-col text-slate-800 antialiased">

    {{-- Banner de Superadmin Simulando --}}
    @if($esSuperadminSimulando ?? false)
        <div class="bg-amber-500 text-slate-900 px-4 py-2 shadow-sm border-b border-amber-600 font-medium text-sm">
            <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-user-secret text-base"></i>
                    <span><strong>Modo Supervisión:</strong> Estás viendo el portal docente como <strong>{{ $docenteActivo?->name ?? 'Docente (' . $docenteActivoRun . ')' }}</strong> (RUN: {{ $docenteActivoRun }}).</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-amber-200 text-amber-900">Modo Prueba Activo</span>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('docente.supervision') }}" class="px-2.5 py-1 rounded bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold transition">
                        <i class="fa-solid fa-users mr-1"></i> Cambiar Docente
                    </a>
                    <a href="{{ route('dashboard') }}" class="px-2.5 py-1 rounded bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold transition">
                        <i class="fa-solid fa-arrow-left mr-1"></i> Volver a Panel Admin
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- Barra de Navegación Superior --}}
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('docente.dashboard') }}" class="flex items-center gap-2 text-primary-600 font-bold text-xl tracking-tight hover:opacity-90 transition">
                    <div class="w-9 h-9 rounded-lg bg-blue-600 text-white flex items-center justify-center shadow">
                        <i class="fa-solid fa-graduation-cap text-lg"></i>
                    </div>
                    <span>Aula<span class="text-blue-600">Sync</span> <span class="text-xs uppercase px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 font-semibold tracking-wider">Docente</span></span>
                </a>
            </div>

            <div class="flex items-center gap-4">
                <div class="text-right hidden sm:block">
                    <div class="text-sm font-semibold text-slate-800">{{ $docenteActivo?->name ?? Auth::user()->name }}</div>
                    <div class="text-xs text-slate-500">RUN: {{ $docenteActivoRun ?? Auth::user()->run }}</div>
                </div>

                <div class="h-8 w-px bg-slate-200 hidden sm:block"></div>

                @if(Auth::user()->hasRole('Administrador') || Auth::user()->hasRole('Supervisor') || Auth::user()->is_superuser)
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-200 transition" title="Ir al Panel de Administración">
                        <i class="fa-solid fa-gauge text-slate-500"></i>
                        <span class="hidden md:inline">Panel Admin</span>
                    </a>
                @endif

                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-medium text-slate-600 hover:text-red-600 hover:bg-red-50 border border-slate-200 hover:border-red-200 transition">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        <span class="hidden sm:inline">Cerrar Sesión</span>
                    </button>
                </form>
            </div>
        </div>
    </header>

    {{-- Mensajes Flash --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-3 shadow-sm">
                <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                <div class="flex-1 font-medium">{{ session('success') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center gap-3 shadow-sm">
                <i class="fa-solid fa-circle-exclamation text-rose-600 text-lg"></i>
                <div class="flex-1 font-medium">{{ session('error') }}</div>
            </div>
        @endif

        @if(session('info'))
            <div class="p-4 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 text-sm flex items-center gap-3 shadow-sm">
                <i class="fa-solid fa-circle-info text-blue-600 text-lg"></i>
                <div class="flex-1 font-medium">{{ session('info') }}</div>
            </div>
        @endif
    </div>

    {{-- Contenido Principal --}}
    <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 w-full">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-white border-t border-slate-200 py-4 mt-auto">
        <div class="max-w-7xl mx-auto px-4 text-center text-xs text-slate-400">
            &copy; {{ date('Y') }} AulaSync &bull; Portal Docente de Asistencia y Gestión de Aulas
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
