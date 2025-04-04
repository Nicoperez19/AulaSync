<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight" style="font-style: oblique;">
                {{ __('Universidad/ Área Académica/ Editar') }}
            </h2>
        </div>
    </x-slot>

    <form action="{{ route('academic_areas.update', $areaAcademica->id_area_academica) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="grid gap-4 p-4">
            <!-- Datos Generales -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                <div>
                    <x-form.label for="id_area_academica" :value="__('ID Área Académica')" />
                    <x-form.input withicon id="id_area_academica" class="block w-full" type="text" name="id_area_academica"
                        value="{{ old('id_area_academica', $areaAcademica->id_area_academica) }}" required />
                </div>

                <div>
                    <x-form.label for="nombre_area_academica" :value="__('Nombre Área Académica')" />
                    <x-form.input withicon id="nombre_area_academica" class="block w-full" type="text"
                        name="nombre_area_academica" value="{{ old('nombre_area_academica', $areaAcademica->nombre_area_academica) }}" required />
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <x-form.label for="tipo_area_academica" :value="__('Tipo de Área Académica')" />
                    <select name="tipo_area_academica" id="tipo_area_academica" class="block w-full" required>
                        <option value="departamento" {{ $areaAcademica->tipo_area_academica == 'departamento' ? 'selected' : '' }}>
                            Departamento
                        </option>
                        <option value="escuela" {{ $areaAcademica->tipo_area_academica == 'escuela' ? 'selected' : '' }}>
                            Escuela
                        </option>
                    </select>
                </div>

                <div>
                    <x-form.label for="id_facultad" :value="__('Facultad')" />
                    <select name="id_facultad" id="id_facultad" class="block w-full" required>
                        @foreach ($facultades as $facultad)
                            <option value="{{ $facultad->id_facultad }}"
                                {{ $facultad->id_facultad == $areaAcademica->id_facultad ? 'selected' : '' }}>
                                {{ $facultad->nombre_facultad }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

           

            <div class="flex justify-end mt-6">
                <x-button>{{ __('Guardar Cambios') }}</x-button>
            </div>
        </div>
    </form>

</x-app-layout>
