<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight">
                {{ __('Dashboard') }}
            </h2>
        </div>
    </x-slot>

    <!-- Contenedor principal -->
    <div class="p-6 overflow-hidden bg-white rounded-md shadow-md dark:bg-dark-eval-1">

        <!-- Fila superior con dos div -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="flex flex-col gap-2 bg-gray-500 p-4 rounded-md">
            </div>

            <div class="flex flex-col gap-2 bg-gray-500 p-4 rounded-md">
                <h3 class="font-medium text-white">Último usuario registrado</h3>
                <div class="p-4 bg-gray-400 rounded-md">
                    <p class="text-sm text-white">Nombre: Juan Pérez</p>
                    <p class="text-sm text-white">Correo: juan.perez@example.com</p>
                    <p class="text-sm text-white">Fecha de registro: 03/04/2025</p>
                </div>
            </div>
        </div>

        <!-- Gráfico principal en el centro con fondo gris -->
        <div class="mb-6 bg-gray-500 p-4 rounded-md">
            <h3 class="font-medium text-white mb-2">Gráfico principal</h3>
            <!-- Aquí iría el gráfico -->
            <div class="w-full h-64 bg-gray-400 rounded-md">
                <p class="text-center text-white mt-24">Próximamente...</p>
            </div>
        </div>

        <!-- Fila inferior con dos gráficos, ambos con fondo gris -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Primer gráfico con fondo gris -->
            <div class="w-full h-64 bg-gray-500 rounded-md p-4">
                <p class="text-center text-white mt-24">Gráfico 1 - Próximamente...</p>
            </div>

            <!-- Segundo gráfico con fondo gris -->
            <div class="w-full h-64 bg-gray-500 rounded-md p-4">
                <p class="text-center text-white mt-24">Gráfico 2 - Próximamente...</p>
            </div>
        </div>

    </div>
</x-app-layout>
