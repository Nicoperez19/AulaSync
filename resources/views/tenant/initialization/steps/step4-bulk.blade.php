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
                <i class="fas fa-info-circle text-blue-500 mt-1 mr-3 flex-shrink-0"></i>
                <div>
                    <h4 class="font-semibold text-blue-800">&iquest;Qu&eacute; puede cargar?</h4>
                    <ul class="text-sm text-blue-700 mt-2 space-y-1">
                        <li><i class="fas fa-check mr-2"></i>Datos de profesores (RUN, nombre, email, carrera)</li>
                        <li><i class="fas fa-check mr-2"></i>Asignaturas y secciones</li>
                        <li><i class="fas fa-check mr-2"></i>Horarios y planificaci&oacute;n semanal</li>
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
                    Formatos: Excel (.xlsx, .xls) o CSV. M&aacute;ximo 10MB.
                </p>
            </div>
            
            <!-- Semestre Selection -->
            <div class="mt-4" id="semestre-section" style="display: none;">
                <label for="semestre_selector" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-calendar mr-2"></i>Semestre Acad&eacute;mico <span class="text-red-500">*</span>
                </label>
                @php
                    $anioAcad = \App\Helpers\SemesterHelper::getCurrentAcademicYear();
                    $semActual = \App\Helpers\SemesterHelper::getCurrentSemester();
                @endphp
                <select name="semestre_selector" id="semestre_selector" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    <option value="" disabled selected>-- Seleccione el Semestre Acad&eacute;mico --</option>
                    <option value="1">Primer Semestre {{ $anioAcad }} ({{ $anioAcad }}-1)</option>
                    <option value="2">Segundo Semestre {{ $anioAcad }} ({{ $anioAcad }}-2)</option>
                </select>
                <p class="text-sm text-gray-500 mt-1">
                    Seleccione obligatoriamente el semestre al que corresponden los datos del archivo.
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
                        class="inline-flex items-center px-6 py-3 bg-orange-600 text-white font-semibold rounded-lg hover:bg-orange-700 transition shadow-sm">
                    <i class="fas fa-cloud-upload-alt mr-2"></i>
                    Cargar Datos
                </button>
            </div>
        </form>

        <!-- Skip Option -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle text-yellow-500 mr-3 flex-shrink-0"></i>
                <p class="text-sm text-yellow-800">
                    Este paso es <strong>opcional</strong>. Puede omitirlo y realizar la carga de datos posteriormente desde el men&uacute; <strong>Carga Masiva</strong>.
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
                    class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition shadow-sm">
                Omitir y Continuar
                <i class="fas fa-arrow-right ml-2"></i>
            </button>
        </form>
    </div>
</div>

<div data-page-config="Step4BulkConfig" data-config="{{ json_encode(['processBulkUrl' => route('tenant.initialization.process-bulk'), 'completeBulkUrl' => route('tenant.initialization.complete-bulk'), 'csrfToken' => csrf_token()]) }}" class="hidden"></div>
@vite(['resources/js/views/tenant/initialization/steps/step4-bulk.js'])
