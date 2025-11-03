<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-cog"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold leading-tight">Editar Configuración</h2>
                    <p class="text-sm text-gray-500">Modifica la configuración del sistema</p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="p-6 bg-white rounded-lg shadow-lg">
        <form method="POST" action="{{ route('configuracion.update', $configuracion->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid gap-4">
                <div class="space-y-2">
                    <x-form.label for="clave" value="Clave" />
                    <x-form.input id="clave" name="clave" type="text" class="w-full bg-gray-100" value="{{ $configuracion->clave }}" readonly />
                </div>

                @if($configuracion->clave === 'logo_institucional')
                    <div class="space-y-2">
                        <x-form.label for="logo" value="Logo Institucional *" />
                        @if($configuracion->valor)
                            <div class="mb-2">
                                <img src="{{ asset('storage/images/logo/' . $configuracion->valor) }}" alt="Logo actual" class="h-20">
                                <p class="text-sm text-gray-600">Logo actual</p>
                            </div>
                        @endif
                        <x-form.input id="logo" name="logo" type="file" class="w-full @error('logo') border-red-500 @enderror" accept="image/*" />
                        @error('logo')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @else
                    <div class="space-y-2">
                        <x-form.label for="valor" value="Valor *" />
                        <textarea id="valor" name="valor" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('valor') border-red-500 @enderror" required>{{ old('valor', $configuracion->valor) }}</textarea>
                        @error('valor')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <div class="space-y-2">
                    <x-form.label for="descripcion" value="Descripción" />
                    <x-form.input id="descripcion" name="descripcion" type="text" class="w-full @error('descripcion') border-red-500 @enderror" maxlength="255" value="{{ old('descripcion', $configuracion->descripcion) }}" />
                    @error('descripcion')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-2 mt-6">
                    <x-button variant="secondary" type="button" onclick="window.location='{{ route('configuracion.index') }}'">Cancelar</x-button>
                    <x-button variant="success">Actualizar Configuración</x-button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
