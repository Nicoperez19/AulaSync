<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight" style="font-style: oblique;">
                {{ __('Usuarios / Usuarios') }}
            </h2>
        </div>
    </x-slot>

    <div class="p-6 bg-white rounded-lg shadow-lg">
        <div class="flex justify-end mb-4">
            
        </div>

        <div class="mt-4 flex items-center justify-between">
            <!-- Buscador pequeño a la izquierda -->
            <div class="w-2/3">
                <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Buscar por RUN o Nombre"
                    class="px-4 py-2 border rounded dark:bg-gray-700 dark:text-white w-full">
            </div>
            <x-button target="_blank" variant="primary" class="max-w-xs gap-2"
            x-on:click="$dispatch('open-modal', 'add-user')">
            <x-icons.add class="w-6 h-6" aria-hidden="true" />
        </x-button>
           
        </div>

        <livewire:users-table />

        <x-modal name="add-user" :show="$errors->any()" focusable>
            <form method="POST" action="{{ route('users.add') }}">
                @csrf
                <div class="grid gap-6 p-6">

                    <!-- Campo RUN -->
                    <div class="space-y-2">
                        <x-form.label for="run_add" :value="__('RUN')" class="text-left" />
                        <x-form.input id="run_add" class="block w-full sm:w-1/2"
                            type="text" name="run"
                            value="{{ old('run', '') }}" placeholder="RUN" required />
                        @error('run')
                            <div class="mt-1 text-xs text-red-500">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Campo Nombre -->
                    <div class="space-y-2">
                        <x-form.label for="name_add" :value="__('Nombre')" class="text-left" />
                        <x-form.input id="name_add" class="block w-full sm:w-1/2"
                            type="text" name="name"
                            value="{{ old('name', '') }}" placeholder="Nombre" required />
                        @error('name')
                            <div class="mt-1 text-xs text-red-500">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Campo Correo -->
                    <div class="space-y-2">
                        <x-form.label for="email_add" :value="__('Correo')" class="text-left" />
                        <x-form.input id="email_add" class="block w-full sm:w-1/2"
                            type="email" name="email"
                            value="{{ old('email', '') }}" placeholder="Correo" required />
                        @error('email')
                            <div class="mt-1 text-xs text-red-500">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Campo Celular -->
                    <div class="space-y-2">
                        <x-form.label for="celular_add" :value="__('Celular')" class="text-left" />
                        <x-form.input id="celular_add" class="block w-full sm:w-1/2"
                            type="text" name="celular"
                            value="{{ old('celular', '') }}" placeholder="Celular" />
                        @error('celular')
                            <div class="mt-1 text-xs text-red-500">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Campo Dirección -->
                    <div class="space-y-2">
                        <x-form.label for="direccion_add" :value="__('Dirección')" class="text-left" />
                        <x-form.input id="direccion_add" class="block w-full sm:w-1/2"
                            type="text" name="direccion"
                            value="{{ old('direccion', '') }}" placeholder="Dirección" />
                        @error('direccion')
                            <div class="mt-1 text-xs text-red-500">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Campo Fecha de Nacimiento -->
                    <div class="space-y-2">
                        <x-form.label for="fecha_nacimiento_add" :value="__('Fecha de Nacimiento')" class="text-left" />
                        <x-form.input id="fecha_nacimiento_add" class="block w-full sm:w-1/2"
                            type="date" name="fecha_nacimiento"
                            value="{{ old('fecha_nacimiento', '') }}" />
                        @error('fecha_nacimiento')
                            <div class="mt-1 text-xs text-red-500">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Año de Ingreso como select --}}
                    <div class="space-y-2">
                        <x-form.label for="anio_ingreso_add" :value="__('Año de Ingreso')" class="text-left" />
                        <select id="anio_ingreso_add" name="anio_ingreso" class="block w-full sm:w-1/2" required>
                            <option value="" disabled selected>Seleccione un año</option>
                            @foreach ($years as $year)
                                <option value="{{ $year }}"
                                    {{ old('anio_ingreso') == $year ? 'selected' : '' }}>{{ $year }}</option>
                            @endforeach
                        </select>
                        @error('anio_ingreso')
                            <div class="mt-1 text-xs text-red-500">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Botón de Enviar -->
                    <div>
                        <x-button variant="primary" class="justify-center max-w-xs gap-2">
                            <x-heroicon-o-user-add class="w-6 h-6" aria-hidden="true" />
                            <span>{{ __('Agregar') }}</span>
                        </x-button>
                    </div>
                </div>
            </form>
        </x-modal>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmDelete(userRun) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "¡No podrás revertir esta acción!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + userRun).submit();
                }
            });
        }

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

</x-app-layout>
