<?php 
    require_once 'src/auth.php'; 
    require_once 'src/Database.php';

    $db = Database::getInstance();
    // Obtenemos clientes y contamos cuántas citas tienen (para saber quién es cliente frecuente)
    $query = "SELECT c.*, COUNT(ci.id) as total_citas 
            FROM clientes c 
            LEFT JOIN citas ci ON c.id = ci.cliente_id 
            GROUP BY c.id 
            ORDER BY c.nombre ASC";
    $clientes = $db->query($query)->fetchAll();
?>

<?php include 'templates/sidebar.php'; ?>
<?php include 'templates/header.php'; ?>

<main class="p-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Directorio de Clientes</h2>
            <p class="text-gray-500 text-sm">Gestiona la información y contacto de tus clientes.</p>
        </div>
        <button onclick="abrirModalCliente()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition shadow-md">
            <i class="fas fa-user-plus mr-2"></i> Nuevo Cliente
        </button>
    </div>

    <div class="mb-4 relative">
        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
            <i class="fas fa-search"></i>
        </span>
        <input type="text" id="buscadorClientes" placeholder="Buscar por nombre o teléfono..." 
            class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition-all">
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">

        <table class="w-full text-left">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase">Nombre</th>
                    <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase">Contacto</th>
                    <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase">Citas Realizadas</th>
                    <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach($clientes as $cl): ?>
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="h-10 w-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold mr-3">
                                <?= strtoupper(substr($cl['nombre'], 0, 1)) ?>
                            </div>
                            <span class="font-semibold text-gray-700"><?= htmlspecialchars($cl['nombre']) ?></span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm text-gray-800"><i class="fas fa-phone text-gray-400 mr-2"></i><?= $cl['telefono'] ?></p>
                        <p class="text-xs text-gray-500"><i class="fas fa-envelope text-gray-400 mr-2"></i><?= htmlspecialchars($cl['email']) ?></p>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="bg-gray-100 px-3 py-1 rounded-full text-xs font-bold text-gray-600">
                            <?= $cl['total_citas'] ?> citas
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center space-x-2">
                        <button onclick='editarCliente(<?= json_encode($cl) ?>)' class="text-blue-600 hover:bg-blue-50 p-2 rounded-lg transition">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="eliminarCliente(<?= $cl['id'] ?>)" class="text-red-600 hover:bg-red-50 p-2 rounded-lg transition">
                            <i class="fas fa-trash"></i>
                        </button>
                        <?php 
                            // Limpiamos el número de teléfono (quitamos espacios o guiones para el enlace)
                            $tel_limpio = preg_replace('/[^0-9]/', '', $cl['telefono']);
                            $mensaje = urlencode("Hola " . $cl['nombre'] . ", te saludamos de BookingPro. ¿En qué podemos ayudarte?");
                            $url_wa = "https://wa.me/" . $tel_limpio . "?text=" . $mensaje;
                            ?>

                            <a href="<?= $url_wa ?>" target="_blank" class="text-green-500 hover:bg-green-50 p-2 rounded-lg transition" title="Enviar WhatsApp">
                                <i class="fab fa-whatsapp text-xl"></i>
                            </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script>
        document.getElementById('buscadorClientes').addEventListener('input', function(e) {
            const termino = e.target.value.toLowerCase();
            const filas = document.querySelectorAll('tbody tr');

            filas.forEach(fila => {
                const texto = fila.innerText.toLowerCase();
                // Si el término existe en la fila, la mostramos, si no, la ocultamos
                fila.style.display = texto.includes(termino) ? '' : 'none';
            });
        });

        function eliminarCliente(id) {
            // 1. Consultamos si tiene citas pendientes
            fetch(`clientes_verificar_citas.php?id=${id}`)
                .then(response => response.text())
                .then(totalCitas => {
                    totalCitas = parseInt(totalCitas);

                    if (totalCitas > 0) {
                        // ALERTA DE BLOQUEO: No se puede eliminar
                        Swal.fire({
                            icon: 'error',
                            title: 'No se puede eliminar',
                            text: `El cliente tiene ${totalCitas} cita(s) pendiente(s). Debes cancelarlas o completarlas primero.`,
                            confirmButtonColor: '#3b82f6'
                        });
                    } else {
                        // ALERTA DE CONFIRMACIÓN: Procede normalmente
                        Swal.fire({
                            title: '¿Estás seguro?',
                            text: "Se eliminará el historial del cliente. Esta acción es definitiva.",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#3085d6',
                            confirmButtonText: 'Sí, eliminar',
                            cancelButtonText: 'Cancelar'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = `clientes_eliminar.php?id=${id}`;
                            }
                        });
                    }
                });
        }
    </script>
</main>

<?php include 'templates/modal_cliente.php'; ?>
<?php include 'templates/footer.php'; ?>