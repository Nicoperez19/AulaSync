<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight" style="font-style: oblique;">
                {{ __('Universidad/ Área Académica') }}
            </h2>
        </div>
    </x-slot>

    <div class="flex justify-end mb-4">
        <x-button variant="primary" class="justify-end max-w-xs gap-2"
            x-on:click.prevent="$dispatch('open-modal', 'add-academic-area')">
            <x-icons.add class="w-6 h-6" aria-hidden="true" />
        </x-button>
    </div>

    @livewire('academic-area-table')

    <div class="space-y-1">
        <x-modal name="add-academic-area" :show="$errors->any()" focusable>
            <form method="POST" action="{{ route('academic_areas.add') }}" enctype="multipart/form-data">
                @csrf

                <div class="grid gap-6 p-6">
                    <div class="space-y-2">
                        <x-form.label for="id_area_academica" :value="__('ID Área Académica')" class="text-left" />
                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <x-heroicon-o-user aria-hidden="true" class="w-5 h-5" />
                            </x-slot>
                            <x-form.input id="id_area_academica" class="block w-full" type="text"
                                name="id_area_academica" :value="old('id_area_academica')" required autofocus
                                placeholder="{{ __('ID Área Académica') }}" />
                        </x-form.input-with-icon-wrapper>
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="nombre_area_academica" :value="__('Nombre Área Académica')" class="text-left" />
                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <x-heroicon-o-academic-cap aria-hidden="true" class="w-5 h-5" />
                            </x-slot>
                            <x-form.input id="nombre_area_academica" class="block w-full" type="text"
                                name="nombre_area_academica" :value="old('nombre_area_academica')" required
                                placeholder="{{ __('Nombre Área Académica') }}" />
                        </x-form.input-with-icon-wrapper>
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="tipo_area_academica" :value="__('Tipo Área Académica')" class="text-left" />
                        <select name="tipo_area_academica" id="tipo_area_academica"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            required>
                            <option value="departamento" {{ old('tipo_area_academica') == 'departamento' ? 'selected' : '' }}>
                                Departamento
                            </option>
                            <option value="escuela" {{ old('tipo_area_academica') == 'escuela' ? 'selected' : '' }}>
                                Escuela
                            </option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="id_facultad" :value="__('Facultad')" class="text-left" />
                        <select name="id_facultad" id="id_facultad"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            required>
                            <option value="" disabled selected>Seleccionar Facultad</option>
                            @foreach ($facultades as $facultad)
                                <option value="{{ $facultad->id_facultad }}"
                                    {{ old('id_facultad') == $facultad->id_facultad ? 'selected' : '' }}>
                                    {{ $facultad->nombre_facultad }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-button class="justify-center w-full gap-2">
                            <x-heroicon-o-user-add class="w-6 h-6" aria-hidden="true" />
                            <span>{{ __('Agregar Área Académica') }}</span>
                        </x-button>
                    </div>
                </div>
            </form>
        </x-modal>
    </div>
</x-app-layout>
