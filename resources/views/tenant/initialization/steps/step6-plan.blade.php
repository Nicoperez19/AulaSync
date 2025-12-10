{{-- Step 6: Digital Floor Plan --}}
<div class="p-8">
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-teal-100 mb-4">
            <i class="fas fa-map text-2xl text-teal-600"></i>
        </div>
        <h2 class="text-2xl font-bold text-gray-800">Plano Digital y Espacios</h2>
        <p class="text-gray-600 mt-2">Configure el plano digital y los espacios de su sede</p>
    </div>

    <div class="space-y-6">
        <!-- Info Box -->
        <div class="bg-teal-50 border border-teal-200 rounded-lg p-4">
            <div class="flex items-start">
                <i class="fas fa-map-marked-alt text-teal-500 mt-1 mr-3"></i>
                <div>
                    <h4 class="font-semibold text-teal-800">¿Qué es el Plano Digital?</h4>
                    <p class="text-sm text-teal-700 mt-2">
                        El plano digital permite visualizar los espacios de su sede en un mapa interactivo.
                        Puede ver el estado de ocupación de cada sala en tiempo real.
                    </p>
                </div>
            </div>
        </div>

        <!-- Features List -->
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h4 class="font-semibold text-gray-800 mb-4">
                <i class="fas fa-cogs mr-2"></i>Funcionalidades disponibles
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-start p-3 bg-gray-50 rounded-lg">
                    <i class="fas fa-door-open text-blue-500 mr-3 mt-1"></i>
                    <div>
                        <span class="font-medium">Gestión de Espacios</span>
                        <p class="text-sm text-gray-500">Salas, laboratorios, auditorios</p>
                    </div>
                </div>
                <div class="flex items-start p-3 bg-gray-50 rounded-lg">
                    <i class="fas fa-building text-green-500 mr-3 mt-1"></i>
                    <div>
                        <span class="font-medium">Pisos y Facultades</span>
                        <p class="text-sm text-gray-500">Organización por edificio</p>
                    </div>
                </div>
                <div class="flex items-start p-3 bg-gray-50 rounded-lg">
                    <i class="fas fa-eye text-purple-500 mr-3 mt-1"></i>
                    <div>
                        <span class="font-medium">Monitoreo en Tiempo Real</span>
                        <p class="text-sm text-gray-500">Estado de ocupación</p>
                    </div>
                </div>
                <div class="flex items-start p-3 bg-gray-50 rounded-lg">
                    <i class="fas fa-qrcode text-orange-500 mr-3 mt-1"></i>
                    <div>
                        <span class="font-medium">Códigos QR</span>
                        <p class="text-sm text-gray-500">Acceso rápido a espacios</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Links -->
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
            <h4 class="font-semibold text-gray-800 mb-4">
                <i class="fas fa-external-link-alt mr-2"></i>Acceder a la configuración
            </h4>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('mapas.index') }}" target="_blank" 
                   class="inline-flex items-center px-4 py-2 bg-teal-600 text-white font-medium rounded-lg hover:bg-teal-700 transition">
                    <i class="fas fa-map mr-2"></i>
                    Configurar Mapas
                </a>
                <a href="{{ route('spaces_index') }}" target="_blank" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-door-closed mr-2"></i>
                    Gestionar Espacios
                </a>
                <a href="{{ route('floors_index') }}" target="_blank" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 text-white font-medium rounded-lg hover:bg-gray-700 transition">
                    <i class="fas fa-layer-group mr-2"></i>
                    Gestionar Pisos
                </a>
            </div>
        </div>

        <!-- Skip Option -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle text-yellow-500 mr-3"></i>
                <p class="text-sm text-yellow-800">
                    Este paso es <strong>opcional</strong>. Puede configurar el plano digital y los espacios posteriormente desde el menú principal.
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
        <form action="{{ route('tenant.initialization.skip-plan') }}" method="POST" class="inline">
            @csrf
            <button type="submit" 
                    class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                Continuar
                <i class="fas fa-arrow-right ml-2"></i>
            </button>
        </form>
    </div>
</div>
