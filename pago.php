<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// 1. Incluimos la base de datos y el Mailer
include('includes/db.php');
include('includes/mailer.php'); // <--- IMPORTANTE: Asegúrate que esta ruta sea correcta

// --- LÓGICA DE PROCESAMIENTO FINAL ---
if (isset($_POST['final_confirm']) && $_POST['final_confirm'] == '1') {
    
    if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['cliente_id'])) {
        header('Location: login.php');
        exit();
    }

    $cliente_id = $_SESSION['cliente_id'];
    $usuario_id = $_SESSION['usuario_id'];
    $habitacion_id = $_POST['habitacion_id'];
    $fecha_entrada = $_POST['fecha_entrada'];
    $fecha_salida = $_POST['fecha_salida'];
    $metodo_pago_id = $_POST['metodo_pago_id'];
    
    try {
        $pdo->beginTransaction();

        // 1. Verificamos el tipo de cliente
        $stmt_cliente_tipo = $pdo->prepare("SELECT PersonaID FROM Clientes WHERE ClienteID = ?");
        $stmt_cliente_tipo->execute([$cliente_id]);
        $es_persona = $stmt_cliente_tipo->fetchColumn();

        // 2. Asignamos documento
        $tipo_documento = $es_persona ? 'B' : 'F'; 

        // 3. Insertamos Reserva
        $sql_reserva = "INSERT INTO Reservas (ClienteID, UsuarioID, HabitacionID, FechaEntrada, FechaSalida, MetodoPagoID, TipoDocumento, Estado) VALUES (?, ?, ?, ?, ?, ?, ?, '1')";
        $stmt_reserva = $pdo->prepare($sql_reserva);
        $stmt_reserva->execute([$cliente_id, $usuario_id, $habitacion_id, $fecha_entrada, $fecha_salida, $metodo_pago_id, $tipo_documento]);
        $reserva_id = $pdo->lastInsertId();

        // 4. Datos Habitación y Precios
        // OJO: Modifiqué la consulta para traer el nombre y numero de habitación para el correo
        $stmt_hab = $pdo->prepare("SELECT h.PrecioPorNoche, h.NumeroHabitacion, th.N_TipoHabitacion 
                                   FROM Habitaciones h 
                                   JOIN TiposHabitacion th ON h.TipoHabitacionID = th.TipoHabitacionID 
                                   WHERE h.HabitacionID = ?");
        $stmt_hab->execute([$habitacion_id]);
        $datos_hab = $stmt_hab->fetch();
        
        $precio_noche = $datos_hab['PrecioPorNoche'];
        $dias = (strtotime($fecha_salida) - strtotime($fecha_entrada)) / 86400;
        $total = $precio_noche * $dias;

        // 5. Insertamos Venta
        $sql_venta = "INSERT INTO Venta (ReservaID, Total, Estado) VALUES (?, ?, '1')";
        $pdo->prepare($sql_venta)->execute([$reserva_id, $total]);
        
        // 6. Insertamos Comprobante
        $numero_comprobante = str_pad($reserva_id, 4, '0', STR_PAD_LEFT);
        $serie = ($tipo_documento == 'B') ? 'B001' : 'F001';
        $tabla_comp = ($tipo_documento == 'B') ? 'Boleta' : 'Factura';

        $sql_comprobante = "INSERT INTO $tabla_comp (ReservaID, Numero, Serie, Estado) VALUES (?, ?, ?, '1')";
        $pdo->prepare($sql_comprobante)->execute([$reserva_id, $numero_comprobante, $serie]);

        // 7. Obtenemos DATOS COMPLETOS del cliente para el correo
        // Esta consulta une Cliente -> Persona O Empresa para tener todos los datos
        $sql_datos_cliente = "
            SELECT 
                COALESCE(p.Correo, '') as Correo,
                CASE WHEN p.PersonaID IS NOT NULL THEN CONCAT(p.Nombres, ' ', p.Ape_Paterno) ELSE e.Razon_Social END as Nombre,
                CASE WHEN p.PersonaID IS NOT NULL THEN p.Doc_Identidad ELSE e.RUC END as Documento,
                CASE WHEN p.PersonaID IS NOT NULL THEN p.Direccion ELSE e.Direccion END as Direccion
            FROM Clientes c
            LEFT JOIN Persona p ON c.PersonaID = p.PersonaID
            LEFT JOIN Empresa e ON c.EmpresaID = e.EmpresaID
            WHERE c.ClienteID = ?
        ";
        $stmt_c = $pdo->prepare($sql_datos_cliente);
        $stmt_c->execute([$cliente_id]);
        $info_cliente = $stmt_c->fetch();

        $pdo->commit(); // Confirmamos la base de datos PRIMERO

        // ==========================================================
        //         AQUÍ VA EL ENVÍO DE CORREO (INCRUSTADO)
        // ==========================================================
        
        $titulo_doc = ($tipo_documento == 'B') ? "BOLETA DE VENTA ELECTRÓNICA" : "FACTURA ELECTRÓNICA";
        $fecha_emision = date('d/m/Y');
        
        // Plantilla HTML (Idéntica a tu diseño web)
        $mensajeHTML = "
        <div style='font-family: monospace, sans-serif; max-width: 600px; border: 1px solid #ddd; padding: 20px; background-color: #fff; margin: auto;'>
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
                <strong>Fecha:</strong> $fecha_emision<br>
                <strong>Cliente:</strong> {$info_cliente['Nombre']}<br>
                <strong>DOC:</strong> {$info_cliente['Documento']}<br>
                <strong>Dirección:</strong> {$info_cliente['Direccion']}
            </div>
            <table style='width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 14px;'>
                <thead>
                    <tr style='background-color: #f2f2f2;'>
                        <th style='border: 1px solid #ddd; padding: 8px;'>Cant.</th>
                        <th style='border: 1px solid #ddd; padding: 8px;'>Descripción</th>
                        <th style='border: 1px solid #ddd; padding: 8px;'>Importe</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style='border: 1px solid #ddd; padding: 8px;'>$dias</td>
                        <td style='border: 1px solid #ddd; padding: 8px;'>
                            Alojamiento - {$datos_hab['N_TipoHabitacion']} (#{$datos_hab['NumeroHabitacion']})<br>
                            <small>Del $fecha_entrada al $fecha_salida</small>
                        </td>
                        <td style='border: 1px solid #ddd; padding: 8px; text-align: right;'>S/ ".number_format($total, 2)."</td>
                    </tr>
                </tbody>
            </table>
            <div style='text-align: right; margin-top: 20px;'>
                <h3 style='margin: 0;'>TOTAL: S/ ".number_format($total, 2)."</h3>
            </div>
        </div>
        ";

        // Lógica de Envío: Si tiene correo -> Cliente + Copia Admin. Si no -> Solo Admin.
        if (!empty($info_cliente['Correo'])) {
            enviarNotificacion($info_cliente['Correo'], $info_cliente['Nombre'], "Reserva Confirmada #$reserva_id", $mensajeHTML);
        } else {
            // PON AQUÍ TU CORREO DE ADMIN PARA RECIBIR LA ALERTA
            enviarNotificacion('brayan.mh1087@gmail.com', 'Admin', "NUEVA RESERVA #$reserva_id (Cliente sin email)", $mensajeHTML);
        }

        // ==========================================================
        
        header('Location: admin/generar_recibo.php?id=' . $reserva_id);
        exit();

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        header('Location: reservar.php?error=' . urlencode('Error al procesar: ' . $e->getMessage()));
        exit();
    }
}

// --- LÓGICA VISUAL (Esto no cambia) ---
$habitacion_id = $_POST['habitacion_id'] ?? null;
$fecha_entrada = $_POST['fecha_entrada'] ?? null;
$fecha_salida = $_POST['fecha_salida'] ?? null;
$metodo_pago_id = $_POST['metodo_pago_id'] ?? null;

if (!$habitacion_id) { die("Error: Faltan datos."); }
include('includes/header_public.php');
?>
<style>
    .payment-container { display: flex; justify-content: center; padding: 40px 0; }
    .payment-box { padding: 30px; background: #fff; box-shadow: 0 0 15px rgba(0,0,0,0.1); border-radius: 8px; width: 600px; text-align: center; }
    .payment-instruction img { max-width: 300px; margin: 20px 0; }
</style>

<div class="payment-container">
    <div class="payment-box">
        <h2>Instrucciones de Pago</h2>
        <div class="payment-instruction">
            <?php
            switch ($metodo_pago_id) {
                case '1': echo '<p>Escanee el siguiente código para pagar con Yape:</p><img src="img/yape.jpg" alt="Código QR de Yape">'; break;
                case '2': echo '<p>Escanee el siguiente código para pagar con Plin:</p><img src="img/plin.jpg" alt="Código QR de Plin">'; break;
                case '3': echo '<h3>Pago en Recepción</h3><p>Por favor, acérquese a la recepción del hotel para completar el pago.</p>'; break;
            }
            ?>
        </div>
        
        <form action="pago.php" method="POST">
            <input type="hidden" name="habitacion_id" value="<?php echo htmlspecialchars($habitacion_id); ?>">
            <input type="hidden" name="fecha_entrada" value="<?php echo htmlspecialchars($fecha_entrada); ?>">
            <input type="hidden" name="fecha_salida" value="<?php echo htmlspecialchars($fecha_salida); ?>">
            <input type="hidden" name="metodo_pago_id" value="<?php echo htmlspecialchars($metodo_pago_id); ?>">
            <input type="hidden" name="final_confirm" value="1">
            <button type="submit" class="btn btn-success" style="width:100%; margin-top:20px; padding: 12px;">Continuar</button>
        </form>
    </div>
</div>

<?php include('includes/footer.php'); ?>