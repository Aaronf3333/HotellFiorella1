<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

function enviarCorreo($destinatario, $nombre, $asunto, $cuerpoHTML, $adjuntoPDF = null, $nombrePDF = 'Documento.pdf') {
    $mail = new PHPMailer(true);

    try {
        // --- 🔒 LECTURA DE VARIABLES DE ENTORNO ---
        // Usamos getenv() para obtener los valores seguros de Render.
        $SMTP_HOST = getenv('SMTP_HOST') ?: 'smtp.gmail.com'; 
        $SMTP_USER = getenv('SMTP_USER') ?: 'brayan.mh1087@gmail.com'; 
        // Eliminamos los espacios de la contraseña por si acaso
        $SMTP_PASSWORD = str_replace(' ', '', getenv('SMTP_PASSWORD') ?: 'dcmcalymlyuzzici'); 
        $SMTP_PORT = getenv('SMTP_PORT') ?: 587; 
        
        $mail->isSMTP();
        // ASIGNACIÓN USANDO VARIABLES DE ENTORNO
        $mail->Host       = $SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = $SMTP_USER;
        $mail->Password   = $SMTP_PASSWORD;
        // La configuración de seguridad se mantiene para 587
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $SMTP_PORT;

        // El resto del código usa la variable leída
        $mail->setFrom($SMTP_USER, 'Hotel Fiorella'); 
        $mail->addAddress($destinatario, $nombre);
        
        // --- Notificación al personal ---
        $mail->addBCC('hotelfiorella@hotmail.com');

        if ($adjuntoPDF !== null) {
            $mail->addStringAttachment($adjuntoPDF, $nombrePDF, 'base64', 'application/pdf');
        }

        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body    = $cuerpoHTML;
        $mail->AltBody = strip_tags($cuerpoHTML);
        $mail->CharSet = 'UTF-8';

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Es crucial registrar el error para diagnosticar problemas
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}
?>
