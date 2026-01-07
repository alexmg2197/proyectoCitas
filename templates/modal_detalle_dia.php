<div id="modalDia" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="fixed inset-0 bg-black bg-opacity-50"></div>
    <div class="relative top-10 mx-auto p-6 border w-full max-w-2xl shadow-2xl rounded-xl bg-white">
        <div class="flex justify-between items-center border-b pb-4">
            <div class="flex items-center gap-4">
                <button onclick="navegarDia(-1)" class="text-gray-400 hover:text-blue-600"><i class="fas fa-chevron-left"></i></button>
                <h3 class="text-xl font-bold text-gray-800" id="tituloFecha">--/--/----</h3>
                <button onclick="navegarDia(1)" class="text-gray-400 hover:text-blue-600"><i class="fas fa-chevron-right"></i></button>
            </div>
            <button onclick="cerrarModalDia()" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
        </div>

        <div id="contenidoCitas" class="mt-6 max-h-96 overflow-y-auto space-y-3">
            <p class="text-center text-gray-500">Cargando citas...</p>
        </div>

        <div class="mt-8 pt-4 border-t flex justify-end">
            <button onclick="abrirModalNuevaCitaDesdeDia()" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-blue-700">
                + Agendar en este día
            </button>
        </div>
    </div>
</div>