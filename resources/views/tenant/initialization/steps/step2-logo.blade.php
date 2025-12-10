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
            <!-- Current Logo Preview -->
            <div class="flex justify-center">
                <div id="logo-preview" class="w-40 h-40 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center bg-gray-50">
                    @if($sede && $sede->logo)
                        <img src="{{ $sede->getLogoUrl() }}" alt="Logo actual" class="max-w-full max-h-full object-contain">
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
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('logo-preview').innerHTML = 
                    '<img src="' + e.target.result + '" alt="Vista previa" class="max-w-full max-h-full object-contain">';
            };
            reader.readAsDataURL(file);
        }
    });
</script>
