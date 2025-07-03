<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="fa-solid fa-user-gear text-white text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold leading-tight">Información del Perfil</h2>
                    <p class="text-gray-500 text-sm">Actualice la información de su perfil y su correo electrónico.</p>
                </div>
            </div>
        </div>
    </x-slot>

    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @if (session('status') === 'profile-updated')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
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
            <div class="flex flex-col md:flex-row gap-6 w-full max-w-7xl mx-auto">
                <!-- Columna izquierda -->
                <div class="bg-white p-6 rounded-lg shadow-md flex flex-col items-center text-center w-full md:w-1/3">
                    <div class="w-32 h-32 bg-red-100 rounded-full border-4 border-red-200 flex items-center justify-center mb-4">
                        <i class="fa-solid fa-user text-5xl text-red-400"></i>
                    </div>
                    <h2 class="text-lg font-semibold">{{ $user->name }}</h2>
                    <p class="text-gray-500">{{ $user->email }}</p>
                    <div class="flex items-center justify-center mt-2 text-gray-500 text-sm">
                        <i class="fa-solid fa-graduation-cap mr-1"></i> Ingreso {{ $user->anio_ingreso ?? '----' }}
                    </div>
                    <button @click="open = true"
                        class="mt-6 flex items-center justify-center gap-2 px-6 py-3 w-full text-base font-semibold text-white bg-red-600 hover:bg-red-700 rounded-md shadow border border-red-700 transition">
                        <i class="fa-solid fa-pen-to-square"></i> Editar Perfil y Contraseña
                    </button>
                </div>

                <!-- Columna derecha -->
                <div class="bg-white p-6 rounded-lg shadow-md flex-1 w-full">
                    <h1 class="text-2xl font-semibold text-black mb-4 flex items-center">
                        <i class="fa-solid fa-user mr-2 text-black"></i> Información del Perfil
                    </h1>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8">
                        <!-- Nombre -->
                        <div class="flex items-start gap-3">
                            <i class="fa-solid fa-user text-2xl text-red-500 mt-1"></i>
                            <div>
                                <p class="text-lg font-semibold text-red-600">Nombre</p>
                                <p class="text-gray-800 text-base">{{ $user->name }}</p>
                            </div>
                        </div>
                        <!-- Dirección -->
                        <div class="flex items-start gap-3">
                            <i class="fa-solid fa-location-dot text-2xl text-red-500 mt-1"></i>
                            <div>
                                <p class="text-lg font-semibold text-red-600">Dirección</p>
                                <p class="text-gray-800 text-base">{{ $user->direccion }}</p>
                            </div>
                        </div>
                        <!-- Email -->
                        <div class="flex items-start gap-3">
                            <i class="fa-solid fa-envelope text-2xl text-red-500 mt-1"></i>
                            <div>
                                <p class="text-lg font-semibold text-red-600">Email</p>
                                <p class="text-gray-800 text-base">{{ $user->email }}</p>
                            </div>
                        </div>
                        <!-- Fecha de nacimiento -->
                        <div class="flex items-start gap-3">
                            <i class="fa-solid fa-calendar-days text-2xl text-red-500 mt-1"></i>
                            <div>
                                <p class="text-lg font-semibold text-red-600">Fecha de Nacimiento</p>
                                <p class="text-gray-800 text-base">
                                    {{ $user->fecha_nacimiento ? \Carbon\Carbon::parse($user->fecha_nacimiento)->translatedFormat('d \d\e F \d\e Y') : '---' }}
                                </p>
                            </div>
                        </div>
                        <!-- Celular -->
                        <div class="flex items-start gap-3">
                            <i class="fa-solid fa-phone text-2xl text-red-500 mt-1"></i>
                            <div>
                                <p class="text-lg font-semibold text-red-600">Celular</p>
                                <p class="text-gray-800 text-base">{{ $user->celular }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Unificado -->
        <div x-show="open" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
            <div @click.away="open = false" class="bg-white rounded-b-lg rounded-t-2xl shadow-xl w-full max-w-2xl p-0 relative overflow-hidden">
                <!-- Header personalizado -->
                <div class="relative flex items-center justify-between bg-red-700 rounded-t-2xl px-6 py-4 overflow-hidden">
                    <!-- Círculos decorativos -->
                    <div class="absolute -top-8 -left-8 w-24 h-24 bg-white opacity-20 rounded-full"></div>
                    <div class="absolute -top-8 -right-8 w-24 h-24 bg-white opacity-20 rounded-full"></div>
                    <div class="flex items-center gap-3 z-10">
                        <i class="fa-solid fa-user-circle text-2xl text-white"></i>
                        <span class="text-white text-xl font-bold">Editar perfil</span>
                    </div>
                    <button @click="open = false" class="text-white text-2xl hover:text-gray-200 focus:outline-none z-10">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="p-8">
                    <form method="POST" action="{{ route('profile.update') }}" class="space-y-6">
                        @csrf
                        @method('patch')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                                <x-form.input id="celular" name="celular" type="text" class="w-full" :value="old('celular', $user->celular)" placeholder="9XXXXXXXX" />
                                <x-form.error :messages="$errors->get('celular')" />
                            </div>
                            <!-- Dirección -->
                            <div>
                                <x-form.label for="direccion" :value="__('Dirección')" />
                                <x-form.input id="direccion" name="direccion" type="text" class="w-full" :value="old('direccion', $user->direccion)" placeholder="Ingrese su dirección" />
                                <x-form.error :messages="$errors->get('direccion')" />
                            </div>
                            <!-- Fecha de Nacimiento -->
                            <div>
                                <x-form.label for="fecha_nacimiento" :value="__('Fecha de Nacimiento')" />
                                <x-form.input id="fecha_nacimiento" name="fecha_nacimiento" type="date" class="w-full" :value="old('fecha_nacimiento', $user->fecha_nacimiento)" />
                                <x-form.error :messages="$errors->get('fecha_nacimiento')" />
                            </div>
                            <!-- Año de Ingreso -->
                            <div>
                                <x-form.label for="anio_ingreso" :value="__('Año de Ingreso')" />
                                <select id="anio_ingreso" name="anio_ingreso" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-400 focus:ring-blue-400">
                                    <option value="" disabled selected>Seleccione un año</option>
                                    @for ($year = 2010; $year <= date('Y'); $year++)
                                        <option value="{{ $year }}" {{ old('anio_ingreso', $user->anio_ingreso) == $year ? 'selected' : '' }}>{{ $year }}</option>
                                    @endfor
                                </select>
                                <x-form.error :messages="$errors->get('anio_ingreso')" />
                            </div>
                        </div>
                        <hr>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Contraseña actual -->
                            <div>
                                <x-form.label for="current_password" :value="__('Contraseña Actual')" />
                                <x-form.input id="current_password" name="current_password" type="password" class="w-full" autocomplete="current-password" />
                                <x-form.error :messages="$errors->get('current_password')" />
                            </div>
                            <!-- Nueva contraseña -->
                            <div>
                                <x-form.label for="password" :value="__('Nueva Contraseña')" />
                                <x-form.input id="password" name="password" type="password" class="w-full" autocomplete="new-password" />
                                <x-form.error :messages="$errors->get('password')" />
                            </div>
                            <!-- Confirmar contraseña -->
                            <div>
                                <x-form.label for="password_confirmation" :value="__('Confirmar Contraseña')" />
                                <x-form.input id="password_confirmation" name="password_confirmation" type="password" class="w-full" autocomplete="new-password" />
                                <x-form.error :messages="$errors->get('password_confirmation')" />
                            </div>
                        </div>
                        <div class="flex items-center justify-center w-full gap-4 mt-8">
                            <x-button>{{ __('Guardar cambios') }}</x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>