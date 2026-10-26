<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue text-white shadow-md">
                    <i class="text-2xl fa-solid fa-users-rectangle"></i>
                </div>

                <div>
                    <h2 class="text-2xl font-bold leading-tight text-gray-800 dark:text-gray-100">
                        Carga Masiva de Estudiantes
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Importación e inscripción masiva de nóminas de estudiantes para Control de Asistencia y QR
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('estudiantes.carga-masiva.plantilla') }}" 
                   class="inline-flex items-center gap-2 px-3.5 py-2 bg-white hover:bg-slate-50 dark:bg-dark-eval-1 dark:hover:bg-dark-eval-2 border border-slate-200 hover:border-slate-300 dark:border-gray-600 text-slate-700 dark:text-gray-200 text-sm font-semibold rounded-xl shadow-xs transition">
                    <i class="fa-solid fa-download text-emerald-600"></i>
                    <span>Descargar Plantilla CSV</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        {{-- Mensajes de Notificación y Resumen --}}
        @if(session('error'))
            <div class="p-4 rounded-xl bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 flex items-center gap-3 text-sm">
                <i class="fa-solid fa-triangle-exclamation text-xl text-red-600"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if(session('resumen'))
            @php $res = session('resumen'); @endphp
            <div class="p-6 bg-white dark:bg-dark-eval-1 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-md space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Carga Masiva Procesada</h3>
                        <p class="text-xs text-gray-500">Se han procesado {{ $res['total_leidos'] }} registros del archivo.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                    <div class="p-4 rounded-xl bg-blue-50 dark:bg-blue-950/30 border border-blue-100 dark:border-blue-900/50">
                        <div class="text-xs text-blue-600 dark:text-blue-400 font-semibold uppercase">Nuevos Estudiantes</div>
                        <div class="text-2xl font-bold text-blue-950 dark:text-blue-100 mt-1">{{ $res['creados'] }}</div>
                    </div>
                    <div class="p-4 rounded-xl bg-purple-50 dark:bg-purple-950/30 border border-purple-100 dark:border-purple-900/50">
                        <div class="text-xs text-purple-600 dark:text-purple-400 font-semibold uppercase">Estudiantes Actualizados</div>
                        <div class="text-2xl font-bold text-purple-950 dark:text-purple-100 mt-1">{{ $res['actualizados'] }}</div>
                    </div>
                    <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-900/50">
                        <div class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold uppercase">Inscripciones a Asignaturas</div>
                        <div class="text-2xl font-bold text-emerald-950 dark:text-emerald-100 mt-1">{{ $res['inscripciones'] }}</div>
                    </div>
                </div>

                @if(!empty($res['errores']))
                    <div class="mt-4 p-4 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 text-xs text-amber-900 dark:text-amber-200 space-y-1">
                        <div class="font-bold flex items-center gap-1.5 mb-2 text-amber-800 dark:text-amber-300">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            Observaciones durante la carga ({{ count($res['errores']) }}):
                        </div>
                        <ul class="list-disc list-inside space-y-0.5 max-h-40 overflow-y-auto">
                            @foreach($res['errores'] as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @endif

        {{-- Formulario Principal de Carga --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Panel Izquierdo: Formulario de Subida --}}
            <div class="lg:col-span-2 bg-white dark:bg-dark-eval-1 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-md p-6">
                <form action="{{ route('estudiantes.carga-masiva.procesar') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="periodo" class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                                Período Académico
                            </label>
                            <input type="text" id="periodo" name="periodo" value="{{ $periodoActual }}" required
                                   class="w-full text-sm px-3.5 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-dark-eval-2 text-gray-800 dark:text-gray-100 font-semibold focus:ring-2 focus:ring-light-cloud-blue outline-none shadow-xs">
                            <p class="text-[11px] text-gray-400 mt-1">Semestre en el que se inscribirán los cursos (ej: 2026-2).</p>
                        </div>

                        <div>
                            <label for="sede_defecto" class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                                Sede por Defecto (Fallback)
                            </label>
                            <select id="sede_defecto" name="sede_defecto" 
                                    class="w-full text-sm px-3.5 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-dark-eval-2 text-gray-800 dark:text-gray-100 font-semibold focus:ring-2 focus:ring-light-cloud-blue outline-none shadow-xs">
                                <option value="">Detectar automáticamente por columna "Sede"</option>
                                @foreach($tenants as $t)
                                    <option value="{{ $t->id }}">
                                        {{ $t->sede?->nombre_sede ?? $t->name }} (Tenant: {{ $t->name }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-[11px] text-gray-400 mt-1">Se usará si alguna fila del Excel viene sin el nombre de la sede.</p>
                        </div>
                    </div>

                    {{-- Zona de Drag and Drop para el archivo --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">
                            Archivo de Estudiantes (.xlsx, .xls o .csv) <span class="text-red-500">*</span>
                        </label>

                        <div class="relative border-2 border-dashed border-gray-300 dark:border-gray-600 hover:border-light-cloud-blue rounded-2xl p-8 text-center transition bg-gray-50/50 dark:bg-dark-eval-2/30 cursor-pointer"
                             onclick="document.getElementById('input_archivo').click()">
                            <input type="file" id="input_archivo" name="archivo" accept=".xlsx,.xls,.csv,.txt" required class="hidden"
                                   onchange="actualizarNombreArchivo(this)">
                            
                            <div class="w-14 h-14 rounded-full bg-blue-50 dark:bg-blue-950/50 text-light-cloud-blue flex items-center justify-center mx-auto mb-3">
                                <i class="fa-solid fa-file-excel text-2xl"></i>
                            </div>

                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-200" id="texto_archivo_nombre">
                                Haz clic aquí para seleccionar o arrastra tu archivo Excel
                            </p>
                            <p class="text-xs text-gray-400 mt-1">Formatos soportados: Excel (.xlsx, .xls) o CSV delimitado por comas o punto y coma (Máx. 15MB)</p>
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit" 
                                class="inline-flex items-center gap-2 px-6 py-3 bg-red-700 hover:bg-red-800 text-white font-bold text-sm rounded-xl shadow-md transition transform hover:-translate-y-0.5">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                            <span>Procesar Carga Masiva</span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Panel Derecho: Instrucciones y Formato Requerido --}}
            <div class="space-y-4">
                <div class="bg-white dark:bg-dark-eval-1 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-md p-5 space-y-3">
                    <h4 class="text-sm font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                        <i class="fa-solid fa-table-list text-light-cloud-blue"></i>
                        <span>Estructura de Columnas Requerida</span>
                    </h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        La primera fila del archivo debe contener exactamente o de forma similar los siguientes nombres de columna:
                    </p>

                    <div class="space-y-2 text-xs">
                        <div class="p-2.5 rounded-lg bg-gray-50 dark:bg-dark-eval-2 border border-gray-100 dark:border-gray-700">
                            <span class="font-bold text-gray-800 dark:text-gray-200">RUN:</span>
                            <span class="text-gray-500"> Obligatorio. Formato libre (con/sin puntos ni guión).</span>
                        </div>
                        <div class="p-2.5 rounded-lg bg-gray-50 dark:bg-dark-eval-2 border border-gray-100 dark:border-gray-700">
                            <span class="font-bold text-gray-800 dark:text-gray-200">Nombre:</span>
                            <span class="text-gray-500"> Apellidos y Nombres completos del estudiante.</span>
                        </div>
                        <div class="p-2.5 rounded-lg bg-gray-50 dark:bg-dark-eval-2 border border-gray-100 dark:border-gray-700">
                            <span class="font-bold text-gray-800 dark:text-gray-200">Correo:</span>
                            <span class="text-gray-500"> Email institucional para notificaciones de asistencia QR.</span>
                        </div>
                        <div class="p-2.5 rounded-lg bg-gray-50 dark:bg-dark-eval-2 border border-gray-100 dark:border-gray-700">
                            <span class="font-bold text-gray-800 dark:text-gray-200">Carrera:</span>
                            <span class="text-gray-500"> Código o nombre de la carrera (ej: 1284).</span>
                        </div>
                        <div class="p-2.5 rounded-lg bg-gray-50 dark:bg-dark-eval-2 border border-gray-100 dark:border-gray-700">
                            <span class="font-bold text-gray-800 dark:text-gray-200">COD_ASIGNATURA:</span>
                            <span class="text-gray-500"> NRC / ID o código de la asignatura a inscribir (ej: 573259).</span>
                        </div>
                        <div class="p-2.5 rounded-lg bg-gray-50 dark:bg-dark-eval-2 border border-gray-100 dark:border-gray-700">
                            <span class="font-bold text-gray-800 dark:text-gray-200">Sede:</span>
                            <span class="text-gray-500"> Nombre de la sede (ej: Talcahuano).</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function actualizarNombreArchivo(input) {
            if (input.files && input.files[0]) {
                document.getElementById('texto_archivo_nombre').innerHTML = 
                    `<span class="text-emerald-600 font-bold"><i class="fa-solid fa-check-circle mr-1"></i> Archivo seleccionado:</span> ${input.files[0].name} (${(input.files[0].size / 1024 / 1024).toFixed(2)} MB)`;
            }
        }
    </script>
</x-app-layout>
