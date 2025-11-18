<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-cog"></i>
                </div>

                <div>
                    <h2 class="text-2xl font-bold leading-tight">Configuración del Sistema</h2>
                    <p class="text-sm text-gray-500">Administra la configuración general del sistema (logo institucional, etc.)</p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="p-6 bg-white rounded-lg shadow-lg">

        <div class="flex items-center justify-between mb-6">
            <div class="w-2/3">
                <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Buscar por Clave o Descripción"
                    class="w-full px-4 py-2 border rounded dark:bg-gray-700 dark:text-white">
            </div>
            <x-button variant="add" class="max-w-xs gap-2" x-on:click.prevent="$dispatch('open-modal', 'add-configuracion')">
                <x-icons.add class="w-6 h-6" aria-hidden="true" />
                Agregar Configuración
            </x-button>
        </div>
        <livewire:configuracion-table />

        <x-modal name="add-configuracion" :show="$errors->any()" focusable>
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
                        Agregar Configuración
                    </h2>
                </div>
                <button @click="show = false"
                    class="ml-2 text-2xl font-bold text-white hover:text-gray-200">&times;</button>
            </div>
            @endslot

            <form method="POST" action="{{ route('configuracion.store') }}" class="p-6" enctype="multipart/form-data">
                @csrf

                <div class="grid gap-4">
                    <div class="space-y-2">
                        <x-form.label for="clave" value="Clave *" />
                        <select id="clave" name="clave" 
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('clave') border-red-500 @enderror" 
                            required onchange="toggleLogoUpload()">
                            <option value="">Seleccione una configuración</option>
                            <option value="logo_institucional" {{ old('clave') == 'logo_institucional' ? 'selected' : '' }}>Logo Institucional</option>
                            <option value="other" {{ old('clave') && old('clave') !== 'logo_institucional' ? 'selected' : '' }}>Otra configuración</option>
                        </select>
                        @error('clave')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="sede-select-section" class="space-y-2" style="display: none;">
                        <x-form.label for="id_sede" value="Sede *" />
                        <select id="id_sede" name="id_sede" 
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('id_sede') border-red-500 @enderror">
                            <option value="">Seleccione una sede</option>
                            @foreach(\App\Models\Sede::all() as $sede)
                                <option value="{{ $sede->id_sede }}" {{ old('id_sede') == $sede->id_sede ? 'selected' : '' }}>
                                    {{ $sede->nombre_sede }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_sede')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="other-clave-input" class="space-y-2" style="display: none;">
                        <x-form.label for="clave_custom" value="Nombre de la clave *" />
                        <x-form.input id="clave_custom" name="clave_custom" type="text"
                            class="w-full @error('clave_custom') border-red-500 @enderror" maxlength="100"
                            placeholder="Ej: nombre_institucion" value="{{ old('clave_custom') }}" />
                        @error('clave_custom')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="logo-upload-section" class="space-y-2" style="display: none;">
                        <x-form.label for="logo_create" value="Logo Institucional *" />
                        <x-form.input id="logo_create" name="logo" type="file" 
                            class="w-full @error('logo') border-red-500 @enderror" accept="image/*" />
                        <p class="text-xs text-gray-500">Formatos permitidos: JPEG, PNG, JPG, GIF, SVG (máx. 2MB)</p>
                        @error('logo')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="valor-section" class="space-y-2">
                        <x-form.label for="valor" value="Valor *" />
                        <textarea id="valor" name="valor" rows="3"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('valor') border-red-500 @enderror">{{ old('valor') }}</textarea>
                        @error('valor')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <x-form.label for="descripcion" value="Descripción" />
                        <x-form.input id="descripcion" name="descripcion" type="text"
                            class="w-full @error('descripcion') border-red-500 @enderror" maxlength="255"
                            placeholder="Descripción de la configuración" value="{{ old('descripcion') }}" />
                        @error('descripcion')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end mt-6">
                        <x-button variant="success">{{ __('Crear Configuración') }}</x-button>
                    </div>
                </div>
            </form>
        </x-modal>
    </div>

    <script>
        function toggleLogoUpload() {
            var claveSelect = document.getElementById('clave');
            var logoSection = document.getElementById('logo-upload-section');
            var valorSection = document.getElementById('valor-section');
            var otherClaveInput = document.getElementById('other-clave-input');
            var sedeSection = document.getElementById('sede-select-section');
            var valorTextarea = document.getElementById('valor');
            var logoInput = document.getElementById('logo_create');
            var sedeSelect = document.getElementById('id_sede');
            
            if (claveSelect.value === 'logo_institucional') {
                logoSection.style.display = 'block';
                valorSection.style.display = 'none';
                otherClaveInput.style.display = 'none';
                sedeSection.style.display = 'block';
                
                valorTextarea.removeAttribute('required');
                logoInput.setAttribute('required', 'required');
                sedeSelect.setAttribute('required', 'required');
            } else if (claveSelect.value === 'other') {
                logoSection.style.display = 'none';
                valorSection.style.display = 'block';
                otherClaveInput.style.display = 'block';
                sedeSection.style.display = 'none';
                
                valorTextarea.setAttribute('required', 'required');
                logoInput.removeAttribute('required');
                sedeSelect.removeAttribute('required');
            } else {
                logoSection.style.display = 'none';
                valorSection.style.display = 'none';
                otherClaveInput.style.display = 'none';
                sedeSection.style.display = 'none';
                
                valorTextarea.removeAttribute('required');
                logoInput.removeAttribute('required');
                sedeSelect.removeAttribute('required');
            }
        }

        function searchTable() {
            var input = document.getElementById("searchInput").value.toLowerCase();
            var table = document.getElementById("configuracion-table");
            var rows = table.getElementsByTagName("tr");

            for (var i = 1; i < rows.length; i++) {
                var cells = rows[i].getElementsByTagName("td");
                if (cells.length < 3) continue;
                var clave = cells[0].textContent.toLowerCase();
                var descripcion = cells[2].textContent.toLowerCase();

                if (clave.includes(input) || descripcion.includes(input)) {
                    rows[i].style.display = "";
                } else {
                    rows[i].style.display = "none";
                }
            }
        }
    </script>

</x-app-layout>
