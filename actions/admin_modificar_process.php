<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('../includes/db.php');
include('../includes/mailer.php'); // <--- 1. AGREGADO: Importamos el mailer

// Seguridad: solo administradores
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 1 || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit();
}

$reserva_id = $_POST['reserva_id'];
$fecha_entrada = $_POST['fecha_entrada'];
$fecha_salida = $_POST['fecha_salida'];

if (strtotime($fecha_salida) <= strtotime($fecha_entrada)) {
    header('Location: ../admin/admin_modificar_reserva.php?id=' . $reserva_id . '&error=fecha_invalida');
    exit();
}

try {
    $pdo->beginTransaction();

    // 2. MODIFICADO: Consultamos TODOS los datos necesarios de una vez
    // (Datos de habitación para el precio y datos del cliente para el correo)
    $sql_datos = "
        SELECT 
            r.HabitacionID, 
            h.PrecioPorNoche,
            h.NumeroHabitacion,
            th.N_TipoHabitacion,
            COALESCE(p.Correo, '') as Correo,
            CASE WHEN p.PersonaID IS NOT NULL THEN CONCAT(p.Nombres, ' ', p.Ape_Paterno) ELSE e.Razon_Social END as NombreCliente
        FROM Reservas r
        JOIN Habitaciones h ON r.HabitacionID = h.HabitacionID
        JOIN TiposHabitacion th ON h.TipoHabitacionID = th.TipoHabitacionID
        LEFT JOIN Clientes c ON r.ClienteID = c.ClienteID
        LEFT JOIN Persona p ON c.PersonaID = p.PersonaID
        LEFT JOIN Empresa e ON c.EmpresaID = e.EmpresaID
        WHERE r.ReservaID = ?
    ";
    $stmt_datos = $pdo->prepare($sql_datos);
    $stmt_datos->execute([$reserva_id]);
    $reserva_data = $stmt_datos->fetch();

    if (!$reserva_data) {
        throw new Exception("La reserva no existe.");
    }

    // Actualizamos las fechas
    $sql_update = "UPDATE Reservas SET FechaEntrada = ?, FechaSalida = ? WHERE ReservaID = ?";
    $pdo->prepare($sql_update)->execute([$fecha_entrada, $fecha_salida, $reserva_id]);

    // Recalculamos el total
    $precio_noche = $reserva_data['PrecioPorNoche'];
    $dias = (strtotime($fecha_salida) - strtotime($fecha_entrada)) / 86400;
    $total_nuevo = $precio_noche * $dias;

    // Actualizamos el total en Venta
    $sql_venta = "UPDATE Venta SET Total = ? WHERE ReservaID = ?";
    $pdo->prepare($sql_venta)->execute([$total_nuevo, $reserva_id]);

    $pdo->commit();

    // --- 3. AGREGADO: ENVIAR NOTIFICACIÓN ---
    // Preparamos el correo
    $asunto = "Actualización de Reserva #XYZ$reserva_id - Hotel Fiorella";
    $mensajeHTML = "
    <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #ccc; max-width: 600px;'>
        <h2 style='color: #007bff;'>Su reserva ha sido modificada</h2>
        <p>Hola <strong>{$reserva_data['NombreCliente']}</strong>,</p>
        <p>La administración ha realizado cambios en las fechas de su reserva #XYZ$reserva_id.</p>
        <hr>
        <h3>Nuevos Detalles:</h3>
        <ul>
            <li><strong>Habitación:</strong> {$reserva_data['N_TipoHabitacion']} ({$reserva_data['NumeroHabitacion']})</li>
            <li><strong>Nueva Entrada:</strong> " . date('d/m/Y', strtotime($fecha_entrada)) . "</li>
            <li><strong>Nueva Salida:</strong> " . date('d/m/Y', strtotime($fecha_salida)) . "</li>
            <li><strong>Nuevo Total:</strong> S/ " . number_format($total_nuevo, 2) . "</li>
        </ul>
        <p>Si tiene dudas, por favor contáctenos.</p>
        <p><em>Hotel Fiorella Administración</em></p>
    </div>
    ";

    // Enviamos
    if (!empty($reserva_data['Correo'])) {
        enviarNotificacion($reserva_data['Correo'], $reserva_data['NombreCliente'], $asunto, $mensajeHTML);
    } else {
        // Si no tiene correo, te avisa a ti como Admin
        enviarNotificacion('brayan.mh1087@gmail.com', 'Admin', $asunto . " (Cliente sin Email)", $mensajeHTML);
    }

    header('Location: ../admin/gestion_reservas.php?success=modify_ok');
    exit();

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    header('Location: ../admin/admin_modificar_reserva.php?id=' . $reserva_id . '&error=' . urlencode($e->getMessage()));
    exit();
}
?>