{{-- Step 5: Academic Periods --}}
<div class="p-8">
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-indigo-100 mb-4">
            <i class="fas fa-calendar-alt text-2xl text-indigo-600"></i>
        </div>
        <h2 class="text-2xl font-bold text-gray-800">Períodos Académicos</h2>
        <p class="text-gray-600 mt-2">Configure los períodos académicos del año {{ date('Y') }}</p>
    </div>

    <form action="{{ route('tenant.initialization.store-periods') }}" method="POST">
        @csrf
        
        <div class="space-y-6">
            <!-- Info Box -->
            <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4">
                <div class="flex items-start">
                    <i class="fas fa-calendar text-indigo-500 mt-1 mr-3"></i>
                    <div>
                        <h4 class="font-semibold text-indigo-800">¿Qué son los períodos académicos?</h4>
                        <p class="text-sm text-indigo-700 mt-2">
                            Los períodos académicos definen los semestres del año escolar.
                            Esto permite organizar los horarios y planificaciones por período.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Primer Semestre -->
            <div class="bg-white border border-gray-200 rounded-lg p-5">
                <div class="flex items-center mb-4">
                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                        <span class="font-bold text-blue-600">1</span>
                    </div>
                    <h4 class="font-semibold text-gray-800">Primer Semestre {{ date('Y') }}</h4>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="periodo1_inicio" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar-day mr-1"></i> Fecha de Inicio
                        </label>
                        <input type="date" name="periodo1_inicio" id="periodo1_inicio" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                               value="{{ old('periodo1_inicio', date('Y') . '-03-01') }}">
                    </div>
                    <div>
                        <label for="periodo1_fin" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar-check mr-1"></i> Fecha de Término
                        </label>
                        <input type="date" name="periodo1_fin" id="periodo1_fin" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                               value="{{ old('periodo1_fin', date('Y') . '-07-31') }}">
                    </div>
                </div>
            </div>

            <!-- Segundo Semestre -->
            <div class="bg-white border border-gray-200 rounded-lg p-5">
                <div class="flex items-center mb-4">
                    <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center mr-3">
                        <span class="font-bold text-green-600">2</span>
                    </div>
                    <h4 class="font-semibold text-gray-800">Segundo Semestre {{ date('Y') }}</h4>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="periodo2_inicio" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar-day mr-1"></i> Fecha de Inicio
                        </label>
                        <input type="date" name="periodo2_inicio" id="periodo2_inicio" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                               value="{{ old('periodo2_inicio', date('Y') . '-08-01') }}">
                    </div>
                    <div>
                        <label for="periodo2_fin" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar-check mr-1"></i> Fecha de Término
                        </label>
                        <input type="date" name="periodo2_fin" id="periodo2_fin" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                               value="{{ old('periodo2_fin', date('Y') . '-12-31') }}">
                    </div>
                </div>
            </div>

            <!-- Skip Warning -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex items-start">
                    <i class="fas fa-bell text-yellow-500 mt-1 mr-3"></i>
                    <div>
                        <h4 class="font-semibold text-yellow-800">Recordatorio</h4>
                        <p class="text-sm text-yellow-700 mt-1">
                            Si omite este paso, recibirá una notificación cada <strong>15 días</strong> 
                            en el Dashboard recordándole configurar los períodos académicos.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 flex justify-between">
            <a href="{{ route('tenant.initialization.previous') }}" 
               class="inline-flex items-center px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition">
                <i class="fas fa-arrow-left mr-2"></i>
                Anterior
            </a>
            
            <div class="flex gap-3">
                <button type="button" onclick="document.getElementById('skip-form').submit();"
                        class="inline-flex items-center px-6 py-3 bg-gray-400 text-white font-semibold rounded-lg hover:bg-gray-500 transition">
                    Omitir
                </button>
                <button type="submit" 
                        class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition">
                    <i class="fas fa-save mr-2"></i>
                    Guardar y Continuar
                </button>
            </div>
        </div>
    </form>
    
    <!-- Hidden form for skip -->
    <form id="skip-form" action="{{ route('tenant.initialization.skip-periods') }}" method="POST" class="hidden">
        @csrf
    </form>
</div>
