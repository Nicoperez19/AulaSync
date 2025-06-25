<table class="min-w-full text-center border border-gray-300 rounded-lg dark:bg-dark-eval-1">
    <thead>
        <tr class="bg-gray-200 dark:bg-dark-eval-2">
            <th class="px-4 py-2 border">Usuario</th>
            <th class="px-4 py-2 border">Espacio</th>
            <th class="px-4 py-2 border">Fecha</th>
            <th class="px-4 py-2 border">Módulo</th>
            <th class="px-4 py-2 border">Motivo</th>
        </tr>
    </thead>
    <tbody>
        @forelse($noUtilizadasDia as $item)
        <tr class="bg-white dark:bg-dark-eval-1">
            <td class="border">{{ $item['usuario'] }}</td>
            <td class="border">{{ $item['espacio'] }}</td>
            <td class="border">{{ $item['fecha'] }}</td>
            <td class="border">{{ $item['modulo'] }}</td>
            <td class="border text-red-600 font-bold">No utilizado</td>
        </tr>
        @empty
        <tr>
            <td colspan="5" class="p-4 text-center text-gray-500">No hay registros para este día.</td>
        </tr>
        @endforelse
    </tbody>
</table> 