<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-user-gear"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold leading-tight">Información del Perfil</h2>
                    <p class="text-sm text-gray-500">Actualice la información de su perfil y su correo electrónico.</p>
                </div>
            </div>
        </div>
    </x-slot>

    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @if (session('status') === 'profile-updated')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'success',
                    title: '¡Perfil actualizado!',
                    text: 'Los cambios se guardaron correctamente.',
                    confirmButtonColor: '#b91c1c',
                    customClass: {
                        popup: 'rounded-xl'
                    }
                });
            });
        </script>
    @endif

    <div x-data="{ open: false }">
        <div class="p-4 md:p-8">
            <div class="flex flex-col w-full gap-6 mx-auto md:flex-row max-w-7xl">
                <!-- Columna izquierda -->
                <div class="flex flex-col items-center w-full p-6 text-center bg-white rounded-lg shadow-md md:w-1/3">
                    <div
                        class="flex items-center justify-center w-32 h-32 mb-4 bg-red-100 border-4 border-red-200 rounded-full">
                        <i class="text-5xl text-red-400 fa-solid fa-user"></i>
                    </div>
                    <h2 class="text-lg font-semibold">{{ $user->name }}</h2>
                    <p class="text-gray-500">{{ $user->email }}</p>

                    <button @click="open = true"
                        class="flex items-center justify-center w-full gap-2 px-6 py-3 mt-6 text-base font-semibold text-white transition bg-red-600 border border-red-700 rounded-md shadow hover:bg-red-700">
                        <i class="fa-solid fa-pen-to-square"></i> Editar Perfil y Contraseña
                    </button>
                </div>

                <!-- Columna derecha -->
                <div class="flex-1 w-full p-6 bg-white rounded-lg shadow-md">
                    <h1 class="flex items-center mb-4 text-2xl font-semibold text-black">
                        <i class="mr-2 text-black fa-solid fa-user"></i> Información del Perfil
                    </h1>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8">
                        <!-- Nombre -->
                        <div class="flex items-start gap-3">
                            <i class="mt-1 text-2xl text-red-500 fa-solid fa-user"></i>
                            <div>
                                <p class="text-lg font-semibold text-red-600">Nombre</p>
                                <p class="text-base text-gray-800">{{ $user->name }}</p>
                            </div>
                        </div>
                        <!-- Dirección -->
                        <div class="flex items-start gap-3">
                            <i class="mt-1 text-2xl text-red-500 fa-solid fa-location-dot"></i>
                            <div>
                                <p class="text-lg font-semibold text-red-600">Dirección</p>
                                <p class="text-base text-gray-800">{{ $user->direccion }}</p>
                            </div>
                        </div>
                        <!-- Email -->
                        <div class="flex items-start gap-3">
                            <i class="mt-1 text-2xl text-red-500 fa-solid fa-envelope"></i>
                            <div>
                                <p class="text-lg font-semibold text-red-600">Email</p>
                                <p class="text-base text-gray-800">{{ $user->email }}</p>
                            </div>
                        </div>
                        <!-- Fecha de nacimiento -->
                        <div class="flex items-start gap-3">
                            <i class="mt-1 text-2xl text-red-500 fa-solid fa-calendar-days"></i>
                            <div>
                                <p class="text-lg font-semibold text-red-600">Fecha de Nacimiento</p>
                                <p class="text-base text-gray-800">
                                    {{ $user->fecha_nacimiento ? \Carbon\Carbon::parse($user->fecha_nacimiento)->translatedFormat('d \d\e F \d\e Y') : '---' }}
                                </p>
                            </div>
                        </div>
                        <!-- Celular -->
                        <div class="flex items-start gap-3">
                            <i class="mt-1 text-2xl text-red-500 fa-solid fa-phone"></i>
                            <div>
                                <p class="text-lg font-semibold text-red-600">Celular</p>
                                <p class="text-base text-gray-800">{{ $user->celular }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Unificado -->
        <div x-show="open" style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
            <div @click.away="open = false"
                class="relative w-full max-w-2xl p-0 overflow-hidden bg-white rounded-b-lg shadow-xl rounded-t-2xl">
                <!-- Header personalizado -->
                <div
                    class="relative flex items-center justify-between px-6 py-4 overflow-hidden bg-red-700 rounded-t-2xl">
                    <!-- Círculos decorativos -->
                    <div class="absolute w-24 h-24 bg-white rounded-full -top-8 -left-8 opacity-20"></div>
                    <div class="absolute w-24 h-24 bg-white rounded-full -top-8 -right-8 opacity-20"></div>
                    <div class="z-10 flex items-center gap-3">
                        <i class="text-2xl text-white fa-solid fa-user-circle"></i>
                        <span class="text-xl font-bold text-white">Editar perfil</span>
                    </div>
                    <button @click="open = false"
                        class="z-10 text-2xl text-white hover:text-gray-200 focus:outline-none">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="p-8">
                    <form method="POST" action="{{ route('profile.update') }}" class="space-y-6">
                        @csrf
                        @method('patch')
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Nombre -->
                            <div>
                                <x-form.label for="name" :value="__('Nombre')" />
                                <x-form.input id="name" name="name" type="text" class="w-full" :value="old('name', $user->name)" autocomplete="name" placeholder="Ingrese su nombre" />
                                <x-form.error :messages="$errors->get('name')" />
                            </div>
                            <!-- Email -->
                            <div>
                                <x-form.label for="email" :value="__('Email')" />
                                <x-form.input id="email" name="email" type="email" class="w-full" :value="old('email', $user->email)" autocomplete="email" placeholder="ejemplo@correo.com" />
                                <x-form.error :messages="$errors->get('email')" />
                            </div>
                            <!-- Celular -->
                            <div>
                                <x-form.label for="celular" :value="__('Celular')" />
                                <x-form.input id="celular" name="celular" type="text" class="w-full"
                                    :value="old('celular', $user->celular)" placeholder="9XXXXXXXX" />
                                <x-form.error :messages="$errors->get('celular')" />
                            </div>
                            <!-- Dirección -->
                            <div>
                                <x-form.label for="direccion" :value="__('Dirección')" />
                                <x-form.input id="direccion" name="direccion" type="text" class="w-full"
                                    :value="old('direccion', $user->direccion)" placeholder="Ingrese su dirección" />
                                <x-form.error :messages="$errors->get('direccion')" />
                            </div>
                            <!-- Fecha de Nacimiento -->
                            <div>
                                <x-form.label for="fecha_nacimiento" :value="__('Fecha de Nacimiento')" />
                                <x-form.input id="fecha_nacimiento" name="fecha_nacimiento" type="date" class="w-full"
                                    :value="old('fecha_nacimiento', $user->fecha_nacimiento ? \Carbon\Carbon::parse($user->fecha_nacimiento)->format('Y-m-d') : '')" />
                                <x-form.error :messages="$errors->get('fecha_nacimiento')" />
                            </div>

                        </div>
                        <hr>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Contraseña actual -->
                            <div>
                                <x-form.label for="current_password" :value="__('Contraseña Actual')" />
                                <x-form.input id="current_password" name="current_password" type="password"
                                    class="w-full" autocomplete="current-password" />
                                <x-form.error :messages="$errors->get('current_password')" />
                            </div>
                            <!-- Nueva contraseña -->
                            <div>
                                <x-form.label for="password" :value="__('Nueva Contraseña')" />
                                <x-form.input id="password" name="password" type="password" class="w-full"
                                    autocomplete="new-password" />
                                <x-form.error :messages="$errors->get('password')" />
                            </div>
                            <!-- Confirmar contraseña -->
                            <div>
                                <x-form.label for="password_confirmation" :value="__('Confirmar Contraseña')" />
                                <x-form.input id="password_confirmation" name="password_confirmation" type="password"
                                    class="w-full" autocomplete="new-password" />
                                <x-form.error :messages="$errors->get('password_confirmation')" />
                            </div>
                        </div>
                        <div class="flex items-center justify-center w-full gap-4 mt-8">
                            <x-button
                                class="inline-flex items-center gap-2 px-4 py-2 mt-3 text-sm font-medium hover:bg-red-700"
                                variant="primary">{{ __('Guardar cambios') }}</x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>