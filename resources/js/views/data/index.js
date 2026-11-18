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
            xhr.setRequestHeader('X-CSRF-TOKEN', (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? ''));
            xhr.setRequestHeader('Accept', 'application/json');

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
            if (selectedFileName) selectedFileName.textContent = '';
        }



Object.assign(window, { handleFileSelect, cerrarModal });
