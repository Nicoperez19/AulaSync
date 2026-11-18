        function mostrarSweetAlertNoMapas() {
            Swal.fire({
                title: 'No hay mapas disponibles',
                html: `
                    <div class="text-center">
                        <p class="mb-4">No se han encontrado mapas digitales en el sistema.</p>
                        <p class="text-sm text-gray-600">Por favor póngase en contacto con el administrador del sistema.</p>
                    </div>
                `,
                icon: 'warning',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#D12420'
            });
        }

        // Mostrar SweetAlert automáticamente si no hay mapas
        if (!window.PlanoDigitalIndexConfig?.tieneMapas) {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                mostrarSweetAlertNoMapas();
            }, 500);
        });
        }

if (typeof mostrarSweetAlertNoMapas === 'function') window.mostrarSweetAlertNoMapas = mostrarSweetAlertNoMapas;
