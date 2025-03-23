<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight">
                {{ __('Usuarios/ Usuarios/ Edición') }}
            </h2>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('users.update', $user->id) }}">
        @csrf
        @method('PUT')

        <div class="grid gap-1 p-1">
            <!-- RUN -->
            <div class="space-y-4">
                <!-- Contenedor con Grid para 2 columnas -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <!-- Columna izquierda -->
                    <div class="space-y-2">
                        <!-- RUN -->
                        <x-form.label for="run" :value="__('RUN')" />
                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <x-heroicon-o-user aria-hidden="true" class="w-5 h-5" />
                            </x-slot>
                            <x-form.input withicon id="run_update" class="block w-full sm:w-64" type="text" name="run"
                                value="{{ old('run', $user->run) }}" required autofocus placeholder="{{ __('RUN') }}" />
                        </x-form.input-with-icon-wrapper>
                    </div>
            
                    <div class="space-y-2">
                        <!-- Nombre -->
                        <x-form.label for="name" :value="__('Nombre')" />
                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <x-heroicon-o-user aria-hidden="true" class="w-5 h-5" />
                            </x-slot>
                            <x-form.input withicon id="name_update" class="block w-full sm:w-64" type="text" name="name"
                                value="{{ old('name', $user->name) }}" required autofocus placeholder="{{ __('Nombre') }}" />
                        </x-form.input-with-icon-wrapper>
                    </div>
            
                    <div class="space-y-2">
                        <!-- Correo -->
                        <x-form.label for="email" :value="__('Correo')" />
                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <x-heroicon-o-mail aria-hidden="true" class="w-5 h-5" />
                            </x-slot>
                            <x-form.input withicon id="email_update" class="block w-full sm:w-64" type="email" name="email"
                                value="{{ old('email', $user->email) }}" required placeholder="{{ __('Correo') }}" />
                        </x-form.input-with-icon-wrapper>
                    </div>
            
                    <div class="space-y-2">
                        <!-- Contraseña Actual -->
                        <x-form.label for="current_password" :value="__('Contraseña Actual')" />
                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <x-heroicon-o-lock-closed aria-hidden="true" class="w-5 h-5" />
                            </x-slot>
                            <x-form.input withicon id="current_password" class="block w-full sm:w-64" type="password"
                                name="current_password" placeholder="{{ __('Contraseña Actual') }}" required />
                        </x-form.input-with-icon-wrapper>
                    </div>
            
                    <!-- Segunda columna -->
                    <div class="space-y-2">
                        <!-- Añadir más campos si es necesario -->
                    </div>
            
                    <div class="space-y-2">
                        <!-- Añadir más campos si es necesario -->
                    </div>
                </div>
            </div>
            

            <!-- Cuadro de Roles y Permisos -->
            <div class="mt-8">

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                    <!-- Columna de Roles -->
                    <div class="p-4 border rounded-lg shadow-lg ">
                        <div class="py-2 text-lg font-semibold text-center bg-gray-200 rounded-t-lg"
                            style="background-color: #f5f5f5;"> 
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

                    <!-- Columna de Permisos -->
                    <div class="p-4 border rounded-lg shadow-lg">
                        <div class="py-2 text-lg font-semibold text-center bg-gray-200 rounded-t-lg"
                            style="background-color: #f5f5f5;">
                            {{ __('Permisos') }}
                        </div>
                        <!-- Lista de Permisos -->
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
            </div>
       

    </form>
    <div>
        <x-button class="justify-center w-full gap-2">
            <x-icons.ajust class="w-6 h-6" aria-hidden="true" />
            <span>{{ __('Editar') }}</span>
        </x-button>
    </div>



</x-app-layout>
