@props(['type' => 'success', 'title' => '', 'message' => ''])

<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* Animaciones fluidas de entrada y salida */
    @keyframes swalModalIn {
        0% {
            opacity: 0;
            transform: scale(0.94) translateY(8px);
        }
        100% {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }
    @keyframes swalModalOut {
        0% {
            opacity: 1;
            transform: scale(1);
        }
        100% {
            opacity: 0;
            transform: scale(0.96) translateY(4px);
        }
    }

    .swal2-show {
        animation: swalModalIn 240ms cubic-bezier(0.16, 1, 0.3, 1) !important;
    }
    .swal2-hide {
        animation: swalModalOut 180ms ease-in !important;
    }

    /* Estilos globales y unificados de SweetAlert2 con Paleta Oficial AulaSync */
    .swal2-popup {
        font-family: 'Roboto', sans-serif !important;
        border-radius: 1.25rem !important;
        padding: 1.75rem !important;
        background-color: #FFFFFF !important;
        border: 1px solid #C0C0C0 !important;
        box-shadow: 0 25px 50px -12px rgba(25, 25, 25, 0.18) !important;
    }
    .dark .swal2-popup {
        background-color: #1F1F1F !important;
        border-color: #404040 !important;
    }
    .swal2-title {
        font-size: 1.45rem !important;
        font-weight: 700 !important;
        color: #191919 !important;
        margin-top: 0.75rem !important;
        line-height: 1.3 !important;
    }
    .dark .swal2-title {
        color: #F1EFF2 !important;
    }
    .swal2-html-container {
        color: #525252 !important;
        font-size: 0.95rem !important;
        margin-top: 0.6rem !important;
        line-height: 1.5 !important;
    }
    .dark .swal2-html-container {
        color: #D4D4D4 !important;
    }
    .swal2-actions {
        margin-top: 1.5rem !important;
        gap: 0.75rem !important;
    }
    .swal2-confirm, .swal2-cancel {
        border-radius: 0.625rem !important;
        font-size: 0.925rem !important;
        font-weight: 600 !important;
        padding: 0.65rem 1.4rem !important;
        margin: 0 !important;
        transition: all 0.15s ease-in-out !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1) !important;
    }
    .swal2-confirm:hover, .swal2-cancel:hover {
        transform: translateY(-1px) !important;
        filter: brightness(1.04) !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.15) !important;
    }
    .swal2-confirm:active, .swal2-cancel:active {
        transform: translateY(0) scale(0.98) !important;
    }
    .swal2-confirm:focus, .swal2-cancel:focus {
        box-shadow: 0 0 0 3px rgba(209, 36, 32, 0.25) !important;
    }
    .swal2-icon {
        margin-top: 0.5rem !important;
        border-width: 3px !important;
    }
    .swal2-icon.swal2-success {
        border-color: #10B981 !important;
        color: #10B981 !important;
    }
    .swal2-icon.swal2-success [class^='swal2-success-line'] {
        background-color: #10B981 !important;
    }
    .swal2-icon.swal2-success .swal2-success-ring {
        border-color: rgba(16, 185, 129, 0.3) !important;
    }
    .swal2-icon.swal2-error {
        border-color: #D12420 !important;
        color: #D12420 !important;
    }
    .swal2-icon.swal2-error [class^='swal2-x-mark-line'] {
        background-color: #D12420 !important;
    }
</style>

<script>
    (function() {
        if (typeof Swal !== 'undefined') {
            const originalFire = Swal.fire;

            // Paleta de colores oficial del sistema AulaSync
            const SYSTEM_COLORS = {
                primary: '#D12420',        // Rojo Institucional UCSC / AulaSync
                primaryDark: '#950101',    // Carmesí Oscuro
                success: '#10B981',        // Verde Confirmar / Aceptar / Continuar
                successHover: '#059669',   // Verde Intenso Hover
                danger: '#D12420',         // Rojo Peligro / Eliminar / Error
                dangerHover: '#950101',    // Carmesí Oscuro Hover
                warning: '#F59E0B',        // Ámbar Advertencia
                secondary: '#8D8D8D',      // Gris Neutro para Cancelar
                secondaryHover: '#737373', // Gris Neutro Hover
                border: '#C0C0C0',         // Gris Plata
                canvas: '#F1EFF2',         // Fondo Lienzo
                dark: '#191919'            // Negro Obsidiana
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
                } else if (options.confirmButtonColor === '#3085d6' || options.confirmButtonColor === '#3B82F6' || options.confirmButtonColor === '#2563eb') {
                    options.confirmButtonColor = SYSTEM_COLORS.success; // Confirmaciones usan el verde oficial
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
                        // Acción positiva estándar (Continuar / Confirmar / Aceptar) usa VERDE
                        options.confirmButtonColor = options.confirmButtonColor || SYSTEM_COLORS.success;
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
                        options.confirmButtonColor = options.confirmButtonColor || SYSTEM_COLORS.success;
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
                    confirmButtonColor: SYSTEM_COLORS.success,
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
                text: 'Tu sesión ha caducado por inactividad. Por favor, inicia sesión nuevamente para continuar.',
                icon: 'warning',
                showCancelButton: false,
                confirmButtonColor: '#D12420',
                confirmButtonText: 'Iniciar Sesión',
                allowOutsideClick: false
            }).then(() => {
                window.location.href = '{{ route("login") }}';
            });
        } else {
            window.location.href = '{{ route("login") }}';
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
                confirmButtonColor: '#10B981',
                confirmButtonText: 'Aceptar'
            });
        @endif

        @if (session('error'))
            Swal.fire({
                title: '¡Error!',
                text: @json(session('error')),
                icon: 'error',
                confirmButtonColor: '#D12420',
                confirmButtonText: 'Entendido'
            });
        @endif

        @if (session('warning'))
            Swal.fire({
                title: 'Atención',
                text: @json(session('warning')),
                icon: 'warning',
                confirmButtonColor: '#D12420',
                confirmButtonText: 'Entendido'
            });
        @endif

        @if (session('info'))
            Swal.fire({
                title: 'Información',
                text: @json(session('info')),
                icon: 'info',
                confirmButtonColor: '#D12420',
                confirmButtonText: 'Aceptar'
            });
        @endif

        // Validaciones de formularios (excepto data.index)
        @if ($errors->any() && !request()->routeIs('data.index'))
            Swal.fire({
                title: '{{ request()->routeIs('login') ? '¡Error de Inicio de Sesión!' : '¡Error de Validación!' }}',
                html: `
                    <ul class="text-left list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error === 'auth.failed' ? 'El usuario o la contraseña ingresada son incorrectos.' : $error }}</li>
                        @endforeach
                    </ul>
                `,
                icon: 'error',
                confirmButtonColor: '#D12420',
                confirmButtonText: 'Entendido'
            });
        @endif
    });
</script>
