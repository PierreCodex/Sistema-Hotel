-- =====================================================
-- CREAR TABLAS PARA FACTURA ELECTRÓNICA
-- Sistema Hotel - Facturación Electrónica SUNAT
-- Fecha: 2025-12-06
-- =====================================================
-- INSTRUCCIONES:
-- 1. Abrir phpMyAdmin
-- 2. Seleccionar la base de datos del hotel
-- 3. Ir a la pestaña SQL
-- 4. Copiar y pegar este script COMPLETO
-- 5. Ejecutar
-- =====================================================

-- =====================================================
-- 1. CREAR TABLA FACTURA
-- =====================================================
CREATE TABLE IF NOT EXISTS factura (
    fac_id INT AUTO_INCREMENT PRIMARY KEY,
    rec_id INT NULL COMMENT 'ID de recepción (puede ser NULL si es venta directa)',
    
    -- Datos del comprobante
    fac_tipo VARCHAR(2) DEFAULT '01' COMMENT '01 = Factura',
    fac_serie VARCHAR(10) NOT NULL COMMENT 'Serie: F001, F002, etc.',
    fac_correlativo VARCHAR(20) NOT NULL COMMENT 'Número correlativo',
    fac_fecha_emision DATETIME NOT NULL,
    
    -- Datos del cliente (empresa)
    fac_cliente_tipo_doc VARCHAR(2) DEFAULT '6' COMMENT '6 = RUC',
    fac_cliente_ruc VARCHAR(11) NOT NULL COMMENT 'RUC del cliente',
    fac_cliente_razon_social VARCHAR(200) NOT NULL COMMENT 'Razón social de la empresa',
    fac_cliente_direccion VARCHAR(300) NULL COMMENT 'Dirección fiscal',
    fac_cliente_ubigeo VARCHAR(6) NULL COMMENT 'Código de ubigeo',
    fac_cliente_email VARCHAR(100) NULL COMMENT 'Email para envío electrónico',
    
    -- Montos
    fac_op_gravadas DECIMAL(10,2) DEFAULT 0 COMMENT 'Total operaciones gravadas',
    fac_op_exoneradas DECIMAL(10,2) DEFAULT 0 COMMENT 'Total operaciones exoneradas',
    fac_op_inafectas DECIMAL(10,2) DEFAULT 0 COMMENT 'Total operaciones inafectas',
    fac_subtotal DECIMAL(10,2) NOT NULL COMMENT 'Subtotal sin IGV',
    fac_igv DECIMAL(10,2) NOT NULL COMMENT 'IGV 18%',
    fac_total DECIMAL(10,2) NOT NULL COMMENT 'Total con IGV',
    
    -- Forma de pago
    fac_forma_pago VARCHAR(20) DEFAULT 'Contado' COMMENT 'Contado o Credito',
    fac_metodo_pago VARCHAR(50) NULL COMMENT 'Efectivo, Tarjeta, Transferencia, etc.',
    fac_cuotas INT DEFAULT 0 COMMENT 'Número de cuotas si es crédito',
    
    -- Estado y SUNAT
    fac_estado VARCHAR(20) DEFAULT 'PENDIENTE' COMMENT 'PENDIENTE, ACEPTADA, RECHAZADA, ANULADA',
    fac_hash VARCHAR(100) NULL COMMENT 'Hash del comprobante (DigestValue)',
    fac_xml LONGTEXT NULL COMMENT 'XML firmado',
    fac_cdr LONGTEXT NULL COMMENT 'CDR de SUNAT (base64)',
    fac_observaciones TEXT NULL COMMENT 'Observaciones o mensaje de SUNAT',
    
    -- Rutas de archivos
    fac_xml_ruta VARCHAR(500) NULL,
    fac_cdr_ruta VARCHAR(500) NULL,
    fac_pdf_ruta VARCHAR(500) NULL,
    
    -- Auditoría
    fac_usuario_registro INT NULL,
    fac_fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    fac_usuario_modificacion INT NULL,
    fac_fecha_modificacion DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    
    -- Índices
    INDEX idx_fac_rec_id (rec_id),
    INDEX idx_fac_serie_correlativo (fac_serie, fac_correlativo),
    INDEX idx_fac_cliente_ruc (fac_cliente_ruc),
    INDEX idx_fac_fecha_emision (fac_fecha_emision),
    INDEX idx_fac_estado (fac_estado),
    
    -- Restricción única para serie-correlativo
    UNIQUE KEY uk_fac_serie_correlativo (fac_serie, fac_correlativo),
    
    -- Foreign keys
    CONSTRAINT fk_fac_recepcion FOREIGN KEY (rec_id) REFERENCES recepcion(IdRecepcion) ON DELETE SET NULL,
    CONSTRAINT fk_fac_usuario FOREIGN KEY (fac_usuario_registro) REFERENCES usuario(IdUsuario) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
COMMENT='Facturas electrónicas emitidas a empresas (RUC)';


-- =====================================================
-- 2. CREAR TABLA FACTURA_DETALLE
-- =====================================================
CREATE TABLE IF NOT EXISTS factura_detalle (
    fac_det_id INT AUTO_INCREMENT PRIMARY KEY,
    fac_id INT NOT NULL COMMENT 'ID de la factura',
    
    -- Datos del item
    fac_det_orden INT NOT NULL COMMENT 'Orden del item',
    fac_det_codigo VARCHAR(20) NULL COMMENT 'Código del producto/servicio',
    fac_det_descripcion VARCHAR(500) NOT NULL COMMENT 'Descripción del item',
    fac_det_unidad VARCHAR(10) DEFAULT 'NIU' COMMENT 'Unidad de medida (NIU, ZZ, etc.)',
    
    -- Cantidades y precios
    fac_det_cantidad DECIMAL(10,2) NOT NULL DEFAULT 1,
    fac_det_precio_unitario DECIMAL(10,2) NOT NULL COMMENT 'Precio unitario sin IGV',
    fac_det_valor_unitario DECIMAL(10,2) NOT NULL COMMENT 'Valor unitario (incluye IGV)',
    fac_det_descuento DECIMAL(10,2) DEFAULT 0 COMMENT 'Descuento aplicado',
    
    -- Totales
    fac_det_subtotal DECIMAL(10,2) NOT NULL COMMENT 'Subtotal sin IGV',
    fac_det_igv DECIMAL(10,2) NOT NULL COMMENT 'IGV del item',
    fac_det_total DECIMAL(10,2) NOT NULL COMMENT 'Total con IGV',
    
    -- Tipo de afectación IGV
    fac_det_tipo_afectacion VARCHAR(2) DEFAULT '10' COMMENT '10=Gravado, 20=Exonerado, 30=Inafecto',
    
    -- Índices
    INDEX idx_fac_det_fac_id (fac_id),
    
    -- Foreign key
    CONSTRAINT fk_fac_det_factura FOREIGN KEY (fac_id) REFERENCES factura(fac_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
COMMENT='Detalle de items de las facturas';


-- =====================================================
-- 3. CREAR TABLA FACTURA_CUOTAS (para crédito)
-- =====================================================
CREATE TABLE IF NOT EXISTS factura_cuotas (
    fac_cuo_id INT AUTO_INCREMENT PRIMARY KEY,
    fac_id INT NOT NULL,
    
    fac_cuo_numero INT NOT NULL COMMENT 'Número de cuota',
    fac_cuo_monto DECIMAL(10,2) NOT NULL COMMENT 'Monto de la cuota',
    fac_cuo_fecha_vencimiento DATE NOT NULL COMMENT 'Fecha de vencimiento',
    fac_cuo_estado VARCHAR(20) DEFAULT 'PENDIENTE' COMMENT 'PENDIENTE, PAGADA',
    fac_cuo_fecha_pago DATE NULL,
    
    INDEX idx_fac_cuo_fac_id (fac_id),
    
    CONSTRAINT fk_fac_cuo_factura FOREIGN KEY (fac_id) REFERENCES factura(fac_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
COMMENT='Cuotas de facturas a crédito';


-- =====================================================
-- STORED PROCEDURES PARA FACTURA
-- =====================================================

DELIMITER //

-- =====================================================
-- SP: Obtener siguiente correlativo de factura
-- =====================================================
DROP PROCEDURE IF EXISTS SP_FAC_OBTENER_CORRELATIVO//
CREATE PROCEDURE SP_FAC_OBTENER_CORRELATIVO(
    IN p_serie VARCHAR(10)
)
BEGIN
    SELECT COALESCE(MAX(CAST(fac_correlativo AS UNSIGNED)), 0) + 1 AS siguiente 
    FROM factura 
    WHERE fac_serie = p_serie COLLATE utf8mb4_general_ci;
END//


-- =====================================================
-- SP: Insertar factura
-- =====================================================
DROP PROCEDURE IF EXISTS SP_FAC_INSERTAR//
CREATE PROCEDURE SP_FAC_INSERTAR(
    IN p_rec_id INT,
    IN p_serie VARCHAR(10),
    IN p_correlativo VARCHAR(20),
    IN p_fecha_emision DATETIME,
    IN p_cliente_ruc VARCHAR(11),
    IN p_cliente_razon_social VARCHAR(200),
    IN p_cliente_direccion VARCHAR(300),
    IN p_cliente_ubigeo VARCHAR(6),
    IN p_cliente_email VARCHAR(100),
    IN p_op_gravadas DECIMAL(10,2),
    IN p_subtotal DECIMAL(10,2),
    IN p_igv DECIMAL(10,2),
    IN p_total DECIMAL(10,2),
    IN p_forma_pago VARCHAR(20),
    IN p_metodo_pago VARCHAR(50),
    IN p_estado VARCHAR(20),
    IN p_hash VARCHAR(100),
    IN p_xml LONGTEXT,
    IN p_cdr LONGTEXT,
    IN p_observaciones TEXT,
    IN p_usuario_registro INT
)
BEGIN
    INSERT INTO factura (
        rec_id, fac_tipo, fac_serie, fac_correlativo, fac_fecha_emision,
        fac_cliente_tipo_doc, fac_cliente_ruc, fac_cliente_razon_social,
        fac_cliente_direccion, fac_cliente_ubigeo, fac_cliente_email,
        fac_op_gravadas, fac_subtotal, fac_igv, fac_total,
        fac_forma_pago, fac_metodo_pago, fac_estado, fac_hash,
        fac_xml, fac_cdr, fac_observaciones, fac_usuario_registro
    ) VALUES (
        p_rec_id, '01', p_serie, p_correlativo, p_fecha_emision,
        '6', p_cliente_ruc, p_cliente_razon_social,
        p_cliente_direccion, p_cliente_ubigeo, p_cliente_email,
        p_op_gravadas, p_subtotal, p_igv, p_total,
        p_forma_pago, p_metodo_pago, p_estado, p_hash,
        p_xml, p_cdr, p_observaciones, p_usuario_registro
    );
    
    SELECT LAST_INSERT_ID() AS fac_id;
END//


-- =====================================================
-- SP: Insertar detalle de factura
-- =====================================================
DROP PROCEDURE IF EXISTS SP_FAC_INSERTAR_DETALLE//
CREATE PROCEDURE SP_FAC_INSERTAR_DETALLE(
    IN p_fac_id INT,
    IN p_orden INT,
    IN p_codigo VARCHAR(20),
    IN p_descripcion VARCHAR(500),
    IN p_unidad VARCHAR(10),
    IN p_cantidad DECIMAL(10,2),
    IN p_precio_unitario DECIMAL(10,2),
    IN p_valor_unitario DECIMAL(10,2),
    IN p_subtotal DECIMAL(10,2),
    IN p_igv DECIMAL(10,2),
    IN p_total DECIMAL(10,2),
    IN p_tipo_afectacion VARCHAR(2)
)
BEGIN
    INSERT INTO factura_detalle (
        fac_id, fac_det_orden, fac_det_codigo, fac_det_descripcion,
        fac_det_unidad, fac_det_cantidad, fac_det_precio_unitario,
        fac_det_valor_unitario, fac_det_subtotal, fac_det_igv, 
        fac_det_total, fac_det_tipo_afectacion
    ) VALUES (
        p_fac_id, p_orden, p_codigo, p_descripcion,
        p_unidad, p_cantidad, p_precio_unitario,
        p_valor_unitario, p_subtotal, p_igv, 
        p_total, p_tipo_afectacion
    );
END//


-- =====================================================
-- SP: Obtener factura por recepción
-- =====================================================
DROP PROCEDURE IF EXISTS SP_FAC_OBTENER_POR_RECEPCION//
CREATE PROCEDURE SP_FAC_OBTENER_POR_RECEPCION(
    IN p_rec_id INT
)
BEGIN
    SELECT 
        f.*, 
        r.TotalPagado, 
        r.Adelanto, 
        r.IdCliente, 
        r.IdHabitacion,
        u.Nombre AS usuario_nombre, 
        u.Apellido AS usuario_apellido
    FROM factura f
    INNER JOIN recepcion r ON f.rec_id = r.IdRecepcion
    LEFT JOIN usuario u ON f.fac_usuario_registro = u.IdUsuario
    WHERE f.rec_id = p_rec_id
    ORDER BY f.fac_id DESC
    LIMIT 1;
END//


-- =====================================================
-- SP: Obtener detalles de factura
-- =====================================================
DROP PROCEDURE IF EXISTS SP_FAC_OBTENER_DETALLES//
CREATE PROCEDURE SP_FAC_OBTENER_DETALLES(
    IN p_fac_id INT
)
BEGIN
    SELECT * 
    FROM factura_detalle 
    WHERE fac_id = p_fac_id 
    ORDER BY fac_det_orden;
END//


-- =====================================================
-- SP: Actualizar rutas de archivos
-- =====================================================
DROP PROCEDURE IF EXISTS SP_FAC_ACTUALIZAR_RUTA_PDF//
CREATE PROCEDURE SP_FAC_ACTUALIZAR_RUTA_PDF(
    IN p_fac_id INT,
    IN p_ruta VARCHAR(500)
)
BEGIN
    UPDATE factura SET fac_pdf_ruta = p_ruta WHERE fac_id = p_fac_id;
END//

DROP PROCEDURE IF EXISTS SP_FAC_ACTUALIZAR_RUTA_XML//
CREATE PROCEDURE SP_FAC_ACTUALIZAR_RUTA_XML(
    IN p_fac_id INT,
    IN p_ruta VARCHAR(500)
)
BEGIN
    UPDATE factura SET fac_xml_ruta = p_ruta WHERE fac_id = p_fac_id;
END//

DROP PROCEDURE IF EXISTS SP_FAC_ACTUALIZAR_RUTA_CDR//
CREATE PROCEDURE SP_FAC_ACTUALIZAR_RUTA_CDR(
    IN p_fac_id INT,
    IN p_ruta VARCHAR(500)
)
BEGIN
    UPDATE factura SET fac_cdr_ruta = p_ruta WHERE fac_id = p_fac_id;
END//


-- =====================================================
-- SP: Insertar cuota de factura a crédito
-- =====================================================
DROP PROCEDURE IF EXISTS SP_FAC_INSERTAR_CUOTA//
CREATE PROCEDURE SP_FAC_INSERTAR_CUOTA(
    IN p_fac_id INT,
    IN p_numero INT,
    IN p_monto DECIMAL(10,2),
    IN p_fecha_vencimiento DATE
)
BEGIN
    INSERT INTO factura_cuotas (
        fac_id, fac_cuo_numero, fac_cuo_monto, fac_cuo_fecha_vencimiento
    ) VALUES (
        p_fac_id, p_numero, p_monto, p_fecha_vencimiento
    );
END//


DELIMITER ;

-- =====================================================
-- VERIFICACIÓN FINAL
-- =====================================================
SELECT 'Tablas creadas:' AS mensaje;
SELECT TABLE_NAME, TABLE_COMMENT 
FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME IN ('factura', 'factura_detalle', 'factura_cuotas');

SELECT 'Stored Procedures creados:' AS mensaje;
SELECT ROUTINE_NAME 
FROM INFORMATION_SCHEMA.ROUTINES 
WHERE ROUTINE_SCHEMA = DATABASE() 
AND ROUTINE_NAME LIKE 'SP_FAC_%';

SELECT '¡Script de FACTURA ejecutado correctamente!' AS resultado;
