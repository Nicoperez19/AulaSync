    document.addEventListener('DOMContentLoaded', function () {
        Livewire.on('show-success', (data) => {
            const message = data[0]?.message || 'Operación completada exitosamente.';
            Swal.fire({
                title: '¡Éxito!',
                text: message,
                icon: 'success',
                confirmButtonColor: '#10B981',
                confirmButtonText: 'Aceptar'
            });
        });

        Livewire.on('show-error', (data) => {
            const message = data[0]?.message || 'Ocurrió un error al procesar la solicitud.';
            Swal.fire({
                title: '¡Error!',
                text: message,
                icon: 'error',
                confirmButtonColor: '#D12420',
                confirmButtonText: 'Aceptar'
            });
        });
    });

    function confirmDeleteSingle(id) {
        Swal.fire({
            title: '¿Está seguro?',
            text: '¿Desea eliminar este registro de carga? Al hacerlo, se eliminará permanentemente toda la información de planificación y horarios importados desde este archivo.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#D12420',
            cancelButtonColor: '#8D8D8D'
        }).then((result) => {
            if (result.isConfirmed) {
                window.Livewire.find(window.DataLoadTableConfig?.componentId)?.call('deleteSingle', id);
            }
        });
    }

    function confirmDeleteSelected() {
        Swal.fire({
            title: '¿Está seguro?',
            text: '¿Desea eliminar los registros de carga seleccionados? Al hacerlo, se eliminará permanentemente toda la información de planificación y horarios importados desde estos archivos.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#D12420',
            cancelButtonColor: '#8D8D8D'
        }).then((result) => {
            if (result.isConfirmed) {
                window.Livewire.find(window.DataLoadTableConfig?.componentId)?.call('deleteSelected');
            }
        });
    }

Object.assign(window, { confirmDeleteSelected, confirmDeleteSingle });
