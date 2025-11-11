<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps(['type' => 'success', 'title' => '', 'message' => '']) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps(['type' => 'success', 'title' => '', 'message' => '']); ?>
<?php foreach (array_filter((['type' => 'success', 'title' => '', 'message' => '']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Manejar mensajes de sesión
        <?php if(session('success')): ?>
            Swal.fire({
                title: '¡Éxito!',
                text: <?php echo json_encode(session('success'), 15, 512) ?>,
                icon: 'success',
                confirmButtonText: 'Aceptar'
            });
        <?php endif; ?>

        <?php if(session('error')): ?>
            Swal.fire({
                title: '¡Error!',
                text: <?php echo json_encode(session('error'), 15, 512) ?>,
                icon: 'error',
                confirmButtonText: 'Aceptar'
            });
        <?php endif; ?>

        // Manejar mensajes de validación solo si no estamos en la página de carga de datos
        <?php if($errors->any() && !request()->routeIs('data.index')): ?>
            Swal.fire({
                title: '¡Error de Validación!',
                html: `
                    <ul class="text-left">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                `,
                icon: 'error',
                confirmButtonText: 'Aceptar'
            });
        <?php endif; ?>
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
</script> <?php /**PATH D:\Dev\AulaSync\resources\views/components/sweet-alert.blade.php ENDPATH**/ ?>