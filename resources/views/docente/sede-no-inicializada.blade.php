<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sede en Configuración | {{ config('app.name', 'AulaSync') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="h-full flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-3xl p-8 border border-slate-200 shadow-xl text-center space-y-6">
        <div class="w-20 h-20 rounded-2xl bg-amber-50 text-amber-500 border border-amber-200 flex items-center justify-center mx-auto text-3xl shadow-inner">
            <i class="fa-solid fa-hourglass-half"></i>
        </div>

        <div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Sede en Proceso de Configuración</h1>
            <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                Tu sede asignada aún no ha completado el proceso de configuración inicial por parte del administrador del sistema.
            </p>
            <p class="text-xs text-slate-600 mt-2 font-medium bg-slate-50 p-3 rounded-xl border border-slate-100">
                Por favor, comunícate con la administración académica o soporte técnico para más información.
            </p>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full py-2.5 px-4 text-xs font-bold rounded-xl bg-slate-800 hover:bg-slate-900 text-white shadow transition">
                <i class="fa-solid fa-arrow-right-from-bracket mr-1"></i> Cerrar Sesión
            </button>
        </form>
    </div>
</body>
</html>
