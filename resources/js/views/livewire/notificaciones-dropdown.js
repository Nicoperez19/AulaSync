    // Actualizar notificaciones cada 2 minutos
    setInterval(() => {
        window.Livewire.find(window.NotificacionesDropdownConfig?.componentId)?.call('cargarNotificaciones');
    }, 120000);
