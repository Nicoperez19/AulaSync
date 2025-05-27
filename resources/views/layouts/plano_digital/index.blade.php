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
            @foreach($sedes as $sede)
                <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                            {{ $sede->nombre_sede }}
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">
                            {{ $sede->universidad->nombre_universidad }}
                        </p>
                        
                        @php
                            $primerPiso = $sede->facultades->flatMap->pisos->flatMap->mapas->first();
                        @endphp
                        
                        @if($primerPiso)
                            <a href="{{ route('plano.show', $primerPiso->id_mapa) }}" 
                               class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                Ver Plano
                            </a>
                        @else
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                No hay planos disponibles
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout> 