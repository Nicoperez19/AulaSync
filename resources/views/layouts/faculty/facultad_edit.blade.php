<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight" style="font-style: oblique;">
                {{ __('Universidad/ Facultad/ Editar') }}
            </h2>
        </div>
    </x-slot>

    <form action="{{ route('faculties.update', $facultad->id_facultad) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="grid gap-4 p-4">
            <!-- Datos Generales -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                <div>
                    <x-form.label for="id_facultad" :value="__('ID Facultad')" />
                    <x-form.input withicon id="id_facultad" class="block w-full" type="text" name="id_facultad"
                        value="{{ old('id_facultad', $facultad->id_facultad) }}" required />
                </div>

                <div>
                    <x-form.label for="nombre_facultad" :value="__('Nombre Facultad')" />
                    <x-form.input withicon id="nombre_facultad" class="block w-full" type="text"
                        name="nombre_facultad" value="{{ old('nombre_facultad', $facultad->nombre_facultad) }} "
                        required />
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <x-form.label for="ubicacion_facultad" :value="__('Ubicación Universidad')" />
                    <x-form.input withicon id="ubicacion_facultad" class="block w-full" type="text"
                        name="ubicacion_facultad" value="{{ old('ubicacion_facultad', $facultad->ubicacion_facultad) }}"
                        required />
                </div>

                <div>
                    <x-form.label for="id_universidad" :value="__('Universidad')" />
                    <select name="id_universidad" id="id_universidad" class="block w-full">
                        @foreach ($universidades as $univesidad)
                            <option value="{{ $univesidad->id_universidad }}"
                                {{ $univesidad->id_universidad == $facultad->id_universidad ? 'selected' : '' }}>
                                {{ $univesidad->nombre_universidad }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <x-form.label for="logo_facultad" :value="__('Logo')" />
                    <input type="file" name="logo_facultad" id="logo_facultad"
                        class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50"
                        accept="image/*" onchange="previewImage(event)">
                    @if ($facultad->logo_facultad)
                        <img src="{{ asset('images/logo_facultad/' . $facultad->logo_facultad) }}" alt="Logo"
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
