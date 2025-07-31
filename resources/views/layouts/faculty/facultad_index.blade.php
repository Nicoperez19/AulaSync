<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-graduation-cap"></i>
                </div>

                <div>
                    <h2 class="text-2xl font-bold leading-tight">Facultades</h2>
                    <p class="text-sm text-gray-500">Administra las facultades disponibles en el sistema</p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="p-6 bg-white rounded-lg shadow-lg">
        <div class="flex items-center justify-between mb-6">
            <div class="w-2/3">
                <input type="text" wire:model.live="search" placeholder="Buscar por Nombre o ID"
                    class="w-full px-4 py-2 border rounded dark:bg-gray-700 dark:text-white">
            </div>
            <x-button variant="add" class="max-w-xs gap-2" x-on:click.prevent="$dispatch('open-modal', 'add-faculty')">
                <x-icons.add class="w-6 h-6" aria-hidden="true" />
                Agregar Facultad
            </x-button>
        </div>
        <livewire:faculties-table />
        <x-modal name="add-faculty" :show="$errors->any()" focusable>
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
                        Agregar Facultad
                    </h2>
                </div>
                <button @click="show = false"
                    class="ml-2 text-2xl font-bold text-white hover:text-gray-200">&times;</button>
                <!-- Círculos decorativos -->
                <span
                    class="absolute top-0 left-0 w-32 h-32 -translate-x-1/2 -translate-y-1/2 bg-white rounded-full pointer-events-none bg-opacity-10"></span>
                <span
                    class="absolute top-0 right-0 w-32 h-32 translate-x-1/2 -translate-y-1/2 bg-white rounded-full pointer-events-none bg-opacity-10"></span>
            </div>
            @endslot

            <form method="POST" action="{{ route('faculties.add') }}" class="p-6">
                @csrf

                <div class="grid gap-4">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="space-y-2">
                            <x-form.label for="id_facultad" value="ID Facultad *" />
                            <x-form.input id="id_facultad" name="id_facultad" type="text"
                                class="w-full @error('id_facultad') border-red-500 @enderror" required maxlength="20"
                                placeholder="Ej: IT" value="{{ old('id_facultad') }}" />
                            @error('id_facultad')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <x-form.label for="nombre_facultad" value="Nombre Facultad *" />
                            <x-form.input id="nombre_facultad" name="nombre_facultad" type="text"
                                class="w-full @error('nombre_facultad') border-red-500 @enderror" required
                                maxlength="100" placeholder="Ej: Instituto Tecnológico"
                                value="{{ old('nombre_facultad') }}" />
                            @error('nombre_facultad')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="id_universidad" value="Universidad *" />
                        <select name="id_universidad" id="id_universidad"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-m @error('id_universidad') border-red-500 @enderror"
                            required>
                            <option value="" disabled selected>{{ __('Seleccionar Universidad') }}</option>
                            @foreach($universidades as $universidad)
                                <option value="{{ $universidad->id_universidad }}" {{ old('id_universidad') == $universidad->id_universidad ? 'selected' : '' }}>
                                    {{ $universidad->nombre_universidad }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_universidad')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="id_sede" value="Sede *" />
                        <select name="id_sede" id="id_sede"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-m @error('id_sede') border-red-500 @enderror"
                            required>
                            <option value="" disabled selected>{{ __('Seleccionar Sede') }}</option>
                            @foreach($sedes as $sede)
                                <option value="{{ $sede->id_sede }}" {{ old('id_sede') == $sede->id_sede ? 'selected' : '' }}>
                                    {{ $sede->nombre_sede }} - {{ $sede->universidad->nombre_universidad }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_sede')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="id_campus" value="Campus" />
                        <select name="id_campus" id="id_campus"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-m @error('id_campus') border-red-500 @enderror">
                            <option value="" selected>{{ __('Seleccionar Campus (Opcional)') }}</option>
                            @foreach($campuses as $campus)
                                <option value="{{ $campus->id_campus }}" {{ old('id_campus') == $campus->id_campus ? 'selected' : '' }}>
                                    {{ $campus->nombre_campus }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_campus')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end mt-6">
                        <x-button variant="success">{{ __('Crear Facultad') }}</x-button>
                    </div>
                </div>
            </form>
        </x-modal>
    </div>
</x-app-layout>