<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Página Expirada - SIA | AulaSync</title>

    <!-- Fuentes & Iconos -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-['Roboto'] antialiased min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl overflow-hidden text-center border border-gray-100">
        <!-- Header Banner Rojo Institucional -->
        <div class="bg-red-700 p-6 text-white relative">
            <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-3 backdrop-blur-sm">
                <i class="fa-solid fa-clock-rotate-left text-3xl text-white"></i>
            </div>
            <h1 class="text-2xl font-bold">Página Expirada</h1>
            <p class="text-red-100 text-sm mt-1">Código: 419 (Sesión Expirada)</p>
        </div>

        <!-- Contenido -->
        <div class="p-6 space-y-6">
            <p class="text-gray-600 text-base leading-relaxed">
                Tu sesión ha caducado por inactividad o la solicitud ha expirado por motivos de seguridad.
            </p>

            <div class="flex flex-col sm:flex-row gap-3 justify-center pt-2">
                <button onclick="window.location.reload()" 
                   class="inline-flex items-center justify-center px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-medium text-sm rounded-lg shadow transition-colors duration-200 gap-2 cursor-pointer">
                    <i class="fa-solid fa-rotate-right"></i>
                    Recargar Página
                </button>

                <a href="{{ route('login') }}" 
                   class="inline-flex items-center justify-center px-5 py-2.5 bg-gray-600 hover:bg-gray-700 text-white font-medium text-sm rounded-lg shadow transition-colors duration-200 gap-2">
                    <i class="fa-solid fa-right-to-bracket"></i>
                    Iniciar Sesión
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 px-6 py-3 border-t border-gray-100 text-xs text-gray-400">
            AulaSync &bull; Sistema de Información de Aulas
        </div>
    </div>
</body>
</html>
