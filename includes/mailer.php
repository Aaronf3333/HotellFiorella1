<?php
// Cargar clases de PHPMailer usando rutas absolutas
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// EL CAMBIO ESTÁ AQUÍ ABAJO: Agregamos __DIR__ . '/' antes de la ruta
require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

function enviarNotificacion($destinatario, $nombre_destinatario, $asunto, $mensajeHTML) {
    $mail = new PHPMailer(true);

    try {
        // Configuración del Servidor SMTP de Gmail
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'brayan.mh1087@gmail.com'; // <--- REVISA QUE ESTO ESTÉ CON TUS DATOS
        $mail->Password   = 'dcmc alym lyuz zici'; // <--- REVISA QUE ESTO ESTÉ CON TUS DATOS
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Configuración del Remitente y Destinatario
        $mail->setFrom('brayan.mh1087@gmail.com', 'Hotel Fiorella - Notificaciones');
        $mail->addAddress($destinatario, $nombre_destinatario);
        $mail->addBCC('brayan.mh1087@gmail.com'); 

        // Contenido
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body    = $mensajeHTML;
        $mail->AltBody = strip_tags($mensajeHTML);
        $mail->CharSet = 'UTF-8';

        $mail->send();
        return true;
    } catch (Exception $e) {
        // IMPORTANTE: Si falla el correo, lo guardamos en el log de errores de PHP
        // pero NO detenemos la ejecución de la web.
        error_log("Error PHPMailer: " . $mail->ErrorInfo);
        return false;
    }
}
?>