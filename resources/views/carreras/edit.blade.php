<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-user-graduate"></i>
                </div>

                <div>
                    <h2 class="text-2xl font-bold leading-tight">Carreras</h2>
                    <p class="text-sm text-gray-500">Administra las carreras disponibles en el sistema</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <x-button href="{{ route('careers.index') }}" variant="back">
                    <i class="mr-2 fa-solid fa-arrow-left"></i>
                    Volver
                </x-button>
            </div>
        </div>
    </x-slot>

    <div class="p-6 bg-white rounded-lg shadow-lg">
        <form id="edit-career-form" action="{{ route('careers.update', $carrera->id_carrera) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid gap-4 p-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <x-form.label for="id_carrera" :value="__('ID Carrera')" />
                        <x-form.input id="id_carrera" class="block w-full" type="text" name="id_carrera"
                            value="{{ old('id_carrera', $carrera->id_carrera) }}" required />
                    </div>

                    <div>
                        <x-form.label for="nombre" :value="__('Nombre Carrera')" />
                        <x-form.input id="nombre" class="block w-full" type="text" name="nombre"
                            value="{{ old('nombre', $carrera->nombre) }}" required />
                    </div>
                </div>

                <div>
                    <x-form.label for="id_area_academica" :value="__('Área Académica')" />
                    <select name="id_area_academica" id="id_area_academica"
                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#D12420] focus:border-[#D12420] sm:text-m"
                        required>
                        <option value="" disabled>{{ __('Seleccionar Área Académica') }}</option>
                        @foreach($areasAcademicas as $areaAcademica)
                            <option value="{{ $areaAcademica->id_area_academica }}" 
                                {{ $carrera->id_area_academica == $areaAcademica->id_area_academica ? 'selected' : '' }}>
                                {{ $areaAcademica->nombre_area_academica }} - {{ $areaAcademica->facultad->nombre_facultad ?? 'Sin Facultad' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center justify-end gap-3 mt-6">
                    <x-button href="{{ route('careers.index') }}" variant="cancel">
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
            const form = document.getElementById('edit-career-form');

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
