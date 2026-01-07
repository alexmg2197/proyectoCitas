<?php
require_once 'src/auth.php';
require_once 'src/Database.php';

$fecha = $_GET['fecha'] ?? null;

if (!$fecha) {
    echo "<p class='text-center text-red-500'>Fecha no válida.</p>";
    exit;
}

$db = Database::getInstance();

// Consulta profesional: Traemos cliente, servicio y el estado de la cita
$stmt = $db->prepare("
    SELECT c.id, c.hora, c.estado, cl.nombre as cliente, cl.telefono, s.nombre as servicio, s.precio, c.cliente_id, c.servicio_id, c.fecha
    FROM citas c
    JOIN clientes cl ON c.cliente_id = cl.id
    JOIN servicios s ON c.servicio_id = s.id
    WHERE c.fecha = ?
    ORDER BY c.hora ASC
");
$stmt->execute([$fecha]);
$citas = $stmt->fetchAll();

if (count($citas) === 0): ?>
    <div class="text-center py-10">
        <i class="fas fa-calendar-day text-gray-200 text-5xl mb-3"></i>
        <p class="text-gray-500">No hay citas programadas para este día.</p>
    </div>
<?php else: 
    foreach ($citas as $cita): 
        // Color según estado
        $badgeColor = [
            'pendiente' => 'bg-yellow-100 text-yellow-700',
            'confirmada' => 'bg-green-100 text-green-700',
            'cancelada' => 'bg-red-100 text-red-700',
            'completada' => 'bg-blue-100 text-blue-700'
        ][$cita['estado']] ?? 'bg-gray-100';
    ?>
    <div class="flex items-center justify-between p-4 border rounded-xl hover:shadow-md transition bg-white group">
        <div class="flex items-center gap-4">
            <div class="text-center border-r pr-4">
                <p class="text-lg font-bold text-blue-600"><?php echo date('H:i', strtotime($cita['hora'])); ?></p>
            </div>
            <div>
                <p class="font-bold text-gray-800"><?php echo htmlspecialchars($cita['cliente']); ?></p>
                <p class="text-xs text-gray-500"><?php echo htmlspecialchars($cita['servicio']); ?> • $<?php echo $cita['precio']; ?></p>
            </div>
        </div>
        
        <div class="flex items-center gap-3">
            <span class="px-2 py-1 rounded-md text-[10px] font-bold uppercase <?php echo $badgeColor; ?>">
                <?php echo $cita['estado']; ?>
            </span>
            
            <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition">
                <select onchange="actualizarEstadoCita(<?php echo $cita['id']; ?>, this.value)" 
                        class="text-xs border rounded p-1 bg-gray-50 focus:ring-2 focus:ring-blue-500">
                    <option value="pendiente" <?php echo $cita['estado'] == 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                    <option value="confirmada" <?php echo $cita['estado'] == 'confirmada' ? 'selected' : ''; ?>>Confirmada</option>
                    <option value="completada" <?php echo $cita['estado'] == 'completada' ? 'selected' : ''; ?>>Completada</option>
                    <option value="cancelada" <?php echo $cita['estado'] == 'cancelada' ? 'selected' : ''; ?>>Cancelada</option>
                </select>
                <?php
                $datosJson = json_encode($cita);
                    echo "<button onclick='abrirModalEditarCita($datosJson)' class='text-blue-500 hover:bg-blue-50 p-2 rounded-full'>
                            <i class='fas fa-edit'></i>
                        </button>";
                        ?>
                <button onclick="eliminarCita(<?php echo $cita['id']; ?>)" class="p-2 text-red-500 hover:bg-red-50 rounded-full">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>

    </div>
    <?php endforeach; 
endif; ?>