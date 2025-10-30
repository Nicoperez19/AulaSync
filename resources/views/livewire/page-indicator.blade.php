<div x-data="{ paginaActual: 1, totalPaginas: 1 }" 
     @actualizar-pagina.window="paginaActual = $event.detail.pagina; totalPaginas = $event.detail.total">
    <div x-show="totalPaginas > 1" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 rounded-lg border">
        <span class="text-sm font-medium text-gray-600">Página</span>
        <span class="px-2 py-1 bg-blue-600 text-white text-sm font-bold rounded" x-text="paginaActual"></span>
        <span class="text-sm text-gray-600">de</span>
        <span class="px-2 py-1 bg-gray-200 text-gray-700 text-sm font-medium rounded" x-text="totalPaginas"></span>
    </div>
</div>
