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
            <x-button target="_blank" variant="add" class="justify-end max-w-xs gap-2 p-2"
                x-on:click="$dispatch('open-modal', 'add-data')">
                <x-icons.add class="w-6 h-6" aria-hidden="true" />
                Cargar archivo
            </x-button>
        </div>
    </x-slot>


    <div class="px-6">
        <div class="flex flex-col pt-1 mb-4 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-2">
                <input type="text" id="searchInput" placeholder="Buscar documentos..."
                    class="px-3 py-2 text-sm border rounded" onkeydown="if(event.key==='Enter'){buscarArchivo();}">
                <x-button
                    class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-white border rounded bg-light-cloud-blue hover:bg-light-red-800"
                    onclick="buscarArchivo()">

                    Buscar
                </x-button>
            </div>
        </div>
        <livewire:data-load-table :search="request()->get('search')" />
        <div class="flex justify-end mt-4">
            @if(isset($dataLoads))
                {{ $dataLoads->links() }}
            @endif
        </div>
    </div>

    <x-modal name="add-data" :show="$errors->any()" focusable x-on:close="limpiarFormulario()">
        @slot('title')
        <div class="relative flex items-center justify-between p-2 bg-red-700">
            <div class="flex items-center gap-3">
                <div class="p-4 bg-red-100 rounded-full">
                    <i class="text-xl text-red-600 fa-solid fa-upload"></i>
                </div>
                <h2 class="text-2xl font-bold text-white">
                    Cargar Archivo de Datos
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
                            Semestre Académico
                        </label>
                        <select id="semestre_selector" name="semestre_selector"
                            class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">Seleccionar semestre...</option>
                            <option value="1">Primer Semestre</option>
                            <option value="2">Segundo Semestre</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Selecciona el semestre al que corresponden los datos del archivo. El año será
                            automáticamente el actual ({{ date('Y') }}).
                        </p>
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

    <script>
        function handleFileSelect(input) {
            const loadButton = document.getElementById('load-button');
            const selectedFileName = document.getElementById('selected-file-name');
            const file = input.files[0];

            if (file) {
                const validTypes = ['.xlsx', '.xls', '.csv'];
                const fileExtension = '.' + file.name.split('.').pop().toLowerCase();

                if (!validTypes.includes(fileExtension)) {
                    showError('Por favor, seleccione un archivo Excel (.xlsx, .xls) o CSV válido.');
                    input.value = '';
                    loadButton.classList.add('hidden');
                    selectedFileName.textContent = '';
                    return;
                }

                if (file.size > 10 * 1024 * 1024) {
                    showError('El archivo es demasiado grande. El tamaño máximo permitido es 10MB.');
                    input.value = '';
                    loadButton.classList.add('hidden');
                    selectedFileName.textContent = '';
                    return;
                }

                selectedFileName.className = 'mt-2 text-sm font-medium text-green-600';
                selectedFileName.textContent = `Archivo seleccionado: ${file.name}`;

                // Verificar si se ha seleccionado semestre
                const semestreSelector = document.getElementById('semestre_selector');

                if (semestreSelector.value) {
                    loadButton.classList.remove('hidden');
                }

                hideMessages();
            } else {
                loadButton.classList.add('hidden');
                selectedFileName.textContent = '';
            }
        }

        function showError(message) {
            const errorDiv = document.getElementById('error-message');
            errorDiv.innerText = message;
            errorDiv.classList.remove('hidden');
        }

        function hideMessages() {
            document.getElementById('error-message').classList.add('hidden');
            document.getElementById('success-message').classList.add('hidden');
            document.getElementById('uploaded-file-name').classList.add('hidden');
        }

        document.getElementById('load-button').addEventListener('click', function () {
            const form = document.getElementById('upload-form');
            const fileInput = document.getElementById('file-upload');
            const file = fileInput.files[0];
            const semestreSelector = document.getElementById('semestre_selector');
            const loadingSpinner = document.getElementById('loading-spinner');

            if (!file) {
                showError('Por favor, seleccione un archivo primero.');
                return;
            }

            if (!semestreSelector.value) {
                showError('Por favor, seleccione un semestre.');
                return;
            }

            hideMessages();
            loadingSpinner.classList.remove('hidden');

            const formData = new FormData();
            formData.append('file', file);
            formData.append('semestre_selector', semestreSelector.value);

            const xhr = new XMLHttpRequest();
            xhr.open('POST', form.action, true);
            xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');

            xhr.onload = function () {
                loadingSpinner.classList.add('hidden');

                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        const dataLoad = response.data.nombre_archivo;
                        let alertShown = false; // Bandera para evitar múltiples alerts

                        // Iniciar consulta periódica del progreso
                        const progressInterval = setInterval(() => {
                            fetch(`/data/progress/${dataLoad.id}`)
                                .then(res => res.json())
                                .then(data => {
                                    if (data.estado === 'completado' || data.estado === 'error') {
                                        clearInterval(progressInterval);
                                        if (data.estado === 'completado' && !alertShown) {
                                            alertShown = true; // Marcar como mostrado
                                            // ✅ SweetAlert único al cargar correctamente
                                            Swal.fire({
                                                title: '¡Éxito!',
                                                text: 'El archivo se cargó correctamente.',
                                                icon: 'success',
                                                timer: 2000,
                                                showConfirmButton: false,
                                                allowOutsideClick: true,
                                                timerProgressBar: true,
                                            }).then(() => {
                                                window.location.reload();
                                            });
                                        } else if (data.estado === 'error' && !alertShown) {
                                            alertShown = true; // Marcar como mostrado
                                            Swal.fire({
                                                title: 'Error',
                                                text: 'Error al procesar el archivo',
                                                icon: 'error',
                                                confirmButtonText: 'Aceptar',
                                            });
                                        }
                                    }
                                })
                                .catch(error => {
                                    // Error al consultar el progreso
                                    clearInterval(progressInterval);
                                    if (!alertShown) {
                                        alertShown = true; // Marcar como mostrado
                                        Swal.fire({
                                            title: 'Error',
                                            text: 'Error al verificar el progreso del archivo',
                                            icon: 'error',
                                            confirmButtonText: 'Aceptar',
                                        });
                                    }
                                });
                        }, 1000); // Consultar cada segundo

                    } catch (e) {
                        showError('Error al procesar la respuesta del servidor');
                    }
                } else if (xhr.status === 422) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        Swal.fire({
                            title: 'Error',
                            text: response.message || 'Error al subir el archivo',
                            icon: 'error',
                            confirmButtonText: 'Aceptar',
                        });
                    } catch (e) {
                        showError('Error al subir el archivo');
                    }
                } else {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        showError(response.message || 'Error al subir el archivo');
                    } catch (e) {
                        showError('Error al subir el archivo');
                    }
                }
            };

            xhr.onerror = function () {
                loadingSpinner.classList.add('hidden');
                showError('Error de conexión al subir el archivo');
            };

            xhr.send(formData);
        });

        function buscarArchivo() {
            const search = document.getElementById('searchInput').value;
            window.livewire.emit('buscarArchivo', search);
        }

        // Inicialización cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', function () {
            // Página de carga de datos inicializada

            // Event listeners para los selectores
            const semestreSelector = document.getElementById('semestre_selector');
            const loadButton = document.getElementById('load-button');
            const fileInput = document.getElementById('file-upload');

            function checkFormComplete() {
                const file = fileInput.files[0];
                const semestre = semestreSelector.value;

                if (file && semestre) {
                    loadButton.classList.remove('hidden');
                } else {
                    loadButton.classList.add('hidden');
                }
            }

            semestreSelector.addEventListener('change', checkFormComplete);

            // Drag and Drop functionality
            const dropZone = document.querySelector('.border-2.border-gray-300.border-dashed');

            if (dropZone) {
                dropZone.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropZone.classList.add('border-blue-500', 'bg-blue-50', 'border-2');
                    dropZone.classList.remove('border-gray-300');
                });

                dropZone.addEventListener('dragleave', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropZone.classList.remove('border-blue-500', 'bg-blue-50');
                    dropZone.classList.add('border-gray-300');
                });

                dropZone.addEventListener('drop', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropZone.classList.remove('border-blue-500', 'bg-blue-50');
                    dropZone.classList.add('border-gray-300');

                    const files = e.dataTransfer.files;
                    if (files && files.length > 0) {
                        fileInput.files = files;
                        handleFileSelect(fileInput);
                    }
                });
            }
        });

        // Función para abrir el modal y cargar los datos
        function abrirModalDetalleCarga(data) {
            const modal = document.getElementById('modal-detalle-carga');
                            // Modal encontrado
                // Datos a mostrar

            if (modal) {


                // Llenar información del archivo
                document.getElementById('archivo-nombre').textContent = data.nombre_archivo || 'N/A';
                document.getElementById('archivo-tamano').textContent = data.tamano || 'N/A';
                document.getElementById('archivo-registros').textContent = data.registros_cargados || 0;

                // Configurar estado del archivo
                const archivoEstado = document.getElementById('archivo-estado');
                if (data.estado === 'procesado' || data.estado === 'completado') {
                    archivoEstado.className = 'inline-block px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700';
                } else if (data.estado === 'error') {
                    archivoEstado.className = 'inline-block px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700';
                } else if (data.estado === 'pendiente' || data.estado === 'procesando') {
                    archivoEstado.className = 'inline-block px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-700';
                } else {
                    archivoEstado.className = 'inline-block px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-700';
                }
                archivoEstado.textContent = data.estado ? data.estado.charAt(0).toUpperCase() + data.estado.slice(1) : 'Desconocido';

                // Llenar información del usuario
                document.getElementById('usuario-nombre').textContent = data.usuario_nombre || 'N/A';
                document.getElementById('fecha-carga').textContent = data.fecha_carga || 'N/A';
                document.getElementById('fecha-actualizacion').textContent = data.fecha_actualizacion || 'N/A';


                // Mostrar el modal
                modal.classList.remove('hidden');
                    } else {
            // Modal no encontrado
        }
        }

        function verDetalleCarga(id) {
            // Iniciando verDetalleCarga con ID
            fetch('/data/detalle/' + id)
                .then(res => {
                    if (!res.ok) {
                        throw new Error('Error al obtener los datos');
                    }
                    return res.json();
                })
                .then(data => {
                    // Datos recibidos
                    abrirModalDetalleCarga(data);
                })
                .catch(error => {
                    // Error
                    Swal.fire({
                        title: 'Error',
                        text: 'No se pudo cargar los detalles del archivo',
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                });
        }

        function cerrarModal() {
            const modal = document.getElementById('modal-detalle-carga');
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        function limpiarFormulario() {
            // Limpiar el formulario
            const form = document.getElementById('upload-form');
            if (form) {
                form.reset();
            }

            // Ocultar el botón de cargar
            const loadButton = document.getElementById('load-button');
            if (loadButton) {
                loadButton.classList.add('hidden');
            }

            // Limpiar mensajes
            const errorMessage = document.getElementById('error-message');
            const successMessage = document.getElementById('success-message');
            const uploadedFileName = document.getElementById('uploaded-file-name');
            const selectedFileName = document.getElementById('selected-file-name');

            if (errorMessage) errorMessage.classList.add('hidden');
            if (successMessage) successMessage.classList.add('hidden');
            if (uploadedFileName) uploadedFileName.classList.add('hidden');
            if (selectedFileName) selectedFileName.textContent = '';
        }
    </script>
</x-app-layout>
