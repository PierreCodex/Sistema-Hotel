-- =====================================================
-- STORED PROCEDURES PARA MODELO BOLETA
-- Sistema Hotel - Facturación Electrónica SUNAT
-- Fecha: 2025-12-01
-- Collation: utf8mb4_general_ci
-- =====================================================

-- Establecer collation correcto para la sesión
SET NAMES utf8mb4 COLLATE utf8mb4_general_ci;

DELIMITER //

-- =====================================================
-- 1. OBTENER SIGUIENTE CORRELATIVO
-- =====================================================
DROP PROCEDURE IF EXISTS SP_BOL_OBTENER_CORRELATIVO//
CREATE PROCEDURE SP_BOL_OBTENER_CORRELATIVO(
    IN p_serie VARCHAR(10) CHARSET utf8mb4
)
BEGIN
    SELECT COALESCE(MAX(bol_correlativo), 0) + 1 AS siguiente 
    FROM boleta 
    WHERE bol_serie = p_serie COLLATE utf8mb4_general_ci;
END//

-- =====================================================
-- 2. INSERTAR BOLETA/FACTURA (con hash)
-- =====================================================
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
        bol_metodo_pago, bol_xml, bol_cdr, bol_observaciones, bol_usuario_registro, bol_hash
    ) VALUES (
        p_rec_id, p_tipo, p_serie, p_correlativo,
        p_fecha_emision, p_cliente_tipo_doc, p_cliente_num_doc,
        p_cliente_razon_social, p_cliente_direccion,
        p_subtotal, p_igv, p_total, p_estado,
        p_metodo_pago, p_xml, p_cdr, p_observaciones, p_usuario_registro, p_hash
    );
    
    SELECT LAST_INSERT_ID() AS bol_id;
END//

-- =====================================================
-- 3. INSERTAR DETALLE DE BOLETA
-- =====================================================
DROP PROCEDURE IF EXISTS SP_BOL_INSERTAR_DETALLE//
CREATE PROCEDURE SP_BOL_INSERTAR_DETALLE(
    IN p_bol_id INT,
    IN p_orden INT,
    IN p_codigo VARCHAR(20),
    IN p_descripcion VARCHAR(500),
    IN p_unidad VARCHAR(10),
    IN p_cantidad DECIMAL(10,2),
    IN p_precio_unitario DECIMAL(10,2),
    IN p_subtotal DECIMAL(10,2),
    IN p_igv DECIMAL(10,2),
    IN p_total DECIMAL(10,2)
)
BEGIN
    INSERT INTO boleta_detalle (
        bol_id, bol_det_orden, bol_det_codigo, 
        bol_det_descripcion, bol_det_unidad, bol_det_cantidad, 
        bol_det_precio_unitario, bol_det_subtotal, bol_det_igv, bol_det_total
    ) VALUES (
        p_bol_id, p_orden, p_codigo,
        p_descripcion, p_unidad, p_cantidad,
        p_precio_unitario, p_subtotal, p_igv, p_total
    );
END//

-- =====================================================
-- 4. OBTENER BOLETA POR RECEPCION (para PDF)
-- =====================================================
DROP PROCEDURE IF EXISTS SP_BOL_OBTENER_POR_RECEPCION//
CREATE PROCEDURE SP_BOL_OBTENER_POR_RECEPCION(
    IN p_rec_id INT
)
BEGIN
    SELECT 
        b.*, 
        r.TotalPagado, 
        r.Adelanto, 
        r.IdCliente, 
        r.IdHabitacion,
        u.Nombre AS usuario_nombre, 
        u.Apellido AS usuario_apellido
    FROM boleta b
    INNER JOIN recepcion r ON b.rec_id = r.IdRecepcion
    LEFT JOIN usuario u ON b.bol_usuario_registro = u.IdUsuario
    WHERE b.rec_id = p_rec_id
    ORDER BY b.bol_id DESC
    LIMIT 1;
END//

-- =====================================================
-- 5. OBTENER CLIENTE POR ID
-- =====================================================
DROP PROCEDURE IF EXISTS SP_BOL_OBTENER_CLIENTE//
CREATE PROCEDURE SP_BOL_OBTENER_CLIENTE(
    IN p_cliente_id INT
)
BEGIN
    SELECT 
        IdCliente, 
        TipoDocumento, 
        Documento, 
        Nombre, 
        Apellido, 
        Direccion 
    FROM cliente 
    WHERE IdCliente = p_cliente_id;
