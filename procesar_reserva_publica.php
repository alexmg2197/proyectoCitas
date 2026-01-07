<?php
require_once 'src/Database.php';
$db = Database::getInstance();

$nombre = $_POST['nombre'] ?? '';
$telefono = $_POST['telefono'] ?? '';
$correo = $_POST['correo'] ?? '';
$servicio_id = $_POST['servicio_id'] ?? '';
$fecha = $_POST['fecha'] ?? '';
$hora = $_POST['hora'] ?? '';

try {
    // 1. Verificar si el cliente ya existe por teléfono
    $stmt = $db->prepare("SELECT id FROM clientes WHERE telefono = ?");
    $stmt->execute([$telefono]);
    $cliente = $stmt->fetch();

    if ($cliente) {
        $cliente_id = $cliente['id'];
    } else {
        // 2. Si no existe, lo creamos
        $ins = $db->prepare("INSERT INTO clientes (nombre, telefono, email) VALUES (?, ?, ?)");
        $ins->execute([$nombre, $telefono, $correo]);
        $cliente_id = $db->lastInsertId();
    }

    // 3. Insertar la cita
    $cita = $db->prepare("INSERT INTO citas (cliente_id, servicio_id, fecha, hora, estado) VALUES (?, ?, ?, ?, 'pendiente')");
    $cita->execute([$cliente_id, $servicio_id, $fecha, $hora]);

    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}