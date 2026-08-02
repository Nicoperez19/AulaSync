@props(['type' => 'success', 'title' => '', 'message' => ''])

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11">
    // Interceptor Global para errores 419 (Página/Sesión Expirada) en Livewire y AJAX
    function showExpiredModal() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¡Sesión Expirada!',
                text: 'Tu sesión ha caducado por inactividad. ¿Deseas recargar la página para continuar?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#b91c1c',
                cancelButtonColor: '#4b5563',
                confirmButtonText: 'Recargar página',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.reload();
                }
            });
        } else {
            window.location.reload();
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (window.Livewire) {
            if (typeof Livewire.hook === 'function') {
                Livewire.hook('request', ({ fail }) => {
                    fail(({ status, preventDefault }) => {
                        if (status === 419) {
                            preventDefault();
                            showExpiredModal();
                        }
                    });
                });
            }
            if (typeof Livewire.onError === 'function') {
                Livewire.onError(function(statusCode) {
                    if (statusCode === 419) {
                        showExpiredModal();
                        return false;
                    }
                });
            }
        }
    });
</script>

<style>
    /* Estilos premium globales para SweetAlert2 de AulaSync */
    .swal2-popup {
        font-family: 'Roboto', sans-serif !important;
        border-radius: 12px !important;
        padding: 2rem !important;
    }
    .swal2-title {
        font-size: 1.6rem !important;
        font-weight: 700 !important;
        color: #1f2937 !important; /* gray-800 */
        margin-top: 15px !important;
    }
    .swal2-html-container {
        color: #4b5563 !important; /* gray-600 */
        font-size: 1rem !important;
        margin-top: 10px !important;
        line-height: 1.5 !important;
    }
    .swal2-confirm, .swal2-cancel {
        border-radius: 6px !important;
        font-size: 0.95rem !important;
        font-weight: 500 !important;
        padding: 10px 22px !important;
        margin: 0 8px !important;
        transition: all 0.2s ease-in-out !important;
    }
    .swal2-confirm {
        box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.2), 0 2px 4px -1px rgba(59, 130, 246, 0.1) !important;
    }
    .swal2-cancel {
        box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.2), 0 2px 4px -1px rgba(239, 68, 68, 0.1) !important;
    }
    .swal2-confirm:hover {
        transform: translateY(-1px) !important;
        box-shadow: 0 6px 8px -1px rgba(59, 130, 246, 0.3) !important;
    }
    .swal2-cancel:hover {
        transform: translateY(-1px) !important;
        box-shadow: 0 6px 8px -1px rgba(239, 68, 68, 0.3) !important;
    }
    .swal2-actions {
        margin-top: 25px !important;
    }
</style>

