<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

function enviarCorreo($destinatario, $nombre, $asunto, $cuerpoHTML, $adjuntoPDF = null, $nombrePDF = 'Documento.pdf') {
    $mail = new PHPMailer(true);

    try {
        // --- LECTURA DE VARIABLES DE ENTORNO ---
        // (El valor por defecto es ahora 465, que coincide con el cifrado SMTPS)
        $SMTP_HOST = getenv('SMTP_HOST') ?: 'smtp.gmail.com'; 
        $SMTP_USER = getenv('SMTP_USER') ?: 'brayan.mh1087@gmail.com'; 
        $SMTP_PASSWORD = str_replace(' ', '', getenv('SMTP_PASSWORD') ?: 'dcmcalymlyuzzici'); 
        $SMTP_PORT = getenv('SMTP_PORT') ?: 465; // CAMBIO AQUÍ: Valor por defecto a 465
        
        $mail->isSMTP();
        $mail->Host       = $SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = $SMTP_USER;
        $mail->Password   = $SMTP_PASSWORD;
        
        // ** CAMBIO CLAVE 1: Configuración SMTPS para Puerto 465 **
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Usamos SMTPS (SSL)
        $mail->Port       = $SMTP_PORT;

        // ** CAMBIO CLAVE 2: Tiempo de Espera (Timeout) **
        $mail->Timeout = 10; // Falla más rápido (10 segundos) si no puede conectar

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
        // Esto registrará el error específico en los logs de Render
        error_log("Mailer Error: " . $mail->ErrorInfo); 
        return false;
    }
}
?>
