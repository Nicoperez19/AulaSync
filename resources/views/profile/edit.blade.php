<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="fa-solid fa-user-gear text-white text-2xl"></i> <!-- Icono de perfil con engranaje -->
                </div>

                <div>
                    <h2 class="text-2xl font-bold leading-tight">Perfil de Usuario</h2>
                    <p class="text-gray-500 text-sm">Visualiza y actualiza la información de tu cuenta</p>
                </div>
            </div>
        </div>
    </x-slot>


    <div class="p-6">
        <div class="flex flex-col lg:flex-row gap-6">

            {{-- Columna izquierda --}}
            <div class="bg-white p-6 rounded-lg shadow-md w-full lg:w-1/3 flex flex-col items-center text-center">
                <div
                    class="w-32 h-32 bg-red-100 rounded-full border-4 border-red-200 flex items-center justify-center mb-4">
                    <i class="fa-solid fa-user text-5xl text-red-400"></i>
                </div>

                <h2 class="text-lg font-semibold">{{ $user->name }}</h2>
                <p class="text-gray-500">{{ $user->email }}</p>
                
                <a href=""
                    class="mt-6 inline-flex items-center justify-center gap-2 px-4 py-2 w-full text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-md shadow border border-black/40">
                    <i class="fa-solid fa-pen-to-square"></i> Editar Perfil y Contraseña
                </a>
            </div>

            {{-- Columna derecha --}}
            <div class="bg-white p-6 rounded-lg shadow-md w-full lg:w-2/3">
                <h3 class="text-lg font-semibold text-red-600 mb-1">
                    <i class="fa-solid fa-user mr-1"></i> Información del Perfil
                </h3>
                <p class="text-gray-500 text-sm mb-4">Actualice la información de su perfil y su correo electrónico.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">

                    {{-- Nombre --}}
                    <div>
                        <p class="text-gray-500 font-medium">Nombre</p>
                        <p class="text-gray-900">{{ $user->name }}</p>
                    </div>

                    {{-- Dirección --}}
                    <div>
                        <p class="text-gray-500 font-medium">Dirección</p>
                        <p class="text-gray-900">{{ $user->direccion }}</p>
                    </div>

                    {{-- Email --}}
                    <div>
                        <p class="text-gray-500 font-medium">Email</p>
                        <p class="text-gray-900">{{ $user->email }}</p>
                    </div>

                    {{-- Fecha de nacimiento --}}
                    <div>
                        <p class="text-gray-500 font-medium">Fecha de Nacimiento</p>
                        <p class="text-gray-900">
                            {{ \Carbon\Carbon::parse($user->fecha_nacimiento)->translatedFormat('d \d\e F \d\e Y') }}
                        </p>
                    </div>

                    {{-- Celular --}}
                    <div>
                        <p class="text-gray-500 font-medium">Celular</p>
                        <p class="text-gray-900">{{ $user->celular }}</p>
                    </div>

                    {{-- Año de ingreso --}}
                    <div>
                        <p class="text-gray-500 font-medium">Año de Ingreso</p>
                        <p class="text-gray-900">{{ $user->anio_ingreso }}</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>