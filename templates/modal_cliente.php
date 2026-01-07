<div id="modalCliente" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="fixed inset-0 bg-black bg-opacity-40 backdrop-blur-sm"></div>
    <div class="relative top-20 mx-auto p-6 border w-full max-w-md shadow-2xl rounded-2xl bg-white">
        <div class="flex justify-between items-center border-b pb-4">
            <h3 class="text-xl font-bold text-gray-800" id="tituloModalCliente">Registrar Cliente</h3>
            <button onclick="cerrarModalCliente()" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
        </div>

        <form action="clientes_procesar.php" method="POST" class="mt-6 space-y-4">
            <input type="hidden" name="id" id="cliente_id">
            
            <div>
                <label class="block text-sm font-semibold text-gray-700">Nombre Completo</label>
                <input type="text" name="nombre" id="cl_nombre" required 
                    class="w-full mt-1 p-2.5 border rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Teléfono</label>
                    <input type="tel" name="telefono" id="cl_telefono" required 
                        class="w-full mt-1 p-2.5 border rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Email (Opcional)</label>
                    <input type="email" name="email" id="cl_email" 
                        class="w-full mt-1 p-2.5 border rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="submit" class="flex-1 bg-blue-600 text-white py-3 rounded-xl font-bold hover:bg-blue-700 transition shadow-lg">
                    Guardar Cliente
                </button>
                <button type="button" onclick="cerrarModalCliente()" class="flex-1 bg-gray-100 text-gray-600 py-3 rounded-xl font-bold">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModalCliente() {
    document.getElementById('cliente_id').value = '';
    document.getElementById('cl_nombre').value = '';
    document.getElementById('cl_telefono').value = '';
    document.getElementById('cl_email').value = '';
    document.getElementById('tituloModalCliente').innerText = 'Registrar Cliente';
    document.getElementById('modalCliente').classList.remove('hidden');
}

function editarCliente(datos) {
    document.getElementById('cliente_id').value = datos.id;
    document.getElementById('cl_nombre').value = datos.nombre;
    document.getElementById('cl_telefono').value = datos.telefono;
    document.getElementById('cl_email').value = datos.email;
    document.getElementById('tituloModalCliente').innerText = 'Editar Cliente';
    document.getElementById('modalCliente').classList.remove('hidden');
}

function cerrarModalCliente() {
    document.getElementById('modalCliente').classList.add('hidden');
}
</script>