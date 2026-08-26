<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-school"></i>
                </div>

                <div>
                    <h2 class="text-2xl font-bold leading-tight">Escuelas</h2>
                    <p class="text-sm text-gray-500">Administra las escuelas del sistema</p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="p-6 bg-white rounded-lg shadow-lg">

        <div class="flex items-center justify-end mb-6">
            <x-button variant="add" class="max-w-xs gap-2" x-on:click.prevent="$dispatch('open-modal', 'add-escuela')">
                <x-icons.add class="w-6 h-6" aria-hidden="true" />
                Agregar Escuela
            </x-button>
        </div>
        <livewire:escuelas-table />

        <x-modal name="add-escuela" :show="$errors->any()" focusable>
            @slot('title')
            <div class="relative flex items-center justify-between p-2 bg-red-700">
                <div class="flex items-center gap-3">
                    <div class="p-4 bg-red-100 rounded-full">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-white">
                        Agregar Escuela
                    </h2>
                </div>
                <button @click="show = false"
                    class="ml-2 text-2xl font-bold text-white hover:text-gray-200">&times;</button>
            </div>
            @endslot

            <form method="POST" action="{{ route('escuelas.store') }}" class="p-6">
                @csrf

                <div class="grid gap-4">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="space-y-2">
                            <x-form.label for="id_area_academica" value="ID Escuela *" />
                            <x-form.input id="id_area_academica" name="id_area_academica" type="text"
                                class="w-full @error('id_area_academica') border-red-500 @enderror" required maxlength="20"
                                placeholder="Ej: ESC01" value="{{ old('id_area_academica') }}" />
                            @error('id_area_academica')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <x-form.label for="nombre_area_academica" value="Nombre Escuela *" />
                            <x-form.input id="nombre_area_academica" name="nombre_area_academica" type="text"
                                class="w-full @error('nombre_area_academica') border-red-500 @enderror" required maxlength="255"
                                placeholder="Ej: Escuela de Ingeniería" value="{{ old('nombre_area_academica') }}" />
                            @error('nombre_area_academica')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="id_facultad" value="Facultad *" />
                        <select name="id_facultad" id="id_facultad"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-m @error('id_facultad') border-red-500 @enderror"
                            required>
                            <option value="" disabled selected>{{ __('Seleccionar Facultad') }}</option>
                            @foreach($facultades as $facultad)
                                <option value="{{ $facultad->id_facultad }}" {{ old('id_facultad') == $facultad->id_facultad ? 'selected' : '' }}>
                                    {{ $facultad->nombre_facultad }} ({{ $facultad->sede->nombre_sede ?? '' }})
                                </option>
                            @endforeach
                        </select>
                        @error('id_facultad')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end mt-6">
                        <x-button variant="success">{{ __('Crear Escuela') }}</x-button>
                    </div>
                </div>
            </form>
        </x-modal>
    </div>
</x-app-layout>
