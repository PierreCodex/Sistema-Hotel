-- Tabla para almacenar boletas electrónicas
CREATE TABLE IF NOT EXISTS `boleta` (
  `bol_id` INT(11) NOT NULL AUTO_INCREMENT,
  `rec_id` INT(11) NOT NULL COMMENT 'ID de la recepción',
  `bol_tipo` VARCHAR(2) NOT NULL DEFAULT '03' COMMENT '03=Boleta',
  `bol_serie` VARCHAR(10) NOT NULL COMMENT 'Serie del comprobante (B001)',
  `bol_correlativo` VARCHAR(20) NOT NULL COMMENT 'Número correlativo',
  `bol_fecha_emision` DATETIME NOT NULL,
  `bol_fecha_vencimiento` DATE NULL,
  
  -- Datos del cliente
  `bol_cliente_tipo_doc` VARCHAR(2) NULL DEFAULT '1' COMMENT '1=DNI',
  `bol_cliente_num_doc` VARCHAR(20) NULL,
  `bol_cliente_razon_social` VARCHAR(200) NULL,
  `bol_cliente_direccion` VARCHAR(200) NULL,
  
  -- Montos
  `bol_subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `bol_igv` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `bol_total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  
  -- Estado y control
  `bol_estado` VARCHAR(20) NOT NULL DEFAULT 'EMITIDA' COMMENT 'EMITIDA, ANULADA, RECHAZADA, ACEPTADA',
  `bol_metodo_pago` VARCHAR(50) NULL COMMENT 'EFECTIVO, TARJETA, TRANSFERENCIA',
  `bol_observaciones` TEXT NULL COMMENT 'Descripción y observaciones SUNAT',
  
  -- Datos SUNAT
  `bol_hash` VARCHAR(255) NULL COMMENT 'Hash del comprobante',
  `bol_xml` LONGTEXT NULL COMMENT 'XML firmado',
  `bol_cdr` LONGTEXT NULL COMMENT 'CDR (Constancia de Recepción) en base64',
  `bol_pdf_ruta` VARCHAR(255) NULL COMMENT 'Ruta del PDF generado',
  
  -- Auditoría
  `bol_fecha_registro` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `bol_usuario_registro` INT(11) NULL,
  `bol_fecha_modificacion` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`bol_id`),
  UNIQUE KEY `uk_serie_correlativo` (`bol_serie`, `bol_correlativo`),
  KEY `idx_recepcion` (`rec_id`),
  KEY `idx_fecha_emision` (`bol_fecha_emision`),
  KEY `idx_estado` (`bol_estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla para el detalle de la boleta (opcional, si quieres guardar los ítems)
CREATE TABLE IF NOT EXISTS `boleta_detalle` (
  `bol_det_id` INT(11) NOT NULL AUTO_INCREMENT,
  `bol_id` INT(11) NOT NULL,
  `bol_det_orden` INT(11) NOT NULL DEFAULT 1,
  `bol_det_codigo` VARCHAR(50) NULL,
  `bol_det_descripcion` VARCHAR(200) NOT NULL,
  `bol_det_unidad` VARCHAR(10) NOT NULL DEFAULT 'NIU',
  `bol_det_cantidad` DECIMAL(10,2) NOT NULL,
  `bol_det_precio_unitario` DECIMAL(10,2) NOT NULL,
  `bol_det_subtotal` DECIMAL(10,2) NOT NULL,
  `bol_det_igv` DECIMAL(10,2) NOT NULL,
  `bol_det_total` DECIMAL(10,2) NOT NULL,
  
  PRIMARY KEY (`bol_det_id`),
  KEY `idx_boleta` (`bol_id`),
  CONSTRAINT `fk_boleta_detalle` FOREIGN KEY (`bol_id`) REFERENCES `boleta` (`bol_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Script para migrar datos de factura a boleta (si ya tienes datos)
-- RENAME TABLE factura TO boleta;
-- O ejecutar: DROP TABLE IF EXISTS factura; DROP TABLE IF EXISTS factura_detalle;
