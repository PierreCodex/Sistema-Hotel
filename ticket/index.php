<?php
/**
 * Página pública para consulta de tickets/comprobantes
 * URL: /ticket/B001-00000001
 * 
 * Esta página es accesible sin login para que los clientes puedan
 * ver y descargar su comprobante escaneando el QR.
 */

// No requiere autenticación - es página pública
require_once(__DIR__ . "/../config/conexion.php");
require_once(__DIR__ . "/../models/Boleta.php");

// Obtener el parámetro del ticket
$ticket_param = $_GET['t'] ?? '';

// Si viene de la URL amigable (ej: /ticket/B001-00000001)
if (empty($ticket_param)) {
    $uri = $_SERVER['REQUEST_URI'];
    if (preg_match('/\/ticket\/([A-Z]\d{3}-\d+)$/i', $uri, $matches)) {
        $ticket_param = $matches[1];
    }
}

// Validar formato del ticket
if (empty($ticket_param) || !preg_match('/^[A-Z]\d{3}-\d+$/i', $ticket_param)) {
    // Mostrar página de error amigable
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Ticket no encontrado - Hotel Las Palmeras</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            .card {
                background: white;
                border-radius: 16px;
                padding: 40px;
                max-width: 400px;
                text-align: center;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            }
            .icon { font-size: 60px; margin-bottom: 20px; }
            h1 { color: #333; margin-bottom: 10px; font-size: 24px; }
            p { color: #666; margin-bottom: 20px; }
        </style>
    </head>
    <body>
        <div class="card">
            <div class="icon">🔍</div>
            <h1>Ticket no encontrado</h1>
            <p>El número de ticket ingresado no es válido o no existe.</p>
            <p style="font-size: 12px; color: #999;">Formato esperado: B001-00000001</p>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Separar serie y correlativo
list($serie, $correlativo) = explode('-', $ticket_param);

// Buscar el comprobante en la base de datos
$boleta = new Boleta();
$conexion = Conectar::conexion();
Conectar::set_names();

try {
    $stmt = $conexion->prepare("
        SELECT b.*, 
               c.cli_nom as cliente_nombre, 
               c.cli_ape as cliente_apellido,
               c.cli_doc as cliente_documento
        FROM boleta b
        LEFT JOIN recepcion r ON b.rec_id = r.rec_id
        LEFT JOIN cliente c ON r.cli_id = c.cli_id
        WHERE b.bol_serie = ? AND b.bol_correlativo = ?
        LIMIT 1
    ");
    $stmt->execute([$serie, $correlativo]);
    $comprobante = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $comprobante = null;
}

if (!$comprobante) {
    // Ticket no encontrado
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Ticket no encontrado - Hotel Las Palmeras</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            .card {
                background: white;
                border-radius: 16px;
                padding: 40px;
                max-width: 400px;
                text-align: center;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            }
            .icon { font-size: 60px; margin-bottom: 20px; }
            h1 { color: #333; margin-bottom: 10px; font-size: 24px; }
            p { color: #666; margin-bottom: 20px; }
        </style>
    </head>
    <body>
        <div class="card">
            <div class="icon">❌</div>
            <h1>Comprobante no encontrado</h1>
            <p>No se encontró el comprobante <strong><?php echo htmlspecialchars($ticket_param); ?></strong></p>
            <p style="font-size: 12px; color: #999;">Verifique el número e intente nuevamente.</p>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Mostrar información del comprobante
$tipo_doc = $comprobante['bol_tipo'] == '01' ? 'FACTURA' : 'BOLETA';
$fecha = date('d/m/Y', strtotime($comprobante['bol_fecha_emision']));
$hora = date('H:i', strtotime($comprobante['bol_fecha_emision']));
$cliente = trim(($comprobante['bol_cliente_razon_social'] ?? '') ?: 
           ($comprobante['cliente_nombre'] ?? '') . ' ' . ($comprobante['cliente_apellido'] ?? ''));
$total = number_format($comprobante['bol_total'], 2);
$estado = $comprobante['bol_estado'] ?? 'PENDIENTE';
$hash = $comprobante['bol_hash'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $tipo_doc; ?> <?php echo $ticket_param; ?> - Hotel Las Palmeras</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #405189 0%, #0ab39c 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .card {
            background: white;
            border-radius: 16px;
            padding: 30px;
            max-width: 450px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 2px dashed #eee;
            margin-bottom: 20px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #405189;
            margin-bottom: 5px;
        }
        .doc-type {
            display: inline-block;
            background: <?php echo $comprobante['bol_tipo'] == '01' ? '#0ab39c' : '#405189'; ?>;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .doc-number {
            font-size: 28px;
            font-weight: bold;
            color: #333;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .info-label {
            color: #666;
            font-size: 14px;
        }
        .info-value {
            color: #333;
            font-weight: 500;
            font-size: 14px;
            text-align: right;
        }
        .total-row {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .total-label {
            font-size: 16px;
            color: #666;
        }
        .total-value {
            font-size: 28px;
            font-weight: bold;
            color: #0ab39c;
        }
        .status {
            text-align: center;
            margin-top: 20px;
        }
        .status-badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-aceptada { background: #d1fae5; color: #059669; }
        .status-pendiente { background: #fef3c7; color: #d97706; }
        .status-rechazada { background: #fee2e2; color: #dc2626; }
        .btn-download {
            display: block;
            width: 100%;
            padding: 15px;
            background: #405189;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 20px;
            text-decoration: none;
            text-align: center;
            transition: background 0.3s;
        }
        .btn-download:hover {
            background: #2d3a5c;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 11px;
            color: #999;
        }
        .hash {
            font-family: monospace;
            font-size: 10px;
            color: #999;
            word-break: break-all;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <div class="logo">🏨 Hotel Las Palmeras</div>
            <div class="doc-type"><?php echo $tipo_doc; ?> ELECTRÓNICA</div>
            <div class="doc-number"><?php echo $ticket_param; ?></div>
        </div>
        
        <div class="info-row">
            <span class="info-label">Cliente</span>
            <span class="info-value"><?php echo htmlspecialchars($cliente ?: 'Cliente General'); ?></span>
        </div>
        
        <div class="info-row">
            <span class="info-label">Documento</span>
            <span class="info-value"><?php echo htmlspecialchars($comprobante['bol_cliente_num_doc'] ?? '-'); ?></span>
        </div>
        
        <div class="info-row">
            <span class="info-label">Fecha de Emisión</span>
            <span class="info-value"><?php echo $fecha; ?> <?php echo $hora; ?></span>
        </div>
        
        <div class="info-row">
            <span class="info-label">Subtotal</span>
            <span class="info-value">S/ <?php echo number_format($comprobante['bol_subtotal'], 2); ?></span>
        </div>
        
        <div class="info-row">
            <span class="info-label">IGV (18%)</span>
            <span class="info-value">S/ <?php echo number_format($comprobante['bol_igv'], 2); ?></span>
        </div>
        
        <div class="total-row">
            <span class="total-label">TOTAL</span>
            <span class="total-value">S/ <?php echo $total; ?></span>
        </div>
        
        <div class="status">
            <span class="status-badge status-<?php echo strtolower($estado); ?>">
                ✓ <?php echo $estado; ?>
            </span>
        </div>
        
        <a href="../controller/boleta.php?op=generar_pdf&rec_id=<?php echo $comprobante['rec_id']; ?>&tipo=80mm" 
           class="btn-download" target="_blank">
            📄 Descargar Comprobante
        </a>
        
        <div class="footer">
            <p>Comprobante emitido electrónicamente</p>
            <p>Consulte en: sunat.gob.pe</p>
            <?php if ($hash) { ?>
            <div class="hash">Hash: <?php echo $hash; ?></div>
            <?php } ?>
        </div>
    </div>
</body>
</html>
