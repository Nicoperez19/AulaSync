    if (typeof window.irAControlDocente !== 'function') {
        window.irAControlDocente = function(estado) {
            const inicio = document.getElementById('status-fecha-inicio')?.value || '';
            const fin = document.getElementById('status-fecha-fin')?.value || '';
            const baseUrl = (window.StatusClasesChartConfig?.clasesNoRealizadasUrl ?? '/clases-no-realizadas');
            const params = new URLSearchParams();
            if (estado) params.set('estado', estado);
            if (inicio) params.set('fecha_inicio', inicio);
            if (fin) params.set('fecha_fin', fin);
            const query = params.toString();
            window.location.href = query ? `${baseUrl}?${query}` : baseUrl;
        };
    }

    if (typeof window.irAControlDocentePorEscuela !== 'function') {
        window.irAControlDocentePorEscuela = function(escuela, estado = '') {
            const inicio = document.getElementById('status-fecha-inicio')?.value || '';
            const fin = document.getElementById('status-fecha-fin')?.value || '';
            const baseUrl = (window.StatusClasesChartConfig?.clasesNoRealizadasUrl ?? '/clases-no-realizadas');
            const params = new URLSearchParams();
            if (escuela) params.set('escuela', escuela);
            if (estado) params.set('estado', estado);
            if (inicio) params.set('fecha_inicio', inicio);
            if (fin) params.set('fecha_fin', fin);
            const query = params.toString();
            window.location.href = query ? `${baseUrl}?${query}` : baseUrl;
        };
    }

    if (typeof window.irAControlDocenteConBusqueda !== 'function') {
        window.irAControlDocenteConBusqueda = function(search, estado = '') {
            const inicio = document.getElementById('status-fecha-inicio')?.value || '';
            const fin = document.getElementById('status-fecha-fin')?.value || '';
            const baseUrl = (window.StatusClasesChartConfig?.clasesNoRealizadasUrl ?? '/clases-no-realizadas');
            const params = new URLSearchParams();
            if (search) params.set('search', search);
            if (estado) params.set('estado', estado);
            if (inicio) params.set('fecha_inicio', inicio);
            if (fin) params.set('fecha_fin', fin);
            const query = params.toString();
            window.location.href = query ? `${baseUrl}?${query}` : baseUrl;
        };
    }
