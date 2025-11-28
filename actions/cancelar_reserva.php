<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('../includes/db.php');
include('../includes/mailer.php'); // <--- INCLUIMOS EL MAILER

if (!isset($_SESSION['usuario_id']) || !isset($_GET['id'])) {
    header('Location: ../login.php');
    exit();
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
    
    // Cancelamos
    $sql_cancelar = "UPDATE Reservas SET Estado = '0' WHERE ReservaID = ?";
    $stmt_cancelar = $pdo->prepare($sql_cancelar);
    $stmt_cancelar->execute([$reserva_id]);

    // Liberamos habitación
    $sql_liberar = "UPDATE Habitaciones SET Estado_HabitacionID = 1 WHERE HabitacionID = ?";
    $stmt_liberar = $pdo->prepare($sql_liberar);
    $stmt_liberar->execute([$reserva['HabitacionID']]);
    
    // OBTENER EMAIL
    $sql_cliente = "SELECT p.Correo, p.Nombres FROM Clientes c JOIN Persona p ON c.PersonaID = p.PersonaID WHERE c.ClienteID = ?";
    $stmt_c = $pdo->prepare($sql_cliente);
    $stmt_c->execute([$cliente_id_sesion]);
    $d_cliente = $stmt_c->fetch();

    $pdo->commit();

    // ENVIAR NOTIFICACIÓN
    if ($d_cliente && !empty($d_cliente['Correo'])) {
        $asunto = "Cancelación de Reserva #$reserva_id - Hotel Fiorella";
        $mensajeHTML = "
        <h2 style='color:red;'>Reserva Cancelada</h2>
        <p>Estimado/a {$d_cliente['Nombres']},</p>
        <p>Confirmamos que su reserva <strong>#$reserva_id</strong> ha sido cancelada correctamente.</p>
        <p>Esperamos verle en otra oportunidad.</p>
        ";
        enviarNotificacion($d_cliente['Correo'], $d_cliente['Nombres'], $asunto, $mensajeHTML);
    }

    header('Location: ../client/index.php?success=cancel_ok');
    exit();

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    header('Location: ../client/index.php?error=' . urlencode($e->getMessage()));
    exit();
}
?>