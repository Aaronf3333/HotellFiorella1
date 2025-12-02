<?php
// =========================================================
// LÓGICA DE PROCESAMIENTO (Se ejecuta al dar clic en Confirmar)
// =========================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('includes/db.php');
// Incluimos el GESTOR que maneja los correos y PDFs
include('includes/gestor_notificaciones.php');

$error_message = ''; // Variable para almacenar mensajes de error

// AQUÍ ESTÁ LA CLAVE: Verificamos si se envió la señal 'final_confirm'
if (isset($_POST['final_confirm']) && $_POST['final_confirm'] == '1') {
    
    if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['cliente_id'])) {
        header('Location: login.php'); exit();
    }

    $cliente_id = $_SESSION['cliente_id'];
    $usuario_id = $_SESSION['usuario_id'];
    $habitacion_id = $_POST['habitacion_id'];
    $fecha_entrada = $_POST['fecha_entrada'];
    $fecha_salida = $_POST['fecha_salida'];
    $metodo_pago_id = $_POST['metodo_pago_id'];
    
    // ===================================================
    // 🛑 INICIO DE LA VALIDACIÓN DE FECHAS (SERVIDOR) 🛑
    // ===================================================
    try {
        $f_ingreso = new DateTime($fecha_entrada);
        $f_salida = new DateTime($fecha_salida);
        $hoy = new DateTime('today'); // Fecha actual al inicio del día

        // 1. Validar Fechas Pasadas (Fecha de Salida)
        if ($f_salida < $hoy) {
            $error_message = "La fecha de salida no puede ser anterior a la fecha actual. Por favor, corrija la fecha.";
        }
        
        // 2. Validar Duración Mínima (Salida debe ser al menos 1 día después del Ingreso)
        $f_ingreso_mas_un_dia = clone $f_ingreso;
        $f_ingreso_mas_un_dia->modify('+1 day');

        if (empty($error_message) && $f_salida < $f_ingreso_mas_un_dia) {
            $error_message = "Debe reservar por lo menos una noche (un día). La fecha de salida debe ser posterior a la fecha de ingreso.";
        }
        
    } catch (Exception $e) {
        $error_message = "Error en el formato de fecha proporcionado. Por favor, intente de nuevo.";
    }
    // ===================================================
    // 🟢 FIN DE LA VALIDACIÓN DE FECHAS 🟢
    // ===================================================

    // Si hay un error, guardarlo en sesión y NO procesar el pago
    if (!empty($error_message)) {
        $_SESSION['toast_error'] = $error_message;
        
        // Al fallar la validación, necesitamos mantener los datos para que no se pierdan.
        // Como ya estamos en pago.php, simplemente terminamos la ejecución de la lógica
        // y el código continúa hacia la vista HTML, que mostrará el toast.

    } else {
        // --- Si no hay errores de validación, procedemos con la transacción ---
        try {
            $pdo->beginTransaction();

            // 1. Determinar si es Boleta o Factura
            $stmt = $pdo->prepare("SELECT PersonaID FROM Clientes WHERE ClienteID = ?");
            $stmt->execute([$cliente_id]);
            $es_persona = $stmt->fetchColumn();
            $tipo_documento = $es_persona ? 'B' : 'F';

            // 2. Insertar Reserva
            $sql = "INSERT INTO Reservas (ClienteID, UsuarioID, HabitacionID, FechaEntrada, FechaSalida, MetodoPagoID, TipoDocumento, Estado) VALUES (?, ?, ?, ?, ?, ?, ?, '1')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$cliente_id, $usuario_id, $habitacion_id, $fecha_entrada, $fecha_salida, $metodo_pago_id, $tipo_documento]);
            $reserva_id = $pdo->lastInsertId();

            // 3. Calcular Totales
            $stmt = $pdo->prepare("SELECT PrecioPorNoche FROM Habitaciones WHERE HabitacionID = ?");
            $stmt->execute([$habitacion_id]);
            $precio = $stmt->fetchColumn();
            $dias = (strtotime($fecha_salida) - strtotime($fecha_entrada)) / 86400;
            $total = $precio * $dias;

            // 4. Insertar Venta y Comprobante (Usando 4 dígitos para el número)
            $pdo->prepare("INSERT INTO Venta (ReservaID, Total, Estado) VALUES (?, ?, '1')")->execute([$reserva_id, $total]);
            
            $tabla = ($es_persona) ? 'Boleta' : 'Factura';
            $serie = ($es_persona) ? 'B001' : 'F001';
            $numero = str_pad($reserva_id, 4, '0', STR_PAD_LEFT);
            
            $pdo->prepare("INSERT INTO $tabla (ReservaID, Numero, Serie, Estado) VALUES (?, ?, ?, '1')")->execute([$reserva_id, $numero, $serie]);

            $pdo->commit();

            // 5. NOTIFICACIÓN (Llamamos al gestor)
            // Usamos try/catch para que si falla el correo, la reserva siga siendo válida
            try {
                notificarReservaCreada($pdo, $reserva_id);
            } catch (Exception $e) {
                error_log("Error en notificación: " . $e->getMessage());
            }

            // Redirigir al recibo
            header('Location: admin/generar_recibo.php?id=' . $reserva_id);
            exit();

        } catch (Exception $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            // Si hay un error de DB, lo guardamos en sesión para el toast
            $_SESSION['toast_error'] = "Error procesando la reserva en la base de datos: " . $e->getMessage();
            // Y dejamos que continúe hacia la vista
        }
    }
}

