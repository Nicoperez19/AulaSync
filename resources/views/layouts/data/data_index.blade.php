<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight" style="font-style: oblique;">
                {{ __('Carga Información') }}
            </h2>
        </div>
    </x-slot>

    <div class="p-6 bg-white rounded-lg shadow-lg">
        <div class="flex items-center justify-between mt-4 mb-[2rem]">
            <!-- Buscador pequeño a la izquierda -->
            <div class="w-2/3">
                <input type="text" id="searchInput" onkeyup="searchTable()" placeholder=""
                    class="w-full px-4 py-2 border rounded dark:bg-gray-700 dark:text-white">
            </div>
            <x-button target="_blank" variant="primary" class="max-w-xs gap-2"
                x-on:click="$dispatch('open-modal', 'add-data')">
                <x-icons.add class="w-6 h-6" aria-hidden="true" />
            </x-button>

        </div>

        <livewire:data-load-table />

        <x-modal name="add-data" :show="$errors->any()" focusable>
            <form id="upload-form" action="{{ route('data.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="grid gap-6 p-6">
                    <div class="space-y-4">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            Cargar Archivo de Datos
                        </h2>

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
                                    <div class="flex text-sm text-gray-600 dark:text-gray-400">
                                        <label for="file-upload"
                                            class="relative font-medium text-blue-600 bg-white rounded-md cursor-pointer dark:bg-gray-800 hover:text-blue-500">
                                            <span>Subir un archivo</span>
                                            <input id="file-upload" name="file" type="file" class="sr-only"
                                                accept=".xlsx,.xls,.csv" onchange="handleFileSelect(this)">
                                        </label>
                                        <p class="pl-1">o arrastrar y soltar</p>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Excel o CSV hasta 10MB
                                    </p>
                                    <!-- Nombre del archivo seleccionado -->
                                    <div id="selected-file-name" class="mt-2 text-sm font-medium text-gray-700 dark:text-gray-300"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Barra de progreso -->
                        <div id="upload-progress" class="hidden mt-4">
                            <div class="relative pt-1">
                                <div class="flex h-2 overflow-hidden text-xs bg-blue-200 rounded">
                                    <div id="progress-bar"
                                        class="flex flex-col justify-center text-center text-white transition-all duration-500 bg-blue-500 shadow-none whitespace-nowrap"
                                        style="width: 0%"></div>
                                </div>
                                <div id="progress-text" class="mt-1 text-sm text-center text-gray-600 dark:text-gray-400">0%</div>
                            </div>
                        </div>

                        <!-- Mensajes -->
                        <div id="error-message" class="hidden mt-2 text-sm text-red-600"></div>
                        <div id="success-message" class="hidden mt-2 text-sm text-green-600"></div>
                        <div id="uploaded-file-name" class="hidden mt-2 text-sm font-semibold text-green-700"></div>
                    </div>

                    <div class="flex justify-end gap-4">
                        <x-button variant="secondary" x-on:click="$dispatch('close')">
                            Cancelar
                        </x-button>
                        <x-button variant="primary" type="button" id="load-button" class="hidden">
                            Cargar
                        </x-button>
                        <x-button variant="primary" type="button" id="upload-button" class="hidden">
                            Guardar
                        </x-button>
                    </div>
                </div>
            </form>
        </x-modal>

        <script>
            function handleFileSelect(input) {
                const loadButton = document.getElementById('load-button');
                const selectedFileName = document.getElementById('selected-file-name');
                const file = input.files[0];
                
                if (file) {
                    // Validar el tipo de archivo
                    const validTypes = ['.xlsx', '.xls', '.csv'];
                    const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
                    
                    if (!validTypes.includes(fileExtension)) {
                        showError('Por favor, seleccione un archivo Excel (.xlsx, .xls) o CSV válido.');
                        input.value = '';
                        loadButton.classList.add('hidden');
                        selectedFileName.textContent = '';
                        return;
                    }
                    
                    // Validar el tamaño del archivo (10MB)
                    if (file.size > 10 * 1024 * 1024) {
                        showError('El archivo es demasiado grande. El tamaño máximo permitido es 10MB.');
                        input.value = '';
                        loadButton.classList.add('hidden');
                        selectedFileName.textContent = '';
                        return;
                    }
                    
                    // Mostrar el nombre del archivo seleccionado
                    selectedFileName.textContent = `Archivo seleccionado: ${file.name}`;
                    
                    // Mostrar el botón de cargar
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

            function updateProgress(percent) {
                const progressBar = document.getElementById('progress-bar');
                const progressText = document.getElementById('progress-text');
                progressBar.style.width = percent + '%';
                progressText.textContent = percent + '%';
            }

            document.getElementById('load-button').addEventListener('click', function () {
                document.getElementById('upload-button').classList.remove('hidden');
                document.getElementById('upload-progress').classList.remove('hidden');
                this.classList.add('hidden');
                updateProgress(0);
            });

            document.getElementById('upload-button').addEventListener('click', function () {
                const form = document.getElementById('upload-form');
                const fileInput = document.getElementById('file-upload');
                const file = fileInput.files[0];

                if (!file) {
                    showError('Por favor, seleccione un archivo primero.');
                    return;
                }

                const errorDiv = document.getElementById('error-message');
                const successDiv = document.getElementById('success-message');
                const uploadedFileDiv = document.getElementById('uploaded-file-name');
                const progressDiv = document.getElementById('upload-progress');

                hideMessages();
                progressDiv.classList.remove('hidden');
                updateProgress(0);

                const formData = new FormData();
                formData.append('file', file);

                const xhr = new XMLHttpRequest();
                xhr.open('POST', form.action, true);
                xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');

                xhr.upload.addEventListener('progress', function (e) {
                    if (e.lengthComputable) {
                        const percent = Math.round((e.loaded / e.total) * 100);
                        updateProgress(percent);
                    }
                });

                xhr.onload = function () {
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            successDiv.innerText = response.message;
                            successDiv.classList.remove('hidden');
                            uploadedFileDiv.innerText = "Archivo: " + response.data.nombre_archivo;
                            uploadedFileDiv.classList.remove('hidden');
                            updateProgress(100);

                            // Esperar un momento antes de recargar
                            setTimeout(() => {
                                window.location.reload();
                            }, 2000);
                        } catch (e) {
                            showError('Error al procesar la respuesta del servidor');
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

                // Error en la conexión
                xhr.onerror = function () {
                    showError('Error en la conexión al servidor');
                };

                xhr.send(formData);
            });
        </script>
    </div>

</x-app-layout>
