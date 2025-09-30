// ========================================
// CONFIGURACIÓN Y VARIABLES GLOBALES
// ========================================
let autoRefreshInterval;
let autoRefreshEnabled = true;
let moduloActual = null;
let moduloCheckInterval;

// Configuración de horarios de módulos
window.horariosModulos = window.horariosModulos || {
    'lunes': {
        1: { inicio: '08:10:00', fin: '09:00:00' },
        2: { inicio: '09:10:00', fin: '10:00:00' },
        3: { inicio: '10:10:00', fin: '11:00:00' },
        4: { inicio: '11:10:00', fin: '12:00:00' },
        5: { inicio: '12:10:00', fin: '13:00:00' },
        6: { inicio: '13:10:00', fin: '14:00:00' },
        7: { inicio: '14:10:00', fin: '15:00:00' },
        8: { inicio: '15:10:00', fin: '16:00:00' },
        9: { inicio: '16:10:00', fin: '17:00:00' },
        10: { inicio: '17:10:00', fin: '18:00:00' },
        11: { inicio: '18:10:00', fin: '19:00:00' },
        12: { inicio: '19:10:00', fin: '20:00:00' },
        13: { inicio: '20:10:00', fin: '21:00:00' },
        14: { inicio: '21:10:00', fin: '22:00:00' },
        15: { inicio: '22:10:00', fin: '23:00:00' }
    },
    'martes': {
        1: { inicio: '08:10:00', fin: '09:00:00' },
        2: { inicio: '09:10:00', fin: '10:00:00' },
        3: { inicio: '10:10:00', fin: '11:00:00' },
        4: { inicio: '11:10:00', fin: '12:00:00' },
        5: { inicio: '12:10:00', fin: '13:00:00' },
        6: { inicio: '13:10:00', fin: '14:00:00' },
        7: { inicio: '14:10:00', fin: '15:00:00' },
        8: { inicio: '15:10:00', fin: '16:00:00' },
        9: { inicio: '16:10:00', fin: '17:00:00' },
        10: { inicio: '17:10:00', fin: '18:00:00' },
        11: { inicio: '18:10:00', fin: '19:00:00' },
        12: { inicio: '19:10:00', fin: '20:00:00' },
        13: { inicio: '20:10:00', fin: '21:00:00' },
        14: { inicio: '21:10:00', fin: '22:00:00' },
        15: { inicio: '22:10:00', fin: '23:00:00' }
    },
    'miercoles': {
        1: { inicio: '08:10:00', fin: '09:00:00' },
        2: { inicio: '09:10:00', fin: '10:00:00' },
        3: { inicio: '10:10:00', fin: '11:00:00' },
        4: { inicio: '11:10:00', fin: '12:00:00' },
        5: { inicio: '12:10:00', fin: '13:00:00' },
        6: { inicio: '13:10:00', fin: '14:00:00' },
        7: { inicio: '14:10:00', fin: '15:00:00' },
        8: { inicio: '15:10:00', fin: '16:00:00' },
        9: { inicio: '16:10:00', fin: '17:00:00' },
        10: { inicio: '17:10:00', fin: '18:00:00' },
        11: { inicio: '18:10:00', fin: '19:00:00' },
        12: { inicio: '19:10:00', fin: '20:00:00' },
        13: { inicio: '20:10:00', fin: '21:00:00' },
        14: { inicio: '21:10:00', fin: '22:00:00' },
        15: { inicio: '22:10:00', fin: '23:00:00' }
    },
    'jueves': {
        1: { inicio: '08:10:00', fin: '09:00:00' },
        2: { inicio: '09:10:00', fin: '10:00:00' },
        3: { inicio: '10:10:00', fin: '11:00:00' },
        4: { inicio: '11:10:00', fin: '12:00:00' },
        5: { inicio: '12:10:00', fin: '13:00:00' },
        6: { inicio: '13:10:00', fin: '14:00:00' },
        7: { inicio: '14:10:00', fin: '15:00:00' },
        8: { inicio: '15:10:00', fin: '16:00:00' },
        9: { inicio: '16:10:00', fin: '17:00:00' },
        10: { inicio: '17:10:00', fin: '18:00:00' },
        11: { inicio: '18:10:00', fin: '19:00:00' },
        12: { inicio: '19:10:00', fin: '20:00:00' },
        13: { inicio: '20:10:00', fin: '21:00:00' },
        14: { inicio: '21:10:00', fin: '22:00:00' },
        15: { inicio: '22:10:00', fin: '23:00:00' }
    },
    'viernes': {
        1: { inicio: '08:10:00', fin: '09:00:00' },
        2: { inicio: '09:10:00', fin: '10:00:00' },
        3: { inicio: '10:10:00', fin: '11:00:00' },
        4: { inicio: '11:10:00', fin: '12:00:00' },
        5: { inicio: '12:10:00', fin: '13:00:00' },
        6: { inicio: '13:10:00', fin: '14:00:00' },
        7: { inicio: '14:10:00', fin: '15:00:00' },
        8: { inicio: '15:10:00', fin: '16:00:00' },
        9: { inicio: '16:10:00', fin: '17:00:00' },
        10: { inicio: '17:10:00', fin: '18:00:00' },
        11: { inicio: '18:10:00', fin: '19:00:00' },
        12: { inicio: '19:10:00', fin: '20:00:00' },
        13: { inicio: '20:10:00', fin: '21:00:00' },
        14: { inicio: '21:10:00', fin: '22:00:00' },
        15: { inicio: '22:10:00', fin: '23:00:00' }
    }
};

