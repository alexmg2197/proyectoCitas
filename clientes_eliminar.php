<?php
require_once 'src/auth.php';
require_once 'config/Database.php';

if (isset($_GET['id'])) {
    $db = Database::getInstance();
    $id = (int)$_GET['id'];

    // OPCIONAL: Podrías verificar si tiene citas pendientes antes de borrar
    $stmt = $db->prepare("DELETE FROM clientes WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: clientes.php?status=deleted");
exit;