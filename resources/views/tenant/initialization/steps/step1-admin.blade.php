{{-- Step 1: Create Admin Account --}}
<div class="p-8">
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-100 mb-4">
            <i class="fas fa-user-plus text-2xl text-blue-600"></i>
        </div>
        <h2 class="text-2xl font-bold text-gray-800">Crear Cuenta de Administrador</h2>
        <p class="text-gray-600 mt-2">Configure la cuenta del administrador para esta sede</p>
    </div>

    <form action="{{ route('tenant.initialization.store-admin') }}" method="POST">
        @csrf
        
        <div class="space-y-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-user mr-2"></i>Nombre Completo
                </label>
                <input type="text" name="name" id="name" 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                       value="{{ old('name') }}"
                       placeholder="Ingrese el nombre completo"
                       required>
            </div>

            <div>
                <label for="run" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-id-card mr-2"></i>RUN (Sin puntos ni guión)
                </label>
                <input type="text" name="run" id="run" 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                       value="{{ old('run') }}"
                       placeholder="Ej: 12345678"
                       maxlength="8"
                       required>
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-envelope mr-2"></i>Correo Electrónico
                </label>
                <input type="email" name="email" id="email" 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                       value="{{ old('email') }}"
                       placeholder="admin@ejemplo.com"
                       required>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-lock mr-2"></i>Contraseña
                </label>
                <input type="password" name="password" id="password" 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                       placeholder="Mínimo 8 caracteres"
                       required>
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-lock mr-2"></i>Confirmar Contraseña
                </label>
                <input type="password" name="password_confirmation" id="password_confirmation" 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                       placeholder="Repita la contraseña"
                       required>
            </div>
        </div>

        <div class="mt-8 flex justify-end">
            <button type="submit" 
                    class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                Siguiente
                <i class="fas fa-arrow-right ml-2"></i>
            </button>
        </div>
    </form>
</div>
