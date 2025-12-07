-- =============================================================================
-- Script para crear tablas y stored procedures de FACTURA
-- Ejecutar en MySQL/phpMyAdmin
-- =============================================================================

-- -----------------------------------------------------------------------------
-- TABLA: factura
-- Almacena las facturas electrónicas emitidas
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `factura` (
    `IdFactura` INT AUTO_INCREMENT PRIMARY KEY,
    `IdRecepcion` INT NOT NULL,
    `fac_serie` VARCHAR(10) NOT NULL DEFAULT 'F001',
    `fac_correlativo` VARCHAR(15) NOT NULL,
    `fac_fecha_emision` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `fac_ruc` VARCHAR(11) NOT NULL,
    `fac_razon_social` VARCHAR(255) NOT NULL,
    `fac_direccion` VARCHAR(500) DEFAULT NULL,
    `fac_ubigeo` VARCHAR(10) DEFAULT NULL,
    `fac_email` VARCHAR(100) DEFAULT NULL,
    `fac_op_gravadas` DECIMAL(12,2) NOT NULL DEFAULT 0,
    `fac_subtotal` DECIMAL(12,2) NOT NULL DEFAULT 0,
    `fac_igv` DECIMAL(12,2) NOT NULL DEFAULT 0,
    `fac_total` DECIMAL(12,2) NOT NULL DEFAULT 0,
    `fac_forma_pago` VARCHAR(20) DEFAULT 'Contado',
    `fac_metodo_pago` VARCHAR(50) DEFAULT 'EFECTIVO',
    `fac_estado` VARCHAR(20) NOT NULL DEFAULT 'PENDIENTE',
    `fac_hash` VARCHAR(100) DEFAULT NULL,
    `fac_xml` LONGTEXT DEFAULT NULL,
    `fac_cdr` LONGTEXT DEFAULT NULL,
    `fac_descripcion_cdr` TEXT DEFAULT NULL,
    `fac_usuario_id` INT DEFAULT NULL,
    `fac_fecha_registro` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_factura_recepcion` (`IdRecepcion`),
    INDEX `idx_factura_serie_correlativo` (`fac_serie`, `fac_correlativo`),
    INDEX `idx_factura_ruc` (`fac_ruc`),
    INDEX `idx_factura_fecha` (`fac_fecha_emision`),
    INDEX `idx_factura_estado` (`fac_estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- TABLA: factura_detalle
-- Almacena los detalles/ítems de cada factura
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `factura_detalle` (
    `IdFacturaDetalle` INT AUTO_INCREMENT PRIMARY KEY,
    `IdFactura` INT NOT NULL,
    `fd_orden` INT NOT NULL DEFAULT 1,
    `fd_codigo` VARCHAR(20) DEFAULT NULL,
    `fd_descripcion` VARCHAR(500) NOT NULL,
    `fd_unidad` VARCHAR(10) DEFAULT 'ZZ',
    `fd_cantidad` DECIMAL(12,4) NOT NULL DEFAULT 1,
    `fd_precio_unitario` DECIMAL(12,4) NOT NULL,
    `fd_precio_con_igv` DECIMAL(12,4) NOT NULL,
    `fd_subtotal` DECIMAL(12,2) NOT NULL,
    `fd_igv` DECIMAL(12,2) NOT NULL,
    `fd_total` DECIMAL(12,2) NOT NULL,
    `fd_tipo_afectacion` VARCHAR(5) DEFAULT '10',
    FOREIGN KEY (`IdFactura`) REFERENCES `factura`(`IdFactura`) ON DELETE CASCADE,
    INDEX `idx_fd_factura` (`IdFactura`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- TABLA: factura_cuota
-- Almacena las cuotas para facturas a crédito
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `factura_cuota` (
    `IdFacturaCuota` INT AUTO_INCREMENT PRIMARY KEY,
    `IdFactura` INT NOT NULL,
    `fc_numero` INT NOT NULL,
    `fc_monto` DECIMAL(12,2) NOT NULL,
    `fc_fecha_vencimiento` DATE NOT NULL,
    `fc_estado` VARCHAR(20) DEFAULT 'PENDIENTE',
    FOREIGN KEY (`IdFactura`) REFERENCES `factura`(`IdFactura`) ON DELETE CASCADE,
    INDEX `idx_fc_factura` (`IdFactura`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- SP: SP_FAC_OBTENER_CORRELATIVO
-- Obtiene el siguiente correlativo disponible para una serie
-- -----------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS `SP_FAC_OBTENER_CORRELATIVO`;

DELIMITER //
CREATE PROCEDURE `SP_FAC_OBTENER_CORRELATIVO`(
    IN p_serie VARCHAR(10)
)
BEGIN
    DECLARE v_ultimo INT DEFAULT 0;
    
    SELECT IFNULL(MAX(CAST(fac_correlativo AS UNSIGNED)), 0) INTO v_ultimo
    FROM factura 
    WHERE fac_serie = p_serie;
    
    SELECT (v_ultimo + 1) AS siguiente;
END //
DELIMITER ;

-- -----------------------------------------------------------------------------
-- SP: SP_FAC_INSERTAR
-- Inserta una nueva factura y devuelve el ID
-- -----------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS `SP_FAC_INSERTAR`;

DELIMITER //
CREATE PROCEDURE `SP_FAC_INSERTAR`(
    IN p_rec_id INT,
    IN p_serie VARCHAR(10),
    IN p_correlativo VARCHAR(15),
    IN p_fecha_emision DATETIME,
    IN p_ruc VARCHAR(11),
    IN p_razon_social VARCHAR(255),
    IN p_direccion VARCHAR(500),
    IN p_ubigeo VARCHAR(10),
    IN p_email VARCHAR(100),
    IN p_op_gravadas DECIMAL(12,2),
    IN p_subtotal DECIMAL(12,2),
    IN p_igv DECIMAL(12,2),
    IN p_total DECIMAL(12,2),
    IN p_forma_pago VARCHAR(20),
    IN p_metodo_pago VARCHAR(50),
    IN p_estado VARCHAR(20),
    IN p_hash VARCHAR(100),
    IN p_xml LONGTEXT,
    IN p_cdr LONGTEXT,
    IN p_descripcion_cdr TEXT,
    IN p_usuario_id INT
)
BEGIN
    INSERT INTO factura (
        IdRecepcion,
        fac_serie,
        fac_correlativo,
        fac_fecha_emision,
        fac_ruc,
        fac_razon_social,
        fac_direccion,
        fac_ubigeo,
        fac_email,
        fac_op_gravadas,
        fac_subtotal,
        fac_igv,
        fac_total,
        fac_forma_pago,
        fac_metodo_pago,
        fac_estado,
        fac_hash,
        fac_xml,
        fac_cdr,
        fac_descripcion_cdr,
        fac_usuario_id
    ) VALUES (
        p_rec_id,
        p_serie,
        p_correlativo,
        p_fecha_emision,
        p_ruc,
        p_razon_social,
        p_direccion,
        p_ubigeo,
        p_email,
        p_op_gravadas,
        p_subtotal,
        p_igv,
        p_total,
        p_forma_pago,
        p_metodo_pago,
        p_estado,
        p_hash,
        p_xml,
        p_cdr,
        p_descripcion_cdr,
        p_usuario_id
    );
    
    SELECT LAST_INSERT_ID() AS fac_id;
END //
DELIMITER ;

-- -----------------------------------------------------------------------------
-- SP: SP_FAC_INSERTAR_DETALLE
-- Inserta un detalle de factura
-- -----------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS `SP_FAC_INSERTAR_DETALLE`;

DELIMITER //
CREATE PROCEDURE `SP_FAC_INSERTAR_DETALLE`(
    IN p_fac_id INT,
    IN p_orden INT,
    IN p_codigo VARCHAR(20),
    IN p_descripcion VARCHAR(500),
    IN p_unidad VARCHAR(10),
    IN p_cantidad DECIMAL(12,4),
    IN p_precio_unitario DECIMAL(12,4),
    IN p_precio_con_igv DECIMAL(12,4),
    IN p_subtotal DECIMAL(12,2),
    IN p_igv DECIMAL(12,2),
    IN p_total DECIMAL(12,2),
    IN p_tipo_afectacion VARCHAR(5)
)
BEGIN
    INSERT INTO factura_detalle (
        IdFactura,
        fd_orden,
        fd_codigo,
        fd_descripcion,
        fd_unidad,
        fd_cantidad,
        fd_precio_unitario,
        fd_precio_con_igv,
        fd_subtotal,
        fd_igv,
        fd_total,
        fd_tipo_afectacion
    ) VALUES (
        p_fac_id,
        p_orden,
        p_codigo,
        p_descripcion,
        p_unidad,
        p_cantidad,
        p_precio_unitario,
        p_precio_con_igv,
        p_subtotal,
        p_igv,
        p_total,
        p_tipo_afectacion
    );
END //
DELIMITER ;

-- -----------------------------------------------------------------------------
-- SP: SP_FAC_INSERTAR_CUOTA
-- Inserta una cuota para factura a crédito
-- -----------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS `SP_FAC_INSERTAR_CUOTA`;

DELIMITER //
CREATE PROCEDURE `SP_FAC_INSERTAR_CUOTA`(
    IN p_fac_id INT,
    IN p_numero INT,
    IN p_monto DECIMAL(12,2),
    IN p_fecha_vencimiento DATE
)
BEGIN
    INSERT INTO factura_cuota (
        IdFactura,
        fc_numero,
        fc_monto,
        fc_fecha_vencimiento
    ) VALUES (
        p_fac_id,
        p_numero,
        p_monto,
        p_fecha_vencimiento
    );
END //
DELIMITER ;

-- -----------------------------------------------------------------------------
-- SP: SP_FAC_OBTENER_X_RECEPCION
-- Obtiene factura por ID de recepción
-- -----------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS `SP_FAC_OBTENER_X_RECEPCION`;

DELIMITER //
CREATE PROCEDURE `SP_FAC_OBTENER_X_RECEPCION`(
    IN p_rec_id INT
)
BEGIN
    SELECT * FROM factura 
    WHERE IdRecepcion = p_rec_id 
    ORDER BY IdFactura DESC 
    LIMIT 1;
END //
DELIMITER ;

-- -----------------------------------------------------------------------------
-- SP: SP_FAC_OBTENER_X_ID
-- Obtiene factura por ID
-- -----------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS `SP_FAC_OBTENER_X_ID`;

DELIMITER //
CREATE PROCEDURE `SP_FAC_OBTENER_X_ID`(
    IN p_fac_id INT
)
BEGIN
    SELECT * FROM factura WHERE IdFactura = p_fac_id;
END //
DELIMITER ;

-- -----------------------------------------------------------------------------
-- SP: SP_FAC_OBTENER_DETALLES
-- Obtiene detalles de una factura
-- -----------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS `SP_FAC_OBTENER_DETALLES`;

DELIMITER //
CREATE PROCEDURE `SP_FAC_OBTENER_DETALLES`(
    IN p_fac_id INT
)
BEGIN
    SELECT * FROM factura_detalle WHERE IdFactura = p_fac_id ORDER BY fd_orden;
END //
DELIMITER ;

-- =============================================================================
-- FIN DEL SCRIPT
-- =============================================================================
