<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight" style="font-style: oblique;">
                {{ __('Universidad/ Facultad') }}
            </h2>
        </div>
    </x-slot>

    <div class="flex justify-end mb-4">
        <x-button variant="primary" class="justify-end max-w-xs gap-2"
            x-on:click.prevent="$dispatch('open-modal', 'add-faculty')">
            <x-icons.add class="w-6 h-6" aria-hidden="true" />
        </x-button>
    </div>

    @livewire('faculties-table')
 

    <div class="space-y-1">
        <x-modal name="add-faculty" :show="$errors->any()" focusable>
            <form id="add-faculty-form" method="POST" action="{{ route('faculties.add') }}" enctype="multipart/form-data">
                @csrf

                <div class="grid gap-6 p-6">
                    <div class="space-y-2">
                        <x-form.label for="id_facultad" :value="__('ID Facultad')" class="text-left" />
                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <x-heroicon-o-user aria-hidden="true" class="w-5 h-5" />
                            </x-slot>
                            <x-form.input id="id_facultad" class="block w-full" type="text"
                                name="id_facultad" :value="old('id_facultad')" required autofocus
                                placeholder="{{ __('ID Facultad') }}" />
                        </x-form.input-with-icon-wrapper>
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="nombre_facultad" :value="__('Nombre Facultad')" class="text-left" />
                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <x-heroicon-o-academic-cap aria-hidden="true" class="w-5 h-5" />
                            </x-slot>
                            <x-form.input id="nombre_facultad" class="block w-full" type="text"
                                name="nombre_facultad" :value="old('nombre_facultad')" required
                                placeholder="{{ __('Nombre Facultad') }}" />
                        </x-form.input-with-icon-wrapper>
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="ubicacion_facultad" :value="__('Ubicación Facultad')" class="text-left" />
                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <x-heroicon-o-home aria-hidden="true" class="w-5 h-5" />
                            </x-slot>
                            <x-form.input id="ubicacion_facultad" class="block w-full" type="text"
                                name="ubicacion_facultad" :value="old('ubicacion_facultad')" required
                                placeholder="{{ __('Ubicación Facultad') }}" />
                        </x-form.input-with-icon-wrapper>
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="id_universidad" :value="__('Universidad')" class="text-left" />
                        <select name="id_universidad" id="id_universidad"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            required>
                            <option value="" disabled selected>Seleccionar Universidad</option>
                            @foreach ($universidades as $universidad)
                                <option value="{{ $universidad->id_universidad }}"
                                    {{ old('id_universidad') == $universidad->id_universidad ? 'selected' : '' }}>
                                    {{ $universidad->nombre_universidad }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="logo_facultad" :value="__('Logo Facultad')" class="text-left" />
                        <x-form.input id="logo_facultad" class="block w-full" type="file" name="logo_facultad"
                            accept="image/*" />
                    </div>

                    <div>
                        <x-button class="justify-center w-full gap-2">
                            <x-heroicon-o-user-add class="w-6 h-6" aria-hidden="true" />
                            <span>{{ __('Agregar Facultad') }}</span>
                        </x-button>
                    </div>
                </div>
            </form>
        </x-modal>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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