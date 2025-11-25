<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boleta Electrónica</title>
    <style>
        /* ================= BASE ================= */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica', Arial, sans-serif;
            margin: 0;
            padding: 0;
            width: 50mm;
        }

        .ticket {
            width: 100%;
            padding: 3mm;
            margin: 0;
        }

        /* ================= HEADER ================= */
        .header {
            text-align: center;
            margin-bottom: 2px;
            padding-bottom: 2px;
            border-bottom: 1px dashed #ccc;
        }

        .logo-section-ticket {
            text-align: center;
            margin-bottom: 1px;
        }

        .logo-img-ticket {
            width: 75px;
            height: 31px;
            object-fit: contain;
            display: block;
            margin: 0 auto 1px;
            padding: 1px;
        }

        .company-name {
            font-size: 9px;
            font-weight: bold;
            margin-bottom: 1px;
            text-transform: uppercase;
            color: #000;
        }

        .company-ruc {
            font-size: 8px;
            font-weight: bold;
            margin-bottom: 1px;
        }

        .company-details {
            font-size: 7px;
            line-height: 1.1;
            margin-bottom: 2px;
        }

        /* ================= DOCUMENT TITLE ================= */
        .document-title {
            font-size: 8px;
            font-weight: bold;
            text-align: center;
            margin: 3px 0;
            text-transform: uppercase;
            padding: 2px 0;
            border-top: 1px dashed #ccc;
            border-bottom: 1px dashed #ccc;
        }

        .document-number {
            font-size: 8px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 3px;
        }

        /* ================= CLIENT INFO ================= */
        .client-section {
            margin: 3px 0;
            font-size: 7px;
            padding: 2px 0;
            border-bottom: 1px dashed #ccc;
        }

        .client-name {
            font-weight: bold;
            font-size: 8px;
            text-align: center;
            margin-bottom: 1px;
        }

        .client-details {
            font-size: 7px;
            margin-bottom: 2px;
            text-align: left;
        }

        /* ================= ITEMS TABLE ================= */
        .items-header {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 1px 0;
            font-size: 7px;
            font-weight: bold;
            margin: 2px 0;
            display: table;
            width: 100%;
        }

        .items-header > div {
            display: table-cell;
            text-align: center;
            padding: 1px;
        }

        .header-desc { width: 75%; text-align: left; padding-left: 2px; }
        .header-precio { width: 25%; text-align: right; padding-right: 2px; }

        .items-section {
            margin: 2px 0;
            border-bottom: 1px solid #000;
            padding-bottom: 2px;
        }

        .item {
            margin-bottom: 1px;
            font-size: 7px;
            display: table;
            width: 100%;
        }

        .item > div {
            display: table-cell;
            text-align: center;
            padding: 1px;
        }

        .item-desc { width: 75%; text-align: left; padding-left: 2px; font-size: 6px; word-wrap: break-word; }
        .item-precio { width: 25%; text-align: right; padding-right: 2px; }

        /* ================= TOTALS ================= */
        .totals-section {
            margin: 2px 0;
            font-size: 7px;
            border-top: 1px solid #000;
            padding-top: 1px;
        }

        .total-line {
            display: block;
            width: 100%;
            margin-bottom: 1px;
            font-size: 7px;
            line-height: 1.2;
        }

        .total-line::after {
            content: "";
            display: table;
            clear: both;
        }

        .total-text {
            float: left;
            font-weight: bold;
        }

        .total-value {
            float: right;
            font-weight: bold;
        }

        .total-final {
            border-top: 1px solid #000;
            padding-top: 1px;
            margin-top: 1px;
            font-size: 7px;
        }

        .total-letras {
            font-size: 6px;
            font-weight: bold;
            margin: 2px 0;
            text-align: left;
            word-wrap: break-word;
        }

        /* ================= PAYMENT INFO ================= */
        .payment-info {
            font-size: 7px;
            margin: 2px 0;
            text-align: left;
            padding: 2px 0;
            border-top: 1px dashed #ccc;
            border-bottom: 1px dashed #ccc;
        }

        .payment-info div {
            margin-bottom: 1px;
        }

        /* ================= QR AND FOOTER ================= */
        .qr-section {
            text-align: center;
            margin: 3px 0;
            padding: 3px 0;
            border-bottom: 1px dashed #ccc;
        }

        .qr-code img {
            width: 60px;
            height: 60px;
            margin: 2px 0;
        }

        .footer-text {
            font-size: 7px;
            text-align: center;
            line-height: 1.1;
            margin: 1px 0;
        }

        .footer-url {
            font-size: 7px;
            text-align: center;
            font-weight: bold;
            margin: 1px 0;
        }

        .footer-auth {
            font-size: 6px;
            text-align: center;
            margin: 1px 0;
            word-wrap: break-word;
        }

        @page {
            margin: 0;
            size: 50mm auto;
        }
        
        @media print {
            body { width: 50mm; }
        }
    </style>
