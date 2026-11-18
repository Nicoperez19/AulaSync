        // Función para mostrar SweetAlert cuando no hay mapas
        function mostrarSweetAlertNoMapas(event) {
            event.preventDefault();
            Swal.fire({
                title: 'No hay mapas disponibles',
                html: `
                    <div class="text-center">
                        <p class="mb-4">No se han encontrado mapas digitales en el sistema.</p>
                        <p class="text-sm text-gray-600">Por favor pongase en contacto con el administrador del sistema.</p>
                    </div>
                `,
                icon: 'warning',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#D12420'
            });
        }

        // Función para mostrar SweetAlert cuando no hay profesores
        function mostrarSweetAlertNoProfesores(event) {
            event.preventDefault();
            Swal.fire({
                title: 'No hay profesores disponibles',
                html: `
                    <div class="text-center">
                        <p class="mb-4">No se han encontrado profesores en el sistema.</p>
                        <p class="text-sm text-gray-600">Por favor, contacte al administrador para cargar los datos de profesores.</p>
                    </div>
                `,
                icon: 'warning',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#D12420'
            });
        }

        // Función para mostrar SweetAlert cuando no hay espacios
        function mostrarSweetAlertNoEspacios(event) {
            event.preventDefault();
            Swal.fire({
                title: 'No hay espacios disponibles',
                html: `
                    <div class="text-center">
                        <p class="mb-4">No se han encontrado espacios en el sistema.</p>
                        <p class="text-sm text-gray-600">Por favor, contacte al administrador para cargar los espacios.</p>
                    </div>
                `,
                icon: 'warning',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#D12420'
            });
        }

        document.addEventListener('alpine:init', () => {
            Alpine.data('mainState', () => ({
                isSidebarOpen: window.innerWidth > 1024,
                isSidebarHovered: false,
                handleSidebarHover(hovered) {
                    if (window.innerWidth > 1024) {
                        this.isSidebarHovered = hovered;
                    }
                },
                handleWindowResize() {
                    if (window.innerWidth > 1024) {
                        this.isSidebarOpen = true;
                    } else {
                        this.isSidebarOpen = false;
                    }
                }
            }));
        });
