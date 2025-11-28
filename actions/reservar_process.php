<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Asegúrate de que las rutas sean correctas
include('../includes/db.php');
include('../includes/mailer.php'); 

if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['cliente_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit();
}

$cliente_id = $_SESSION['cliente_id'];
$usuario_id = $_SESSION['usuario_id'];
// Recibir datos del formulario
$habitacion_id = $_POST['habitacion_id'];
$fecha_entrada = $_POST['fecha_entrada'];
$fecha_salida = $_POST['fecha_salida'];
$metodo_pago_id = $_POST['metodo_pago_id'];
$tipo_documento = $_POST['tipo_documento'];

// Validación de fechas
if (strtotime($fecha_salida) <= strtotime($fecha_entrada)) {
    header('Location: ../reservar.php?error=' . urlencode('La fecha de salida debe ser posterior a la fecha de entrada.'));
    exit();
}

try {
    $pdo->beginTransaction();

    // 1. Insertamos la Reserva
    $sql_reserva = "INSERT INTO Reservas (ClienteID, UsuarioID, HabitacionID, FechaEntrada, FechaSalida, MetodoPagoID, TipoDocumento, Estado) VALUES (?, ?, ?, ?, ?, ?, ?, '1')";
    $stmt_reserva = $pdo->prepare($sql_reserva);
    $stmt_reserva->execute([$cliente_id, $usuario_id, $habitacion_id, $fecha_entrada, $fecha_salida, $metodo_pago_id, $tipo_documento]);
    $reserva_id = $pdo->lastInsertId();

    // 2. Obtener datos de la Habitación y Precios
    $stmt_hab = $pdo->prepare("SELECT h.PrecioPorNoche, h.NumeroHabitacion, th.N_TipoHabitacion 
                               FROM Habitaciones h
                               INNER JOIN TiposHabitacion th ON h.TipoHabitacionID = th.TipoHabitacionID 
                               WHERE h.HabitacionID = ?");
    $stmt_hab->execute([$habitacion_id]);
    $habitacion_data = $stmt_hab->fetch(); 
    
    // Cálculos
    $precio_noche = $habitacion_data['PrecioPorNoche'];
    $dias = (strtotime($fecha_salida) - strtotime($fecha_entrada)) / 86400;
    $total = $precio_noche * $dias;

    // 3. Insertamos Venta
    $sql_venta = "INSERT INTO Venta (ReservaID, Total, Estado) VALUES (?, ?, '1')";
    $stmt_venta = $pdo->prepare($sql_venta);
    $stmt_venta->execute([$reserva_id, $total]);
    
    // 4. Generamos Comprobante (Simulado para que coincida con tu imagen)
    $es_boleta = ($tipo_documento == 'B');
    $serie = $es_boleta ? 'B001' : 'F001';
    $tabla_comp = $es_boleta ? 'Boleta' : 'Factura';
    // Generamos un número correlativo simple basado en el ID (para el ejemplo)
    $numero_comprobante = str_pad($reserva_id, 8, "0", STR_PAD_LEFT); 
    
    $sql_comprobante = "INSERT INTO $tabla_comp (ReservaID, Numero, Serie, Estado) VALUES (?, ?, ?, '1')";
    $stmt_comp = $pdo->prepare($sql_comprobante);
    $stmt_comp->execute([$reserva_id, $numero_comprobante, $serie]);

    // 5. OBTENER DATOS DEL CLIENTE (Nombre, DNI, Dirección, Correo)
    $sql_cliente = "SELECT p.Correo, p.Nombres, p.Ape_Paterno, p.Doc_Identidad, p.Direccion 
                    FROM Clientes c 
                    INNER JOIN Persona p ON c.PersonaID = p.PersonaID 
                    WHERE c.ClienteID = ?";
    $stmt_cliente = $pdo->prepare($sql_cliente);
    $stmt_cliente->execute([$cliente_id]);
    $datos_cliente = $stmt_cliente->fetch();

    $pdo->commit(); 

    // =========================================================================
    //   AQUÍ OCURRE LA MAGIA: ENVÍO DEL CORREO (Estilo Boleta idéntico a tu foto)
    // =========================================================================
    
    if ($datos_cliente && !empty($datos_cliente['Correo'])) {
        
        $nombre_completo = $datos_cliente['Nombres'] . ' ' . $datos_cliente['Ape_Paterno'];
        $dni_cliente = $datos_cliente['Doc_Identidad'];
        $direccion_cliente = $datos_cliente['Direccion'];
        $titulo_doc = $es_boleta ? "BOLETA DE VENTA ELECTRÓNICA" : "FACTURA ELECTRÓNICA";
        $fecha_emision = date('d/m/Y');
        
        // Plantilla HTML diseñada para parecerse a tu imagen
        $mensajeHTML = "
        <div style='font-family: monospace, sans-serif; max-width: 600px; border: 1px solid #ddd; padding: 20px; background-color: #fff;'>
            
            <div style='text-align: center; margin-bottom: 20px;'>
                <h2 style='margin: 0; font-size: 24px; font-weight: bold;'>Hotel Fiorella</h2>
                <p style='margin: 5px 0;'>Av Paracas mz a lote 4, Paracas</p>
                <p style='margin: 5px 0;'>RUC: 20325065266</p>
            </div>
            
            <hr style='border: 0; border-top: 1px solid #ccc;'>
            
            <div style='text-align: center; margin: 20px 0;'>
                <h3 style='margin: 0;'>$titulo_doc</h3>
                <p style='margin: 10px 0; font-weight: bold; font-size: 18px;'>$serie - N° $numero_comprobante</p>
            </div>
            
            <hr style='border: 0; border-top: 1px solid #ccc;'>
            
            <div style='margin: 20px 0; font-size: 14px; line-height: 1.6;'>
                <strong>Fecha de Emisión:</strong> $fecha_emision<br>
                <strong>Cliente:</strong> $nombre_completo<br>
                <strong>DNI:</strong> $dni_cliente<br>
                <strong>Dirección:</strong> $direccion_cliente
            </div>
            
            <table style='width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 14px;'>
                <thead>
                    <tr style='background-color: #f2f2f2;'>
                        <th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Cant.</th>
                        <th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Descripción</th>
                        <th style='border: 1px solid #ddd; padding: 8px; text-align: right;'>P. Unit.</th>
                        <th style='border: 1px solid #ddd; padding: 8px; text-align: right;'>Importe</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style='border: 1px solid #ddd; padding: 8px;'>$dias</td>
                        <td style='border: 1px solid #ddd; padding: 8px;'>
                            Servicio de Alojamiento - {$habitacion_data['N_TipoHabitacion']} (#{$habitacion_data['NumeroHabitacion']})<br>
                            <small>Del $fecha_entrada al $fecha_salida</small>
                        </td>
                        <td style='border: 1px solid #ddd; padding: 8px; text-align: right;'>S/ ".number_format($precio_noche, 2)."</td>
                        <td style='border: 1px solid #ddd; padding: 8px; text-align: right;'>S/ ".number_format($total, 2)."</td>
                    </tr>
                </tbody>
            </table>
            
            <div style='text-align: right; margin-top: 20px;'>
                <h3 style='margin: 0;'>TOTAL: S/ ".number_format($total, 2)."</h3>
            </div>
            
            <p style='text-align: center; font-size: 12px; color: #888; margin-top: 30px;'>
                Gracias por su preferencia.
            </p>
        </div>
        ";

        // Enviamos al cliente y la copia oculta al admin
        $asunto = "Comprobante de Pago - Reserva #$reserva_id";
        enviarNotificacion($datos_cliente['Correo'], $nombre_completo, $asunto, $mensajeHTML);
    
    } else {
        // Si NO tiene correo, notificamos al admin
        // RECUERDA PONER TU CORREO ABAJO
        $asunto = "NUEVA RESERVA #$reserva_id (Cliente Sin Email)";
        enviarNotificacion('brayan.mh1087@gmail.com', 'Admin', $asunto, "Se generó una reserva pero el cliente no tiene email registrado.");
    }

    // =========================================================================
    //   REDIRECCIÓN FINAL
    // =========================================================================
    
    // IMPORTANTE: Aquí es donde debes decidir a dónde va el usuario.
    // Si tienes un archivo que muestra la boleta (como la imagen), pon su nombre aquí.
    // Ejemplo: header("Location: ../ver_boleta.php?id=$reserva_id");
    
    header("Location: ../client/index.php?success=reserva_ok");
    exit();

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // Si falla, lo mandamos de vuelta al formulario con el error
    header('Location: ../reservar.php?error=' . urlencode('Error en el servidor: ' . $e->getMessage()));
    exit();
}
?>