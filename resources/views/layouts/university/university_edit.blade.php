<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight" style="font-style: oblique;">
                {{ __('Universidades/ Universidad/ Editar') }}
            </h2>
        </div>
    </x-slot>

    <form action="{{ route('universities.update', $universidad->id_universidad) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
    
        <div class="grid gap-4 p-4">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <x-form.label for="id_universidad" :value="__('ID Universidad')" />
                    <x-form.input withicon id="id_universidad" class="block w-full" type="text"
                        name="id_universidad" value="{{ old('id_universidad', $universidad->id_universidad) }}" required />
                </div>
    
                <div>
                    <x-form.label for="nombre_universidad" :value="__('Nombre Universidad')" />
                    <x-form.input withicon id="nombre_universidad" class="block w-full" type="text"
                        name="nombre_universidad" value="{{ old('nombre_universidad', $universidad->nombre_universidad) }}" required />
                </div>
            </div>
    
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <x-form.label for="direccion_universidad" :value="__('Dirección Universidad')" />
                    <x-form.input withicon id="direccion_universidad" class="block w-full" type="text"
                        name="direccion_universidad" value="{{ old('direccion_universidad', $universidad->direccion_universidad) }}" required />
                </div>
    
                <div>
                    <x-form.label for="telefono_universidad" :value="__('Teléfono Universidad')" />
                    <x-form.input withicon id="telefono_universidad" class="block w-full" type="text"
                        name="telefono_universidad" value="{{ old('telefono_universidad', $universidad->telefono_universidad) }}" required />
                </div>
            </div>
    
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <x-form.label for="comunas_id" :value="__('Comuna')" />
                    <select name="comunas_id" id="comunas_id" class="block w-full">
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
                    <input type="file" name="imagen_logo" id="imagen_logo"
                        class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50"
                        accept="image/*">
                    @if ($universidad->imagen_logo)
                        <img src="{{ asset('images/logos_universidad/' . $universidad->imagen_logo) }}" alt="Logo"
                            class="mt-2 max-h-40" id="logoPreview">
                    @else
                        <img src="" alt="Logo" class="mt-2 max-h-40" id="logoPreview" style="display:none;">
                    @endif
                </div>
            </div>
    
            <div class="flex justify-end mt-6">
                <x-button>{{ __('Guardar Cambios') }}</x-button>
            </div>
        </div>
    </form>
    

    <script>
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
