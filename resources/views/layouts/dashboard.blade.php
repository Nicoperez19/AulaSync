<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="p-6">
        <div class="max-w-2xl mx-auto">
            <h3 class="mb-4 text-lg font-semibold">Lector de Códigos QR</h3>
            
            <div class="p-4 mb-4 bg-gray-100 rounded-lg">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-600">Contenido del QR:</span>
                    <span id="qr-content" class="text-sm font-semibold text-gray-800">--</span>
                </div>
            </div>

            <input type="text" id="qr-input" class="absolute w-full h-full opacity-0" autofocus>

            <div id="scan-history" class="mt-4 p-4 bg-gray-100 rounded-lg max-h-60 overflow-y-auto">
            </div>
        </div>
    </div>

    <script>
        let bufferQR = '';
        let lastScanTime = 0;
        let esperandoUsuario = true;
        let usuarioEscaneado = null;

        function handleScan(event) {
            const currentTime = new Date().getTime();
            if (currentTime - lastScanTime > 1000) {
                bufferQR = '';
            }
            lastScanTime = currentTime;

            if (event.key.length === 1) {
                bufferQR += event.key;
            }

            if (event.key === 'Enter') {
                if (esperandoUsuario) {
                    // Procesar QR del usuario
                    usuarioEscaneado = bufferQR;
                    document.getElementById('qr-content').textContent = `Usuario: ${bufferQR}`;
                    esperandoUsuario = false;
                    
                    const history = document.getElementById('scan-history');
                    const entry = document.createElement('div');
                    entry.className = 'p-2 mb-2 bg-white rounded shadow';
                    entry.innerHTML = `
                        <div class="font-medium">Usuario: ${bufferQR}</div>
                        <div class="text-xs text-gray-500">${new Date().toLocaleString()}</div>
                    `;
                    history.insertBefore(entry, history.firstChild);
                } else {
                    // Procesar QR del espacio
                    document.getElementById('qr-content').textContent = `Espacio: ${bufferQR}`;
                    esperandoUsuario = true;
                    
                    const history = document.getElementById('scan-history');
                    const entry = document.createElement('div');
                    entry.className = 'p-2 mb-2 bg-white rounded shadow';
                    entry.innerHTML = `
                        <div class="font-medium">Usuario: ${usuarioEscaneado} - Espacio: ${bufferQR}</div>
                        <div class="text-xs text-gray-500">${new Date().toLocaleString()}</div>
                    `;
                    history.insertBefore(entry, history.firstChild);
                }

                bufferQR = '';
                event.target.value = '';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const scannerInput = document.getElementById('qr-input');
            scannerInput.addEventListener('keydown', handleScan);
            scannerInput.focus();
        });
    </script>
</x-app-layout> 