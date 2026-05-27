<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('soporte.index') }}"
               class="flex items-center justify-center w-8 h-8 bg-white rounded-lg shadow-sm border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Nuevo Ticket de Soporte</h2>
                <p class="text-sm text-gray-500 mt-0.5">Describe tu problema y un técnico lo atenderá</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-2xl pb-10">
        <div class="bg-white rounded-xl shadow-sm p-6 sm:p-8">

            {{-- Errores --}}
            @if($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-sm font-semibold text-red-700 mb-1"><i class="fas fa-exclamation-circle mr-1"></i> Hay errores en el formulario:</p>
                    <ul class="text-sm text-red-600 list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('soporte.store') }}" class="space-y-5">
                @csrf

                {{-- Título --}}
                <div>
                    <label for="title" class="block text-sm font-semibold text-gray-700 mb-1">
                        Título del problema <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        value="{{ old('title') }}"
                        placeholder="Ej: No puedo crear una reserva para la Sala A"
                        maxlength="200"
                        class="w-full px-4 py-2.5 text-sm border {{ $errors->has('title') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        required
                    >
                    <p class="text-xs text-gray-400 mt-1">Máximo 200 caracteres. Sé descriptivo y concreto.</p>
                </div>

                {{-- Prioridad --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Prioridad <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-3 gap-3">
                        @foreach([
                            ['value' => 'low',    'label' => 'Baja',   'desc' => 'Sin urgencia',          'color' => 'gray',   'icon' => 'fa-arrow-down'],
                            ['value' => 'medium', 'label' => 'Media',  'desc' => 'Requiere atención',      'color' => 'yellow', 'icon' => 'fa-minus'],
                            ['value' => 'high',   'label' => 'Alta',   'desc' => 'Urgente / bloquea trabajo','color' => 'red', 'icon' => 'fa-arrow-up'],
                        ] as $p)
                        <label class="relative flex flex-col items-center cursor-pointer">
                            <input type="radio" name="priority" value="{{ $p['value'] }}"
                                   class="sr-only peer"
                                   {{ old('priority', 'medium') === $p['value'] ? 'checked' : '' }}>
                            <div class="w-full p-3 text-center rounded-lg border-2 border-gray-200 peer-checked:border-{{ $p['color'] }}-500 peer-checked:bg-{{ $p['color'] }}-50 transition-all hover:border-{{ $p['color'] }}-300">
                                <i class="fas {{ $p['icon'] }} text-{{ $p['color'] }}-500 mb-1 block"></i>
                                <p class="text-sm font-semibold text-gray-700">{{ $p['label'] }}</p>
                                <p class="text-xs text-gray-400">{{ $p['desc'] }}</p>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Descripción --}}
                <div>
                    <label for="description" class="block text-sm font-semibold text-gray-700 mb-1">
                        Descripción detallada <span class="text-red-500">*</span>
                    </label>
                    <textarea
                        id="description"
                        name="description"
                        rows="6"
                        maxlength="5000"
                        placeholder="Describe el problema con el mayor detalle posible: ¿qué intentabas hacer?, ¿qué pasó?, ¿qué mensaje de error apareció?..."
                        class="w-full px-4 py-2.5 text-sm border {{ $errors->has('description') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                        required
                    >{{ old('description') }}</textarea>
                    <p class="text-xs text-gray-400 mt-1">Cuanto más detalle incluyas, más rápido podremos ayudarte.</p>
                </div>

                {{-- Botones --}}
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit"
                            class="px-6 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
                        <i class="fas fa-paper-plane mr-2"></i> Enviar Ticket
                    </button>
                    <a href="{{ route('soporte.index') }}"
                       class="px-4 py-2.5 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>

        {{-- Tips --}}
        <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-xl">
            <p class="text-sm font-semibold text-blue-700 mb-2"><i class="fas fa-lightbulb mr-1"></i> Consejos para un buen ticket:</p>
            <ul class="text-xs text-blue-600 space-y-1 list-disc list-inside">
                <li>Incluye el nombre exacto de la función, página o espacio donde ocurrió el problema.</li>
                <li>Si hay un mensaje de error, cópialo tal como aparece.</li>
                <li>Menciona si el problema ocurre siempre o sólo en ciertas condiciones.</li>
                <li>Proporciona captura de pantalla si es posible (incluye la URL en el texto).</li>
            </ul>
        </div>
    </div>
</x-app-layout>
