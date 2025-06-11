<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight">
                {{ __('Accesos registrados (QR)') }}
            </h2>
        </div>
    </x-slot>

    <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
        <div class="flex gap-2 mb-4">
            <a href="{{ route('reporteria.accesos.export', ['format' => 'excel']) }}" class="btn btn-success">Exportar Excel</a>
            <a href="{{ route('reporteria.accesos.export', ['format' => 'pdf']) }}" class="btn btn-danger">Exportar PDF</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded-lg dark:bg-gray-800">
                <thead>
                    <tr>
                        <th class="px-4 py-2 border">Sala</th>
                        <th class="px-4 py-2 border">Fecha</th>
                        <th class="px-4 py-2 border">Usuario</th>
                        <th class="px-4 py-2 border">Tipo de usuario</th>
                        <th class="px-4 py-2 border">Incidencias</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Aquí se mostrarán los datos de accesos --}}
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout> 