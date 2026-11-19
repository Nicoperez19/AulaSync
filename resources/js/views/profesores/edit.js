        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('edit-professor-form');
            
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                Swal.fire({
                    title: '¿Seguro de guardar los cambios?',
                    text: "Estás a punto de actualizar la información del profesor.",
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
