<x-app-layout>
    <style>
        [x-cloak] { display: none !important; }
    </style>
    <x-slot name="header">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between pr-6">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="fa-solid fa-file-arrow-up text-white text-2xl"></i>
                </div>

                <div>
                    <h2 class="text-2xl font-bold leading-tight">Carga Masiva de Datos</h2>
                    <p class="text-gray-500 text-sm">Sube archivos para importar información al sistema de forma rápida
                    </p>
                </div>
            </div>
            <x-button target="_blank" variant="primary" class="max-w-xs p-2 gap-2 justify-end"
                x-on:click="$dispatch('open-modal', 'add-data')">
                <x-icons.add class="w-6 h-6" aria-hidden="true" />
                Cargar archivo
            </x-button>
        </div>
    </x-slot>


    <div class="p-6">
        <div class="">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4">
                <h3 class="text-lg font-semibold mb-2 md:mb-0">Documentos Cargados</h3>
                <div class="flex gap-2 items-center">
                    <input type="text" id="searchInput" placeholder="Buscar documentos..."
                        class="px-3 py-2 border rounded text-sm" onkeydown="if(event.key==='Enter'){buscarArchivo();}">
                    <x-button
                        class="bg-light-cloud-blue border px-3 py-2 rounded text-sm flex items-center gap-2 hover:bg-light-red-800 text-white font-medium"
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

        <x-modal name="add-data" :show="$errors->any()" focusable>
            @slot('title')
            <h2 class="text-lg font-medium text-white dark:text-gray-100">
                Cargar Archivo de Datos
            </h2>
            @endslot
            <form id="upload-form" action="{{ route('data.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="grid gap-6 p-6">
                    <div class="space-y-4">


                        <div class="p-4 mb-4 rounded-lg bg-gray-50 dark:bg-gray-800">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Seleccione un archivo Excel (.xlsx, .xls) o CSV para cargar los datos. El archivo debe
                                tener un tamaño máximo de 10MB.
                            </p>
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

                        <!-- Spinner de carga -->
                        <div id="loading-spinner" class="hidden mt-4">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-12 h-12 border-b-2 border-blue-500 rounded-full animate-spin"></div>
                                <p class="mt-2 text-sm font-medium text-gray-600 dark:text-gray-400">Procesando archivo...</p>
                            </div>
                        </div>

                        <!-- Mensajes -->
                        <div id="error-message" class="hidden mt-2 text-sm text-red-600"></div>
                        <div id="success-message" class="hidden mt-2 text-sm text-green-600"></div>
                        <div id="uploaded-file-name" class="hidden mt-2 text-sm font-semibold text-green-700"></div>
                    </div>

                    <div class="flex justify-end gap-4">
                        <x-button variant="primary" type="button" x-on:click="$dispatch('close')">
                            Cancelar
                        </x-button>
                        <x-button variant="success" type="button" id="load-button" class="hidden">
                            Cargar
                        </x-button>
                    </div>
                </div>
            </form>
        </x-modal>

        <!-- Modal de Detalles de Carga -->
        <div id="modal-detalle-carga" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden" style="padding-top: 80px;">
            <div class="bg-white rounded-xl shadow-lg w-full max-w-6xl p-8 relative">
                <button class="absolute top-6 right-6 text-gray-400 hover:text-gray-600" onclick="cerrarModal()">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                <h2 class="text-2xl font-bold mb-6 text-gray-900">Detalles de Carga de Datos</h2>
             

                <!-- Información principal en 2 columnas -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                    <!-- Información del archivo -->
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl border border-blue-200 p-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-3 rounded-lg bg-blue-200">
                                <svg class="w-7 h-7 text-blue-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-6a2 2 0 012-2h2a2 2 0 012 2v6"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900">Información del Archivo</h3>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="bg-white rounded-lg p-4 border border-blue-200">
                                <div class="text-sm font-medium text-gray-500 mb-1">Nombre del Archivo</div>
                                <div id="archivo-nombre" class="text-gray-900 font-semibold break-words"></div>
                            </div>
                            <div class="bg-white rounded-lg p-4 border border-blue-200">
                                <div class="text-sm font-medium text-gray-500 mb-1">Tipo de Archivo</div>
                                <div class="text-gray-900 font-semibold">XLSX</div>
                            </div>
                            <div class="bg-white rounded-lg p-4 border border-blue-200">
                                <div class="text-sm font-medium text-gray-500 mb-1">Tamaño</div>
                                <div id="archivo-tamano" class="text-gray-900 font-semibold"></div>
                            </div>
                            <div class="bg-white rounded-lg p-4 border border-blue-200">
                                <div class="text-sm font-medium text-gray-500 mb-1">Registros Procesados</div>
                                <div id="archivo-registros" class="text-green-600 font-bold text-lg"></div>
                            </div>
                            <div class="bg-white rounded-lg p-4 border border-blue-200">
                                <div class="text-sm font-medium text-gray-500 mb-1">Estado</div>
                                <span id="archivo-estado" class="inline-block px-3 py-1 text-sm font-semibold rounded-full"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Información del usuario -->
                    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl border border-green-200 p-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-3 rounded-lg bg-green-200">
                                <svg class="w-7 h-7 text-green-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900">Información del Usuario</h3>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="bg-white rounded-lg p-4 border border-green-200">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex items-center justify-center w-12 h-12 bg-green-100 rounded-full">
                                        <svg class="w-7 h-7 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 12c2.7 0 8 1.34 8 4v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2c0-2.66 5.3-4 8-4zm0-2a4 4 0 100-8 4 4 0 000 8z"/>
                                        </svg>
                                    </span>
                                    <div>
                                        <div id="usuario-nombre" class="font-semibold text-gray-900 text-lg"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-white rounded-lg p-4 border border-green-200">
                                <div class="text-sm font-medium text-gray-500 mb-1">Fecha de Carga</div>
                                <div id="fecha-carga" class="text-gray-900 font-semibold"></div>
                            </div>
                            <div class="bg-white rounded-lg p-4 border border-green-200">
                                <div class="text-sm font-medium text-gray-500 mb-1">Última Actualización</div>
                                <div id="fecha-actualizacion" class="text-gray-900 font-semibold"></div>
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
                    loadButton.classList.remove('hidden');
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
                const loadingSpinner = document.getElementById('loading-spinner');

                if (!file) {
                    showError('Por favor, seleccione un archivo primero.');
                    return;
                }

                hideMessages();
                loadingSpinner.classList.remove('hidden');

                const formData = new FormData();
                formData.append('file', file);

                const xhr = new XMLHttpRequest();
                xhr.open('POST', form.action, true);
                xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');

                xhr.onload = function () {
                    loadingSpinner.classList.remove('hidden');

                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            const dataLoad = response.data.nombre_archivo;

                            // Iniciar consulta periódica del progreso
                            const progressInterval = setInterval(() => {
                                fetch(`/data/progress/${dataLoad.id}`)
                                    .then(res => res.json())
                                    .then(data => {
                                        if (data.estado === 'completado' || data.estado === 'error') {
                                            clearInterval(progressInterval);
                                            if (data.estado === 'completado') {
                                                // ✅ SweetAlert al cargar correctamente
                                                Swal.fire({
                                                    title: '¡Éxito!',
                                                    text: 'El archivo se cargó correctamente.',
                                                    icon: 'success',
                                                    timer: 5000,
                                                    showConfirmButton: true,
                                                    allowOutsideClick: true,
                                                    timerProgressBar: true,
                                                });

                                                setTimeout(() => {
                                                    window.location.reload();
                                                }, 2000);
                                            }
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Error al consultar el progreso:', error);
                                        clearInterval(progressInterval);
                                    });
                            }, 500); // Consultar cada medio segundo

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
                            document.getElementById('loading-spinner').classList.add('hidden');
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
            document.addEventListener('DOMContentLoaded', function() {
                console.log('Página de carga de datos inicializada');
            });

            // Función para abrir el modal y cargar los datos
            function abrirModalDetalleCarga(data) {
                const modal = document.getElementById('modal-detalle-carga');
                console.log('Modal encontrado:', modal);
                console.log('Datos a mostrar:', data);
                
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
                    console.error('Modal no encontrado');
                }
            }

            function verDetalleCarga(id) {
                console.log('Iniciando verDetalleCarga con ID:', id);
                fetch('/data/detalle/' + id)
                    .then(res => {
                        if (!res.ok) {
                            throw new Error('Error al obtener los datos');
                        }
                        return res.json();
                    })
                    .then(data => {
                        console.log('Datos recibidos:', data);
                        abrirModalDetalleCarga(data);
                    })
                    .catch(error => {
                        console.error('Error:', error);
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
        </script>
    </div>
</x-app-layout>