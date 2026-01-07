<?php 
require_once 'src/auth.php'; 
require_once 'src/Database.php';

$db = Database::getInstance();
$stmt = $db->query("SELECT * FROM servicios ORDER BY id DESC");
$servicios = $stmt->fetchAll();
?>

<?php include 'templates/sidebar.php'; ?>
<?php include 'templates/header.php'; ?>

<main class="p-4 md:p-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <h2 class="text-xl md:text-2xl font-bold text-gray-800">Gestión de Servicios</h2>
        <button onclick="abrirModalNuevo()" class="w-full sm:w-auto bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition flex items-center justify-center">
            <i class="fas fa-plus mr-2"></i> Nuevo Servicio
        </button>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left min-w-[600px]">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-4 md:px-6 py-3 text-xs font-bold text-gray-500 uppercase">Nombre</th>
                        <th class="px-4 md:px-6 py-3 text-xs font-bold text-gray-500 uppercase">Descripción</th>
                        <th class="px-4 md:px-6 py-3 text-xs font-bold text-gray-500 uppercase">Precio</th>
                        <th class="px-4 md:px-6 py-3 text-xs font-bold text-gray-500 uppercase">Duración</th>
                        <th class="px-4 md:px-6 py-3 text-xs font-bold text-gray-500 uppercase text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach($servicios as $s): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 md:px-6 py-4 font-semibold text-gray-700"><?php echo htmlspecialchars($s['nombre']); ?></td>
                        <td class="px-4 md:px-6 py-4 text-gray-600 text-sm"><?php echo htmlspecialchars($s['descripcion']); ?></td>
                        <td class="px-4 md:px-6 py-4 text-gray-600">$<?php echo number_format($s['precio'], 2); ?></td>
                        <td class="px-4 md:px-6 py-4 text-gray-600"><?php echo $s['duracion_minutos']; ?> min</td>
                        <td class="px-4 md:px-6 py-4 text-center space-x-2 md:space-x-3 whitespace-nowrap">
                            <button onclick='abrirModalEditar(<?php echo json_encode($s); ?>)' class="text-blue-600 hover:text-blue-900 p-1">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="eliminarServicio(<?php echo $s['id']; ?>)" class="text-red-600 hover:text-red-900 p-1">
                                <i class="fas fa-trash"></i>
                            </button>
                            <?php if(isset($_GET['status'])): ?>
                                <script>
                                    const status = "<?php echo $_GET['status']; ?>";
                                    
                                    if (status === 'deleted') {
                                        Swal.fire({
                                            title: "¡Eliminado!",
                                            text: "El servicio ha sido borrado correctamente.",
                                            icon: "success"
                                        });
                                    } else if (status === 'error') {
                                        Swal.fire({
                                            title: "Error",
                                            text: "No se pudo eliminar el servicio.",
                                            icon: "error"
                                        });
                                    }
                                </script>
                            <?php endif; ?>                        <!-- <a href="servicios_eliminar.php?id=<?php echo $s['id']; ?>" 
                               onclick="return confirm('¿Estás seguro de eliminar este servicio?')" 
                               class="text-red-600 hover:text-red-900"><i class="fas fa-trash"></i></a> -->
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Modal agregar servico -->
<div id="modalServicio" class="fixed inset-0 z-[60] hidden overflow-y-auto p-4">
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="cerrarModal()"></div>

    <div class="relative top-10 mx-auto p-6 border w-full max-w-md shadow-2xl rounded-xl bg-white">
        <div class="flex justify-between items-center pb-3 border-b">
            <h3 class="text-2xl font-bold text-gray-800" id="modalTitle">Nuevo Servicio</h3>
            <button onclick="cerrarModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <form id="formServicio" action="servicios_procesar.php" method="POST" class="mt-4 space-y-4">
            <input type="hidden" name="id" id="servicio_id">

            <div>
                <label class="block text-sm font-medium text-gray-700">Nombre</label>
                <input type="text" name="nombre" id="nombre" required 
                    class="w-full mt-1 p-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Descripción</label>
                <input type="text" name="descripcion" id="descripcion" required 
                    class="w-full mt-1 p-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Precio ($)</label>
                    <input type="number" step="0.01" name="precio" id="precio" required 
                        class="w-full mt-1 p-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Duración (min)</label>
                    <input type="number" name="duracion" id="duracion" required 
                        class="w-full mt-1 p-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
            </div>
            
            <div class="flex gap-3 pt-4">
                <button type="submit" class="flex-1 bg-blue-600 text-white py-2 rounded-lg font-bold hover:bg-blue-700 transition">
                    Guardar Cambios
                </button>
                <!-- Confirmacion de -->
                <?php if(isset($_GET['status']) && $_GET['status'] == 'success'): ?>
                    <script>
                        Swal.fire({
                            icon: 'success',
                            title: '¡Logrado!',
                            text: 'El servicio se guardó correctamente',
                            // timer: 2000,
                            showConfirmButton: true
                        });
                    </script>
                <?php endif; ?>
                <button type="button" onclick="cerrarModal()" class="flex-1 bg-gray-100 text-gray-700 py-2 rounded-lg font-bold hover:bg-gray-200">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const modal = document.getElementById('modalServicio');
    const form = document.getElementById('formServicio');
    const modalTitle = document.getElementById('modalTitle');

    function abrirModalNuevo() {
        form.reset(); // Limpia el formulario
        document.getElementById('servicio_id').value = '';
        modalTitle.innerText = 'Nuevo Servicio';
        modal.classList.remove('hidden');
    }

    function abrirModalEditar(servicio) {
        modalTitle.innerText = 'Editar Servicio';
        // Llenamos los campos con los datos actuales
        document.getElementById('servicio_id').value = servicio.id;
        document.getElementById('nombre').value = servicio.nombre;
        document.getElementById('descripcion').value = servicio.descripcion;
        document.getElementById('precio').value = servicio.precio;
        document.getElementById('duracion').value = servicio.duracion_minutos;
        
        modal.classList.remove('hidden');
    }

    function cerrarModal() {
        modal.classList.add('hidden');
    }

    function eliminarServicio(id){
        Swal.fire({
            title: "¿Estás seguro que deseas eliminar el servicio?",
            text: "Esta acción no se puede revertir!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Si, eliminalo!",
            cancelButtonText: "Cancelar"
            }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `servicios_eliminar.php?id=${id}`;
            }
        });
    }
</script>

<?php include 'templates/footer.php'; ?>