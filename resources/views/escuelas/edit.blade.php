<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-school"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold leading-tight">Editar Escuela</h2>
                    <p class="text-sm text-gray-500">Modifica la información de la escuela</p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="p-6 bg-white rounded-lg shadow-lg">
        <form method="POST" action="{{ route('escuelas.update', $escuela->id_area_academica) }}">
            @csrf
            @method('PUT')

            <div class="grid gap-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <x-form.label for="id_area_academica" value="ID Escuela *" />
                        <x-form.input id="id_area_academica" name="id_area_academica" type="text" class="w-full @error('id_area_academica') border-red-500 @enderror" required maxlength="20" value="{{ old('id_area_academica', $escuela->id_area_academica) }}" />
                        @error('id_area_academica')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="nombre_area_academica" value="Nombre Escuela *" />
                        <x-form.input id="nombre_area_academica" name="nombre_area_academica" type="text" class="w-full @error('nombre_area_academica') border-red-500 @enderror" required maxlength="255" value="{{ old('nombre_area_academica', $escuela->nombre_area_academica) }}" />
                        @error('nombre_area_academica')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="space-y-2">
                    <x-form.label for="id_facultad" value="Facultad *" />
                    <select name="id_facultad" id="id_facultad" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-m @error('id_facultad') border-red-500 @enderror" required>
                        <option value="" disabled>{{ __('Seleccionar Facultad') }}</option>
                        @foreach($facultades as $facultad)
                            <option value="{{ $facultad->id_facultad }}" {{ old('id_facultad', $escuela->id_facultad) == $facultad->id_facultad ? 'selected' : '' }}>
                                {{ $facultad->nombre_facultad }} ({{ $facultad->sede->nombre_sede ?? '' }})
                            </option>
                        @endforeach
                    </select>
                    @error('id_facultad')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-2 mt-6">
                    <x-button variant="secondary" type="button" onclick="window.location='{{ route('escuelas.index') }}'">Cancelar</x-button>
                    <x-button variant="success">Actualizar Escuela</x-button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
