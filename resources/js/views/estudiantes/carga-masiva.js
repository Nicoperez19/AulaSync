        function actualizarNombreArchivo(input) {
            if (input.files && input.files[0]) {
                document.getElementById('texto_archivo_nombre').innerHTML = 
                    `<span class="text-emerald-600 font-bold"><i class="fa-solid fa-check-circle mr-1"></i> Archivo seleccionado:</span> ${input.files[0].name} (${(input.files[0].size / 1024 / 1024).toFixed(2)} MB)`;
            }
        }

if (typeof actualizarNombreArchivo === 'function') window.actualizarNombreArchivo = actualizarNombreArchivo;
