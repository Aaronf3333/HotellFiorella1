<?php
// actions/admin_cancelar_reserva.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include('../includes/db.php');
// 1. INCLUIMOS EL GESTOR
include('../includes/gestor_notificaciones.php');

// Seguridad: Solo administradores
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 1 || !isset($_GET['id'])) {
    header('Location: ../login.php'); exit();
}

$reserva_id = $_GET['id'];

try {
    $pdo->beginTransaction();

    // Verificamos que la reserva exista y obtenemos la habitación para liberarla
    $sql_check = "SELECT HabitacionID FROM Reservas WHERE ReservaID = ?";
    $stmt = $pdo->prepare($sql_check);
    $stmt->execute([$reserva_id]);
    $reserva = $stmt->fetch();

    if ($reserva) {
        // 1. Cancelar Reserva (Estado = 0)
        $pdo->prepare("UPDATE Reservas SET Estado = '0' WHERE ReservaID = ?")->execute([$reserva_id]);
        
        // 2. Liberar Habitación (Estado = 1 Disponible)
        $pdo->prepare("UPDATE Habitaciones SET Estado_HabitacionID = 1 WHERE HabitacionID = ?")->execute([$reserva['HabitacionID']]);
        
        $pdo->commit();

        // --- 3. NOTIFICACIÓN AUTOMÁTICA ---
        // Esto envía un correo al cliente diciendo "Su reserva fue cancelada"
        // Y envía copia a hotelfiorella@hotmail.com
        try {
            notificarReservaCancelada($pdo, $reserva_id);
        } catch (Exception $e) {
            error_log("Error enviando notificación cancelación: " . $e->getMessage());
        }

    } else {
        $pdo->rollBack();
    }
    
    header('Location: ../admin/gestion_reservas.php?success=cancel_ok');
    exit();

} catch (Exception $e) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    header('Location: ../admin/gestion_reservas.php?error=' . urlencode($e->getMessage()));
    exit();
}
?>