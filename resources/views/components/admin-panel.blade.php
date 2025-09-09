<!-- Panel de Administración -->
<div class="flex flex-col items-center gap-3 p-4  bg-red-700 rounded-lg shadow-sm  text-white mx-2">
    <h3 class="text-sm font-semibold text-center">Acciones rápidas</h3>
    <div class="flex-row w-full grid grid-cols-3 gap-3">

        <!-- Botón Agregar Reserva -->
        <button
            onclick="abrirModalAgregarReserva()"
            class="group relative w-full flex items-center justify-center px-3 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-all duration-200"
            title="Agregar nueva reserva">
            <div class="flex items-center space-x-2">
                <x-heroicon-s-plus class="w-4 h-4 text-white" />

            </div>
            <!-- Tooltip -->
            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                Agregar nueva reserva
                <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900"></div>
            </div>
        </button>

        <!-- Botón Editar -->
        <button
            onclick="abrirModalEditar()"
            class="group relative w-full flex items-center justify-center px-3 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-all duration-200"
            title="Editar reservas o espacios">
            <div class="flex items-center space-x-2">
                <x-heroicon-s-pencil class="w-4 h-4" />
                
            </div>
            <!-- Tooltip -->
            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                Editar reservas o espacios
                <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900"></div>
            </div>
        </button>

        <!-- Botón Vaciar Reservas -->
        <button
            onclick="confirmarVaciarReservas()"
            class="group relative w-full flex items-center justify-center px-3 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-all duration-200"
            title="Finalizar todas las reservas activas">
            <div class="flex items-center space-x-2">
                <x-heroicon-s-trash class="w-4 h-4" />

            </div>
            <!-- Tooltip -->
            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                Finalizar todas las reservas activas
                <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900"></div>
            </div>
        </button>

    </div>
</div>