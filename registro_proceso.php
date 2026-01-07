<?php
require_once 'src/Database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = Database::getInstance();
    
    $nombre = trim($_POST['nombre']);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    // 1. Validaciones básicas
    if (empty($nombre) || empty($email) || empty($password)) {
        die("Todos los campos son obligatorios.");
    }

    // 2. HASHEAR la contraseña
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);
    echo($passwordHash);

    try {
        $stmt = $db->prepare("INSERT INTO usuarios (nombre, email, password) VALUES (:nombre, :email, :password)");
        $stmt->execute([
            'nombre'   => $nombre,
            'email'    => $email,
            'password' => $passwordHash // Guardamos el hash, no la clave real
        ]);
        echo "Usuario registrado con éxito.";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Error de duplicado (email único)
            echo "El correo ya está registrado.";
        } else {
            echo "Error al registrar.";
        }
    }
}