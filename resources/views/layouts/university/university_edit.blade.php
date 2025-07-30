<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-university"></i>
                </div>

                <div>
                    <h2 class="text-2xl font-bold leading-tight">Universidades</h2>
                    <p class="text-sm text-gray-500">Administra las universidades registradas en el sistema</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <x-button href="{{ route('universities.index') }}" 
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
        <form id="edit-university-form" action="{{ route('universities.update', $universidad->id_universidad) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="grid gap-4 p-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <x-form.label for="id_universidad" :value="__('ID Universidad')" />
                        <x-form.input id="id_universidad" class="block w-full" type="text" name="id_universidad"
                            value="{{ old('id_universidad', $universidad->id_universidad) }}" required maxlength="255" />
                    </div>
                    <div>
                        <x-form.label for="nombre_universidad" :value="__('Nombre Universidad')" />
                        <x-form.input id="nombre_universidad" class="block w-full" type="text" name="nombre_universidad"
                            value="{{ old('nombre_universidad', $universidad->nombre_universidad) }}" required maxlength="255" />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <x-form.label for="direccion_universidad" :value="__('Dirección Universidad')" />
                        <x-form.input id="direccion_universidad" class="block w-full" type="text" name="direccion_universidad"
                            value="{{ old('direccion_universidad', $universidad->direccion_universidad) }}" required maxlength="255" />
                    </div>
                    <div>
                        <x-form.label for="telefono_universidad" :value="__('Teléfono Universidad')" />
                        <x-form.input id="telefono_universidad" class="block w-full" type="text" name="telefono_universidad"
                            value="{{ old('telefono_universidad', $universidad->telefono_universidad) }}" required maxlength="15" pattern="[0-9+]+" />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <x-form.label for="comunas_id" :value="__('Comuna')" />
                        <select name="comunas_id" id="comunas_id" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-m" required>
                            <option value="" disabled>{{ __('Seleccionar Comuna') }}</option>
                            @foreach ($comunas as $comuna)
                                <option value="{{ $comuna->id }}"
                                    {{ $universidad->comunas_id == $comuna->id ? 'selected' : '' }}>
                                    {{ $comuna->nombre_comuna }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-form.label for="imagen_logo" :value="__('Logo')" />
                        <x-form.input id="imagen_logo" name="imagen_logo" type="file"
                            class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50"
                            accept="image/*" onchange="previewImage(event)">
                        <p class="text-xs text-gray-500 mt-1">Formatos: JPG, JPEG, PNG, GIF. Máximo 2MB</p>
                        @if ($universidad->imagen_logo)
                            <div class="mt-2">
                                <p class="text-sm text-gray-600 mb-2">Logo actual:</p>
                                <img src="{{ asset('images/logo_universidad/' . $universidad->imagen_logo) }}" alt="Logo actual"
                                    class="max-h-20 rounded border">
                            </div>
                        @endif
                        <div class="mt-2">
                            <p class="text-sm text-gray-600 mb-2">Vista previa:</p>
                            <img src="" alt="Vista previa" class="max-h-20 rounded border" id="logoPreview" style="display:none;">
                        </div>
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
            const form = document.getElementById('edit-university-form');
            
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                Swal.fire({
                    title: '¿Seguro de guardar los cambios?',
                    text: "Estás a punto de actualizar la información de la universidad.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, guardar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });

        function previewImage(event) {
            var file = event.target.files[0];
            var reader = new FileReader();

            reader.onload = function(e) {
                var preview = document.getElementById('logoPreview');
                preview.src = e.target.result;
                preview.style.display = 'block'; 
            }

            if (file) {
                reader.readAsDataURL(file);
            }
        }
    </script>
</x-app-layout>
