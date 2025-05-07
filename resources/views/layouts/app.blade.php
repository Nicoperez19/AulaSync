<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'AulaSync') }}</title>
    @livewireStyles
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@200..900&display=swap" as="style"
        onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@200..900&display=swap" rel="stylesheet">
    </noscript>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>

<body class="font-sans antialiased">
    <div x-data="mainState" :class="{ dark: isDarkMode }" x-on:resize.window="handleWindowResize" x-cloak>
        <div class="min-h-screen text-gray-900 bg-gray-100 dark:bg-dark-eval-0 dark:text-gray-200">

            <div class="fixed top-0 left-0 z-50 w-full">
                <x-navbar />
            </div>

            <x-sidebar.sidebar />

            <div class="flex flex-col min-h-screen pt-16 bg-cloud-light dark:bg-dark-eval-2"
                :style="{ 'margin-left': isSidebarOpen || isSidebarHovered ? '16rem' : '4rem' }"
                style="transition-property: margin; transition-duration: 150ms;">

                <header>
                    <div class="mt-4 p-4 sm:p-6">
                        {{ $header }}
                    </div>
                </header>

                <main class="flex-1 px-4 sm:px-6 overflow-x-auto">
                    {{ $slot }}
                </main>
                <x-footer />
            </div>
        </div>
    </div>
    @livewireScripts
</body>


</html>
