<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-semibold leading-tight" style="font-style: oblique;">
                {{ __('Crear Nuevo Baneo') }}
            </h2>
        </div>
    </x-slot>

    <div class="p-6 overflow-hidden bg-white rounded-md shadow-md dark:bg-dark-eval-1">
        <form method="POST" action="{{ route('bans.store') }}" class="space-y-6">
            @csrf

            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-md">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div>
                <label for="run" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    RUN del Usuario <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="run" 
                       id="run" 
                       value="{{ old('run') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white"
                       required
                       placeholder="Ej: 12345678-9">
                <p class="mt-1 text-sm text-gray-500">Ingrese el RUN del usuario que desea banear</p>
            </div>

            <div>
                <label for="reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Razón del Baneo <span class="text-red-500">*</span>
                </label>
                <textarea name="reason" 
                          id="reason" 
                          rows="4"
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white"
                          required
                          maxlength="500"
                          placeholder="Describa la razón del baneo">{{ old('reason') }}</textarea>
                <p class="mt-1 text-sm text-gray-500">Esta razón será visible para el usuario (máximo 500 caracteres)</p>
            </div>

            <div>
                <label for="banned_until" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Baneado Hasta <span class="text-red-500">*</span>
                </label>
                <input type="datetime-local" 
                       name="banned_until" 
                       id="banned_until" 
                       value="{{ old('banned_until') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white"
                       required>
                <p class="mt-1 text-sm text-gray-500">Seleccione la fecha y hora hasta cuando estará baneado el usuario</p>
            </div>

            <div class="flex items-center space-x-4">
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                    Crear Baneo
                </button>
                <a href="{{ route('bans.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
