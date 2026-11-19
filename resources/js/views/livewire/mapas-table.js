import '../../shared/page-config';
        // Escuchar eventos de Livewire
        document.addEventListener('livewire:init', () => {
            Livewire.on('modalClosed', () => {
                // El modal se cerró, limpiar cualquier estado adicional si es necesario
                console.log('Modal cerrado');
            });
            
            // Cerrar modales con la tecla Escape
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    if ((window.Livewire.find(window.MapasTableConfig?.componentId)?.mostrarModal ?? false)) {
                        window.Livewire.find(window.MapasTableConfig?.componentId)?.call('cerrarModal');
                    }
                    if ((window.Livewire.find(window.MapasTableConfig?.componentId)?.mostrarModalEliminar ?? false)) {
                        window.Livewire.find(window.MapasTableConfig?.componentId)?.call('cerrarEliminarModal');
                    }
                }
            });
        });
