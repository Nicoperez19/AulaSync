<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight">
                {{ __('Plano Digital') }}
            </h2>
        </div>
    </x-slot>

    <!-- Incluir SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
        @if (!$tieneMapas)
            <div class="text-center py-12">
                <div class="mb-4">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 4m0 13V4m-6 3l6-3" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                    No hay mapas disponibles
                </h3>
                <p class="text-gray-500 dark:text-gray-400 mb-6">
                    Hay que contactarse con el administrador para generar los mapas.
                </p>
                <button onclick="mostrarSweetAlertNoMapas()" 
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Más Información
                </button>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($sedes as $sede)
                    @php
                        // Buscar el primer mapa asociado a esta sede
                        $primerMapa = $sede->facultades->flatMap->pisos->flatMap->mapas->first();
                    @endphp

                    @if ($primerMapa)
                        <div
                            class="bg-white dark:bg-gray-700 rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                            <div class="p-6">
                                @foreach ($sede->facultades as $facultad)
                                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                                        {{ "$facultad->nombre_facultad, $sede->nombre_sede" }}
                                    </h3>
                                @endforeach

                                <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">
                                    {{ $sede->universidad->nombre_universidad }}
                                </p>

                                <a href="{{ route('plano.show', $primerMapa->id_mapa) }}"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                    Ver Plano
                                </a>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif
    </div>

    <script>
        // Mostrar mensajes de sesión con SweetAlert
        @if(session('success'))
            Swal.fire({
                title: '¡Éxito!',
                text: '{{ session('success') }}',
                icon: 'success',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#3B82F6'
            });
        @endif

        @if(session('error'))
            Swal.fire({
                title: 'Error',
                text: '{{ session('error') }}',
                icon: 'error',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#EF4444'
            });
        @endif

        @if(session('warning'))
            Swal.fire({
                title: 'Advertencia',
                text: '{{ session('warning') }}',
                icon: 'warning',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#F59E0B'
            });
        @endif

        @if(session('info'))
            Swal.fire({
                title: 'Información',
                text: '{{ session('info') }}',
                icon: 'info',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#3B82F6'
            });
        @endif

        function mostrarSweetAlertNoMapas() {
            Swal.fire({
                title: 'No hay mapas disponibles',
                html: `
                    <div class="text-center">
                        <p class="mb-4">No se han encontrado mapas digitales en el sistema.</p>
                        <p class="text-sm text-gray-600">Hay que contactarse con el administrador para generar los mapas.</p>
                    </div>
                `,
                icon: 'warning',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#3B82F6'
            });
        }

        // Mostrar SweetAlert automáticamente si no hay mapas
        @if (!$tieneMapas)
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(() => {
                    mostrarSweetAlertNoMapas();
                }, 500);
            });
        @endif
    </script>
</x-app-layout>
