<?php
require_once 'src/auth.php';
require_once 'src/Database.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $db = Database::getInstance();
    
    // Preparamos la sentencia para evitar SQL Injection
    $stmt = $db->prepare("DELETE FROM servicios WHERE id = ?");
    
    if ($stmt->execute([$id])) {
        // Redirigir con éxito
        header("Location: servicios.php?status=deleted");
    } else {
        // Redirigir con error
        header("Location: servicios.php?status=error");
    }
} else {
    header("Location: servicios.php");
}
exit();