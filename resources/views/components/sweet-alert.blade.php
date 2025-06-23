@props(['type' => 'success', 'title' => '', 'message' => ''])

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
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
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
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
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, actualizar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(formId).submit();
            }
        });
    }
</script> 