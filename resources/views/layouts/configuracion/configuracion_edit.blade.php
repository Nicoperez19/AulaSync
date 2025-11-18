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
                    @if(str_starts_with($configuracion->clave, 'logo_institucional_'))
                        @php
                            $idSede = str_replace('logo_institucional_', '', $configuracion->clave);
                            $sede = \App\Models\Sede::find($idSede);
                        @endphp
                        <p class="text-sm text-gray-600">Logo institucional de la sede: <strong>{{ $sede ? $sede->nombre_sede : $idSede }}</strong></p>
                    @elseif(str_starts_with($configuracion->clave, 'correo_administrativo_'))
                        @php
                            $idFacultad = str_replace('correo_administrativo_', '', $configuracion->clave);
                            $facultad = \App\Models\Facultad::find($idFacultad);
                        @endphp
                        <p class="text-sm text-gray-600">Correo administrativo de la escuela: <strong>{{ $facultad ? $facultad->nombre_facultad : $idFacultad }}</strong></p>
                    @elseif(str_starts_with($configuracion->clave, 'nombre_remitente_'))
                        @php
                            $idFacultad = str_replace('nombre_remitente_', '', $configuracion->clave);
                            $facultad = \App\Models\Facultad::find($idFacultad);
                        @endphp
                        <p class="text-sm text-gray-600">Nombre del remitente para la escuela: <strong>{{ $facultad ? $facultad->nombre_facultad : $idFacultad }}</strong></p>
                    @endif
                </div>

                @if(str_starts_with($configuracion->clave, 'logo_institucional_'))
                    <div class="space-y-2">
                        <x-form.label for="logo" value="Logo Institucional *" />
                        @if($configuracion->valor)
                            <div class="p-4 mb-3 border border-gray-200 rounded-lg bg-gray-50">
                                <img src="{{ asset('storage/images/logo/' . $configuracion->valor) }}" alt="Logo actual" class="max-h-32 mb-2">
                                <p class="text-sm font-medium text-gray-700">Logo actual</p>
                                <p class="text-xs text-gray-500">{{ $configuracion->valor }}</p>
                            </div>
                        @endif
                        <x-form.input id="logo" name="logo" type="file" class="w-full @error('logo') border-red-500 @enderror" accept="image/*" />
                        <p class="text-xs text-gray-500">Formatos permitidos: JPEG, PNG, JPG, GIF, SVG (máx. 2MB)</p>
                        <p class="text-xs text-gray-500">Deje vacío si no desea cambiar el logo actual.</p>
                        @error('logo')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @elseif(str_starts_with($configuracion->clave, 'correo_administrativo_'))
                    @php
                        $idFacultad = str_replace('correo_administrativo_', '', $configuracion->clave);
                        $nombreRemitenteConfig = \App\Models\Configuracion::where('clave', "nombre_remitente_{$idFacultad}")->first();
                    @endphp
                    <div class="space-y-2">
                        <x-form.label for="valor" value="Correo Electrónico *" />
                        <x-form.input id="valor" name="valor" type="email" class="w-full @error('valor') border-red-500 @enderror" value="{{ old('valor', $configuracion->valor) }}" required />
                        <p class="text-xs text-gray-500">Este correo será usado como remitente en notificaciones de la escuela</p>
                        @error('valor')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="nombre_remitente" value="Nombre del Remitente" />
                        <x-form.input id="nombre_remitente" name="nombre_remitente" type="text" class="w-full @error('nombre_remitente') border-red-500 @enderror" 
                            value="{{ old('nombre_remitente', $nombreRemitenteConfig ? $nombreRemitenteConfig->valor : '') }}" />
                        <p class="text-xs text-gray-500">Nombre que aparecerá como remitente en los correos</p>
                        @error('nombre_remitente')
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
