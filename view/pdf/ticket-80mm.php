<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Boleta Electrónica</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 8pt;
            width: 80mm;
            margin: 0;
            padding: 2mm;
            color: #000;
        }

        .header {
            text-align: center;
            padding-bottom: 3mm;
            border-bottom: 1px dashed #aaa;
            margin-bottom: 2mm;
        }

        .company-name {
            font-size: 10pt;
            font-weight: bold;
        }

        .company-ruc {
            font-size: 8pt;
            font-weight: bold;
            color: #0066cc;
        }

        .company-info {
            font-size: 7pt;
            color: #0066cc;
            line-height: 1.4;
        }

        .doc-title {
            text-align: center;
            font-weight: bold;
            font-size: 8pt;
            padding: 2mm 0;
            border-top: 1px dashed #aaa;
            border-bottom: 1px dashed #aaa;
            margin: 2mm 0;
        }

        .doc-number {
            text-align: center;
            font-weight: bold;
            font-size: 8pt;
            margin-bottom: 2mm;
        }

        .client-section {
            text-align: center;
            padding-bottom: 2mm;
            border-bottom: 1px dashed #aaa;
            margin-bottom: 2mm;
            font-size: 7pt;
        }

        .client-name {
            font-weight: bold;
            font-size: 8pt;
        }

        .items-header {
            display: table;
            width: 100%;
            border-top: 1px dashed #aaa;
            border-bottom: 1px dashed #aaa;
            padding: 1mm 0;
            font-size: 7pt;
            font-weight: bold;
        }

        .items-header span {
            display: table-cell;
        }

        .col-cant { width: 10%; }
        .col-um { width: 10%; text-align: center; }
        .col-cod { width: 15%; text-align: center; }
        .col-precio { width: 30%; text-align: right; }
        .col-total { width: 35%; text-align: right; }

        .descripcion-label {
            font-size: 7pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 1mm 0;
        }

        .item {
            margin-bottom: 2mm;
            font-size: 7pt;
        }

        .item-row {
            display: table;
            width: 100%;
        }

        .item-row span {
            display: table-cell;
        }

        .item-desc {
            font-size: 7pt;
        }

        .totals {
            border-top: 1px dashed #aaa;
            padding-top: 2mm;
            margin-top: 2mm;
            font-size: 7pt;
        }

        .total-row {
            display: table;
            width: 100%;
            margin-bottom: 1mm;
        }

        .total-row span {
            display: table-cell;
        }

        .total-label { width: 35%; }
        .total-dots { width: 30%; color: #aaa; }
        .total-value { width: 35%; text-align: right; }

        .total-final {
            border-top: 1px dashed #aaa;
            padding-top: 1mm;
            margin-top: 1mm;
        }

        .total-letras {
            font-size: 7pt;
            font-weight: bold;
            border-top: 1px dashed #aaa;
            padding-top: 2mm;
            margin-top: 2mm;
        }

        .payment {
            font-size: 7pt;
            border-top: 1px dashed #aaa;
            border-bottom: 1px dashed #aaa;
            padding: 2mm 0;
            margin: 2mm 0;
        }

        .footer {
            text-align: center;
            font-size: 7pt;
            margin-top: 2mm;
        }

        .footer-url {
            font-weight: bold;
        }

        @page { margin: 0; size: 80mm auto; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name"><?php echo $empresa['razon_social']; ?></div>
        <div class="company-ruc">RUC: <?php echo $empresa['ruc']; ?></div>
        <div class="company-info">
            <?php echo $empresa['direccion']; ?><br>
            <?php if (isset($empresa['telefono'])): ?>Tel: <?php echo $empresa['telefono']; ?><br><?php endif; ?>
            <?php if (isset($empresa['email'])): ?>Email: <?php echo $empresa['email']; ?><?php endif; ?>
        </div>
    </div>

    <div class="doc-title"><?php echo $tipo_documento == '03' ? 'BOLETA DE VENTA ELECTRÓNICA' : 'FACTURA ELECTRÓNICA'; ?></div>
    <div class="doc-number"><?php echo $serie . '-' . $correlativo; ?></div>

    <div class="client-section">
        <div class="client-name"><?php echo strtoupper($cliente['razon_social']); ?></div>
        <?php echo $cliente['tipo_doc'] == '6' ? 'RUC' : 'DNI'; ?>: <?php echo $cliente['num_doc']; ?><br>
        <?php if (isset($cliente['direccion']) && !empty($cliente['direccion'])): ?>Dirección: <?php echo $cliente['direccion']; ?><br><?php endif; ?>
        Fecha Emisión: <?php echo $fecha_emision; ?>
    </div>

    <div class="items-header">
        <span class="col-cant">Cant</span>
        <span class="col-um">U.M</span>
        <span class="col-cod">COD</span>
        <span class="col-precio">PRECIO</span>
        <span class="col-total">TOTAL</span>
    </div>
    <div class="descripcion-label">DESCRIPCIÓN</div>

    <?php foreach ($detalles as $item): ?>
    <div class="item">
        <div class="item-row">
            <span class="col-cant"><?php echo $item['cantidad']; ?></span>
            <span class="col-um"><?php echo isset($item['unidad']) ? $item['unidad'] : 'UND'; ?></span>
            <span class="col-cod"><?php echo isset($item['codigo']) ? $item['codigo'] : 'P001'; ?></span>
            <span class="col-precio"><?php echo number_format($item['precio_unitario'], 2); ?></span>
            <span class="col-total"><?php echo number_format($item['cantidad'] * $item['precio_unitario'], 2); ?></span>
        </div>
        <div class="item-desc"><?php echo $item['descripcion']; ?></div>
    </div>
    <?php endforeach; ?>

    <div class="totals">
        <div class="total-row">
            <span class="total-label">OP. GRAVADA</span>
            <span class="total-dots">........................</span>
            <span class="total-value">S/ <?php echo number_format($totales['subtotal'], 2); ?></span>
        </div>
        <div class="total-row">
            <span class="total-label">IGV (18%)</span>
            <span class="total-dots">........................</span>
            <span class="total-value">S/ <?php echo number_format($totales['igv'], 2); ?></span>
        </div>
        <div class="total-row total-final">
            <span class="total-label">TOTAL</span>
            <span class="total-dots">........................</span>
            <span class="total-value">S/ <?php echo number_format($totales['total'], 2); ?></span>
        </div>
    </div>

    <div class="total-letras">SON: <?php echo $total_letras; ?></div>

    <?php if (isset($forma_pago)): ?>
    <div class="payment">
        <strong>Forma de Pago:</strong> <?php echo $forma_pago; ?><br>
        <?php if (isset($metodo_pago)): ?><strong>Método de Pago:</strong> <?php echo $metodo_pago; ?><?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if (isset($encargado) && !empty($encargado)): ?>
    <div class="payment">
        <strong>Atendido por:</strong> <?php echo htmlspecialchars($encargado); ?>
    </div>
    <?php endif; ?>


    <div class="footer">
        Representación impresa de la <?php echo $tipo_documento == '03' ? 'Boleta' : 'Factura'; ?> Electrónica<br>
        Consulte su comprobante en:<br>
        <span class="footer-url">sunat.gob.pe</span><br>
        <?php if (isset($hash_cpe)): ?>Hash: <?php echo $hash_cpe; ?><?php endif; ?>
    </div>
</body>
</html>