END//

-- =====================================================
-- 6. OBTENER DETALLES DE BOLETA
-- =====================================================
DROP PROCEDURE IF EXISTS SP_BOL_OBTENER_DETALLES//
CREATE PROCEDURE SP_BOL_OBTENER_DETALLES(
    IN p_bol_id INT
)
BEGIN
    SELECT * 
    FROM boleta_detalle 
    WHERE bol_id = p_bol_id 
    ORDER BY bol_det_orden;
END//

-- =====================================================
-- 7. OBTENER HABITACION POR ID
-- =====================================================
DROP PROCEDURE IF EXISTS SP_BOL_OBTENER_HABITACION//
CREATE PROCEDURE SP_BOL_OBTENER_HABITACION(
    IN p_habitacion_id INT
)
BEGIN
    SELECT Numero 
    FROM habitacion 
    WHERE IdHabitacion = p_habitacion_id;
END//

-- =====================================================
-- 8. ACTUALIZAR RUTA PDF
-- =====================================================
DROP PROCEDURE IF EXISTS SP_BOL_ACTUALIZAR_RUTA_PDF//
CREATE PROCEDURE SP_BOL_ACTUALIZAR_RUTA_PDF(
    IN p_bol_id INT,
    IN p_ruta VARCHAR(500)
)
BEGIN
    UPDATE boleta 
    SET bol_pdf_ruta = p_ruta 
    WHERE bol_id = p_bol_id;
END//

-- =====================================================
-- 9. ACTUALIZAR RUTA XML
-- =====================================================
DROP PROCEDURE IF EXISTS SP_BOL_ACTUALIZAR_RUTA_XML//
CREATE PROCEDURE SP_BOL_ACTUALIZAR_RUTA_XML(
    IN p_bol_id INT,
    IN p_ruta VARCHAR(500)
)
BEGIN
    UPDATE boleta 
    SET bol_xml_ruta = p_ruta 
    WHERE bol_id = p_bol_id;
END//

-- =====================================================
-- 10. ACTUALIZAR RUTA CDR
-- =====================================================
DROP PROCEDURE IF EXISTS SP_BOL_ACTUALIZAR_RUTA_CDR//
CREATE PROCEDURE SP_BOL_ACTUALIZAR_RUTA_CDR(
    IN p_bol_id INT,
    IN p_ruta VARCHAR(500)
)
BEGIN
    UPDATE boleta 
    SET bol_cdr_ruta = p_ruta 
    WHERE bol_id = p_bol_id;
END//

-- =====================================================
-- 11. OBTENER XML PARA DESCARGA
-- =====================================================
DROP PROCEDURE IF EXISTS SP_BOL_OBTENER_XML//
CREATE PROCEDURE SP_BOL_OBTENER_XML(
    IN p_bol_id INT
)
BEGIN
    SELECT 
        bol_serie, 
        bol_correlativo, 
        bol_xml, 
        bol_xml_ruta 
    FROM boleta 
    WHERE bol_id = p_bol_id;
END//

-- =====================================================
-- 12. OBTENER CDR PARA DESCARGA
-- =====================================================
DROP PROCEDURE IF EXISTS SP_BOL_OBTENER_CDR//
CREATE PROCEDURE SP_BOL_OBTENER_CDR(
    IN p_bol_id INT
)
BEGIN
    SELECT 
        bol_serie, 
        bol_correlativo, 
        bol_cdr, 
        bol_cdr_ruta 
    FROM boleta 
    WHERE bol_id = p_bol_id;
END//

-- =====================================================
-- 13. OBTENER PDF PARA DESCARGA
-- =====================================================
DROP PROCEDURE IF EXISTS SP_BOL_OBTENER_PDF//
CREATE PROCEDURE SP_BOL_OBTENER_PDF(
    IN p_bol_id INT
)
BEGIN
    SELECT 
        bol_serie, 
        bol_correlativo, 
        bol_pdf_ruta 
    FROM boleta 
    WHERE bol_id = p_bol_id;
END//

