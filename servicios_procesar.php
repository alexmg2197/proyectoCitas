<?php
require_once 'src/auth.php';
require_once 'src/Database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = Database::getInstance();
    $id = $_POST['id'];
    $nombre = trim($_POST['nombre']);
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $duracion = $_POST['duracion'];

    if (empty($id)) {
        // CREAR
        $stmt = $db->prepare("INSERT INTO servicios (nombre, descripcion, precio, duracion_minutos) VALUES (?, ?, ?,?)");
        $stmt->execute([$nombre, $descripcion, $precio, $duracion]);
    } else {
        // ACTUALIZAR
        $stmt = $db->prepare("UPDATE servicios SET nombre = ?, descripcion = ?, precio = ?, duracion_minutos = ? WHERE id = ?");
        $stmt->execute([$nombre ,$descripcion, $precio, $duracion, $id]);
    }

    header("Location: servicios.php?status=success");
    exit;
}