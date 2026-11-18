window.confirmDeleteRecord = function (id, formPrefix = 'delete-form-', message = 'Esta acción no se puede deshacer') {
    Swal.fire({
        title: '¿Estás seguro?',
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#D12420',
        cancelButtonColor: '#8D8D8D',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById(formPrefix + id)?.submit();
        }
    });
};

window.confirmDeleteNamed = function (id, entityLabel, name, formPrefix = 'delete-form-') {
    const message = name
        ? `Esta acción eliminará ${entityLabel} "${name}" y no se puede deshacer`
        : 'Esta acción no se puede deshacer';
    window.confirmDeleteRecord(id, formPrefix, message);
};

window.confirmDeleteByFormId = function (formId) {
    window.confirmDeleteRecord(formId, '');
};

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('form[data-confirm-save]').forEach((form) => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            Swal.fire({
                title: '¿Guardar cambios?',
                text: 'Estás a punto de guardar los cambios realizados.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, guardar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#10B981',
                cancelButtonColor: '#8D8D8D',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
});
