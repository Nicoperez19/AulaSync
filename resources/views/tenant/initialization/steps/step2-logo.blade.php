{{-- Step 2: Upload Sede Logo --}}
<div class="p-8">
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-purple-100 mb-4">
            <i class="fas fa-image text-2xl text-purple-600"></i>
        </div>
        <h2 class="text-2xl font-bold text-gray-800">Logo de la Sede</h2>
        <p class="text-gray-600 mt-2">Suba el logo institucional de su sede</p>
    </div>

    <form action="{{ route('tenant.initialization.store-logo') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="space-y-6">
            <!-- Info sobre dimensiones recomendadas -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-500 mt-1 mr-3"></i>
                    <div>
                        <h4 class="font-semibold text-blue-800">Dimensiones recomendadas</h4>
                        <p class="text-sm text-blue-700 mt-1">
                            Altura mínima: <strong>80px</strong>. Formato preferido: PNG con fondo transparente.
                            El logo se mostrará con una altura máxima de 48px en la barra de navegación.
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Current Logo Preview -->
            <div class="flex justify-center">
                <div id="logo-preview" class="w-64 h-24 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center bg-gray-50 p-4">
                    @if($sede && $sede->logo)
                        <img src="{{ $sede->getLogoUrl() }}" alt="Logo actual" class="max-w-full h-16 object-contain">
                    @else
                        <div class="text-center text-gray-400">
                            <i class="fas fa-cloud-upload-alt text-4xl mb-2"></i>
                            <p class="text-sm">Vista previa</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- File Input -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2 text-center">
                    Seleccione una imagen
                </label>
                <div class="flex justify-center">
                    <label for="logo" class="cursor-pointer inline-flex items-center px-6 py-3 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition border border-gray-300">
                        <i class="fas fa-folder-open mr-2"></i>
                        Explorar archivos
                        <input type="file" name="logo" id="logo" accept="image/*" class="hidden" required>
                    </label>
                </div>
                <p class="text-center text-sm text-gray-500 mt-2">
                    Formatos permitidos: JPEG, PNG, JPG, GIF, SVG. Máximo 2MB.
                </p>
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

<script>
    document.getElementById('logo').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validate that file is an image
            if (!file.type.startsWith('image/')) {
                alert('Por favor seleccione un archivo de imagen válido.');
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewContainer = document.getElementById('logo-preview');
                // Clear existing content safely
                while (previewContainer.firstChild) {
                    previewContainer.removeChild(previewContainer.firstChild);
                }
                // Create img element safely
                const img = document.createElement('img');
                img.src = e.target.result;
                img.alt = 'Vista previa';
                img.className = 'max-w-full h-16 object-contain';
                previewContainer.appendChild(img);
            };
            reader.readAsDataURL(file);
        }
    });
</script>
