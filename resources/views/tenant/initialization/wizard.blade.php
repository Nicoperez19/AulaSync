<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'AulaSync') }} - Configuración Inicial</title>

    <!-- Fuentes -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;300;400;500;700;900&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .step-indicator {
            transition: all 0.3s ease;
        }
        .step-indicator.active {
            background-color: #2563eb;
            color: white;
        }
        .step-indicator.completed {
            background-color: #10b981;
            color: white;
        }
        .step-line {
            transition: all 0.3s ease;
        }
        .step-line.completed {
            background-color: #10b981;
        }
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="font-sans antialiased bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <header class="bg-light-cloud-blue shadow-md">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex items-center justify-between">
                    <img src="{{ asset('images/aulasync-logo.png') }}" alt="AulaSync" class="h-10 sm:h-12">
                    <div class="text-white">
                        <span class="font-semibold">{{ $sede->nombre_sede ?? 'Configuración de Sede' }}</span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-grow container mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Step Indicators -->
            <div class="max-w-4xl mx-auto mb-8">
                <div class="flex items-center justify-between">
                    @php
                        $steps = [
                            1 => ['icon' => 'fa-user-plus', 'title' => 'Administrador'],
                            2 => ['icon' => 'fa-image', 'title' => 'Logo'],
                            3 => ['icon' => 'fa-building', 'title' => 'Información'],
                            4 => ['icon' => 'fa-upload', 'title' => 'Carga Masiva'],
                            5 => ['icon' => 'fa-calendar', 'title' => 'Períodos'],
                            6 => ['icon' => 'fa-map', 'title' => 'Plano Digital'],
                            7 => ['icon' => 'fa-check', 'title' => 'Finalizar'],
                        ];
                    @endphp

                    @foreach($steps as $stepNum => $stepInfo)
                        <div class="flex items-center {{ $stepNum < 7 ? 'flex-1' : '' }}">
                            <div class="relative flex flex-col items-center">
                                <div class="step-indicator w-10 h-10 sm:w-12 sm:h-12 rounded-full flex items-center justify-center border-2 
                                    {{ $step > $stepNum ? 'completed' : ($step == $stepNum ? 'active border-blue-600' : 'border-gray-300 bg-white text-gray-500') }}">
                                    @if($step > $stepNum)
                                        <i class="fas fa-check"></i>
                                    @else
                                        <i class="fas {{ $stepInfo['icon'] }}"></i>
                                    @endif
                                </div>
                                <span class="mt-2 text-xs sm:text-sm font-medium {{ $step >= $stepNum ? 'text-blue-600' : 'text-gray-500' }} hidden sm:block">
                                    {{ $stepInfo['title'] }}
                                </span>
                            </div>
                            @if($stepNum < 7)
                                <div class="step-line flex-1 h-1 mx-2 {{ $step > $stepNum ? 'completed' : 'bg-gray-300' }}"></div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Alerts -->
            @if(session('success'))
                <div class="max-w-2xl mx-auto mb-6">
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded" role="alert">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle mr-2"></i>
                            <p>{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('info'))
                <div class="max-w-2xl mx-auto mb-6">
                    <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 rounded" role="alert">
                        <div class="flex items-center">
                            <i class="fas fa-info-circle mr-2"></i>
                            <p>{{ session('info') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="max-w-2xl mx-auto mb-6">
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded" role="alert">
                        <p class="font-bold mb-2">Por favor, corrija los siguientes errores:</p>
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <!-- Step Content -->
            <div class="max-w-2xl mx-auto fade-in">
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    @switch($step)
                        @case(1)
                            @include('tenant.initialization.steps.step1-admin')
                            @break
                        @case(2)
                            @include('tenant.initialization.steps.step2-logo')
                            @break
                        @case(3)
                            @include('tenant.initialization.steps.step3-info')
                            @break
                        @case(4)
                            @include('tenant.initialization.steps.step4-bulk')
                            @break
                        @case(5)
                            @include('tenant.initialization.steps.step5-periods')
                            @break
                        @case(6)
                            @include('tenant.initialization.steps.step6-plan')
                            @break
                        @case(7)
                            @include('tenant.initialization.steps.step7-complete')
                            @break
                        @default
                            @include('tenant.initialization.steps.step1-admin')
                    @endswitch
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-gray-800 text-white py-4">
            <div class="container mx-auto px-4 text-center">
                <p class="text-gray-400 text-sm">
                    &copy; {{ date('Y') }} AulaSync - Configuración Inicial de Sede
                </p>
            </div>
        </footer>
    </div>
</body>
</html>
