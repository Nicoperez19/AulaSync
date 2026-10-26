<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between pr-2">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-[#c5221f] flex items-center justify-center text-white shadow-xs flex-shrink-0">
                    <i class="text-2xl fa-solid fa-cog"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold leading-tight text-gray-900">Configuración del Sistema</h2>
                    <p class="text-sm text-gray-500">Administra la configuración general del sistema (logo institucional, etc.)</p>
                </div>
            </div>

            <div class="flex items-center gap-3 flex-wrap">
                @if(isset($sedes) && count($sedes) > 1)
                <div class="flex items-center gap-2 bg-white border border-gray-300 rounded-lg px-3 py-1.5 shadow-2xs">
                    <span class="text-xs font-semibold text-gray-500">Sede:</span>
                    <select onchange="window.location.href='{{ route('configuracion.index') }}?id_sede=' + this.value" 
                        class="text-xs font-bold text-gray-800 border-none bg-transparent focus:ring-0 cursor-pointer p-0 pr-4">
                        @foreach($sedes as $s)
                            <option value="{{ $s->id_sede }}" {{ $sede && $sede->id_sede == $s->id_sede ? 'selected' : '' }}>
                                {{ $s->nombre_sede }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <button type="button" x-on:click.prevent="$dispatch('open-modal', 'add-configuracion')"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-[#0e5c3c] hover:bg-[#0b482f] text-white text-sm font-semibold rounded-lg shadow-xs transition-colors">
                    <i class="fa-solid fa-circle-plus text-base"></i>
                    <span>Agregar Configuración</span>
                </button>
            </div>
        </div>
    </x-slot>

    {{-- CARD PRINCIPAL: Identidad Visual y Períodos Académicos --}}
    <div class="bg-white rounded-xl shadow-xs border border-gray-200 p-6 md:p-8 mb-6">
        @if (session('success'))
            <div class="mb-5 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg flex items-center gap-2 shadow-2xs">
                <i class="fa-solid fa-circle-check text-green-500 text-lg"></i>
                <span class="font-medium text-sm">{{ session('success') }}</span>
            </div>
        @endif

        @if (isset($errors) && $errors->any())
            <div class="mb-5 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg shadow-2xs">
                <div class="flex items-center gap-2 mb-1">
                    <i class="fa-solid fa-circle-exclamation text-red-500 text-lg"></i>
                    <span class="font-semibold text-sm">Por favor corrige los siguientes errores:</span>
                </div>
                <ul class="list-disc list-inside text-xs text-red-700 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('configuracion.guardar-general') }}" method="POST" enctype="multipart/form-data" id="config-general-form">
            @csrf
            <input type="hidden" name="id_sede" value="{{ $sede ? $sede->id_sede : 'LA' }}">
            <input type="hidden" name="anio" value="{{ $anio }}">
            <input type="hidden" name="eliminar_logo" id="eliminar_logo_input" value="0">
            <input type="file" id="logo_file_input" name="logo" accept="image/jpeg,image/png,image/jpg,image/gif,image/svg+xml" class="hidden" onchange="handleLogoSelect(this)">

            {{-- SECCIÓN 1: Identidad Visual --}}
            <div class="mb-6">
                <h3 class="text-lg font-bold text-gray-900 mb-2">Identidad Visual</h3>
                <hr class="border-gray-200 mb-4">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <!-- Izquierda: Logo institucional -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-800">
                            Logo institucional de la Sede {{ $sede ? $sede->nombre_sede : 'Los Ángeles' }}
                        </label>
                        <div class="flex items-center gap-3 flex-wrap">
                            <!-- Botones agrupados: Subir Archivo y Examinar -->
                            <div class="inline-flex rounded-md shadow-2xs" role="group">
                                <button type="button" onclick="document.getElementById('logo_file_input').click()" 
                                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-l-md hover:bg-gray-50 focus:z-10 transition">
                                    <i class="fa-solid fa-arrow-up-from-bracket text-gray-500"></i> Subir Archivo
                                </button>
                                <button type="button" onclick="document.getElementById('logo_file_input').click()" 
                                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-medium text-gray-700 bg-white border-t border-b border-r border-gray-300 rounded-r-md hover:bg-gray-50 focus:z-10 transition">
                                    <i class="fa-regular fa-folder text-gray-500"></i> Examinar
                                </button>
                            </div>

                            <!-- Miniatura Logo y Eliminar -->
                            <div id="logo-preview-badge" class="inline-flex items-center gap-2 border border-gray-300 rounded-md px-2.5 py-1 bg-white shadow-2xs">
                                <img id="logo-preview-img" src="{{ $logoUrl }}" alt="Logo" class="w-6 h-6 object-contain rounded-full border border-gray-200">
                                <button type="button" onclick="eliminarLogoClick()" class="text-xs font-semibold text-red-600 hover:text-red-800 hover:underline transition">
                                    Eliminar
                                </button>
                            </div>

                            <span id="logo-status-text" class="text-xs font-medium text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200 hidden"></span>
                        </div>
                    </div>

                    <!-- Derecha: Toggle Switch Períodos Definidos -->
                    <div class="flex items-center">
                        <label class="relative inline-flex items-center cursor-pointer select-none">
                            <input type="checkbox" name="periodos_definidos" id="periodos_definidos" value="1" 
                                class="sr-only peer" {{ $periodosDefinidos ? 'checked' : '' }} onchange="togglePeriodosDefinidosUI(this.checked)">
                            <div class="w-11 h-6 bg-gray-300 peer-focus:outline-hidden rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#0e8050]"></div>
                            <span class="ms-3 text-sm font-medium text-gray-800">Períodos académicos definidos para la sede</span>
                        </label>
                    </div>
                </div>
            </div>

            <hr class="border-gray-200 my-6">

            {{-- SECCIÓN 2: Períodos Académicos y Semanas de Receso --}}
            <div class="mb-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Períodos Académicos y Semanas de Receso</h3>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5" id="periodos-cards-container">
                    <!-- Tarjeta: Primer Semestre -->
                    <div class="border border-gray-200 rounded-xl p-4 bg-white shadow-2xs transition-all duration-200">
                        <h4 class="font-bold text-gray-900 text-sm mb-3">Primer Semestre {{ $anio }} ({{ $anio }}-1)</h4>
                        
                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Inicio</label>
                                <input type="date" name="periodo1_inicio" id="periodo1_inicio" value="{{ old('periodo1_inicio', $periodo1Inicio) }}" required
                                    class="w-full text-xs sm:text-sm bg-gray-50/70 border border-gray-300 rounded-lg px-3 py-1.5 text-gray-800 focus:ring-1 focus:ring-teal-600 focus:border-teal-600 transition">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Fin</label>
                                <input type="date" name="periodo1_fin" id="periodo1_fin" value="{{ old('periodo1_fin', $periodo1Fin) }}" required
                                    class="w-full text-xs sm:text-sm bg-gray-50/70 border border-gray-300 rounded-lg px-3 py-1.5 text-gray-800 focus:ring-1 focus:ring-teal-600 focus:border-teal-600 transition">
                            </div>
                        </div>

                        <!-- Receso Box Inset -->
                        <div class="bg-[#ebedf0] bg-gray-100 rounded-lg p-3 border border-gray-200/80">
                            <h5 class="font-bold text-xs text-gray-800 mb-2">Semana de Receso</h5>
                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 items-center">
                                <div class="sm:col-span-5 flex items-center">
                                    <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-medium text-gray-700">
                                        <input type="checkbox" name="receso1_habilitado" id="receso1_habilitado" value="1" 
                                            {{ old('receso1_habilitado', $receso1Habilitado) ? 'checked' : '' }} 
                                            onchange="toggleRecesoInputs(1, this.checked)"
                                            class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                                        <span>Habilitar semana de receso</span>
                                    </label>
                                </div>
                                <div class="sm:col-span-3">
                                    <label class="block text-[11px] text-gray-500 mb-0.5">Inicio</label>
                                    <input type="date" name="receso1_inicio" id="receso1_inicio" value="{{ old('receso1_inicio', $receso1Inicio) }}"
                                        class="w-full text-xs bg-white border border-gray-300 rounded-md px-2 py-1 text-gray-800 disabled:opacity-50 disabled:bg-gray-100 transition">
                                </div>
                                <div class="sm:col-span-4">
                                    <label class="block text-[11px] text-gray-500 mb-0.5">Fin</label>
                                    <input type="date" name="receso1_fin" id="receso1_fin" value="{{ old('receso1_fin', $receso1Fin) }}"
                                        class="w-full text-xs bg-white border border-gray-300 rounded-md px-2 py-1 text-gray-800 disabled:opacity-50 disabled:bg-gray-100 transition">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tarjeta: Segundo Semestre -->
                    <div class="border border-gray-200 rounded-xl p-4 bg-white shadow-2xs transition-all duration-200">
                        <h4 class="font-bold text-gray-900 text-sm mb-3">Segundo Semestre {{ $anio }} ({{ $anio }}-2)</h4>
                        
                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Inicio</label>
                                <input type="date" name="periodo2_inicio" id="periodo2_inicio" value="{{ old('periodo2_inicio', $periodo2Inicio) }}" required
                                    class="w-full text-xs sm:text-sm bg-gray-50/70 border border-gray-300 rounded-lg px-3 py-1.5 text-gray-800 focus:ring-1 focus:ring-teal-600 focus:border-teal-600 transition">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Fin</label>
                                <input type="date" name="periodo2_fin" id="periodo2_fin" value="{{ old('periodo2_fin', $periodo2Fin) }}" required
                                    class="w-full text-xs sm:text-sm bg-gray-50/70 border border-gray-300 rounded-lg px-3 py-1.5 text-gray-800 focus:ring-1 focus:ring-teal-600 focus:border-teal-600 transition">
                            </div>
                        </div>

                        <!-- Receso Box Inset -->
                        <div class="bg-[#ebedf0] bg-gray-100 rounded-lg p-3 border border-gray-200/80">
                            <h5 class="font-bold text-xs text-gray-800 mb-2">Semana de Receso</h5>
                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 items-center">
                                <div class="sm:col-span-5 flex items-center">
                                    <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-medium text-gray-700">
                                        <input type="checkbox" name="receso2_habilitado" id="receso2_habilitado" value="1" 
                                            {{ old('receso2_habilitado', $receso2Habilitado) ? 'checked' : '' }} 
                                            onchange="toggleRecesoInputs(2, this.checked)"
                                            class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                                        <span>Habilitar semana de receso</span>
                                    </label>
                                </div>
                                <div class="sm:col-span-3">
                                    <label class="block text-[11px] text-gray-500 mb-0.5">Inicio</label>
                                    <input type="date" name="receso2_inicio" id="receso2_inicio" value="{{ old('receso2_inicio', $receso2Inicio) }}"
                                        class="w-full text-xs bg-white border border-gray-300 rounded-md px-2 py-1 text-gray-800 disabled:opacity-50 disabled:bg-gray-100 transition">
                                </div>
                                <div class="sm:col-span-4">
                                    <label class="block text-[11px] text-gray-500 mb-0.5">Fin</label>
                                    <input type="date" name="receso2_fin" id="receso2_fin" value="{{ old('receso2_fin', $receso2Fin) }}"
                                        class="w-full text-xs bg-white border border-gray-300 rounded-md px-2 py-1 text-gray-800 disabled:opacity-50 disabled:bg-gray-100 transition">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botón Guardar Cambios -->
            <div class="flex justify-end mt-6">
                <button type="submit" 
                    class="inline-flex items-center px-6 py-2.5 bg-[#0d6955] hover:bg-[#0a5243] text-white text-sm font-semibold rounded-lg shadow-xs transition-colors">
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>

    {{-- SECCIÓN SECUNDARIA: Otras configuraciones del sistema (Tabla y Modal) --}}
    <div x-data="{ openTable: false }" class="bg-white rounded-xl shadow-xs border border-gray-200 overflow-hidden mb-6">
        <div @click="openTable = !openTable" class="p-4 bg-gray-50/70 border-b border-gray-200 flex items-center justify-between cursor-pointer hover:bg-gray-100 transition select-none">
            <div class="flex items-center gap-2.5">
                <i class="fa-solid fa-list-check text-gray-500"></i>
                <h4 class="text-sm font-bold text-gray-800">Otras Configuraciones del Sistema</h4>
                <span class="text-xs text-gray-500 font-medium">({{ isset($configuraciones) ? $configuraciones->count() : 0 }} registros)</span>
            </div>
            <div class="flex items-center gap-2 text-xs font-semibold text-gray-600">
                <span x-text="openTable ? 'Ocultar listado' : 'Ver listado'"></span>
                <i class="fa-solid text-xs" :class="openTable ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
            </div>
        </div>
        <div x-show="openTable" class="p-6" style="display: none;">
            <livewire:configuracion-table />
        </div>
    </div>

    {{-- MODAL PARA AGREGAR CONFIGURACIÓN GENERAL --}}
    <x-modal name="add-configuracion" :show="isset($errors) && ($errors->has('clave') || $errors->has('valor'))" focusable>
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
                        @foreach(\App\Models\Sede::all() as $s)
                            <option value="{{ $s->id_sede }}" {{ old('id_sede') == $s->id_sede ? 'selected' : '' }}>
                                {{ $s->nombre_sede }}
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

    <script>
        function handleLogoSelect(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                if (!file.type.startsWith('image/')) {
                    alert('Por favor seleccione un archivo de imagen válido.');
                    input.value = '';
                    return;
                }
                if (file.size > 2 * 1024 * 1024) {
                    alert('El archivo no debe superar los 2MB.');
                    input.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('logo-preview-img').src = e.target.result;
                    document.getElementById('eliminar_logo_input').value = '0';
                    const statusText = document.getElementById('logo-status-text');
                    if (statusText) {
                        statusText.innerText = 'Nuevo logo: ' + file.name + ' (Guardar para aplicar)';
                        statusText.classList.remove('hidden');
                    }
                };
                reader.readAsDataURL(file);
            }
        }

        function eliminarLogoClick() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¿Eliminar logo institucional?',
                    text: 'Se restablecerá el logo por defecto una vez guardes los cambios.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#D12420',
                    cancelButtonColor: '#8D8D8D',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        aplicarEliminacionLogo();
                    }
                });
            } else {
                if (confirm('¿Desea eliminar el logo institucional?')) {
                    aplicarEliminacionLogo();
                }
            }
        }

        function aplicarEliminacionLogo() {
            document.getElementById('eliminar_logo_input').value = '1';
            document.getElementById('logo_file_input').value = '';
            document.getElementById('logo-preview-img').src = '{{ asset("images/logo_instituto_tecnologico-01.png") }}';
            const statusText = document.getElementById('logo-status-text');
            if (statusText) {
                statusText.innerText = 'Logo marcado para eliminación (Guardar para aplicar)';
                statusText.classList.remove('hidden');
                statusText.classList.remove('text-emerald-700', 'bg-emerald-50', 'border-emerald-200');
                statusText.classList.add('text-amber-800', 'bg-amber-50', 'border-amber-200');
            }
        }

        function toggleRecesoInputs(semestre, habilitado) {
            const inicio = document.getElementById('receso' + semestre + '_inicio');
            const fin = document.getElementById('receso' + semestre + '_fin');
            if (inicio && fin) {
                if (!habilitado) {
                    inicio.setAttribute('disabled', 'disabled');
                    fin.setAttribute('disabled', 'disabled');
                } else {
                    inicio.removeAttribute('disabled');
                    fin.removeAttribute('disabled');
                }
            }
        }

        function togglePeriodosDefinidosUI(definidos) {
            const container = document.getElementById('periodos-cards-container');
            if (container) {
                if (!definidos) {
                    container.classList.add('opacity-75');
                } else {
                    container.classList.remove('opacity-75');
                }
            }
        }

        function toggleLogoUpload() {
            var claveSelect = document.getElementById('clave');
            var logoSection = document.getElementById('logo-upload-section');
            var valorSection = document.getElementById('valor-section');
            var otherClaveInput = document.getElementById('other-clave-input');
            var sedeSection = document.getElementById('sede-select-section');
            var valorTextarea = document.getElementById('valor');
            var logoInput = document.getElementById('logo_create');
            var sedeSelect = document.getElementById('id_sede');
            
            if (!claveSelect) return;

            if (claveSelect.value === 'logo_institucional') {
                if (logoSection) logoSection.style.display = 'block';
                if (valorSection) valorSection.style.display = 'none';
                if (otherClaveInput) otherClaveInput.style.display = 'none';
                if (sedeSection) sedeSection.style.display = 'block';
                
                if (valorTextarea) valorTextarea.removeAttribute('required');
                if (logoInput) logoInput.setAttribute('required', 'required');
                if (sedeSelect) sedeSelect.setAttribute('required', 'required');
            } else if (claveSelect.value === 'other') {
                if (logoSection) logoSection.style.display = 'none';
                if (valorSection) valorSection.style.display = 'block';
                if (otherClaveInput) otherClaveInput.style.display = 'block';
                if (sedeSection) sedeSection.style.display = 'none';
                
                if (valorTextarea) valorTextarea.setAttribute('required', 'required');
                if (logoInput) logoInput.removeAttribute('required');
                if (sedeSelect) sedeSelect.removeAttribute('required');
            } else {
                if (logoSection) logoSection.style.display = 'none';
                if (valorSection) valorSection.style.display = 'none';
                if (otherClaveInput) otherClaveInput.style.display = 'none';
                if (sedeSection) sedeSection.style.display = 'none';
                
                if (valorTextarea) valorTextarea.removeAttribute('required');
                if (logoInput) logoInput.removeAttribute('required');
                if (sedeSelect) sedeSelect.removeAttribute('required');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const receso1 = document.getElementById('receso1_habilitado');
            if (receso1) toggleRecesoInputs(1, receso1.checked);
            const receso2 = document.getElementById('receso2_habilitado');
            if (receso2) toggleRecesoInputs(2, receso2.checked);
            const def = document.getElementById('periodos_definidos');
            if (def) togglePeriodosDefinidosUI(def.checked);
        });
    </script>
</x-app-layout>
