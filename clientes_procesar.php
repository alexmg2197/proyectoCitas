<?php
require_once 'src/auth.php';
require_once 'src/Database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = Database::getInstance();
    $id = $_POST['id'] ?? '';
    $nombre = trim($_POST['nombre']);
    $telefono = trim($_POST['telefono']);
    $email = trim($_POST['email']);

    if (empty($id)) {
        // CREAR
        $stmt = $db->prepare("INSERT INTO clientes (nombre, telefono, email) VALUES (?, ?, ?)");
        $stmt->execute([$nombre, $telefono, $email]);
    } else {
        // ACTUALIZAR
        $stmt = $db->prepare("UPDATE clientes SET nombre = ?, telefono = ?, email = ? WHERE id = ?");
        $stmt->execute([$nombre, $telefono, $email, $id]);
    }

    header("Location: clientes.php?status=success");
    exit;
}