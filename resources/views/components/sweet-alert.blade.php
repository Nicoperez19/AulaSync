@props(['type' => 'success', 'title' => '', 'message' => ''])

<!-- SweetAlert2 Local (sin dependencia externa CDN para intranets aisladas) -->
<script src="{{ asset('js/sweetalert2.all.min.js') }}"></script>

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

    /* Estilos globales y unificados de SweetAlert2 con Paleta Oficial SIA */
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
window.SweetAlertConfig = {
    loginUrl: "{{ route('login') }}",
    success: @json(session('success')),
    error: @json(session('error')),
    warning: @json(session('warning')),
    info: @json(session('info')),
    errorsTitle: @json(request()->routeIs('login') ? '¡Error de Inicio de Sesión!' : '¡Error de Validación!'),
    errors: @json(($errors->any() && !request()->routeIs('data.index')) ? collect($errors->all())->map(fn ($e) => $e === 'auth.failed' ? 'El usuario o la contraseña ingresada son incorrectos.' : $e)->values() : [])
};
</script>
