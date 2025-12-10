{{-- Step 7: Complete --}}
<div class="p-8">
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-green-100 mb-4">
            <i class="fas fa-check text-4xl text-green-600"></i>
        </div>
        <h2 class="text-2xl font-bold text-gray-800">¡Configuración Completada!</h2>
        <p class="text-gray-600 mt-2">Su sede está lista para comenzar a operar</p>
    </div>

    <div class="space-y-6">
        <!-- Summary -->
        <div class="bg-green-50 border border-green-200 rounded-lg p-6">
            <h4 class="font-semibold text-green-800 mb-4">
                <i class="fas fa-clipboard-check mr-2"></i>Resumen de la configuración
            </h4>
            <div class="space-y-3">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 mr-3"></i>
                    <span>Cuenta de administrador creada</span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 mr-3"></i>
                    <span>Logo de sede configurado</span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 mr-3"></i>
                    <span>Información de sede confirmada</span>
                </div>
            </div>
        </div>

        <!-- Sede Preview -->
        @if($sede)
            <div class="bg-white border border-gray-200 rounded-lg p-6 text-center">
                @if($sede->logo)
                    <img src="{{ $sede->getLogoUrl() }}" alt="{{ $sede->nombre_sede }}" class="h-20 mx-auto mb-4">
                @endif
                <h3 class="text-xl font-bold text-gray-800">{{ $sede->nombre_sede }}</h3>
                @if($sede->descripcion)
                    <p class="text-gray-600 mt-2">{{ $sede->descripcion }}</p>
                @endif
            </div>
        @endif

        <!-- Next Steps -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h4 class="font-semibold text-blue-800 mb-4">
                <i class="fas fa-forward mr-2"></i>Próximos pasos recomendados
            </h4>
            <ul class="space-y-2 text-blue-700">
                <li class="flex items-start">
                    <i class="fas fa-angle-right text-blue-500 mr-2 mt-1"></i>
                    <span>Realizar una carga masiva de profesores y horarios</span>
                </li>
                <li class="flex items-start">
                    <i class="fas fa-angle-right text-blue-500 mr-2 mt-1"></i>
                    <span>Configurar los espacios y el plano digital</span>
                </li>
                <li class="flex items-start">
                    <i class="fas fa-angle-right text-blue-500 mr-2 mt-1"></i>
                    <span>Crear usuarios adicionales si es necesario</span>
                </li>
                <li class="flex items-start">
                    <i class="fas fa-angle-right text-blue-500 mr-2 mt-1"></i>
                    <span>Explorar el Dashboard y las funcionalidades</span>
                </li>
            </ul>
        </div>
    </div>

    <div class="mt-8 flex justify-center">
        <form action="{{ route('tenant.initialization.complete') }}" method="POST">
            @csrf
            <button type="submit" 
                    class="inline-flex items-center px-8 py-4 bg-green-600 text-white font-bold text-lg rounded-lg hover:bg-green-700 transition shadow-lg">
                <i class="fas fa-rocket mr-3"></i>
                Finalizar y comenzar a usar AulaSync
            </button>
        </form>
    </div>
</div>