// =========================================================
// VISTA HTML (Se muestra cuando vienes de seleccionar método)
// =========================================================

// Recibir datos del paso anterior (reservar.php)
// Usamos los datos POST para que, si falla la validación, los datos se mantengan en el formulario
$habitacion_id = $_POST['habitacion_id'] ?? null;
$fecha_entrada = $_POST['fecha_entrada'] ?? null;
$fecha_salida = $_POST['fecha_salida'] ?? null;
$metodo_pago_id = $_POST['metodo_pago_id'] ?? null;

if (!$habitacion_id) { 
    // Si intentan entrar directo sin datos, mandar al inicio
    header('Location: index.php'); exit(); 
}

include('includes/header_public.php');
?>

<div class="container" style="margin-top: 50px; margin-bottom: 50px;">
    <!-- Contenedor del Toast de error -->
    <div aria-live="polite" aria-atomic="true" style="position: fixed; top: 0; right: 0; min-height: 200px; z-index: 1050;">
        <div style="position: absolute; top: 10px; right: 10px;">
        
        <?php
        // Mostrar el Toast si existe un mensaje de error en la sesión
        if (isset($_SESSION['toast_error'])): ?>
            <div class="toast bg-danger text-white border-0" role="alert" aria-live="assertive" aria-atomic="true" data-delay="6000">
                <div class="toast-header bg-danger text-white">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong class="mr-auto">Error de Reserva</strong>
                    <button type="button" class="ml-2 mb-1 close text-white" data-dismiss="toast" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="toast-body">
                    <?php echo htmlspecialchars($_SESSION['toast_error']); ?>
                </div>
            </div>
            <script>
                // Inicializar y mostrar el Toast con JavaScript
                window.onload = function() {
                    $('.toast').toast('show');
                };
            </script>
        <?php
            // Limpiar la sesión para que el mensaje no se muestre de nuevo
            unset($_SESSION['toast_error']);
        endif;
        ?>

        </div>
    </div>
    <!-- Fin Contenedor del Toast -->

    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-header bg-success text-white text-center py-3">
                    <h3 class="mb-0"><i class="fas fa-money-bill-wave mr-2"></i>Finalizar Pago</h3>
                </div>
                <div class="card-body p-5 text-center">
                    
                    <h5 class="mb-4 font-weight-bold text-secondary">Instrucciones para completar su reserva</h5>
                    
                    <div class="payment-instruction mb-5 p-4 bg-light rounded">
                        <?php if ($metodo_pago_id == '1'): ?>
                            <p class="lead mb-3">Escanee el siguiente código para pagar con <strong>Yape</strong>:</p>
                            <img src="img/yape.jpg" alt="Código QR de Yape" class="img-fluid rounded shadow-sm" style="max-width: 250px; border: 2px solid #28a745;">
                            <p class="mt-3 text-muted"><small>Envíe el comprobante al WhatsApp del hotel.</small></p>
                        
                        <?php elseif ($metodo_pago_id == '2'): ?>
                            <p class="lead mb-3">Escanee el siguiente código para pagar con <strong>Plin</strong>:</p>
                            <img src="img/plin.jpg" alt="Código QR de Plin" class="img-fluid rounded shadow-sm" style="max-width: 250px; border: 2px solid #007bff;">
                             <p class="mt-3 text-muted"><small>Envíe el comprobante al WhatsApp del hotel.</small></p>

                        <?php else: ?>
                            <div class="alert alert-info shadow-sm">
                                <h4 class="alert-heading"><i class="fas fa-concierge-bell mr-2"></i>Pago en Recepción</h4>
                                <p class="mb-0">Por favor, acérquese a la recepción del hotel para realizar el pago y confirmar su estadía.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <form action="pago.php" method="POST">
                        <input type="hidden" name="habitacion_id" value="<?php echo htmlspecialchars($habitacion_id); ?>">
                        <input type="hidden" name="fecha_entrada" value="<?php echo htmlspecialchars($fecha_entrada); ?>">
                        <input type="hidden" name="fecha_salida" value="<?php echo htmlspecialchars($fecha_salida); ?>">
                        <input type="hidden" name="metodo_pago_id" value="<?php echo htmlspecialchars($metodo_pago_id); ?>">
                        
                        <input type="hidden" name="final_confirm" value="1">
                        
                        <button type="submit" class="btn btn-lg btn-block btn-success font-weight-bold py-3 shadow transition-hover">
                            <i class="fas fa-check-circle mr-2"></i>Confirmar Pago y Generar Boleta
                        </button>
                    </form>
                    </div>
            </div>
            <div class="text-center mt-3">
                <a href="index.php" class="text-muted"><i class="fas fa-arrow-left mr-1"></i>Volver al inicio</a>
            </div>
        </div>
    </div>
</div>

<style>
    .transition-hover:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
</style>
