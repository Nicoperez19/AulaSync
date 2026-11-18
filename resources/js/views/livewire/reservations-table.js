    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.btn-delete-reserva').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const espacioId = this.getAttribute('data-espacio');
                const form = this.closest('form');
                if (!form) return;

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: '¿Cancelar reserva?',
                        text: '¿Estás seguro de cancelar y eliminar definitivamente esta reserva? Esta acción liberará el espacio.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#D12420',
                        cancelButtonColor: '#8D8D8D',
                        confirmButtonText: 'Sí, cancelar reserva',
                        cancelButtonText: 'No, mantener'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            localStorage.setItem('reserva_eliminada', JSON.stringify({ id_espacio: espacioId, ts: Date.now() }));
                            localStorage.setItem('reserva_cambiada', Date.now());
                            form.submit();
                        }
                    });
                } else {
                    if (confirm('¿Estás seguro de cancelar esta reserva?')) {
                        localStorage.setItem('reserva_eliminada', JSON.stringify({ id_espacio: espacioId, ts: Date.now() }));
                        localStorage.setItem('reserva_cambiada', Date.now());
                        form.submit();
                    }
                }
            });
        });
    });
