<!-- Modal para Editar (Selector de Opciones) -->
<div id="modal-editar" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="flex flex-col w-full max-w-md mx-2 overflow-hidden bg-white rounded-lg shadow-lg">
        <!-- Encabezado -->
        <div class="relative flex items-center justify-between p-4 bg-blue-600 text-white">
            <h2 class="text-lg font-bold">¿Qué desea editar?</h2>
            <button onclick="cerrarModalEditar()" class="text-white hover:text-gray-200">
                <x-heroicon-s-x class="w-5 h-5" />
            </button>
        </div>

        <!-- Opciones -->
        <div class="p-6 space-y-4">
            <button 
                onclick="abrirModalEditarReservas()"
                class="w-full flex items-center justify-between p-4 text-left bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition-colors">
                <div class="flex items-center space-x-3">
                    <x-heroicon-s-calendar class="w-5 h-5 text-blue-600" />
                    <div>
                        <p class="font-medium text-gray-900">Editar Reservas</p>
                        <p class="text-sm text-gray-600">Cambiar estado de reservas activas</p>
                    </div>
                </div>
                <x-heroicon-s-chevron-right class="w-4 h-4 text-gray-400" />
            </button>

            <button 
                onclick="abrirModalEditarEspacios()"
                class="w-full flex items-center justify-between p-4 text-left bg-green-50 border border-green-200 rounded-lg hover:bg-green-100 transition-colors">
                <div class="flex items-center space-x-3">
                    <x-heroicon-s-office-building class="w-5 h-5 text-green-600" />
                    <div>
                        <p class="font-medium text-gray-900">Editar Espacios</p>
                        <p class="text-sm text-gray-600">Cambiar estado de espacios ocupados</p>
                    </div>
                </div>
                <x-heroicon-s-chevron-right class="w-4 h-4 text-gray-400" />
            </button>
        </div>
    </div>
</div>
