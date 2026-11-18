        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('edit-university-form');
            
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                Swal.fire({
                    title: '¿Seguro de guardar los cambios?',
                    text: "Estás a punto de actualizar la información de la universidad.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#10B981',
                    cancelButtonColor: '#8D8D8D',
                    confirmButtonText: 'Sí, guardar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });

        function previewImage(event) {
            var file = event.target.files[0];
            var reader = new FileReader();

            reader.onload = function(e) {
                var preview = document.getElementById('logoPreview');
                preview.src = e.target.result;
                preview.style.display = 'block'; 
            }

            if (file) {
                reader.readAsDataURL(file);
            }
        }

if (typeof previewImage === 'function') window.previewImage = previewImage;
