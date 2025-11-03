<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight" style="font-style: oblique;">
                {{ __('Editar Baneo') }}
            </h2>
        </div>
    </x-slot>

    <div class="p-6 overflow-hidden bg-white rounded-md shadow-md dark:bg-dark-eval-1 max-w-3xl mx-auto">
        <form action="{{ route('bans.update', $ban->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div>
                <label for="run_solicitante" class="block text-sm font-medium text-gray-700">
                    Solicitante
                </label>
                <input type="text" id="run_solicitante_display" readonly
                    value="{{ $ban->solicitante ? $ban->solicitante->nombre : 'N/A' }} ({{ $ban->run_solicitante }})"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100 sm:text-sm">
                <p class="mt-1 text-sm text-gray-500">El solicitante no puede ser modificado</p>
            </div>

            <div>
                <label for="razon" class="block text-sm font-medium text-gray-700">
                    Razón del Baneo *
                </label>
                <textarea id="razon" name="razon" rows="4" required
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    placeholder="Explique detalladamente la razón del baneo...">{{ old('razon', $ban->razon) }}</textarea>
                <p class="mt-1 text-sm text-gray-500">Mínimo 10 caracteres, máximo 500 caracteres</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="fecha_inicio" class="block text-sm font-medium text-gray-700">
                        Fecha y Hora de Inicio *
                    </label>
                    <input type="datetime-local" id="fecha_inicio" name="fecha_inicio" required
                        value="{{ old('fecha_inicio', $ban->fecha_inicio->format('Y-m-d\TH:i')) }}"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>

                <div>
                    <label for="fecha_fin" class="block text-sm font-medium text-gray-700">
                        Fecha y Hora de Fin *
                    </label>
                    <input type="datetime-local" id="fecha_fin" name="fecha_fin" required
                        value="{{ old('fecha_fin', $ban->fecha_fin->format('Y-m-d\TH:i')) }}"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
            </div>

            <div class="flex items-center">
                <input type="checkbox" id="activo" name="activo" {{ old('activo', $ban->activo) ? 'checked' : '' }}
                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                <label for="activo" class="ml-2 block text-sm text-gray-900">
                    Baneo activo
                </label>
            </div>

            <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            Estado actual: 
                            @if ($ban->estaVigente())
                                <strong class="text-red-600">Vigente</strong> ({{ $ban->diasRestantes() }} días restantes)
                            @elseif ($ban->activo && $ban->fecha_fin > now())
                                <strong class="text-yellow-600">Programado</strong>
                            @else
                                <strong class="text-gray-600">Finalizado</strong>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('bans.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancelar
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                    Actualizar Baneo
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
