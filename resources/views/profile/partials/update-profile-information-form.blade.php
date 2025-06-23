<section>
<<<<<<< HEAD
    <header>
        <h2 class="text-lg font-medium">
            {{ __('Información del perfil') }}
=======
    <header class="mb-6">
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Información del Perfil') }}
>>>>>>> Nperez
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
<<<<<<< HEAD
            {{ __('Actualiza la información de tu cuenta y dirección de correo electrónico.') }}
=======
            {{ __('Actualice la información de su perfil y su correo electrónico.') }}
>>>>>>> Nperez
        </p>
    </header>

    <style>
        .form-input::placeholder {
            color: #4B5563;
            opacity: 0.7;
        }

        .form-input:focus::placeholder {
            color: #3B82F6;
        }

        .icon-wrapper svg {
            color: #3B82F6;
        }

        .form-input:focus+.icon-wrapper svg {
            color: #2563EB;
        }
    </style>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

<<<<<<< HEAD
    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div class="space-y-2">
            <x-form.label for="name" :value="__('Nombre')" />

            <x-form.input id="name" name="name" type="text" class="block w-full" :value="old('name', $user->name)" required
                autofocus autocomplete="name" />
=======
    <form method="post" action="{{ route('profile.update') }}" class="space-y-8">
        @csrf
        @method('patch')

        <div class="grid grid-cols-3 gap-6 md:grid-cols-3 lg:grid-cols-3">
            {{-- Nombre --}}
            <div class="flex flex-col gap-1">
                <x-form.label for="name" :value="__('Nombre')" />
                <x-form.input id="name" name="name" type="text" class="w-full" :value="old('name', $user->name)" required
                    autofocus autocomplete="name" placeholder="Ingrese su nombre" />
                <x-form.error :messages="$errors->get('name')" />
            </div>

            {{-- Email --}}
            <div class="flex flex-col gap-1">
                <x-form.label for="email" :value="__('Email')" />
                <x-form.input id="email" name="email" type="email" class="w-full" :value="old('email', $user->email)" required
                    autocomplete="email" placeholder="ejemplo@correo.com" />
                <x-form.error :messages="$errors->get('email')" />
            </div>
>>>>>>> Nperez

            {{-- Celular --}}
            <div class="flex flex-col gap-1">
                <x-form.label for="celular" :value="__('Celular')" />
                <x-form.input-with-icon-wrapper class="relative">
                    <x-slot name="icon">
                        <x-heroicon-o-phone class="absolute w-5 h-5 text-gray-400 left-3 top-3" />
                    </x-slot>
                    <x-form.input withicon id="celular" name="celular" type="text" class="w-full pl-10"
                        :value="old('celular', $user->celular)" placeholder="9XXXXXXXX" />
                </x-form.input-with-icon-wrapper>
                <x-form.error :messages="$errors->get('celular')" />
            </div>

            {{-- Dirección --}}
            <div class="flex flex-col gap-1">
                <x-form.label for="direccion" :value="__('Dirección')" />
                <x-form.input-with-icon-wrapper class="relative">
                    <x-slot name="icon">
                        <x-heroicon-o-home class="absolute w-5 h-5 text-gray-400 left-3 top-3" />
                    </x-slot>
                    <x-form.input withicon id="direccion" name="direccion" type="text" class="w-full pl-10"
                        :value="old('direccion', $user->direccion)" placeholder="Ingrese su dirección" />
                </x-form.input-with-icon-wrapper>
                <x-form.error :messages="$errors->get('direccion')" />
            </div>

            {{-- Fecha de Nacimiento --}}
            <div class="flex flex-col gap-1">
                <x-form.label for="fecha_nacimiento" :value="__('Fecha de Nacimiento')" />
                <x-form.input-with-icon-wrapper class="relative">
                    <x-slot name="icon">
                        <x-heroicon-o-calendar class="absolute w-5 h-5 text-gray-400 left-3 top-3" />
                    </x-slot>
                    <x-form.input withicon id="fecha_nacimiento" name="fecha_nacimiento" type="date"
                        class="w-full pl-10" :value="old('fecha_nacimiento', $user->fecha_nacimiento)" />
                </x-form.input-with-icon-wrapper>
                <x-form.error :messages="$errors->get('fecha_nacimiento')" />
            </div>

            {{-- Año de Ingreso --}}
            <div class="flex flex-col gap-1">
                <x-form.label for="anio_ingreso" :value="__('Año de Ingreso')" />
                <x-form.input-with-icon-wrapper class="relative">
                    <x-slot name="icon">
                        <x-heroicon-o-academic-cap class="absolute w-5 h-5 text-gray-400 left-3 top-3" />
                    </x-slot>
                    <select id="anio_ingreso" name="anio_ingreso"
                        class="w-full pl-10 border-gray-300 rounded-md shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-400 focus:ring-blue-400">
                        <option value="" disabled selected>Seleccione un año</option>
                        @for ($year = 2010; $year <= date('Y'); $year++)
                            <option value="{{ $year }}"
                                {{ old('anio_ingreso', $user->anio_ingreso) == $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endfor
                    </select>
                </x-form.input-with-icon-wrapper>
                <x-form.error :messages="$errors->get('anio_ingreso')" />
            </div>
        </div>

<<<<<<< HEAD
        <div class="space-y-2">
            <x-form.label for="email" :value="__('Correo electrónico')" />

            <x-form.input id="email" name="email" type="email" class="block w-full" :value="old('email', $user->email)" required
                autocomplete="email" />

            <x-form.error :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
                <div>
                    <p class="mt-2 text-sm text-gray-800 dark:text-gray-300">
                        {{ __('Tu dirección de correo electrónico no está verificada.') }}

                        <button form="send-verification"
                            class="text-sm text-gray-600 underline rounded-md hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:text-gray-400 dark:hover:text-gray-200 dark:focus:ring-offset-gray-800">
                            {{ __('Haz clic aquí para reenviar el correo de verificación.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-sm font-medium text-green-600">
                            {{ __('Se ha enviado un nuevo enlace de verificación a tu dirección de correo electrónico.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-button>
                {{ __('Guardar') }}
            </x-button>
=======
        {{-- Botón y mensaje --}}
        <div class="flex items-center justify-center w-full gap-4 mt-8">
            <x-button>{{ __('Guardar') }}</x-button>
>>>>>>> Nperez

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Guardado.') }}
                </p>
            @endif
        </div>
    </form>
</section>
