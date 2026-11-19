import '../../shared/page-config';
document.addEventListener('DOMContentLoaded', function() {
    // Cargar estadísticas
    cargarEstadisticasRapidas();
    
    // Actualizar cada 30 segundos
    setInterval(cargarEstadisticasRapidas, 30000);
});

function cargarEstadisticasRapidas() {
    fetch((window.AccionesRapidasIndexConfig?.dashboardDataUrl ?? '/quick-actions/dashboard-data'))
        .then(response => response.json())
        .then(data => {
            document.getElementById('reservas-hoy').textContent = data.reservas_hoy || '0';
            document.getElementById('espacios-libres').textContent = data.espacios_libres || '0';
            document.getElementById('espacios-ocupados').textContent = data.espacios_ocupados || '0';
            document.getElementById('espacios-mantencion').textContent = data.espacios_mantencion || '0';
        })
        .catch(error => {
            console.error('Error al cargar estadísticas:', error);
        });
}
