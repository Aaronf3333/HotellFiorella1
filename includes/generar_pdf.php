<?php
// includes/generar_pdf.php

if (ob_get_length()) ob_clean();
require_once __DIR__ . '/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

function generarPDF($pdo, $reserva_id, $modo = 'string') {
    // 1. CONSULTA SQL
    $sql = "
        SELECT 
            r.ReservaID, r.FechaEntrada, r.FechaSalida, r.TipoDocumento,
            v.Total,
            CASE WHEN c.PersonaID IS NOT NULL THEN CONCAT(p.Nombres, ' ', p.Ape_Paterno, ' ', p.Ape_Materno) ELSE e.Razon_Social END as ClienteNombre,
            CASE WHEN c.PersonaID IS NOT NULL THEN p.Doc_Identidad ELSE e.RUC END as ClienteDoc,
            CASE WHEN c.PersonaID IS NOT NULL THEN p.Direccion ELSE e.Direccion END as ClienteDir,
            CASE WHEN c.PersonaID IS NOT NULL THEN p.Correo ELSE NULL END as ClienteEmail,
            h.NumeroHabitacion, h.PrecioPorNoche, th.N_TipoHabitacion,
            COALESCE(b.Serie, f.Serie) as Serie,
            COALESCE(b.Numero, f.Numero) as Numero,
            mp.NombreMetodo as MetodoPago
        FROM Reservas r
        JOIN Venta v ON r.ReservaID = v.ReservaID
        JOIN Habitaciones h ON r.HabitacionID = h.HabitacionID
        JOIN TiposHabitacion th ON h.TipoHabitacionID = th.TipoHabitacionID
        JOIN MetodosPago mp ON r.MetodoPagoID = mp.MetodoPagoID
        LEFT JOIN Clientes c ON r.ClienteID = c.ClienteID
        LEFT JOIN Persona p ON c.PersonaID = p.PersonaID
        LEFT JOIN Empresa e ON c.EmpresaID = e.EmpresaID
        LEFT JOIN Boleta b ON r.ReservaID = b.ReservaID
        LEFT JOIN Factura f ON r.ReservaID = f.ReservaID
        WHERE r.ReservaID = ?
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$reserva_id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) return null;

    // 2. CÁLCULOS
    $total = $data['Total'];
    $subtotal = $total / 1.18;
    $igv = $total - $subtotal;
    $dias = (strtotime($data['FechaSalida']) - strtotime($data['FechaEntrada'])) / 86400;
    
    $tipo_doc_texto = ($data['TipoDocumento'] == 'B') ? 'BOLETA DE VENTA ELECTRÓNICA' : 'FACTURA ELECTRÓNICA';
    $doc_label = ($data['TipoDocumento'] == 'B') ? 'DNI' : 'RUC';
    $color = ($data['TipoDocumento'] == 'B') ? '#007bff' : '#d9534f'; 

    // --- TRUCO VISUAL: RELLENAMOS CON CEROS PARA QUE SE VEA PROFESIONAL (8 Dígitos) ---
    // Aunque en la BD sean 4, aquí lo mostramos como 8.
    $numero_visual = str_pad($data['Numero'], 8, '0', STR_PAD_LEFT);

    // 3. DISEÑO HTML
    $html = '
    <html>
    <head>
        <style>
            body { font-family: Helvetica, Arial, sans-serif; font-size: 12px; color: #333; line-height: 1.4; }
            .header-table { width: 100%; margin-bottom: 20px; }
            .company-info { font-size: 11px; color: #555; }
            .ruc-box { border: 2px solid ' . $color . '; padding: 10px; text-align: center; border-radius: 8px; width: 100%; }
            .ruc-box h3 { margin: 0; font-size: 14px; color: #333; }
            .ruc-number { font-size: 16px; font-weight: bold; margin-top: 5px; color: #000; }
            
            .client-box { background-color: #f8f9fa; padding: 15px; border-left: 4px solid ' . $color . '; margin-bottom: 20px; }
            .client-row { margin-bottom: 4px; }
            .label { font-weight: bold; width: 100px; display: inline-block; }
            
            .items-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            .items-table th { background-color: ' . $color . '; color: #fff; padding: 8px; text-align: left; font-size: 11px; }
            .items-table td { border-bottom: 1px solid #ddd; padding: 8px; }
            
            .totals-table { width: 40%; float: right; margin-top: 20px; }
            .totals-table td { text-align: right; padding: 4px; }
            .total-final { background-color: #eee; font-weight: bold; border-top: 1px solid #999; }
            
            .footer { margin-top: 50px; text-align: center; font-size: 10px; color: #999; border-top: 1px solid #eee; padding-top: 10px; }
        </style>
    </head>
    <body>
        <table class="header-table">
            <tr>
                <td width="60%" valign="top">
                    <h1 style="color: #2c3e50; margin: 0;">HOTEL FIORELLA</h1>
                    <div class="company-info">
                        Av. Paracas Mz A Lote 4, Paracas, Ica<br>
                        RUC: 20325065266<br>
                        Tel: (056) 123-456 | Email: reservas@hotelfiorella.com
                    </div>
                </td>
                <td width="35%" valign="top">
                    <div class="ruc-box">
                        <h3>R.U.C. 20325065266</h3>
                        <div style="background: #eee; margin: 5px 0; padding: 2px;">' . $tipo_doc_texto . '</div>
                        <div class="ruc-number">' . $data['Serie'] . ' - ' . $numero_visual . '</div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="client-box">
            <div class="client-row"><span class="label">Cliente:</span> ' . htmlspecialchars($data['ClienteNombre']) . '</div>
            <div class="client-row"><span class="label">' . $doc_label . ':</span> ' . htmlspecialchars($data['ClienteDoc']) . '</div>
            <div class="client-row"><span class="label">Dirección:</span> ' . htmlspecialchars($data['ClienteDir']) . '</div>
            <div class="client-row"><span class="label">Fecha:</span> ' . date('d/m/Y') . '</div>
            <div class="client-row"><span class="label">Pago:</span> ' . htmlspecialchars($data['MetodoPago']) . '</div>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th width="10%">CANT.</th>
                    <th width="50%">DESCRIPCIÓN</th>
                    <th width="20%" style="text-align: right;">P. UNIT</th>
                    <th width="20%" style="text-align: right;">IMPORTE</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>' . $dias . '</td>
                    <td>
                        SERVICIO DE ALOJAMIENTO<br>
                        <small style="color: #666;">Hab: ' . htmlspecialchars($data['N_TipoHabitacion']) . ' (#' . $data['NumeroHabitacion'] . ')</small><br>
                        <small>Del ' . date('d/m/Y', strtotime($data['FechaEntrada'])) . ' al ' . date('d/m/Y', strtotime($data['FechaSalida'])) . '</small>
                    </td>
                    <td style="text-align: right;">' . number_format($data['PrecioPorNoche'], 2) . '</td>
                    <td style="text-align: right;">' . number_format($total, 2) . '</td>
                </tr>
            </tbody>
        </table>

        <table class="totals-table">
            <tr><td>OP. GRAVADA:</td><td>S/ ' . number_format($subtotal, 2) . '</td></tr>
            <tr><td>I.G.V. (18%):</td><td>S/ ' . number_format($igv, 2) . '</td></tr>
            <tr class="total-final"><td>TOTAL:</td><td>S/ ' . number_format($total, 2) . '</td></tr>
        </table>

        <div style="clear: both;"></div>
        <div class="footer">
            Representación impresa del Comprobante Electrónico. Gracias por su preferencia.
        </div>
    </body>
    </html>';

    // 4. GENERAR
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);
    
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    if ($modo == 'descargar') {
        $dompdf->stream("Comprobante_" . $data['Serie'] . "-" . $numero_visual . ".pdf", ["Attachment" => true]);
    } elseif ($modo == 'ver') {
        $dompdf->stream("Comprobante.pdf", ["Attachment" => false]);
    } else {
        return $dompdf->output();
    }
}
?>