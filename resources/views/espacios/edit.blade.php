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

            <div class="flex items-center gap-2">
                <x-button href="{{ route('spaces.index') }}" variant="back">
                    <i class="mr-2 fa-solid fa-arrow-left"></i>
                    Volver
                </x-button>
            </div>
        </div>
    </x-slot>
    <div class="p-6 bg-white rounded-lg shadow-lg">

        <form id="edit-space-form" action="{{ route('spaces.update', $espacio->id_espacio) }}" method="POST">
            @csrf
            @method('PUT')


            <div class="grid gap-4 p-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <x-form.label for="id_espacio" :value="__('ID del Espacio')" />
                        <x-form.input id="id_espacio" class="block w-full" type="text" name="id_espacio"
                            value="{{ old('id_espacio', $espacio->id_espacio) }}" required readonly />
                        <p class="mt-1 text-xs text-gray-500">El identificador del espacio no se puede modificar en edición.</p>
                    </div>

                    <div>
                        <x-form.label for="nombre_espacio" :value="__('Nombre del Espacio')" />
                        <x-form.input id="nombre_espacio" class="block w-full" type="text" name="nombre_espacio"
                            value="{{ old('nombre_espacio', $espacio->nombre_espacio) }}" required />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <x-form.label for="universidad" :value="__('Universidad')" />
                        <select name="id_universidad" id="universidad"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#D12420] focus:border-[#D12420] sm:text-m"
                            required onchange="loadSedes()">
                            <option value="" disabled>{{ __('Seleccionar Universidad') }}</option>
                            @foreach($universidades as $universidad)
                                <option value="{{ $universidad->id_universidad }}" 
                                    {{ $espacio->piso->facultad->sede->id_universidad == $universidad->id_universidad ? 'selected' : '' }}>
                                    {{ $universidad->nombre_universidad }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-form.label for="sede" :value="__('Sede')" />
                        <select name="id_sede" id="sede"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#D12420] focus:border-[#D12420] sm:text-m"
                           required onchange="loadFacultades()">
                            <option value="" disabled>{{ __('Seleccionar Sede') }}</option>
                            @foreach($sedes as $sede)
                                <option value="{{ $sede->id_sede }}" 
                                    {{ $espacio->piso->facultad->id_sede == $sede->id_sede ? 'selected' : '' }}>
                                    {{ $sede->nombre_sede }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <x-form.label for="facultad" :value="__('Facultad')" />
                        <select name="id_facultad" id="facultad"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#D12420] focus:border-[#D12420] sm:text-m"
                            required onchange="loadPisos()">
                            <option value="" disabled>{{ __('Seleccionar Facultad') }}</option>
                            @foreach($facultades as $facultad)
                                <option value="{{ $facultad->id_facultad }}" 
                                    {{ $espacio->piso->id_facultad == $facultad->id_facultad ? 'selected' : '' }}>
                                    {{ $facultad->nombre_facultad }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-form.label for="piso_id" :value="__('Piso')" />
                        <select name="piso_id" id="piso_id"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#D12420] focus:border-[#D12420] sm:text-m"
                            required>
                            @foreach ($pisos as $piso)
                                <option value="{{ $piso->id }}"
                                    {{ $piso->id == $espacio->piso_id ? 'selected' : '' }}>
                                    {{ $piso->nombre_piso ?: 'Piso ' . $piso->numero_piso }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <x-form.label for="tipo_espacio" :value="__('Tipo de Espacio')" />
                        <select name="tipo_espacio" id="tipo_espacio"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#D12420] focus:border-[#D12420] sm:text-m"
                            required>
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
                                <option value="{{ $value }}"
                                    {{ old('tipo_espacio', $espacio->tipo_espacio) == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-form.label for="estado" :value="__('Estado')" />
                        <select name="estado" id="estado"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#D12420] focus:border-[#D12420] sm:text-m"
                            required>
                            @foreach (['Disponible', 'Ocupado', 'Reservado', 'Mantenimiento'] as $estado)
                                <option value="{{ $estado }}"
                                    {{ old('estado', $espacio->estado) == $estado ? 'selected' : '' }}>
                                    {{ $estado }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <x-form.label for="puestos_disponibles" :value="__('Puestos Disponibles')" />
                        <x-form.input id="puestos_disponibles" class="block w-full" type="number"
                            name="puestos_disponibles"
                            value="{{ old('puestos_disponibles', $espacio->puestos_disponibles) }}" min="1"
                            step="1" />
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 mt-6">
                    <x-button href="{{ route('spaces.index') }}" variant="cancel">
                        {{ __('Cancelar') }}
                    </x-button>
                    <x-button type="submit" variant="save">
                        <i class="mr-2 fa-solid fa-floppy-disk"></i>
                        {{ __('Guardar Cambios') }}
                    </x-button>
                </div>
            </div>
        </form>
    </div>

    @vite(['resources/js/views/espacios/edit.js'])
</x-app-layout>
