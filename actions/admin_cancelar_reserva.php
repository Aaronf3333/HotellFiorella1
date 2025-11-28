<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('../includes/db.php');
include('../includes/mailer.php'); // <--- 1. INCLUIR MAILER

// Seguridad: solo administradores
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 1 || !isset($_GET['id'])) {
    header('Location: ../login.php');
    exit();
}

$reserva_id = $_GET['id'];

try {
    $pdo->beginTransaction();

    // 2. OBTENER DATOS ANTES DE CANCELAR (Para el correo)
    // Necesitamos saber quién es el cliente y qué habitación tenía
    $sql_datos = "
        SELECT 
            r.HabitacionID, 
            COALESCE(p.Correo, '') as Correo,
            CASE WHEN p.PersonaID IS NOT NULL THEN CONCAT(p.Nombres, ' ', p.Ape_Paterno) ELSE e.Razon_Social END as NombreCliente,
            h.NumeroHabitacion,
            th.N_TipoHabitacion
        FROM Reservas r
        LEFT JOIN Clientes c ON r.ClienteID = c.ClienteID
        LEFT JOIN Persona p ON c.PersonaID = p.PersonaID
        LEFT JOIN Empresa e ON c.EmpresaID = e.EmpresaID
        LEFT JOIN Habitaciones h ON r.HabitacionID = h.HabitacionID
        LEFT JOIN TiposHabitacion th ON h.TipoHabitacionID = th.TipoHabitacionID
        WHERE r.ReservaID = ?
    ";
    $stmt_datos = $pdo->prepare($sql_datos);
    $stmt_datos->execute([$reserva_id]);
    $reserva = $stmt_datos->fetch();

    if ($reserva) {
        // Cancelar Reserva
        $sql_cancelar = "UPDATE Reservas SET Estado = '0' WHERE ReservaID = ?";
        $pdo->prepare($sql_cancelar)->execute([$reserva_id]);

        // Liberar Habitación
        $sql_liberar = "UPDATE Habitaciones SET Estado_HabitacionID = 1 WHERE HabitacionID = ?";
        $pdo->prepare($sql_liberar)->execute([$reserva['HabitacionID']]);
        
        $pdo->commit(); // Confirmamos cambio en BD

        // --- 3. ENVIAR NOTIFICACIÓN ---
        $asunto = "Cancelación de Reserva #XYZ$reserva_id - Hotel Fiorella";
        $mensajeHTML = "
        <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #ffcccc; background-color: #fffafa;'>
            <h2 style='color: #d9534f;'>Reserva Cancelada</h2>
            <p>Estimado/a <strong>{$reserva['NombreCliente']}</strong>,</p>
            <p>Le informamos que su reserva ha sido cancelada por la administración.</p>
            <ul>
                <li><strong>Reserva:</strong> #XYZ$reserva_id</li>
                <li><strong>Habitación:</strong> {$reserva['N_TipoHabitacion']} ({$reserva['NumeroHabitacion']})</li>
            </ul>
            <p>Si cree que esto es un error, por favor contáctenos.</p>
        </div>
        ";

        if (!empty($reserva['Correo'])) {
            enviarNotificacion($reserva['Correo'], $reserva['NombreCliente'], $asunto, $mensajeHTML);
        } else {
            // Si el cliente no tiene correo, te avisa a ti
            enviarNotificacion('brayan.mh1087@gmail.com', 'Admin', $asunto . " (Cliente sin Email)", $mensajeHTML);
        }

    } else {
        // Si no existe la reserva, rollback por si acaso
        $pdo->rollBack();
    }
    
    header('Location: ../admin/gestion_reservas.php?success=cancel_ok');
    exit();

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    header('Location: ../admin/gestion_reservas.php?error=' . urlencode($e->getMessage()));
    exit();
}
?>