// ========================================
// FUNCIÓN DE NOTIFICACIONES
// ========================================
function mostrarNotificacion(mensaje, tipo = 'info', duracion = 3000) {
    // Crear el elemento de notificación
    const notificacion = document.createElement('div');
    notificacion.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full`;
    
    // Configurar colores según el tipo
    let bgColor, textColor, icon;
    switch (tipo) {
        case 'success':
            bgColor = 'bg-green-500';
            textColor = 'text-white';
            icon = '✓';
            break;
        case 'error':
            bgColor = 'bg-red-500';
            textColor = 'text-white';
            icon = '✗';
            break;
        case 'warning':
            bgColor = 'bg-yellow-500';
            textColor = 'text-white';
            icon = '⚠';
            break;
        default:
            bgColor = 'bg-blue-500';
            textColor = 'text-white';
            icon = 'ℹ';
    }
    
    notificacion.className += ` ${bgColor} ${textColor}`;
    notificacion.innerHTML = `
        <div class="flex items-center gap-3">
            <span class="text-lg font-bold">${icon}</span>
            <span>${mensaje}</span>
        </div>
    `;
    
    // Agregar al DOM
    document.body.appendChild(notificacion);
    
    // Animar entrada
    setTimeout(() => {
        notificacion.classList.remove('translate-x-full');
    }, 100);
    
    // Auto-remover después del tiempo especificado
    setTimeout(() => {
        notificacion.classList.add('translate-x-full');
        setTimeout(() => {
            if (notificacion.parentNode) {
                notificacion.parentNode.removeChild(notificacion);
            }
        }, 300);
    }, duracion);
}

// ========================================
// SISTEMA DE AUTO-REFRESH MEJORADO
// ========================================

function iniciarAutoRefresh() {
    if (!autoRefreshEnabled) return;

    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
    }

    autoRefreshInterval = setInterval(function () {
        actualizarDashboard();
    }, 30000);

    console.log('Auto-refresh iniciado');
}

function detenerAutoRefresh() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
        autoRefreshInterval = null;
    }
}

function actualizarDashboard() {
    mostrarIndicadorActualizacion();

    return fetch('/dashboard/widget-data', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        },
        credentials: 'same-origin'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        actualizarWidgets(data);
        mostrarNotificacion('Dashboard actualizado', 'success', 2000);
    })
    .catch(error => {
        console.error('Error actualizando dashboard:', error);
        mostrarNotificacion('Error al actualizar el dashboard', 'error', 3000);
    });
}

function mostrarIndicadorActualizacion() {
    let indicador = document.getElementById('auto-refresh-indicator');
    if (!indicador) {
        indicador = document.createElement('div');
        indicador.id = 'auto-refresh-indicator';
        indicador.className = 'fixed top-4 left-4 bg-blue-500 text-white px-4 py-2 rounded-lg shadow-lg z-50';
        indicador.textContent = 'Actualizando...';
        document.body.appendChild(indicador);
    }
    if (indicador) indicador.style.display = 'block';
    setTimeout(() => {
        if (indicador) indicador.style.display = 'none';
    }, 2000);
}

// ========================================
// FUNCIONES DE ACTUALIZACIÓN DE WIDGETS
// ========================================

// Función principal de actualización de widgets
function actualizarWidgets(data) {
    const errores = [];

    // Actualizar KPIs con manejo individual de errores
    try {
        actualizarKPIs(data);
    } catch (error) {
        errores.push('Error actualizando KPIs: ' + error.message);
    }

    // Actualizar gráficos con manejo individual
    try {
        if (window.graficoBarras && data.usoPorDia) {
            actualizarGraficoBarras(data.usoPorDia);
        }
    } catch (error) {
        errores.push('Error actualizando gráfico de barras: ' + error.message);
    }

    try {
        if (window.graficoMensual && data.evolucionMensual) {
            actualizarGraficoEvolucionMensual(data.evolucionMensual);
        }
    } catch (error) {
        errores.push('Error actualizando gráfico mensual: ' + error.message);
    }

    try {
        if (window.graficoCircularSalas && data.salasOcupadas) {
            actualizarGraficoCircularSalas(data.salasOcupadas);
        }
    } catch (error) {
        errores.push('Error actualizando gráfico circular: ' + error.message);
    }

    // Ocultar indicadores de carga
    ocultarCargando();

    // Mostrar errores si los hay
    if (errores.length > 0) {
        console.error('Errores durante la actualización:', errores);
    } else {
        console.log('Widgets actualizados exitosamente');
    }
}

// Función para actualizar todos los KPIs
function actualizarKPIs(data) {
    if (!data) return;

    const kpis = [
        { id: 'ocupacion-semanal', valor: data.ocupacionSemanal },
        { id: 'ocupacion-diaria', valor: data.ocupacionDiaria },
        { id: 'ocupacion-mensual', valor: data.ocupacionMensual },
        { id: 'usuarios-sin-escaneo', valor: data.usuariosSinEscaneo },
        { id: 'horas-utilizadas', valor: data.horasUtilizadas }
    ];

    kpis.forEach(kpi => {
        actualizarKPI(kpi.id, kpi.valor);
    });
}

function actualizarKPI(id, valor) {
    const elemento = document.getElementById(id);
    if (elemento) {
        elemento.textContent = valor;
        // Agregar animación temporal
        elemento.classList.add('kpi-value', 'updating');
        setTimeout(() => {
            elemento.classList.remove('updating');
        }, 500);
    }
}

function actualizarGraficoBarras(usoPorDia) {
    if (window.graficoBarras && usoPorDia) {
        window.graficoBarras.data.labels = Object.keys(usoPorDia.datos || {});
        window.graficoBarras.data.datasets[0].data = Object.values(usoPorDia.datos || {});
        window.graficoBarras.update();
    }
}

function actualizarGraficoEvolucionMensual(evolucionMensual) {
    if (window.graficoMensual && evolucionMensual) {
        window.graficoMensual.data.labels = evolucionMensual.dias || [];
        window.graficoMensual.data.datasets[0].data = evolucionMensual.ocupacion || [];
        window.graficoMensual.update();
    }
}

function actualizarGraficoCircularSalas(salasOcupadas) {
    if (window.graficoCircularSalas && salasOcupadas) {
        const ocupadas = salasOcupadas.ocupadas || 0;
        const libres = salasOcupadas.libres || 0;
        
        window.graficoCircularSalas.data.datasets[0].data = [ocupadas, libres];
        window.graficoCircularSalas.update();
        
        // Actualizar el texto de salas ocupadas
        const elementoSalas = document.getElementById('salas-ocupadas');
        if (elementoSalas) {
            elementoSalas.textContent = `${ocupadas} de ${ocupadas + libres} ocupadas`;
        }
    }
}

// ========================================
// FUNCIONES DE CARGA Y UTILIDADES
// ========================================

function mostrarCargando() {
    const widgets = document.querySelectorAll('.bg-white.rounded-xl.shadow-lg');
    widgets.forEach(widget => {
        const loadingDiv = document.createElement('div');
        loadingDiv.className = 'absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center rounded-xl';
        loadingDiv.innerHTML = '<div class="w-8 h-8 border-b-2 border-blue-600 rounded-full animate-spin"></div>';
        widget.style.position = 'relative';
        widget.appendChild(loadingDiv);
    });
}

function ocultarCargando() {
    const widgets = document.querySelectorAll('.bg-white.rounded-xl.shadow-lg');
    widgets.forEach(widget => {
        const loading = widget.querySelector('.absolute.inset-0');
        if (loading) {
            loading.remove();
        }
    });
}

// ========================================
// FUNCIONES DE DETECCIÓN DE MÓDULO
// ========================================

function verificarCambioModulo() {
    const nuevoModulo = obtenerModuloActual();

    if (nuevoModulo !== moduloActual) {
        moduloActual = nuevoModulo;
        actualizarIndicadorModuloInfo(nuevoModulo);
        console.log('Cambio de módulo detectado:', nuevoModulo);
    }
}

function actualizarIndicadorModuloInfo(modulo) {
    const textoModulo = document.getElementById('modulo-actual-text');
    if (!textoModulo) return;

    const diaActual = obtenerDiaActual();
    const horarios = window.horariosModulos && window.horariosModulos[diaActual];

    if (modulo && horarios && horarios[modulo]) {
        const inicio = horarios[modulo].inicio;
        const fin = horarios[modulo].fin;
        textoModulo.textContent = `Módulo ${modulo} (${inicio} - ${fin})`;
    } else {
        textoModulo.textContent = 'Sin módulo activo';
    }
}

function obtenerDiaActual() {
    const dias = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
    return dias[new Date().getDay()];
}

function obtenerModuloActual(hora = null) {
    const diaActual = obtenerDiaActual();
    const horaAhora = hora || new Date().toTimeString().slice(0, 8);

    if (!window.horariosModulos || !window.horariosModulos[diaActual]) {
        return null;
    }

    for (const [num, horario] of Object.entries(window.horariosModulos[diaActual])) {
        if (horaAhora >= horario.inicio && horaAhora <= horario.fin) {
            return parseInt(num);
        }
    }
    return null;
}

function iniciarVerificacionModulo() {
    // Verificar inmediatamente
    verificarCambioModulo();

    // Verificar cada 30 segundos
    moduloCheckInterval = setInterval(verificarCambioModulo, 30000);
}

function detenerVerificacionModulo() {
    if (moduloCheckInterval) {
        clearInterval(moduloCheckInterval);
        moduloCheckInterval = null;
    }
}

// Modal fijo de reloj digital y módulo actual
function actualizarModalReloj() {
    const ahora = new Date();
    // Hora en formato 24h
    const hora = ahora.toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });
    const elementoHora = document.getElementById('modal-hora-actual');
    if (elementoHora) {
        elementoHora.textContent = hora;
    }
    
    // Módulo actual
    let modulo = '-';
    if (typeof obtenerModuloActual === 'function') {
        const moduloActual = obtenerModuloActual();
        if (moduloActual) {
            modulo = moduloActual;
        }
    }
    const elementoModulo = document.getElementById('modal-modulo-actual');
    if (elementoModulo) {
        elementoModulo.textContent = 'Módulo actual: ' + modulo;
    }
}

// Función para actualizar día actual
function actualizarDiaActual() {
    const ahora = new Date();
    const diasSemana = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
    const diaActual = diasSemana[ahora.getDay()];
    
    // Actualizar elementos que muestren el día actual si existen
    const elementos = document.querySelectorAll('[data-dia-actual]');
    elementos.forEach(elemento => {
        elemento.textContent = diaActual;
    });
}

// ========================================
// INICIALIZACIÓN DE GRÁFICOS
// ========================================

// Función para inicializar gráficos con datos desde PHP
function inicializarGraficos(data) {
    // Gráfico de barras: Uso por Día
    if (document.getElementById('grafico-barras')) {
        window.graficoBarras = new Chart(document.getElementById('grafico-barras'), {
            type: 'bar',
            data: {
                labels: ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'],
                datasets: [{
                    label: 'Reservas',
                    data: data.usoPorDia || [],
                    backgroundColor: 'rgba(59,130,246,0.8)',
                    borderColor: 'rgba(59,130,246,1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Cantidad de reservas'
                        }
                    }
                }
            }
        });
    }

    // Gráfico de línea: Evolución mensual
    if (document.getElementById('grafico-mensual')) {
        window.graficoMensual = new Chart(document.getElementById('grafico-mensual'), {
            type: 'line',
            data: {
                labels: data.evolucionMensual?.dias || [],
                datasets: [{
                    label: 'Ocupación %',
                    data: data.evolucionMensual?.ocupacion || [],
                    borderColor: 'rgba(59,130,246,1)',
                    backgroundColor: 'rgba(59,130,246,0.2)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Porcentaje de ocupación'
                        }
                    }
                }
            }
        });
    }

    // Gráfico circular: Salas ocupadas/libres
    if (document.getElementById('grafico-circular-salas')) {
        const ocupadas = data.salasOcupadas?.ocupadas || 0;
        const libres = data.salasOcupadas?.libres || 0;
        
        window.graficoCircularSalas = new Chart(document.getElementById('grafico-circular-salas'), {
            type: 'doughnut',
            data: {
                labels: ['Ocupadas', 'Libres'],
                datasets: [{
                    data: [ocupadas, libres],
                    backgroundColor: [
                        '#a21caf',
                        '#f3f4f6'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            },
            plugins: [{
                beforeDraw: function(chart) {
                    const width = chart.width;
                    const height = chart.height;
                    const ctx = chart.ctx;
                    ctx.restore();
                    const fontSize = (height / 114).toFixed(2);
                    ctx.font = fontSize + "em Arial";
                    ctx.textBaseline = "middle";
                    const text = ocupadas.toString();
                    const textX = Math.round((width - ctx.measureText(text).width) / 2);
                    const textY = height / 2;
                    ctx.fillText(text, textX, textY);
                    ctx.save();
                }
            }]
        });
    }
}

// ========================================
// INICIALIZACIÓN
// ========================================

document.addEventListener('DOMContentLoaded', function () {
    console.log('Dashboard JavaScript iniciado');
    
    // Inicializar gráficos con datos desde el servidor
    if (window.dashboardData) {
        inicializarGraficos(window.dashboardData);
    }
    
    // Iniciar auto-refresh
    iniciarAutoRefresh();

    // Iniciar verificación de módulos
    iniciarVerificacionModulo();

    // Inicializar indicador del módulo actual
    const moduloInicial = obtenerModuloActual();
    actualizarIndicadorModuloInfo(moduloInicial);
    moduloActual = moduloInicial;

    // Inicializar día actual
    actualizarDiaActual();

    // Inicializar reloj
    actualizarModalReloj();
    setInterval(actualizarModalReloj, 1000);

    // Event listener para filtro de fecha
    const input = document.getElementById('filtro_fecha_no_utilizadas');
    if (input) {
        input.addEventListener('change', function () {
            const fecha = input.value;
            fetch(`/dashboard/no-utilizadas-dia?fecha=${fecha}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('tabla-no-utilizadas-dia').innerHTML = html;
                })
                .catch(error => {
                    mostrarNotificacion('Error al cargar los datos de la tabla', 'error');
                });
        });
    }

    // Detener auto-refresh cuando la página no esté visible
    document.addEventListener('visibilitychange', function () {
        if (document.hidden) {
            detenerAutoRefresh();
            detenerVerificacionModulo();
        } else if (autoRefreshEnabled) {
            iniciarAutoRefresh();
            iniciarVerificacionModulo();
        }
    });
});

// Exponer funciones globalmente si es necesario
window.dashboardUtils = {
    actualizarDashboard,
    mostrarNotificacion,
    iniciarAutoRefresh,
    detenerAutoRefresh,
    inicializarGraficos,
    obtenerModuloActual,
    actualizarModalReloj
};