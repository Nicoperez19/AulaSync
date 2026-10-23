<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-graduation-cap"></i>
                </div>

                <div>
                    <h2 class="text-2xl font-bold leading-tight">Áreas Académicas</h2>
                    <p class="text-sm text-gray-500">Administra las áreas académicas disponibles en el sistema</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <x-button href="{{ route('academic_areas.index') }}" 
                   class="inline-flex items-center px-4 py-2 text-m font-medium border border-gray-300 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Volver
                </x-button>
            </div>
        </div>
    </x-slot>

    <div class="p-6 bg-white rounded-lg shadow-lg">
        <form id="edit-academic-area-form" action="{{ route('academic_areas.update', $areaAcademica->id_area_academica) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid gap-4 p-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <x-form.label for="id_area_academica" :value="__('ID Área Académica')" />
                        <x-form.input id="id_area_academica" class="block w-full" type="text" name="id_area_academica"
                            value="{{ old('id_area_academica', $areaAcademica->id_area_academica) }}" required />
                    </div>

                    <div>
                        <x-form.label for="nombre_area_academica" :value="__('Nombre Área Académica')" />
                        <x-form.input id="nombre_area_academica" class="block w-full" type="text" name="nombre_area_academica"
                            value="{{ old('nombre_area_academica', $areaAcademica->nombre_area_academica) }}" required />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <x-form.label for="tipo_area_academica" :value="__('Tipo de Área')" />
                        <select name="tipo_area_academica" id="tipo_area_academica"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-m"
                            required>
                            <option value="" disabled>{{ __('Seleccionar Tipo') }}</option>
                            <option value="departamento" 
                                {{ $areaAcademica->tipo_area_academica == 'departamento' ? 'selected' : '' }}>
                                Departamento
                            </option>
                            <option value="escuela" 
                                {{ $areaAcademica->tipo_area_academica == 'escuela' ? 'selected' : '' }}>
                                Escuela
                            </option>
                        </select>
                    </div>

                    <div>
                        <x-form.label for="id_facultad" :value="__('Facultad')" />
                        <select name="id_facultad" id="id_facultad"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-m"
                            required>
                            <option value="" disabled>{{ __('Seleccionar Facultad') }}</option>
                            @foreach($facultades as $facultad)
                                <option value="{{ $facultad->id_facultad }}" 
                                    {{ $areaAcademica->id_facultad == $facultad->id_facultad ? 'selected' : '' }}>
                                    {{ $facultad->nombre_facultad }} - {{ $facultad->sede->nombre_sede ?? 'Sin Sede' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex justify-end mt-6">
                    <x-button variant="success">{{ __('Guardar Cambios') }}</x-button>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('edit-academic-area-form');

            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    Swal.fire({
                        title: '¿Seguro de editar?',
                        text: "Estás a punto de guardar los cambios.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, editar',
                        cancelButtonText: 'Cancelar',
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
