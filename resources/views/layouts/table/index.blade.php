<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 pr-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-light-cloud-blue">
                    <i class="text-2xl text-white fa-solid fa-table"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold leading-tight">Módulos Actuales</h2>
                    <p class="text-sm text-gray-500">Visualiza las asignaturas activas en el módulo actual</p>
                </div>
            </div>

        </div>
    </x-slot>

    <livewire:modulos-actuales-table />

    <div id="modal-reloj"
        class="fixed bottom-6 right-8 z-50 bg-light-cloud-blue shadow-lg rounded-xl border border-gray-200 px-5 py-3 flex flex-col items-center gap-1 min-w-[162px] text-white">
        <span class="px-3 py-1 font-mono text-lg font-bold text-white" id="hora-actual"></span>
        <span class="px-3 py-1 font-mono text-sm text-white" id="fecha-actual"></span>
    </div>
    <script>
        function actualizarFechaHora() {
            const ahora = new Date();
            const hora = ahora.toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });
            const fecha = ahora.toLocaleDateString('es-CL', {  year: 'numeric', month: 'long', day: 'numeric' });
            document.getElementById('hora-actual').textContent = hora;
            document.getElementById('fecha-actual').textContent = fecha.charAt(0).toUpperCase() + fecha.slice(1);
        }
        setInterval(actualizarFechaHora, 1000);
        actualizarFechaHora();
    </script>
</x-app-layout>