<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight" style="font-style: oblique;">
                {{ __('Horarios por Espacios') }}
            </h2>
        </div>
    </x-slot>
    <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($sedes as $sede)
                @php
                    $primerMapa = $sede->facultades->flatMap->pisos->flatMap->mapas->first();
                @endphp

                @if ($primerMapa)
                    <div
                        class="overflow-hidden transition-shadow duration-300 bg-white rounded-lg shadow-md dark:bg-gray-700 hover:shadow-lg">
                        <div class="p-6">
                            @foreach ($sede->facultades as $facultad)
                                <h3 class="mb-2 text-xl font-semibold text-gray-900 dark:text-white">
                                    {{ "$facultad->nombre_facultad, $sede->nombre_sede" }}
                                </h3>
                            @endforeach

                            <p class="mb-4 text-sm text-gray-600 dark:text-gray-300">
                                {{ $sede->universidad->nombre_universidad }}
                            </p>

                            <a href="{{ route('espacios.show') }}"
                                class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition-colors duration-200 bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Ver Espacios
                            </a>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</x-app-layout>
