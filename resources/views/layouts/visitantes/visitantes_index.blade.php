<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-users"></i>
                </div>

                <div>
                    <h2 class="text-2xl font-bold leading-tight">Visitantes</h2>
                    <p class="text-sm text-gray-500">Administra los visitantes registrados en el sistema</p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="p-6 bg-white rounded-lg shadow-lg">
        <div class="flex items-center justify-end mb-6">
            <x-button variant="add" class="max-w-xs gap-2" x-on:click.prevent="$dispatch('open-modal', 'add-visitante')">
                <x-icons.add class="w-6 h-6" aria-hidden="true" />
                Agregar Visitante
            </x-button>
        </div>
        @livewire('visitantes-table')
  <!-- Modal para agregar visitante -->
  <x-modal name="add-visitante" :show="$errors->any()" focusable>
        @slot('title')
            <div class="relative flex items-center justify-between p-2 bg-blue-700">
                <div class="flex items-center gap-3">
                    <div class="p-4 bg-blue-100 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-white">
                        Agregar Visitante
                    </h2>
                </div>
                <button @click="show = false" class="ml-2 text-2xl font-bold text-white hover:text-gray-200">&times;</button>
                <!-- Círculos decorativos -->
                <span class="absolute top-0 left-0 w-32 h-32 -translate-x-1/2 -translate-y-1/2 bg-white rounded-full pointer-events-none bg-opacity-10"></span>
                <span class="absolute top-0 right-0 w-32 h-32 translate-x-1/2 -translate-y-1/2 bg-white rounded-full pointer-events-none bg-opacity-10"></span>
            </div>
        @endslot

            <form method="POST" action="{{ route('visitantes.add') }}" class="p-6">
                @csrf
                <div class="grid gap-4">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="space-y-2">
                            <x-form.label for="run_solicitante" value="RUN Solicitante *" />
                            <x-form.input id="run_solicitante" name="run_solicitante" type="text"
                                class="w-full @error('run_solicitante') border-red-500 @enderror" required maxlength="255"
                                placeholder="Ej: 12345678-9" value="{{ old('run_solicitante') }}" />
                            @error('run_solicitante')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <x-form.label for="nombre" value="Nombre *" />
                            <x-form.input id="nombre" name="nombre" type="text"
                                class="w-full @error('nombre') border-red-500 @enderror" required maxlength="255"
                                placeholder="Ej: Juan Pérez González" value="{{ old('nombre') }}" />
                            @error('nombre')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="space-y-2">
                            <x-form.label for="correo" value="Correo *" />
                            <x-form.input id="correo" name="correo" type="email"
                                class="w-full @error('correo') border-red-500 @enderror" required maxlength="255"
                                placeholder="Ej: juan.perez@email.com" value="{{ old('correo') }}" />
                            @error('correo')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <x-form.label for="telefono" value="Teléfono" />
                            <x-form.input id="telefono" name="telefono" type="text"
                                class="w-full @error('telefono') border-red-500 @enderror" maxlength="255"
                                placeholder="Ej: +56912345678" value="{{ old('telefono') }}" />
                            @error('telefono')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="space-y-2">
                            <x-form.label for="tipo_solicitante" value="Tipo de Solicitante *" />
                            <select name="tipo_solicitante" id="tipo_solicitante"
                                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-m @error('tipo_solicitante') border-red-500 @enderror"
                                required>
                                <option value="" disabled selected>{{ __('Seleccionar Tipo') }}</option>
                                <option value="estudiante" {{ old('tipo_solicitante') == 'estudiante' ? 'selected' : '' }}>Estudiante</option>
                                <option value="personal" {{ old('tipo_solicitante') == 'personal' ? 'selected' : '' }}>Personal</option>
                                <option value="visitante" {{ old('tipo_solicitante') == 'visitante' ? 'selected' : '' }}>Visitante</option>
                                <option value="otro" {{ old('tipo_solicitante') == 'otro' ? 'selected' : '' }}>Otro</option>
                            </select>
                            @error('tipo_solicitante')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <x-form.label for="activo" value="Estado" />
                            <select name="activo" id="activo"
                                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-m @error('activo') border-red-500 @enderror">
                                <option value="1" {{ old('activo', '1') == '1' ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ old('activo') == '0' ? 'selected' : '' }}>Inactivo</option>
                            </select>
                            @error('activo')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                <div class="flex justify-end mt-6">
                    <x-button variant="success">{{ __('Crear Visitante') }}</x-button>
                </div>
            </div>
        </form>
    </x-modal>
    </div>

   <script>
    function deleteVisitante(id, nombre) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción no se puede deshacer",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }
   </script>

</x-app-layout>