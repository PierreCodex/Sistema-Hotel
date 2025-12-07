<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura Electrónica <?php echo $serie . '-' . $correlativo; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            color: #333;
            line-height: 1.4;
        }
        
        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 15px;
        }
        
        /* Header */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .header-left {
            display: table-cell;
            width: 60%;
            vertical-align: top;
        }
        
        .header-right {
            display: table-cell;
            width: 40%;
            vertical-align: top;
        }
        
        .company-name {
            font-size: 16pt;
            font-weight: bold;
            color: #1a5276;
            margin-bottom: 5px;
        }
        
        .company-info {
            font-size: 9pt;
            color: #555;
            line-height: 1.5;
        }
        
        .doc-box {
            border: 2px solid #1a5276;
            padding: 15px;
            text-align: center;
        }
        
        .doc-title {
            font-size: 14pt;
            font-weight: bold;
            color: #1a5276;
            margin-bottom: 5px;
        }
        
        .doc-ruc {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .doc-number {
            font-size: 12pt;
            font-weight: bold;
            color: #c0392b;
        }
        
        /* Cliente */
        .client-section {
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 15px;
            background: #f9f9f9;
        }
        
        .client-title {
            font-weight: bold;
            margin-bottom: 10px;
            color: #1a5276;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        
        .client-row {
            display: table;
            width: 100%;
            margin-bottom: 5px;
        }
        
        .client-label {
            display: table-cell;
            width: 120px;
            font-weight: bold;
            color: #555;
        }
        
        .client-value {
            display: table-cell;
        }
        
        /* Tabla de items */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        .items-table th {
            background: #1a5276;
            color: white;
            padding: 8px 5px;
            text-align: center;
            font-size: 9pt;
        }
        
        .items-table td {
            border: 1px solid #ddd;
            padding: 8px 5px;
            font-size: 9pt;
        }
        
        .items-table .col-item {
            width: 8%;
            text-align: center;
        }
        
        .items-table .col-codigo {
            width: 12%;
            text-align: center;
        }
        
        .items-table .col-descripcion {
            width: 40%;
        }
        
        .items-table .col-cantidad {
            width: 10%;
            text-align: center;
        }
        
        .items-table .col-precio {
            width: 15%;
            text-align: right;
        }
        
        .items-table .col-total {
            width: 15%;
            text-align: right;
        }
        
        .items-table tbody tr:nth-child(even) {
            background: #f9f9f9;
        }
        
        /* Totales */
        .totals-section {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        
        .totals-left {
            display: table-cell;
            width: 60%;
            vertical-align: top;
        }
        
        .totals-right {
            display: table-cell;
            width: 40%;
            vertical-align: top;
        }
        
        .total-letras {
            border: 1px solid #ddd;
            padding: 10px;
            font-size: 9pt;
            background: #f9f9f9;
        }
        
        .total-letras-title {
            font-weight: bold;
            color: #555;
            margin-bottom: 5px;
        }
        
        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .totals-table td {
            padding: 5px 10px;
            border: 1px solid #ddd;
        }
        
        .totals-table .label {
            background: #f0f0f0;
            font-weight: bold;
            text-align: right;
        }
        
        .totals-table .value {
            text-align: right;
        }
        
        .totals-table .total-row td {
            background: #1a5276;
            color: white;
            font-weight: bold;
            font-size: 12pt;
        }
        
        /* Info adicional */
        .info-section {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        
        .info-left {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 10px;
        }
        
        .info-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-left: 10px;
        }
        
        .info-box {
            border: 1px solid #ddd;
            padding: 10px;
            font-size: 9pt;
        }
        
        .info-title {
            font-weight: bold;
            color: #1a5276;
            margin-bottom: 5px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 3px;
        }
        
        .info-row {
            margin-bottom: 3px;
        }
        
        .info-label {
            font-weight: bold;
            color: #555;
        }
        
        /* QR y Hash */
        .qr-section {
            display: table;
            width: 100%;
            border: 1px solid #ddd;
            padding: 10px;
        }
        
        .qr-left {
            display: table-cell;
            width: 120px;
            vertical-align: middle;
            text-align: center;
        }
        
        .qr-right {
            display: table-cell;
            vertical-align: middle;
            padding-left: 15px;
        }
        
        .qr-code {
            width: 100px;
            height: 100px;
            border: 1px solid #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f9f9f9;
        }
        
        .hash-text {
            font-size: 8pt;
            color: #666;
            word-break: break-all;
            margin-top: 5px;
        }
        
        .sunat-text {
            font-size: 8pt;
            color: #888;
            margin-top: 10px;
        }
        
        /* Footer */
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 8pt;
            color: #888;
        }
        
        .representacion {
            font-style: italic;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <div class="company-name"><?php echo $empresa['razon_social']; ?></div>
                <div class="company-info">
                    <strong>Dirección:</strong> <?php echo $empresa['direccion']; ?><br>
                    <strong>Teléfono:</strong> <?php echo $empresa['telefono']; ?><br>
                    <strong>Email:</strong> <?php echo $empresa['email']; ?>
                </div>
            </div>
            <div class="header-right">
                <div class="doc-box">
                    <div class="doc-ruc">RUC: <?php echo $empresa['ruc']; ?></div>
                    <div class="doc-title">FACTURA ELECTRÓNICA</div>
                    <div class="doc-number"><?php echo $serie . '-' . $correlativo; ?></div>
                </div>
            </div>
        </div>
        
        <!-- Datos del cliente -->
        <div class="client-section">
            <div class="client-title">DATOS DEL CLIENTE</div>
            <div class="client-row">
                <span class="client-label">RUC:</span>
                <span class="client-value"><?php echo $cliente['num_doc']; ?></span>
            </div>
            <div class="client-row">
                <span class="client-label">Razón Social:</span>
                <span class="client-value"><?php echo $cliente['razon_social']; ?></span>
            </div>
            <div class="client-row">
                <span class="client-label">Dirección:</span>
                <span class="client-value"><?php echo $cliente['direccion'] ?: '-'; ?></span>
            </div>
            <div class="client-row">
                <span class="client-label">Fecha Emisión:</span>
                <span class="client-value"><?php echo $fecha_emision; ?></span>
            </div>
        </div>
        
        <!-- Tabla de items -->
        <table class="items-table">
            <thead>
                <tr>
                    <th class="col-item">ITEM</th>
                    <th class="col-codigo">CÓDIGO</th>
                    <th class="col-descripcion">DESCRIPCIÓN</th>
                    <th class="col-cantidad">CANT.</th>
                    <th class="col-precio">P. UNIT.</th>
                    <th class="col-total">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $item_num = 1;
                foreach ($detalles as $item): 
                    $subtotal_item = $item['cantidad'] * $item['precio_unitario'];
                ?>
                <tr>
                    <td class="col-item"><?php echo $item_num; ?></td>
                    <td class="col-codigo"><?php echo $item['codigo'] ?? 'P00' . $item_num; ?></td>
                    <td class="col-descripcion"><?php echo $item['descripcion']; ?></td>
                    <td class="col-cantidad"><?php echo number_format($item['cantidad'], 2); ?></td>
                    <td class="col-precio">S/ <?php echo number_format($item['precio_unitario'], 2); ?></td>
                    <td class="col-total">S/ <?php echo number_format($subtotal_item, 2); ?></td>
                </tr>
                <?php 
                    $item_num++;
                endforeach; 
                ?>
            </tbody>
        </table>
        
        <!-- Totales -->
        <div class="totals-section">
            <div class="totals-left">
                <div class="total-letras">
                    <div class="total-letras-title">SON:</div>
                    <?php echo $total_letras; ?>
                </div>
            </div>
            <div class="totals-right">
                <table class="totals-table">
                    <tr>
                        <td class="label">OP. GRAVADAS</td>
                        <td class="value">S/ <?php echo number_format($totales['subtotal'], 2); ?></td>
                    </tr>
                    <tr>
                        <td class="label">IGV (18%)</td>
                        <td class="value">S/ <?php echo number_format($totales['igv'], 2); ?></td>
                    </tr>
                    <tr class="total-row">
                        <td class="label">TOTAL</td>
                        <td class="value">S/ <?php echo number_format($totales['total'], 2); ?></td>
                    </tr>
                </table>
            </div>
        </div>
        
        <!-- Info de pago -->
        <div class="info-section">
            <div class="info-left">
                <div class="info-box">
                    <div class="info-title">INFORMACIÓN DE PAGO</div>
                    <div class="info-row">
                        <span class="info-label">Forma de Pago:</span> <?php echo $forma_pago; ?>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Método:</span> <?php echo $metodo_pago; ?>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Moneda:</span> SOLES (PEN)
                    </div>
                </div>
            </div>
            <div class="info-right">
                <div class="info-box">
                    <div class="info-title">DATOS ADICIONALES</div>
                    <div class="info-row">
                        <span class="info-label">Atendido por:</span> <?php echo $encargado ?: 'No registrado'; ?>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Tipo Operación:</span> Venta Interna
                    </div>
                </div>
            </div>
        </div>
        
        <!-- QR y Hash -->
        <div class="qr-section">
            <div class="qr-left">
                <div class="qr-code">
                    [QR]
                </div>
            </div>
            <div class="qr-right">
                <div class="info-title">REPRESENTACIÓN IMPRESA</div>
                <div class="hash-text">
                    <strong>Hash:</strong> <?php echo $hash_cpe ?: 'No disponible'; ?>
                </div>
                <div class="sunat-text">
                    Autorizado mediante Resolución de Superintendencia Nº 000-2018/SUNAT<br>
                    Consulte este documento en: <strong>https://www.sunat.gob.pe</strong>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <div class="representacion">
                Representación impresa de la Factura Electrónica
            </div>
            <div>
                Emitido desde Sistema de Facturación Electrónica - Hotel Las Palmeras
            </div>
        </div>
    </div>
</body>
</html>
