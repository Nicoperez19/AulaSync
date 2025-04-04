<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight" style="font-style: oblique;">
                {{ __('Pisos y Espacios') }}
            </h2>
        </div>
    </x-slot>

    <div class="bg-gray-100 p-6 rounded-lg shadow-lg">
        <div class="flex justify-end mb-4">
            <x-button target="_blank" variant="primary" class="justify-end max-w-xs gap-2"
                x-on:click.prevent="$dispatch('open-modal', 'add-floor-space')">
                <x-icons.add class="w-6 h-6" aria-hidden="true" />
            </x-button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Columna de Pisos (1 parte) -->
            <div class="col-span-1">
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold">Pisos</h3>

                    <!-- Lista de Pisos Aquí -->
                    <!-- Puedes agregar una tabla o formulario para manejar los pisos -->

                    <div class="space-y-2">
                        <x-form.label for="floor_name" :value="__('Nombre del Piso')" />
                        <x-form.input id="floor_name" class="block w-full" type="text" name="floor_name"
                            placeholder="{{ __('Nombre del Piso') }}" />
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="faculty" :value="__('Facultad')" />
                        <x-form.select id="faculty" name="faculty">
                            <option value="">Seleccionar Facultad</option>
                            <!-- Agregar opciones de facultades -->
                        </x-form.select>
                    </div>

                    <x-button class="w-full justify-center gap-2 bg-blue-500 hover:bg-blue-700 text-white rounded">
                        <x-heroicon-o-plus class="w-5 h-5" />
                        <span>{{ __('Agregar Piso') }}</span>
                    </x-button>
                </div>
            </div>

            <!-- Columna de Espacios (3 partes) -->
            <div class="col-span-3">
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold">Espacios</h3>


                    <div class="space-y-2">
                        <x-form.label for="space_name" :value="__('Nombre del Espacio')" />
                        <x-form.input id="space_name" class="block w-full" type="text" name="space_name"
                            placeholder="{{ __('Nombre del Espacio') }}" />
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="domain" :value="__('Dominio')" />
                        <x-form.input id="domain" class="block w-full" type="text" name="domain"
                            placeholder="{{ __('Dominio del Espacio') }}" />
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="floor_association" :value="__('Piso Asociado')" />
                        <x-form.select id="floor_association" name="floor_association">
                            <option value="">Seleccionar Piso</option>
                            <!-- Agregar opciones de pisos -->
                        </x-form.select>
                    </div>

                    <x-button class="w-full justify-center gap-2 bg-blue-500 hover:bg-blue-700 text-white rounded">
                        <x-heroicon-o-plus class="w-5 h-5" />
                        <span>{{ __('Agregar Espacio') }}</span>
                    </x-button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
