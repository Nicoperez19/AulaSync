{{-- Step 3: Confirm Sede Information --}}
<div class="p-8">
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 mb-4">
            <i class="fas fa-building text-2xl text-green-600"></i>
        </div>
        <h2 class="text-2xl font-bold text-gray-800">Información de la Sede</h2>
        <p class="text-gray-600 mt-2">Confirme y complete la información de su sede</p>
    </div>

    <form action="{{ route('tenant.initialization.confirm-sede') }}" method="POST">
        @csrf
        
        <div class="space-y-6">
            <!-- Logo Preview -->
            @if($sede && $sede->logo)
                <div class="flex justify-center mb-4">
                    <img src="{{ $sede->getLogoUrl() }}" alt="{{ $sede->nombre_sede }}" class="h-20 object-contain">
                </div>
            @endif

            <div>
                <label for="nombre_sede" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-signature mr-2"></i>Nombre de la Sede
                </label>
                <input type="text" name="nombre_sede" id="nombre_sede" 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                       value="{{ old('nombre_sede', $sede->nombre_sede ?? '') }}"
                       required>
            </div>

            <div>
                <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-align-left mr-2"></i>Descripción (Opcional)
                </label>
                <textarea name="descripcion" id="descripcion" rows="3"
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                          placeholder="Breve descripción de la sede">{{ old('descripcion', $sede->descripcion ?? '') }}</textarea>
            </div>

            <div>
                <label for="direccion" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-map-marker-alt mr-2"></i>Dirección (Opcional)
                </label>
                <input type="text" name="direccion" id="direccion" 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                       value="{{ old('direccion', $sede->direccion ?? '') }}"
                       placeholder="Dirección física de la sede">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="telefono" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-phone mr-2"></i>Teléfono (Opcional)
                    </label>
                    <input type="text" name="telefono" id="telefono" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                           value="{{ old('telefono', $sede->telefono ?? '') }}"
                           placeholder="+56 X XXXX XXXX">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-envelope mr-2"></i>Email (Opcional)
                    </label>
                    <input type="email" name="email" id="email" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                           value="{{ old('email', $sede->email ?? '') }}"
                           placeholder="contacto@sede.cl">
                </div>
            </div>
        </div>

        <div class="mt-8 flex justify-between">
            <a href="{{ route('tenant.initialization.previous') }}" 
               class="inline-flex items-center px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition">
                <i class="fas fa-arrow-left mr-2"></i>
                Anterior
            </a>
            <button type="submit" 
                    class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                Siguiente
                <i class="fas fa-arrow-right ml-2"></i>
            </button>
        </div>
    </form>
</div>
