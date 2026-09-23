@props(['type' => 'success', 'title' => '', 'message' => ''])

<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* Estilos globales y unificados de SweetAlert2 para AulaSync */
    .swal2-popup {
        font-family: 'Roboto', sans-serif !important;
        border-radius: 1rem !important;
        padding: 1.75rem !important;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
    }
    .swal2-title {
        font-size: 1.45rem !important;
        font-weight: 700 !important;
        color: #111827 !important; /* gray-900 */
        margin-top: 0.75rem !important;
        line-height: 1.3 !important;
    }
    .swal2-html-container {
        color: #4b5563 !important; /* gray-600 */
        font-size: 0.95rem !important;
        margin-top: 0.6rem !important;
        line-height: 1.5 !important;
    }
    .swal2-actions {
        margin-top: 1.5rem !important;
        gap: 0.75rem !important;
    }
    .swal2-confirm, .swal2-cancel {
        border-radius: 0.5rem !important;
        font-size: 0.925rem !important;
        font-weight: 600 !important;
        padding: 0.625rem 1.25rem !important;
        margin: 0 !important;
        transition: all 0.2s ease-in-out !important;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
    }
    .swal2-confirm:hover, .swal2-cancel:hover {
        transform: translateY(-1px) !important;
        filter: brightness(1.05) !important;
    }
    .swal2-confirm:active, .swal2-cancel:active {
        transform: translateY(0) !important;
    }
    .swal2-confirm:focus, .swal2-cancel:focus {
        box-shadow: none !important;
    }
    .swal2-icon {
        margin-top: 0.5rem !important;
        border-width: 3px !important;
    }
</style>

