<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include('../includes/db.php');
// 1. INCLUIMOS EL NUEVO GESTOR (Ya no el mailer directo)
include('../includes/gestor_notificaciones.php');

if (!isset($_SESSION['usuario_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php'); exit();
}

$reserva_id = $_POST['reserva_id'];
$fecha_entrada = $_POST['fecha_entrada'];
$fecha_salida = $_POST['fecha_salida'];
$cliente_id_sesion = $_SESSION['cliente_id'];

if (strtotime($fecha_salida) <= strtotime($fecha_entrada)) {
    header('Location: ../client/modificar_reserva.php?id=' . $reserva_id . '&error=fecha_invalida');
    exit();
}

try {
    $pdo->beginTransaction();

    // Validar propiedad de la reserva
    $stmt_check = $pdo->prepare("SELECT HabitacionID, ClienteID FROM Reservas WHERE ReservaID = ?");
    $stmt_check->execute([$reserva_id]);
    $reserva = $stmt_check->fetch();

    if (!$reserva || $reserva['ClienteID'] != $cliente_id_sesion) {
        throw new Exception("Acción no autorizada.");
    }

    // Actualizar fechas
    $sql_update = "UPDATE Reservas SET FechaEntrada = ?, FechaSalida = ? WHERE ReservaID = ?";
    $pdo->prepare($sql_update)->execute([$fecha_entrada, $fecha_salida, $reserva_id]);

    // Recalcular total
    $stmt_precio = $pdo->prepare("SELECT PrecioPorNoche FROM Habitaciones WHERE HabitacionID = ?");
    $stmt_precio->execute([$reserva['HabitacionID']]);
    $precio_noche = $stmt_precio->fetchColumn();
    
    $dias = (strtotime($fecha_salida) - strtotime($fecha_entrada)) / 86400;
    $total_nuevo = $precio_noche * $dias;

    $sql_venta = "UPDATE Venta SET Total = ? WHERE ReservaID = ?";
    $pdo->prepare($sql_venta)->execute([$total_nuevo, $reserva_id]);

    $pdo->commit();

    // 2. USAMOS EL GESTOR PARA NOTIFICAR (Igual que en el admin)
    try {
        notificarReservaModificada($pdo, $reserva_id, $fecha_entrada, $fecha_salida, $total_nuevo);
    } catch (Exception $e) { 
        // Si falla el correo, no detenemos la redirección
    }

    header('Location: ../client/index.php?success=modify_ok');
    exit();

} catch (Exception $e) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    header('Location: ../client/modificar_reserva.php?id=' . $reserva_id . '&error=' . urlencode($e->getMessage()));
    exit();
}
?>