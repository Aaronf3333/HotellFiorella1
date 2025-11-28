<?php
// includes/gestor_notificaciones.php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/mailer.php';
require_once __DIR__ . '/generar_pdf.php'; // Asegúrate de tener el generador de PDF aquí

// 1. NOTIFICACIÓN DE NUEVA RESERVA (Con PDF Adjunto)
function notificarReservaCreada($pdo, $reserva_id) {
    // Datos del cliente y reserva
    $sql = "SELECT COALESCE(p.Correo, '') as Email, 
                   CASE WHEN p.PersonaID IS NOT NULL THEN CONCAT(p.Nombres, ' ', p.Ape_Paterno) ELSE e.Razon_Social END as Nombre
            FROM Reservas r
            LEFT JOIN Clientes c ON r.ClienteID = c.ClienteID
            LEFT JOIN Persona p ON c.PersonaID = p.PersonaID
            LEFT JOIN Empresa e ON c.EmpresaID = e.EmpresaID
            WHERE r.ReservaID = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$reserva_id]);
    $datos = $stmt->fetch();

    if (!$datos) return;

    // Generar PDF en memoria
    $pdfBinario = generarPDF($pdo, $reserva_id, 'string');
    $nombreArchivo = "Comprobante_Reserva_{$reserva_id}.pdf";

    // Mensaje
    $asunto = "Confirmación de Reserva #$reserva_id - Hotel Fiorella";
    $mensajeHTML = "
        <h2>¡Gracias por su reserva!</h2>
        <p>Estimado(a) <strong>{$datos['Nombre']}</strong>,</p>
        <p>Su reserva ha sido confirmada. Adjunto encontrará su comprobante electrónico detallado.</p>
    ";

    // Decidir destinatario (Si no tiene email, va al hotel)
    $emailDestino = !empty($datos['Email']) ? $datos['Email'] : 'hotelfiorella@hotmail.com';
    
    enviarCorreo($emailDestino, $datos['Nombre'], $asunto, $mensajeHTML, $pdfBinario, $nombreArchivo);
}

// 2. NOTIFICACIÓN DE MODIFICACIÓN (Admin cambia fechas)
function notificarReservaModificada($pdo, $reserva_id, $fecha_in, $fecha_out, $nuevo_total) {
    $sql = "SELECT COALESCE(p.Correo, '') as Email, 
                   CASE WHEN p.PersonaID IS NOT NULL THEN CONCAT(p.Nombres, ' ', p.Ape_Paterno) ELSE e.Razon_Social END as Nombre,
                   h.NumeroHabitacion, th.N_TipoHabitacion
            FROM Reservas r
            JOIN Habitaciones h ON r.HabitacionID = h.HabitacionID
            JOIN TiposHabitacion th ON h.TipoHabitacionID = th.TipoHabitacionID
            LEFT JOIN Clientes c ON r.ClienteID = c.ClienteID
            LEFT JOIN Persona p ON c.PersonaID = p.PersonaID
            LEFT JOIN Empresa e ON c.EmpresaID = e.EmpresaID
            WHERE r.ReservaID = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$reserva_id]);
    $datos = $stmt->fetch();

    if (!$datos) return;

    $asunto = "Actualización de Reserva #$reserva_id";
    $mensajeHTML = "
        <h2 style='color:#007bff;'>Su reserva ha sido modificada</h2>
        <p>Hola <strong>{$datos['Nombre']}</strong>,</p>
        <p>Se han actualizado los detalles de su estadía:</p>
        <ul>
            <li><strong>Habitación:</strong> {$datos['N_TipoHabitacion']} ({$datos['NumeroHabitacion']})</li>
            <li><strong>Nuevas Fechas:</strong> " . date('d/m/Y', strtotime($fecha_in)) . " al " . date('d/m/Y', strtotime($fecha_out)) . "</li>
            <li><strong>Nuevo Total:</strong> S/ " . number_format($nuevo_total, 2) . "</li>
        </ul>
    ";

    $emailDestino = !empty($datos['Email']) ? $datos['Email'] : 'hotelfiorella@hotmail.com';
    enviarCorreo($emailDestino, $datos['Nombre'], $asunto, $mensajeHTML);
}

// 3. NOTIFICACIÓN DE CANCELACIÓN
function notificarReservaCancelada($pdo, $reserva_id) {
    $sql = "SELECT COALESCE(p.Correo, '') as Email, 
                   CASE WHEN p.PersonaID IS NOT NULL THEN CONCAT(p.Nombres, ' ', p.Ape_Paterno) ELSE e.Razon_Social END as Nombre
            FROM Reservas r
            LEFT JOIN Clientes c ON r.ClienteID = c.ClienteID
            LEFT JOIN Persona p ON c.PersonaID = p.PersonaID
            LEFT JOIN Empresa e ON c.EmpresaID = e.EmpresaID
            WHERE r.ReservaID = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$reserva_id]);
    $datos = $stmt->fetch();

    if (!$datos) return;

    $asunto = "Cancelación de Reserva #$reserva_id";
    $mensajeHTML = "<h2 style='color:red;'>Reserva Cancelada</h2><p>La reserva #$reserva_id ha sido anulada correctamente.</p>";

    $emailDestino = !empty($datos['Email']) ? $datos['Email'] : 'hotelfiorella@hotmail.com';
    enviarCorreo($emailDestino, $datos['Nombre'], $asunto, $mensajeHTML);
}
?>