<?php
// admin/generar_recibo.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include('../includes/db.php');
// Incluimos el generador maestro
include('../includes/generar_pdf.php');

if (!isset($_GET['id'])) { die("Falta ID de reserva"); }
$reserva_id = $_GET['id'];

// SI LA URL TIENE ?action=download, FORZAMOS LA DESCARGA
if (isset($_GET['action']) && $_GET['action'] == 'download') {
    generarPDF($pdo, $reserva_id, 'descargar');
    exit();
}
// SI LA URL TIENE ?action=view, MOSTRAMOS EL PDF EN EL NAVEGADOR
if (isset($_GET['action']) && $_GET['action'] == 'view') {
    generarPDF($pdo, $reserva_id, 'ver');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Comprobante de Reserva #<?php echo $reserva_id; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { margin: 0; display: flex; flex-direction: column; height: 100vh; background: #525659; font-family: sans-serif; }
        .toolbar { background: #323639; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.2); }
        .toolbar h1 { margin: 0; font-size: 18px; font-weight: normal; }
        .actions a { text-decoration: none; padding: 8px 15px; border-radius: 4px; font-size: 14px; margin-left: 10px; color: white; transition: 0.2s; }
        .btn-download { background-color: #28a745; }
        .btn-download:hover { background-color: #218838; }
        .btn-back { background-color: #6c757d; }
        .btn-back:hover { background-color: #5a6268; }
        iframe { flex: 1; border: none; width: 100%; height: 100%; display: block; }
    </style>
</head>
<body>
    <div class="toolbar">
        <h1><i class="fas fa-receipt"></i> Comprobante #<?php echo $reserva_id; ?></h1>
        <div class="actions">
            <a href="generar_recibo.php?id=<?php echo $reserva_id; ?>&action=download" class="btn-download">
                <i class="fas fa-download"></i> Descargar PDF
            </a>
            <a href="gestion_reservas.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
    
    <iframe src="generar_recibo.php?id=<?php echo $reserva_id; ?>&action=view#toolbar=0"></iframe>
</body>
</html>