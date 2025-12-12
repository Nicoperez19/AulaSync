<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'AulaSync') }} - Configuración Exitosa</title>

    <!-- Fuentes -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;300;400;500;700;900&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .confetti {
            position: fixed;
            width: 10px;
            height: 10px;
            background: #f00;
            animation: confetti-fall 3s ease-in-out infinite;
        }
        @keyframes confetti-fall {
            0% { transform: translateY(-100vh) rotate(0deg); opacity: 1; }
            100% { transform: translateY(100vh) rotate(720deg); opacity: 0; }
        }
        .pulse-ring {
            animation: pulse-ring 1.5s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
        }
        @keyframes pulse-ring {
            0% { transform: scale(0.5); opacity: 1; }
            100% { transform: scale(1.5); opacity: 0; }
        }
    </style>
</head>
<body class="font-sans antialiased bg-gradient-to-br from-green-50 to-emerald-100 min-h-screen">
    <div class="min-h-screen flex flex-col items-center justify-center px-4">
        <div class="max-w-2xl mx-auto text-center">
            <!-- Success Animation -->
            <div class="relative inline-block mb-8">
                <div class="absolute inset-0 rounded-full bg-green-300 pulse-ring"></div>
                <div class="relative inline-flex items-center justify-center w-32 h-32 rounded-full bg-green-500 text-white shadow-lg">
                    <i class="fas fa-check text-6xl"></i>
                </div>
            </div>

            <!-- Success Message -->
            <h1 class="text-4xl font-bold text-gray-800 mb-4">
                ¡Felicitaciones!
            </h1>
            <p class="text-xl text-gray-600 mb-8">
                La sede <strong class="text-green-600">{{ $tenant->sede->nombre_sede ?? 'Nueva Sede' }}</strong> 
                ha sido configurada exitosamente.
            </p>

            <!-- Sede Info Card -->
            <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
                @if($tenant->sede && $tenant->sede->logo)
                    <img src="{{ $tenant->sede->getLogoUrl() }}" 
                         alt="{{ $tenant->sede->nombre_sede }}" 
                         class="h-24 mx-auto mb-4">
                @endif
                
                <h2 class="text-2xl font-bold text-gray-800 mb-2">
                    {{ $tenant->sede->nombre_sede ?? 'Sede Configurada' }}
                </h2>
                
                <p class="text-gray-500 mb-4">
                    Configurada el {{ $tenant->initialized_at?->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i') }}
                </p>

                <div class="grid grid-cols-3 gap-4 text-center mt-6">
                    <div class="p-4 bg-green-50 rounded-lg">
                        <i class="fas fa-user-shield text-2xl text-green-600 mb-2"></i>
                        <p class="text-sm text-gray-600">Administrador</p>
                        <p class="font-semibold text-green-600">Creado</p>
                    </div>
                    <div class="p-4 bg-blue-50 rounded-lg">
                        <i class="fas fa-image text-2xl text-blue-600 mb-2"></i>
                        <p class="text-sm text-gray-600">Logo</p>
                        <p class="font-semibold text-blue-600">Configurado</p>
                    </div>
                    <div class="p-4 bg-purple-50 rounded-lg">
                        <i class="fas fa-cog text-2xl text-purple-600 mb-2"></i>
                        <p class="text-sm text-gray-600">Sistema</p>
                        <p class="font-semibold text-purple-600">Listo</p>
                    </div>
                </div>
            </div>

            <!-- Action Button -->
            <a href="{{ route('login') }}" 
               class="inline-flex items-center px-8 py-4 bg-green-600 text-white font-bold text-lg rounded-lg hover:bg-green-700 transition shadow-lg">
                <i class="fas fa-sign-in-alt mr-3"></i>
                Ir al Login
            </a>

            <p class="mt-6 text-gray-500 text-sm">
                Utilice las credenciales del administrador creado para iniciar sesión.
            </p>
        </div>
    </div>

    <!-- Simple confetti effect -->
    <script>
        function createConfetti() {
            const colors = ['#f00', '#0f0', '#00f', '#ff0', '#f0f', '#0ff'];
            for (let i = 0; i < 50; i++) {
                setTimeout(() => {
                    const confetti = document.createElement('div');
                    confetti.className = 'confetti';
                    confetti.style.left = Math.random() * 100 + 'vw';
                    confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                    confetti.style.animationDuration = (Math.random() * 2 + 2) + 's';
                    confetti.style.animationDelay = Math.random() + 's';
                    document.body.appendChild(confetti);
                    
                    setTimeout(() => confetti.remove(), 5000);
                }, i * 50);
            }
        }
        
        // Show confetti on load
        document.addEventListener('DOMContentLoaded', createConfetti);
    </script>
</body>
</html>