<script>
    // Interceptor Global de SweetAlert2 para estandarizar el diseño
    (function() {
        if (typeof Swal !== 'undefined') {
            const originalFire = Swal.fire;
            const standardConfirmColor = '#3085d6'; // Azul institucional del diseño
            const standardCancelColor = '#d33';     // Rojo del diseño

            Swal.fire = function(...args) {
                if (args.length === 1 && typeof args[0] === 'object') {
                    let options = { ...args[0] };
                    
                    // Si muestra botón de cancelar (es una consulta o confirmación)
                    if (options.showCancelButton) {
                        options.confirmButtonColor = standardConfirmColor;
                        options.cancelButtonColor = standardCancelColor;
                        options.reverseButtons = false;
                        
                        // Si no tiene icono y es confirmación, sugerir 'question'
                        if (!options.icon) {
                            options.icon = 'question';
                        }
                    } else {
                        // Para alertas informativas con un solo botón, forzar el color azul
                        if (!options.confirmButtonColor) {
                            options.confirmButtonColor = standardConfirmColor;
                        }
                    }
                    return originalFire.call(Swal, options);
                } else if (args.length > 0) {
                    // Mapear llamadas posicionales Swal.fire(title, text, icon) a objeto
                    let options = {};
                    if (typeof args[0] === 'string') options.title = args[0];
                    if (typeof args[1] === 'string') options.text = args[1];
                    if (typeof args[2] === 'string') {
                        options.icon = args[2];
                        if (options.icon === 'question' || options.icon === 'warning') {
                            options.showCancelButton = true;
                            options.confirmButtonColor = standardConfirmColor;
                            options.cancelButtonColor = standardCancelColor;
                            options.reverseButtons = false;
                        }
                    }
                    
                    if (!options.confirmButtonColor) {
                        options.confirmButtonColor = standardConfirmColor;
                    }
                    
                    return originalFire.call(Swal, options);
                }
                
                return originalFire.apply(Swal, args);
            };

            // Sobreescribir Swal.mixin para aplicar la misma estandarización
            const originalMixin = Swal.mixin;
            Swal.mixin = function(mixinOptions) {
                const mixinInstance = originalMixin.call(Swal, mixinOptions);
                const originalMixinFire = mixinInstance.fire;
                
                mixinInstance.fire = function(...args) {
                    if (args.length === 1 && typeof args[0] === 'object') {
                        let options = { ...mixinOptions, ...args[0] };
                        if (options.showCancelButton) {
                            options.confirmButtonColor = standardConfirmColor;
                            options.cancelButtonColor = standardCancelColor;
                            options.reverseButtons = false;
                        } else if (!options.confirmButtonColor) {
                            options.confirmButtonColor = standardConfirmColor;
                        }
                        return originalFire.call(Swal, options);
                    }
                    return originalMixinFire.apply(mixinInstance, args);
                };
                return mixinInstance;
            };
        }
    })();

    document.addEventListener('DOMContentLoaded', function() {
        // Manejar mensajes de sesión
        @if (session('success'))
            Swal.fire({
                title: '¡Éxito!',
                text: @json(session('success')),
                icon: 'success',
                confirmButtonText: 'Aceptar'
            });
        @endif

        @if (session('error'))
            Swal.fire({
                title: '¡Error!',
                text: @json(session('error')),
                icon: 'error',
                confirmButtonText: 'Aceptar'
            });
        @endif

        // Manejar mensajes de validación solo si no estamos en la página de carga de datos
        @if ($errors->any() && !request()->routeIs('data.index'))
            Swal.fire({
                title: '¡Error de Validación!',
                html: `
                    <ul class="text-left">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                `,
                icon: 'error',
                confirmButtonText: 'Aceptar'
            });
        @endif
    });

    // Función para confirmar eliminación
    function confirmDelete(formId, message = '¿Estás seguro de que deseas eliminar este elemento?') {
        Swal.fire({
            title: '¿Estás seguro?',
            text: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(formId).submit();
            }
        });
    }

    // Función para confirmar actualización
    function confirmUpdate(formId, message = '¿Estás seguro de que deseas actualizar este elemento?') {
        Swal.fire({
            title: '¿Estás seguro?',
            text: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, actualizar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(formId).submit();
            }
        });
    }

    // Interceptor Global para errores 419 (Página/Sesión Expirada) en Livewire y AJAX
    function showExpiredModal() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¡Sesión Expirada!',
                text: 'Tu sesión ha caducado por inactividad. ¿Deseas recargar la página para continuar?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#b91c1c',
                cancelButtonColor: '#4b5563',
                confirmButtonText: 'Recargar página',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.reload();
                }
            });
        } else {
            window.location.reload();
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (window.Livewire) {
            if (typeof Livewire.hook === 'function') {
                Livewire.hook('request', ({ fail }) => {
                    fail(({ status, preventDefault }) => {
                        if (status === 419) {
                            preventDefault();
                            showExpiredModal();
                        }
                    });
                });
            }
            if (typeof Livewire.onError === 'function') {
                Livewire.onError(function(statusCode) {
                    if (statusCode === 419) {
                        showExpiredModal();
                        return false;
                    }
                });
            }
        }
    });
</script>
