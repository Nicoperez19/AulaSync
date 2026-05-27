<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-user-edit"></i>
                </div>

                <div>
                    <h2 class="text-2xl font-bold leading-tight">Usuarios</h2>
                    <p class="text-sm text-gray-500">Administra los usuarios del sistema</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <x-button href="{{ route('users.index') }}" 
                   class="inline-flex items-center px-4 py-2 text-m font-medium border border-gray-300 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Volver
                </x-button>
            </div>
        </div>
    </x-slot>

    <div class="p-6 bg-white rounded-lg shadow-lg">
        <form id="edit-user-form" method="POST" action="{{ isset($user) ? route('users.update', $user->run) : route('users.store') }}" x-data="{ isSuperuser: {{ old('is_superuser', $user->is_superuser ?? false) ? 'true' : 'false' }} }">
            @csrf
            @if(isset($user))
                @method('PUT')
            @endif

            <div class="grid gap-4 p-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <x-form.label for="run" :value="__('RUN')" />
                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <x-heroicon-o-user class="w-5 h-5" />
                            </x-slot>
                            <x-form.input withicon id="run" class="block w-full" type="text" name="run"
                                value="{{ old('run', $user->run ?? '') }}" autofocus placeholder="{{ __('RUN') }}" />
                        </x-form.input-with-icon-wrapper>
                        @error('run')
                            <div class="mt-1 text-xs text-red-500">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <x-form.label for="name" :value="__('Nombre')" />
                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <x-heroicon-o-user class="w-5 h-5" />
                            </x-slot>
                            <x-form.input withicon id="name" class="block w-full" type="text" name="name"
                                value="{{ old('name', $user->name ?? '') }}" placeholder="{{ __('Nombre') }}" />
                        </x-form.input-with-icon-wrapper>
                        @error('name')
                            <div class="mt-1 text-xs text-red-500">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Correo y Celular -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <x-form.label for="email" :value="__('Correo')" />
                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <x-heroicon-o-mail class="w-5 h-5" />
                            </x-slot>
                            <x-form.input withicon id="email" class="block w-full" type="email" name="email"
                                value="{{ old('email', $user->email ?? '') }}" placeholder="{{ __('Correo') }}" />
                        </x-form.input-with-icon-wrapper>
                        @error('email')
                            <div class="mt-1 text-xs text-red-500">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <x-form.label for="celular" :value="__('Celular')" />
                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <x-heroicon-o-phone class="w-5 h-5" />
                            </x-slot>
                            <x-form.input withicon id="celular" class="block w-full" type="text" name="celular"
                                value="{{ old('celular', $user->celular ?? '') }}" placeholder="{{ __('Celular') }}" />
                        </x-form.input-with-icon-wrapper>
                        @error('celular')
                            <div class="mt-1 text-xs text-red-500">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Dirección y Fecha de Nacimiento -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <x-form.label for="direccion" :value="__('Dirección')" />
                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <x-heroicon-o-location-marker class="w-5 h-5" />
                            </x-slot>
                            <x-form.input withicon id="direccion" class="block w-full" type="text" name="direccion"
                                value="{{ old('direccion', $user->direccion ?? '') }}"
                                placeholder="{{ __('Dirección') }}" />
                        </x-form.input-with-icon-wrapper>
                        @error('direccion')
                            <div class="mt-1 text-xs text-red-500">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <x-form.label for="fecha_nacimiento" :value="__('Fecha de Nacimiento')" />
                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <x-heroicon-o-calendar class="w-5 h-5" />
                            </x-slot>
                            <x-form.input withicon id="fecha_nacimiento" class="block w-full" type="date"
                                name="fecha_nacimiento"
                                value="{{ old('fecha_nacimiento', $user->fecha_nacimiento ?? '') }}" />
                        </x-form.input-with-icon-wrapper>
                        @error('fecha_nacimiento')
                            <div class="mt-1 text-xs text-red-500">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Sede -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <x-form.label for="id_sede" :value="__('Sede Asignada')" />
                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </x-slot>
                            <select name="id_sede" id="id_sede" class="block w-full py-2 pl-10 pr-3 text-base border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">{{ __('Sin Sede Asignada') }}</option>
                                @foreach($sedes as $sede)
                                    <option value="{{ $sede->id_sede }}" {{ old('id_sede', $user->id_sede ?? '') == $sede->id_sede ? 'selected' : '' }}>
                                        {{ $sede->nombre_sede }} ({{ $sede->id_sede }})
                                    </option>
                                @endforeach
                            </select>
                        </x-form.input-with-icon-wrapper>
                        @error('id_sede')
                            <div class="mt-1 text-xs text-red-500">{{ $message }}</div>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500 italic">
                            {{ __('Si el usuario no es superusuario, solo podrá acceder a los datos de esta sede.') }}
                        </p>
                    </div>

                <!-- Contraseña -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <!-- Contraseña del Usuario -->
                    <div>
                        <x-form.label for="password" :value="__('Contraseña' . (!isset($user) ? ' *' : ' Nueva'))" />
                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <x-heroicon-o-lock-closed class="w-5 h-5" />
                            </x-slot>
                            <x-form.input 
                                withicon 
                                id="password" 
                                name="password" 
                                class="block w-full" 
                                type="password"
                                autocomplete="new-password"
                                placeholder="{{ isset($user) ? __('Dejar en blanco si no desea cambiarla') : __('Mínimo 8 caracteres') }}"
                                @if(!isset($user)) required @endif 
                            />
                        </x-form.input-with-icon-wrapper>
                        @error('password')
                            <div class="mt-1 text-xs text-red-500">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Roles y Permisos -->
                <div class="grid grid-cols-1 gap-6 mt-8 md:grid-cols-2">
                    <div class="p-4 border rounded-lg shadow-lg">
                        <div class="py-2 text-lg font-semibold text-center bg-gray-200 rounded-t-lg">
                            {{ __('Roles') }}
                        </div>
                        <div class="p-2 overflow-y-auto max-h-64">
                            <ul>
                                @foreach ($roles as $role)
                                    <li class="flex items-center mb-2">
                                        <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                                            {{ isset($user) && $user->hasRole($role->name) ? 'checked' : '' }} class="mr-2" />
                                        <label for="role-{{ $role->id }}">{{ $role->name }}</label>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    <div class="p-4 border rounded-lg shadow-lg">
                        <div class="py-2 text-lg font-semibold text-center bg-gray-200 rounded-t-lg">
                            {{ __('Permisos') }}
                        </div>
                        <div class="p-2 overflow-y-auto max-h-64">
                            <ul>
                                @foreach ($permissions as $permission)
                                    <li class="flex items-center mb-2">
                                        <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                            {{ isset($user) && $user->hasPermissionTo($permission->name) ? 'checked' : '' }}
                                            class="mr-2" />
                                        <label for="permission-{{ $permission->id }}">{{ $permission->name }}</label>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Permisos Especiales -->
                <div class="p-4 mt-6 border-2 border-yellow-400 rounded-lg bg-yellow-50">
                    <div class="flex items-center gap-3 mb-4">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        <h3 class="text-lg font-semibold text-yellow-800">{{ __('Permisos de Superusuario') }}</h3>
                    </div>
                    <div class="flex items-start gap-3">
                        <input type="checkbox" 
                               id="is_superuser" 
                               name="is_superuser" 
                               value="1"
                               {{ old('is_superuser', $user->is_superuser ?? false) ? 'checked' : '' }}
                               class="w-5 h-5 mt-1 text-yellow-600 border-gray-300 rounded focus:ring-yellow-500"
                               x-model="isSuperuser">
                        <div>
                            <label for="is_superuser" class="font-medium text-gray-900">
                                {{ __('Superusuario') }}
                            </label>
                            <p class="text-sm text-gray-600">
                                {{ __('Los superusuarios pueden seleccionar cualquier sede del sistema. Si está desactivado, el usuario solo podrá acceder a su sede asignada.') }}
                            </p>
                        </div>
                    </div>

                    <!-- Contraseña del Wizard - Solo si es Superusuario -->
                    <div x-show="isSuperuser" x-transition class="p-4 mt-4 border-2 border-red-400 rounded-lg bg-red-50">
                        <div class="flex items-center gap-3 mb-3">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            <h4 class="font-semibold text-red-800">{{ __('Seguridad Requerida') }}</h4>
                        </div>
                        <div>
                            <x-form.label for="wizard_password" :value="__('Contraseña  *')" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon">
                                    <x-heroicon-o-lock-closed class="w-5 h-5" />
                                </x-slot>
                                <x-form.input withicon id="wizard_password" name="wizard_password" class="block w-full" type="password"
                                    autocomplete="off"
                                    placeholder="{{ __('Ingresa la contraseña para efectuar el cambio') }}"
                                    x-bind:required="isSuperuser" />
                            </x-form.input-with-icon-wrapper>
                            @error('wizard_password')
                                <div class="mt-1 text-xs text-red-500">{{ $message }}</div>
                            @enderror
                            <p class="text-sm text-red-700 mt-2">
                                {{ __('Debes proporcionar la contraseña para efectuar el cambio para otorgar permisos de superusuario.') }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Botón de Guardar -->
                <div class="flex justify-end mt-6">
                    <x-button variant="success">
                        <x-icons.ajust class="w-6 h-6" aria-hidden="true" />
                        <span>{{ __('Guardar Cambios') }}</span>
                    </x-button>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('edit-user-form');

            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    // Validación básica
                    const run = form.querySelector('input[name="run"]').value;
                    const celular = form.querySelector('input[name="celular"]').value;
                    
                    if (!/^\d{7,8}$/.test(run)) {
                        Swal.fire({
                            title: 'Error',
                            text: 'El RUN debe ser un número de 7 u 8 dígitos',
                            icon: 'error'
                        });
                        return;
                    }
                    
                    if (celular && !/^9\d{8}$/.test(celular)) {
                        Swal.fire({
                            title: 'Error',
                            text: 'El celular debe comenzar con 9 y tener 9 dígitos',
                            icon: 'error'
                        });
                        return;
                    }

                    Swal.fire({
                        title: '¿Seguro de editar?',
                        text: "Estás a punto de guardar los cambios.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, editar',
                        cancelButtonText: 'Cancelar',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            }
        });
    </script>

</x-app-layout>
