{{-- Step 0: Password Gate --}}
<div class="p-8">
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
            <i class="fas fa-lock text-2xl text-gray-600"></i>
        </div>
        <h2 class="text-2xl font-bold text-gray-800">Acceso al Asistente de Configuración</h2>
        <p class="text-gray-600 mt-2">Ingrese la contraseña de inicialización para continuar</p>
    </div>

    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                <span class="text-red-700">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <form action="{{ route('tenant.initialization.verify-password') }}" method="POST" class="max-w-md mx-auto">
        @csrf
        
        <div class="space-y-6">
            <!-- Password Field -->
            <div>
                <label for="init_password" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-key mr-1"></i> Contraseña de Inicialización
                </label>
                <div class="relative">
                    <input type="password" 
                           id="init_password" 
                           name="init_password" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('init_password') border-red-500 @enderror"
                           placeholder="Ingrese la contraseña"
                           required 
                           autofocus>
                    <button type="button" 
                            onclick="togglePasswordVisibility()" 
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                        <i class="fas fa-eye" id="toggleIcon"></i>
                    </button>
                </div>
                @error('init_password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Info Box -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-500 mt-1 mr-3"></i>
                    <div>
                        <h4 class="font-semibold text-blue-800">¿Dónde obtener esta contraseña?</h4>
                        <p class="text-sm text-blue-700 mt-1">
                            La contraseña de inicialización fue proporcionada por el administrador del sistema.
                            Si no la tiene, contacte al equipo de soporte técnico.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8">
            <button type="submit" 
                    class="w-full inline-flex items-center justify-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-unlock-alt mr-2"></i>
                Verificar y Continuar
            </button>
        </div>
    </form>
</div>

<script>
function togglePasswordVisibility() {
    const passwordInput = document.getElementById('init_password');
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}
</script>
