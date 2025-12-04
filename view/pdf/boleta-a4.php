<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo ?? 'Comprobante'; ?></title>
    <style>
        /* ================= BASE ================= */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
            background: #fff;
        }

        .container {
            width: 18cm;
            margin: 20px auto;
            padding: 15px;
            box-sizing: border-box;
            border: 1px solid #000;
            border-radius: 10px;
        }

        /* ================= HEADER ================= */
        .header {
            display: table;
            width: 97%;
            border-bottom: 1px solid #000;
            padding-bottom: 15px;
            table-layout: fixed;
        }

        .header > div {
            display: table-cell;
            vertical-align: top;
            padding: 5px;
        }

        .logo-section {
            width: 25%;
            text-align: left;
        }

        .logo-img {
            width: 145px;
            height: 78px;
            object-fit: contain;
            vertical-align: top;
            margin-right: 5px;
        }

        .company-section {
            width: 50%;
            text-align: left;
            padding: 0 15px;
        }

        .company-name {
            margin: 0 0 5px 0;
            font-size: 16px;
            font-weight: bold;
            color: #000;
        }

        .company-details {
            line-height: 1.4;
            margin: 0;
            font-size: 11px;
            color: #333;
        }

        .document-section {
            width: 25%;
            text-align: center;
            vertical-align: top;
        }

        .factura-box {
            border: 1px solid #000;
            border-radius: 8px;
            padding: 12px;
            font-size: 12px;
            background-color: #fff;
            display: inline-block;
            min-width: 180px;
        }

        .factura-box p {
            margin: 2px 0;
            font-weight: bold;
        }

        .factura-box .ruc {
            font-size: 14px;
            color: #000;
        }

        .factura-box .tipo-doc {
            font-size: 13px;
            color: #d32f2f;
            margin: 8px 0;
        }

        .factura-box .numero {
            font-size: 12px;
        }

        /* ================= CLIENT INFO ================= */
        .client-info {
            margin-top: 15px;
            margin-bottom: 15px;
            display: table;
            width: 100%;
            font-size: 12px;
            table-layout: fixed;
        }

        .client-info > div {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 5px;
        }

        .client-info p {
            line-height: 1.6;
            margin: 0;
            padding: 5px 0;
        }

        .client-info strong {
            display: inline-block;
            min-width: 120px;
        }

        /* ================= TABLA PRINCIPAL ================= */
        .items-table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            font-size: 11px;
            border: 1px solid #000;
            border-radius: 8px;
            margin-bottom: 5px;
        }

        .items-table thead {
            background-color: #f0f0f0;
        }

        .items-table th,
        .items-table td {
            border-right: 1px solid #000;
            padding: 8px 5px;
            text-align: left;
        }

        .items-table thead th {
            border-bottom: 1px solid #000;
            font-weight: bold;
        }

        .items-table th:last-child,
        .items-table td:last-child {
            border-right: none;
        }

        .items-table tbody tr:last-child td {
            border-bottom: none;
        }

        .items-table thead th {
            border-top: none;
        }

        .items-table thead th:first-child {
            border-top-left-radius: 6px;
        }

        .items-table thead th:last-child {
            border-top-right-radius: 6px;
        }

        .items-table tbody tr:last-child td:first-child {
            border-bottom-left-radius: 6px;
        }

        .items-table tbody tr:last-child td:last-child {
            border-bottom-right-radius: 6px;
        }

        /* Columnas numéricas alineadas a la derecha */
        .items-table th.text-center,
        .items-table td.text-center {
            text-align: center;
        }

        .items-table th.text-right,
        .items-table td.text-right {
            text-align: right;
        }

        /* ================= SON EN LETRAS ================= */
        .en-letras {
            margin-top: 5px;
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border: 1px solid #000;
            border-radius: 8px;
        }

        .en-letras td {
            text-align: center;
            font-weight: bold;
            padding: 6px;
            font-size: 11px;
            border: none;
        }

        /* ================= TOTALES ================= */
        .totals-section {
            margin-top: 10px;
            display: table;
            width: 100%;
            table-layout: fixed;
        }

        .totals-section > div {
            display: table-cell;
            vertical-align: top;
        }

        .qr-section {
            width: 120px;
            text-align: center;
            padding-right: 10px;
        }

        .qr-section img {
            width: 100px;
            height: 100px;
            display: block;
            margin: 0 auto;
        }

        .info-footer {
            font-size: 10px;
            text-align: left;
            vertical-align: top;
            padding: 5px 10px;
            line-height: 1.4;
        }

        .totals-table {
            border-collapse: separate;
            border-spacing: 0;
            border: 1px solid #000;
            border-radius: 8px;
            float: right;
            min-width: 250px;
        }

        .totals-table td {
            padding: 4px 10px;
            font-size: 11px;
            vertical-align: top;
            line-height: 1.4;
            border-bottom: 1px solid #000;
        }

        .totals-table tr:last-child td {
            border-bottom: none;
        }

        .totals-table .label {
            text-align: right;
            font-weight: bold;
            background: #f9f9f9;
        }

        .totals-table .value {
            text-align: right;
            min-width: 80px;
        }

        .totals-table .resaltado {
            background: #f0f0f0;
            font-weight: bold;
            font-size: 12px;
        }

        /* Esquinas redondeadas para totales */
        .totals-table tr:first-child td:first-child {
            border-top-left-radius: 6px;
        }
        .totals-table tr:first-child td:last-child {
            border-top-right-radius: 6px;
        }
        .totals-table tr:last-child td:first-child {
            border-bottom-left-radius: 6px;
        }
        .totals-table tr:last-child td:last-child {
            border-bottom-right-radius: 6px;
        }

        /* ================= FOOTER EXTRA ================= */
        .footer {
            margin-top: 20px;
            padding: 15px;
            border: 1px solid #000;
            border-radius: 8px;
            background-color: #f9f9f9;
            font-size: 10px;
            line-height: 1.4;
            clear: both;
        }

        .footer p {
            margin: 3px 0;
        }

        /* ================= UTILIDADES ================= */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .fw-bold { font-weight: bold; }
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

        /* ================= PRINT ================= */
        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            .container {
                border: 1px solid #000;
                margin: 0;
                width: 100%;
            }

            @page {
                size: A4;
                margin: 10mm;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- ================= HEADER ================= -->
        <div class="header">
            <div class="logo-section">
                <img src="<?php echo $empresa['logo'] ?? '../../assets/images/logo-dark.png'; ?>" alt="Logo" class="logo-img">
            </div>
            
            <div class="company-section">
                <h1 class="company-name"><?php echo $empresa['razon_social'] ?? 'HOTEL LAS PALMERAS S.A.C.'; ?></h1>
                <p class="company-details">
                    <?php echo $empresa['direccion'] ?? 'Av. Principal 123, Lima - Perú'; ?><br>
                    Teléfono: <?php echo $empresa['telefono'] ?? '(01) 123-4567'; ?><br>
                    Email: <?php echo $empresa['email'] ?? 'info@hotellaspalmeras.com'; ?><br>
                    Web: <?php echo $empresa['web'] ?? 'www.hotellaspalmeras.com'; ?>
                </p>
            </div>
            
            <div class="document-section">
                <div class="factura-box">
                    <p class="ruc">RUC: <?php echo $empresa['ruc'] ?? '20123456789'; ?></p>
                    <p class="tipo-doc"><?php echo $comprobante['tipo_nombre'] ?? 'BOLETA DE VENTA'; ?></p>
                    <p class="tipo-doc">ELECTRÓNICA</p>
                    <p class="numero"><?php echo $comprobante['serie'] ?? 'B001'; ?> - <?php echo str_pad($comprobante['correlativo'] ?? '1', 8, '0', STR_PAD_LEFT); ?></p>
                </div>
            </div>
        </div>

        <!-- ================= DATOS DEL CLIENTE ================= -->
        <div class="client-info">
            <div>
                <p><strong><?php echo ($comprobante['tipo'] == '01') ? 'RUC:' : 'DNI:'; ?></strong> <?php echo $cliente['documento'] ?? '-'; ?></p>
                <p><strong>Cliente:</strong> <?php echo $cliente['nombre'] ?? 'CLIENTE GENERAL'; ?></p>
                <p><strong>Dirección:</strong> <?php echo $cliente['direccion'] ?? '-'; ?></p>
            </div>
            <div>
                <p><strong>Fecha Emisión:</strong> <?php echo $comprobante['fecha_emision'] ?? date('d/m/Y'); ?></p>
                <p><strong>Hora:</strong> <?php echo $comprobante['hora_emision'] ?? date('H:i:s'); ?></p>
                <p><strong>Moneda:</strong> <?php echo $comprobante['moneda'] ?? 'SOLES'; ?></p>
            </div>
        </div>

        <!-- ================= TABLA DE ITEMS ================= -->
        <table class="items-table">
            <thead>
                <tr>
                    <th class="text-center" style="width: 40px;">ITEM</th>
                    <th class="text-center" style="width: 80px;">CÓDIGO</th>
                    <th>DESCRIPCIÓN</th>
                    <th class="text-center" style="width: 50px;">UND</th>
                    <th class="text-center" style="width: 60px;">CANT.</th>
                    <th class="text-right" style="width: 80px;">P. UNIT.</th>
                    <th class="text-right" style="width: 80px;">IMPORTE</th>
                </tr>
            </thead>
            <tbody>
                <?php if(isset($items) && count($items) > 0) { ?>
                   <?php $i = 1; foreach($items as $item) { ?>
                    <tr>
                        <td class="text-center"><?php echo $i++; ?></td>
                        <td class="text-center"><?php echo $item['codigo'] ?? '-'; ?></td>
                        <td><?php echo $item['descripcion']; ?></td>
                        <td class="text-center"><?php echo $item['unidad'] ?? 'UND'; ?></td>
                        <td class="text-center"><?php echo number_format($item['cantidad'], 2); ?></td>
                        <td class="text-right">S/ <?php echo number_format($item['precio_unitario'], 2); ?></td>
                        <td class="text-right">S/ <?php echo number_format($item['importe'], 2); ?></td>
                    </tr>
                   <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="7" class="text-center" style="padding: 20px;">Sin items</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <!-- ================= MONTO EN LETRAS ================= -->
        <table class="en-letras">
            <tr>
                <td>SON: <?php echo $comprobante['monto_letras'] ?? 'CERO CON 00/100 SOLES'; ?></td>
            </tr>
        </table>

        <!-- ================= TOTALES Y QR ================= -->
        <div class="totals-section clearfix">
            <div class="qr-section">
                <?php if(isset($comprobante['qr_code'])) { ?>
                <img src="<?php echo $comprobante['qr_code']; ?>" alt="QR Code">
                <?php } else { ?>
                <div style="width: 100px; height: 100px; border: 1px solid #ccc; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                    <span style="font-size: 10px; color: #999;">QR</span>
                </div>
               <?php } ?>
            </div>
            
            <div class="info-footer">
                <p><strong>Hash:</strong> <?php echo $comprobante['hash'] ?? '-'; ?></p>
                <p style="margin-top: 10px; font-size: 9px;">
                    Representación impresa de la <?php echo $comprobante['tipo_nombre'] ?? 'Boleta de Venta'; ?> Electrónica.<br>
                    Autorizado mediante Resolución de Superintendencia N° 203-2015/SUNAT.<br>
                    Consulte su documento en: www.sunat.gob.pe
                </p>
            </div>
            
            <table class="totals-table">
                <tr>
                    <td class="label">OP. GRAVADAS:</td>
                    <td class="value">S/ <?php echo number_format($totales['gravadas'] ?? 0, 2); ?></td>
                </tr>
                <tr>
                    <td class="label">OP. EXONERADAS:</td>
                    <td class="value">S/ <?php echo number_format($totales['exoneradas'] ?? 0, 2); ?></td>
                </tr>
                <tr>
                    <td class="label">OP. INAFECTAS:</td>
                    <td class="value">S/ <?php echo number_format($totales['inafectas'] ?? 0, 2); ?></td>
                </tr>
                <tr>
                    <td class="label">DESCUENTO:</td>
                    <td class="value">S/ <?php echo number_format($totales['descuento'] ?? 0, 2); ?></td>
                </tr>
                <tr>
                    <td class="label">IGV (18%):</td>
                    <td class="value">S/ <?php echo number_format($totales['igv'] ?? 0, 2); ?></td>
                </tr>
                <tr class="resaltado">
                    <td class="label resaltado">TOTAL:</td>
                    <td class="value resaltado">S/ <?php echo number_format($totales['total'] ?? 0, 2); ?></td>
                </tr>
            </table>
        </div>

        <!-- ================= FOOTER / OBSERVACIONES ================= -->
<?php if(isset($comprobante['observaciones']) && !empty($comprobante['observaciones'])) { ?>        <div class="footer">
            <p><strong>Observaciones:</strong></p>
            <p><?php echo $comprobante['observaciones']; ?></p>
        </div>
        <?php } ?>
        <div class="footer" style="margin-top: 15px; text-align: center;">
            <p><strong>¡Gracias por su preferencia!</strong></p>
            <p>Este documento ha sido emitido conforme a las normas de SUNAT.</p>
            <p>Consulte su comprobante en: <?php echo $empresa['web'] ?? 'www.hotellaspalmeras.com'; ?></p>
        </div>
    </div>
</body>
</html>
