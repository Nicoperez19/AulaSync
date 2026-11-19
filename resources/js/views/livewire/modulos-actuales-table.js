import '../../shared/page-config';
        // Actualizar datos cada 15 segundos para mantenerlo sincronizado en tiempo real
        setInterval(() => {
            window.Livewire.find(window.ModulosActualesConfig?.componentId)?.call('actualizarAutomaticamente');
        }, 15000); // 15 segundos

        // [NUEVO] Escuchar cambios en localStorage para sincronizar entre pestañas
        window.addEventListener('storage', (e) => {
            if (e.key === 'reserva_cambiada' || e.key === 'espacio_cambiado') {
                console.log('🔄 Cambio detectado en localStorage, actualizando tablero...');
                window.Livewire.find(window.ModulosActualesConfig?.componentId)?.call('actualizarDatos');
            }
        });

        // Refresh completo de página cada hora para limpiar caché
        setTimeout(function() {
            location.reload();
        }, 3600000); // 1 hora = 3600000 milisegundos

        // Escuchar eventos de Livewire para actualizar el feriado cuando se recarguen los datos
        document.addEventListener('livewire:load', function() {
            Livewire.on('datosActualizados', function() {
                window.dispatchEvent(new CustomEvent('actualizar-feriado', {
                    detail: {
                        esFeriado: Boolean(window.ModulosActualesConfig?.esFeriado),
                        nombreFeriado: (window.ModulosActualesConfig?.nombreFeriado ?? '')
                    }
                }));
                
                window.dispatchEvent(new CustomEvent('actualizar-periodo', {
                    detail: {
                        periodoNoIniciado: Boolean(window.ModulosActualesConfig?.periodoNoIniciado),
                        nombrePeriodo: (window.ModulosActualesConfig?.nombrePeriodo ?? '')
                    }
                }));
            });
        });
