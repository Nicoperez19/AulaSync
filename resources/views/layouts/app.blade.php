<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'AulaSync') }}</title>
    @livewireStyles
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,200;0,300;0,400;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>

<body class="font-sans antialiased">
    <div x-data="mainState" :class="{ dark: isDarkMode }" x-on:resize.window="handleWindowResize" x-cloak>
        <div class="min-h-screen text-gray-900 bg-gray-100 dark:bg-dark-eval-0 dark:text-gray-200">
            <div class="fixed top-0 left-0 z-50 w-full">
                <x-navbar />
            </div> <x-sidebar.sidebar />
            <div class="flex flex-col min-h-screen pt-16"
                style="transition-property: margin; transition-duration: 150ms;">

                <div class="container" style="margin-top: 6rem; min-height: 100vh;">
                    <header :style="{ 'margin-left': isSidebarOpen || isSidebarHovered ? '16rem' : '4rem' }">
                        <div class="p-4 sm:p-6">
                            {{ $header }}
                        </div>
                    </header>
                    <main class="flex-1 px-4 sm:px-6" style="margin-top: 1rem; overflow-x: auto;"
                        :style="{ 'margin-left': isSidebarOpen || isSidebarHovered ? '16rem' : '4rem' }">
                        {{ $slot }}
                    </main>
                </div>
                <x-footer />
            </div>
        </div>
    </div>
    @livewireScripts
    
</body>

</html>
