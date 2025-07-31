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
        </div>
    </x-slot>

    <div class="p-6 bg-white rounded-lg shadow-lg">
        <livewire:universitys-table />
    </div>

    <!-- Modal para agregar universidad -->
    <x-modal name="add-university" :show="$errors->any()" focusable>
        @slot('title')
        <div class="relative flex items-center justify-between p-2 bg-red-700">
            <div class="flex items-center gap-3">
                <div class="p-4 bg-red-100 rounded-full">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-white">
                    Agregar Universidad
                </h2>
            </div>
            <button @click="show = false"
                class="ml-2 text-2xl font-bold text-white hover:text-gray-200">&times;</button>
            <!-- Círculos decorativos -->
            <span
                class="absolute top-0 left-0 w-32 h-32 -translate-x-1/2 -translate-y-1/2 bg-white rounded-full pointer-events-none bg-opacity-10"></span>
            <span
                class="absolute top-0 right-0 w-32 h-32 translate-x-1/2 -translate-y-1/2 bg-white rounded-full pointer-events-none bg-opacity-10"></span>
        </div>
        @endslot

        <form method="POST" action="{{ route('universities.store') }}" enctype="multipart/form-data" class="p-6">
            @csrf
            <div class="grid gap-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <x-form.label for="id_universidad" value="ID Universidad *" />
                        <x-form.input id="id_universidad" name="id_universidad" type="text"
                            class="w-full @error('id_universidad') border-red-500 @enderror" required maxlength="255"
                            placeholder="Ej: UCH" value="{{ old('id_universidad') }}" />
                        @error('id_universidad')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="nombre_universidad" value="Nombre Universidad *" />
                        <x-form.input id="nombre_universidad" name="nombre_universidad" type="text"
                            class="w-full @error('nombre_universidad') border-red-500 @enderror" required
                            maxlength="255" placeholder="Ej: Universidad de Chile"
                            value="{{ old('nombre_universidad') }}" />
                        @error('nombre_universidad')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <x-form.label for="direccion_universidad" value="Dirección Universidad *" />
                        <x-form.input id="direccion_universidad" name="direccion_universidad" type="text"
                            class="w-full @error('direccion_universidad') border-red-500 @enderror" required
                            maxlength="255" placeholder="Ej: Av. Libertador Bernardo O'Higgins 1058"
                            value="{{ old('direccion_universidad') }}" />
                        @error('direccion_universidad')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="telefono_universidad" value="Teléfono Universidad *" />
                        <x-form.input id="telefono_universidad" name="telefono_universidad" type="text"
                            class="w-full @error('telefono_universidad') border-red-500 @enderror" required
                            maxlength="15" pattern="[0-9+]+" placeholder="Ej: +56229781234"
                            value="{{ old('telefono_universidad') }}" />
                        @error('telefono_universidad')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <x-form.label for="comunas_id" value="Comuna *" />
                        <select name="comunas_id" id="comunas_id"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-m @error('comunas_id') border-red-500 @enderror"
                            required>
                            <option value="" disabled selected>{{ __('Seleccionar Comuna') }}</option>
                            @foreach($comunas as $comuna)
                                <option value="{{ $comuna->id }}" {{ old('comunas_id') == $comuna->id ? 'selected' : '' }}>
                                    {{ $comuna->nombre_comuna }}
                                </option>
                            @endforeach
                        </select>
                        @error('comunas_id')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="imagen_logo" value="Logo Universidad" />
                        <x-form.input id="imagen_logo" name="imagen_logo" type="file"
                            class="w-full @error('imagen_logo') border-red-500 @enderror" accept="image/*" />
                        <p class="text-xs text-gray-500">Formatos: JPG, JPEG, PNG, GIF. Máximo 2MB</p>
                        @error('imagen_logo')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-end mt-6">
                    <x-button variant="success">{{ __('Crear Universidad') }}</x-button>
                </div>
            </div>
        </form>
    </x-modal>


</x-app-layout>