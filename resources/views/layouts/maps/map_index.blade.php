<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight" style="font-style: oblique;">
                {{ __('Gestión de Mapas') }}
            </h2>
        </div>
    </x-slot>

    {{-- Mostrar botón de agregar solo si es admin --}}
    @if (auth()->user()->hasRole('Administrador'))
        <div class="flex justify-end mb-4">
            <x-button x-on:click.prevent="window.location.href='{{ route('mapas.add') }}'" variant="primary"
                class="max-w-xs gap-2">
                <x-icons.add class="w-6 h-6" aria-hidden="true" />
            </x-button>
        </div>
    @endif

    @livewire('mapas-table')
</x-app-layout>

@push('scripts')
    <script>
        document.addEventListener('livewire:initialized', function() {
            Livewire.on('mostrar-mapa', (data) => {
                const modal = document.getElementById('modal-mapa');
                const imagenOriginal = document.getElementById('imagen-original');
                const imagenBloques = document.getElementById('imagen-bloques');

                // Cargar la imagen original
                imagenOriginal.src = data.ruta;

                // Crear un canvas para dibujar los bloques
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                const img = new Image();

                img.onload = function() {
                    canvas.width = img.width;
                    canvas.height = img.height;

                    // Dibujar la imagen base
                    ctx.drawImage(img, 0, 0);

                    // Dibujar los bloques
                    data.bloques.forEach(bloque => {
                        ctx.fillStyle = 'rgba(59, 130, 246, 0.5)'; // Azul semi-transparente
                        ctx.fillRect(bloque.posicion_x - 20, bloque.posicion_y - 20, 40, 40);
                        ctx.strokeStyle = '#FFFFFF';
                        ctx.lineWidth = 2;
                        ctx.strokeRect(bloque.posicion_x - 20, bloque.posicion_y - 20, 40, 40);

                        // Dibujar el ID del espacio
                        ctx.fillStyle = '#FFFFFF';
                        ctx.font = 'bold 16px Arial';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        ctx.fillText(bloque.id_espacio, bloque.posicion_x, bloque.posicion_y);
                    });

                    // Convertir el canvas a imagen
                    imagenBloques.src = canvas.toDataURL();
                };

                img.src = data.ruta;

                // Mostrar el modal
                modal.classList.remove('hidden');
            });
        });
    </script>
@endpush

<!-- Modal para mostrar el mapa -->
<div id="modal-mapa" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-7xl shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">Visualización del Mapa</h3>
            <button onclick="document.getElementById('modal-mapa').classList.add('hidden')"
                class="text-gray-400 hover:text-gray-500">
                <span class="sr-only">Cerrar</span>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h4 class="text-sm font-medium text-gray-700 mb-2">Mapa Original</h4>
                <img id="imagen-original" class="w-full h-auto rounded-lg border shadow-md" alt="Mapa Original">
            </div>
            <div>
                <h4 class="text-sm font-medium text-gray-700 mb-2">Mapa con Bloques</h4>
                <img id="imagen-bloques" class="w-full h-auto rounded-lg border shadow-md" alt="Mapa con Bloques">
            </div>
        </div>
    </div>
</div>
