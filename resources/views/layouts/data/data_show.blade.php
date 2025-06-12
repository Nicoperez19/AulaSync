<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight" style="font-style: oblique;">
                {{ __('Detalles de Carga') }}
            </h2>
        </div>
    </x-slot>

    <div class="p-6 bg-white rounded-lg shadow-lg dark:bg-gray-800">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div class="p-4 bg-gray-50 rounded-lg dark:bg-gray-700">
                <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Información del Archivo</h3>
                <div class="space-y-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Nombre del Archivo</p>
                        <p class="mt-1 text-gray-900 dark:text-white">{{ $dataLoad->nombre_archivo }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Tipo de Archivo</p>
                        <p class="mt-1 text-gray-900 dark:text-white">{{ strtoupper($dataLoad->tipo_carga) }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Estado</p>
                        <p class="mt-1">
                            <span class="px-2 py-1 text-sm font-semibold rounded-full
                                {{ $dataLoad->estado === 'pendiente' ? 'bg-yellow-100 text-yellow-800' : 
                                   ($dataLoad->estado === 'completado' ? 'bg-green-100 text-green-800' : 
                                    'bg-red-100 text-red-800') }}">
                                {{ ucfirst($dataLoad->estado) }}
                            </span>
                        </p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Registros Cargados</p>
                        <p class="mt-1 text-gray-900 dark:text-white">{{ $dataLoad->registros_cargados }}</p>
                    </div>
                </div>
            </div>

            <div class="p-4 bg-gray-50 rounded-lg dark:bg-gray-700">
                <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Información de la Carga</h3>
                <div class="space-y-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Usuario que Cargó</p>
                        <p class="mt-1 text-gray-900 dark:text-white">{{ $dataLoad->user->name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">RUN Usuario</p>
                        <p class="mt-1 text-gray-900 dark:text-white">{{ $dataLoad->user->run ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha de Carga</p>
                        <p class="mt-1 text-gray-900 dark:text-white">{{ $dataLoad->created_at->format('d/m/Y H:i:s') }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Última Actualización</p>
                        <p class="mt-1 text-gray-900 dark:text-white">{{ $dataLoad->updated_at->format('d/m/Y H:i:s') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end mt-6 space-x-4">
            <x-button variant="secondary" href="{{ route('data.index') }}">
                Volver
            </x-button>
            <form action="{{ route('data.destroy', $dataLoad) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <x-button variant="danger" type="submit" onclick="return confirm('¿Está seguro de que desea eliminar este registro?')">
                    Eliminar
                </x-button>
            </form>
        </div>
    </div>
</x-app-layout> 