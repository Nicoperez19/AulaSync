<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight" style="font-style: oblique;">
                {{ __('Nuevo Baneo') }}
            </h2>
        </div>
    </x-slot>

    <div class="p-6 overflow-hidden bg-white rounded-md shadow-md dark:bg-dark-eval-1 max-w-3xl mx-auto">
        <form action="{{ route('bans.store') }}" method="POST" class="space-y-6">
            @csrf

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
                    Solicitante *
                </label>
                <select id="run_solicitante" name="run_solicitante" required
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                    <option value="">Seleccione un solicitante</option>
                    @foreach ($solicitantes as $solicitante)
                        <option value="{{ $solicitante->run_solicitante }}" {{ old('run_solicitante') == $solicitante->run_solicitante ? 'selected' : '' }}>
                            {{ $solicitante->nombre }} ({{ $solicitante->run_solicitante }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="razon" class="block text-sm font-medium text-gray-700">
                    Razón del Baneo *
                </label>
                <textarea id="razon" name="razon" rows="4" required
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    placeholder="Explique detalladamente la razón del baneo...">{{ old('razon') }}</textarea>
                <p class="mt-1 text-sm text-gray-500">Mínimo 10 caracteres, máximo 500 caracteres</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="fecha_inicio" class="block text-sm font-medium text-gray-700">
                        Fecha y Hora de Inicio *
                    </label>
                    <input type="datetime-local" id="fecha_inicio" name="fecha_inicio" required
                        value="{{ old('fecha_inicio', now()->format('Y-m-d\TH:i')) }}"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>

                <div>
                    <label for="fecha_fin" class="block text-sm font-medium text-gray-700">
                        Fecha y Hora de Fin *
                    </label>
                    <input type="datetime-local" id="fecha_fin" name="fecha_fin" required
                        value="{{ old('fecha_fin', now()->addDays(7)->format('Y-m-d\TH:i')) }}"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
            </div>

            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            El solicitante baneado no podrá realizar reservas durante el período especificado.
                            Se le mostrará la razón y duración del baneo cuando intente reservar.
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('bans.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancelar
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                    Crear Baneo
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
