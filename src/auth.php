<?php
session_start();

// Si no existe la sesión del usuario, redirigir al login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}