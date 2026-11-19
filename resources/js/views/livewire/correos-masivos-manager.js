document.addEventListener('DOMContentLoaded', function() {

    // Confirmación para eliminar tipo de correo
    Livewire.on('confirm-delete-tipo', (data) => {
        const tipo = data[0];

        Swal.fire({
            title: '¿Eliminar tipo de correo?',
            html: `
                <div class="text-left">
                    <p class="mb-2"><strong>Nombre:</strong> ${tipo.nombre}</p>
                    <br>
                    <p class="text-red-600"><strong>Esta acción no se puede deshacer</strong></p>
                    <p class="text-sm text-gray-600 mt-2">Se eliminarán todas las asignaciones de destinatarios asociadas.</p>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-trash"></i> Sí, eliminar',
            cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
            confirmButtonColor: '#D12420',
            cancelButtonColor: '#8D8D8D',
        }).then((result) => {
            if (result.isConfirmed) {
                window.Livewire.find(window.CorreosMasivosConfig?.componentId).call('confirmDeleteTipo', tipo.id);
            }
        });
    });

    // Confirmación para eliminar destinatario
    Livewire.on('confirm-delete-destinatario', (data) => {
        const destinatario = data[0];

        Swal.fire({
            title: '¿Eliminar destinatario?',
            html: `
                <div class="text-left">
                    <p class="mb-2"><strong>Nombre:</strong> ${destinatario.nombre}</p>
                    ${destinatario.rol ? `<p class="mb-2"><strong>Rol:</strong> ${destinatario.rol}</p>` : ''}
                    <br>
                    <p class="text-red-600"><strong>Esta acción no se puede deshacer</strong></p>
                    <p class="text-sm text-gray-600 mt-2">Se eliminarán todas las asignaciones de tipos de correos asociadas.</p>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-trash"></i> Sí, eliminar',
            cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
            confirmButtonColor: '#D12420',
            cancelButtonColor: '#8D8D8D',
        }).then((result) => {
            if (result.isConfirmed) {
                window.Livewire.find(window.CorreosMasivosConfig?.componentId).call('confirmDeleteDestinatario', destinatario.id);
            }
        });
    });

    // Confirmación para eliminar plantilla
    Livewire.on('confirm-delete-plantilla', (data) => {
        const plantilla = data[0];

        Swal.fire({
            title: '¿Eliminar plantilla?',
            html: `
                <div class="text-left">
                    <p class="mb-2"><strong>Nombre:</strong> ${plantilla.nombre}</p>
                    <br>
                    <p class="text-red-600"><strong>Esta acción no se puede deshacer</strong></p>
                    <p class="text-sm text-gray-600 mt-2">Esta plantilla ya no estará disponible para enviar correos.</p>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-trash"></i> Sí, eliminar',
            cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
            confirmButtonColor: '#D12420',
            cancelButtonColor: '#8D8D8D',
        }).then((result) => {
            if (result.isConfirmed) {
                window.Livewire.find(window.CorreosMasivosConfig?.componentId).call('confirmDeletePlantilla', plantilla.id);
            }
        });
    });

    // Mensajes de éxito
    Livewire.on('show-success', (data) => {
        Swal.fire({
            title: '¡Éxito!',
            text: data[0].message,
            icon: 'success',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#10B981',
            timer: 3000,
            timerProgressBar: true
        });
    });

    // Mensajes de error
    Livewire.on('show-error', (data) => {
        Swal.fire({
            title: 'Error',
            text: data[0].message,
            icon: 'error',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#D12420'
        });
    });
});

    function abrirVistaPreviaPlantilla(plantillaId, nombrePlantilla) {
        console.log('Abriendo vista previa de plantilla:', plantillaId, nombrePlantilla);
        
        const modal = document.getElementById('modalVistaPreviaPlantilla');
        const titulo = document.getElementById('modalVistaPreviaTitulo');
        const contenido = document.getElementById('contenidoVistaPreviaPlantilla');
        const btnDescargar = document.getElementById('btnDescargarPdfPlantilla');
        
        if (!modal) {
            console.error('Modal no encontrado');
            return;
        }
        
        // Actualizar título
        titulo.textContent = nombrePlantilla;
        
        // Actualizar enlace de descarga
        btnDescargar.href = `/test/plantillas-pdf/generar/${plantillaId}`;
        
        // Mostrar modal con loading
        modal.classList.remove('hidden');
        contenido.innerHTML = `
            <div class="flex items-center justify-center min-h-[500px]">
                <div class="text-center">
                    <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-indigo-200 border-t-indigo-600 mb-3"></div>
                    <p class="text-gray-700 font-medium text-lg">Cargando vista previa...</p>
                    <p class="text-gray-500 text-sm mt-2">Por favor espera un momento</p>
                </div>
            </div>
        `;
        
        // Bloquear scroll del body
        document.body.style.overflow = 'hidden';
        
        // Cargar contenido via fetch
        fetch(`/test/plantillas-pdf/preview/${plantillaId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error al cargar la vista previa');
                }
                return response.text();
            })
            .then(html => {
                contenido.innerHTML = `
                    <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200">
                        <iframe srcdoc="${html.replace(/"/g, '&quot;')}" 
                                class="w-full border-0 rounded" 
                                style="height: 70vh; min-height: 500px;">
                        </iframe>
                    </div>
                `;
            })
            .catch(error => {
                console.error('Error:', error);
                contenido.innerHTML = `
                    <div class="flex items-center justify-center min-h-[500px]">
                        <div class="text-center max-w-md mx-auto">
                            <div class="bg-red-100 text-red-600 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-exclamation-circle text-3xl"></i>
                            </div>
                            <p class="text-red-600 text-lg font-semibold mb-2">Error al cargar la vista previa</p>
                            <p class="text-gray-600 text-sm mb-4">${error.message}</p>
                            <button onclick="cerrarModalVistaPrevia()" 
                                    class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg hover:from-indigo-700 hover:to-purple-700 transition-all">
                                Cerrar
                            </button>
                        </div>
                    </div>
                `;
            });
    }
    
    function cerrarModalVistaPrevia(event) {
        // Si se hizo click en el backdrop o en el botón cerrar
        if (!event || event.target.id === 'modalVistaPreviaPlantilla' || event.type === 'click') {
            const modal = document.getElementById('modalVistaPreviaPlantilla');
            if (modal) {
                modal.classList.add('hidden');
                // Restaurar scroll del body
                document.body.style.overflow = 'auto';
            }
        }
    }
    
    // Cerrar modal con tecla ESC
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            cerrarModalVistaPrevia();
        }
    });

Object.assign(window, { cerrarModalVistaPrevia });
