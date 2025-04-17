<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight" style="font-style: oblique;">
                {{ __('Usuarios/ Usuarios') }}
            </h2>
        </div>
    </x-slot>

    <div class="p-6 bg-gray-100 rounded-lg shadow-lg">
        @auth
            @if (auth()->user()->hasRole('Administrador'))
                <div class="flex justify-end mb-4">
                    <x-button target="_blank" variant="primary" class="justify-end max-w-xs gap-2"
                        x-on:click.prevent="$dispatch('open-modal', 'add-user')">
                        <x-icons.add class="w-6 h-6" aria-hidden="true" />
                    </x-button>
                </div>
            @endif
        @endauth

        @livewire('users-table')

        <div class="space-y-6">
            <x-modal name="add-user" :show="$errors->any()" focusable>
                <form method="POST" action="{{ route('users.add') }}">
                    @csrf

                    <div class="grid gap-6 p-6">
                        <div class="space-y-2">
                            <x-form.label for="run" :value="__('RUN')" class="text-left" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon">
                                    <x-heroicon-o-user aria-hidden="true" class="w-5 h-5" />
                                </x-slot>
                                <x-form.input withicon id="run_add" class="block w-1/2" type="text" name="run"
                                    :value="old('run')" required autofocus placeholder="{{ __('RUN') }}" />
                            </x-form.input-with-icon-wrapper>
                        </div>

                        <div class="space-y-2">
                            <x-form.label for="name" :value="__('Nombre')" class="text-left" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon">
                                    <x-heroicon-o-user aria-hidden="true" class="w-5 h-5" />
                                </x-slot>
                                <x-form.input withicon id="name_add" class="block w-1/2" type="text" name="name"
                                    :value="old('name')" required autofocus placeholder="{{ __('Nombre') }}" />
                            </x-form.input-with-icon-wrapper>
                        </div>

                        <div class="space-y-2">
                            <x-form.label for="email" :value="__('Correo')" class="text-left" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon">
                                    <x-heroicon-o-mail aria-hidden="true" class="w-5 h-5" />
                                </x-slot>
                                <x-form.input withicon id="email_add" class="block w-1/2" type="email" name="email"
                                    :value="old('email')" required placeholder="{{ __('Correo') }}" />
                            </x-form.input-with-icon-wrapper>
                        </div>

                        <div class="space-y-2">
                            <x-form.label for="password" :value="__('Contraseña')" class="text-left" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon">
                                    <x-heroicon-o-lock-closed aria-hidden="true" class="w-5 h-5" />
                                </x-slot>
                                <x-form.input withicon id="password_add" class="block w-1/2" type="password"
                                    name="password" required autocomplete="new-password"
                                    placeholder="{{ __('Contraseña') }}" />
                            </x-form.input-with-icon-wrapper>
                        </div>

                        <div class="space-y-2">
                            <x-form.label for="celular" :value="__('Celular')" class="text-left" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon">
                                    <x-heroicon-o-phone aria-hidden="true" class="w-5 h-5" />
                                </x-slot>
                                <x-form.input withicon id="celular_add" class="block w-1/2" type="text"
                                    name="celular" :value="old('celular')" required placeholder="{{ __('Celular') }}" />
                            </x-form.input-with-icon-wrapper>
                        </div>

                        <div class="space-y-2">
                            <x-form.label for="direccion" :value="__('Dirección')" class="text-left" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon">
                                    <x-heroicon-o-home aria-hidden="true" class="w-5 h-5" />
                                </x-slot>
                                <x-form.input withicon id="direccion_add" class="block w-1/2" type="text"
                                    name="direccion" :value="old('direccion')" required placeholder="{{ __('Dirección') }}" />
                            </x-form.input-with-icon-wrapper>
                        </div>

                        <div class="space-y-2">
                            <x-form.label for="fecha_nacimiento" :value="__('Fecha de Nacimiento')" class="text-left" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon">
                                    <x-heroicon-o-calendar aria-hidden="true" class="w-5 h-5" />
                                </x-slot>
                                <x-form.input withicon id="fecha_nacimiento_add" class="block w-1/2" type="date"
                                    name="fecha_nacimiento" :value="old('fecha_nacimiento')" required />
                            </x-form.input-with-icon-wrapper>
                        </div>

                        <div class="space-y-2">
                            <x-form.label for="anio_ingreso" :value="__('Año de Ingreso')" class="text-left" />
                            <x-form.input-with-icon-wrapper>
                                <x-slot name="icon">
                                    <x-heroicon-o-clock aria-hidden="true" class="w-5 h-5" />
                                </x-slot>
                                <x-form.input withicon id="anio_ingreso_add" class="block w-1/2" type="number"
                                    name="anio_ingreso" :value="old('anio_ingreso')" required
                                    placeholder="{{ __('Año de Ingreso') }}" />
                            </x-form.input-with-icon-wrapper>
                        </div>

                        <div>
                            <x-button class="justify-center w-full gap-2">
                                <x-heroicon-o-user-add class="w-6 h-6" aria-hidden="true" />
                                <span>{{ __('Agregar') }}</span>
                            </x-button>
                        </div>
                    </div>
                </form>
            </x-modal>
        </div>
    </div>

    <script>
        @if (session('success'))
            Swal.fire({
                title: '¡Éxito!',
                text: '{{ session('success') }}',
                icon: 'success',
                confirmButtonText: 'Aceptar'
            });
        @endif

        @if (session('error'))
            Swal.fire({
                title: '¡Error!',
                text: '{{ session('error') }}',
                icon: 'error',
                confirmButtonText: 'Aceptar'
            });
        @endif
    </script>
</x-app-layout>
