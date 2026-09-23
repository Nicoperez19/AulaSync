{{-- Step 5: Academic Periods --}}
<div class="p-8">
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-100 mb-4">
            <i class="fas fa-calendar-alt text-2xl text-blue-600"></i>
        </div>
        <h2 class="text-2xl font-bold text-gray-800">Per&iacute;odos Acad&eacute;micos</h2>
        <p class="text-gray-600 mt-2">Configure los per&iacute;odos acad&eacute;micos del a&ntilde;o {{ date('Y') }}</p>
    </div>

    <form action="{{ route('tenant.initialization.store-periods') }}" method="POST">
        @csrf
        
        <div class="space-y-6">
            <!-- Info Box -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-start">
                    <i class="fas fa-calendar text-blue-500 mt-1 mr-3 flex-shrink-0"></i>
                    <div>
                        <h4 class="font-semibold text-blue-800">&iquest;Qu&eacute; son los per&iacute;odos acad&eacute;micos?</h4>
                        <p class="text-sm text-blue-700 mt-1">
                            Los per&iacute;odos acad&eacute;micos definen los semestres del a&ntilde;o escolar.
                            Esto permite organizar los horarios y planificaciones por per&iacute;odo.
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
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                               value="{{ old('periodo1_inicio', date('Y') . '-03-01') }}">
                    </div>
                    <div>
                        <label for="periodo1_fin" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar-check mr-1"></i> Fecha de T&eacute;rmino
                        </label>
                        <input type="date" name="periodo1_fin" id="periodo1_fin" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                               value="{{ old('periodo1_fin', date('Y') . '-07-31') }}">
                    </div>
                </div>
            </div>

            <!-- Segundo Semestre -->
            <div class="bg-white border border-gray-200 rounded-lg p-5">
                <div class="flex items-center mb-4">
                    <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center mr-3">
                        <span class="font-bold text-emerald-600">2</span>
                    </div>
                    <h4 class="font-semibold text-gray-800">Segundo Semestre {{ date('Y') }}</h4>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="periodo2_inicio" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar-day mr-1"></i> Fecha de Inicio
                        </label>
                        <input type="date" name="periodo2_inicio" id="periodo2_inicio" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                               value="{{ old('periodo2_inicio', date('Y') . '-08-01') }}">
                    </div>
                    <div>
                        <label for="periodo2_fin" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar-check mr-1"></i> Fecha de T&eacute;rmino
                        </label>
                        <input type="date" name="periodo2_fin" id="periodo2_fin" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                               value="{{ old('periodo2_fin', date('Y') . '-12-31') }}">
                    </div>
                </div>
            </div>

            <!-- Skip Warning -->
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                <div class="flex items-start">
                    <i class="fas fa-bell text-amber-500 mt-1 mr-3 flex-shrink-0"></i>
                    <div>
                        <h4 class="font-semibold text-amber-800">Recordatorio</h4>
                        <p class="text-sm text-amber-700 mt-1">
                            Si omite este paso, recibir&aacute; una notificaci&oacute;n cada <strong>15 d&iacute;as</strong> 
                            en el Dashboard record&aacute;ndole configurar los per&iacute;odos acad&eacute;micos.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 flex justify-between items-center">
            <a href="{{ route('tenant.initialization.previous') }}" 
               class="inline-flex items-center px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition">
                <i class="fas fa-arrow-left mr-2"></i>
                Anterior
            </a>
            
            <div class="flex gap-3">
                <button type="button" onclick="document.getElementById('skip-form').submit();"
                        class="inline-flex items-center px-6 py-3 bg-gray-400 text-white font-semibold rounded-lg hover:bg-gray-500 transition">
                    Omitir
                </button>
                <button type="submit" 
                        class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 shadow-sm transition">
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
