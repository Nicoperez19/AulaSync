<x-app-layout>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-file-arrow-up"></i>
                </div>

                <div>
                    <h2 class="text-2xl font-bold leading-tight">Carga Masiva de Datos</h2>
                    <p class="text-sm text-gray-500">Sube archivos para importar información al sistema de forma rápida
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 max-w-xs justify-end">
                @if(auth()->check() && (auth()->user()->is_superuser || auth()->user()->hasRole('Super Admin') || (string)auth()->user()->run === '19716146'))
                    <a href="{{ route('estudiantes.carga-masiva.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 bg-white hover:bg-slate-50 dark:bg-dark-eval-1 dark:hover:bg-dark-eval-2 border border-slate-200 hover:border-slate-300 dark:border-gray-600 text-slate-700 dark:text-gray-200 text-sm font-semibold rounded-xl shadow-xs transition"><i class="fa-solid fa-users text-light-cloud-blue"></i><span>Cargar Estudiantes</span></a>
                @endif
                <x-button target="_blank" variant="add" class="gap-2 p-2"
                    x-on:click="$dispatch('open-modal', 'add-data')">
                    <x-icons.add class="w-6 h-6" aria-hidden="true" />
                    Cargar archivo
                </x-button>
            </div>
        </div>
    </x-slot>


    <div class="px-6">
        <livewire:data-load-table />
    </div>

    <x-modal name="add-data" :show="$errors->any()" focusable x-on:close="limpiarFormulario()">
        @slot('title')
        <div class="relative flex items-center justify-between px-3 py-1.5 bg-red-700">
            <div class="flex items-center gap-2">
                <div class="flex items-center justify-center w-6 h-6 bg-red-100 rounded-full shrink-0">
                    <i class="text-xs text-red-600 fa-solid fa-upload"></i>
                </div>
                <h2 class="text-lg font-bold text-white">
                    Cargar Archivo de Datos
                </h2>
            </div>
            <button @click="show = false"
                class="ml-2 text-xl font-bold text-white hover:text-gray-200 leading-none">&times;</button>
            <!-- Círculos decorativos -->
            <span
                class="absolute top-0 left-0 w-16 h-16 -translate-x-1/2 -translate-y-1/2 bg-white rounded-full pointer-events-none bg-opacity-10"></span>
            <span
                class="absolute top-0 right-0 w-16 h-16 translate-x-1/2 -translate-y-1/2 bg-white rounded-full pointer-events-none bg-opacity-10"></span>
        </div>
        @endslot
        <form id="upload-form" action="{{ route('data.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="grid gap-6 p-6">
                <div class="space-y-4">
                    <div
                        class="mb-4 border border-blue-200 rounded-lg bg-blue-50 dark:bg-blue-900/20 dark:border-blue-700">
                        <div class="flex items-start gap-3">
                            <div class="p-2">
                                <h4 class="mb-1 text-sm font-medium text-blue-800 dark:text-blue-200">
                                    Información Importante
                                </h4>
                                <p class="text-sm text-justify text-blue-700 dark:text-blue-300">
                                    Seleccione un archivo Excel (.xlsx, .xls) o CSV para cargar los datos. El archivo
                                    debe
                                    tener un tamaño máximo de 10MB. <strong>Importante:</strong> Seleccione el semestre
                                    académico
                                    al que corresponden los datos del archivo. El año será automáticamente el actual.
                                    Esto asegurará que los horarios se creen con el período correcto.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Seleccionar archivo
                        </label>
                        <div
                            class="flex justify-center px-6 pt-5 pb-6 mt-1 border-2 border-gray-300 border-dashed rounded-md">
                            <div class="space-y-1 text-center">
                                <svg class="w-12 h-12 mx-auto text-gray-400" stroke="currentColor" fill="none"
                                    viewBox="0 0 48 48" aria-hidden="true">
                                    <path
                                        d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                    <label for="file-upload"
                                        class="relative font-medium text-blue-600 bg-white rounded-md cursor-pointer dark:bg-gray-800 hover:text-blue-500">
                                        <span>Subir un archivo o Arrastrar y soltar</span>
                                        <input id="file-upload" name="file" type="file" class="sr-only"
                                            accept=".xlsx,.xls,.csv" onchange="handleFileSelect(this)">
                                    </label>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Excel o CSV hasta 10MB
                                </p>
                                <div id="selected-file-name"
                                    class="mt-2 text-sm font-medium text-gray-700 dark:text-gray-300"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Selector de Semestre -->
                    <div>
                        <label for="semestre_selector"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Semestre Académico <span class="text-red-500">*</span>
                        </label>
                        @php
                            $anioAcad = \App\Helpers\SemesterHelper::getCurrentAcademicYear();
                            $semActual = \App\Helpers\SemesterHelper::getCurrentSemester();
                        @endphp
                        <select id="semestre_selector" name="semestre_selector" required
                            class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="" disabled>-- Seleccione el Semestre Académico --</option>
                            <option value="1" @selected($semActual == 1)>Primer Semestre ({{ $anioAcad }}-1)</option>
                            <option value="2" @selected($semActual == 2)>Segundo Semestre ({{ $anioAcad }}-2)</option>
                        </select>
                    </div>

                    <!-- Spinner de carga -->
                    <div id="loading-spinner" class="hidden mt-4">
                        <div class="flex flex-col items-center justify-center">
                            <div class="w-12 h-12 border-b-2 border-blue-500 rounded-full animate-spin"></div>
                            <p class="mt-2 text-sm font-medium text-gray-600 dark:text-gray-400">Procesando archivo...
                            </p>
                        </div>
                    </div>

                    <!-- Mensajes -->
                    <div id="error-message" class="hidden mt-2 text-sm text-red-600"></div>
                    <div id="success-message" class="hidden mt-2 text-sm text-green-600"></div>
                    <div id="uploaded-file-name" class="hidden mt-2 text-sm font-semibold text-green-700"></div>
                </div>

                <div class="flex justify-end gap-4">
                    <x-button variant="success" type="button" id="load-button" class="hidden">
                        Cargar
                    </x-button>
                </div>
            </div>
        </form>
    </x-modal>

    <!-- Modal de Detalles de Carga -->
    <div id="modal-detalle-carga" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="flex flex-col w-full max-h-screen mx-2 overflow-hidden bg-white rounded-lg shadow-lg max-w-6xl md:mx-8">
            <!-- Encabezado azul con diseño tipo banner -->
            <div class="relative flex flex-col gap-6 p-8 bg-blue-700 md:flex-row md:items-center md:justify-between">
                <!-- Círculos decorativos -->
                <span class="absolute top-0 left-0 w-32 h-32 -translate-x-1/2 -translate-y-1/2 bg-white rounded-full pointer-events-none bg-opacity-10"></span>
                <span class="absolute top-0 right-0 w-32 h-32 translate-x-1/2 -translate-y-1/2 bg-white rounded-full pointer-events-none bg-opacity-10"></span>

                <div class="flex items-center flex-1 min-w-0 gap-5">
                    <div class="flex flex-col items-center justify-center flex-shrink-0">
                        <div class="p-4 mb-2 bg-white rounded-full bg-opacity-20">
                            <i class="text-3xl text-white fa-solid fa-file-excel"></i>
                        </div>
                    </div>
                    <div class="flex flex-col min-w-0">
                        <h1 class="text-3xl font-bold text-white truncate">Detalles de Carga de Datos</h1>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-lg truncate text-white/80">Información del Proceso</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center self-start flex-shrink-0 gap-3 md:self-center">
                    <button onclick="cerrarModal()" class="ml-2 text-3xl font-bold text-white hover:text-gray-200">&times;</button>
                </div>
            </div>

            <!-- Contenido del modal -->
            <div class="p-6 bg-gray-50 overflow-y-auto max-h-[70vh] flex-1">
                <!-- Información principal en 2 columnas -->
                <div class="grid grid-cols-1 gap-8 mb-8 lg:grid-cols-2">
                    <!-- Información del archivo -->
                    <div class="p-6 bg-white rounded-lg shadow-sm border-l-4 border-blue-500">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-3 bg-blue-100 rounded-lg">
                                <i class="text-blue-700 fa-solid fa-file-excel text-xl"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900">Información del Archivo</h3>
                        </div>

                        <div class="space-y-4">
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <div class="mb-1 text-sm font-medium text-gray-500">Nombre del Archivo</div>
                                <div id="archivo-nombre" class="font-semibold text-gray-900 break-words"></div>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <div class="mb-1 text-sm font-medium text-gray-500">Tipo de Archivo</div>
                                <div class="font-semibold text-gray-900">XLSX</div>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <div class="mb-1 text-sm font-medium text-gray-500">Tamaño</div>
                                <div id="archivo-tamano" class="font-semibold text-gray-900"></div>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <div class="mb-1 text-sm font-medium text-gray-500">Registros Procesados</div>
                                <div id="archivo-registros" class="text-lg font-bold text-green-600"></div>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <div class="mb-1 text-sm font-medium text-gray-500">Estado</div>
                                <span id="archivo-estado" class="inline-block px-3 py-1 text-sm font-semibold rounded-full"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Información del usuario -->
                    <div class="p-6 bg-white rounded-lg shadow-sm border-l-4 border-green-500">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-3 bg-green-100 rounded-lg">
                                <i class="text-green-700 fa-solid fa-user text-xl"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900">Información del Usuario</h3>
                        </div>

                        <div class="space-y-4">
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex items-center justify-center w-12 h-12 bg-green-100 rounded-full">
                                        <i class="text-green-600 fa-solid fa-user text-xl"></i>
                                    </span>
                                    <div>
                                        <div id="usuario-nombre" class="text-lg font-semibold text-gray-900"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <div class="mb-1 text-sm font-medium text-gray-500">Fecha de Carga</div>
                                <div id="fecha-carga" class="font-semibold text-gray-900"></div>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <div class="mb-1 text-sm font-medium text-gray-500">Última Actualización</div>
                                <div id="fecha-actualizacion" class="font-semibold text-gray-900"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @vite(['resources/js/views/data/index.js'])
</x-app-layout>