<script>
    (function() {
        if (typeof Swal !== 'undefined') {
            const originalFire = Swal.fire;

            // Paleta de colores oficial del sistema AulaSync (UCSC)
            const SYSTEM_COLORS = {
                primary: '#2563eb',    // Azul UCSC / SIA
                success: '#059669',    // Verde Esmeralda UCSC
                danger: '#dc2626',     // Rojo UCSC (eliminar / error)
                warning: '#d97706',    // Ámbar
                secondary: '#6b7280'   // Gris Neutro para Cancelar
            };

            // Interceptor Global para unificar automáticamente cualquier llamada a Swal.fire
            Swal.fire = function(...args) {
                let options = {};

                if (args.length === 1 && typeof args[0] === 'object' && args[0] !== null) {
                    options = { ...args[0] };
                } else if (args.length > 0) {
                    if (typeof args[0] === 'string') options.title = args[0];
                    if (typeof args[1] === 'string') options.text = args[1];
                    if (typeof args[2] === 'string') options.icon = args[2];
                }

                const titleLower = (options.title || '').toString().toLowerCase();
                const textLower = (options.text || '').toString().toLowerCase();
                const isDelete = options.isDelete || 
                                 titleLower.includes('eliminar') || 
                                 titleLower.includes('borrar') || 
                                 textLower.includes('eliminar') || 
                                 textLower.includes('borrar') ||
                                 options.confirmButtonColor === '#d33';

                // Normalizar colores antiguos o arbitrarios
                if (options.cancelButtonColor === '#3085d6' || options.cancelButtonColor === '#d33') {
                    options.cancelButtonColor = SYSTEM_COLORS.secondary;
                }
                if (options.confirmButtonColor === '#d33') {
                    options.confirmButtonColor = SYSTEM_COLORS.danger;
                } else if (options.confirmButtonColor === '#3085d6' || options.confirmButtonColor === '#3B82F6') {
                    options.confirmButtonColor = SYSTEM_COLORS.primary;
                } else if (options.confirmButtonColor === '#b91c1c') {
                    options.confirmButtonColor = isDelete ? SYSTEM_COLORS.danger : SYSTEM_COLORS.primary;
                }

                // Modo confirmación (con botón cancelar)
                if (options.showCancelButton) {
                    if (isDelete) {
                        options.icon = options.icon || 'warning';
                        options.confirmButtonColor = options.confirmButtonColor || SYSTEM_COLORS.danger;
                        options.confirmButtonText = options.confirmButtonText || 'Sí, eliminar';
                    } else {
                        options.icon = options.icon || 'question';
                        options.confirmButtonColor = options.confirmButtonColor || SYSTEM_COLORS.primary;
                        options.confirmButtonText = options.confirmButtonText || 'Confirmar';
                    }
                    options.cancelButtonColor = options.cancelButtonColor || SYSTEM_COLORS.secondary;
                    options.cancelButtonText = options.cancelButtonText || 'Cancelar';
                } else {
                    // Modo alerta informativa (un solo botón)
                    if (options.icon === 'success') {
                        options.confirmButtonColor = options.confirmButtonColor || SYSTEM_COLORS.success;
                        options.confirmButtonText = options.confirmButtonText || 'Aceptar';
                    } else if (options.icon === 'error') {
                        options.confirmButtonColor = options.confirmButtonColor || SYSTEM_COLORS.danger;
                        options.confirmButtonText = options.confirmButtonText || 'Entendido';
                    } else if (options.icon === 'warning') {
                        options.confirmButtonColor = options.confirmButtonColor || SYSTEM_COLORS.primary;
                        options.confirmButtonText = options.confirmButtonText || 'Entendido';
                    } else if (options.icon === 'info') {
                        options.confirmButtonColor = options.confirmButtonColor || SYSTEM_COLORS.primary;
                        options.confirmButtonText = options.confirmButtonText || 'Entendido';
                    } else {
                        options.confirmButtonColor = options.confirmButtonColor || SYSTEM_COLORS.primary;
                        options.confirmButtonText = options.confirmButtonText || 'Aceptar';
                    }
                }

                return originalFire.call(Swal, options);
            };

            // Interceptor para Swal.mixin
            const originalMixin = Swal.mixin;
            Swal.mixin = function(mixinOptions) {
                const mixinInstance = originalMixin.call(Swal, mixinOptions);
                const originalMixinFire = mixinInstance.fire;

                mixinInstance.fire = function(...args) {
                    let merged = { ...mixinOptions };
                    if (args.length === 1 && typeof args[0] === 'object' && args[0] !== null) {
                        merged = { ...merged, ...args[0] };
                    } else if (args.length > 0) {
                        if (typeof args[0] === 'string') merged.title = args[0];
                        if (typeof args[1] === 'string') merged.text = args[1];
                        if (typeof args[2] === 'string') merged.icon = args[2];
                    }
                    return Swal.fire(merged);
                };
                return mixinInstance;
            };

            // Métodos helper estandarizados en Swal
            Swal.confirmDelete = function(config = {}) {
                if (typeof config === 'string') config = { text: config };
                return Swal.fire({
                    title: config.title || '¿Estás seguro?',
                    text: config.text || 'Esta acción no se puede deshacer.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: SYSTEM_COLORS.danger,
                    cancelButtonColor: SYSTEM_COLORS.secondary,
                    confirmButtonText: config.confirmButtonText || 'Sí, eliminar',
                    cancelButtonText: config.cancelButtonText || 'Cancelar',
                    ...config
                });
            };

            Swal.confirmAction = function(config = {}) {
                if (typeof config === 'string') config = { text: config };
                return Swal.fire({
                    title: config.title || '¿Estás seguro?',
                    text: config.text || '',
                    icon: config.icon || 'question',
                    showCancelButton: true,
                    confirmButtonColor: SYSTEM_COLORS.primary,
                    cancelButtonColor: SYSTEM_COLORS.secondary,
                    confirmButtonText: config.confirmButtonText || 'Confirmar',
                    cancelButtonText: config.cancelButtonText || 'Cancelar',
                    ...config
                });
            };

            Swal.success = function(title, text = '', options = {}) {
                return Swal.fire({
                    title: title || '¡Éxito!',
                    text: text,
                    icon: 'success',
                    confirmButtonColor: SYSTEM_COLORS.success,
                    confirmButtonText: 'Aceptar',
                    ...options
                });
            };

            Swal.error = function(title, text = '', options = {}) {
                return Swal.fire({
                    title: title || '¡Error!',
                    text: text,
                    icon: 'error',
                    confirmButtonColor: SYSTEM_COLORS.danger,
                    confirmButtonText: 'Entendido',
                    ...options
                });
            };

            Swal.warning = function(title, text = '', options = {}) {
                return Swal.fire({
                    title: title || 'Atención',
                    text: text,
                    icon: 'warning',
                    confirmButtonColor: SYSTEM_COLORS.primary,
                    confirmButtonText: 'Entendido',
                    ...options
                });
            };

            Swal.info = function(title, text = '', options = {}) {
                return Swal.fire({
                    title: title || 'Información',
                    text: text,
                    icon: 'info',
                    confirmButtonColor: SYSTEM_COLORS.primary,
                    confirmButtonText: 'Aceptar',
                    ...options
                });
            };

            Swal.toastSuccess = function(title) {
                return Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: title,
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            };

            Swal.toastError = function(title) {
                return Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: title,
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            };

            // Helpers para formularios en Window
            window.confirmDelete = function(formId, message = '¿Estás seguro de que deseas eliminar este elemento?') {
                Swal.confirmDelete({ text: message }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.getElementById(formId);
                        if (form) form.submit();
                    }
                });
            };

            window.confirmUpdate = function(formId, message = '¿Estás seguro de que deseas guardar los cambios?') {
                Swal.confirmAction({ text: message, confirmButtonText: 'Sí, guardar' }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.getElementById(formId);
                        if (form) form.submit();
                    }
                });
            };
        }
    })();

    // Modal para error 419 (Sesión Expirada)
    function showExpiredModal() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¡Sesión Expirada!',
                text: 'Tu sesión ha caducado por inactividad. ¿Deseas recargar la página para continuar?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#2563eb',
                cancelButtonColor: '#6b7280',
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
        // Intercepción de error 419 en Livewire
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

        // Manejar mensajes de sesión Flash
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
                confirmButtonText: 'Entendido'
            });
        @endif

        @if (session('warning'))
            Swal.fire({
                title: 'Atención',
                text: @json(session('warning')),
                icon: 'warning',
                confirmButtonText: 'Entendido'
            });
        @endif

        @if (session('info'))
            Swal.fire({
                title: 'Información',
                text: @json(session('info')),
                icon: 'info',
                confirmButtonText: 'Aceptar'
            });
        @endif

        // Validaciones de formularios (excepto data.index)
        @if ($errors->any() && !request()->routeIs('data.index'))
            Swal.fire({
                title: '¡Error de Validación!',
                html: `
                    <ul class="text-left list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                `,
                icon: 'error',
                confirmButtonText: 'Entendido'
            });
        @endif
    });
</script>
