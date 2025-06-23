<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight" style="font-style: oblique;">
                {{ __('Usuarios / Edición') }}
            </h2>
        </div>
    </x-slot>

    <div class="p-6 bg-gray-100 rounded-lg shadow-lg">
        <form method="POST" action="{{ route('users.update', $user->run) }}">
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
                    <x-button variant="add">
                        <x-icons.ajust class="w-6 h-6" aria-hidden="true" />
                        <span>{{ __('Guardar Cambios') }}</span>
                    </x-button>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('edit-user-form');
            const submitButton = form.querySelector('button[type="submit"]');

            form.addEventListener('submit', async function(e) {
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

                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

                try {
                    const formData = new FormData(form);
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const data = await response.json();

                    if (response.ok) {
                        Swal.fire({
                            title: '¡Éxito!',
                            text: data.message,
                            icon: 'success'
                        }).then(() => {
                            window.location.href = '{{ route("users.index") }}';
                        });
                    } else {
                        let errorMessage = 'Ha ocurrido un error';
                        if (data.errors) {
                            errorMessage = Object.values(data.errors).flat().join('\n');
                        } else if (data.message) {
                            errorMessage = data.message;
                        }
                        Swal.fire({
                            title: 'Error',
                            text: errorMessage,
                            icon: 'error'
                        });
                    }
                } catch (error) {
                    Swal.fire({
                        title: 'Error',
                        text: 'Ha ocurrido un error al procesar la solicitud',
                        icon: 'error'
                    });
                } finally {
                    submitButton.disabled = false;
                    submitButton.innerHTML = 'Guardar';
                }
            });
        });
    </script>

</x-app-layout>
