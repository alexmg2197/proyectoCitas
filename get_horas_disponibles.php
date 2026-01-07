<?php
require_once 'src/Database.php';
$db = Database::getInstance();

$fecha = $_GET['fecha'] ?? '';

if (!$fecha) {
    echo json_encode([]);
    exit;
}

// 1. Definir rango de horas de trabajo (puedes cambiarlo a tu gusto)
$inicio = new DateTime('09:00');
$fin = new DateTime('18:00');
$intervalo = new DateInterval('PT1H'); // Citas cada 1 hora
$periodo = new DatePeriod($inicio, $intervalo, $fin);

$horasPosibles = [];
foreach ($periodo as $hora) {
    $horasPosibles[] = $hora->format('H:i:s');
}

// 2. Consultar horas ya ocupadas en la BD para esa fecha
$stmt = $db->prepare("SELECT hora FROM citas WHERE fecha = ? AND estado != 'cancelada'");
$stmt->execute([$fecha]);
$ocupadas = $stmt->fetchAll(PDO::FETCH_COLUMN);

// 3. Filtrar las horas: solo dejamos las que NO están en el array de ocupadas
$libres = array_values(array_diff($horasPosibles, $ocupadas));

header('Content-Type: application/json');
echo json_encode($libres);