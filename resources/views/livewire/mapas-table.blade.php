<div class="overflow-x-auto border border-gray-200 rounded-lg shadow-md dark:border-gray-700">
    <table class="w-full text-center border-collapse table-auto min-w-max">
        <thead>
<<<<<<< HEAD
<<<<<<< HEAD
            <tr class="bg-gray-100 text-left">
                <th class="px-4 py-2">Nombre</th>
=======
            <tr class="bg-gray-100 text-center">
                <th class="px-4 py-2">Nombre Mapa</th>
>>>>>>> Nperez
=======
            <tr class="bg-gray-100 text-left">
                <th class="px-4 py-2">Nombre</th>
>>>>>>> 6c05e560f5edb88b89bd0fe7d8d71ecb8386c841
                <th class="px-4 py-2">Ver</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($mapas as $mapa)
                <tr class="border-t">
                    <td class="px-4 py-2">{{ $mapa->nombre_mapa }}</td>
                    <td class="px-4 py-2">
<<<<<<< HEAD
<<<<<<< HEAD
                        <x-button variant="ghost" class="text-blue-500"
                            x-on:click.prevent="$dispatch('open-modal', 'ver-mapa-{{ $mapa->id_mapa }}')">
=======
                        <x-button variant="ghost" class="text-blue-500" wire:click="verMapa('{{ $mapa->id_mapa }}')">
>>>>>>> Nperez
=======
                        <x-button variant="ghost" class="text-blue-500"
                            x-on:click.prevent="$dispatch('open-modal', 'ver-mapa-{{ $mapa->id_mapa }}')">
>>>>>>> 6c05e560f5edb88b89bd0fe7d8d71ecb8386c841
                            Ver
                        </x-button>
                    </td>
                </tr>

                {{-- Modal por cada mapa --}}
<<<<<<< HEAD
<<<<<<< HEAD
                <x-modal  name="ver-mapa-{{ $mapa->id_mapa }}" :show="false" focusable>
=======
                <x-modal name="ver-mapa-{{ $mapa->id_mapa }}" :show="false" focusable>
>>>>>>> Nperez
=======
                <x-modal  name="ver-mapa-{{ $mapa->id_mapa }}" :show="false" focusable>
>>>>>>> 6c05e560f5edb88b89bd0fe7d8d71ecb8386c841
                    <div class="p-6 w-full max-w-7xl mx-auto"> {{-- max-w-7xl aumenta el ancho --}}
                        <h2 class="text-2xl font-semibold text-center mb-4">
                            {{ $mapa->nombre_mapa }}
                        </h2>
<<<<<<< HEAD
<<<<<<< HEAD
                
=======

>>>>>>> Nperez
=======
                
>>>>>>> 6c05e560f5edb88b89bd0fe7d8d71ecb8386c841
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="font-medium text-center">Imagen del Mapa</h3>
                                <img src="{{ asset($mapa->ruta_mapa) }}" alt="Mapa Original"
                                    class="w-full h-auto max-h-[500px] object-contain rounded-lg border shadow-md mt-2">
                            </div>
                            <div>
                                <h3 class="font-medium text-center">Imagen del Canvas</h3>
                                <img src="{{ asset($mapa->ruta_canvas) }}" alt="Canvas"
                                    class="w-full h-auto max-h-[500px] object-contain rounded-lg border shadow-md mt-2">
                            </div>
                        </div>
                    </div>
                </x-modal>
            @endforeach
        </tbody>
    </table>
<<<<<<< HEAD
<<<<<<< HEAD
</div>
=======
</div>
>>>>>>> Nperez
=======
</div>
>>>>>>> 6c05e560f5edb88b89bd0fe7d8d71ecb8386c841
