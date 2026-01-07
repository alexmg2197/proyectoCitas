<?php
require_once 'src/auth.php';
require_once 'src/Database.php';

if (isset($_POST['id']) && isset($_POST['estado'])) {
    $db = Database::getInstance();
    $id = (int)$_POST['id'];
    $estado = $_POST['estado'];

    $stmt = $db->prepare("UPDATE citas SET estado = ? WHERE id = ?");
    if ($stmt->execute([$estado, $id])) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
}