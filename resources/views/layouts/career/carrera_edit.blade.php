<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight" style="font-style: oblique;">
                {{ __('Universidad/ Carrera/ Editar') }}
            </h2>
        </div>
    </x-slot>

    <form action="{{ route('careers.update', $carrera->id_carrera) }}" method="POST" enctype="multipart/form-data">
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

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <x-form.label for="id_facultad" :value="__('Facultad')" />
                    <select name="id_facultad" id="id_facultad" class="block w-full" required>
                        @foreach ($facultades as $facultad)
                            <option value="{{ $facultad->id_facultad }}"
                                {{ $facultad->id_facultad == $carrera->id_facultad ? 'selected' : '' }}>
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
