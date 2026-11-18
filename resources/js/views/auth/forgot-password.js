        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('forgotPasswordForm');
            
            // Función para refrescar el token CSRF
            function refreshCsrfToken() {
                return fetch('/csrf-token', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.token) {
                        document.querySelector('input[name="_token"]').value = data.token;
                    }
                })
                .catch(error => {
                    console.error('Error refreshing CSRF token:', error);
                });
            }
            
            // Verificar si hay mensaje de sesión (éxito)
            if (window.ForgotPasswordConfig?.hasStatus) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Mensaje enviado! ',
                    text: 'Hemos enviado el enlace de recuperación a tu correo. ¡Revisa tu bandeja de entrada (y la carpeta de spam, por si acaso)! 🚀',
                    confirmButtonText: '¡Perfecto!',
                    confirmButtonColor: '#10B981'
                }).then((result) => {
                    window.location.href = (window.ForgotPasswordConfig?.loginUrl ?? '/login');
                });
            }
            
            form.addEventListener('submit', function(e) {
                // Prevenir el envío normal del formulario
                e.preventDefault();
                
                // Mostrar loading
                Swal.fire({
                    title: 'Procesando...',
                    text: 'Enviando enlace de recuperación',
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
                .then(response => {
                    if (!response.ok) {
                        if (response.status === 419) {
                            // Token CSRF expirado - intentar refrescar automáticamente
                            Swal.fire({
                                icon: 'warning',
                                title: 'Sesión Expirada',
                                text: 'Refrescando tu sesión automáticamente...',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            
                            // Intentar refrescar el token CSRF
                            refreshCsrfToken().then(() => {
                                Swal.close();
                                // Reintentar el envío del formulario
                                form.dispatchEvent(new Event('submit'));
                            }).catch(() => {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Sesión Expirada',
                                    text: 'Tu sesión ha expirado. Por favor, inicia sesión nuevamente.',
                                    confirmButtonText: 'Iniciar Sesión',
                                    confirmButtonColor: '#D12420'
                                }).then(() => {
                                    window.location.href = (window.ForgotPasswordConfig?.loginUrl ?? '/login');
                                });
                            });
                            return;
                        }
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data && data.success) {
                        // Éxito
                        Swal.fire({
                            icon: 'success',
                            title: '¡Mensaje enviado!',
                            text: 'Hemos enviado el enlace de recuperación a tu correo. ¡Revisa tu bandeja de entrada (y la carpeta de spam, por si acaso)! 🚀',
                            confirmButtonText: '¡Perfecto!',
                            confirmButtonColor: '#10B981'
                        }).then((result) => {
                            window.location.href = (window.ForgotPasswordConfig?.loginUrl ?? '/login');
                        });
                    } else if (data) {
                        // Error
                        Swal.fire({
                            icon: 'error',
                            title: '¡Ups! Algo salió mal',
                            text: data.message || 'Hubo un problema al enviar el correo. Inténtalo de nuevo.',
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
