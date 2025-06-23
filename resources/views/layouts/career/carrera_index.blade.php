<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight" style="font-style: oblique;">
                {{ __('Universidad/ Carrera') }}
            </h2>
        </div>
    </x-slot>

    <div class="flex justify-end mb-4">
        <x-button variant="primary" class="justify-end max-w-xs gap-2"
            x-on:click.prevent="$dispatch('open-modal', 'add-career')">
            <x-icons.add class="w-6 h-6" aria-hidden="true" />
        </x-button>
    </div>

    @livewire('careers-table')

    <div class="space-y-1">
        <x-modal name="add-career" :show="$errors->any()" focusable>
            <form method="POST" action="{{ route('careers.add') }}">
                @csrf

                <div class="grid gap-6 p-6">
                    <div class="space-y-2">
                        <x-form.label for="id_carrera" :value="__('ID Carrera')" class="text-left" />
                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <x-heroicon-o-user aria-hidden="true" class="w-5 h-5" />
                            </x-slot>
                            <x-form.input id="id_carrera" class="block w-full" type="text" name="id_carrera"
                                :value="old('id_carrera')" required autofocus placeholder="{{ __('ID Carrera') }}" />
                        </x-form.input-with-icon-wrapper>
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="nombre" :value="__('Nombre Carrera')" class="text-left" />
                        <x-form.input-with-icon-wrapper>
                            <x-slot name="icon">
                                <x-heroicon-o-academic-cap aria-hidden="true" class="w-5 h-5" />
                            </x-slot>
                            <x-form.input id="nombre" class="block w-full" type="text" name="nombre"
                                :value="old('nombre')" required placeholder="{{ __('Nombre Carrera') }}" />
                        </x-form.input-with-icon-wrapper>
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="id_universidad" :value="__('Universidad')" class="text-left" />
                        <select id="selectedUniversidad" name="id_universidad" class="w-full p-2 border rounded">
                            <option value="">Seleccione</option>
                            @foreach ($universidades as $uni)
                                <option value="{{ $uni->id_universidad }}">{{ $uni->nombre_universidad }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <x-form.label for="id_facultad" :value="__('Facultad')" class="text-left" />
                        <select id="selectedFacultad" name="id_facultad" class="w-full p-2 border rounded" disabled>
                            <option value="">Seleccione</option>
                        </select>

                    </div>

                    <div>
                        <x-button class="justify-center w-full gap-2">
                            <x-heroicon-o-user-add class="w-6 h-6" aria-hidden="true" />
                            <span>{{ __('Agregar Carrera') }}</span>
                        </x-button>
                    </div>
                </div>
            </form>
        </x-modal>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        @if (session('success'))
            Swal.fire({
                title: '¡Éxito!',
                text: '{{ session('success') }}',
                icon: 'success',
                confirmButtonText: 'Aceptar'
            });
        @endif

        @if (session('error'))
            Swal.fire({
                title: '¡Error!',
                text: '{{ session('error') }}',
                icon: 'error',
                confirmButtonText: 'Aceptar'
            });
        @endif
    </script>
    <script>
        const universidades = @json($universidades);
        const facultades = @json($facultades);

        document.getElementById('selectedUniversidad').addEventListener('change', function() {
            const universidadId = this.value;
            const facultadSelect = document.getElementById('selectedFacultad');

            // Limpiar opciones actuales
            facultadSelect.innerHTML = '<option value="">Seleccione</option>';

            if (universidadId) {
                const facultadesFiltradas = facultades.filter(f => f.id_universidad == universidadId);

                facultadesFiltradas.forEach(fac => {
                    const option = document.createElement('option');
                    option.value = fac.id_facultad;
                    option.text = fac.nombre_facultad;
                    facultadSelect.appendChild(option);
                });

                facultadSelect.disabled = false;
            } else {
                facultadSelect.disabled = true;
            }
        });
    </script>
</x-app-layout>
