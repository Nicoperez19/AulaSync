<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight" style="font-style: oblique;">
                {{ __('Usuarios / Edición') }}
            </h2>
        </div>
    </x-slot>
    <div class="bg-gray-100 p-6 rounded-lg shadow-lg">

        <form method="POST" action="{{ route('users.update', $user->id) }}">
            @csrf
            @method('PUT')

            <div class="grid gap-4 p-4">
                <!-- RUN y Nombre -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <x-form.label for="run" :value="__('RUN')" />
                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <x-heroicon-o-user class="w-5 h-5" />
                            </x-slot>
                            <x-form.input withicon id="run_update" class="block w-full" type="text" name="run"
                                value="{{ old('run', $user->run) }}" required autofocus
                                placeholder="{{ __('RUN') }}" />
                        </x-form.input-with-icon-wrapper>
                    </div>

                    <div>
                        <x-form.label for="nombre" :value="__('Nombre')" />
                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <x-heroicon-o-user class="w-5 h-5" />
                            </x-slot>
                            <x-form.input withicon id="nombre_update" class="block w-full" type="text" name="nombre"
                                value="{{ old('nombre', $user->name) }}" required placeholder="{{ __('Nombre') }}" />
                        </x-form.input-with-icon-wrapper>
                    </div>
                </div>

                <!-- Correo y Celular -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <x-form.label for="correo" :value="__('Correo')" />
                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <x-heroicon-o-mail class="w-5 h-5" />
                            </x-slot>
                            <x-form.input withicon id="correo_update" class="block w-full" type="email" name="correo"
                                value="{{ old('correo', $user->email) }}" required placeholder="{{ __('Correo') }}" />
                        </x-form.input-with-icon-wrapper>
                    </div>

                    <div>
                        <x-form.label for="celular" :value="__('Celular')" />
                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <x-heroicon-o-phone class="w-5 h-5" />
                            </x-slot>
                            <x-form.input withicon id="celular_update" class="block w-full" type="text"
                                name="celular" value="{{ old('celular', $user->celular) }}" required
                                placeholder="{{ __('Celular') }}" />
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
                            <x-form.input withicon id="direccion_update" class="block w-full" type="text"
                                name="direccion" value="{{ old('direccion', $user->direccion) }}" required
                                placeholder="{{ __('Dirección') }}" />
                        </x-form.input-with-icon-wrapper>
                    </div>

                    <div>
                        <x-form.label for="fecha_nacimiento" :value="__('Fecha de Nacimiento')" />
                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <x-heroicon-o-calendar class="w-5 h-5" />
                            </x-slot>
                            <x-form.input withicon id="fecha_nacimiento_update" class="block w-full" type="date"
                                name="fecha_nacimiento" value="{{ old('fecha_nacimiento', $user->fecha_nacimiento) }}"
                                required />
                        </x-form.input-with-icon-wrapper>
                    </div>
                </div>

                <!-- Año de Ingreso y Contraseña -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <x-form.label for="anio_ingreso" :value="__('Año de Ingreso')" />
                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <x-heroicon-o-academic-cap class="w-5 h-5" />
                            </x-slot>
                            <x-form.input withicon id="anio_ingreso_update" class="block w-full" type="number"
                                name="anio_ingreso" value="{{ old('anio_ingreso', $user->anio_ingreso) }}" required
                                min="1900" max="{{ date('Y') }}" />
                        </x-form.input-with-icon-wrapper>
                    </div>

                    <div>
                        <x-form.label for="contrasena" :value="__('Contraseña Nueva')" />
                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <x-heroicon-o-lock-closed class="w-5 h-5" />
                            </x-slot>
                            <x-form.input withicon id="contrasena" class="block w-full" type="password"
                                {{-- value="{{ old('password', $user->password) }}" name="contrasena" --}}
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
                    <x-button>
                        <x-icons.ajust class="w-6 h-6" aria-hidden="true" />
                        <span>{{ __('Guardar Cambios') }}</span>
                    </x-button>
                </div>
            </div>
        </form>
    </div>

</x-app-layout>
