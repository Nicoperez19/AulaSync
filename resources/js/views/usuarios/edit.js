        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('edit-user-form');

            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    // Validación básica
                    const run = form.querySelector('input[name="run"]').value;
                    const celular = form.querySelector('input[name="celular"]').value;
                    
                    if (!/^\d{7,8}$/.test(run)) {
                        Swal.fire({
                            title: 'Error',
                            text: 'El RUN debe ser un número de 7 u 8 dígitos',
                            icon: 'error'
                        });
                        return;
                    }
                    
                    if (celular && !/^9\d{8}$/.test(celular)) {
                        Swal.fire({
                            title: 'Error',
                            text: 'El celular debe comenzar con 9 y tener 9 dígitos',
                            icon: 'error'
                        });
                        return;
                    }

                    Swal.fire({
                        title: '¿Guardar cambios?',
                        text: 'Estás a punto de guardar los cambios realizados.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, guardar',
                        cancelButtonText: 'Cancelar',
                        confirmButtonColor: '#10B981',
                        cancelButtonColor: '#8D8D8D',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            }
        });
