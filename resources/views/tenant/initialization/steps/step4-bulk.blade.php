{{-- Step 4: Bulk Data Loading --}}
<div class="p-8">
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-orange-100 mb-4">
            <i class="fas fa-upload text-2xl text-orange-600"></i>
        </div>
        <h2 class="text-2xl font-bold text-gray-800">Carga Masiva de Datos</h2>
        <p class="text-gray-600 mt-2">Importe profesores, asignaturas y horarios desde un archivo Excel</p>
    </div>

    <div class="space-y-6">
        <!-- Info Box -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-start">
                <i class="fas fa-info-circle text-blue-500 mt-1 mr-3"></i>
                <div>
                    <h4 class="font-semibold text-blue-800">¿Qué puede cargar?</h4>
                    <ul class="text-sm text-blue-700 mt-2 space-y-1">
                        <li><i class="fas fa-check mr-2"></i>Datos de profesores (RUN, nombre, email, carrera)</li>
                        <li><i class="fas fa-check mr-2"></i>Asignaturas y secciones</li>
                        <li><i class="fas fa-check mr-2"></i>Horarios y planificación semanal</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Upload Form -->
        <form id="bulk-upload-form" enctype="multipart/form-data">
            @csrf
            
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center bg-gray-50" id="drop-zone">
                <div id="upload-area">
                    <i class="fas fa-file-excel text-4xl text-gray-400 mb-4"></i>
                    <p class="text-gray-600 mb-4">
                        Seleccione o arrastre un archivo Excel (.xlsx, .xls) o CSV
                    </p>
                    
                    <label for="bulk_file" class="cursor-pointer inline-flex items-center px-4 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition">
                        <i class="fas fa-folder-open mr-2"></i>
                        Seleccionar Archivo
                        <input type="file" name="file" id="bulk_file" accept=".xlsx,.xls,.csv" class="hidden" onchange="handleBulkFileSelect(this)">
                    </label>
                    
                    <div id="selected-file-info" class="mt-4 hidden">
                        <div class="inline-flex items-center px-4 py-2 bg-green-100 text-green-800 rounded-lg">
                            <i class="fas fa-file-excel mr-2"></i>
                            <span id="selected-file-name"></span>
                            <button type="button" onclick="clearBulkFile()" class="ml-3 text-red-500 hover:text-red-700">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <p class="text-xs text-gray-500 mt-4">
                    Formatos: Excel (.xlsx, .xls) o CSV. Máximo 10MB.
                </p>
            </div>
            
            <!-- Semestre Selection -->
            <div class="mt-4" id="semestre-section" style="display: none;">
                <label for="semestre_selector" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-calendar mr-2"></i>Semestre Académico
                </label>
                <select name="semestre_selector" id="semestre_selector" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    <option value="">Seleccionar semestre...</option>
                    <option value="1">Primer Semestre {{ date('Y') }}</option>
                    <option value="2">Segundo Semestre {{ date('Y') }}</option>
                </select>
                <p class="text-sm text-gray-500 mt-1">
                    Seleccione el semestre al que corresponden los datos del archivo.
                </p>
            </div>
            
            <!-- Loading Spinner -->
            <div id="loading-spinner" class="hidden mt-6">
                <div class="flex flex-col items-center justify-center p-6 bg-blue-50 rounded-lg">
                    <div class="w-12 h-12 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mb-3"></div>
                    <p class="text-blue-700 font-medium">Procesando archivo...</p>
                    <p class="text-sm text-blue-600 mt-1">Esto puede tardar unos minutos</p>
                </div>
            </div>
            
            <!-- Messages -->
            <div id="error-message" class="hidden mt-4 p-4 bg-red-100 border border-red-300 rounded-lg text-red-700"></div>
            <div id="success-message" class="hidden mt-4 p-4 bg-green-100 border border-green-300 rounded-lg text-green-700"></div>
            
            <!-- Upload Button -->
            <div class="mt-4 text-center" id="upload-button-section" style="display: none;">
                <button type="button" id="upload-btn" onclick="uploadBulkFile()"
                        class="inline-flex items-center px-6 py-3 bg-orange-600 text-white font-semibold rounded-lg hover:bg-orange-700 transition">
                    <i class="fas fa-cloud-upload-alt mr-2"></i>
                    Cargar Datos
                </button>
            </div>
        </form>

        <!-- Skip Option -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle text-yellow-500 mr-3"></i>
                <p class="text-sm text-yellow-800">
                    Este paso es <strong>opcional</strong>. Puede omitirlo y realizar la carga de datos posteriormente desde el menú <strong>Carga Masiva</strong>.
                </p>
            </div>
        </div>
    </div>

    <div class="mt-8 flex justify-between">
        <a href="{{ route('tenant.initialization.previous') }}" 
           class="inline-flex items-center px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition">
            <i class="fas fa-arrow-left mr-2"></i>
            Anterior
        </a>
        <form action="{{ route('tenant.initialization.skip-bulk') }}" method="POST" class="inline" id="skip-form">
            @csrf
            <button type="submit" id="skip-btn"
                    class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                Omitir y Continuar
                <i class="fas fa-arrow-right ml-2"></i>
            </button>
        </form>
    </div>
</div>

