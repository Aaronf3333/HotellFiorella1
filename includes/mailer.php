<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

function enviarCorreo($destinatario, $nombre, $asunto, $cuerpoHTML, $adjuntoPDF = null, $nombrePDF = 'Documento.pdf') {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'brayan.mh1087@gmail.com'; 
        $mail->Password   = 'dcmc alym lyuz zici';     
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('brayan.mh1087@gmail.com', 'Hotel Fiorella'); 
        $mail->addAddress($destinatario, $nombre);
        
        // --- AQUÍ EL CAMBIO PARA QUE TE LLEGUE A TI ---
        // Usamos el alias +admin o tu correo de hotmail
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
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}
?>