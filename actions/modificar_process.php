<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('../includes/db.php');
include('../includes/mailer.php'); // <--- INCLUIMOS EL MAILER

if (!isset($_SESSION['usuario_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit();
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

    // Verificamos reserva y obtenemos HabitacionID
    $stmt_check = $pdo->prepare("SELECT HabitacionID, ClienteID FROM Reservas WHERE ReservaID = ?");
    $stmt_check->execute([$reserva_id]);
    $reserva = $stmt_check->fetch();

    if (!$reserva || $reserva['ClienteID'] != $cliente_id_sesion) {
        throw new Exception("Acción no autorizada.");
    }

    // Actualizamos Reserva
    $sql_update = "UPDATE Reservas SET FechaEntrada = ?, FechaSalida = ? WHERE ReservaID = ?";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->execute([$fecha_entrada, $fecha_salida, $reserva_id]);

    // Recalculamos Total
    $stmt_precio = $pdo->prepare("SELECT PrecioPorNoche FROM Habitaciones WHERE HabitacionID = ?");
    $stmt_precio->execute([$reserva['HabitacionID']]);
    $precio_noche = $stmt_precio->fetchColumn();
    
    $dias = (strtotime($fecha_salida) - strtotime($fecha_entrada)) / 86400;
    $total_nuevo = $precio_noche * $dias;

    $sql_venta = "UPDATE Venta SET Total = ? WHERE ReservaID = ?";
    $stmt_venta = $pdo->prepare($sql_venta);
    $stmt_venta->execute([$total_nuevo, $reserva_id]);

    // OBTENER EMAIL
    $sql_cliente = "SELECT p.Correo, p.Nombres FROM Clientes c JOIN Persona p ON c.PersonaID = p.PersonaID WHERE c.ClienteID = ?";
    $stmt_c = $pdo->prepare($sql_cliente);
    $stmt_c->execute([$cliente_id_sesion]);
    $d_cliente = $stmt_c->fetch();

    $pdo->commit();

    // ENVIAR NOTIFICACIÓN
    if ($d_cliente && !empty($d_cliente['Correo'])) {
        $asunto = "Modificación de Reserva #$reserva_id - Hotel Fiorella";
        $mensajeHTML = "
        <h2>Sus fechas han cambiado</h2>
        <p>Hola {$d_cliente['Nombres']}, se han actualizado los datos de su reserva:</p>
        <ul>
            <li><strong>Nueva Entrada:</strong> $fecha_entrada</li>
            <li><strong>Nueva Salida:</strong> $fecha_salida</li>
            <li><strong>Nuevo Total:</strong> S/ $total_nuevo</li>
        </ul>
        ";
        enviarNotificacion($d_cliente['Correo'], $d_cliente['Nombres'], $asunto, $mensajeHTML);
    }

    header('Location: ../client/index.php?success=modify_ok');
    exit();

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    header('Location: ../client/modificar_reserva.php?id=' . $reserva_id . '&error=' . urlencode($e->getMessage()));
    exit();
}
?>