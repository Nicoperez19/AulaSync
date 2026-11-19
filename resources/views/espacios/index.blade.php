<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-building"></i>
                </div>

                <div>
                    <h2 class="text-2xl font-bold leading-tight">Espacios</h2>
                    <p class="text-sm text-gray-500">Administra los espacios físicos disponibles en el sistema</p>
                </div>
            </div>

        </div>
    </x-slot>

    <div class="space-y-4">
        <div class="flex items-center justify-end gap-2">
            <x-button variant="add" class="gap-2"
                x-on:click.prevent="$dispatch('open-modal', 'add-espacio')">
                <x-icons.add class="w-4 h-4 shrink-0" aria-hidden="true" />
                <span>Agregar Espacio</span>
            </x-button>
            <x-button variant="secondary" href="{{ route('spaces.download-all-qr') }}"
                class="gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    </path>
                </svg>
                <span>QRs (ZIP)</span>
            </x-button>
            <x-button variant="secondary" href="{{ route('spaces.download-all-qr-pdf') }}"
                class="gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                    </path>
                </svg>
                <span>QRs (PDF)</span>
            </x-button>
        </div>
        <livewire:spaces-table />
        <x-modal name="add-espacio" :show="$errors->any()" focusable>
            @slot('title')
            <div class="relative flex items-center justify-between px-3 py-1.5 bg-red-700">
                <div class="flex items-center gap-2">
                    <div class="flex items-center justify-center w-6 h-6 bg-red-100 rounded-full shrink-0">
                        <x-icons.building-office class="w-3.5 h-3.5 text-red-600" aria-hidden="true" rt />
                    </div>
                    <h2 class="text-lg font-bold text-white">
                        Agregar Espacio </h2>
                </div>
                <button @click="show = false"
                    class="ml-2 text-xl font-bold text-white hover:text-gray-200 leading-none">&times;</button>
                <!-- Círculos decorativos -->
                <span
                    class="absolute top-0 left-0 w-16 h-16 -translate-x-1/2 -translate-y-1/2 bg-white rounded-full pointer-events-none bg-opacity-10"></span>
                <span
                    class="absolute top-0 right-0 w-16 h-16 translate-x-1/2 -translate-y-1/2 bg-white rounded-full pointer-events-none bg-opacity-10"></span>
            </div>
            @endslot

            <form method="POST" action="{{ route('spaces.store') }}">
                @csrf

                <!-- Campos hidden con valores por defecto -->
                <input type="hidden" name="estado" value="Disponible">

                <div class="p-6 space-y-6">
                    <!-- Información básica del espacio -->
                    <div class="space-y-4">
                        <h3 class="text-sm font-semibold tracking-wide text-gray-700 uppercase">Información del Espacio
                        </h3>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <x-form.label for="id_espacio" :value="__('ID del Espacio')"
                                    class="font-medium text-left text-gray-700" />
                                <x-form.input-with-icon-wrapper>
                                    <x-slot name="icon">
                                        <x-icons.user class="w-5 h-5 text-gray-400" aria-hidden="true" />
                                    </x-slot>
                                    <x-form.input id="id_espacio" class="block w-full" type="text" name="id_espacio"
                                        :value="old('id_espacio')" placeholder="{{ __('Ej: ESP-001') }}" required />
                                </x-form.input-with-icon-wrapper>
                            </div>

                            <div class="space-y-2">
                                <x-form.label for="nombre_espacio" :value="__('Nombre del Espacio')"
                                    class="font-medium text-left text-gray-700" />
                                <x-form.input-with-icon-wrapper>
                                    <x-slot name="icon">
                                        <x-icons.building-office class="w-5 h-5 text-gray-400" aria-hidden="true" />
                                    </x-slot>
                                    <x-form.input id="nombre_espacio" class="block w-full" type="text"
                                        name="nombre_espacio" :value="old('nombre_espacio')"
                                        placeholder="{{ __('Ej: Laboratorio de Computación') }}" required />
                                </x-form.input-with-icon-wrapper>
                            </div>
                        </div>
                    </div>

                    <!-- Ubicación -->
                    <div class="space-y-4">
                        <h3 class="text-sm font-semibold tracking-wide text-gray-700 uppercase">Ubicación</h3>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <x-form.label for="universidad" :value="__('Universidad')"
                                    class="font-medium text-left text-gray-700" />
                                <select name="id_universidad" id="universidad"
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm"
                                    required onchange="loadSedes()">
                                    <option value="" disabled selected>{{ __('Seleccionar Universidad') }}</option>
                                    @foreach($universidades as $universidad)
                                        <option value="{{ $universidad->id_universidad }}" {{ old('id_universidad') == $universidad->id_universidad ? 'selected' : '' }}>
                                            {{ $universidad->nombre_universidad }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="space-y-2">
                                <x-form.label for="sede" :value="__('Sede')"
                                    class="font-medium text-left text-gray-700" />
                                <select name="id_sede" id="sede"
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm"
                                    required onchange="loadFacultades()" disabled>
                                    <option value="" disabled selected>{{ __('Seleccionar Sede') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <x-form.label for="facultad" :value="__('Facultad')"
                                    class="font-medium text-left text-gray-700" />
                                <select name="id_facultad" id="facultad"
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm"
                                    required onchange="loadPisos()" disabled>
                                    <option value="" disabled selected>{{ __('Seleccionar Facultad') }}</option>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <x-form.label for="selectedPiso" :value="__('Piso')"
                                    class="font-medium text-left text-gray-700" />
                                <select name="piso_id" id="selectedPiso"
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm"
                                    required disabled>
                                    <option value="" disabled selected>{{ __('Seleccionar Piso') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Características del espacio -->
                    <div class="space-y-4">
                        <h3 class="text-sm font-semibold tracking-wide text-gray-700 uppercase">Características</h3>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <x-form.label for="tipo_espacio" :value="__('Tipo de Espacio')"
                                    class="font-medium text-left text-gray-700" />
                                <select name="tipo_espacio" id="tipo_espacio"
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm"
                                    required>
                                    <option value="" disabled selected>{{ __('Seleccionar Tipo de Espacio') }}</option>
                                    @php
                                        $tipos = [
                                            'Sala de Clases' => 'Sala de Clases',
                                            'Laboratorio' => 'Laboratorio',
                                            'Laboratorio de Computación' => 'Laboratorio de Computación',
                                            'Biblioteca' => 'Biblioteca',
                                            'Sala de Reuniones' => 'Sala de Reuniones',
                                            'Oficinas' => 'Oficinas',
                                            'Taller' => 'Taller',
                                            'Auditorio' => 'Auditorio',
                                            'Sala de Estudio' => 'Sala de Estudio',
                                            'Gimnasio' => 'Gimnasio',
                                            'Sala Multiusos' => 'Sala Multiusos',
                                        ];
                                    @endphp
                                    @foreach ($tipos as $value => $label)
                                        <option value="{{ $value }}" {{ old('tipo_espacio') == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="space-y-2">
                                <x-form.label for="puestos_disponibles" :value="__('Puestos Disponibles')"
                                    class="font-medium text-left text-gray-700" />
                                <x-form.input-with-icon-wrapper>
                                    <x-slot name="icon">
                                        <x-icons.user class="w-5 h-5 text-gray-400" aria-hidden="true" />
                                    </x-slot>
                                    <x-form.input id="puestos_disponibles" class="block w-full" type="number"
                                        name="puestos_disponibles" :value="old('puestos_disponibles')"
                                        placeholder="{{ __('Puestos Disponibles') }}" min="1" step="1" />
                                </x-form.input-with-icon-wrapper>
                            </div>
                        </div>
                    </div>

                    <!-- Botón de acción -->
                    <div class="flex justify-end pt-6 border-t border-gray-200">
                        <x-button variant="primary" class="gap-2">
                            <x-icons.add class="w-5 h-5" aria-hidden="true" />
                            <span>{{ __('Agregar Espacio') }}</span>
                        </x-button>
                    </div>
                </div>
            </form>
        </x-modal>
    </div>

    @vite(['resources/js/views/espacios/index.js'])
</x-app-layout>
