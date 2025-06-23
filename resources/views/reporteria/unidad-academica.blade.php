<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight">
                {{ __('Reporte por unidad académica') }}
            </h2>
        </div>
    </x-slot>

    <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
        <div class="mb-4 flex gap-2">
            <a href="{{ route('reporteria.unidad-academica.export', ['format' => 'excel']) }}" class="btn btn-success">Exportar Excel</a>
            <a href="{{ route('reporteria.unidad-academica.export', ['format' => 'pdf']) }}" class="btn btn-danger">Exportar PDF</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white dark:bg-gray-800 rounded-lg">
                <thead>
                    <tr>
                        <th class="py-2 px-4 border">Escuela/Carrera</th>
                        <th class="py-2 px-4 border">Uso de espacios</th>
                        <th class="py-2 px-4 border">Reservas rechazadas</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Aquí se mostrarán los datos por unidad académica --}}
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout> 