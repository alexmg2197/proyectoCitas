<?php
require_once 'src/auth.php';
require_once 'src/Database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = Database::getInstance();
    
    $id = $_POST['id'] ?? '';
    $cliente_id = $_POST['cliente_id'];
    $servicio_id = $_POST['servicio_id'];
    $fecha = $_POST['fecha'];
    $hora_inicio_nueva = $_POST['hora']; // Ejemplo: "12:10"

    // 1. Obtener duración del servicio actual
    $stmtS = $db->prepare("SELECT duracion_minutos FROM servicios WHERE id = ?");
    $stmtS->execute([$servicio_id]);
    $duracion_nueva = $stmtS->fetch()['duracion_minutos'];

    // 2. Calcular fin de la nueva cita en segundos (Timestamp)
    $inicio_nueva_ts = strtotime("$fecha $hora_inicio_nueva");
    $fin_nueva_ts = strtotime("+$duracion_nueva minutes", $inicio_nueva_ts);

    // 3. Consultar TODAS las citas de ese día (excepto la que estamos editando)
    $sql = "SELECT c.hora, s.duracion_minutos 
            FROM citas c 
            JOIN servicios s ON c.servicio_id = s.id 
            WHERE c.fecha = ? AND c.id != ? AND c.estado != 'cancelada'";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([$fecha, $id]);
    $citas_existentes = $stmt->fetchAll();

    $choque = false;
    foreach ($citas_existentes as $cita) {
        // Datos de la cita ya guardada en la DB
        $inicio_existente_ts = strtotime("$fecha " . $cita['hora']);
        $fin_existente_ts = strtotime("+{$cita['duracion_minutos']} minutes", $inicio_existente_ts);

        // REGLA DE ORO DE COLISIÓN:
        // Hay choque si: (Inicio Nueva < Fin Existente) Y (Fin Nueva > Inicio Existente)
        if ($inicio_nueva_ts < $fin_existente_ts && $fin_nueva_ts > $inicio_existente_ts) {
            $choque = true;
            break;
        }
    }

    if ($choque) {
        // Redirigir con un error para SweetAlert
        header("Location: citas.php?status=error_overlap&fecha=$fecha");
        exit;
    }

    // 4. Si no hay choque, proceder con el INSERT o UPDATE habitual
    if (empty($id)) {
        $stmt = $db->prepare("INSERT INTO citas (cliente_id, servicio_id, fecha, hora, estado) VALUES (?, ?, ?, ?, 'pendiente')");
        $stmt->execute([$cliente_id, $servicio_id, $fecha, $hora_inicio_nueva]);
    } else {
        $stmt = $db->prepare("UPDATE citas SET cliente_id = ?, servicio_id = ?, fecha = ?, hora = ? WHERE id = ?");
        $stmt->execute([$cliente_id, $servicio_id, $fecha, $hora_inicio_nueva, $id]);
    }

    header("Location: citas.php?status=success&fecha=$fecha");
    exit;
}