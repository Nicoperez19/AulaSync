        let _adminReservaId = null;
        let _adminAccion = null; // 'entrada' | 'salida'
        const _csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

        function abrirModalAdmin(accion, reservaId, docente, sala, fecha, horario) {
            _adminReservaId = reservaId;
            _adminAccion = accion;

            const header = document.getElementById('modal-admin-header');
            const titulo = document.getElementById('modal-admin-titulo');
            const icono = document.getElementById('modal-admin-icono');
            const btnConfirmar = document.getElementById('btn-confirmar-admin');
            const campoHoraReal = document.getElementById('campo-hora-real');
            const errorDiv = document.getElementById('modal-admin-error');

            document.getElementById('modal-admin-docente').textContent = docente || '—';
            document.getElementById('modal-admin-sala').textContent = sala || '—';
            document.getElementById('modal-admin-fecha').textContent = fecha || '—';
            document.getElementById('modal-admin-horario').textContent = horario || '—';
            errorDiv.classList.add('hidden');
            errorDiv.textContent = '';

            if (accion === 'entrada') {
                header.className = 'px-6 py-4 flex items-center justify-between bg-emerald-600';
                icono.textContent = '🟢';
                titulo.textContent = 'Registrar Entrada (Admin)';
                btnConfirmar.className = btnConfirmar.className.replace(/bg-\S+/g, '') + ' bg-emerald-600 hover:bg-emerald-700';
                campoHoraReal.classList.remove('hidden');
                const now = new Date();
                document.getElementById('input-hora-real').value =
                    String(now.getHours()).padStart(2,'0') + ':' + String(now.getMinutes()).padStart(2,'0');
            } else {
                header.className = 'px-6 py-4 flex items-center justify-between bg-rose-600';
                icono.textContent = '🔴';
                titulo.textContent = 'Registrar Salida (Admin)';
                btnConfirmar.className = btnConfirmar.className.replace(/bg-\S+/g, '') + ' bg-rose-600 hover:bg-rose-700';
                campoHoraReal.classList.add('hidden');
            }

            const modal = document.getElementById('modal-admin-registro');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function cerrarModalAdmin() {
            const modal = document.getElementById('modal-admin-registro');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            _adminReservaId = null;
            _adminAccion = null;
        }

        async function confirmarRegistroAdmin() {
            if (!_adminReservaId || !_adminAccion) return;

            const btn = document.getElementById('btn-confirmar-admin');
            const spinner = document.getElementById('btn-spinner');
            const texto = document.getElementById('btn-confirmar-texto');
            const errorDiv = document.getElementById('modal-admin-error');

            btn.disabled = true;
            spinner.classList.remove('hidden');
            texto.textContent = 'Procesando...';
            errorDiv.classList.add('hidden');

            try {
                const url = `/reservas/${_adminReservaId}/admin-${_adminAccion}`;
                const body = {};

                if (_adminAccion === 'entrada') {
                    const horaReal = document.getElementById('input-hora-real')?.value;
                    if (horaReal) body.hora_inicio_real = horaReal;
                }

                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': _csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(body),
                });

                const data = await response.json();

                if (data.success) {
                    cerrarModalAdmin();
                    window.location.reload();
                } else {
                    errorDiv.textContent = data.message || 'Ocurrió un error al procesar la solicitud.';
                    errorDiv.classList.remove('hidden');
                }
            } catch (err) {
                errorDiv.textContent = 'Error de conexión. Intente nuevamente.';
                errorDiv.classList.remove('hidden');
            } finally {
                btn.disabled = false;
                spinner.classList.add('hidden');
                texto.textContent = 'Confirmar';
            }
        }

        document.getElementById('modal-admin-registro')?.addEventListener('click', function(e) {
            if (e.target === this) cerrarModalAdmin();
        });

Object.assign(window, { abrirModalAdmin, cerrarModalAdmin, confirmarRegistroAdmin });
