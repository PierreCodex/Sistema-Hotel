CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_COMPROBANTE_LISTAR` (IN `p_fecha_inicio` DATE, IN `p_fecha_fin` DATE, IN `p_tipo` VARCHAR(2), IN `p_estado` VARCHAR(20))   BEGIN
    -- Combinar boletas y facturas en una sola consulta
    SELECT * FROM (
        -- BOLETAS
        SELECT 
            b.bol_id AS comp_id,
            b.rec_id,
            b.bol_tipo COLLATE utf8mb4_general_ci AS comp_tipo,
            b.bol_serie COLLATE utf8mb4_general_ci AS comp_serie,
            b.bol_correlativo COLLATE utf8mb4_general_ci AS comp_correlativo,
            b.bol_fecha_emision AS comp_fecha_emision,
            b.bol_cliente_tipo_doc COLLATE utf8mb4_general_ci AS comp_cliente_tipo_doc,
            b.bol_cliente_num_doc COLLATE utf8mb4_general_ci AS comp_cliente_num_doc,
            b.bol_cliente_razon_social COLLATE utf8mb4_general_ci AS comp_cliente_razon_social,
            b.bol_subtotal AS comp_subtotal,
            b.bol_igv AS comp_igv,
            b.bol_total AS comp_total,
            b.bol_estado COLLATE utf8mb4_general_ci AS comp_estado,
            b.bol_metodo_pago COLLATE utf8mb4_general_ci AS comp_metodo_pago,
            b.bol_hash COLLATE utf8mb4_general_ci AS comp_hash,
            b.bol_xml_ruta COLLATE utf8mb4_general_ci AS comp_xml_ruta,
            b.bol_cdr_ruta COLLATE utf8mb4_general_ci AS comp_cdr_ruta,
            b.bol_pdf_ruta COLLATE utf8mb4_general_ci AS comp_pdf_ruta,
            CONCAT(IFNULL(u.Nombre, ''), ' ', IFNULL(u.Apellido, '')) COLLATE utf8mb4_general_ci AS usuario_nombre,
            'boleta' AS origen_tabla
        FROM boleta b
        LEFT JOIN usuario u ON b.bol_usuario_registro = u.IdUsuario
        WHERE DATE(b.bol_fecha_emision) BETWEEN p_fecha_inicio AND p_fecha_fin
        AND (p_tipo = '' OR p_tipo IS NULL OR b.bol_tipo COLLATE utf8mb4_general_ci = p_tipo COLLATE utf8mb4_general_ci)
        AND (p_estado = '' OR p_estado IS NULL OR b.bol_estado COLLATE utf8mb4_general_ci = p_estado COLLATE utf8mb4_general_ci)
        
        UNION ALL
        
        -- FACTURAS
        SELECT 
            f.fac_id AS comp_id,
            f.rec_id,
            f.fac_tipo COLLATE utf8mb4_general_ci AS comp_tipo,
            f.fac_serie COLLATE utf8mb4_general_ci AS comp_serie,
            f.fac_correlativo COLLATE utf8mb4_general_ci AS comp_correlativo,
            f.fac_fecha_emision AS comp_fecha_emision,
            f.fac_cliente_tipo_doc COLLATE utf8mb4_general_ci AS comp_cliente_tipo_doc,
            f.fac_cliente_ruc COLLATE utf8mb4_general_ci AS comp_cliente_num_doc,
            f.fac_cliente_razon_social COLLATE utf8mb4_general_ci AS comp_cliente_razon_social,
            f.fac_subtotal AS comp_subtotal,
            f.fac_igv AS comp_igv,
            f.fac_total AS comp_total,
            f.fac_estado COLLATE utf8mb4_general_ci AS comp_estado,
            f.fac_metodo_pago COLLATE utf8mb4_general_ci AS comp_metodo_pago,
            f.fac_hash COLLATE utf8mb4_general_ci AS comp_hash,
            f.fac_xml_ruta COLLATE utf8mb4_general_ci AS comp_xml_ruta,
            f.fac_cdr_ruta COLLATE utf8mb4_general_ci AS comp_cdr_ruta,
            f.fac_pdf_ruta COLLATE utf8mb4_general_ci AS comp_pdf_ruta,
            CONCAT(IFNULL(u.Nombre, ''), ' ', IFNULL(u.Apellido, '')) COLLATE utf8mb4_general_ci AS usuario_nombre,
            'factura' AS origen_tabla
        FROM factura f
        LEFT JOIN usuario u ON f.fac_usuario_registro = u.IdUsuario
        WHERE DATE(f.fac_fecha_emision) BETWEEN p_fecha_inicio AND p_fecha_fin
        AND (p_tipo = '' OR p_tipo IS NULL OR f.fac_tipo COLLATE utf8mb4_general_ci = p_tipo COLLATE utf8mb4_general_ci)
        AND (p_estado = '' OR p_estado IS NULL OR f.fac_estado COLLATE utf8mb4_general_ci = p_estado COLLATE utf8mb4_general_ci)
    ) AS comprobantes
    ORDER BY comp_fecha_emision DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_COMPROBANTE_OBTENER_DETALLES` (IN `p_comp_id` INT, IN `p_origen` VARCHAR(10))   BEGIN
    IF p_origen = 'boleta' THEN
        SELECT 
            bol_det_orden AS det_orden,
            bol_det_codigo AS det_codigo,
            bol_det_descripcion AS det_descripcion,
            bol_det_unidad AS det_unidad,
            bol_det_cantidad AS det_cantidad,
            bol_det_precio_unitario AS det_precio_unitario,
            bol_det_subtotal AS det_subtotal,
            bol_det_igv AS det_igv,
            bol_det_total AS det_total
        FROM boleta_detalle
        WHERE bol_id = p_comp_id
        ORDER BY bol_det_orden;
    ELSE
        SELECT 
            fac_det_orden AS det_orden,
            fac_det_codigo AS det_codigo,
            fac_det_descripcion AS det_descripcion,
            fac_det_unidad AS det_unidad,
            fac_det_cantidad AS det_cantidad,
            fac_det_precio_unitario AS det_precio_unitario,
            fac_det_subtotal AS det_subtotal,
            fac_det_igv AS det_igv,
            fac_det_total AS det_total
        FROM factura_detalle
        WHERE fac_id = p_comp_id
        ORDER BY fac_det_orden;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_COMPROBANTE_OBTENER_POR_ID` (IN `p_comp_id` INT, IN `p_origen` VARCHAR(10))   BEGIN
    IF p_origen = 'boleta' THEN
        SELECT 
            b.bol_id AS comp_id,
            b.rec_id,
            b.bol_tipo AS comp_tipo,
            b.bol_serie AS comp_serie,
            b.bol_correlativo AS comp_correlativo,
            b.bol_fecha_emision AS comp_fecha_emision,
            b.bol_cliente_tipo_doc AS comp_cliente_tipo_doc,
            b.bol_cliente_num_doc AS comp_cliente_num_doc,
            b.bol_cliente_razon_social AS comp_cliente_razon_social,
            b.bol_cliente_direccion AS comp_cliente_direccion,
            b.bol_subtotal AS comp_subtotal,
            b.bol_igv AS comp_igv,
            b.bol_total AS comp_total,
            b.bol_estado AS comp_estado,
            b.bol_metodo_pago AS comp_metodo_pago,
            b.bol_hash AS comp_hash,
            b.bol_xml AS comp_xml,
            b.bol_cdr AS comp_cdr,
            b.bol_xml_ruta AS comp_xml_ruta,
            b.bol_cdr_ruta AS comp_cdr_ruta,
            b.bol_pdf_ruta AS comp_pdf_ruta,
            b.bol_observaciones AS comp_descripcion_cdr,
            CONCAT(IFNULL(u.Nombre, ''), ' ', IFNULL(u.Apellido, '')) AS usuario_nombre,
            'boleta' AS origen_tabla
        FROM boleta b
        LEFT JOIN usuario u ON b.bol_usuario_registro = u.IdUsuario
        WHERE b.bol_id = p_comp_id;
    ELSE
        SELECT 
            f.fac_id AS comp_id,
            f.rec_id,
            f.fac_tipo AS comp_tipo,
            f.fac_serie AS comp_serie,
            f.fac_correlativo AS comp_correlativo,
            f.fac_fecha_emision AS comp_fecha_emision,
            f.fac_cliente_tipo_doc AS comp_cliente_tipo_doc,
            f.fac_cliente_ruc AS comp_cliente_num_doc,
            f.fac_cliente_razon_social AS comp_cliente_razon_social,
            f.fac_cliente_direccion AS comp_cliente_direccion,
            f.fac_subtotal AS comp_subtotal,
            f.fac_igv AS comp_igv,
            f.fac_total AS comp_total,
            f.fac_estado AS comp_estado,
            f.fac_metodo_pago AS comp_metodo_pago,
            f.fac_hash AS comp_hash,
            f.fac_xml AS comp_xml,
            f.fac_cdr AS comp_cdr,
            f.fac_xml_ruta AS comp_xml_ruta,
            f.fac_cdr_ruta AS comp_cdr_ruta,
            f.fac_pdf_ruta AS comp_pdf_ruta,
            f.fac_observaciones AS comp_descripcion_cdr,
            CONCAT(IFNULL(u.Nombre, ''), ' ', IFNULL(u.Apellido, '')) AS usuario_nombre,
            'factura' AS origen_tabla
        FROM factura f
        LEFT JOIN usuario u ON f.fac_usuario_registro = u.IdUsuario
        WHERE f.fac_id = p_comp_id;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_COMPROBANTE_RESUMEN` (IN `p_fecha_inicio` DATE, IN `p_fecha_fin` DATE, IN `p_tipo` VARCHAR(2), IN `p_estado` VARCHAR(20))   BEGIN
    SELECT 
        -- Total emitidos
        (
            SELECT COUNT(*) FROM boleta 
            WHERE DATE(bol_fecha_emision) BETWEEN p_fecha_inicio AND p_fecha_fin
            AND (p_tipo = '' OR p_tipo IS NULL OR bol_tipo COLLATE utf8mb4_general_ci = p_tipo COLLATE utf8mb4_general_ci)
            AND (p_estado = '' OR p_estado IS NULL OR bol_estado COLLATE utf8mb4_general_ci = p_estado COLLATE utf8mb4_general_ci)
        ) + (
            SELECT COUNT(*) FROM factura 
            WHERE DATE(fac_fecha_emision) BETWEEN p_fecha_inicio AND p_fecha_fin
            AND (p_tipo = '' OR p_tipo IS NULL OR fac_tipo COLLATE utf8mb4_general_ci = p_tipo COLLATE utf8mb4_general_ci)
            AND (p_estado = '' OR p_estado IS NULL OR fac_estado COLLATE utf8mb4_general_ci = p_estado COLLATE utf8mb4_general_ci)
        ) AS total_emitidos,
        
        -- Total boletas
        (
            SELECT COUNT(*) FROM boleta 
            WHERE DATE(bol_fecha_emision) BETWEEN p_fecha_inicio AND p_fecha_fin
            AND bol_tipo = '03'
            AND (p_estado = '' OR p_estado IS NULL OR bol_estado COLLATE utf8mb4_general_ci = p_estado COLLATE utf8mb4_general_ci)
        ) AS total_boletas,
        
        -- Monto boletas
        (
            SELECT IFNULL(SUM(bol_total), 0) FROM boleta 
            WHERE DATE(bol_fecha_emision) BETWEEN p_fecha_inicio AND p_fecha_fin
            AND bol_tipo = '03'
            AND (p_estado = '' OR p_estado IS NULL OR bol_estado COLLATE utf8mb4_general_ci = p_estado COLLATE utf8mb4_general_ci)
        ) AS monto_boletas,
        
        -- Total facturas
        (
            SELECT COUNT(*) FROM factura 
            WHERE DATE(fac_fecha_emision) BETWEEN p_fecha_inicio AND p_fecha_fin
            AND (p_estado = '' OR p_estado IS NULL OR fac_estado COLLATE utf8mb4_general_ci = p_estado COLLATE utf8mb4_general_ci)
        ) AS total_facturas,
        
        -- Monto facturas
        (
            SELECT IFNULL(SUM(fac_total), 0) FROM factura 
            WHERE DATE(fac_fecha_emision) BETWEEN p_fecha_inicio AND p_fecha_fin
            AND (p_estado = '' OR p_estado IS NULL OR fac_estado COLLATE utf8mb4_general_ci = p_estado COLLATE utf8mb4_general_ci)
        ) AS monto_facturas,
        
        -- Total facturado (boletas + facturas)
        (
            SELECT IFNULL(SUM(bol_total), 0) FROM boleta 
            WHERE DATE(bol_fecha_emision) BETWEEN p_fecha_inicio AND p_fecha_fin
            AND (p_tipo = '' OR p_tipo IS NULL OR bol_tipo COLLATE utf8mb4_general_ci = p_tipo COLLATE utf8mb4_general_ci)
            AND (p_estado = '' OR p_estado IS NULL OR bol_estado COLLATE utf8mb4_general_ci = p_estado COLLATE utf8mb4_general_ci)
        ) + (
            SELECT IFNULL(SUM(fac_total), 0) FROM factura 
            WHERE DATE(fac_fecha_emision) BETWEEN p_fecha_inicio AND p_fecha_fin
            AND (p_tipo = '' OR p_tipo IS NULL OR fac_tipo COLLATE utf8mb4_general_ci = p_tipo COLLATE utf8mb4_general_ci)
            AND (p_estado = '' OR p_estado IS NULL OR fac_estado COLLATE utf8mb4_general_ci = p_estado COLLATE utf8mb4_general_ci)
        ) AS total_facturado;
END$$