<script>
function handleBulkFileSelect(input) {
    const file = input.files[0];
    if (file) {
        const allowedTypes = [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel',
            'text/csv'
        ];
        
        if (!allowedTypes.includes(file.type) && !file.name.match(/\.(xlsx|xls|csv)$/i)) {
            showError('Por favor seleccione un archivo Excel (.xlsx, .xls) o CSV.');
            input.value = '';
            return;
        }
        
        if (file.size > 10 * 1024 * 1024) {
            showError('El archivo es demasiado grande. El tamaño máximo permitido es 10MB.');
            input.value = '';
            return;
        }
        
        document.getElementById('selected-file-name').textContent = file.name;
        document.getElementById('selected-file-info').classList.remove('hidden');
        document.getElementById('semestre-section').style.display = 'block';
        hideMessages();
        checkFormComplete();
    }
}

function clearBulkFile() {
    document.getElementById('bulk_file').value = '';
    document.getElementById('selected-file-info').classList.add('hidden');
    document.getElementById('semestre-section').style.display = 'none';
    document.getElementById('upload-button-section').style.display = 'none';
    hideMessages();
}

function checkFormComplete() {
    const file = document.getElementById('bulk_file').files[0];
    const semestre = document.getElementById('semestre_selector').value;
    
    if (file && semestre) {
        document.getElementById('upload-button-section').style.display = 'block';
    } else {
        document.getElementById('upload-button-section').style.display = 'none';
    }
}

function showError(message) {
    const errorDiv = document.getElementById('error-message');
    errorDiv.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i>' + message;
    errorDiv.classList.remove('hidden');
}

function showSuccess(message) {
    const successDiv = document.getElementById('success-message');
    successDiv.innerHTML = '<i class="fas fa-check-circle mr-2"></i>' + message;
    successDiv.classList.remove('hidden');
}

function hideMessages() {
    document.getElementById('error-message').classList.add('hidden');
    document.getElementById('success-message').classList.add('hidden');
}

function uploadBulkFile() {
    const fileInput = document.getElementById('bulk_file');
    const semestreSelector = document.getElementById('semestre_selector');
    const loadingSpinner = document.getElementById('loading-spinner');
    const uploadBtn = document.getElementById('upload-btn');
    const skipBtn = document.getElementById('skip-btn');
    
    const file = fileInput.files[0];
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
    uploadBtn.disabled = true;
    uploadBtn.classList.add('opacity-50', 'cursor-not-allowed');
    skipBtn.disabled = true;
    skipBtn.classList.add('opacity-50', 'cursor-not-allowed');
    
    const formData = new FormData();
    formData.append('file', file);
    formData.append('semestre_selector', semestreSelector.value);
    formData.append('_token', '{{ csrf_token() }}');
    
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '{{ route("tenant.initialization.process-bulk") }}', true);
    xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
    
    xhr.onload = function() {
        loadingSpinner.classList.add('hidden');
        uploadBtn.disabled = false;
        uploadBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        skipBtn.disabled = false;
        skipBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        
        if (xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                const data = response.data;
                
                const totalProcesado = (data.profesores_procesados || 0) + (data.asignaturas_procesadas || 0) + (data.horarios_procesados || 0);
                
                if (totalProcesado === 0) {
                    // No se procesó nada
                    let message = 'El archivo no contiene datos de esta sede.<br>';
                    message += '<strong>Filas omitidas:</strong> ' + (data.filas_omitidas || 0) + ' (pertenecen a otras sedes)';
                    showError(message);
                    return;
                }
                
                let message = 'Archivo procesado exitosamente.<br>';
                message += '<strong>Profesores:</strong> ' + (data.profesores_procesados || 0) + '<br>';
                message += '<strong>Asignaturas:</strong> ' + (data.asignaturas_procesadas || 0) + '<br>';
                message += '<strong>Horarios:</strong> ' + (data.horarios_procesados || 0);
                
                if (data.filas_omitidas > 0) {
                    message += '<br><small class="text-yellow-600">(' + data.filas_omitidas + ' filas omitidas por ser de otra sede)</small>';
                }
                
                showSuccess(message);
                
                // Esperar 2 segundos y luego avanzar usando complete-bulk
                setTimeout(function() {
                    // Crear formulario para enviar a complete-bulk
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route("tenant.initialization.complete-bulk") }}';
                    
                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';
                    form.appendChild(csrfToken);
                    
                    document.body.appendChild(form);
                    form.submit();
                }, 2000);
                
            } catch (e) {
                showError('Error al procesar la respuesta del servidor.');
            }
        } else {
            try {
                const response = JSON.parse(xhr.responseText);
                showError(response.message || 'Error al procesar el archivo.');
            } catch (e) {
                showError('Error al subir el archivo. Código: ' + xhr.status);
            }
        }
    };
    
    xhr.onerror = function() {
        loadingSpinner.classList.add('hidden');
        uploadBtn.disabled = false;
        uploadBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        skipBtn.disabled = false;
        skipBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        showError('Error de conexión. Por favor, intente nuevamente.');
    };
    
    xhr.send(formData);
}

// Event listener para el selector de semestre
document.getElementById('semestre_selector').addEventListener('change', checkFormComplete);

// Drag and drop
const dropZone = document.getElementById('drop-zone');
if (dropZone) {
    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.classList.add('border-blue-500', 'bg-blue-50');
        this.classList.remove('border-gray-300');
    });
    
    dropZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.classList.remove('border-blue-500', 'bg-blue-50');
        this.classList.add('border-gray-300');
    });
    
    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.classList.remove('border-blue-500', 'bg-blue-50');
        this.classList.add('border-gray-300');
        
        const files = e.dataTransfer.files;
        if (files && files.length > 0) {
            document.getElementById('bulk_file').files = files;
            handleBulkFileSelect(document.getElementById('bulk_file'));
        }
    });
}
</script>
