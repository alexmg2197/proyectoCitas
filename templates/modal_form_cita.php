<?php 
require_once 'src/auth.php'; 
require_once 'src/Database.php';

$db = Database::getInstance();

$stmt = $db->query("SELECT * FROM clientes ORDER BY id DESC");
$clientes = $stmt->fetchAll();

$stmt = $db->query("SELECT * FROM servicios ORDER BY id DESC");
$servicios = $stmt->fetchAll();
?>

<div id="modalFormCita" class="fixed inset-0 z-[60] hidden overflow-y-auto">
    <div class="fixed inset-0 bg-black bg-opacity-50"></div>
    <div class="relative top-10 mx-auto p-6 border w-full max-w-lg shadow-2xl rounded-xl bg-white">
        <div class="flex justify-between items-center border-b pb-3">
            <h3 class="text-xl font-bold" id="formCitaTitle">Agendar Cita</h3>
            <button onclick="cerrarModalFormCita()" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
        </div>

        <form action="citas_procesar.php" method="POST" class="mt-4 space-y-4">
            <input type="hidden" name="id" id="cita_id"> 
            <div>
                <label class="block text-sm font-medium text-gray-700">Cliente</label>
                <select name="cliente_id" id="f_cliente_id" required class="w-full mt-1 p-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="" selected disabled>-- Seleccione un cliente --</option>
                    <?php foreach($clientes as $cl): ?>
                        <option value="<?= $cl['id'] ?>"><?= htmlspecialchars($cl['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Servicio</label>
                <select name="servicio_id" id="f_servicio_id" required class="w-full mt-1 p-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="" selected disabled>-- Seleccione un servicio --</option>
                    <?php foreach($servicios as $ser): ?>
                        <option value="<?= $ser['id'] ?>"><?= htmlspecialchars($ser['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Fecha</label>
                    <input type="date" name="fecha" id="f_fecha" required class="w-full mt-1 p-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Hora</label>
                    <input type="time" name="hora" id="f_hora" required class="w-full mt-1 p-2 border rounded-lg">
                </div>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="submit" class="flex-1 bg-blue-600 text-white py-2 rounded-lg font-bold hover:bg-blue-700">Guardar Cita</button>
                <button type="button" onclick="cerrarModalFormCita()" class="flex-1 bg-gray-100 py-2 rounded-lg font-bold">Cancelar</button>
            </div>
        </form>
    </div>
</div>