-- =====================================================
-- 14. LISTAR COMPROBANTES CON FILTROS
-- =====================================================
DROP PROCEDURE IF EXISTS SP_BOL_LISTAR//
CREATE PROCEDURE SP_BOL_LISTAR(
    IN p_fecha_inicio DATE,
    IN p_fecha_fin DATE,
    IN p_tipo VARCHAR(2) CHARSET utf8mb4,
    IN p_estado VARCHAR(20) CHARSET utf8mb4
)
BEGIN
    SELECT 
        b.bol_id,
        b.bol_tipo,
        b.bol_serie,
        b.bol_correlativo,
        b.bol_fecha_emision,
        b.bol_cliente_razon_social,
        b.bol_cliente_num_doc,
        b.bol_subtotal,
        b.bol_igv,
        b.bol_total,
        b.bol_estado,
        b.bol_metodo_pago
    FROM boleta b
    WHERE DATE(b.bol_fecha_emision) BETWEEN p_fecha_inicio AND p_fecha_fin
      AND (p_tipo = '' OR p_tipo IS NULL OR b.bol_tipo = p_tipo COLLATE utf8mb4_general_ci)
      AND (p_estado = '' OR p_estado IS NULL OR b.bol_estado = p_estado COLLATE utf8mb4_general_ci)
    ORDER BY b.bol_fecha_emision DESC;
END//

-- =====================================================
-- 15. OBTENER RESUMEN DE COMPROBANTES
-- =====================================================
DROP PROCEDURE IF EXISTS SP_BOL_RESUMEN//
CREATE PROCEDURE SP_BOL_RESUMEN(
    IN p_fecha_inicio DATE,
    IN p_fecha_fin DATE,
    IN p_tipo VARCHAR(2) CHARSET utf8mb4,
    IN p_estado VARCHAR(20) CHARSET utf8mb4
)
BEGIN
    SELECT 
        COUNT(*) AS total_emitidos,
        SUM(CASE WHEN bol_tipo = '03' THEN 1 ELSE 0 END) AS total_boletas,
        COALESCE(SUM(CASE WHEN bol_tipo = '03' THEN bol_total ELSE 0 END), 0) AS monto_boletas,
        SUM(CASE WHEN bol_tipo = '01' THEN 1 ELSE 0 END) AS total_facturas,
        COALESCE(SUM(CASE WHEN bol_tipo = '01' THEN bol_total ELSE 0 END), 0) AS monto_facturas,
        COALESCE(SUM(bol_total), 0) AS total_facturado
    FROM boleta b
    WHERE DATE(b.bol_fecha_emision) BETWEEN p_fecha_inicio AND p_fecha_fin
      AND (p_tipo = '' OR p_tipo IS NULL OR b.bol_tipo = p_tipo COLLATE utf8mb4_general_ci)
      AND (p_estado = '' OR p_estado IS NULL OR b.bol_estado = p_estado COLLATE utf8mb4_general_ci);
END//

-- =====================================================
-- 16. OBTENER COMPROBANTE POR ID
-- =====================================================
DROP PROCEDURE IF EXISTS SP_BOL_OBTENER_POR_ID//
CREATE PROCEDURE SP_BOL_OBTENER_POR_ID(
    IN p_bol_id INT
)
BEGIN
    SELECT * 
    FROM boleta 
    WHERE bol_id = p_bol_id;
END//

-- =====================================================
-- 17. OBTENER COMPROBANTE CON RECEPCION
-- =====================================================
DROP PROCEDURE IF EXISTS SP_BOL_OBTENER_CON_RECEPCION//
CREATE PROCEDURE SP_BOL_OBTENER_CON_RECEPCION(
    IN p_bol_id INT
)
BEGIN
    SELECT 
        b.*, 
        r.IdRecepcion 
    FROM boleta b 
    LEFT JOIN recepcion r ON b.rec_id = r.IdRecepcion 
    WHERE b.bol_id = p_bol_id;
END//

DELIMITER ;

-- =====================================================
-- VERIFICAR CREACIÓN DE PROCEDURES
-- =====================================================
SELECT 
    ROUTINE_NAME AS 'Stored Procedure',
    CREATED AS 'Fecha Creación'
FROM INFORMATION_SCHEMA.ROUTINES 
WHERE ROUTINE_SCHEMA = 'db-hotel' COLLATE utf8mb4_general_ci
  AND ROUTINE_NAME LIKE 'SP_BOL_%'
ORDER BY ROUTINE_NAME;
