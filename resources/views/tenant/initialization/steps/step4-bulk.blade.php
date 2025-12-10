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

        <!-- Upload Section -->
        <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center bg-gray-50">
            <i class="fas fa-file-excel text-4xl text-gray-400 mb-4"></i>
            <p class="text-gray-600 mb-4">
                Para realizar la carga masiva, puede acceder a esta funcionalidad desde el menú
                <strong>Carga Masiva</strong> en el Dashboard después de completar la configuración.
            </p>
            <a href="{{ route('data.index') }}" target="_blank" 
               class="inline-flex items-center px-4 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition">
                <i class="fas fa-external-link-alt mr-2"></i>
                Ir a Carga Masiva (Nueva pestaña)
            </a>
        </div>

        <!-- Skip Option -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle text-yellow-500 mr-3"></i>
                <p class="text-sm text-yellow-800">
                    Este paso es <strong>opcional</strong>. Puede omitirlo y realizar la carga de datos posteriormente.
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
        <form action="{{ route('tenant.initialization.skip-bulk') }}" method="POST" class="inline">
            @csrf
            <button type="submit" 
                    class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                Continuar
                <i class="fas fa-arrow-right ml-2"></i>
            </button>
        </form>
    </div>
</div>
