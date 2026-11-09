<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-gear"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold leading-tight">Configuración del Sistema</h2>
                    <p class="text-sm text-gray-500">Administra la configuración general del sistema (logo institucional, etc.)</p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if (session('success'))
            <div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg flex items-center gap-2 shadow-2xs">
                <i class="fa-solid fa-circle-check text-green-500 text-lg"></i>
                <span class="font-medium text-sm">{{ session('success') }}</span>
            </div>
        @endif

        @if (isset($errors) && $errors->any())
            <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg shadow-2xs">
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

        <form action="{{ route('configuracion.guardar-general') }}" method="POST" enctype="multipart/form-data" id="config-general-form" class="space-y-6">
            @csrf
            <input type="hidden" name="id_sede" value="{{ $sede ? $sede->id_sede : 'LA' }}">
            <input type="hidden" name="anio" value="{{ $anio }}">
            <input type="hidden" name="eliminar_logo" id="eliminar_logo_input" value="0">
            <input type="file" id="logo_file_input" name="logo" accept="image/jpeg,image/png,image/jpg,image/gif,image/svg+xml" class="hidden" onchange="handleLogoSelect(this)">

            {{-- TARJETA 1: Identidad Visual y Selector de Sede --}}
            <div class="bg-white rounded-xl shadow-xs border border-gray-200 p-6 md:p-7">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-5 border-b border-gray-200 gap-4">
                    <div>
                        <h3 class="text-base font-bold text-gray-800 flex items-center gap-2">
                            <i class="fa-solid fa-palette text-[#950101]"></i>
                            <span>Identidad Visual</span>
                        </h3>
                        <p class="text-xs text-gray-500">Logo institucional oficial asignado a la sede</p>
                    </div>

                    @if(isset($sedes) && count($sedes) > 1)
                    <div class="flex items-center gap-2 bg-gray-50 border border-gray-300 rounded-lg px-3 py-1.5 shadow-2xs shrink-0 self-start sm:self-auto">
                        <label for="sede_selector" class="text-xs font-semibold text-gray-600 flex items-center gap-1.5">
                            <i class="fa-solid fa-building-columns text-gray-400"></i> Sede:
                        </label>
                        <select id="sede_selector" onchange="window.location.href='{{ route('configuracion.index') }}?id_sede=' + this.value" 
                            class="text-xs font-bold text-gray-800 border-none bg-transparent focus:ring-0 cursor-pointer p-0 pr-4">
                            @foreach($sedes as $s)
                                <option value="{{ $s->id_sede }}" {{ $sede && $sede->id_sede == $s->id_sede ? 'selected' : '' }}>
                                    {{ $s->nombre_sede }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                </div>

                <div class="space-y-3">
                    <label class="block text-xs font-semibold text-gray-700">
                        Logo Institucional de la Sede {{ $sede ? $sede->nombre_sede : 'Los Ángeles' }}
                    </label>

                    <!-- Previsualización Compacta con Fondo Negro -->
                    <div class="w-full sm:w-80 h-28 bg-black rounded-lg p-3 flex items-center justify-center border border-gray-800 shadow-inner relative group overflow-hidden">
                        <img id="logo-preview-img" src="{{ $logoUrl }}" alt="Logo institucional" 
                            class="max-h-20 max-w-full object-contain transition-transform duration-200 group-hover:scale-105">
                        <span class="absolute bottom-1 right-2 text-[9px] text-gray-400 font-mono tracking-wider select-none bg-black/80 px-1 rounded">
                            VISTA PREVIA
                        </span>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="flex items-center gap-2 flex-wrap pt-1">
                        <x-button type="button" variant="primary" size="sm" onclick="document.getElementById('logo_file_input').click()" class="gap-1.5">
                            <i class="fa-solid fa-arrow-up-from-bracket text-xs"></i>
                            <span>Subir Archivo</span>
                        </x-button>

                        <x-button type="button" variant="outline" size="sm" onclick="document.getElementById('logo_file_input').click()" class="gap-1.5">
                            <i class="fa-regular fa-folder-open text-xs"></i>
                            <span>Examinar</span>
                        </x-button>

                        <x-button type="button" variant="danger" size="sm" onclick="eliminarLogoClick()" class="gap-1.5">
                            <i class="fa-solid fa-trash-can text-xs"></i>
                            <span>Eliminar</span>
                        </x-button>
                    </div>

                    <div id="logo-status-container">
                        <span id="logo-status-text" class="hidden"></span>
                    </div>
                </div>
            </div>

            {{-- TARJETA 2: Períodos Académicos y Semanas de Receso --}}
            <div class="bg-white rounded-xl shadow-xs border border-gray-200 p-6 md:p-7">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-5 border-b border-gray-200 gap-4">
                    <div>
                        <h3 class="text-base font-bold text-gray-800 flex items-center gap-2">
                            <i class="fa-regular fa-calendar-days text-[#950101]"></i>
                            <span>Períodos Académicos y Semanas de Receso</span>
                        </h3>
                        <p class="text-xs text-gray-500">Configuración de fechas de inicio, término y recesos por semestre</p>
                    </div>

                    <!-- Toggle Switch Períodos Definidos -->
                    <div class="flex items-center shrink-0 self-start sm:self-auto">
                        <label class="relative inline-flex items-center cursor-pointer select-none">
                            <input type="checkbox" name="periodos_definidos" id="periodos_definidos" value="1" 
                                class="sr-only peer" {{ $periodosDefinidos ? 'checked' : '' }} onchange="togglePeriodosDefinidosUI(this.checked)">
                            <div class="w-11 h-6 bg-gray-300 peer-focus:outline-hidden rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#0e8050]"></div>
                            <span class="ms-3 text-xs sm:text-sm font-medium text-gray-800">Períodos académicos definidos para la sede</span>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5" id="periodos-cards-container">
                    <!-- Tarjeta: Primer Semestre -->
                    <div class="border border-gray-200 rounded-xl p-4 bg-white shadow-2xs transition-all duration-200">
                        <h5 class="font-bold text-gray-900 text-sm mb-3">Primer Semestre {{ $anio }} ({{ $anio }}-1)</h5>
                        
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
                            <h6 class="font-bold text-xs text-gray-800 mb-2">Semana de Receso</h6>
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
                        <h5 class="font-bold text-gray-900 text-sm mb-3">Segundo Semestre {{ $anio }} ({{ $anio }}-2)</h5>
                        
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
                            <h6 class="font-bold text-xs text-gray-800 mb-2">Semana de Receso</h6>
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

            <!-- Botón Guardar Cambios Unificado -->
            <div class="flex justify-end">
                <x-button type="submit" variant="success" size="base" class="gap-2 px-6">
                    <i class="fa-solid fa-floppy-disk text-base"></i>
                    <span>Guardar Cambios</span>
                </x-button>
            </div>
        </form>
    </div>

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
                        statusText.innerText = 'Nuevo logo cargado: ' + file.name + ' (Guardar para aplicar cambios)';
                        statusText.className = 'text-xs font-medium text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-200 inline-flex items-center gap-1.5';
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
                statusText.innerText = 'Logo marcado para eliminación (Guardar para aplicar cambios)';
                statusText.className = 'text-xs font-medium text-amber-800 bg-amber-50 px-2.5 py-1 rounded-md border border-amber-200 inline-flex items-center gap-1.5';
                statusText.classList.remove('hidden');
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
