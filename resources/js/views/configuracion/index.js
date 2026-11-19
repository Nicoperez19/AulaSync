import '../../shared/page-config';
        function handleLogoSelect(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                if (!file.type.startsWith('image/')) {
                    alert('Por favor seleccione un archivo de imagen válido.');
                    input.value = '';
                    return;
                }
                if (file.size > 2 * 1024 * 1024) {
                    alert('El archivo no debe superar los 2MB.');
                    input.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('logo-preview-img').src = e.target.result;
                    document.getElementById('eliminar_logo_input').value = '0';
                    const statusText = document.getElementById('logo-status-text');
                    if (statusText) {
                        statusText.innerText = 'Nuevo logo cargado: ' + file.name + ' (Guardar para aplicar cambios)';
                        statusText.className = 'text-xs font-medium text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-200 inline-flex items-center gap-1.5';
                        statusText.classList.remove('hidden');
                    }
                };
                reader.readAsDataURL(file);
            }
        }

        function eliminarLogoClick() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¿Eliminar logo institucional?',
                    text: 'Se restablecerá el logo por defecto una vez guardes los cambios.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#D12420',
                    cancelButtonColor: '#8D8D8D',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        aplicarEliminacionLogo();
                    }
                });
            } else {
                if (confirm('¿Desea eliminar el logo institucional?')) {
                    aplicarEliminacionLogo();
                }
            }
        }

        function aplicarEliminacionLogo() {
            document.getElementById('eliminar_logo_input').value = '1';
            document.getElementById('logo_file_input').value = '';
            document.getElementById('logo-preview-img').src = (window.ConfiguracionIndexConfig?.defaultLogoUrl ?? '/images/logo_instituto_tecnologico-01.png');
            const statusText = document.getElementById('logo-status-text');
            if (statusText) {
                statusText.innerText = 'Logo marcado para eliminación (Guardar para aplicar cambios)';
                statusText.className = 'text-xs font-medium text-amber-800 bg-amber-50 px-2.5 py-1 rounded-md border border-amber-200 inline-flex items-center gap-1.5';
                statusText.classList.remove('hidden');
            }
        }

        function toggleRecesoInputs(semestre, habilitado) {
            const inicio = document.getElementById('receso' + semestre + '_inicio');
            const fin = document.getElementById('receso' + semestre + '_fin');
            if (inicio && fin) {
                if (!habilitado) {
                    inicio.setAttribute('disabled', 'disabled');
                    fin.setAttribute('disabled', 'disabled');
                } else {
                    inicio.removeAttribute('disabled');
                    fin.removeAttribute('disabled');
                }
            }
        }

        function togglePeriodosDefinidosUI(definidos) {
            const container = document.getElementById('periodos-cards-container');
            if (container) {
                if (!definidos) {
                    container.classList.add('opacity-75');
                } else {
                    container.classList.remove('opacity-75');
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const receso1 = document.getElementById('receso1_habilitado');
            if (receso1) toggleRecesoInputs(1, receso1.checked);
            const receso2 = document.getElementById('receso2_habilitado');
            if (receso2) toggleRecesoInputs(2, receso2.checked);
            const def = document.getElementById('periodos_definidos');
            if (def) togglePeriodosDefinidosUI(def.checked);
        });

Object.assign(window, { handleLogoSelect, eliminarLogoClick, togglePeriodosDefinidosUI, toggleRecesoInputs });
