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

            <div class="flex items-center gap-2">
                <x-button href="{{ route('faculties.index') }}" variant="back">
                    <i class="mr-2 fa-solid fa-arrow-left"></i>
                    Volver
                </x-button>
            </div>
        </div>
    </x-slot>

    <div class="p-6 bg-white rounded-lg shadow-lg">
        <form id="edit-faculty-form" action="{{ route('faculties.update', $facultad->id_facultad) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid gap-4 p-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <x-form.label for="id_facultad" :value="__('ID Facultad')" />
                        <x-form.input id="id_facultad" class="block w-full" type="text" name="id_facultad"
                            value="{{ old('id_facultad', $facultad->id_facultad) }}" required />
                    </div>

                    <div>
                        <x-form.label for="nombre_facultad" :value="__('Nombre Facultad')" />
                        <x-form.input id="nombre_facultad" class="block w-full" type="text" name="nombre_facultad"
                            value="{{ old('nombre_facultad', $facultad->nombre_facultad) }}" required />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <x-form.label for="id_universidad" :value="__('Universidad')" />
                        <select name="id_universidad" id="id_universidad"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#D12420] focus:border-[#D12420] sm:text-m"
                            required>
                            <option value="" disabled>{{ __('Seleccionar Universidad') }}</option>
                            @foreach($universidades as $universidad)
                                <option value="{{ $universidad->id_universidad }}"
                                    {{ $universidad->id_universidad == $facultad->id_universidad ? 'selected' : '' }}>
                                    {{ $universidad->nombre_universidad }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-form.label for="id_sede" :value="__('Sede')" />
                        <select name="id_sede" id="id_sede"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#D12420] focus:border-[#D12420] sm:text-m"
                            required>
                            <option value="" disabled>{{ __('Seleccionar Sede') }}</option>
                            @foreach($sedes as $sede)
                                <option value="{{ $sede->id_sede }}"
                                    {{ $sede->id_sede == $facultad->id_sede ? 'selected' : '' }}>
                                    {{ $sede->nombre_sede }} - {{ $sede->universidad->nombre_universidad }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <x-form.label for="id_campus" :value="__('Campus')" />
                    <select name="id_campus" id="id_campus"
                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#D12420] focus:border-[#D12420] sm:text-m">
                        <option value="">{{ __('Seleccionar Campus (Opcional)') }}</option>
                        @foreach($campuses as $campus)
                            <option value="{{ $campus->id_campus }}"
                                {{ $campus->id_campus == $facultad->id_campus ? 'selected' : '' }}>
                                {{ $campus->nombre_campus }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center justify-end gap-3 mt-6">
                    <x-button href="{{ route('faculties.index') }}" variant="cancel">
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

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('edit-faculty-form');

            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    Swal.fire({
                        title: '¿Guardar cambios?',
                        text: 'Estás a punto de guardar los cambios realizados.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, guardar',
                        cancelButtonText: 'Cancelar',
                        confirmButtonColor: '#10B981',
                        cancelButtonColor: '#8D8D8D',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            }
        });
    </script>
</x-app-layout>
