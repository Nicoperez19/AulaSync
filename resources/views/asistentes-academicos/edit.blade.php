<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-user-graduate"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold leading-tight">Editar Asistente Académico</h2>
                    <p class="text-sm text-gray-500">Modifica la información del asistente académico</p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="p-6 bg-white rounded-lg shadow-lg">
        <form method="POST" action="{{ route('asistentes-academicos.update', $asistenteAcademico->id) }}">
            @csrf
            @method('PUT')

            <div class="grid gap-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <x-form.label for="nombre" value="Nombre *" />
                        <x-form.input id="nombre" name="nombre" type="text" class="w-full @error('nombre') border-red-500 @enderror" required maxlength="100" value="{{ old('nombre', $asistenteAcademico->nombre) }}" />
                        @error('nombre')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="email" value="Email *" />
                        <x-form.input id="email" name="email" type="email" class="w-full @error('email') border-red-500 @enderror" required value="{{ old('email', $asistenteAcademico->email) }}" />
                        @error('email')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500">Este correo se usará para enviar comunicaciones oficiales</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <x-form.label for="nombre_remitente" value="Nombre Remitente" />
                        <x-form.input id="nombre_remitente" name="nombre_remitente" type="text" class="w-full @error('nombre_remitente') border-red-500 @enderror" maxlength="150" placeholder="Ej: Asistencia Académica - Escuela de Ingeniería" value="{{ old('nombre_remitente', $asistenteAcademico->nombre_remitente) }}" />
                        @error('nombre_remitente')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500">Nombre formal que aparecerá en los correos enviados</p>
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="telefono" value="Teléfono" />
                        <x-form.input id="telefono" name="telefono" type="text" class="w-full @error('telefono') border-red-500 @enderror" maxlength="20" value="{{ old('telefono', $asistenteAcademico->telefono) }}" />
                        @error('telefono')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="id_area_academica" value="Escuela *" />
                        <select name="id_area_academica" id="id_area_academica" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-m @error('id_area_academica') border-red-500 @enderror" required>
                            <option value="" disabled>{{ __('Seleccionar Escuela') }}</option>
                            @foreach($escuelas as $escuela)
                                <option value="{{ $escuela->id_area_academica }}" {{ old('id_area_academica', $asistenteAcademico->id_area_academica) == $escuela->id_area_academica ? 'selected' : '' }}>
                                    {{ $escuela->nombre_area_academica }} ({{ $escuela->facultad->nombre_facultad ?? '' }})
                                </option>
                            @endforeach
                        </select>
                        @error('id_area_academica')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-end gap-2 mt-6">
                    <x-button variant="secondary" type="button" onclick="window.location='{{ route('asistentes-academicos.index') }}'">Cancelar</x-button>
                    <x-button variant="success">Actualizar Asistente Académico</x-button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
