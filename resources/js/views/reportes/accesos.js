        document.addEventListener('DOMContentLoaded', function () {
            const periodosLimites = (window.ReportesAccesosConfig?.periodosLimites ?? {});
            const periodoSelect = document.getElementById('periodo_filtro');
            const fechaInicio = document.getElementById('fecha_inicio');
            const fechaFin = document.getElementById('fecha_fin');

            if (periodoSelect && fechaInicio && fechaFin) {
                periodoSelect.addEventListener('change', function () {
                    const val = this.value;
                    if (val && periodosLimites[val]) {
                        fechaInicio.value = periodosLimites[val].inicio;
                        fechaFin.value = periodosLimites[val].fin;
                    }
                });
            }
        });
