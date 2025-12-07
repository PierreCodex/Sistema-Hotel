-- =====================================================
-- Script para agregar campo bol_hash a la tabla boleta
-- y actualizar el stored procedure SP_BOL_INSERTAR
-- Sistema Hotel - Facturación Electrónica SUNAT
-- Fecha: 2025
-- =====================================================
-- INSTRUCCIONES:
-- 1. Abrir phpMyAdmin
-- 2. Seleccionar la base de datos del hotel
-- 3. Ir a la pestaña SQL
-- 4. Copiar y pegar este script COMPLETO
-- 5. Ejecutar
-- =====================================================

-- 1. Agregar columna bol_hash si no existe
-- (Si MySQL < 8.0, usar este comando alternativo)
-- Verificar primero si la columna existe
SET @dbname = DATABASE();
SET @tablename = 'boleta';
SET @columnname = 'bol_hash';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname
    AND TABLE_NAME = @tablename
    AND COLUMN_NAME = @columnname
  ) > 0,
  'SELECT "La columna bol_hash ya existe"',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' VARCHAR(100) NULL COMMENT "Hash del comprobante (DigestValue del XML firmado)" AFTER bol_observaciones')
));

PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 2. Actualizar el stored procedure SP_BOL_INSERTAR
DELIMITER //

DROP PROCEDURE IF EXISTS SP_BOL_INSERTAR//
CREATE PROCEDURE SP_BOL_INSERTAR(
    IN p_rec_id INT,
    IN p_tipo VARCHAR(2),
    IN p_serie VARCHAR(10),
    IN p_correlativo VARCHAR(20),
    IN p_fecha_emision DATETIME,
    IN p_cliente_tipo_doc VARCHAR(2),
    IN p_cliente_num_doc VARCHAR(20),
    IN p_cliente_razon_social VARCHAR(200),
    IN p_cliente_direccion VARCHAR(300),
    IN p_subtotal DECIMAL(10,2),
    IN p_igv DECIMAL(10,2),
    IN p_total DECIMAL(10,2),
    IN p_estado VARCHAR(20),
    IN p_metodo_pago VARCHAR(50),
    IN p_xml LONGTEXT,
    IN p_cdr LONGTEXT,
    IN p_observaciones TEXT,
    IN p_usuario_registro INT,
    IN p_hash VARCHAR(100)
)
BEGIN
    INSERT INTO boleta (
        rec_id, bol_tipo, bol_serie, bol_correlativo, 
        bol_fecha_emision, bol_cliente_tipo_doc, bol_cliente_num_doc, 
        bol_cliente_razon_social, bol_cliente_direccion,
        bol_subtotal, bol_igv, bol_total, bol_estado, 
        bol_metodo_pago, bol_xml, bol_cdr, bol_observaciones, 
        bol_usuario_registro, bol_hash
    ) VALUES (
        p_rec_id, p_tipo, p_serie, p_correlativo,
        p_fecha_emision, p_cliente_tipo_doc, p_cliente_num_doc,
        p_cliente_razon_social, p_cliente_direccion,
        p_subtotal, p_igv, p_total, p_estado,
        p_metodo_pago, p_xml, p_cdr, p_observaciones, 
        p_usuario_registro, p_hash
    );
    
    SELECT LAST_INSERT_ID() AS bol_id;
END//

DELIMITER ;

-- 3. Verificar que el cambio se aplicó
SELECT 'Verificando columna bol_hash:' AS mensaje;
SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_COMMENT 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'boleta' 
AND COLUMN_NAME = 'bol_hash';

SELECT 'Verificando stored procedure:' AS mensaje;
SELECT ROUTINE_NAME, ROUTINE_TYPE 
FROM INFORMATION_SCHEMA.ROUTINES 
WHERE ROUTINE_SCHEMA = DATABASE() 
AND ROUTINE_NAME = 'SP_BOL_INSERTAR';

SELECT '¡Script ejecutado correctamente!' AS resultado;
