<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight">
                {{ __('Universidad/ Carrera') }}
            </h2>
        </div>
    </x-slot>

    <div class="flex justify-end mb-4">
        <x-button variant="primary" class="justify-end max-w-xs gap-2"
            x-on:click.prevent="$dispatch('open-modal', 'add-career')">
            <x-icons.add class="w-6 h-6" aria-hidden="true" />
        </x-button>
    </div>

    @livewire('careers-table')

    <div class="space-y-1">
        <x-modal name="add-career" :show="$errors->any()" focusable>
            <form method="POST" action="{{ route('careers.add') }}">
                @csrf

                <div class="grid gap-6 p-6">
                    <div class="space-y-2">
                        <x-form.label for="id_carrera" :value="__('ID Carrera')" class="text-left" />
                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <x-heroicon-o-user aria-hidden="true" class="w-5 h-5" />
                            </x-slot>
                            <x-form.input id="id_carrera" class="block w-full" type="text"
                                name="id_carrera" :value="old('id_carrera')" required autofocus
                                placeholder="{{ __('ID Carrera') }}" />
                        </x-form.input-with-icon-wrapper>
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="nombre" :value="__('Nombre Carrera')" class="text-left" />
                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <x-heroicon-o-academic-cap aria-hidden="true" class="w-5 h-5" />
                            </x-slot>
                            <x-form.input id="nombre" class="block w-full" type="text"
                                name="nombre" :value="old('nombre')" required
                                placeholder="{{ __('Nombre Carrera') }}" />
                        </x-form.input-with-icon-wrapper>
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
                                    {{ $facultad->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-button class="justify-center w-full gap-2">
                            <x-heroicon-o-user-add class="w-6 h-6" aria-hidden="true" />
                            <span>{{ __('Agregar Carrera') }}</span>
                        </x-button>
                    </div>
                </div>
            </form>
        </x-modal>
    </div>
</x-app-layout>