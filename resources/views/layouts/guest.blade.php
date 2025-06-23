<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@200..900&display=swap" as="style"
        onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@200..900&display=swap" rel="stylesheet">
    </noscript>
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> 6c05e560f5edb88b89bd0fe7d8d71ecb8386c841
    @livewireStyles 
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/livewire-v2.11.0/livewire.js"></script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@2.8.2/dist/alpine.min.js" defer></script>
<<<<<<< HEAD
=======
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;300;400;500;700;900&display=swap"
        rel="stylesheet">
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/livewire-v2.11.0/livewire.js"></script>
>>>>>>> Nperez
=======
>>>>>>> 6c05e560f5edb88b89bd0fe7d8d71ecb8386c841

</head>

<body>
    <div x-data="mainState" class="font-sans antialiased" :class="{ dark: isDarkMode }" x-cloak>
<<<<<<< HEAD
<<<<<<< HEAD
        <div class="flex flex-col min-h-screen text-black bg-light-cloud-blue dark:bg-dark-eval-0 dark:text-gray-200">
=======
        <div class="flex flex-col min-h-screen text-black bg-gray-100 dark:bg-dark-eval-0 dark:text-gray-200">
>>>>>>> Nperez
=======
        <div class="flex flex-col min-h-screen text-black bg-light-cloud-blue dark:bg-dark-eval-0 dark:text-gray-200">
>>>>>>> 6c05e560f5edb88b89bd0fe7d8d71ecb8386c841
            {{ $slot }}
        </div>

        <div class="fixed top-10 right-10">
<<<<<<< HEAD
<<<<<<< HEAD
            <x-button type="button" icon-only variant="primary" sr-text="Toggle dark mode" x-on:click="toggleTheme">
=======
            <x-button type="button" icon-only variant="login" sr-text="Toggle dark mode" x-on:click="toggleTheme">
>>>>>>> Nperez
=======
            <x-button type="button" icon-only variant="primary" sr-text="Toggle dark mode" x-on:click="toggleTheme">
>>>>>>> 6c05e560f5edb88b89bd0fe7d8d71ecb8386c841
                <x-heroicon-o-moon x-show="!isDarkMode" aria-hidden="true" class="w-6 h-6" />

                <x-heroicon-o-sun x-show="isDarkMode" aria-hidden="true" class="w-6 h-6" />
            </x-button>
        </div>
    </div>
    @livewireScripts
<<<<<<< HEAD
<<<<<<< HEAD
  
=======

>>>>>>> Nperez
=======
  
>>>>>>> 6c05e560f5edb88b89bd0fe7d8d71ecb8386c841

</body>

</html>