</head>
<body>
    <div class="ticket">
        <!-- HEADER -->
        <div class="header">
            <div class="logo-section-ticket">
                <?php if (isset($logo_url) && !empty($logo_url)): ?>
                    <img src="<?php echo $logo_url; ?>" alt="Logo" class="logo-img-ticket">
                <?php endif; ?>
            </div>
            <div class="company-name"><?php echo $empresa['razon_social']; ?></div>
            <div class="company-ruc">RUC: <?php echo $empresa['ruc']; ?></div>
            <div class="company-details">
                <?php echo $empresa['direccion']; ?><br>
                <?php if (isset($empresa['telefono'])): ?>
                    Tel: <?php echo $empresa['telefono']; ?><br>
                <?php endif; ?>
            </div>
        </div>

        <!-- DOCUMENT TITLE -->
        <div class="document-title">
            <?php echo $tipo_documento == '03' ? 'BOLETA ELECTRÓNICA' : 'FACTURA ELECTRÓNICA'; ?>
        </div>
        <div class="document-number">
            <?php echo $serie . '-' . $correlativo; ?>
        </div>

        <!-- CLIENT INFO -->
        <div class="client-section">
            <div class="client-name"><?php echo strtoupper($cliente['razon_social']); ?></div>
            <div class="client-details">
                <strong><?php echo $cliente['tipo_doc'] == '6' ? 'RUC' : 'DNI'; ?>:</strong> <?php echo $cliente['num_doc']; ?><br>
                <strong>Fecha:</strong> <?php echo $fecha_emision; ?>
            </div>
        </div>

        <!-- ITEMS HEADER -->
        <div class="items-header">
            <div class="header-desc">DESCRIPCIÓN</div>
            <div class="header-precio">TOTAL</div>
        </div>

        <!-- ITEMS -->
        <div class="items-section">
            <?php foreach ($detalles as $item): ?>
                <div class="item">
                    <div class="item-desc">
                        <?php echo $item['cantidad']; ?>x <?php echo $item['descripcion']; ?>
                    </div>
                    <div class="item-precio">S/ <?php echo number_format($item['cantidad'] * $item['precio_unitario'], 2); ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- TOTALS -->
        <div class="totals-section">
            <div class="total-line">
                <span class="total-text">SUBTOTAL:</span>
                <span class="total-value">S/ <?php echo number_format($totales['subtotal'], 2); ?></span>
            </div>
            <div class="total-line">
                <span class="total-text">IGV (18%):</span>
                <span class="total-value">S/ <?php echo number_format($totales['igv'], 2); ?></span>
            </div>
            <div class="total-line total-final">
                <span class="total-text">TOTAL:</span>
                <span class="total-value">S/ <?php echo number_format($totales['total'], 2); ?></span>
            </div>
            <div class="total-letras">
                SON: <?php echo $total_letras; ?>
            </div>
        </div>

        <!-- PAYMENT INFO -->
        <?php if (isset($forma_pago)): ?>
            <div class="payment-info">
                <div><strong>Pago:</strong> <?php echo $forma_pago; ?></div>
            </div>
        <?php endif; ?>

        <!-- QR CODE -->
        <?php if (isset($qr_data) && $qr_data): ?>
            <div class="qr-section">
                <div class="qr-code">
                    <img src="<?php echo $qr_data; ?>" alt="Código QR">
                </div>
            </div>
        <?php endif; ?>

        <!-- FOOTER -->
        <div class="footer-text">
            Consulte en: sunat.gob.pe
        </div>
        <?php if (isset($hash_cpe) && !empty($hash_cpe)): ?>
            <div class="footer-auth">
                Hash: <?php echo substr($hash_cpe, 0, 20); ?>...
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
