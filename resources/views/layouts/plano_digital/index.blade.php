<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight">
                {{ __('Plano Digital') }}
            </h2>
        </div>
    </x-slot>

    <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($mapas as $mapa)
                <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md overflow-hidden">
                    <div class="p-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                            {{ $mapa->nombre_mapa }}
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            {{ $mapa->piso->facultad->sede->universidad->nombre_universidad }} - 
                            {{ $mapa->piso->facultad->sede->nombre_sede }} - 
                            {{ $mapa->piso->facultad->nombre_facultad }}
                        </p>
                        <div class="mt-4">
                            <a href="{{ route('plano.show', $mapa->id_mapa) }}" 
                               class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                Ver Plano
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout> 