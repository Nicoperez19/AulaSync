<table class="min-w-full bg-white border rounded shadow">
    <thead>
        <tr class="text-left bg-gray-100">
            <th class="px-4 py-2">Nombre</th>
            <th class="px-4 py-2">Espacio</th>
            <th class="px-4 py-2">Ver</th>
            <th class="px-4 py-2">Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($mapas as $mapa)
            <tr class="border-t">
                <td class="px-4 py-2">{{ $mapa->nombre_mapa }}</td>
                <td class="px-4 py-2">{{ $mapa->espacio->nombre_espacio ?? 'Sin espacio' }}</td>
                <td class="px-4 py-2">
                    <x-button wire:click="verMapa('{{ $mapa->ruta_mapa }}')" variant="ghost" class="text-blue-500">
                        Ver
                    </x-button>
                </td>
                <td class="flex gap-2 px-4 py-2">
                    <a href="{{ route('mapas.edit', $mapa->id_mapa) }}" class="px-3 py-1 text-white bg-yellow-400 rounded">Editar</a>
                    <x-button wire:click="eliminar({{ $mapa->id_mapa }})" variant="destructive">
                        Eliminar
                    </x-button>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
