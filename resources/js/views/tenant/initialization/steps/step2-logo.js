    document.getElementById('logo').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            if (!file.type.startsWith('image/')) {
                alert('Por favor seleccione un archivo de imagen v\u00e1lido.');
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewContainer = document.getElementById('logo-preview');
                while (previewContainer.firstChild) {
                    previewContainer.removeChild(previewContainer.firstChild);
                }
                const img = document.createElement('img');
                img.src = e.target.result;
                img.alt = 'Vista previa';
                img.className = 'max-w-full h-16 object-contain';
                previewContainer.appendChild(img);
            };
            reader.readAsDataURL(file);
        }
    });
