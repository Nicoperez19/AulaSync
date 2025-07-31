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
        <form id="edit-user-form" method="POST" action="{{ route('users.update', $user->run) }}">
            @csrf
            @method('PUT')

            <div class="grid gap-4 p-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <x-form.label for="run" :value="__('RUN')" />
                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <x-heroicon-o-user class="w-5 h-5" />
                            </x-slot>
                            <x-form.input withicon id="run" class="block w-full" type="text" name="run"
                                value="{{ old('run', $user->run) }}" autofocus placeholder="{{ __('RUN') }}" />
                        </x-form.input-with-icon-wrapper>
                    </div>

                    <div>
                        <x-form.label for="name" :value="__('Nombre')" />
                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <x-heroicon-o-user class="w-5 h-5" />
                            </x-slot>
                            <x-form.input withicon id="name" class="block w-full" type="text" name="name"
                                value="{{ old('name', $user->name) }}" placeholder="{{ __('Nombre') }}" />
                        </x-form.input-with-icon-wrapper>
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
                                value="{{ old('email', $user->email) }}" placeholder="{{ __('Correo') }}" />
                        </x-form.input-with-icon-wrapper>
                    </div>

                    <div>
                        <x-form.label for="celular" :value="__('Celular')" />
                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <x-heroicon-o-phone class="w-5 h-5" />
                            </x-slot>
                            <x-form.input withicon id="celular" class="block w-full" type="text" name="celular"
                                value="{{ old('celular', $user->celular) }}" placeholder="{{ __('Celular') }}" />
                        </x-form.input-with-icon-wrapper>
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
                                value="{{ old('direccion', $user->direccion) }}"
                                placeholder="{{ __('Dirección') }}" />
                        </x-form.input-with-icon-wrapper>
                    </div>

                    <div>
                        <x-form.label for="fecha_nacimiento" :value="__('Fecha de Nacimiento')" />
                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <x-heroicon-o-calendar class="w-5 h-5" />
                            </x-slot>
                            <x-form.input withicon id="fecha_nacimiento" class="block w-full" type="date"
                                name="fecha_nacimiento"
                                value="{{ old('fecha_nacimiento', $user->fecha_nacimiento) }}" />
                        </x-form.input-with-icon-wrapper>
                    </div>
                </div>

                <!-- Año de Ingreso y Contraseña -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <x-form.label for="anio_ingreso_add" :value="__('Año de Ingreso')" class="text-left" />
                        <select id="anio_ingreso" name="anio_ingreso" class="block w-full sm:w-1/2" required>
                            <option value="" disabled selected>Seleccione un año</option>
                            @foreach ($years as $year)
                                <option value="{{ $year }}"
                                    {{ old('anio_ingreso', $user->anio_ingreso) == $year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endforeach
                        </select>

                        @error('anio_ingreso')
                            <div class="mt-1 text-xs text-red-500">{{ $message }}</div>
                        @enderror
                    </div>


                    <div>
                        <x-form.label for="password" :value="__('Contraseña Nueva')" />
                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <x-heroicon-o-lock-closed class="w-5 h-5" />
                            </x-slot>
                            <x-form.input withicon id="password" name="password" class="block w-full" type="password"
                                placeholder="{{ __('Dejar en blanco si no desea cambiarla') }}" />
                        </x-form.input-with-icon-wrapper>
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
                                            {{ $user->hasRole($role->name) ? 'checked' : '' }} class="mr-2" />
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
                                            {{ $user->hasPermissionTo($permission->name) ? 'checked' : '' }}
                                            class="mr-2" />
                                        <label for="permission-{{ $permission->id }}">{{ $permission->name }}</label>
                                    </li>
                                @endforeach
                            </ul>
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
