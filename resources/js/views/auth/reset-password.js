import '../../shared/page-config';
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('resetPasswordForm');
            
            form.addEventListener('submit', function(e) {
                // Prevenir el envío normal del formulario
                e.preventDefault();
                
                // Mostrar loading
                Swal.fire({
                    title: 'Procesando...',
                    text: 'Restableciendo tu contraseña',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Enviar el formulario
                fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Éxito
                        Swal.fire({
                            icon: 'success',
                            title: '¡Contraseña Restablecida! 🎉',
                            text: 'Tu contraseña ha sido actualizada exitosamente. Serás redirigido al login.',
                            confirmButtonText: '¡Perfecto!',
                            confirmButtonColor: '#10B981'
                        }).then((result) => {
                            window.location.href = (window.ResetPasswordConfig?.loginUrl ?? '/login');
                        });
                    } else {
                        // Error
                        Swal.fire({
                            icon: 'error',
                            title: '¡Ups! Algo salió mal 😅',
                            text: data.message || 'Hubo un problema al restablecer tu contraseña. Inténtalo de nuevo.',
                            confirmButtonText: 'Entendido',
                            confirmButtonColor: '#D12420'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de Conexión',
                        text: 'No se pudo conectar con el servidor. Verifica tu conexión e inténtalo de nuevo.',
                        confirmButtonText: 'Entendido',
                        confirmButtonColor: '#D12420'
                    });
                });
            });
        });
