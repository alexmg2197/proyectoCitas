<?php
session_start(); // Iniciamos sesión
require_once 'src/Database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = Database::getInstance();
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    $stmt = $db->prepare("SELECT id, nombre, password FROM usuarios WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $usuario = $stmt->fetch();

    // password_verify es la clave de la seguridad aquí
    if ($usuario && password_verify($password, $usuario['password'])) {
        // Login exitoso: Guardamos datos en la sesión
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nombre'] = $usuario['nombre'];
        
        // Regenerar ID de sesión para evitar "Session Fixation" (Seguridad Extra)
        session_regenerate_id(true);

        header("Location: dashboard.php");
        exit;
    } else {
        echo "Credenciales incorrectas.";
    }
}