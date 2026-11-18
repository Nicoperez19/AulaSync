        document.addEventListener('DOMContentLoaded', function () {
            const canvas = document.getElementById('chart-reporte-escuelas');
            if (!canvas || !window.Chart) return;

            let escuelas = [];
            try {
                escuelas = JSON.parse(canvas.dataset.escuelas || '[]');
            } catch (e) {
                escuelas = [];
            }

            if (!Array.isArray(escuelas) || escuelas.length === 0) return;

            const labels = escuelas.map(e => e.nombre || 'Sin Escuela');
            const dataImpartidas = escuelas.map(e => e.impartidas || 0);
            const dataNoRegistradas = escuelas.map(e => e.no_registradas || 0);
            const dataPcts = escuelas.map(e => e.pct || 0);

            new Chart(canvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Clases Impartidas (Efectivas)',
                            data: dataImpartidas,
                            backgroundColor: '#10B981',
                            hoverBackgroundColor: '#059669',
                            borderRadius: 6,
                            borderSkipped: false,
                        },
                        {
                            label: 'Clases No Registradas',
                            data: dataNoRegistradas,
                            backgroundColor: '#D12420',
                            hoverBackgroundColor: '#950101',
                            borderRadius: 6,
                            borderSkipped: false,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                font: { weight: 'bold', size: 12 },
                                useBorderRadius: true,
                                borderRadius: 4,
                                boxWidth: 14,
                                boxHeight: 14,
                            }
                        },
                        tooltip: {
                            backgroundColor: '#1e293b',
                            titleFont: { weight: 'bold', size: 13 },
                            bodyFont: { size: 12 },
                            padding: 12,
                            cornerRadius: 8,
                            callbacks: {
                                afterBody: function(context) {
                                    const idx = context[0].dataIndex;
                                    return `Cumplimiento: ${dataPcts[idx]}%`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: {
                                font: { size: 11, weight: '600' },
                                color: '#334155',
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f1f5f9' },
                            ticks: {
                                precision: 0,
                                font: { size: 11 },
                                color: '#64748b',
                            }
                        }
                    }
                }
            });
        });
