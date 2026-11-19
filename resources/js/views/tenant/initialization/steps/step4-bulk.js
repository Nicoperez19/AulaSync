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
            showError('El archivo es demasiado grande. El tama\u00f1o m\u00e1ximo permitido es 10MB.');
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
    formData.append('_token', (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? window.Step4BulkConfig?.csrfToken ?? ''));
    
    const xhr = new XMLHttpRequest();
    xhr.open('POST', (window.Step4BulkConfig?.processBulkUrl ?? '/tenant-initialization/process-bulk'), true);
    xhr.setRequestHeader('X-CSRF-TOKEN', (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? window.Step4BulkConfig?.csrfToken ?? ''));
    
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
                
                setTimeout(function() {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = (window.Step4BulkConfig?.completeBulkUrl ?? '/tenant-initialization/complete-bulk');
                    
                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? window.Step4BulkConfig?.csrfToken ?? '');
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
                showError('Error al subir el archivo. C\u00f3digo: ' + xhr.status);
            }
        }
    };
    
    xhr.onerror = function() {
        loadingSpinner.classList.add('hidden');
        uploadBtn.disabled = false;
        uploadBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        skipBtn.disabled = false;
        skipBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        showError('Error de conexi\u00f3n. Por favor, intente nuevamente.');
    };
    
    xhr.send(formData);
}

document.getElementById('semestre_selector').addEventListener('change', checkFormComplete);

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

Object.assign(window, { handleBulkFileSelect, clearBulkFile, uploadBulkFile });
