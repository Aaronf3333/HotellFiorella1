<?php
// actions/admin_modificar_process.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include('../includes/db.php');
// 1. INCLUIMOS EL GESTOR (El cerebro que maneja correos y PDFs)
include('../includes/gestor_notificaciones.php');

// Seguridad: Solo administradores
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 1 || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php'); exit();
}

$reserva_id = $_POST['reserva_id'];
$fecha_entrada = $_POST['fecha_entrada'];
$fecha_salida = $_POST['fecha_salida'];

// Validación de fechas
if (strtotime($fecha_salida) <= strtotime($fecha_entrada)) {
    header('Location: ../admin/admin_modificar_reserva.php?id=' . $reserva_id . '&error=fecha_invalida');
    exit();
}

try {
    $pdo->beginTransaction();

    // 1. Obtener precio por noche de la habitación actual
    $stmt_hab = $pdo->prepare("SELECT HabitacionID FROM Reservas WHERE ReservaID = ?");
    $stmt_hab->execute([$reserva_id]);
    $reserva = $stmt_hab->fetch();
    
    if (!$reserva) { throw new Exception("Reserva no encontrada"); }

    $stmt_precio = $pdo->prepare("SELECT PrecioPorNoche FROM Habitaciones WHERE HabitacionID = ?");
    $stmt_precio->execute([$reserva['HabitacionID']]);
    $precio_noche = $stmt_precio->fetchColumn();

    // 2. Calcular nuevo total
    $dias = (strtotime($fecha_salida) - strtotime($fecha_entrada)) / 86400;
    $total_nuevo = $precio_noche * $dias;

    // 3. Actualizar Base de Datos
    $sql_update = "UPDATE Reservas SET FechaEntrada = ?, FechaSalida = ? WHERE ReservaID = ?";
    $pdo->prepare($sql_update)->execute([$fecha_entrada, $fecha_salida, $reserva_id]);

    $sql_venta = "UPDATE Venta SET Total = ? WHERE ReservaID = ?";
    $pdo->prepare($sql_venta)->execute([$total_nuevo, $reserva_id]);

    $pdo->commit();

    // --- 4. NOTIFICACIÓN AUTOMÁTICA ---
    // Llamamos al gestor. Él buscará el correo del cliente, le enviará el aviso
    // y automáticamente le enviará una copia oculta (BCC) a 'hotelfiorella@hotmail.com' (según configuramos en mailer.php)
    try {
        notificarReservaModificada($pdo, $reserva_id, $fecha_entrada, $fecha_salida, $total_nuevo);
    } catch (Exception $e) {
        // Si el correo falla, no detenemos el sistema, solo lo registramos
        error_log("Error enviando notificación admin: " . $e->getMessage());
    }

    header('Location: ../admin/gestion_reservas.php?success=modify_ok');
    exit();

} catch (Exception $e) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    header('Location: ../admin/admin_modificar_reserva.php?id=' . $reserva_id . '&error=' . urlencode($e->getMessage()));
    exit();
}
?>