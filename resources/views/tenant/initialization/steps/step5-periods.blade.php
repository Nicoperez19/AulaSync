{{-- Step 5: Academic Periods --}}
<div class="p-8">
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-indigo-100 mb-4">
            <i class="fas fa-calendar-alt text-2xl text-indigo-600"></i>
        </div>
        <h2 class="text-2xl font-bold text-gray-800">Períodos Académicos</h2>
        <p class="text-gray-600 mt-2">Configure los períodos académicos del año</p>
    </div>

    <div class="space-y-6">
        <!-- Info Box -->
        <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4">
            <div class="flex items-start">
                <i class="fas fa-calendar text-indigo-500 mt-1 mr-3"></i>
                <div>
                    <h4 class="font-semibold text-indigo-800">¿Qué son los períodos académicos?</h4>
                    <p class="text-sm text-indigo-700 mt-2">
                        Los períodos académicos definen los semestres o trimestres del año escolar.
                        Esto permite organizar los horarios y planificaciones por período.
                    </p>
                </div>
            </div>
        </div>

        <!-- Current Year Periods -->
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h4 class="font-semibold text-gray-800 mb-4">
                <i class="fas fa-list mr-2"></i>Períodos sugeridos para {{ date('Y') }}
            </h4>
            <div class="space-y-3">
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-3"></i>
                        <span class="font-medium">Primer Semestre {{ date('Y') }}</span>
                    </div>
                    <span class="text-sm text-gray-500">Marzo - Julio</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-3"></i>
                        <span class="font-medium">Segundo Semestre {{ date('Y') }}</span>
                    </div>
                    <span class="text-sm text-gray-500">Agosto - Diciembre</span>
                </div>
            </div>
        </div>

        <!-- Skip Warning -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex items-start">
                <i class="fas fa-bell text-yellow-500 mt-1 mr-3"></i>
                <div>
                    <h4 class="font-semibold text-yellow-800">Recordatorio Importante</h4>
                    <p class="text-sm text-yellow-700 mt-1">
                        Si omite este paso, recibirá una notificación cada <strong>15 días</strong> 
                        en el Dashboard recordándole configurar los períodos académicos.
                    </p>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="text-center">
            <p class="text-sm text-gray-500 mb-4">
                Los períodos académicos se configuran automáticamente al realizar una carga masiva de datos.
                También puede configurarlos manualmente desde el Dashboard.
            </p>
        </div>
    </div>

    <div class="mt-8 flex justify-between">
        <a href="{{ route('tenant.initialization.previous') }}" 
           class="inline-flex items-center px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition">
            <i class="fas fa-arrow-left mr-2"></i>
            Anterior
        </a>
        <form action="{{ route('tenant.initialization.skip-periods') }}" method="POST" class="inline">
            @csrf
            <button type="submit" 
                    class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                Continuar
                <i class="fas fa-arrow-right ml-2"></i>
            </button>
        </form>
    </div>
</div>
