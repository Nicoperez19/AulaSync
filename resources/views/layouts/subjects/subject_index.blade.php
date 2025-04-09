<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight" style="font-style: oblique;">
                {{ __('Asignaturas') }}
            </h2>
        </div>
    </x-slot>

    <div class="flex justify-end mb-4">
        <x-button x-on:click.prevent="$dispatch('open-modal', 'add-asignatura')" variant="primary" class="max-w-xs gap-2">
            <x-icons.add class="w-6 h-6" aria-hidden="true" />
        </x-button>
    </div>

    @livewire('subjects-table') 

    <x-modal name="add-asignatura" :show="$errors->any()" focusable>
        <form method="POST" action="{{ route('asignaturas.store') }}">
            @csrf
            <div class="p-6 space-y-6">
                <!-- ID -->
                <div class="space-y-2">
                    <x-form.label for="id_asignatura" value="ID Asignatura" />
                    <x-form.input id="id_asignatura" name="id_asignatura" type="text" class="w-full" required maxlength="20" />
                </div>

                <!-- Nombre -->
                <div class="space-y-2">
                    <x-form.label for="nombre" value="Nombre" />
                    <x-form.input id="nombre" name="nombre" type="text" class="w-full" required maxlength="100" />
                </div>

                <!-- Horas Directas -->
                <div class="space-y-2">
                    <x-form.label for="horas_directas" value="Horas Directas" />
                    <x-form.input id="horas_directas" name="horas_directas" type="number" class="w-full" required />
                </div>

                <!-- Horas Indirectas -->
                <div class="space-y-2">
                    <x-form.label for="horas_indirectas" value="Horas Indirectas" />
                    <x-form.input id="horas_indirectas" name="horas_indirectas" type="number" class="w-full" required />
                </div>

                <!-- Área de Conocimiento -->
                <div class="space-y-2">
                    <x-form.label for="area_conocimiento" value="Área de Conocimiento" />
                    <x-form.input id="area_conocimiento" name="area_conocimiento" type="text" class="w-full" required maxlength="100" />
                </div>

                <!-- Periodo -->
                <div class="space-y-2">
                    <x-form.label for="periodo" value="Periodo" />
                    <x-form.input id="periodo" name="periodo" type="text" class="w-full" required maxlength="20" />
                </div>

                <!-- Usuario -->
                <div class="space-y-2">
                    <x-form.label for="id_usuario" value="Docente Responsable" />
                    <select name="id_usuario" id="id_usuario" class="w-full border-gray-300 rounded-md">
                        @foreach ($usuarios as $usuario)
                            <option value="{{ $usuario->id }}">{{ $usuario->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Carrera -->
                <div class="space-y-2">
                    <x-form.label for="id_carrera" value="Carrera" />
                    <select name="id_carrera" id="id_carrera" class="w-full border-gray-300 rounded-md">
                        @foreach ($carreras as $carrera)
                            <option value="{{ $carrera->id_carrera }}">{{ $carrera->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-end pt-4">
                    <x-button class="justify-center w-full gap-2">
                        <x-heroicon-o-plus-circle class="w-6 h-6" />
                        {{ __('Guardar Asignatura') }}
                    </x-button>
                </div>
            </div>
        </form>
    </x-modal>
</x-app-layout>
