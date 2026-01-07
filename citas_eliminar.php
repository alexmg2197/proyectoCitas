<?php
require_once 'src/auth.php';
require_once 'src/Database.php';

if (isset($_GET['id'])) {
    $db = Database::getInstance();
    $id = (int)$_GET['id'];
    
    $stmt = $db->prepare("DELETE FROM citas WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: citas.php?status=deleted");
exit;