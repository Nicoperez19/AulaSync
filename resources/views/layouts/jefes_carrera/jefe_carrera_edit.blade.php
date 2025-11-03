<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-user-tie"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold leading-tight">Editar Jefe de Carrera</h2>
                    <p class="text-sm text-gray-500">Modifica la información del jefe de carrera</p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="p-6 bg-white rounded-lg shadow-lg">
        <form method="POST" action="{{ route('jefes-carrera.update', $jefeCarrera->id) }}">
            @csrf
            @method('PUT')

            <div class="grid gap-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <x-form.label for="nombre" value="Nombre *" />
                        <x-form.input id="nombre" name="nombre" type="text" class="w-full @error('nombre') border-red-500 @enderror" required maxlength="100" value="{{ old('nombre', $jefeCarrera->nombre) }}" />
                        @error('nombre')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="email" value="Email *" />
                        <x-form.input id="email" name="email" type="email" class="w-full @error('email') border-red-500 @enderror" required value="{{ old('email', $jefeCarrera->email) }}" />
                        @error('email')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <x-form.label for="telefono" value="Teléfono" />
                        <x-form.input id="telefono" name="telefono" type="text" class="w-full @error('telefono') border-red-500 @enderror" maxlength="20" value="{{ old('telefono', $jefeCarrera->telefono) }}" />
                        @error('telefono')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="id_carrera" value="Carrera *" />
                        <select name="id_carrera" id="id_carrera" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-m @error('id_carrera') border-red-500 @enderror" required>
                            <option value="" disabled>{{ __('Seleccionar Carrera') }}</option>
                            @foreach($carreras as $carrera)
                                <option value="{{ $carrera->id_carrera }}" {{ old('id_carrera', $jefeCarrera->id_carrera) == $carrera->id_carrera ? 'selected' : '' }}>
                                    {{ $carrera->nombre }} ({{ $carrera->areaAcademica->nombre_area_academica ?? '' }})
                                </option>
                            @endforeach
                        </select>
                        @error('id_carrera')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-end gap-2 mt-6">
                    <x-button variant="secondary" type="button" onclick="window.location='{{ route('jefes-carrera.index') }}'">Cancelar</x-button>
                    <x-button variant="success">Actualizar Jefe de Carrera</x-button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
