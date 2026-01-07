<?php
require_once 'src/auth.php';
require_once 'src/Database.php';

$id = $_GET['id'] ?? 0;
$db = Database::getInstance();

// Contamos solo citas que no estén canceladas ni completadas (pendientes o confirmadas)
$stmt = $db->prepare("SELECT COUNT(*) FROM citas WHERE cliente_id = ? AND estado IN ('pendiente', 'confirmada')");
$stmt->execute([$id]);
echo $stmt->fetchColumn();