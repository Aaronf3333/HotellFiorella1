<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include('../includes/db.php');
// 1. INCLUIMOS EL NUEVO GESTOR
include('../includes/gestor_notificaciones.php');

if (!isset($_SESSION['usuario_id']) || !isset($_GET['id'])) {
    header('Location: ../login.php'); exit();
}

$reserva_id = $_GET['id'];
$cliente_id_sesion = $_SESSION['cliente_id'];

try {
    $pdo->beginTransaction();

    $sql_check = "SELECT HabitacionID, ClienteID FROM Reservas WHERE ReservaID = ?";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([$reserva_id]);
    $reserva = $stmt_check->fetch();

    if (!$reserva || $reserva['ClienteID'] != $cliente_id_sesion) {
        throw new Exception("Acción no autorizada.");
    }
    
    // Cancelar
    $pdo->prepare("UPDATE Reservas SET Estado = '0' WHERE ReservaID = ?")->execute([$reserva_id]);
    // Liberar habitación
    $pdo->prepare("UPDATE Habitaciones SET Estado_HabitacionID = 1 WHERE HabitacionID = ?")->execute([$reserva['HabitacionID']]);
    
    $pdo->commit();

    // 2. NOTIFICAR VIA GESTOR
    try {
        notificarReservaCancelada($pdo, $reserva_id);
    } catch (Exception $e) { 
        // Error silencioso de correo
    }

    header('Location: ../client/index.php?success=cancel_ok');
    exit();

} catch (Exception $e) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    header('Location: ../client/index.php?error=' . urlencode($e->getMessage()));
    exit();
}
?>