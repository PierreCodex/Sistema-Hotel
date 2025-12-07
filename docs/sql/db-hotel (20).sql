-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 07-12-2025 a las 00:55:04
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `db-hotel`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_BOL_ACTUALIZAR_RUTA_CDR` (IN `p_bol_id` INT, IN `p_ruta` VARCHAR(500))   BEGIN
    UPDATE boleta 
    SET bol_cdr_ruta = p_ruta 
    WHERE bol_id = p_bol_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_BOL_ACTUALIZAR_RUTA_PDF` (IN `p_bol_id` INT, IN `p_ruta` VARCHAR(500))   BEGIN
    UPDATE boleta 
    SET bol_pdf_ruta = p_ruta 
    WHERE bol_id = p_bol_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_BOL_ACTUALIZAR_RUTA_XML` (IN `p_bol_id` INT, IN `p_ruta` VARCHAR(500))   BEGIN
    UPDATE boleta 
    SET bol_xml_ruta = p_ruta 
    WHERE bol_id = p_bol_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_BOL_INSERTAR` (IN `p_rec_id` INT, IN `p_tipo` VARCHAR(2), IN `p_serie` VARCHAR(10), IN `p_correlativo` VARCHAR(20), IN `p_fecha_emision` DATETIME, IN `p_cliente_tipo_doc` VARCHAR(2), IN `p_cliente_num_doc` VARCHAR(20), IN `p_cliente_razon_social` VARCHAR(200), IN `p_cliente_direccion` VARCHAR(300), IN `p_subtotal` DECIMAL(10,2), IN `p_igv` DECIMAL(10,2), IN `p_total` DECIMAL(10,2), IN `p_estado` VARCHAR(20), IN `p_metodo_pago` VARCHAR(50), IN `p_xml` LONGTEXT, IN `p_cdr` LONGTEXT, IN `p_observaciones` TEXT, IN `p_usuario_registro` INT, IN `p_hash` VARCHAR(100))   BEGIN
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
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_BOL_INSERTAR_DETALLE` (IN `p_bol_id` INT, IN `p_orden` INT, IN `p_codigo` VARCHAR(20), IN `p_descripcion` VARCHAR(500), IN `p_unidad` VARCHAR(10), IN `p_cantidad` DECIMAL(10,2), IN `p_precio_unitario` DECIMAL(10,2), IN `p_subtotal` DECIMAL(10,2), IN `p_igv` DECIMAL(10,2), IN `p_total` DECIMAL(10,2))   BEGIN
    INSERT INTO boleta_detalle (
        bol_id, bol_det_orden, bol_det_codigo, 
        bol_det_descripcion, bol_det_unidad, bol_det_cantidad, 
        bol_det_precio_unitario, bol_det_subtotal, bol_det_igv, bol_det_total
    ) VALUES (
        p_bol_id, p_orden, p_codigo,
        p_descripcion, p_unidad, p_cantidad,
        p_precio_unitario, p_subtotal, p_igv, p_total
    );
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_BOL_LISTAR` (IN `p_fecha_inicio` DATE, IN `p_fecha_fin` DATE, IN `p_tipo` VARCHAR(2) CHARSET utf8mb4, IN `p_estado` VARCHAR(20) CHARSET utf8mb4)   BEGIN
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
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_BOL_OBTENER_CDR` (IN `p_bol_id` INT)   BEGIN
    SELECT 
        bol_serie, 
        bol_correlativo, 
        bol_cdr, 
        bol_cdr_ruta 
    FROM boleta 
    WHERE bol_id = p_bol_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_BOL_OBTENER_CLIENTE` (IN `p_cliente_id` INT)   BEGIN
    SELECT 
        IdCliente, 
        TipoDocumento, 
        Documento, 
        Nombre, 
        Apellido, 
        Direccion 
    FROM cliente 
    WHERE IdCliente = p_cliente_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_BOL_OBTENER_CON_RECEPCION` (IN `p_bol_id` INT)   BEGIN
    SELECT 
        b.*, 
        r.IdRecepcion 
    FROM boleta b 
    LEFT JOIN recepcion r ON b.rec_id = r.IdRecepcion 
    WHERE b.bol_id = p_bol_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_BOL_OBTENER_CORRELATIVO` (IN `p_serie` VARCHAR(10) CHARSET utf8mb4)   BEGIN
    SELECT COALESCE(MAX(bol_correlativo), 0) + 1 AS siguiente 
    FROM boleta 
    WHERE bol_serie = p_serie COLLATE utf8mb4_general_ci;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_BOL_OBTENER_DETALLES` (IN `p_bol_id` INT)   BEGIN
    SELECT * 
    FROM boleta_detalle 
    WHERE bol_id = p_bol_id 
    ORDER BY bol_det_orden;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_BOL_OBTENER_HABITACION` (IN `p_habitacion_id` INT)   BEGIN
    SELECT Numero 
    FROM habitacion 
    WHERE IdHabitacion = p_habitacion_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_BOL_OBTENER_PDF` (IN `p_bol_id` INT)   BEGIN
    SELECT 
        bol_serie, 
        bol_correlativo, 
        bol_pdf_ruta 
    FROM boleta 
    WHERE bol_id = p_bol_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_BOL_OBTENER_POR_ID` (IN `p_bol_id` INT)   BEGIN
    SELECT * 
    FROM boleta 
    WHERE bol_id = p_bol_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_BOL_OBTENER_POR_RECEPCION` (IN `p_rec_id` INT)   BEGIN
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
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_BOL_OBTENER_XML` (IN `p_bol_id` INT)   BEGIN
    SELECT 
        bol_serie, 
        bol_correlativo, 
        bol_xml, 
        bol_xml_ruta 
    FROM boleta 
    WHERE bol_id = p_bol_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_BOL_RESUMEN` (IN `p_fecha_inicio` DATE, IN `p_fecha_fin` DATE, IN `p_tipo` VARCHAR(2) CHARSET utf8mb4, IN `p_estado` VARCHAR(20) CHARSET utf8mb4)   BEGIN
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
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_CAMBIAR_ESTADO_CATEGORIA_01` (IN `CAT_ID` INT, IN `NUEVO_ESTADO` INT)   BEGIN
    UPDATE categoria 
    SET Estado = NUEVO_ESTADO 
    WHERE IdCategoria = CAT_ID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_CAMBIAR_ESTADO_ESTADO_HABITACION_01` (IN `EST_HAB_ID` INT, IN `NUEVO_ESTADO` INT)   BEGIN
    UPDATE estado_habitacion 
    SET Estado = NUEVO_ESTADO 
    WHERE IdEstadoHabitacion = EST_HAB_ID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_CAMBIAR_ESTADO_HABITACION_01` (IN `HAB_ID` INT, IN `NUEVO_ESTADO` INT)   BEGIN
    UPDATE habitacion 
    SET Estado = NUEVO_ESTADO 
    WHERE IdHabitacion = HAB_ID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_CAMBIAR_ESTADO_PISO_01` (IN `PISO_ID` INT, IN `NUEVO_ESTADO` INT)   BEGIN
    UPDATE piso 
    SET Estado = NUEVO_ESTADO 
    WHERE IdPiso = PISO_ID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_CAMBIAR_ESTADO_PRODUCTO_01` (IN `PRO_ID` INT, IN `NUEVO_ESTADO` INT)   BEGIN
    UPDATE producto 
    SET Estado = NUEVO_ESTADO 
    WHERE IdProducto = PRO_ID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_CAMBIAR_ESTADO_ROL_01` (IN `ROL_ID` INT, IN `NUEVO_ESTADO` INT)   BEGIN
    UPDATE rol 
    SET Estado = NUEVO_ESTADO 
    WHERE IdRol = ROL_ID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_CAMBIAR_ESTADO_USUARIO_01` (IN `USU_ID` INT, IN `NUEVO_ESTADO` INT)   BEGIN
    UPDATE usuario 
    SET Estado = NUEVO_ESTADO 
    WHERE IdUsuario = USU_ID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_CAMBIAR_TIPO_ESTADO_HABITACION_01` (IN `HAB_ID` INT, IN `ID_ESTADO_HABITACION` INT)   BEGIN
    UPDATE habitacion 
    SET IdEstadoHabitacion = ID_ESTADO_HABITACION
    WHERE IdHabitacion = HAB_ID AND Estado = 1;
    
    -- Retornar el resultado de la actualización
    SELECT 
        h.IdHabitacion AS HAB_ID,
        h.Numero AS HAB_NUM,
        h.IdEstadoHabitacion AS HAB_EST_ID,
        eh.Descripcion AS ESTADO_NOM,
        'Tipo de estado actualizado' AS Mensaje
    FROM habitacion h
    LEFT JOIN estado_habitacion eh ON h.IdEstadoHabitacion = eh.IdEstadoHabitacion
    WHERE h.IdHabitacion = HAB_ID;
END$$

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
            b.bol_descripcion_cdr AS comp_descripcion_cdr,
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

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_D_CATEGORIA_01` (IN `CAT_ID` INT)   BEGIN
    DELETE FROM categoria 
    WHERE IdCategoria = CAT_ID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_D_ESTADO_HABITACION_01` (IN `p_est_hab_id` INT)   BEGIN
    DELETE FROM estado_habitacion 
    WHERE IdEstadoHabitacion = p_est_hab_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_D_HABITACION_01` (IN `HAB_ID` INT)   BEGIN
    DELETE FROM habitacion 
    WHERE IdHabitacion = HAB_ID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_D_HABITACION_TARIFA_01` (IN `HAB_TAR_ID` INT)   BEGIN
    DELETE FROM habitacion_tarifa
    WHERE id_habitacion_tarifa = HAB_TAR_ID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_D_PISO_01` (IN `PISO_ID` INT)   BEGIN
    DELETE FROM piso 
    WHERE IdPiso = PISO_ID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_D_PRODUCTO_01` (IN `PRO_ID` INT)   BEGIN
    DELETE FROM producto 
    WHERE IdProducto = PRO_ID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_D_ROL_01` (IN `ROL_ID` INT)   BEGIN
    DELETE FROM rol 
    WHERE IdRol = ROL_ID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_D_TARIFA_01` (IN `TAR_ID` INT)   BEGIN
    DELETE FROM tarifa 
    WHERE IdTarifa = TAR_ID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_D_USUARIO_01` (IN `USU_ID` INT)   BEGIN
    DELETE FROM usuario 
    WHERE IdUsuario = USU_ID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_FAC_ACTUALIZAR_RUTA_CDR` (IN `p_fac_id` INT, IN `p_ruta` VARCHAR(500))   BEGIN
    UPDATE factura SET fac_cdr_ruta = p_ruta WHERE fac_id = p_fac_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_FAC_ACTUALIZAR_RUTA_PDF` (IN `p_fac_id` INT, IN `p_ruta` VARCHAR(500))   BEGIN
    UPDATE factura SET fac_pdf_ruta = p_ruta WHERE fac_id = p_fac_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_FAC_ACTUALIZAR_RUTA_XML` (IN `p_fac_id` INT, IN `p_ruta` VARCHAR(500))   BEGIN
    UPDATE factura SET fac_xml_ruta = p_ruta WHERE fac_id = p_fac_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_FAC_INSERTAR` (IN `p_rec_id` INT, IN `p_serie` VARCHAR(10), IN `p_correlativo` VARCHAR(20), IN `p_fecha_emision` DATETIME, IN `p_cliente_ruc` VARCHAR(11), IN `p_cliente_razon_social` VARCHAR(200), IN `p_cliente_direccion` VARCHAR(300), IN `p_cliente_ubigeo` VARCHAR(6), IN `p_cliente_email` VARCHAR(100), IN `p_op_gravadas` DECIMAL(10,2), IN `p_subtotal` DECIMAL(10,2), IN `p_igv` DECIMAL(10,2), IN `p_total` DECIMAL(10,2), IN `p_forma_pago` VARCHAR(20), IN `p_metodo_pago` VARCHAR(50), IN `p_estado` VARCHAR(20), IN `p_hash` VARCHAR(100), IN `p_xml` LONGTEXT, IN `p_cdr` LONGTEXT, IN `p_observaciones` TEXT, IN `p_usuario_registro` INT)   BEGIN
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
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_FAC_INSERTAR_CUOTA` (IN `p_fac_id` INT, IN `p_numero` INT, IN `p_monto` DECIMAL(10,2), IN `p_fecha_vencimiento` DATE)   BEGIN
    INSERT INTO factura_cuotas (
        fac_id, fac_cuo_numero, fac_cuo_monto, fac_cuo_fecha_vencimiento
    ) VALUES (
        p_fac_id, p_numero, p_monto, p_fecha_vencimiento
    );
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_FAC_INSERTAR_DETALLE` (IN `p_fac_id` INT, IN `p_orden` INT, IN `p_codigo` VARCHAR(20), IN `p_descripcion` VARCHAR(500), IN `p_unidad` VARCHAR(10), IN `p_cantidad` DECIMAL(10,2), IN `p_precio_unitario` DECIMAL(10,2), IN `p_valor_unitario` DECIMAL(10,2), IN `p_subtotal` DECIMAL(10,2), IN `p_igv` DECIMAL(10,2), IN `p_total` DECIMAL(10,2), IN `p_tipo_afectacion` VARCHAR(2))   BEGIN
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
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_FAC_OBTENER_CORRELATIVO` (IN `p_serie` VARCHAR(10))   BEGIN
    SELECT COALESCE(MAX(CAST(fac_correlativo AS UNSIGNED)), 0) + 1 AS siguiente 
    FROM factura 
    WHERE fac_serie = p_serie COLLATE utf8mb4_general_ci;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_FAC_OBTENER_DETALLES` (IN `p_fac_id` INT)   BEGIN
    SELECT * 
    FROM factura_detalle 
    WHERE fac_id = p_fac_id 
    ORDER BY fac_det_orden;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_FAC_OBTENER_POR_RECEPCION` (IN `p_rec_id` INT)   BEGIN
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
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_I_CATEGORIA_01` (IN `CAT_NOM` VARCHAR(150))   BEGIN
    DECLARE existing_id INT DEFAULT 0;
    SELECT IdCategoria INTO existing_id 
    FROM categoria 
    WHERE UPPER(TRIM(Descripcion)) = UPPER(TRIM(CAT_NOM))
    LIMIT 1;
    IF existing_id > 0 THEN
        UPDATE categoria 
        SET Estado = 1,
            FechaCreacion = NOW() 
        WHERE IdCategoria = existing_id;
        SELECT existing_id as IdCategoria, 'Registro reactivado' as Mensaje;
    ELSE
        INSERT INTO categoria (Descripcion, Estado, FechaCreacion) 
        VALUES (CAT_NOM, 1, NOW());
        SELECT LAST_INSERT_ID() as IdCategoria, 'Registro creado' as Mensaje;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_I_CLIENTE_01` (IN `p_tipo_documento` VARCHAR(15), IN `p_documento` VARCHAR(15), IN `p_nombre` VARCHAR(50), IN `p_apellido` VARCHAR(50), IN `p_direccion` VARCHAR(250), OUT `p_id_cliente` INT)   BEGIN
    INSERT INTO cliente (TipoDocumento, Documento, Nombre, Apellido, Direccion, Estado, FechaCreacion)
    VALUES (p_tipo_documento, p_documento, p_nombre, p_apellido, p_direccion, 1, NOW());
    
    SET p_id_cliente = LAST_INSERT_ID();
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_I_ESTADO_HABITACION_01` (IN `EST_HAB_NOM` VARCHAR(50))   BEGIN
    DECLARE existing_id INT DEFAULT 0;
    
    -- Verificar si existe un registro con el mismo nombre (activo o inactivo)
    SELECT IdEstadoHabitacion INTO existing_id 
    FROM estado_habitacion 
    WHERE UPPER(TRIM(Descripcion)) = UPPER(TRIM(EST_HAB_NOM))
    LIMIT 1;
    
    IF existing_id > 0 THEN
        -- Si existe, reactivarlo y actualizar fecha
        UPDATE estado_habitacion 
        SET Estado = 1, 
            FechaCreacion = NOW() 
        WHERE IdEstadoHabitacion = existing_id;
        
        SELECT existing_id as IdEstadoHabitacion, 'Registro reactivado' as Mensaje;
    ELSE
        -- Si no existe, crear nuevo registro
        INSERT INTO estado_habitacion (Descripcion, Estado, FechaCreacion) 
        VALUES (EST_HAB_NOM, 1, NOW());
        
        SELECT LAST_INSERT_ID() as IdEstadoHabitacion, 'Registro creado' as Mensaje;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_I_HABITACION_01` (IN `HAB_NUM` VARCHAR(50), IN `HAB_DET` VARCHAR(500), IN `HAB_EST_ID` INT, IN `HAB_PISO_ID` INT, IN `HAB_CAT_ID` INT)   BEGIN
    DECLARE existing_id INT DEFAULT 0;
    DECLARE estado_disponible INT DEFAULT 1;
    
    -- Si no se proporciona estado o es 0, usar el estado "Disponible" dinámicamente
    IF HAB_EST_ID IS NULL OR HAB_EST_ID = 0 THEN
        -- Obtener dinámicamente el ID del estado "Disponible"
        SELECT IdEstadoHabitacion INTO estado_disponible
        FROM estado_habitacion 
        WHERE Estado = 1 
        AND UPPER(Descripcion) LIKE '%DISPONIBLE%'
        ORDER BY IdEstadoHabitacion ASC
        LIMIT 1;
        
        SET HAB_EST_ID = IFNULL(estado_disponible, 1);
    END IF;
    
    -- Verificar si existe un registro con el mismo número (activo o inactivo)
    SELECT IdHabitacion INTO existing_id 
    FROM habitacion 
    WHERE UPPER(TRIM(Numero)) = UPPER(TRIM(HAB_NUM))
    LIMIT 1;
    
    IF existing_id > 0 THEN
        -- Si existe, reactivarlo y actualizar datos
        UPDATE habitacion 
        SET Estado = 1, 
            Detalle = HAB_DET,
            IdEstadoHabitacion = HAB_EST_ID,
            IdPiso = HAB_PISO_ID,
            IdCategoria = HAB_CAT_ID,
            FechaCreacion = NOW() 
        WHERE IdHabitacion = existing_id;
        
        SELECT existing_id as IdHabitacion, 'Registro reactivado' as Mensaje;
    ELSE
        -- Si no existe, crear nuevo registro
        INSERT INTO habitacion (Numero, Detalle,IdEstadoHabitacion, IdPiso, IdCategoria, Estado, FechaCreacion) 
        VALUES (HAB_NUM, HAB_DET, HAB_EST_ID, HAB_PISO_ID, HAB_CAT_ID, 1, NOW());
        
        SELECT LAST_INSERT_ID() as IdHabitacion, 'Registro creado' as Mensaje;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_I_HABITACION_TARIFA_01` (IN `HAB_ID` INT, IN `TARIFA_ID` INT, IN `FECHA_INICIO` DATETIME, IN `FECHA_FIN` DATETIME)   BEGIN
    DECLARE cnt INT DEFAULT 0;

    -- Validación sencilla: comprobar si existe asignación activa de la misma tarifa
    SELECT COUNT(1) INTO cnt
    FROM habitacion_tarifa
    WHERE id_habitacion = HAB_ID
      AND id_tarifa = TARIFA_ID
      AND (
           (fecha_fin IS NULL AND FECHA_INICIO >= fecha_inicio)
           OR
           (fecha_fin IS NOT NULL AND FECHA_INICIO BETWEEN fecha_inicio AND fecha_fin)
      );

    IF cnt > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ya existe una asignación activa de esta tarifa en el periodo indicado';
    ELSE
        INSERT INTO habitacion_tarifa (id_habitacion, id_tarifa, fecha_inicio, fecha_fin)
        VALUES (HAB_ID, TARIFA_ID, FECHA_INICIO, FECHA_FIN);
        SELECT LAST_INSERT_ID() AS id_insertado;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_I_MENU_02` (`p_IdRol` INT)   BEGIN
    IF (SELECT COUNT(*) FROM TD_MENU WHERE IdRol = p_IdRol) = 0
    THEN
        -- Insertar todos los menús para el rol si no tiene ninguno
        INSERT INTO TD_MENU
        (MEN_ID, IdRol, MEND_PERMI, FECH_CREA, EST)
        (SELECT MEN_ID, p_IdRol, 'No', NOW(3), 1 FROM TM_MENU WHERE EST = 1);
    ELSE
        -- Insertar solo los menús que faltan para el rol
        INSERT INTO TD_MENU
        (MEN_ID, IdRol, MEND_PERMI, FECH_CREA, EST)
        (SELECT MEN_ID, p_IdRol, 'No', NOW(3), 1 
         FROM TM_MENU 
         WHERE EST = 1 
         AND MEN_ID NOT IN (SELECT MEN_ID FROM TD_MENU WHERE IdRol = p_IdRol));
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_I_PISO_01` (IN `PISO_NOM` VARCHAR(50))   BEGIN
    DECLARE existing_id INT DEFAULT 0;
    
    -- Verificar si existe un registro con el mismo nombre (activo o inactivo)
    SELECT IdPiso INTO existing_id 
    FROM piso 
    WHERE UPPER(TRIM(Descripcion)) = UPPER(TRIM(PISO_NOM))
    LIMIT 1;
    
    IF existing_id > 0 THEN
        -- Si existe, reactivarlo y actualizar fecha
        UPDATE piso 
        SET Estado = 1, 
            FechaCreacion = NOW() 
        WHERE IdPiso = existing_id;
        
        SELECT existing_id as IdPiso, 'Registro reactivado' as Mensaje;
    ELSE
        -- Si no existe, crear nuevo registro
        INSERT INTO piso (Descripcion, Estado, FechaCreacion) 
        VALUES (PISO_NOM, 1, NOW());
        
        SELECT LAST_INSERT_ID() as IdPiso, 'Registro creado' as Mensaje;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_I_PRODUCTO_01` (IN `PRO_NOM` VARCHAR(50), IN `PRO_DET` VARCHAR(100), IN `PRO_PRE` DECIMAL(10,2), IN `PRO_CANT` INT)   BEGIN
    DECLARE existing_id INT DEFAULT 0;
    
    -- Verificar si existe un registro con el mismo nombre (activo o inactivo)
    SELECT IdProducto INTO existing_id 
    FROM producto 
    WHERE UPPER(TRIM(Nombre)) = UPPER(TRIM(PRO_NOM))
    LIMIT 1;
    
    IF existing_id > 0 THEN
        -- Si existe, reactivarlo y actualizar fecha
        UPDATE producto 
        SET Estado = 1, 
            FechaCreacion = NOW() 
        WHERE IdProducto = existing_id;
        
        SELECT existing_id as IdProducto, 'Registro reactivado' as Mensaje;
    ELSE
        -- Si no existe, crear nuevo registro
        INSERT INTO producto (Nombre, Detalle, Precio, Cantidad, Estado, FechaCreacion) 
        VALUES (PRO_NOM, PRO_DET, PRO_PRE, PRO_CANT, 1, NOW());
        
        SELECT LAST_INSERT_ID() as IdProducto, 'Registro creado' as Mensaje;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_I_RECEPCION_01` (IN `p_IdCliente` INT, IN `p_IdHabitacion` INT, IN `p_PrecioInicial` DECIMAL(10,2), IN `p_Adelanto` DECIMAL(10,2), IN `p_Observacion` VARCHAR(500), IN `p_FechaSalida` DATETIME, OUT `p_IdRecepcion` INT)   BEGIN
    DECLARE v_PrecioRestante DECIMAL(10,2) DEFAULT 0.00;
    DECLARE v_TotalPagado DECIMAL(10,2) DEFAULT 0.00;
    DECLARE v_CostoPenalidad DECIMAL(10,2) DEFAULT 0.00;
    DECLARE v_EstadoOcupado INT DEFAULT NULL;

    SET v_PrecioRestante = GREATEST(p_PrecioInicial - IFNULL(p_Adelanto, 0.00), 0.00);
    SET v_TotalPagado   = IFNULL(p_Adelanto, 0.00);
    SET v_CostoPenalidad = 0.00;

    INSERT INTO recepcion (
        IdCliente,
        IdHabitacion,
        FechaEntrada,
        FechaSalida,
        FechaSalidaConfirmacion,
        PrecioInicial,
        Adelanto,
        PrecioRestante,
        TotalPagado,
        CostoPenalidad,
        Observacion,
        Estado
    ) VALUES (
        p_IdCliente,
        p_IdHabitacion,
        NOW(),
        p_FechaSalida,
        NULL,
        p_PrecioInicial,
        IFNULL(p_Adelanto, 0.00),
        v_PrecioRestante,
        v_TotalPagado,
        v_CostoPenalidad,
        p_Observacion,
        1
    );

    -- Marcar habitación como "Ocupado" de forma dinámica
    SELECT IdEstadoHabitacion INTO v_EstadoOcupado
    FROM estado_habitacion 
    WHERE Estado = 1 
      AND UPPER(Descripcion) LIKE '%OCUPADO%'
    ORDER BY IdEstadoHabitacion ASC
    LIMIT 1;

    IF v_EstadoOcupado IS NOT NULL THEN
        UPDATE habitacion 
        SET IdEstadoHabitacion = v_EstadoOcupado
        WHERE IdHabitacion = p_IdHabitacion;
    END IF;

    SET p_IdRecepcion = LAST_INSERT_ID();
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_I_RECEPCION_02` (IN `p_IdCliente` INT, IN `p_IdHabitacion` INT, IN `p_IdTarifa` INT, IN `p_PrecioInicial` DECIMAL(10,2), IN `p_Adelanto` DECIMAL(10,2), IN `p_Observacion` VARCHAR(500), IN `p_FechaSalida` DATETIME, OUT `p_IdRecepcion` INT)   BEGIN
    DECLARE v_PrecioRestante DECIMAL(10,2) DEFAULT 0.00;
    DECLARE v_TotalPagado DECIMAL(10,2) DEFAULT 0.00;
    DECLARE v_CostoPenalidad DECIMAL(10,2) DEFAULT 0.00;
    DECLARE v_EstadoOcupado INT DEFAULT NULL;

    SET v_PrecioRestante = GREATEST(p_PrecioInicial - IFNULL(p_Adelanto, 0.00), 0.00);
    SET v_TotalPagado   = IFNULL(p_Adelanto, 0.00);
    SET v_CostoPenalidad = 0.00;

    INSERT INTO recepcion (
        IdCliente,
        IdHabitacion,
        IdTarifa,
        FechaEntrada,
        FechaSalida,
        FechaSalidaConfirmacion,
        PrecioInicial,
        Adelanto,
        PrecioRestante,
        TotalPagado,
        CostoPenalidad,
        Observacion,
        Estado
    ) VALUES (
        p_IdCliente,
        p_IdHabitacion,
        p_IdTarifa,
        NOW(),
        p_FechaSalida,
        NULL,
        p_PrecioInicial,
        IFNULL(p_Adelanto, 0.00),
        v_PrecioRestante,
        v_TotalPagado,
        v_CostoPenalidad,
        p_Observacion,
        1
    );

    SET p_IdRecepcion = LAST_INSERT_ID();

    -- Actualizar estado de habitación a OCUPADO (IdEstadoHabitacion = 11)
    SELECT IdEstadoHabitacion INTO v_EstadoOcupado 
    FROM estado_habitacion 
    WHERE UPPER(Descripcion) LIKE '%OCUPAD%' 
    LIMIT 1;

    IF v_EstadoOcupado IS NOT NULL THEN
        UPDATE habitacion 
        SET IdEstadoHabitacion = v_EstadoOcupado 
        WHERE IdHabitacion = p_IdHabitacion;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_I_RECEPCION_03` (IN `p_IdCliente` INT, IN `p_IdHabitacion` INT, IN `p_IdTarifa` INT, IN `p_PrecioInicial` DECIMAL(10,2), IN `p_Adelanto` DECIMAL(10,2), IN `p_Observacion` VARCHAR(500), IN `p_FechaSalida` DATETIME, IN `p_TipoComprobante` VARCHAR(2), OUT `p_IdRecepcion` INT)   BEGIN
    DECLARE v_PrecioRestante DECIMAL(10,2) DEFAULT 0.00;
    DECLARE v_TotalPagado DECIMAL(10,2) DEFAULT 0.00;
    DECLARE v_CostoPenalidad DECIMAL(10,2) DEFAULT 0.00;
    DECLARE v_EstadoOcupado INT DEFAULT NULL;
    DECLARE v_TipoComp VARCHAR(2);

    SET v_PrecioRestante = GREATEST(p_PrecioInicial - IFNULL(p_Adelanto, 0.00), 0.00);
    SET v_TotalPagado   = IFNULL(p_Adelanto, 0.00);
    SET v_CostoPenalidad = 0.00;
    -- Si no viene tipo de comprobante, usar Boleta por defecto
    SET v_TipoComp = IFNULL(p_TipoComprobante, '03');

    INSERT INTO recepcion (
        IdCliente,
        IdHabitacion,
        IdTarifa,
        FechaEntrada,
        FechaSalida,
        FechaSalidaConfirmacion,
        PrecioInicial,
        Adelanto,
        PrecioRestante,
        TotalPagado,
        CostoPenalidad,
        Observacion,
        TipoComprobante,
        Estado
    ) VALUES (
        p_IdCliente,
        p_IdHabitacion,
        p_IdTarifa,
        NOW(),
        p_FechaSalida,
        NULL,
        p_PrecioInicial,
        IFNULL(p_Adelanto, 0.00),
        v_PrecioRestante,
        v_TotalPagado,
        v_CostoPenalidad,
        p_Observacion,
        v_TipoComp,
        1
    );

    SET p_IdRecepcion = LAST_INSERT_ID();

    -- Actualizar estado de habitación a OCUPADO (IdEstadoHabitacion = 11)
    SELECT IdEstadoHabitacion INTO v_EstadoOcupado 
    FROM estado_habitacion 
    WHERE UPPER(Descripcion) LIKE '%OCUPAD%' 
    LIMIT 1;

    IF v_EstadoOcupado IS NOT NULL THEN
        UPDATE habitacion 
        SET IdEstadoHabitacion = v_EstadoOcupado 
        WHERE IdHabitacion = p_IdHabitacion;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_I_ROL_01` (IN `ROL_NOM` VARCHAR(50))   BEGIN
    DECLARE existing_id INT DEFAULT 0;
    
    -- Verificar si existe un registro con el mismo nombre (activo o inactivo)
    SELECT IdRol INTO existing_id 
    FROM rol 
    WHERE Descripcion = ROL_NOM 
    LIMIT 1;
    
    IF existing_id > 0 THEN
        -- Si existe, reactivarlo y actualizar fecha
        UPDATE rol 
        SET Estado = 1, 
            FechaCreacion = NOW() 
        WHERE IdRol = existing_id;
        
        SELECT existing_id as IdRol, 'Registro reactivado' as Mensaje;
    ELSE
        -- Si no existe, crear nuevo registro
        INSERT INTO rol (Descripcion, Estado, FechaCreacion) 
        VALUES (ROL_NOM, 1, NOW());
        
        SELECT LAST_INSERT_ID() as IdRol, 'Registro creado' as Mensaje;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_I_TARIFA_01` (IN `TAR_DESC` VARCHAR(100), IN `TAR_PRECIO` DECIMAL(10,2))   BEGIN
    INSERT INTO tarifa (Descripcion, Precio, Estado)
    VALUES (TAR_DESC, TAR_PRECIO, 1);
    SELECT LAST_INSERT_ID() AS id_insertado;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_I_USUARIO_01` (IN `USU_NOM` VARCHAR(50), IN `USU_APE` VARCHAR(50), IN `USU_DNI` VARCHAR(8), IN `USU_CORREO` VARCHAR(100), IN `USU_PASS` VARCHAR(255), IN `ROL_ID` INT)   BEGIN
    INSERT INTO usuario (Nombre, Apellido, DNI, Correo, Pass, Estado, FechaCreacion, IdRol) 
    VALUES (USU_NOM, USU_APE, USU_DNI, USU_CORREO, USU_PASS, 1, NOW(), ROL_ID);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_I_VENTA_01` (IN `p_IdRecepcion` INT)   BEGIN
  INSERT INTO venta (IdRecepcion, Total, Estado)
  VALUES (p_IdRecepcion, 0, 'PENDIENTE');
  SELECT LAST_INSERT_ID() AS VENT_ID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_L_CATEGORIA_01` ()   BEGIN
    SELECT 
        IdCategoria AS CAT_ID,
        Descripcion AS CAT_NOM,
        Estado AS EST,
        DATE_FORMAT(FechaCreacion, '%d/%m/%Y %H:%i:%s') AS FECH_CREA
    FROM categoria 
    ORDER BY FechaCreacion DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_L_CATEGORIA_02` (IN `CAT_ID` INT)   BEGIN
    SELECT 
        IdCategoria AS CAT_ID,
        Descripcion AS CAT_NOM,
        Estado AS EST,
        FechaCreacion AS FECH_CREA
    FROM categoria 
    WHERE IdCategoria = CAT_ID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_L_CATEGORIA_03` ()   BEGIN
    SELECT 
        IdCategoria AS CAT_ID,
        Descripcion AS CAT_NOM,
        Estado AS EST,
        DATE_FORMAT(FechaCreacion, '%d/%m/%Y %H:%i:%s') AS FECH_CREA
    FROM categoria 
    WHERE Estado = 1
    ORDER BY FechaCreacion DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_L_CLIENTE_01` ()   BEGIN
    SELECT 
        IdCliente AS CLI_ID,
        TipoDocumento AS CLI_TIPO_DOC,
        Documento AS CLI_DOC,
        Nombre AS CLI_NOM,
        Apellido AS CLI_APE,
        Direccion AS CLI_DIR,
        Estado AS EST,
        DATE_FORMAT(FechaCreacion, '%d/%m/%Y %H:%i:%s') AS FECH_CREA,
        CASE 
            WHEN Estado = 1 THEN 'Activo'
            ELSE 'Inactivo'
        END AS EST_TEXTO
    FROM cliente 
    WHERE Estado = 1
    ORDER BY IdCliente DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_L_CLIENTE_02` (IN `p_id` INT)   BEGIN
    SELECT 
        IdCliente AS CLI_ID,
        TipoDocumento AS CLI_TIPO_DOC,
        Documento AS CLI_DOC,
        Nombre AS CLI_NOM,
        Apellido AS CLI_APE,
        Direccion AS CLI_DIR,
        Estado AS EST,
        FechaCreacion AS FECH_CREA
    FROM cliente 
    WHERE IdCliente = p_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_L_ESTADO_HABITACION_01` ()   BEGIN
    SELECT 
        IdEstadoHabitacion AS EST_HAB_ID,
        Descripcion AS EST_HAB_NOM,
        Estado AS EST,
        FechaCreacion AS FECH_CREA
    FROM estado_habitacion
    ORDER BY IdEstadoHabitacion DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_L_ESTADO_HABITACION_02` (IN `p_est_hab_id` INT)   BEGIN
    SELECT 
        IdEstadoHabitacion AS EST_HAB_ID,
        Descripcion AS EST_HAB_NOM,
        Estado AS EST,
        FechaCreacion AS FECH_CREA
    FROM estado_habitacion
    WHERE IdEstadoHabitacion = p_est_hab_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_L_ESTADO_HABITACION_03` ()   BEGIN
    SELECT 
        IdEstadoHabitacion AS EST_HAB_ID,
        Descripcion AS EST_HAB_NOM,
        Estado AS EST,
        FechaCreacion AS FECH_CREA
    FROM estado_habitacion
    WHERE Estado = 1
    ORDER BY Descripcion ASC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_L_HABITACION_01` ()   BEGIN
    SELECT 
        h.IdHabitacion AS HAB_ID,
        h.Numero AS HAB_NUM,
        h.Detalle AS HAB_DET,
        h.IdEstadoHabitacion AS HAB_EST_ID,
        h.IdPiso AS HAB_PISO_ID,
        h.IdCategoria AS HAB_CAT_ID,
        h.Estado AS EST,
        DATE_FORMAT(h.FechaCreacion, '%d/%m/%Y %H:%i:%s') AS FECH_CREA,
        -- Información adicional con JOINs
        COALESCE(p.Descripcion, 'Sin Piso') AS PISO_NOM,
        COALESCE(c.Descripcion, 'Sin Categoría') AS CAT_NOM,
        COALESCE(eh.Descripcion, 'Sin Estado') AS ESTADO_NOM
    FROM habitacion h
    LEFT JOIN piso p ON h.IdPiso = p.IdPiso AND p.Estado = 1
    LEFT JOIN categoria c ON h.IdCategoria = c.IdCategoria AND c.Estado = 1
    LEFT JOIN estado_habitacion eh ON h.IdEstadoHabitacion = eh.IdEstadoHabitacion AND eh.Estado = 1
    ORDER BY h.FechaCreacion DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_L_HABITACION_02` (IN `HAB_ID` INT)   BEGIN
    SELECT 
        h.IdHabitacion AS HAB_ID,
        h.Numero AS HAB_NUM,
        h.Detalle AS HAB_DET,
        h.IdEstadoHabitacion AS HAB_EST_ID,
        h.IdPiso AS HAB_PISO_ID,
        h.IdCategoria AS HAB_CAT_ID,
        h.Estado AS EST,
        h.FechaCreacion AS FECH_CREA,
        -- Información adicional con JOINs
        COALESCE(p.Descripcion, 'Sin Piso') AS PISO_NOM,
        COALESCE(c.Descripcion, 'Sin Categoría') AS CAT_NOM,
        COALESCE(eh.Descripcion, 'Sin Estado') AS ESTADO_NOM
    FROM habitacion h
    LEFT JOIN piso p ON h.IdPiso = p.IdPiso AND p.Estado = 1
    LEFT JOIN categoria c ON h.IdCategoria = c.IdCategoria AND c.Estado = 1
    LEFT JOIN estado_habitacion eh ON h.IdEstadoHabitacion = eh.IdEstadoHabitacion AND eh.Estado = 1
    WHERE h.IdHabitacion = HAB_ID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_L_HABITACION_03` ()   BEGIN
    SELECT
        h.IdHabitacion AS HAB_ID,
        h.Numero AS HAB_NUM,
        h.Detalle AS HAB_DET,
        h.IdEstadoHabitacion AS HAB_EST_ID,
        h.IdPiso AS HAB_PISO_ID,
        h.IdCategoria AS HAB_CAT_ID,
        h.Estado AS EST,
        DATE_FORMAT(h.FechaCreacion, '%d/%m/%Y %H:%i:%s') AS FECH_CREA,
        COALESCE(p.Descripcion, 'Sin Piso') AS PISO_NOM,
        COALESCE(c.Descripcion, 'Sin Categoría') AS CAT_NOM,
        COALESCE(eh.Descripcion, 'Sin Estado') AS ESTADO_NOM,
        COALESCE(c.Amenities, '') AS CAT_AMENITIES
    FROM habitacion h
    LEFT JOIN piso p ON h.IdPiso = p.IdPiso AND p.Estado = 1
    LEFT JOIN categoria c ON h.IdCategoria = c.IdCategoria AND c.Estado = 1
    LEFT JOIN estado_habitacion eh ON h.IdEstadoHabitacion = eh.IdEstadoHabitacion AND eh.Estado = 1
    WHERE h.Estado = 1
    ORDER BY h.FechaCreacion DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_L_HABITACION_OCUPADO` ()   BEGIN
    SELECT 
        h.IdHabitacion AS HAB_ID,
        h.Numero AS HAB_NUM,
        h.Detalle AS HAB_DET,
        h.IdEstadoHabitacion AS HAB_EST_ID,
        h.IdPiso AS HAB_PISO_ID,
        h.IdCategoria AS HAB_CAT_ID,
        h.Estado AS EST,
        DATE_FORMAT(h.FechaCreacion, '%d/%m/%Y %H:%i:%s') AS FECH_CREA,
        COALESCE(p.Descripcion, 'Sin Piso') AS PISO_NOM,
        COALESCE(c.Descripcion, 'Sin Categoría') AS CAT_NOM,
        COALESCE(eh.Descripcion, 'Sin Estado') AS ESTADO_NOM
    FROM habitacion h
    LEFT JOIN piso p ON h.IdPiso = p.IdPiso AND p.Estado = 1
    LEFT JOIN categoria c ON h.IdCategoria = c.IdCategoria AND c.Estado = 1
    LEFT JOIN estado_habitacion eh ON h.IdEstadoHabitacion = eh.IdEstadoHabitacion AND eh.Estado = 1
    WHERE h.Estado = 1
      AND UPPER(eh.Descripcion) LIKE '%OCUPADO%'
    ORDER BY h.FechaCreacion DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_L_MENU_01` (IN `IdRol` INT)   BEGIN
    SELECT        
        TD_MENU.MEND_ID, 
        TD_MENU.MEN_ID, 
        TD_MENU.IdRol, 
        TD_MENU.MEND_PERMI, 
        TD_MENU.FECH_CREA, 
        TD_MENU.EST, 
        TM_MENU.MEN_NOM, 
        TM_MENU.MEN_RUTA, 
        TM_MENU.MEN_IDENTI,
        TM_MENU.MEN_GRUPO,
        TM_MENU.MEN_ORDEN
    FROM            
        TD_MENU 
    INNER JOIN TM_MENU ON TD_MENU.MEN_ID = TM_MENU.MEN_ID
    WHERE 
        TD_MENU.IdRol = IdRol;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_L_PISO_01` ()   BEGIN
    SELECT 
        IdPiso AS PISO_ID,
        Descripcion AS PISO_NOM,
        Estado AS EST,
        DATE_FORMAT(FechaCreacion, '%d/%m/%Y %H:%i:%s') AS FECH_CREA
    FROM piso 
    ORDER BY FechaCreacion DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_L_PISO_02` (IN `PISO_ID` INT)   BEGIN
    SELECT 
        IdPiso AS PISO_ID,
        Descripcion AS PISO_NOM,
        Estado AS EST,
        FechaCreacion AS FECH_CREA
    FROM piso 
    WHERE IdPiso = PISO_ID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_L_PISO_03` ()   BEGIN
    SELECT 
        IdPiso AS PISO_ID,
        Descripcion AS PISO_NOM,
        Estado AS EST,
        DATE_FORMAT(FechaCreacion, '%d/%m/%Y %H:%i:%s') AS FECH_CREA
    FROM piso 
    WHERE Estado = 1
    ORDER BY FechaCreacion DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_L_PRODUCTO_01` ()   BEGIN
    SELECT 
        IdProducto AS PRO_ID,
        Nombre AS PRO_NOM,
        Detalle AS PRO_DET,
        Precio AS PRO_PRE,
        Cantidad AS PRO_CANT,
        Estado AS EST,
        DATE_FORMAT(FechaCreacion, '%d/%m/%Y %H:%i:%s') AS FECH_CREA
    FROM producto 
    ORDER BY FechaCreacion DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_L_PRODUCTO_02` (IN `PRO_ID` INT)   BEGIN
    SELECT 
        IdProducto AS PRO_ID,
        Nombre AS PRO_NOM,
        Detalle AS PRO_DET,
        Precio AS PRO_PRE,
        Cantidad AS PRO_CANT,
        Estado AS EST,
        FechaCreacion AS FECH_CREA
    FROM producto 
    WHERE IdProducto = PRO_ID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_L_PRODUCTO_03` ()   BEGIN
  SELECT 
    IdProducto AS PRO_ID,
    Nombre AS PRO_NOM,
    Detalle AS PRO_DET,
    Precio AS PRO_PRE,
    Cantidad AS PRO_CANT,
    Estado AS EST,
    FechaCreacion AS FEC_CREA
  FROM producto
  WHERE Estado = 1
  ORDER BY Nombre ASC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_L_ROL_01` ()   BEGIN
    SELECT 
        IdRol AS ROL_ID,
        Descripcion AS ROL_NOM,
        Estado AS EST,
        DATE_FORMAT(FechaCreacion, '%d/%m/%Y %H:%i:%s') AS FECH_CREA
    FROM rol 
    ORDER BY FechaCreacion DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_L_ROL_02` (IN `ROL_ID` INT)   BEGIN
    SELECT 
        IdRol AS ROL_ID,
        Descripcion AS ROL_NOM,
        Estado AS EST,
        FechaCreacion AS FECH_CREA
    FROM rol 
    WHERE IdRol = ROL_ID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_L_ROL_03` ()   BEGIN
    SELECT 
        IdRol AS ROL_ID,
        Descripcion AS ROL_NOM,
        Estado AS EST,
        DATE_FORMAT(FechaCreacion, '%d/%m/%Y %H:%i:%s') AS FECH_CREA
    FROM rol 
    WHERE Estado = 1
    ORDER BY FechaCreacion DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_L_TARIFA_01` ()   BEGIN
    SELECT IdTarifa, Descripcion, Precio
    FROM tarifa
    WHERE Estado = 1
    ORDER BY Descripcion;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_L_TARIFA_CATALOGO_01` ()   BEGIN
    SELECT IdTarifa, Descripcion, Precio, Estado
    FROM tarifa
    ORDER BY Descripcion;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_L_TARIFA_X_HABITACION_01` (IN `HAB_ID` INT)   BEGIN
    SELECT 
        ht.id_habitacion_tarifa,
        ht.id_habitacion,
        ht.id_tarifa,
        ht.fecha_inicio,
        ht.fecha_fin,
        t.Descripcion,
        t.Precio
    FROM habitacion_tarifa ht
    INNER JOIN tarifa t ON t.IdTarifa = ht.id_tarifa
    WHERE ht.id_habitacion = HAB_ID
    ORDER BY ht.fecha_inicio DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_L_TARIFA_X_ID_01` (IN `TAR_ID` INT)   BEGIN
    SELECT IdTarifa AS TAR_ID,
        Descripcion AS TAR_DESC,
        Precio AS TAR_PRECIO,
        Estado AS EST   
    FROM tarifa
    WHERE IdTarifa = TAR_ID
    LIMIT 1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_L_USUARIO_02` (IN `USU_ID` INT)   BEGIN
    SELECT 
        u.IdUsuario AS USU_ID,
        u.Nombre AS USU_NOM,
        u.Apellido AS USU_APE,
        u.DNI AS USU_DNI,
        u.Correo AS USU_CORREO,
        u.Pass AS USU_PASS,
        u.Estado AS EST,
        u.FechaCreacion AS FECH_CREA,
        u.IdRol AS ROL_ID,
        COALESCE(r.Descripcion, 'Sin Rol') AS ROL_NOM
    FROM usuario u
    LEFT JOIN rol r ON u.IdRol = r.IdRol
    WHERE u.IdUsuario = USU_ID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_L_USUARIO_03` (IN `CURRENT_USER_ID` INT)   BEGIN
    SELECT 
        u.IdUsuario AS USU_ID,
        u.Nombre AS USU_NOM,
        u.Apellido AS USU_APE,
        u.DNI AS USU_DNI,
        u.Correo AS USU_CORREO,
        u.Estado AS EST,
        DATE_FORMAT(u.FechaCreacion, '%d/%m/%Y %H:%i:%s') AS FECH_CREA,
        u.IdRol AS ROL_ID,
        COALESCE(r.Descripcion, 'Sin Rol') AS ROL_NOM,
        CASE 
            WHEN u.Estado = 1 THEN 'Activo'
            ELSE 'Inactivo'
        END AS EST_TEXTO
    FROM usuario u
    LEFT JOIN rol r ON u.IdRol = r.IdRol
    WHERE u.IdUsuario != CURRENT_USER_ID
    ORDER BY u.FechaCreacion DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_L_USUARIO_BY_DNI_01` (IN `USU_DNI` VARCHAR(15))   BEGIN
    SELECT IdUsuario 
    FROM usuario 
    WHERE DNI = USU_DNI AND Estado = 1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_L_VENTA_01` (IN `p_VENT_ID` INT)   BEGIN
  SELECT
    dv.IdDetalleVenta AS DETV_ID,
    p.Nombre AS PRO_NOM,
    p.Detalle AS PRO_DET,
    p.Precio AS PROD_PVENTA,
    dv.Cantidad AS DETV_CANT,
    dv.SubTotal AS DETV_TOTAL,
    dv.IdVenta AS VENT_ID,
    dv.IdProducto AS PROD_ID
  FROM detalle_venta dv
  INNER JOIN producto p ON dv.IdProducto = p.IdProducto
  WHERE dv.IdVenta = p_VENT_ID
  ORDER BY dv.IdDetalleVenta DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_R_RECEPCIONES_CLIENTES_FRECUENTES` (IN `p_fecha_inicio` DATE, IN `p_fecha_fin` DATE)   BEGIN
    SELECT 
        CONCAT(c.Nombre, ' ', c.Apellido) AS NombreCliente,
        c.Documento,
        COUNT(r.IdRecepcion) AS Visitas,
        COALESCE(SUM(r.TotalPagado), 0) AS TotalGastado
    FROM recepcion r
    INNER JOIN cliente c ON r.IdCliente = c.IdCliente
    WHERE DATE(r.FechaEntrada) BETWEEN p_fecha_inicio AND p_fecha_fin
    GROUP BY c.IdCliente, c.Nombre, c.Apellido, c.Documento
    HAVING COUNT(r.IdRecepcion) >= 1
    ORDER BY Visitas DESC, TotalGastado DESC
    LIMIT 10;


END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_R_RECEPCIONES_GRAFICO_DIARIO` (IN `p_fecha_inicio` DATE, IN `p_fecha_fin` DATE, IN `p_estado` VARCHAR(10))   BEGIN
    SELECT 
        DATE_FORMAT(r.FechaEntrada, '%d/%m') AS periodo,
        COUNT(r.IdRecepcion) AS recepciones,
        COALESCE(SUM(r.TotalPagado), 0) AS ingresos
    FROM recepcion r
    WHERE DATE(r.FechaEntrada) BETWEEN p_fecha_inicio AND p_fecha_fin
    AND (p_estado = '' OR r.Estado = p_estado)
    GROUP BY DATE(r.FechaEntrada)
    ORDER BY MIN(r.FechaEntrada) ASC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_R_RECEPCIONES_GRAFICO_MENSUAL` (IN `p_fecha_inicio` DATE, IN `p_fecha_fin` DATE, IN `p_estado` VARCHAR(10))   BEGIN
    SELECT 
        DATE_FORMAT(r.FechaEntrada, '%b %Y') AS periodo,
        COUNT(r.IdRecepcion) AS recepciones,
        COALESCE(SUM(r.TotalPagado), 0) AS ingresos
    FROM recepcion r
    WHERE DATE(r.FechaEntrada) BETWEEN p_fecha_inicio AND p_fecha_fin
    AND (p_estado = '' OR r.Estado = p_estado)
    GROUP BY DATE_FORMAT(r.FechaEntrada, '%Y-%m')
    ORDER BY MIN(r.FechaEntrada) ASC;



END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_R_RECEPCIONES_GRAFICO_PISOS` (IN `p_fecha_inicio` DATE, IN `p_fecha_fin` DATE, IN `p_estado` VARCHAR(10))   BEGIN
    SELECT 
        p.Descripcion AS piso,
        COUNT(r.IdRecepcion) AS cantidad
    FROM recepcion r
    INNER JOIN habitacion h ON r.IdHabitacion = h.IdHabitacion
    INNER JOIN piso p ON h.IdPiso = p.IdPiso
    WHERE DATE(r.FechaEntrada) BETWEEN p_fecha_inicio AND p_fecha_fin
    AND (p_estado = '' OR r.Estado = p_estado)
    GROUP BY p.IdPiso, p.Descripcion
    ORDER BY cantidad DESC;



END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_R_RECEPCIONES_GRAFICO_SEMANAL` (IN `p_fecha_inicio` DATE, IN `p_fecha_fin` DATE, IN `p_estado` VARCHAR(10))   BEGIN
    SELECT 
        CONCAT('Sem ', WEEK(r.FechaEntrada, 1)) AS periodo,
        COUNT(r.IdRecepcion) AS recepciones,
        COALESCE(SUM(r.TotalPagado), 0) AS ingresos
    FROM recepcion r
    WHERE DATE(r.FechaEntrada) BETWEEN p_fecha_inicio AND p_fecha_fin
    AND (p_estado = '' OR r.Estado = p_estado)
    GROUP BY YEARWEEK(r.FechaEntrada, 1)
    ORDER BY MIN(r.FechaEntrada) ASC;


END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_R_RECEPCIONES_HABITACIONES_TOP` (IN `p_fecha_inicio` DATE, IN `p_fecha_fin` DATE, IN `p_estado` VARCHAR(10))   BEGIN
    SELECT 
        h.Numero AS NumeroHabitacion,
        cat.Descripcion AS Categoria,
        COUNT(r.IdRecepcion) AS TotalRecepciones,
        COALESCE(SUM(r.TotalPagado), 0) AS Ingresos
    FROM recepcion r
    INNER JOIN habitacion h ON r.IdHabitacion = h.IdHabitacion
    LEFT JOIN categoria cat ON h.IdCategoria = cat.IdCategoria
    WHERE DATE(r.FechaEntrada) BETWEEN p_fecha_inicio AND p_fecha_fin
    AND (p_estado = '' OR r.Estado = p_estado)
    GROUP BY h.IdHabitacion, h.Numero, cat.Descripcion
    ORDER BY TotalRecepciones DESC
    LIMIT 10;




END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_R_RECEPCIONES_LISTA` (IN `p_fecha_inicio` DATE, IN `p_fecha_fin` DATE, IN `p_estado` VARCHAR(10))   BEGIN
    SELECT 
        r.IdRecepcion,
        CONCAT(c.Nombre, ' ', c.Apellido) AS NombreCliente,
        h.Numero AS NumeroHabitacion,
        p.Descripcion AS NombrePiso,
        r.FechaEntrada,
        r.FechaSalida,
        r.FechaSalidaConfirmacion,
        COALESCE(t.Descripcion, 'Sin tarifa') AS NombreTarifa,
        r.TotalPagado,
        r.Estado
    FROM recepcion r
    INNER JOIN cliente c ON r.IdCliente = c.IdCliente
    INNER JOIN habitacion h ON r.IdHabitacion = h.IdHabitacion
    INNER JOIN piso p ON h.IdPiso = p.IdPiso
    LEFT JOIN tarifa t ON r.IdTarifa = t.IdTarifa
    WHERE DATE(r.FechaEntrada) BETWEEN p_fecha_inicio AND p_fecha_fin
    AND (p_estado = '' OR r.Estado = p_estado)
    ORDER BY r.FechaEntrada DESC;
    
    END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_R_RECEPCIONES_POR_PISO` (IN `p_fecha_inicio` DATE, IN `p_fecha_fin` DATE, IN `p_estado` VARCHAR(10))   BEGIN
    SELECT 
        p.Descripcion AS NombrePiso,
        (SELECT COUNT(*) FROM habitacion WHERE IdPiso = p.IdPiso AND Estado = 1) AS TotalHabitaciones,
        COUNT(r.IdRecepcion) AS TotalRecepciones,
        COALESCE(SUM(r.TotalPagado), 0) AS Ingresos
    FROM piso p
    LEFT JOIN habitacion h ON p.IdPiso = h.IdPiso AND h.Estado = 1
    LEFT JOIN recepcion r ON h.IdHabitacion = r.IdHabitacion 
        AND DATE(r.FechaEntrada) BETWEEN p_fecha_inicio AND p_fecha_fin
        AND (p_estado = '' OR r.Estado = p_estado)
    WHERE p.Estado = 1
    GROUP BY p.IdPiso, p.Descripcion
    ORDER BY TotalRecepciones DESC;


END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_R_RECEPCIONES_POR_TARIFA` (IN `p_fecha_inicio` DATE, IN `p_fecha_fin` DATE, IN `p_estado` VARCHAR(10))   BEGIN
    SELECT 
        COALESCE(t.Descripcion, 'Sin tarifa') AS NombreTarifa,
        COUNT(r.IdRecepcion) AS TotalRecepciones,
        COALESCE(SUM(r.TotalPagado), 0) AS Total
    FROM recepcion r
    LEFT JOIN tarifa t ON r.IdTarifa = t.IdTarifa
    WHERE DATE(r.FechaEntrada) BETWEEN p_fecha_inicio AND p_fecha_fin
    AND (p_estado = '' OR r.Estado = p_estado)
    GROUP BY r.IdTarifa, t.Descripcion
    ORDER BY Total DESC;


END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_R_RECEPCIONES_RESUMEN` (IN `p_fecha_inicio` DATE, IN `p_fecha_fin` DATE, IN `p_estado` VARCHAR(10))   BEGIN
    SELECT 
        COUNT(r.IdRecepcion) AS total_recepciones,
        SUM(CASE WHEN r.Estado = 1 THEN 1 ELSE 0 END) AS recepciones_activas,
        COALESCE(SUM(r.TotalPagado), 0) AS ingresos_hospedaje,
        ROUND(AVG(TIMESTAMPDIFF(HOUR, r.FechaEntrada, 
            COALESCE(r.FechaSalidaConfirmacion, r.FechaSalida, NOW()))), 1) AS estancia_promedio
    FROM recepcion r
    WHERE DATE(r.FechaEntrada) BETWEEN p_fecha_inicio AND p_fecha_fin
    AND (p_estado = '' OR r.Estado = p_estado);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_R_RECEPCIONES_VARIACION` (IN `p_fecha_inicio` DATE, IN `p_fecha_fin` DATE)   BEGIN
    SELECT COUNT(*) AS total_anterior 
    FROM recepcion 
    WHERE DATE(FechaEntrada) BETWEEN p_fecha_inicio AND p_fecha_fin;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_R_VENTAS_GRAFICO_DIARIO` (IN `p_fecha_inicio` DATE, IN `p_fecha_fin` DATE, IN `p_estado` VARCHAR(20))   BEGIN
    SELECT 
        DATE_FORMAT(v.FechaCreacion, '%d/%m') AS periodo,
        COALESCE(SUM(v.Total), 0) AS total,
        COUNT(v.IdVenta) AS cantidad
    FROM venta v
    WHERE DATE(v.FechaCreacion) BETWEEN p_fecha_inicio AND p_fecha_fin 
    AND v.Estado != 'BORRADOR' AND v.Estado != 'ANULADO'
    AND (p_estado = '' OR v.Estado = p_estado)
    GROUP BY DATE(v.FechaCreacion)
    ORDER BY MIN(v.FechaCreacion) ASC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_R_VENTAS_GRAFICO_MENSUAL` (IN `p_fecha_inicio` DATE, IN `p_fecha_fin` DATE, IN `p_estado` VARCHAR(20))   BEGIN
    SELECT 
        DATE_FORMAT(v.FechaCreacion, '%b %Y') AS periodo,
        COALESCE(SUM(v.Total), 0) AS total,
        COUNT(v.IdVenta) AS cantidad
    FROM venta v
    WHERE DATE(v.FechaCreacion) BETWEEN p_fecha_inicio AND p_fecha_fin 
    AND v.Estado != 'BORRADOR' AND v.Estado != 'ANULADO'
    AND (p_estado = '' OR v.Estado = p_estado)
    GROUP BY DATE_FORMAT(v.FechaCreacion, '%Y-%m')
    ORDER BY MIN(v.FechaCreacion) ASC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_R_VENTAS_GRAFICO_SEMANAL` (IN `p_fecha_inicio` DATE, IN `p_fecha_fin` DATE, IN `p_estado` VARCHAR(20))   BEGIN
    SELECT 
        CONCAT('Sem ', WEEK(v.FechaCreacion, 1)) AS periodo,
        COALESCE(SUM(v.Total), 0) AS total,
        COUNT(v.IdVenta) AS cantidad
    FROM venta v
    WHERE DATE(v.FechaCreacion) BETWEEN p_fecha_inicio AND p_fecha_fin 
    AND v.Estado != 'BORRADOR' AND v.Estado != 'ANULADO'
    AND (p_estado = '' OR v.Estado = p_estado)
    GROUP BY YEARWEEK(v.FechaCreacion, 1)
    ORDER BY MIN(v.FechaCreacion) ASC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_R_VENTAS_LISTA` (IN `p_fecha_inicio` DATE, IN `p_fecha_fin` DATE, IN `p_estado` VARCHAR(20))   BEGIN
    SELECT 
        v.IdVenta,
        v.FechaCreacion AS FechaVenta,
        v.Total,
        v.Estado,
        h.Numero AS NumeroHabitacion,
        CONCAT(c.Nombre, ' ', c.Apellido) AS NombreCliente,
        CONCAT(u.Nombre, ' ', u.Apellido) AS NombreEmpleado,
        (SELECT COUNT(*) FROM detalle_venta WHERE IdVenta = v.IdVenta) AS CantidadProductos
    FROM venta v
    INNER JOIN recepcion r ON v.IdRecepcion = r.IdRecepcion
    INNER JOIN habitacion h ON r.IdHabitacion = h.IdHabitacion
    LEFT JOIN cliente c ON r.IdCliente = c.IdCliente
    LEFT JOIN boleta b ON r.IdRecepcion = b.rec_id
    LEFT JOIN usuario u ON b.bol_usuario_registro = u.IdUsuario
    WHERE DATE(v.FechaCreacion) BETWEEN p_fecha_inicio AND p_fecha_fin 
    AND v.Estado != 'BORRADOR'
    AND (p_estado = '' OR v.Estado = p_estado)
    ORDER BY v.FechaCreacion DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_R_VENTAS_POR_EMPLEADO` (IN `p_fecha_inicio` DATE, IN `p_fecha_fin` DATE, IN `p_estado` VARCHAR(20))   BEGIN
    SELECT 
        COALESCE(CONCAT(u.Nombre, ' ', u.Apellido), 'No registrado') AS NombreEmpleado,
        COUNT(v.IdVenta) AS CantidadVentas,
        COALESCE(SUM(v.Total), 0) AS TotalVentas
    FROM venta v
    INNER JOIN recepcion r ON v.IdRecepcion = r.IdRecepcion
    LEFT JOIN boleta b ON r.IdRecepcion = b.rec_id
    LEFT JOIN usuario u ON b.bol_usuario_registro = u.IdUsuario
    WHERE DATE(v.FechaCreacion) BETWEEN p_fecha_inicio AND p_fecha_fin 
    AND v.Estado != 'BORRADOR' AND v.Estado != 'ANULADO'
    AND (p_estado = '' OR v.Estado = p_estado)
    GROUP BY u.IdUsuario
    ORDER BY TotalVentas DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_R_VENTAS_RESUMEN` (IN `p_fecha_inicio` DATE, IN `p_fecha_fin` DATE, IN `p_estado` VARCHAR(20))   BEGIN
    SELECT 
        COALESCE(SUM(v.Total), 0) AS total_ventas,
        COUNT(v.IdVenta) AS cantidad_ventas,
        COALESCE(AVG(v.Total), 0) AS ticket_promedio,
        (
            SELECT COALESCE(SUM(dv.Cantidad), 0) 
            FROM detalle_venta dv
            INNER JOIN venta v2 ON dv.IdVenta = v2.IdVenta
            WHERE DATE(v2.FechaCreacion) BETWEEN p_fecha_inicio AND p_fecha_fin 
            AND v2.Estado != 'BORRADOR' AND v2.Estado != 'ANULADO'
            AND (p_estado = '' OR v2.Estado = p_estado)
        ) AS productos_vendidos
    FROM venta v
    WHERE DATE(v.FechaCreacion) BETWEEN p_fecha_inicio AND p_fecha_fin 
    AND v.Estado != 'BORRADOR' AND v.Estado != 'ANULADO'
    AND (p_estado = '' OR v.Estado = p_estado);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_R_VENTAS_TOP_PRODUCTOS` (IN `p_fecha_inicio` DATE, IN `p_fecha_fin` DATE, IN `p_estado` VARCHAR(20))   BEGIN
    SELECT 
        p.Nombre AS NombreProducto,
        SUM(dv.Cantidad) AS CantidadTotal,
        SUM(dv.SubTotal) AS TotalVendido
    FROM detalle_venta dv
    INNER JOIN producto p ON dv.IdProducto = p.IdProducto
    INNER JOIN venta v ON dv.IdVenta = v.IdVenta
    WHERE DATE(v.FechaCreacion) BETWEEN p_fecha_inicio AND p_fecha_fin 
    AND v.Estado != 'BORRADOR' AND v.Estado != 'ANULADO'
    AND (p_estado = '' OR v.Estado = p_estado)
    GROUP BY p.IdProducto, p.Nombre
    ORDER BY CantidadTotal DESC
    LIMIT 10;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_R_VENTAS_VARIACION` (IN `p_fecha_inicio` DATE, IN `p_fecha_fin` DATE)   BEGIN
    SELECT COALESCE(SUM(v.Total), 0) AS total_anterior
    FROM venta v
    WHERE DATE(v.FechaCreacion) BETWEEN p_fecha_inicio AND p_fecha_fin 
    AND v.Estado != 'BORRADOR' AND v.Estado != 'ANULADO';
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_S_TARIFA_VERIFICAR_EXISTENTE_01` (IN `TAR_DESC` VARCHAR(100), IN `EXCLUDE_ID` INT)   BEGIN
    SELECT COUNT(1) AS cnt
    FROM tarifa
    WHERE TRIM(UPPER(Descripcion)) = TRIM(UPPER(TAR_DESC))
      AND (EXCLUDE_ID IS NULL OR IdTarifa <> EXCLUDE_ID);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_U_CATEGORIA_01` (IN `CAT_ID` INT, IN `CAT_NOM` VARCHAR(150))   BEGIN
    UPDATE categoria 
    SET Descripcion = CAT_NOM
    WHERE IdCategoria = CAT_ID AND Estado = 1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_U_ESTADO_HABITACION_01` (IN `EST_HAB_ID` INT, IN `EST_HAB_NOM` VARCHAR(50))   BEGIN
    UPDATE estado_habitacion 
    SET Descripcion = EST_HAB_NOM 
    WHERE IdEstadoHabitacion = EST_HAB_ID AND Estado = 1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_U_HABITACION_01` (IN `HAB_ID` INT, IN `HAB_NUM` VARCHAR(50), IN `HAB_DET` VARCHAR(500), IN `HAB_EST_ID` INT, IN `HAB_PISO_ID` INT, IN `HAB_CAT_ID` INT)   BEGIN
    UPDATE habitacion 
    SET Numero = HAB_NUM,
        Detalle = HAB_DET,
        IdEstadoHabitacion = HAB_EST_ID,
        IdPiso = HAB_PISO_ID,
        IdCategoria = HAB_CAT_ID
    WHERE IdHabitacion = HAB_ID AND Estado = 1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_U_HABITACION_TARIFA_01` (IN `HAB_TAR_ID` INT, IN `FECHA_INICIO` DATETIME, IN `FECHA_FIN` DATETIME)   BEGIN
    UPDATE habitacion_tarifa
    SET fecha_inicio = FECHA_INICIO,
        fecha_fin = FECHA_FIN
    WHERE id_habitacion_tarifa = HAB_TAR_ID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_U_MENU_01` (IN `MEND_ID` INT)   BEGIN
    UPDATE TD_MENU
    SET MEND_PERMI = 'Si'
    WHERE TD_MENU.MEND_ID = MEND_ID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_U_MENU_02` (IN `MEND_ID` INT)   BEGIN
    UPDATE TD_MENU
    SET MEND_PERMI = 'No'
    WHERE TD_MENU.MEND_ID = MEND_ID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_U_PISO_01` (IN `PISO_ID` INT, IN `PISO_NOM` VARCHAR(50))   BEGIN
    UPDATE piso 
    SET Descripcion = PISO_NOM 
    WHERE IdPiso = PISO_ID AND Estado = 1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_U_PRODUCTO_01` (IN `p_IdProducto` INT, IN `p_Nombre` VARCHAR(50), IN `p_Detalle` VARCHAR(100), IN `p_Precio` DECIMAL(10,2), IN `p_Cantidad` INT)   BEGIN
  UPDATE producto
  SET Nombre = p_Nombre,
      Detalle = p_Detalle,
      Precio = p_Precio,
      Cantidad = p_Cantidad
  WHERE IdProducto = p_IdProducto;
  SELECT ROW_COUNT() AS affected;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_U_ROL_01` (IN `ROL_ID` INT, IN `ROL_NOM` VARCHAR(50))   BEGIN
    UPDATE rol 
    SET Descripcion = ROL_NOM 
    WHERE IdRol = ROL_ID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_U_TARIFA_01` (IN `TAR_ID` INT, IN `TAR_DESC` VARCHAR(100), IN `TAR_PRECIO` DECIMAL(10,2))   BEGIN
    UPDATE tarifa
    SET Descripcion = TAR_DESC,
        Precio = TAR_PRECIO
    WHERE IdTarifa = TAR_ID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_U_TARIFA_CAMBIAR_ESTADO_01` (IN `TAR_ID` INT, IN `NUEVO_ESTADO` TINYINT)   BEGIN
    UPDATE tarifa
    SET Estado = NUEVO_ESTADO
    WHERE IdTarifa = TAR_ID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_U_USUARIO_01` (IN `USU_ID` INT, IN `USU_NOM` VARCHAR(50), IN `USU_APE` VARCHAR(50), IN `USU_DNI` VARCHAR(15), IN `USU_CORREO` VARCHAR(100), IN `USU_PASS` VARCHAR(255), IN `ROL_ID` INT)   BEGIN
    UPDATE usuario 
    SET Nombre = USU_NOM,
        Apellido = USU_APE,
        DNI = USU_DNI,
        Correo = USU_CORREO,
        Pass = CASE 
            WHEN USU_PASS IS NOT NULL AND USU_PASS != '' THEN USU_PASS 
            ELSE Pass 
        END,
        IdRol = ROL_ID
    WHERE IdUsuario = USU_ID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_U_USUARIO_PASS_01` (IN `USU_ID` INT, IN `USU_PASS` VARCHAR(255))   BEGIN
    UPDATE usuario 
    SET Pass = USU_PASS
    WHERE IdUsuario = USU_ID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_U_VENTA_01` (IN `p_VENT_ID` INT)   BEGIN
  DECLARE subtotal DECIMAL(10,2) DEFAULT 0;
  DECLARE igv DECIMAL(10,2) DEFAULT 0;
  DECLARE total DECIMAL(10,2) DEFAULT 0;

  SELECT COALESCE(SUM(SubTotal), 0) INTO subtotal
  FROM detalle_venta
  WHERE IdVenta = p_VENT_ID;

  SET igv = ROUND(subtotal * 0.18, 2);
  SET total = subtotal + igv;

  UPDATE venta SET Total = total WHERE IdVenta = p_VENT_ID;

  SELECT
    subtotal AS VENT_SUBTOTAL,
    igv AS VENT_IGV,
    total AS VENT_TOTAL;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `boleta`
--

CREATE TABLE `boleta` (
  `bol_id` int(11) NOT NULL,
  `rec_id` int(11) NOT NULL COMMENT 'ID de la recepción',
  `bol_tipo` varchar(2) NOT NULL DEFAULT '03' COMMENT '03=Boleta',
  `bol_serie` varchar(10) NOT NULL COMMENT 'Serie del comprobante (B001)',
  `bol_correlativo` varchar(20) NOT NULL COMMENT 'Número correlativo',
  `bol_fecha_emision` datetime NOT NULL,
  `bol_fecha_vencimiento` date DEFAULT NULL,
  `bol_cliente_tipo_doc` varchar(2) DEFAULT '1' COMMENT '1=DNI',
  `bol_cliente_num_doc` varchar(20) DEFAULT NULL,
  `bol_cliente_razon_social` varchar(200) DEFAULT NULL,
  `bol_cliente_direccion` varchar(200) DEFAULT NULL,
  `bol_subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `bol_igv` decimal(10,2) NOT NULL DEFAULT 0.00,
  `bol_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `bol_estado` varchar(20) NOT NULL DEFAULT 'EMITIDA' COMMENT 'EMITIDA, ANULADA, RECHAZADA, ACEPTADA',
  `bol_metodo_pago` varchar(50) DEFAULT NULL COMMENT 'EFECTIVO, TARJETA, TRANSFERENCIA',
  `bol_observaciones` text DEFAULT NULL COMMENT 'Descripción y observaciones SUNAT',
  `bol_hash` varchar(255) DEFAULT NULL COMMENT 'Hash del comprobante',
  `bol_xml` longtext DEFAULT NULL COMMENT 'XML firmado',
  `bol_cdr` longtext DEFAULT NULL COMMENT 'CDR (Constancia de Recepción) en base64',
  `bol_pdf_ruta` varchar(255) DEFAULT NULL COMMENT 'Ruta del PDF generado',
  `bol_fecha_registro` datetime DEFAULT current_timestamp(),
  `bol_usuario_registro` int(11) DEFAULT NULL,
  `bol_fecha_modificacion` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `bol_xml_ruta` varchar(255) DEFAULT NULL,
  `bol_cdr_ruta` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `boleta`
--

INSERT INTO `boleta` (`bol_id`, `rec_id`, `bol_tipo`, `bol_serie`, `bol_correlativo`, `bol_fecha_emision`, `bol_fecha_vencimiento`, `bol_cliente_tipo_doc`, `bol_cliente_num_doc`, `bol_cliente_razon_social`, `bol_cliente_direccion`, `bol_subtotal`, `bol_igv`, `bol_total`, `bol_estado`, `bol_metodo_pago`, `bol_observaciones`, `bol_hash`, `bol_xml`, `bol_cdr`, `bol_pdf_ruta`, `bol_fecha_registro`, `bol_usuario_registro`, `bol_fecha_modificacion`, `bol_xml_ruta`, `bol_cdr_ruta`) VALUES
(49, 79, '03', 'B001', '00000001', '2025-12-06 23:55:03', NULL, '1', '20552103816', 'AGROLIGHT PERU S.A.C.', 'PJ. JORGE BASADRE NRO 158 URB. POP LA UNIVERSAL 2DA ET. ', 105.08, 18.92, 124.00, 'ACEPTADA', 'EFECTIVO', 'La Boleta numero B001-00000001, ha sido aceptada', 'b3Azm/g6gVrADzZ6RS7seHMAewg=', '<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<Invoice xmlns=\"urn:oasis:names:specification:ubl:schema:xsd:Invoice-2\" xmlns:cac=\"urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2\" xmlns:cbc=\"urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2\" xmlns:ds=\"http://www.w3.org/2000/09/xmldsig#\" xmlns:ext=\"urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2\"><ext:UBLExtensions><ext:UBLExtension><ext:ExtensionContent><ds:Signature Id=\"GreenterSign\"><ds:SignedInfo><ds:CanonicalizationMethod Algorithm=\"http://www.w3.org/TR/2001/REC-xml-c14n-20010315\"/><ds:SignatureMethod Algorithm=\"http://www.w3.org/2000/09/xmldsig#rsa-sha1\"/><ds:Reference URI=\"\"><ds:Transforms><ds:Transform Algorithm=\"http://www.w3.org/2000/09/xmldsig#enveloped-signature\"/></ds:Transforms><ds:DigestMethod Algorithm=\"http://www.w3.org/2000/09/xmldsig#sha1\"/><ds:DigestValue>b3Azm/g6gVrADzZ6RS7seHMAewg=</ds:DigestValue></ds:Reference></ds:SignedInfo><ds:SignatureValue>EUsiZ/w9DRqKfph85fgid8h3r4EM8rx8H3UbVqbZmmeEniRzVpFDJ5+ijahpGqNH1AzAPc2EhQgs38pPqZAxT1A+axh8VGCewouv1kRjpazmX/nZVUoOBgaoryGvHrzYLTokZU1j/r1R0QMJILcvNLvFdh9YX3kB1Jmp1sIUsFxVhqoReVoxpnCFGBVJlXinA1LK6/SxF6QMsUPTtgWnAq6cj2LNBqCsk/xfS+fsXEV0rmxFBkRSJDHTDWHI0b0Yx2OLrZUbPATT8/Gyt3G4CtBjhdm80E+mQAKM2gaIRQWRodgNUi7ANSK/NthCkvY5QhdgKXCrKkFYklMzDajmcA==</ds:SignatureValue><ds:KeyInfo><ds:X509Data><ds:X509Certificate>MIIFCDCCA/CgAwIBAgIJAN7i98Vb2bM7MA0GCSqGSIb3DQEBCwUAMIIBDTEbMBkGCgmSJomT8ixkARkWC0xMQU1BLlBFIFNBMQswCQYDVQQGEwJQRTENMAsGA1UECAwETElNQTENMAsGA1UEBwwETElNQTEYMBYGA1UECgwPVFUgRU1QUkVTQSBTLkEuMUUwQwYDVQQLDDxETkkgOTk5OTk5OSBSVUMgMjAwMDAwMDAwMDEgLSBDRVJUSUZJQ0FETyBQQVJBIERFTU9TVFJBQ0nDk04xRDBCBgNVBAMMO05PTUJSRSBSRVBSRVNFTlRBTlRFIExFR0FMIC0gQ0VSVElGSUNBRE8gUEFSQSBERU1PU1RSQUNJw5NOMRwwGgYJKoZIhvcNAQkBFg1kZW1vQGxsYW1hLnBlMB4XDTI1MTEyNTAxMDgxMVoXDTI3MTEyNTAxMDgxMVowggENMRswGQYKCZImiZPyLGQBGRYLTExBTUEuUEUgU0ExCzAJBgNVBAYTAlBFMQ0wCwYDVQQIDARMSU1BMQ0wCwYDVQQHDARMSU1BMRgwFgYDVQQKDA9UVSBFTVBSRVNBIFMuQS4xRTBDBgNVBAsMPEROSSA5OTk5OTk5IFJVQyAyMDAwMDAwMDAwMSAtIENFUlRJRklDQURPIFBBUkEgREVNT1NUUkFDScOTTjFEMEIGA1UEAww7Tk9NQlJFIFJFUFJFU0VOVEFOVEUgTEVHQUwgLSBDRVJUSUZJQ0FETyBQQVJBIERFTU9TVFJBQ0nDk04xHDAaBgkqhkiG9w0BCQEWDWRlbW9AbGxhbWEucGUwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQClNF3nRYGxHZ7GLh1QOXV0IYVOZ6O0dKLu7XoWVRji/VH/zeMNEnOcoVP6edyMnEigQzL8pDnpoNKZZUvEh1eOrb9lfrwZ54xZFUewxEaXDNSkooic0vxXKGfaTi+jBzD2ianAZMuuwvr7zCO4skPU4AYw1Lz9poZR+h+PPAN1NcMvSrgg07Jx7kcqhGFIjKozBPImOQhAr4K1EkdhLAAf/Ns3Cg5KAaCxYriTH5lKdXoJHw/jHRebGnGY7QamQienRnWqHD+NZUMi8voWfTmmcTDfkUG2pwZM2+EvidFOdfJ8skWj+pAcekmE+PyXdW8zFhXQNII38NavSeD9KXrnAgMBAAGjZzBlMB0GA1UdDgQWBBSrnc7Ehh9eZvp6QNAyVfYv6a8vfjAfBgNVHSMEGDAWgBSrnc7Ehh9eZvp6QNAyVfYv6a8vfjATBgNVHSUEDDAKBggrBgEFBQcDATAOBgNVHQ8BAf8EBAMCB4AwDQYJKoZIhvcNAQELBQADggEBAIG/CWn7oEzc+1W6nL5QQ8TTb5MiK1cp2OVQjj+2bx0Ye6vP2lFwYa51qB6/1v26FvaqumBQs/RUp48k33Qre9bHFhN9yVxn7gf/tQNK6p587IRqfQMfl34m/l1o09h9MoPKBekqyyV99ZrJGnLIWqaImvh964WtITwS1D2/M8ks5h0xFaCqT5d0jtnMsn9/IzaXw9c2eHXCApSwGF2lYzk2CyAqqZm3TioE5CYgXB5YCqdEpaJR9PpHAKLkm/EppzNaazJvP9vosb4sCRaYoWUWBmR+mmDjbbjxa6Nexel4G7EagKpXzHp1t4jho0NnNAFi7miU4wnRd9NPyJTx7xA=</ds:X509Certificate></ds:X509Data></ds:KeyInfo></ds:Signature></ext:ExtensionContent></ext:UBLExtension></ext:UBLExtensions><cbc:UBLVersionID>2.1</cbc:UBLVersionID><cbc:CustomizationID>2.0</cbc:CustomizationID><cbc:ID>B001-00000001</cbc:ID><cbc:IssueDate>2025-12-06</cbc:IssueDate><cbc:IssueTime>17:55:03</cbc:IssueTime><cbc:InvoiceTypeCode listID=\"0101\">03</cbc:InvoiceTypeCode><cbc:Note languageLocaleID=\"1000\"><![CDATA[CIENTO VEINTE Y CUATRO CON 0/100 SOLES]]></cbc:Note><cbc:DocumentCurrencyCode>PEN</cbc:DocumentCurrencyCode><cac:Signature><cbc:ID>20123456789</cbc:ID><cac:SignatoryParty><cac:PartyIdentification><cbc:ID>20123456789</cbc:ID></cac:PartyIdentification><cac:PartyName><cbc:Name><![CDATA[HOTEL LAS PALMERAS SAC]]></cbc:Name></cac:PartyName></cac:SignatoryParty><cac:DigitalSignatureAttachment><cac:ExternalReference><cbc:URI>#GREENTER-SIGN</cbc:URI></cac:ExternalReference></cac:DigitalSignatureAttachment></cac:Signature><cac:AccountingSupplierParty><cac:Party><cac:PartyIdentification><cbc:ID schemeID=\"6\">20123456789</cbc:ID></cac:PartyIdentification><cac:PartyName><cbc:Name><![CDATA[HOTEL LAS PALMERAS]]></cbc:Name></cac:PartyName><cac:PartyLegalEntity><cbc:RegistrationName><![CDATA[HOTEL LAS PALMERAS SAC]]></cbc:RegistrationName><cac:RegistrationAddress><cbc:ID>150101</cbc:ID><cbc:AddressTypeCode>0000</cbc:AddressTypeCode><cbc:CitySubdivisionName>-</cbc:CitySubdivisionName><cbc:CityName>LIMA</cbc:CityName><cbc:CountrySubentity>LIMA</cbc:CountrySubentity><cbc:District>LIMA</cbc:District><cac:AddressLine><cbc:Line><![CDATA[Av. Principal 123]]></cbc:Line></cac:AddressLine><cac:Country><cbc:IdentificationCode>PE</cbc:IdentificationCode></cac:Country></cac:RegistrationAddress></cac:PartyLegalEntity></cac:Party></cac:AccountingSupplierParty><cac:AccountingCustomerParty><cac:Party><cac:PartyIdentification><cbc:ID schemeID=\"1\">20552103816</cbc:ID></cac:PartyIdentification><cac:PartyLegalEntity><cbc:RegistrationName><![CDATA[AGROLIGHT PERU S.A.C.]]></cbc:RegistrationName></cac:PartyLegalEntity></cac:Party></cac:AccountingCustomerParty><cac:TaxTotal><cbc:TaxAmount currencyID=\"PEN\">18.92</cbc:TaxAmount><cac:TaxSubtotal><cbc:TaxableAmount currencyID=\"PEN\">105.08</cbc:TaxableAmount><cbc:TaxAmount currencyID=\"PEN\">18.92</cbc:TaxAmount><cac:TaxCategory><cac:TaxScheme><cbc:ID>1000</cbc:ID><cbc:Name>IGV</cbc:Name><cbc:TaxTypeCode>VAT</cbc:TaxTypeCode></cac:TaxScheme></cac:TaxCategory></cac:TaxSubtotal></cac:TaxTotal><cac:LegalMonetaryTotal><cbc:LineExtensionAmount currencyID=\"PEN\">105.08</cbc:LineExtensionAmount><cbc:TaxInclusiveAmount currencyID=\"PEN\">124.00</cbc:TaxInclusiveAmount><cbc:PayableAmount currencyID=\"PEN\">124.00</cbc:PayableAmount></cac:LegalMonetaryTotal><cac:InvoiceLine><cbc:ID>1</cbc:ID><cbc:InvoicedQuantity unitCode=\"NIU\">1</cbc:InvoicedQuantity><cbc:LineExtensionAmount currencyID=\"PEN\">50.85</cbc:LineExtensionAmount><cac:PricingReference><cac:AlternativeConditionPrice><cbc:PriceAmount currencyID=\"PEN\">60</cbc:PriceAmount><cbc:PriceTypeCode>01</cbc:PriceTypeCode></cac:AlternativeConditionPrice></cac:PricingReference><cac:TaxTotal><cbc:TaxAmount currencyID=\"PEN\">9.15</cbc:TaxAmount><cac:TaxSubtotal><cbc:TaxableAmount currencyID=\"PEN\">50.85</cbc:TaxableAmount><cbc:TaxAmount currencyID=\"PEN\">9.15</cbc:TaxAmount><cac:TaxCategory><cbc:Percent>18</cbc:Percent><cbc:TaxExemptionReasonCode>10</cbc:TaxExemptionReasonCode><cac:TaxScheme><cbc:ID>1000</cbc:ID><cbc:Name>IGV</cbc:Name><cbc:TaxTypeCode>VAT</cbc:TaxTypeCode></cac:TaxScheme></cac:TaxCategory></cac:TaxSubtotal></cac:TaxTotal><cac:Item><cbc:Description><![CDATA[Hospedaje - Habitación 107 (Tarifa Tematica (3horas))]]></cbc:Description><cac:SellersItemIdentification><cbc:ID>P001</cbc:ID></cac:SellersItemIdentification></cac:Item><cac:Price><cbc:PriceAmount currencyID=\"PEN\">50.8474576271</cbc:PriceAmount></cac:Price></cac:InvoiceLine><cac:InvoiceLine><cbc:ID>2</cbc:ID><cbc:InvoicedQuantity unitCode=\"NIU\">4</cbc:InvoicedQuantity><cbc:LineExtensionAmount currencyID=\"PEN\">54.24</cbc:LineExtensionAmount><cac:PricingReference><cac:AlternativeConditionPrice><cbc:PriceAmount currencyID=\"PEN\">16</cbc:PriceAmount><cbc:PriceTypeCode>01</cbc:PriceTypeCode></cac:AlternativeConditionPrice></cac:PricingReference><cac:TaxTotal><cbc:TaxAmount currencyID=\"PEN\">9.76</cbc:TaxAmount><cac:TaxSubtotal><cbc:TaxableAmount currencyID=\"PEN\">54.24</cbc:TaxableAmount><cbc:TaxAmount currencyID=\"PEN\">9.76</cbc:TaxAmount><cac:TaxCategory><cbc:Percent>18</cbc:Percent><cbc:TaxExemptionReasonCode>10</cbc:TaxExemptionReasonCode><cac:TaxScheme><cbc:ID>1000</cbc:ID><cbc:Name>IGV</cbc:Name><cbc:TaxTypeCode>VAT</cbc:TaxTypeCode></cac:TaxScheme></cac:TaxCategory></cac:TaxSubtotal></cac:TaxTotal><cac:Item><cbc:Description><![CDATA[Aji de Gallina]]></cbc:Description><cac:SellersItemIdentification><cbc:ID>P002</cbc:ID></cac:SellersItemIdentification></cac:Item><cac:Price><cbc:PriceAmount currencyID=\"PEN\">13.5593220339</cbc:PriceAmount></cac:Price></cac:InvoiceLine></Invoice>\n', 'UEsDBBQAAgAIAEmPhlsAAAAAAgAAAAAAAAAGAAAAZHVtbXkvAwBQSwMEFAACAAgASY+GW7ERrsCIBAAAwQ0AACIAAABSLTIwMTIzNDU2Nzg5LTAzLUIwMDEtMDAwMDAwMDEueG1stVddc+I2FH3fX6GBmW67W0e2wQRcwg6BTULDkpSQNO2bYivgXVtyJZlAf32vbGwMMbNhZ0p4EPeee+63NOl+WkUhWlIhA87OataJWUOUedwP2Pysdj+7MNq1T713XSLcfhyHgUcUAKdUxpxJisCYybNaIpjLiQyky0hEpStj6gXPG7CbPIWu9BY0Iu5K+u6ILXngUcOuZeYuEUcyVESyZaMrdSTdgEcRZ59XijJdBfgJlJQpuSX1nrwfIj0HuFdJSH6MsD+fCzonilaR+tCKhVKxi/HLy8vJS+OEizm2TdPEZgcDxpfBvJ6jJSdxgc8cyRNQaXlqqA+YsiUNeUxx4QScF2Z0JUOVgrVYGoT5hgogl8JJnqdMGFEH84ypSMrJ3ml0Va5WTrw6lKuFH7+M71KqHAssdBVXBA2KJCTCAK2gUjdf1npdmCD3/nxcDITMx7xCl0lKs8PgpHrdu2AOGSSiWJE39AXWTJtRf8Seee8dQt0BYZxBncLg37RWX6hacB/1wzkXgVpEB0tgmZoW8vIMz2qy+p+A1gOka1jDKXcR4ZtJzWYeqxFxQetCEkMuiGPZG8opfaYCbg+K7qcjXS4QgngmCJPPXEQyE5RF33W7U6J8GH1D5tFnro8kfUuBgBDvR94dBnMq1ZEVg4rUy3UqeB5ImNDeYPTYxI0lET6+9deErmfN8PFx9vEyuRpeRn/dX4vOPw+Di3loOf3pjer015aTXP3tf/x9EDtfE+KPx49X4VP/4g/7RniX48a3+fVl/+ysi8tedH9w0SAYNbw7a+WJyCw+3IpgCduHvtE1en9OFbmFVYXrjAr1HjGuUBJ/yGhKVt1ruk45u4+O2RkSRbKTtsp2HpgncA34yNuKNvyZQ2Ao8e8bp2wjKRMq7qgISFiWaOLj6Uu2KVfGO0miJyqOZ9uxLjvIw8XbyuCiWts6wrn6TsGvL59XItnrwlulRQ/Zmz4a9uwTs4tfSVPcIJGKR5vbBYRWDt1XpGgNOG05Zstqd9rN9gZaaHWSQ90i27Qdw7INszWzTl3Hcc3GBlpAthYzeC56pumm3xIslaew/I3f486wO8odeEqg/bddq70L3nATzy1VfZOLltzdT/qzUnYFkIv1LRFqncnS48iH5hSvWUFjm1YD/uyO42yJ8GGrXJFNoTZIT6VIMg3eQ+JDwcHyB4qERYJ9pYi3iNJJ0no9MoKRcHsnZJMzHfXqezXQssxRhRH+njNcUecJh241bfMUGehziIaTEfLpE0UwxVSgNvKIIB64ohKxJPqpbjcav4nA4xLwo8nFjYtS458Z97mLagfKuql7DS1JyAXgbNNxbMtstK1W7ZdNVbnKR4Eyn4r/p7240sGUejRYHuPTbjSd1mm782afFS6G3Et0Z/JlyGMpfqWLsukvuDiHl8wws4+V79FWvbNzA+7DMu8uWypLUUMqPRHEaXhjgs55CPeobjAVHO24+RUtCJKBzxHxaKyITzLOMkOeYDmLbW47Q12dRVG+KqusdkEcgPyN/WkZpfk6pkM7XnB1j3D1f2C9/wBQSwECAAAUAAIACABJj4ZbAAAAAAIAAAAAAAAABgAAAAAAAAAAAAAAAAAAAAAAZHVtbXkvUEsBAgAAFAACAAgASY+GW7ERrsCIBAAAwQ0AACIAAAAAAAAAAQAAAAAAJgAAAFItMjAxMjM0NTY3ODktMDMtQjAwMS0wMDAwMDAwMS54bWxQSwUGAAAAAAIAAgCEAAAA7gQAAAAA', 'storage/boletas/2025/12/B001-00000001_20251207_000156.pdf', '2025-12-06 17:55:03', 1, '2025-12-06 18:01:56', 'storage/xml/2025/12/20123456789-03-B001-00000001.xml', 'storage/cdr/2025/12/R-20123456789-03-B001-00000001.zip'),
(50, 80, '03', 'B001', '00000002', '2025-12-07 00:10:58', NULL, '1', '75434567', 'LISET SALY CONDORI TICONA', 'Calle Santa Isabel', 36.44, 6.56, 43.00, 'ACEPTADA', 'EFECTIVO', 'La Boleta numero B001-00000002, ha sido aceptada', 'UYLmhjgmF38v76grMPARpVqbY1k=', '<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<Invoice xmlns=\"urn:oasis:names:specification:ubl:schema:xsd:Invoice-2\" xmlns:cac=\"urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2\" xmlns:cbc=\"urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2\" xmlns:ds=\"http://www.w3.org/2000/09/xmldsig#\" xmlns:ext=\"urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2\"><ext:UBLExtensions><ext:UBLExtension><ext:ExtensionContent><ds:Signature Id=\"GreenterSign\"><ds:SignedInfo><ds:CanonicalizationMethod Algorithm=\"http://www.w3.org/TR/2001/REC-xml-c14n-20010315\"/><ds:SignatureMethod Algorithm=\"http://www.w3.org/2000/09/xmldsig#rsa-sha1\"/><ds:Reference URI=\"\"><ds:Transforms><ds:Transform Algorithm=\"http://www.w3.org/2000/09/xmldsig#enveloped-signature\"/></ds:Transforms><ds:DigestMethod Algorithm=\"http://www.w3.org/2000/09/xmldsig#sha1\"/><ds:DigestValue>UYLmhjgmF38v76grMPARpVqbY1k=</ds:DigestValue></ds:Reference></ds:SignedInfo><ds:SignatureValue>ZXObpjiVTLYAcafdXOAJPhEPiJjFMxou7i9cR8N4sQWakbUFn9B89Dp6u61bEu9BY2zI2EKTZQuB2D1xGzh7dKiEDHeuV13XSlQau1rYFuPOFGBybB+9hbZ8xX7+q2CLIXgTzrZP+D5bZFL8tpKaMSECYLl323zyEJMam7Dg05xjtCicYLOGfzqLx0VT5Hq0hLnezNY0kmSfS7dsWrq0nW1b2aw1Dujotpa0XMihDTwkrDPFFHk4CiVQ36pfxOC6A4FE+2VxNMYD9xZ+0e3rz8ytGNxHRuVGXwMGBufZA0xigtmIJYJuUVrdI0OufVLpn0/vhWz1qPFadSdG1SyU7A==</ds:SignatureValue><ds:KeyInfo><ds:X509Data><ds:X509Certificate>MIIFCDCCA/CgAwIBAgIJAN7i98Vb2bM7MA0GCSqGSIb3DQEBCwUAMIIBDTEbMBkGCgmSJomT8ixkARkWC0xMQU1BLlBFIFNBMQswCQYDVQQGEwJQRTENMAsGA1UECAwETElNQTENMAsGA1UEBwwETElNQTEYMBYGA1UECgwPVFUgRU1QUkVTQSBTLkEuMUUwQwYDVQQLDDxETkkgOTk5OTk5OSBSVUMgMjAwMDAwMDAwMDEgLSBDRVJUSUZJQ0FETyBQQVJBIERFTU9TVFJBQ0nDk04xRDBCBgNVBAMMO05PTUJSRSBSRVBSRVNFTlRBTlRFIExFR0FMIC0gQ0VSVElGSUNBRE8gUEFSQSBERU1PU1RSQUNJw5NOMRwwGgYJKoZIhvcNAQkBFg1kZW1vQGxsYW1hLnBlMB4XDTI1MTEyNTAxMDgxMVoXDTI3MTEyNTAxMDgxMVowggENMRswGQYKCZImiZPyLGQBGRYLTExBTUEuUEUgU0ExCzAJBgNVBAYTAlBFMQ0wCwYDVQQIDARMSU1BMQ0wCwYDVQQHDARMSU1BMRgwFgYDVQQKDA9UVSBFTVBSRVNBIFMuQS4xRTBDBgNVBAsMPEROSSA5OTk5OTk5IFJVQyAyMDAwMDAwMDAwMSAtIENFUlRJRklDQURPIFBBUkEgREVNT1NUUkFDScOTTjFEMEIGA1UEAww7Tk9NQlJFIFJFUFJFU0VOVEFOVEUgTEVHQUwgLSBDRVJUSUZJQ0FETyBQQVJBIERFTU9TVFJBQ0nDk04xHDAaBgkqhkiG9w0BCQEWDWRlbW9AbGxhbWEucGUwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQClNF3nRYGxHZ7GLh1QOXV0IYVOZ6O0dKLu7XoWVRji/VH/zeMNEnOcoVP6edyMnEigQzL8pDnpoNKZZUvEh1eOrb9lfrwZ54xZFUewxEaXDNSkooic0vxXKGfaTi+jBzD2ianAZMuuwvr7zCO4skPU4AYw1Lz9poZR+h+PPAN1NcMvSrgg07Jx7kcqhGFIjKozBPImOQhAr4K1EkdhLAAf/Ns3Cg5KAaCxYriTH5lKdXoJHw/jHRebGnGY7QamQienRnWqHD+NZUMi8voWfTmmcTDfkUG2pwZM2+EvidFOdfJ8skWj+pAcekmE+PyXdW8zFhXQNII38NavSeD9KXrnAgMBAAGjZzBlMB0GA1UdDgQWBBSrnc7Ehh9eZvp6QNAyVfYv6a8vfjAfBgNVHSMEGDAWgBSrnc7Ehh9eZvp6QNAyVfYv6a8vfjATBgNVHSUEDDAKBggrBgEFBQcDATAOBgNVHQ8BAf8EBAMCB4AwDQYJKoZIhvcNAQELBQADggEBAIG/CWn7oEzc+1W6nL5QQ8TTb5MiK1cp2OVQjj+2bx0Ye6vP2lFwYa51qB6/1v26FvaqumBQs/RUp48k33Qre9bHFhN9yVxn7gf/tQNK6p587IRqfQMfl34m/l1o09h9MoPKBekqyyV99ZrJGnLIWqaImvh964WtITwS1D2/M8ks5h0xFaCqT5d0jtnMsn9/IzaXw9c2eHXCApSwGF2lYzk2CyAqqZm3TioE5CYgXB5YCqdEpaJR9PpHAKLkm/EppzNaazJvP9vosb4sCRaYoWUWBmR+mmDjbbjxa6Nexel4G7EagKpXzHp1t4jho0NnNAFi7miU4wnRd9NPyJTx7xA=</ds:X509Certificate></ds:X509Data></ds:KeyInfo></ds:Signature></ext:ExtensionContent></ext:UBLExtension></ext:UBLExtensions><cbc:UBLVersionID>2.1</cbc:UBLVersionID><cbc:CustomizationID>2.0</cbc:CustomizationID><cbc:ID>B001-00000002</cbc:ID><cbc:IssueDate>2025-12-06</cbc:IssueDate><cbc:IssueTime>18:10:58</cbc:IssueTime><cbc:InvoiceTypeCode listID=\"0101\">03</cbc:InvoiceTypeCode><cbc:Note languageLocaleID=\"1000\"><![CDATA[CUARENTA Y TRES CON 0/100 SOLES]]></cbc:Note><cbc:DocumentCurrencyCode>PEN</cbc:DocumentCurrencyCode><cac:Signature><cbc:ID>20123456789</cbc:ID><cac:SignatoryParty><cac:PartyIdentification><cbc:ID>20123456789</cbc:ID></cac:PartyIdentification><cac:PartyName><cbc:Name><![CDATA[HOTEL LAS PALMERAS SAC]]></cbc:Name></cac:PartyName></cac:SignatoryParty><cac:DigitalSignatureAttachment><cac:ExternalReference><cbc:URI>#GREENTER-SIGN</cbc:URI></cac:ExternalReference></cac:DigitalSignatureAttachment></cac:Signature><cac:AccountingSupplierParty><cac:Party><cac:PartyIdentification><cbc:ID schemeID=\"6\">20123456789</cbc:ID></cac:PartyIdentification><cac:PartyName><cbc:Name><![CDATA[HOTEL LAS PALMERAS]]></cbc:Name></cac:PartyName><cac:PartyLegalEntity><cbc:RegistrationName><![CDATA[HOTEL LAS PALMERAS SAC]]></cbc:RegistrationName><cac:RegistrationAddress><cbc:ID>150101</cbc:ID><cbc:AddressTypeCode>0000</cbc:AddressTypeCode><cbc:CitySubdivisionName>-</cbc:CitySubdivisionName><cbc:CityName>LIMA</cbc:CityName><cbc:CountrySubentity>LIMA</cbc:CountrySubentity><cbc:District>LIMA</cbc:District><cac:AddressLine><cbc:Line><![CDATA[Av. Principal 123]]></cbc:Line></cac:AddressLine><cac:Country><cbc:IdentificationCode>PE</cbc:IdentificationCode></cac:Country></cac:RegistrationAddress></cac:PartyLegalEntity></cac:Party></cac:AccountingSupplierParty><cac:AccountingCustomerParty><cac:Party><cac:PartyIdentification><cbc:ID schemeID=\"1\">75434567</cbc:ID></cac:PartyIdentification><cac:PartyLegalEntity><cbc:RegistrationName><![CDATA[LISET SALY CONDORI TICONA]]></cbc:RegistrationName></cac:PartyLegalEntity></cac:Party></cac:AccountingCustomerParty><cac:TaxTotal><cbc:TaxAmount currencyID=\"PEN\">6.56</cbc:TaxAmount><cac:TaxSubtotal><cbc:TaxableAmount currencyID=\"PEN\">36.44</cbc:TaxableAmount><cbc:TaxAmount currencyID=\"PEN\">6.56</cbc:TaxAmount><cac:TaxCategory><cac:TaxScheme><cbc:ID>1000</cbc:ID><cbc:Name>IGV</cbc:Name><cbc:TaxTypeCode>VAT</cbc:TaxTypeCode></cac:TaxScheme></cac:TaxCategory></cac:TaxSubtotal></cac:TaxTotal><cac:LegalMonetaryTotal><cbc:LineExtensionAmount currencyID=\"PEN\">36.44</cbc:LineExtensionAmount><cbc:TaxInclusiveAmount currencyID=\"PEN\">43.00</cbc:TaxInclusiveAmount><cbc:PayableAmount currencyID=\"PEN\">43.00</cbc:PayableAmount></cac:LegalMonetaryTotal><cac:InvoiceLine><cbc:ID>1</cbc:ID><cbc:InvoicedQuantity unitCode=\"NIU\">1</cbc:InvoicedQuantity><cbc:LineExtensionAmount currencyID=\"PEN\">36.44</cbc:LineExtensionAmount><cac:PricingReference><cac:AlternativeConditionPrice><cbc:PriceAmount currencyID=\"PEN\">42.9992</cbc:PriceAmount><cbc:PriceTypeCode>01</cbc:PriceTypeCode></cac:AlternativeConditionPrice></cac:PricingReference><cac:TaxTotal><cbc:TaxAmount currencyID=\"PEN\">6.56</cbc:TaxAmount><cac:TaxSubtotal><cbc:TaxableAmount currencyID=\"PEN\">36.44</cbc:TaxableAmount><cbc:TaxAmount currencyID=\"PEN\">6.56</cbc:TaxAmount><cac:TaxCategory><cbc:Percent>18</cbc:Percent><cbc:TaxExemptionReasonCode>10</cbc:TaxExemptionReasonCode><cac:TaxScheme><cbc:ID>1000</cbc:ID><cbc:Name>IGV</cbc:Name><cbc:TaxTypeCode>VAT</cbc:TaxTypeCode></cac:TaxScheme></cac:TaxCategory></cac:TaxSubtotal></cac:TaxTotal><cac:Item><cbc:Description><![CDATA[Hospedaje - Habitación 105 (Tarifa Dia del Padre)]]></cbc:Description><cac:SellersItemIdentification><cbc:ID>P001</cbc:ID></cac:SellersItemIdentification></cac:Item><cac:Price><cbc:PriceAmount currencyID=\"PEN\">36.44</cbc:PriceAmount></cac:Price></cac:InvoiceLine></Invoice>\n', 'UEsDBBQAAgAIAMaRhlsAAAAAAgAAAAAAAAAGAAAAZHVtbXkvAwBQSwMEFAACAAgAxpGGW0CiFpw3BAAAKg0AACIAAABSLTIwMTIzNDU2Nzg5LTAzLUIwMDEtMDAwMDAwMDIueG1stVdRT+M4EH7fXxGVh5X2LjhJG0qj0FVLWalHQUBbFh5N4ra5Teys7bRlf/2NnSZNStDSlQ54cMbffDPzzdgW/tdtEhtrwkXE6EXLPrVaBqEBCyO6vGjNZ9/M89bX/icfc2+QpnEUYAnAByJSRgUxwJmKi1bGqcewiIRHcUKEJ1ISRIsd2MteYk8EK5JgbytCb0zXLAqI6bRydw/zIxkaMtmzka08ku6SJQmjV1tJqFIBPoGSUCn2pMFL8EekQ4AHjYT4zwgHyyUnSyxJE2kIrVhJmXoIbTab0037lPElcizLQlYPASYU0fKkQAuG0xKfBxKnsKXs2lEtEKFrErOUoDIIBC/dyFbEUoOVWZiYhqaMoJYySFGnyCiW79aZEp5Vi50qdFOtdkG8fa9WGz3dTKaaqsACC9mmDUnDRhZjbsIuJ0I1X7T6PkyQNx9OyoEQxZg37OWWyuxQWMm+P42WUEHGyyPygb7AMVNuJBzTBet/Mgz/ElNGQac4+qW1uiFyxUJjEC8Zj+QqeVcC21K0UFdgBnaHnnwHtBogpWELae4yww+TWp0iVzNhnJxwgU2xwq7t7CgfyIJwuD2IMX8YK7nACOYZx1QsGE9Ebqiafhu2JlExjKEpiuzz0EeSfkQgIESHmfujaEmEPFIxUOSkqlPJ84jjjPQvg/n35f2Gz0Ls9P4RP52r++v7m3nQu/o1+eul++1u6iKObp9nbCS68x9zPognQ/7zefT4L5p1Fra8cTfu02jQncyj9nV287yIx2hwceGjahTVH1Q2CEYN1WetOhG5x5c7Hq3h9Bk/yKvxeUgkvoOjCtcZ4fKzQZk0svRLTlPx8q/Jq+b0n1yrN8IS5yvllZ95YL6FayA0gr1px58HBIYK/6GzZhsLkRE+JTzCcdWiiI+nr/hqrpz3NkteCD+ereZdDVCki/bKoFKtvY6wbr5T0NvL541J9H14q5TpMX/Tx6O+c2r56I1V4y4zIVmyu13AaBfQww2NVoDumWudOeduu9vu5tByVxU5Ui1yLMc1bce0zmb2uWdbnnu+g5aQvccMnou+ZXn6rwLTdg0r3vgD7hxb26zBNYGK3/Hsdh2848aBV1F9V4uyTOe3g1mluhLI+Osd5vI1t+nlOITmlK9ZSeNYdht+nZ7r7onQ+17FRj6FykGvKpnkO+gAid5LDg5/JHFcFjiQEgerRE+S2lcjwymO93dCPjkP4/7JgQbKlgdqcEK/C4YOdVafhIaE/z9SosYADyQg0fqYmE674551z3sfjtkQYsSCTKlQDF6RS/mlh3KnJYQYwqthWvmPU8zsfrs235cshINTH2xt06gREQGPUp3eBBtDFsOdZVDIhjOjFuZvY4UNEYXMwAFJJQ5xzlllKAqsVrGvrTZAzVWU8jV55dpFaQT2D/bnzOy6Hd2gY9pTC4GaG4Sa/9Xp/wdQSwECAAAUAAIACADGkYZbAAAAAAIAAAAAAAAABgAAAAAAAAAAAAAAAAAAAAAAZHVtbXkvUEsBAgAAFAACAAgAxpGGW0CiFpw3BAAAKg0AACIAAAAAAAAAAQAAAAAAJgAAAFItMjAxMjM0NTY3ODktMDMtQjAwMS0wMDAwMDAwMi54bWxQSwUGAAAAAAIAAgCEAAAAnQQAAAAA', 'storage/boletas/2025/12/B001-00000002_20251207_001137.pdf', '2025-12-06 18:10:59', 1, '2025-12-06 18:11:37', 'storage/xml/2025/12/20123456789-03-B001-00000002.xml', 'storage/cdr/2025/12/R-20123456789-03-B001-00000002.zip'),
(51, 81, '03', 'B001', '00000003', '2025-12-07 00:14:26', NULL, '1', '20606422793', 'INTENTIONS ENGINEERING LEADERSHIP SERVICES SOCIEDA -', 'AV. SAN MARTIN NRO 625 DEP. 701 ', 406.78, 73.22, 480.00, 'ACEPTADA', 'EFECTIVO', 'La Boleta numero B001-00000003, ha sido aceptada', 'TKDw3SZz+NSJtyk3SraDKatquKM=', '<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<Invoice xmlns=\"urn:oasis:names:specification:ubl:schema:xsd:Invoice-2\" xmlns:cac=\"urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2\" xmlns:cbc=\"urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2\" xmlns:ds=\"http://www.w3.org/2000/09/xmldsig#\" xmlns:ext=\"urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2\"><ext:UBLExtensions><ext:UBLExtension><ext:ExtensionContent><ds:Signature Id=\"GreenterSign\"><ds:SignedInfo><ds:CanonicalizationMethod Algorithm=\"http://www.w3.org/TR/2001/REC-xml-c14n-20010315\"/><ds:SignatureMethod Algorithm=\"http://www.w3.org/2000/09/xmldsig#rsa-sha1\"/><ds:Reference URI=\"\"><ds:Transforms><ds:Transform Algorithm=\"http://www.w3.org/2000/09/xmldsig#enveloped-signature\"/></ds:Transforms><ds:DigestMethod Algorithm=\"http://www.w3.org/2000/09/xmldsig#sha1\"/><ds:DigestValue>TKDw3SZz+NSJtyk3SraDKatquKM=</ds:DigestValue></ds:Reference></ds:SignedInfo><ds:SignatureValue>WcLYRKs9+6vl3+fOBM5mgRDxJqdCKVn6Eu0gHE+IwW4Jn8Ke5azUi+erwTqX2gm6AUps0CMY2F+5HqS44yqFudbgqACsN4IE6F47JmjmWzPNpZs5HtzuoXJhWr0zOnztUH4PS0XsKjWtbYXa9N9B4BmHrPbyug6sjZ7MJqjDf1BzLWoFtBCKWlcy63bLknZBR09zqNRjUsfLI2kRz2KXT026DS9RXGKSucosBhtvtRbupoQLymOx3PKoQp0Q4fWNW/lw/aLFSK/9LnvdZoqsscMtPP13ZJ9Fioa3PO5AvbFBkQBIOpLHdf32TRKKvT7qI7AndW6Gj3JMTDUdxy7J/g==</ds:SignatureValue><ds:KeyInfo><ds:X509Data><ds:X509Certificate>MIIFCDCCA/CgAwIBAgIJAN7i98Vb2bM7MA0GCSqGSIb3DQEBCwUAMIIBDTEbMBkGCgmSJomT8ixkARkWC0xMQU1BLlBFIFNBMQswCQYDVQQGEwJQRTENMAsGA1UECAwETElNQTENMAsGA1UEBwwETElNQTEYMBYGA1UECgwPVFUgRU1QUkVTQSBTLkEuMUUwQwYDVQQLDDxETkkgOTk5OTk5OSBSVUMgMjAwMDAwMDAwMDEgLSBDRVJUSUZJQ0FETyBQQVJBIERFTU9TVFJBQ0nDk04xRDBCBgNVBAMMO05PTUJSRSBSRVBSRVNFTlRBTlRFIExFR0FMIC0gQ0VSVElGSUNBRE8gUEFSQSBERU1PU1RSQUNJw5NOMRwwGgYJKoZIhvcNAQkBFg1kZW1vQGxsYW1hLnBlMB4XDTI1MTEyNTAxMDgxMVoXDTI3MTEyNTAxMDgxMVowggENMRswGQYKCZImiZPyLGQBGRYLTExBTUEuUEUgU0ExCzAJBgNVBAYTAlBFMQ0wCwYDVQQIDARMSU1BMQ0wCwYDVQQHDARMSU1BMRgwFgYDVQQKDA9UVSBFTVBSRVNBIFMuQS4xRTBDBgNVBAsMPEROSSA5OTk5OTk5IFJVQyAyMDAwMDAwMDAwMSAtIENFUlRJRklDQURPIFBBUkEgREVNT1NUUkFDScOTTjFEMEIGA1UEAww7Tk9NQlJFIFJFUFJFU0VOVEFOVEUgTEVHQUwgLSBDRVJUSUZJQ0FETyBQQVJBIERFTU9TVFJBQ0nDk04xHDAaBgkqhkiG9w0BCQEWDWRlbW9AbGxhbWEucGUwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQClNF3nRYGxHZ7GLh1QOXV0IYVOZ6O0dKLu7XoWVRji/VH/zeMNEnOcoVP6edyMnEigQzL8pDnpoNKZZUvEh1eOrb9lfrwZ54xZFUewxEaXDNSkooic0vxXKGfaTi+jBzD2ianAZMuuwvr7zCO4skPU4AYw1Lz9poZR+h+PPAN1NcMvSrgg07Jx7kcqhGFIjKozBPImOQhAr4K1EkdhLAAf/Ns3Cg5KAaCxYriTH5lKdXoJHw/jHRebGnGY7QamQienRnWqHD+NZUMi8voWfTmmcTDfkUG2pwZM2+EvidFOdfJ8skWj+pAcekmE+PyXdW8zFhXQNII38NavSeD9KXrnAgMBAAGjZzBlMB0GA1UdDgQWBBSrnc7Ehh9eZvp6QNAyVfYv6a8vfjAfBgNVHSMEGDAWgBSrnc7Ehh9eZvp6QNAyVfYv6a8vfjATBgNVHSUEDDAKBggrBgEFBQcDATAOBgNVHQ8BAf8EBAMCB4AwDQYJKoZIhvcNAQELBQADggEBAIG/CWn7oEzc+1W6nL5QQ8TTb5MiK1cp2OVQjj+2bx0Ye6vP2lFwYa51qB6/1v26FvaqumBQs/RUp48k33Qre9bHFhN9yVxn7gf/tQNK6p587IRqfQMfl34m/l1o09h9MoPKBekqyyV99ZrJGnLIWqaImvh964WtITwS1D2/M8ks5h0xFaCqT5d0jtnMsn9/IzaXw9c2eHXCApSwGF2lYzk2CyAqqZm3TioE5CYgXB5YCqdEpaJR9PpHAKLkm/EppzNaazJvP9vosb4sCRaYoWUWBmR+mmDjbbjxa6Nexel4G7EagKpXzHp1t4jho0NnNAFi7miU4wnRd9NPyJTx7xA=</ds:X509Certificate></ds:X509Data></ds:KeyInfo></ds:Signature></ext:ExtensionContent></ext:UBLExtension></ext:UBLExtensions><cbc:UBLVersionID>2.1</cbc:UBLVersionID><cbc:CustomizationID>2.0</cbc:CustomizationID><cbc:ID>B001-00000003</cbc:ID><cbc:IssueDate>2025-12-06</cbc:IssueDate><cbc:IssueTime>18:14:26</cbc:IssueTime><cbc:InvoiceTypeCode listID=\"0101\">03</cbc:InvoiceTypeCode><cbc:Note languageLocaleID=\"1000\"><![CDATA[CUATROCIENTOS OCHENTA CON 0/100 SOLES]]></cbc:Note><cbc:DocumentCurrencyCode>PEN</cbc:DocumentCurrencyCode><cac:Signature><cbc:ID>20123456789</cbc:ID><cac:SignatoryParty><cac:PartyIdentification><cbc:ID>20123456789</cbc:ID></cac:PartyIdentification><cac:PartyName><cbc:Name><![CDATA[HOTEL LAS PALMERAS SAC]]></cbc:Name></cac:PartyName></cac:SignatoryParty><cac:DigitalSignatureAttachment><cac:ExternalReference><cbc:URI>#GREENTER-SIGN</cbc:URI></cac:ExternalReference></cac:DigitalSignatureAttachment></cac:Signature><cac:AccountingSupplierParty><cac:Party><cac:PartyIdentification><cbc:ID schemeID=\"6\">20123456789</cbc:ID></cac:PartyIdentification><cac:PartyName><cbc:Name><![CDATA[HOTEL LAS PALMERAS]]></cbc:Name></cac:PartyName><cac:PartyLegalEntity><cbc:RegistrationName><![CDATA[HOTEL LAS PALMERAS SAC]]></cbc:RegistrationName><cac:RegistrationAddress><cbc:ID>150101</cbc:ID><cbc:AddressTypeCode>0000</cbc:AddressTypeCode><cbc:CitySubdivisionName>-</cbc:CitySubdivisionName><cbc:CityName>LIMA</cbc:CityName><cbc:CountrySubentity>LIMA</cbc:CountrySubentity><cbc:District>LIMA</cbc:District><cac:AddressLine><cbc:Line><![CDATA[Av. Principal 123]]></cbc:Line></cac:AddressLine><cac:Country><cbc:IdentificationCode>PE</cbc:IdentificationCode></cac:Country></cac:RegistrationAddress></cac:PartyLegalEntity></cac:Party></cac:AccountingSupplierParty><cac:AccountingCustomerParty><cac:Party><cac:PartyIdentification><cbc:ID schemeID=\"1\">20606422793</cbc:ID></cac:PartyIdentification><cac:PartyLegalEntity><cbc:RegistrationName><![CDATA[INTENTIONS ENGINEERING LEADERSHIP SERVICES SOCIEDA -]]></cbc:RegistrationName></cac:PartyLegalEntity></cac:Party></cac:AccountingCustomerParty><cac:TaxTotal><cbc:TaxAmount currencyID=\"PEN\">73.22</cbc:TaxAmount><cac:TaxSubtotal><cbc:TaxableAmount currencyID=\"PEN\">406.78</cbc:TaxableAmount><cbc:TaxAmount currencyID=\"PEN\">73.22</cbc:TaxAmount><cac:TaxCategory><cac:TaxScheme><cbc:ID>1000</cbc:ID><cbc:Name>IGV</cbc:Name><cbc:TaxTypeCode>VAT</cbc:TaxTypeCode></cac:TaxScheme></cac:TaxCategory></cac:TaxSubtotal></cac:TaxTotal><cac:LegalMonetaryTotal><cbc:LineExtensionAmount currencyID=\"PEN\">406.78</cbc:LineExtensionAmount><cbc:TaxInclusiveAmount currencyID=\"PEN\">480.00</cbc:TaxInclusiveAmount><cbc:PayableAmount currencyID=\"PEN\">480.00</cbc:PayableAmount></cac:LegalMonetaryTotal><cac:InvoiceLine><cbc:ID>1</cbc:ID><cbc:InvoicedQuantity unitCode=\"NIU\">1</cbc:InvoicedQuantity><cbc:LineExtensionAmount currencyID=\"PEN\">406.78</cbc:LineExtensionAmount><cac:PricingReference><cac:AlternativeConditionPrice><cbc:PriceAmount currencyID=\"PEN\">480.0004</cbc:PriceAmount><cbc:PriceTypeCode>01</cbc:PriceTypeCode></cac:AlternativeConditionPrice></cac:PricingReference><cac:TaxTotal><cbc:TaxAmount currencyID=\"PEN\">73.22</cbc:TaxAmount><cac:TaxSubtotal><cbc:TaxableAmount currencyID=\"PEN\">406.78</cbc:TaxableAmount><cbc:TaxAmount currencyID=\"PEN\">73.22</cbc:TaxAmount><cac:TaxCategory><cbc:Percent>18</cbc:Percent><cbc:TaxExemptionReasonCode>10</cbc:TaxExemptionReasonCode><cac:TaxScheme><cbc:ID>1000</cbc:ID><cbc:Name>IGV</cbc:Name><cbc:TaxTypeCode>VAT</cbc:TaxTypeCode></cac:TaxScheme></cac:TaxCategory></cac:TaxSubtotal></cac:TaxTotal><cac:Item><cbc:Description><![CDATA[Hospedaje - Habitación 104 (Tarifa Simple(Noche))]]></cbc:Description><cac:SellersItemIdentification><cbc:ID>P001</cbc:ID></cac:SellersItemIdentification></cac:Item><cac:Price><cbc:PriceAmount currencyID=\"PEN\">406.78</cbc:PriceAmount></cac:Price></cac:InvoiceLine></Invoice>\n', 'UEsDBBQAAgAIADSShlsAAAAAAgAAAAAAAAAGAAAAZHVtbXkvAwBQSwMEFAACAAgANJKGWwwA7OyIBAAAwQ0AACIAAABSLTIwMTIzNDU2Nzg5LTAzLUIwMDEtMDAwMDAwMDMueG1stVddc9o4FH3vr9DAzHa3u45sAyZ4CR0IaYZuShOSdJt9U2wFNGtLjiQDya/fKxsbk5hp6MwSHsS95577LU36H9dxhJZUKib4ScM5shuI8kCEjM9PGrc3n6zjxsfBuz6R/jBJIhYQDcAZVYngiiIw5uqkkUruC6KY8jmJqfJVQgP2sAH76X3kq2BBY+KvVehP+FKwgFpuIzf3iTyQoSaSLRtd6wPpTkUcC3621pSbKsBPoKRcqy1pcB/8FOkI4EEtIfk5wuF8LumcaFpHGkIrFlonPsar1epo1ToSco5d27ax3cOACRWbNwu0EiQp8bkjdQQqI88MzQFTvqSRSCgunYDz0oyuVaQzsBEri/DQ0gxyKZ0UeaqUE703z4TKtJrstUHX5eoUxOt9uTr4+5eL64yqwAILXSc1QYMijYi0QCupMs1XjUEfJsi/HV2UA6GKMa/R5ZLK7HA46UH/ms0hg1SWK/KGvsCaGTMaTviDGLxDqH9KuOBQp4g9Z7X6QvVChGgYzYVkehHvLYFjG1rIK7ACp82bfwPaDJCpYQNn3GWEbya120WsViwkbUpFLLUgHcfdUM7oA5Vwe1B0O5uYcoEQxDeScPUgZKxyQVX0Q7c7JSqGMbRUEX3u+kDStxQICPHLyPtjNqdKH1gxqEizWqeS5xuJUjp4eL4kv593kwv5mJ7PaPjts/vcu79rjVanzt1yenW8urtyeOepdddV5GwaeY/nCXc8PL1iZ5Px+PYz84bd0derFN/9cxHbl/JRBe7w5KSPq15Mf3DZIBg1vDtr1YnILT5cSraE7UP/0if0fkQ1uYRVheuMSv0ecaFRmnzIaSpW/b/oU8bZ/96xe2OiSX4yVvnOA/MUroEQBVvRhj93CAwV/pfGGdtEqZTKayoZiaoSQ3w4fcU248p5p2l8T+XhbDvWVQdFuHhbGVxWa1tHONffKfj15fNKpAZ9eKuM6Fv+pk/GA/fI7uNX0gx3miot4s3tAkKngL5UZGgD6Hod22vZnuO2Wjm01Jokx6ZFru12LMe1bO/GOfadtu96G2gJ2VrcwHMxsG0/+1ZgmTyDFW/8C+4cu6PcgWcExn/Xbzu74A03CfxK1Te5GMn17XR4U8muBAr5dEmkfspl2XESQnPK16ykcW2nBX9ur9PZEuH9VoUin0JjkJ0qkeQa/AKJ9wUHy880icoEh1qTYBFnk2T0ZmQkJ9H2TsgnZzYZNF/UwMhyRzVG+EfOcE2dpwK61XbtLrLQWYTG0wkK6T1FMMVUomMUEEkCcEUV4mn8SxOG7U/JAqEAP5l++uqjzPhXLkLho8aesm7q3kBLEgkJONf2bK/tut1eq/HbpqpCF6NAeUjl/9NeXOtgRgPKlof4dFvtjtc97r3ZZ42LsQhS05liGYpYyl/Zomz6Cy5G8JJZdv5pFXu0Ve/s3KkIYZl3ly2TZagxVYFkSRbeBUEjEcE9ahpMpUA7bv5AC4IUCwUiAU00CUnOWWUoEqxmsc1tZ6jrsyjLV2eV144lDORv7I9nVebrkA7teMH1PcL1/4EN/gNQSwECAAAUAAIACAA0koZbAAAAAAIAAAAAAAAABgAAAAAAAAAAAAAAAAAAAAAAZHVtbXkvUEsBAgAAFAACAAgANJKGWwwA7OyIBAAAwQ0AACIAAAAAAAAAAQAAAAAAJgAAAFItMjAxMjM0NTY3ODktMDMtQjAwMS0wMDAwMDAwMy54bWxQSwUGAAAAAAIAAgCEAAAA7gQAAAAA', NULL, '2025-12-06 18:14:26', 1, '2025-12-06 18:14:26', 'storage/xml/2025/12/20123456789-03-B001-00000003.xml', 'storage/cdr/2025/12/R-20123456789-03-B001-00000003.zip');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `boleta_detalle`
--

CREATE TABLE `boleta_detalle` (
  `bol_det_id` int(11) NOT NULL,
  `bol_id` int(11) NOT NULL,
  `bol_det_orden` int(11) NOT NULL DEFAULT 1,
  `bol_det_codigo` varchar(50) DEFAULT NULL,
  `bol_det_descripcion` varchar(200) NOT NULL,
  `bol_det_unidad` varchar(10) NOT NULL DEFAULT 'NIU',
  `bol_det_cantidad` decimal(10,2) NOT NULL,
  `bol_det_precio_unitario` decimal(10,2) NOT NULL,
  `bol_det_subtotal` decimal(10,2) NOT NULL,
  `bol_det_igv` decimal(10,2) NOT NULL,
  `bol_det_total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `boleta_detalle`
--

INSERT INTO `boleta_detalle` (`bol_det_id`, `bol_id`, `bol_det_orden`, `bol_det_codigo`, `bol_det_descripcion`, `bol_det_unidad`, `bol_det_cantidad`, `bol_det_precio_unitario`, `bol_det_subtotal`, `bol_det_igv`, `bol_det_total`) VALUES
(72, 49, 1, 'P001', 'Hospedaje - Habitación 107 (Tarifa Tematica (3horas))', 'NIU', 1.00, 50.85, 50.85, 9.15, 60.00),
(73, 49, 2, 'P002', 'Aji de Gallina', 'NIU', 4.00, 13.56, 54.24, 9.76, 64.00),
(74, 50, 1, 'P001', 'Hospedaje - Habitación 105 (Tarifa Dia del Padre)', 'NIU', 1.00, 36.44, 36.44, 6.56, 43.00),
(75, 51, 1, 'P001', 'Hospedaje - Habitación 104 (Tarifa Simple(Noche))', 'NIU', 1.00, 406.78, 406.78, 73.22, 480.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria`
--

CREATE TABLE `categoria` (
  `IdCategoria` int(11) NOT NULL,
  `Descripcion` varchar(50) NOT NULL,
  `Estado` tinyint(1) NOT NULL DEFAULT 1,
  `FechaCreacion` datetime NOT NULL DEFAULT current_timestamp(),
  `Amenities` text DEFAULT NULL COMMENT 'Iconos de amenidades separados por coma'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categoria`
--

INSERT INTO `categoria` (`IdCategoria`, `Descripcion`, `Estado`, `FechaCreacion`, `Amenities`) VALUES
(36, 'Tematica', 1, '2025-10-29 15:35:19', 'ri-tv-2-line,ri-snowy-line,ri-bubble-chart-line,ri-disc-line,ri-speaker-line,ri-wifi-line'),
(37, 'Matrimonial', 1, '2025-10-29 16:30:23', 'ri-tv-line,ri-snowy-line,ri-sofa-line,ri-wifi-line,ri-drop-line'),
(38, 'Simple', 1, '2025-11-17 23:40:00', 'ri-tv-line,ri-windy-line,ri-wifi-line,ri-drop-line');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente`
--

CREATE TABLE `cliente` (
  `IdCliente` int(11) NOT NULL,
  `TipoDocumento` varchar(15) NOT NULL,
  `Documento` varchar(15) NOT NULL,
  `Nombre` varchar(50) NOT NULL,
  `Apellido` varchar(50) NOT NULL,
  `Direccion` varchar(250) DEFAULT NULL,
  `Estado` tinyint(1) NOT NULL DEFAULT 1,
  `FechaCreacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cliente`
--

INSERT INTO `cliente` (`IdCliente`, `TipoDocumento`, `Documento`, `Nombre`, `Apellido`, `Direccion`, `Estado`, `FechaCreacion`) VALUES
(39, 'DNI', '73668217', 'JEAN PIERRE', 'CORDOVA RONDOY', 'Sucasa', 1, '2025-11-26 20:36:48'),
(40, 'DNI', '76453456', 'JULIO DANIEL', 'ROJAS MEZA', 'Mallaritos', 1, '2025-11-27 01:16:15'),
(41, 'DNI', '76545678', 'YESICA', 'CASTRO CASAFRANCA', '', 1, '2025-11-27 01:29:57'),
(42, 'DNI', '75434567', 'LISET SALY', 'CONDORI TICONA', 'Calle Santa Isabel', 1, '2025-12-01 20:55:15'),
(45, 'DNI', '76789875', 'MARCIAL POLICARPO', 'COTRINA VILLALBA', ',mn,', 1, '2025-12-06 16:35:28'),
(46, 'RUC', '20552103816', 'AGROLIGHT PERU S.A.C.', '', 'PJ. JORGE BASADRE NRO 158 URB. POP LA UNIVERSAL 2DA ET. ', 1, '2025-12-06 16:35:38'),
(50, 'DNI', '20552103', '', '', '', 1, '2025-12-06 17:07:21'),
(55, 'RUC', '20606422793', 'INTENTIONS ENGINEERING LEADERSHIP SERVICES SOCIEDA', '-', 'AV. SAN MARTIN NRO 625 DEP. 701 ', 1, '2025-12-06 18:13:43');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_venta`
--

CREATE TABLE `detalle_venta` (
  `IdDetalleVenta` int(11) NOT NULL,
  `IdVenta` int(11) NOT NULL,
  `IdProducto` int(11) NOT NULL,
  `Cantidad` int(11) NOT NULL,
  `SubTotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalle_venta`
--

INSERT INTO `detalle_venta` (`IdDetalleVenta`, `IdVenta`, `IdProducto`, `Cantidad`, `SubTotal`) VALUES
(79, 76, 25, 4, 64.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estado_habitacion`
--

CREATE TABLE `estado_habitacion` (
  `IdEstadoHabitacion` int(11) NOT NULL,
  `Descripcion` varchar(50) NOT NULL,
  `Estado` tinyint(1) NOT NULL DEFAULT 1,
  `FechaCreacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estado_habitacion`
--

INSERT INTO `estado_habitacion` (`IdEstadoHabitacion`, `Descripcion`, `Estado`, `FechaCreacion`) VALUES
(11, 'DISPONIBLE', 1, '2025-10-29 22:31:13'),
(12, 'OCUPADO', 1, '2025-10-29 22:31:25'),
(13, 'LIMPIEZA', 1, '2025-10-29 22:31:30');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `factura`
--

CREATE TABLE `factura` (
  `fac_id` int(11) NOT NULL,
  `rec_id` int(11) DEFAULT NULL COMMENT 'ID de recepción (puede ser NULL si es venta directa)',
  `fac_tipo` varchar(2) DEFAULT '01' COMMENT '01 = Factura',
  `fac_serie` varchar(10) NOT NULL COMMENT 'Serie: F001, F002, etc.',
  `fac_correlativo` varchar(20) NOT NULL COMMENT 'Número correlativo',
  `fac_fecha_emision` datetime NOT NULL,
  `fac_cliente_tipo_doc` varchar(2) DEFAULT '6' COMMENT '6 = RUC',
  `fac_cliente_ruc` varchar(11) NOT NULL COMMENT 'RUC del cliente',
  `fac_cliente_razon_social` varchar(200) NOT NULL COMMENT 'Razón social de la empresa',
  `fac_cliente_direccion` varchar(300) DEFAULT NULL COMMENT 'Dirección fiscal',
  `fac_cliente_ubigeo` varchar(6) DEFAULT NULL COMMENT 'Código de ubigeo',
  `fac_cliente_email` varchar(100) DEFAULT NULL COMMENT 'Email para envío electrónico',
  `fac_op_gravadas` decimal(10,2) DEFAULT 0.00 COMMENT 'Total operaciones gravadas',
  `fac_op_exoneradas` decimal(10,2) DEFAULT 0.00 COMMENT 'Total operaciones exoneradas',
  `fac_op_inafectas` decimal(10,2) DEFAULT 0.00 COMMENT 'Total operaciones inafectas',
  `fac_subtotal` decimal(10,2) NOT NULL COMMENT 'Subtotal sin IGV',
  `fac_igv` decimal(10,2) NOT NULL COMMENT 'IGV 18%',
  `fac_total` decimal(10,2) NOT NULL COMMENT 'Total con IGV',
  `fac_forma_pago` varchar(20) DEFAULT 'Contado' COMMENT 'Contado o Credito',
  `fac_metodo_pago` varchar(50) DEFAULT NULL COMMENT 'Efectivo, Tarjeta, Transferencia, etc.',
  `fac_cuotas` int(11) DEFAULT 0 COMMENT 'Número de cuotas si es crédito',
  `fac_estado` varchar(20) DEFAULT 'PENDIENTE' COMMENT 'PENDIENTE, ACEPTADA, RECHAZADA, ANULADA',
  `fac_hash` varchar(100) DEFAULT NULL COMMENT 'Hash del comprobante (DigestValue)',
  `fac_xml` longtext DEFAULT NULL COMMENT 'XML firmado',
  `fac_cdr` longtext DEFAULT NULL COMMENT 'CDR de SUNAT (base64)',
  `fac_observaciones` text DEFAULT NULL COMMENT 'Observaciones o mensaje de SUNAT',
  `fac_xml_ruta` varchar(500) DEFAULT NULL,
  `fac_cdr_ruta` varchar(500) DEFAULT NULL,
  `fac_pdf_ruta` varchar(500) DEFAULT NULL,
  `fac_usuario_registro` int(11) DEFAULT NULL,
  `fac_fecha_registro` datetime DEFAULT current_timestamp(),
  `fac_usuario_modificacion` int(11) DEFAULT NULL,
  `fac_fecha_modificacion` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Facturas electrónicas emitidas a empresas (RUC)';

--
-- Volcado de datos para la tabla `factura`
--

INSERT INTO `factura` (`fac_id`, `rec_id`, `fac_tipo`, `fac_serie`, `fac_correlativo`, `fac_fecha_emision`, `fac_cliente_tipo_doc`, `fac_cliente_ruc`, `fac_cliente_razon_social`, `fac_cliente_direccion`, `fac_cliente_ubigeo`, `fac_cliente_email`, `fac_op_gravadas`, `fac_op_exoneradas`, `fac_op_inafectas`, `fac_subtotal`, `fac_igv`, `fac_total`, `fac_forma_pago`, `fac_metodo_pago`, `fac_cuotas`, `fac_estado`, `fac_hash`, `fac_xml`, `fac_cdr`, `fac_observaciones`, `fac_xml_ruta`, `fac_cdr_ruta`, `fac_pdf_ruta`, `fac_usuario_registro`, `fac_fecha_registro`, `fac_usuario_modificacion`, `fac_fecha_modificacion`) VALUES
(1, 82, '01', 'F001', '00000001', '2025-12-07 00:25:44', '6', '20606422793', 'INTENTIONS ENGINEERING LEADERSHIP SERVICES SOCIEDA -', 'AV. SAN MARTIN NRO 625 DEP. 701 ', '', '', 101.69, 0.00, 0.00, 101.69, 18.30, 119.99, 'Contado', 'EFECTIVO', 0, 'ACEPTADA', 'mB1OjQL8zJCcrd64XapgZ+gKkJg=', '<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<Invoice xmlns=\"urn:oasis:names:specification:ubl:schema:xsd:Invoice-2\" xmlns:cac=\"urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2\" xmlns:cbc=\"urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2\" xmlns:ds=\"http://www.w3.org/2000/09/xmldsig#\" xmlns:ext=\"urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2\"><ext:UBLExtensions><ext:UBLExtension><ext:ExtensionContent><ds:Signature Id=\"GreenterSign\"><ds:SignedInfo><ds:CanonicalizationMethod Algorithm=\"http://www.w3.org/TR/2001/REC-xml-c14n-20010315\"/><ds:SignatureMethod Algorithm=\"http://www.w3.org/2000/09/xmldsig#rsa-sha1\"/><ds:Reference URI=\"\"><ds:Transforms><ds:Transform Algorithm=\"http://www.w3.org/2000/09/xmldsig#enveloped-signature\"/></ds:Transforms><ds:DigestMethod Algorithm=\"http://www.w3.org/2000/09/xmldsig#sha1\"/><ds:DigestValue>mB1OjQL8zJCcrd64XapgZ+gKkJg=</ds:DigestValue></ds:Reference></ds:SignedInfo><ds:SignatureValue>erQ/ztAR/BCm3NNw324FIOQlGSWg10SeW1XdJ1cQfNkCDxmS1Jy64D6DPa3/dUGv34PlXnAl2GCbZhkz/mpKlXHw3Xexok3r1/XJizSoRX5xIGiiup8eN3gKs8DYd5RCGpHv6mS1z2UB3qm49JTYQSlvLSwlEW0J8x7Zv36uSaPq947qvF/FX15FvmR4ypvyEe+TfzyDfjLNT1J09hT56vsT5+rsiiMpr9U1NjfXraI149J4sVUgT7tsh4I8bU8+MVc3MqZA7oXxRRR18dK57eyHMSgH3TPCQOFwKeuiQ+f5GCGID3KpD6JDnbLQM1ZsTOiPg2LKcHYxUyyyS8UW+A==</ds:SignatureValue><ds:KeyInfo><ds:X509Data><ds:X509Certificate>MIIFCDCCA/CgAwIBAgIJAN7i98Vb2bM7MA0GCSqGSIb3DQEBCwUAMIIBDTEbMBkGCgmSJomT8ixkARkWC0xMQU1BLlBFIFNBMQswCQYDVQQGEwJQRTENMAsGA1UECAwETElNQTENMAsGA1UEBwwETElNQTEYMBYGA1UECgwPVFUgRU1QUkVTQSBTLkEuMUUwQwYDVQQLDDxETkkgOTk5OTk5OSBSVUMgMjAwMDAwMDAwMDEgLSBDRVJUSUZJQ0FETyBQQVJBIERFTU9TVFJBQ0nDk04xRDBCBgNVBAMMO05PTUJSRSBSRVBSRVNFTlRBTlRFIExFR0FMIC0gQ0VSVElGSUNBRE8gUEFSQSBERU1PU1RSQUNJw5NOMRwwGgYJKoZIhvcNAQkBFg1kZW1vQGxsYW1hLnBlMB4XDTI1MTEyNTAxMDgxMVoXDTI3MTEyNTAxMDgxMVowggENMRswGQYKCZImiZPyLGQBGRYLTExBTUEuUEUgU0ExCzAJBgNVBAYTAlBFMQ0wCwYDVQQIDARMSU1BMQ0wCwYDVQQHDARMSU1BMRgwFgYDVQQKDA9UVSBFTVBSRVNBIFMuQS4xRTBDBgNVBAsMPEROSSA5OTk5OTk5IFJVQyAyMDAwMDAwMDAwMSAtIENFUlRJRklDQURPIFBBUkEgREVNT1NUUkFDScOTTjFEMEIGA1UEAww7Tk9NQlJFIFJFUFJFU0VOVEFOVEUgTEVHQUwgLSBDRVJUSUZJQ0FETyBQQVJBIERFTU9TVFJBQ0nDk04xHDAaBgkqhkiG9w0BCQEWDWRlbW9AbGxhbWEucGUwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQClNF3nRYGxHZ7GLh1QOXV0IYVOZ6O0dKLu7XoWVRji/VH/zeMNEnOcoVP6edyMnEigQzL8pDnpoNKZZUvEh1eOrb9lfrwZ54xZFUewxEaXDNSkooic0vxXKGfaTi+jBzD2ianAZMuuwvr7zCO4skPU4AYw1Lz9poZR+h+PPAN1NcMvSrgg07Jx7kcqhGFIjKozBPImOQhAr4K1EkdhLAAf/Ns3Cg5KAaCxYriTH5lKdXoJHw/jHRebGnGY7QamQienRnWqHD+NZUMi8voWfTmmcTDfkUG2pwZM2+EvidFOdfJ8skWj+pAcekmE+PyXdW8zFhXQNII38NavSeD9KXrnAgMBAAGjZzBlMB0GA1UdDgQWBBSrnc7Ehh9eZvp6QNAyVfYv6a8vfjAfBgNVHSMEGDAWgBSrnc7Ehh9eZvp6QNAyVfYv6a8vfjATBgNVHSUEDDAKBggrBgEFBQcDATAOBgNVHQ8BAf8EBAMCB4AwDQYJKoZIhvcNAQELBQADggEBAIG/CWn7oEzc+1W6nL5QQ8TTb5MiK1cp2OVQjj+2bx0Ye6vP2lFwYa51qB6/1v26FvaqumBQs/RUp48k33Qre9bHFhN9yVxn7gf/tQNK6p587IRqfQMfl34m/l1o09h9MoPKBekqyyV99ZrJGnLIWqaImvh964WtITwS1D2/M8ks5h0xFaCqT5d0jtnMsn9/IzaXw9c2eHXCApSwGF2lYzk2CyAqqZm3TioE5CYgXB5YCqdEpaJR9PpHAKLkm/EppzNaazJvP9vosb4sCRaYoWUWBmR+mmDjbbjxa6Nexel4G7EagKpXzHp1t4jho0NnNAFi7miU4wnRd9NPyJTx7xA=</ds:X509Certificate></ds:X509Data></ds:KeyInfo></ds:Signature></ext:ExtensionContent></ext:UBLExtension></ext:UBLExtensions><cbc:UBLVersionID>2.1</cbc:UBLVersionID><cbc:CustomizationID>2.0</cbc:CustomizationID><cbc:ID>F001-00000001</cbc:ID><cbc:IssueDate>2025-12-06</cbc:IssueDate><cbc:IssueTime>18:25:44</cbc:IssueTime><cbc:InvoiceTypeCode listID=\"0101\">01</cbc:InvoiceTypeCode><cbc:Note languageLocaleID=\"1000\"><![CDATA[CIENTO DIECINUEVE CON 99/100 SOLES]]></cbc:Note><cbc:DocumentCurrencyCode>PEN</cbc:DocumentCurrencyCode><cac:Signature><cbc:ID>20123456789</cbc:ID><cac:SignatoryParty><cac:PartyIdentification><cbc:ID>20123456789</cbc:ID></cac:PartyIdentification><cac:PartyName><cbc:Name><![CDATA[HOTEL LAS PALMERAS SAC]]></cbc:Name></cac:PartyName></cac:SignatoryParty><cac:DigitalSignatureAttachment><cac:ExternalReference><cbc:URI>#GREENTER-SIGN</cbc:URI></cac:ExternalReference></cac:DigitalSignatureAttachment></cac:Signature><cac:AccountingSupplierParty><cac:Party><cac:PartyIdentification><cbc:ID schemeID=\"6\">20123456789</cbc:ID></cac:PartyIdentification><cac:PartyName><cbc:Name><![CDATA[HOTEL LAS PALMERAS]]></cbc:Name></cac:PartyName><cac:PartyLegalEntity><cbc:RegistrationName><![CDATA[HOTEL LAS PALMERAS SAC]]></cbc:RegistrationName><cac:RegistrationAddress><cbc:ID>150101</cbc:ID><cbc:AddressTypeCode>0000</cbc:AddressTypeCode><cbc:CityName>LIMA</cbc:CityName><cbc:CountrySubentity>LIMA</cbc:CountrySubentity><cbc:District>LIMA</cbc:District><cac:AddressLine><cbc:Line><![CDATA[Av. Principal 123]]></cbc:Line></cac:AddressLine><cac:Country><cbc:IdentificationCode>PE</cbc:IdentificationCode></cac:Country></cac:RegistrationAddress></cac:PartyLegalEntity></cac:Party></cac:AccountingSupplierParty><cac:AccountingCustomerParty><cac:Party><cac:PartyIdentification><cbc:ID schemeID=\"6\">20606422793</cbc:ID></cac:PartyIdentification><cac:PartyLegalEntity><cbc:RegistrationName><![CDATA[INTENTIONS ENGINEERING LEADERSHIP SERVICES SOCIEDA -]]></cbc:RegistrationName><cac:RegistrationAddress><cac:AddressLine><cbc:Line><![CDATA[AV. SAN MARTIN NRO 625 DEP. 701 ]]></cbc:Line></cac:AddressLine><cac:Country><cbc:IdentificationCode>PE</cbc:IdentificationCode></cac:Country></cac:RegistrationAddress></cac:PartyLegalEntity></cac:Party></cac:AccountingCustomerParty><cac:PaymentTerms><cbc:ID>FormaPago</cbc:ID><cbc:PaymentMeansID>Contado</cbc:PaymentMeansID></cac:PaymentTerms><cac:TaxTotal><cbc:TaxAmount currencyID=\"PEN\">18.30</cbc:TaxAmount><cac:TaxSubtotal><cbc:TaxableAmount currencyID=\"PEN\">101.69</cbc:TaxableAmount><cbc:TaxAmount currencyID=\"PEN\">18.30</cbc:TaxAmount><cac:TaxCategory><cac:TaxScheme><cbc:ID>1000</cbc:ID><cbc:Name>IGV</cbc:Name><cbc:TaxTypeCode>VAT</cbc:TaxTypeCode></cac:TaxScheme></cac:TaxCategory></cac:TaxSubtotal></cac:TaxTotal><cac:LegalMonetaryTotal><cbc:LineExtensionAmount currencyID=\"PEN\">101.69</cbc:LineExtensionAmount><cbc:TaxInclusiveAmount currencyID=\"PEN\">119.99</cbc:TaxInclusiveAmount><cbc:PayableAmount currencyID=\"PEN\">119.99</cbc:PayableAmount></cac:LegalMonetaryTotal><cac:InvoiceLine><cbc:ID>1</cbc:ID><cbc:InvoicedQuantity unitCode=\"ZZ\">1</cbc:InvoicedQuantity><cbc:LineExtensionAmount currencyID=\"PEN\">101.69</cbc:LineExtensionAmount><cac:PricingReference><cac:AlternativeConditionPrice><cbc:PriceAmount currencyID=\"PEN\">119.99</cbc:PriceAmount><cbc:PriceTypeCode>01</cbc:PriceTypeCode></cac:AlternativeConditionPrice></cac:PricingReference><cac:TaxTotal><cbc:TaxAmount currencyID=\"PEN\">18.30</cbc:TaxAmount><cac:TaxSubtotal><cbc:TaxableAmount currencyID=\"PEN\">101.69</cbc:TaxableAmount><cbc:TaxAmount currencyID=\"PEN\">18.30</cbc:TaxAmount><cac:TaxCategory><cbc:Percent>18</cbc:Percent><cbc:TaxExemptionReasonCode>10</cbc:TaxExemptionReasonCode><cac:TaxScheme><cbc:ID>1000</cbc:ID><cbc:Name>IGV</cbc:Name><cbc:TaxTypeCode>VAT</cbc:TaxTypeCode></cac:TaxScheme></cac:TaxCategory></cac:TaxSubtotal></cac:TaxTotal><cac:Item><cbc:Description><![CDATA[Servicio de Hospedaje - Habitación 104 (Tarifa Simple(Noche))]]></cbc:Description><cac:SellersItemIdentification><cbc:ID>P001</cbc:ID></cac:SellersItemIdentification></cac:Item><cac:Price><cbc:PriceAmount currencyID=\"PEN\">101.69</cbc:PriceAmount></cac:Price></cac:InvoiceLine></Invoice>\n', 'UEsDBBQAAgAIAJ2ThlsAAAAAAgAAAAAAAAAGAAAAZHVtbXkvAwBQSwMEFAACAAgAnZOGW8t9+mQ9BAAALg0AACIAAABSLTIwMTIzNDU2Nzg5LTAxLUYwMDEtMDAwMDAwMDEueG1stVddU9s6EH3vr/CEh850rpHjfBGPSQdIoeFSvhIovW/CVhLda0seSU7M/fVdWbFjBzMlnSnwIO+ePbt7tJIG/3MWR9aKCEk5O261D52WRVjAQ8oWx62H2bl91Po8+uBj4Z0kSUQDrAB4T2TCmSQWBDN53EoF8ziWVHoMx0R6MiEBnW/AXvoceTJYkhh7mQy9CVtxGhDbbZlwD4s9GRoq2bKRTO1Jd8bjmLMvmSJMqwCfQEmYklvS4Dn4LdJTgAeNhPj3CE8WC0EWWJEm0hC2YqlU4iG0Xq8P151DLhbIdRwHOUMEmFDSxUGBlhwnJd4kkofg0vY8UC8QYSsS8YSgMgkkL8NIJiOVg7VZ2piFtqLQS5mk6FOmDKs3+0yISKvNTjW6qdd2QZy91WsbPX27muZUBRZYSJY0FA2ONMLCBq8gUm++bI18mCDv4fSqHAhZjHmDz1gqs8NgpUb+lC6gg1SUR+Qd+wLHTIeRcMLmfPTBsvwzzDgDnSL6f67VN6KWPLROogUXVC3jNyVoO5oW+grsoN1lB98BrQdIa9hCOXdZ4btJnW5Rqx1zQQ6ExLZc4l7b3VDekzkRcHsQ6+F+ouUCI5hnAjM55yKWxlA1/TJtTaJiGENbFtWb1HuSvkcgIES7lftjuiBS7akYKHJQ1ankecRRSkbZebi6mS+fyOThX3bz9XI5mMQvSXS1eHL+uexMTx8vfsxmN53w8sv53Y/u+uomhALwxdnc6V3G6nQ4/Zpd0Cy9VnfT6TrOOpKOyfe742MfVbPo/UHlBsGoofqsVSfCRHy6FXQFp8/6j7xYH0+JwrdwVOE6I0J9tBhXVpp8MjSVKP9v8pJz+k89ZzjGCpuVjjJnHpiv4RoIrWBr2vCbhMBQ4d8NztkmUqZETImgOKpaNPH+9JXYnMvwXqfxMxH7s9WiqwmKctFWGVSqtdUR1s13Cnp9+bwyyZEPb5U2PZo3fTIeuYeOj15Zc9xZKhWPN7cLGNsFdNeRozVg0O85/c6gMxy4HQMtvbrJsd4i13F7dtu1nf6sfeS5Pa/b3UBLyDZiBs/FyHG8/K8Cy+05rHjjd7gNtuaswXMCnf/I6w3r4A03DryK6ptetGX6cH0yq3RXArl4ucVCvRhbvpyEsDnla1bSuE67A7/usNfbEqG3owqHmUIdkK8qlRgP2kGit4qDw08VjsoGT5TCwTLOJ0n79cgIhqPtnWAm534yOtjRQNtMooYg9KtkaFdn/UlYSMSfkRI1JrgnAaGrfXK6nW6vPzgavjtnQ4oxD1KtQjF4RS3lVz6UGy0hxTm8GrZjftrFzG7dtfk+4yEcnPpg57YcNSYyEDTJy7vC1jkOQH1sMShHcKuW5y9riS1JQ27hgCQKh9iQVimKDqttbJurTVBzG6V+TVFGPJpQsL9zg/q26/Sdftd1B8POPltUy4KaNwk1/7sz+glQSwECAAAUAAIACACdk4ZbAAAAAAIAAAAAAAAABgAAAAAAAAAAAAAAAAAAAAAAZHVtbXkvUEsBAgAAFAACAAgAnZOGW8t9+mQ9BAAALg0AACIAAAAAAAAAAQAAAAAAJgAAAFItMjAxMjM0NTY3ODktMDEtRjAwMS0wMDAwMDAwMS54bWxQSwUGAAAAAAIAAgCEAAAAowQAAAAA', 'La Factura numero F001-00000001, ha sido aceptada', 'storage/xml/facturas/2025/12/20123456789-01-F001-00000001.xml', 'storage/cdr/facturas/2025/12/R-20123456789-01-F001-00000001.zip', 'storage/boletas/facturas/2025/12/F001-00000001_20251207_002610.pdf', 1, '2025-12-06 18:25:45', NULL, '2025-12-06 18:26:10');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `factura_cuotas`
--

CREATE TABLE `factura_cuotas` (
  `fac_cuo_id` int(11) NOT NULL,
  `fac_id` int(11) NOT NULL,
  `fac_cuo_numero` int(11) NOT NULL COMMENT 'Número de cuota',
  `fac_cuo_monto` decimal(10,2) NOT NULL COMMENT 'Monto de la cuota',
  `fac_cuo_fecha_vencimiento` date NOT NULL COMMENT 'Fecha de vencimiento',
  `fac_cuo_estado` varchar(20) DEFAULT 'PENDIENTE' COMMENT 'PENDIENTE, PAGADA',
  `fac_cuo_fecha_pago` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Cuotas de facturas a crédito';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `factura_detalle`
--

CREATE TABLE `factura_detalle` (
  `fac_det_id` int(11) NOT NULL,
  `fac_id` int(11) NOT NULL COMMENT 'ID de la factura',
  `fac_det_orden` int(11) NOT NULL COMMENT 'Orden del item',
  `fac_det_codigo` varchar(20) DEFAULT NULL COMMENT 'Código del producto/servicio',
  `fac_det_descripcion` varchar(500) NOT NULL COMMENT 'Descripción del item',
  `fac_det_unidad` varchar(10) DEFAULT 'NIU' COMMENT 'Unidad de medida (NIU, ZZ, etc.)',
  `fac_det_cantidad` decimal(10,2) NOT NULL DEFAULT 1.00,
  `fac_det_precio_unitario` decimal(10,2) NOT NULL COMMENT 'Precio unitario sin IGV',
  `fac_det_valor_unitario` decimal(10,2) NOT NULL COMMENT 'Valor unitario (incluye IGV)',
  `fac_det_descuento` decimal(10,2) DEFAULT 0.00 COMMENT 'Descuento aplicado',
  `fac_det_subtotal` decimal(10,2) NOT NULL COMMENT 'Subtotal sin IGV',
  `fac_det_igv` decimal(10,2) NOT NULL COMMENT 'IGV del item',
  `fac_det_total` decimal(10,2) NOT NULL COMMENT 'Total con IGV',
  `fac_det_tipo_afectacion` varchar(2) DEFAULT '10' COMMENT '10=Gravado, 20=Exonerado, 30=Inafecto'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Detalle de items de las facturas';

--
-- Volcado de datos para la tabla `factura_detalle`
--

INSERT INTO `factura_detalle` (`fac_det_id`, `fac_id`, `fac_det_orden`, `fac_det_codigo`, `fac_det_descripcion`, `fac_det_unidad`, `fac_det_cantidad`, `fac_det_precio_unitario`, `fac_det_valor_unitario`, `fac_det_descuento`, `fac_det_subtotal`, `fac_det_igv`, `fac_det_total`, `fac_det_tipo_afectacion`) VALUES
(1, 1, 1, 'P001', 'Servicio de Hospedaje - Habitación 104 (Tarifa Simple(Noche))', 'ZZ', 1.00, 101.69, 119.99, 0.00, 101.69, 18.30, 119.99, '10');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `habitacion`
--

CREATE TABLE `habitacion` (
  `IdHabitacion` int(11) NOT NULL,
  `Numero` varchar(50) NOT NULL,
  `Detalle` varchar(500) DEFAULT NULL,
  `IdEstadoHabitacion` int(11) NOT NULL,
  `IdPiso` int(11) NOT NULL,
  `IdCategoria` int(11) NOT NULL,
  `Estado` tinyint(1) NOT NULL DEFAULT 1,
  `FechaCreacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `habitacion`
--

INSERT INTO `habitacion` (`IdHabitacion`, `Numero`, `Detalle`, `IdEstadoHabitacion`, `IdPiso`, `IdCategoria`, `Estado`, `FechaCreacion`) VALUES
(11, '101', '✓Cama 2 plazas ✓Ventilador ✓Tv Smart ✓Baño Privado ✓Agua Caliente ✓Wi-Fi.', 11, 3, 38, 1, '2025-11-26 18:30:35'),
(12, '102', '✓Cama 2 plazas ✓Ventilador ✓Tv Smart ✓Baño Privado ✓Agua Caliente ✓Wi-Fi', 11, 5, 38, 1, '2025-11-26 18:31:51'),
(13, '103', '✓Cama 2 plazas ✓Ventilador ✓Tv Smart ✓Baño Privado ✓Agua Caliente ✓Wi-Fi.', 11, 5, 38, 1, '2025-11-26 18:32:18'),
(14, '104', '✓Cama 2 plazas ✓Ventilador ✓Tv Smart ✓Baño Privado ✓Agua Caliente ✓Wi-Fi', 13, 5, 38, 1, '2025-11-26 18:33:00'),
(15, '105', '✓Cama 2 plazas ✓Sillón Tántrico ✓Mesa de Noche ✓Aire Acondicionado ✓Tv Smart ✓Baño Privado ✓Agua Cal', 13, 1, 37, 1, '2025-11-26 18:33:57'),
(16, '106', '✓Cama 2 plazas ✓Sillón Tántrico ✓Mesa de Noche ✓Ventilador ✓Tv Smart ✓Baño Privado ✓Agua Caliente', 11, 3, 37, 1, '2025-11-26 18:34:30'),
(18, '107', ' ✓Cama Redonda Flotante King Size ✓Jacuzzi esquinero con Hidromasaje ✓Pole Dance ✓Aire Acondicionado ✓ Tv Smart 60” Full HD 4K ✓Rincón del Sacrificio (Con cadenas) ✓Sillón Tántrico ✓Mesa de Noche ✓Luces Psicodélicas ✓Parlante Bluetooth ✓Baño Privado ✓Agua Caliente ✓Wi-Fi ✓Amplios Espejos ✓Frigobar equipado', 13, 3, 36, 1, '2025-11-26 19:25:36');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `habitacion_tarifa`
--

CREATE TABLE `habitacion_tarifa` (
  `id_habitacion_tarifa` int(11) NOT NULL,
  `id_habitacion` int(11) NOT NULL,
  `id_tarifa` int(11) NOT NULL,
  `fecha_inicio` datetime NOT NULL,
  `fecha_fin` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `habitacion_tarifa`
--

INSERT INTO `habitacion_tarifa` (`id_habitacion_tarifa`, `id_habitacion`, `id_tarifa`, `fecha_inicio`, `fecha_fin`) VALUES
(12, 11, 2, '2025-11-26 21:07:00', '2025-11-30 21:07:00'),
(14, 11, 12, '2025-11-26 21:08:00', '2025-11-30 21:08:00'),
(15, 11, 11, '2025-11-26 21:08:00', '2025-11-26 21:08:00'),
(18, 15, 13, '2025-11-27 01:14:00', '2025-11-30 01:14:00'),
(19, 15, 2, '2025-11-27 01:15:00', '2025-11-30 01:15:00'),
(20, 18, 13, '2025-11-27 01:29:00', '2025-11-30 01:29:00'),
(21, 14, 2, '2025-12-06 18:12:00', '2025-12-30 18:12:00'),
(22, 14, 12, '2025-12-06 18:13:00', '2025-12-30 18:13:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `piso`
--

CREATE TABLE `piso` (
  `IdPiso` int(11) NOT NULL,
  `Descripcion` varchar(50) NOT NULL,
  `Estado` tinyint(1) NOT NULL DEFAULT 1,
  `FechaCreacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `piso`
--

INSERT INTO `piso` (`IdPiso`, `Descripcion`, `Estado`, `FechaCreacion`) VALUES
(1, 'PRIMER PISO', 1, '2025-10-29 16:57:32'),
(3, 'SEGUNDO PISO', 1, '2025-11-03 21:21:11'),
(5, 'TERCER PISO', 1, '2025-11-26 18:31:12');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto`
--

CREATE TABLE `producto` (
  `IdProducto` int(11) NOT NULL,
  `Nombre` varchar(50) NOT NULL,
  `Detalle` varchar(100) DEFAULT NULL,
  `Precio` decimal(10,2) NOT NULL,
  `Cantidad` int(11) NOT NULL,
  `Estado` tinyint(1) NOT NULL DEFAULT 1,
  `FechaCreacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `producto`
--

INSERT INTO `producto` (`IdProducto`, `Nombre`, `Detalle`, `Precio`, `Cantidad`, `Estado`, `FechaCreacion`) VALUES
(1, 'Botella de Agua', 'Botella de agua mineral de 500ml', 2.00, 0, 1, '2025-11-18 00:58:35'),
(2, 'Preservativos', 'Marca Piel', 3.00, 21, 1, '2025-11-19 17:02:23'),
(3, 'Gaseosa Inka Cola', 'Gaseosa Inka Cola de 500ml', 2.00, 31, 1, '2025-11-19 17:42:25'),
(4, 'Gaseosa Coca Cola', 'Gaseosa Coca Cola de 500ml', 2.00, 20, 1, '2025-11-19 18:05:10'),
(5, 'Cerveza Cusqueña', 'Cerveza Cusqueña de 330ml', 4.50, 50, 1, '2025-11-19 18:15:32'),
(6, 'Cerveza Arequipeña', 'Cerveza Arequipeña de 330ml', 4.00, 45, 1, '2025-11-19 18:30:00'),
(7, 'Papas Fritas', 'Papas fritas de bolsa, 100g', 1.50, 100, 1, '2025-11-19 18:45:23'),
(8, 'Sándwich de Jamón y Queso', 'Sándwich con pan integral, jamón y queso', 6.00, 30, 1, '2025-11-19 18:55:12'),
(9, 'Sándwich Vegetariano', 'Sándwich con vegetales y pan integral', 6.00, 25, 1, '2025-11-19 19:05:40'),
(10, 'Ensalada Mixta', 'Ensalada fresca con lechuga, tomate y zanahoria', 5.00, 20, 1, '2025-11-19 19:10:32'),
(11, 'Piqueo Variado', 'Piqueo con papas fritas, nuggets y guacamole', 8.00, 50, 1, '2025-11-19 19:15:50'),
(12, 'Fruta Fresca', 'Plato con frutas frescas de temporada', 7.00, 40, 1, '2025-11-19 19:20:18'),
(13, 'Jugos Naturales', 'Jugo natural de naranja o fresa', 3.50, 60, 1, '2025-11-19 19:25:10'),
(14, 'Té Frío', 'Té helado, variedad de sabores', 2.50, 30, 1, '2025-11-19 19:30:00'),
(15, 'Café Americano', 'Café americano con leche', 2.00, 25, 1, '2025-11-19 19:40:25'),
(16, 'Café Expreso', 'Café expreso de grano peruano', 2.50, 20, 1, '2025-11-19 19:50:35'),
(17, 'Latte', 'Café con leche y espuma', 3.00, 18, 1, '2025-11-19 20:00:10'),
(18, 'Capuchino', 'Café con leche y espuma de leche', 3.50, 15, 1, '2025-11-19 20:10:12'),
(19, 'Cappuccino con Chocolate', 'Capuchino con toque de chocolate', 4.00, 10, 1, '2025-11-19 20:20:30'),
(20, 'Agua Tónica', 'Botella de agua tónica de 500ml', 2.20, 35, 1, '2025-11-19 20:30:45'),
(21, 'Vino Blanco', 'Vino blanco seco, botella de 750ml', 15.00, 40, 1, '2025-11-19 20:40:20'),
(22, 'Vino Tinto', 'Vino tinto suave, botella de 750ml', 15.50, 30, 1, '2025-11-19 20:50:10'),
(23, 'Ceviche', 'Ceviche de pescado fresco, con camote y choclo', 12.00, 10, 1, '2025-11-19 21:00:00'),
(24, 'Lomo Saltado', 'Plato tradicional peruano con carne de res y papas fritas', 18.00, 10, 1, '2025-11-19 21:10:30'),
(25, 'Aji de Gallina', 'Plato tradicional peruano con pollo, arroz y papa', 16.00, 10, 1, '2025-11-19 21:20:40'),
(26, 'Causa Rellena', 'Causa rellena de atún o pollo', 14.00, 20, 1, '2025-11-19 21:30:50'),
(27, 'Papa a la Huancaína', 'Papas con salsa de ají amarillo y queso', 10.00, 30, 1, '2025-11-19 21:40:25'),
(28, 'Tamales', 'Tamales de cerdo o pollo', 8.00, 25, 1, '2025-11-19 21:50:15'),
(29, 'Tacu Tacu', 'Plato de arroz y frijoles, con carne o huevo', 9.00, 20, 1, '2025-11-19 22:00:00'),
(30, 'Anticuchos', 'Brochetas de carne de res con salsa de ají', 7.00, 50, 1, '2025-11-19 22:10:12'),
(31, 'Chicha Morada', 'Bebida refrescante a base de maíz morado', 2.50, 40, 1, '2025-11-19 22:20:30'),
(32, 'Pisco Sour', 'Cóctel de pisco, limón, clara de huevo y amargo de angostura', 8.00, 15, 1, '2025-11-19 22:30:10'),
(33, 'Caipirinha', 'Cóctel brasileño con cachaça, azúcar y limón', 7.50, 10, 1, '2025-11-19 22:40:35'),
(34, 'Margarita', 'Cóctel de tequila, limón y licor de naranja', 7.00, 12, 1, '2025-11-19 22:50:25'),
(35, 'Daiquiri', 'Cóctel de ron, limón y azúcar', 6.50, 20, 1, '2025-11-19 23:00:00'),
(36, 'Tequila Sunrise', 'Cóctel de tequila, jugo de naranja y granadina', 7.00, 25, 1, '2025-11-19 23:10:10'),
(37, 'Pisco', 'Pisco peruano de 750ml', 18.00, 30, 1, '2025-11-19 23:20:12'),
(38, 'Ron', 'Ron blanco, botella de 750ml', 12.00, 40, 1, '2025-11-19 23:30:25'),
(39, 'Vodka', 'Vodka, botella de 750ml', 14.00, 50, 1, '2025-11-19 23:40:15'),
(40, 'Whisky', 'Whisky escocés, botella de 750ml', 25.00, 24, 1, '2025-11-19 23:50:40'),
(41, 'Soda', 'Soda de 500ml', 1.50, 50, 1, '2025-11-20 00:00:00'),
(42, 'Jugo de Mango', 'Jugo natural de mango', 4.00, 35, 1, '2025-11-20 00:10:05'),
(43, 'Jugo de Fresa', 'Jugo natural de fresa', 4.00, 25, 1, '2025-11-20 00:20:35'),
(44, 'Café Mocha', 'Café con chocolate y crema batida', 3.50, 18, 1, '2025-11-20 00:30:12'),
(45, 'Torta de Chocolate', 'Torta de chocolate con crema', 6.50, 16, 1, '2025-11-20 00:40:25'),
(46, 'Torta de Mora', 'Torta de mora con crema', 6.00, 20, 1, '2025-11-20 00:50:40'),
(47, 'Alfajores', 'Alfajores rellenos de dulce de leche', 3.00, 24, 1, '2025-11-20 01:00:50'),
(48, 'Churros', 'Churros rellenos de crema pastelera', 4.00, 23, 1, '2025-11-20 01:10:30'),
(49, 'Muffins', 'Muffins de vainilla y chocolate', 2.50, 40, 1, '2025-11-20 01:20:25'),
(50, 'Cachanga', 'Cachanga, dulce tradicional peruano', 3.50, 30, 1, '2025-11-20 01:30:10');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recepcion`
--

CREATE TABLE `recepcion` (
  `IdRecepcion` int(11) NOT NULL,
  `IdCliente` int(11) NOT NULL,
  `IdHabitacion` int(11) NOT NULL,
  `IdTarifa` int(11) DEFAULT NULL,
  `FechaEntrada` datetime NOT NULL DEFAULT current_timestamp(),
  `FechaSalida` datetime DEFAULT NULL,
  `FechaSalidaConfirmacion` datetime DEFAULT NULL,
  `PrecioInicial` decimal(10,2) NOT NULL,
  `Adelanto` decimal(10,2) NOT NULL DEFAULT 0.00,
  `PrecioRestante` decimal(10,2) NOT NULL DEFAULT 0.00,
  `TotalPagado` decimal(10,2) NOT NULL DEFAULT 0.00,
  `CostoPenalidad` decimal(10,2) NOT NULL DEFAULT 0.00,
  `Observacion` varchar(500) DEFAULT NULL,
  `TipoComprobante` varchar(2) NOT NULL DEFAULT '03' COMMENT '01=Factura, 03=Boleta',
  `Estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `recepcion`
--

INSERT INTO `recepcion` (`IdRecepcion`, `IdCliente`, `IdHabitacion`, `IdTarifa`, `FechaEntrada`, `FechaSalida`, `FechaSalidaConfirmacion`, `PrecioInicial`, `Adelanto`, `PrecioRestante`, `TotalPagado`, `CostoPenalidad`, `Observacion`, `TipoComprobante`, `Estado`) VALUES
(79, 46, 18, 13, '2025-12-06 17:53:56', '2025-12-06 20:53:00', '2025-12-06 22:55:03', 60.00, 60.00, 0.00, 124.00, 0.00, 'fgfgfd', '01', 0),
(80, 42, 15, 2, '2025-12-06 18:10:39', '2025-12-06 21:10:00', '2025-12-06 23:10:58', 43.00, 43.00, 0.00, 43.00, 0.00, 'GFDGFDGFD', '03', 0),
(81, 55, 14, 12, '2025-12-06 18:14:01', '2025-12-10 18:13:00', '2025-12-06 23:15:54', 480.00, 240.00, 240.00, 480.00, 0.00, 'HGFHGF', '01', 0),
(82, 55, 14, 12, '2025-12-06 18:16:52', '2025-12-07 18:16:00', '2025-12-06 23:25:44', 120.00, 120.00, 0.00, 120.00, 0.00, 'CVBVC', '01', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `IdRol` int(11) NOT NULL,
  `Descripcion` varchar(50) NOT NULL,
  `Estado` tinyint(1) NOT NULL DEFAULT 1,
  `FechaCreacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`IdRol`, `Descripcion`, `Estado`, `FechaCreacion`) VALUES
(1, 'Administrador', 1, '2025-10-14 10:08:05'),
(2, 'Empleado', 1, '2025-10-14 10:08:05'),
(12, 'tECNICO', 0, '2025-10-29 14:20:51');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tarifa`
--

CREATE TABLE `tarifa` (
  `IdTarifa` int(11) NOT NULL,
  `Descripcion` varchar(100) NOT NULL,
  `Precio` decimal(10,2) NOT NULL,
  `Estado` tinyint(1) NOT NULL DEFAULT 1,
  `FechaCreacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tarifa`
--

INSERT INTO `tarifa` (`IdTarifa`, `Descripcion`, `Precio`, `Estado`, `FechaCreacion`) VALUES
(2, 'Tarifa Dia del Padre', 43.00, 1, '2025-11-16 16:38:11'),
(11, 'Tarifa Simple (3horas)', 25.00, 1, '2025-11-17 02:49:55'),
(12, 'Tarifa Simple(Noche)', 120.00, 1, '2025-11-17 22:04:10'),
(13, 'Tarifa Tematica (3horas)', 60.00, 1, '2025-11-17 23:02:12'),
(14, 'Tarifa Matrimonial (3horas)', 25.00, 1, '2025-11-21 20:03:28'),
(15, 'Tarifa Matrimonial (Noche)', 150.00, 1, '2025-11-26 18:35:21');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `td_menu`
--

CREATE TABLE `td_menu` (
  `MEND_ID` int(11) NOT NULL,
  `MEN_ID` int(11) DEFAULT NULL,
  `IdRol` int(11) DEFAULT NULL,
  `MEND_PERMI` varchar(2) DEFAULT NULL,
  `FECH_CREA` datetime(3) DEFAULT NULL,
  `EST` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `td_menu`
--

INSERT INTO `td_menu` (`MEND_ID`, `MEN_ID`, `IdRol`, `MEND_PERMI`, `FECH_CREA`, `EST`) VALUES
(1, 2, 1, 'Si', NULL, 1),
(32, 1, 1, 'Si', '2025-10-24 22:41:26.481', 1),
(33, 3, 1, 'Si', '2025-10-24 22:41:26.481', 1),
(34, 4, 1, 'Si', '2025-10-24 22:41:26.481', 1),
(35, 5, 1, 'Si', '2025-10-24 22:41:26.481', 1),
(40, 10, 1, 'Si', '2025-10-24 22:41:26.481', 1),
(41, 11, 1, 'Si', '2025-10-24 22:41:26.481', 1),
(42, 12, 1, 'Si', '2025-10-24 22:41:26.481', 1),
(47, 1, 2, 'Si', '2025-10-24 22:48:40.359', 1),
(48, 2, 2, 'No', '2025-10-24 22:48:40.359', 1),
(49, 3, 2, 'No', '2025-10-24 22:48:40.359', 1),
(50, 4, 2, 'No', '2025-10-24 22:48:40.359', 1),
(51, 5, 2, 'No', '2025-10-24 22:48:40.359', 1),
(56, 10, 2, 'No', '2025-10-24 22:48:40.359', 1),
(57, 11, 2, 'Si', '2025-10-24 22:48:40.359', 1),
(58, 12, 2, 'No', '2025-10-24 22:48:40.359', 1),
(75, 13, 1, 'Si', '2025-11-05 20:26:00.320', 1),
(76, 14, 1, 'Si', '2025-11-12 19:09:38.017', 1),
(77, 15, 1, 'Si', '2025-11-12 20:27:58.993', 1),
(78, 1, 12, 'No', '2025-11-15 22:04:07.499', 1),
(79, 2, 12, 'No', '2025-11-15 22:04:07.499', 1),
(80, 3, 12, 'No', '2025-11-15 22:04:07.499', 1),
(81, 4, 12, 'No', '2025-11-15 22:04:07.499', 1),
(82, 5, 12, 'No', '2025-11-15 22:04:07.499', 1),
(83, 10, 12, 'No', '2025-11-15 22:04:07.499', 1),
(84, 11, 12, 'No', '2025-11-15 22:04:07.499', 1),
(85, 12, 12, 'No', '2025-11-15 22:04:07.499', 1),
(86, 13, 12, 'No', '2025-11-15 22:04:07.499', 1),
(87, 14, 12, 'No', '2025-11-15 22:04:07.499', 1),
(88, 15, 12, 'No', '2025-11-15 22:04:07.499', 1),
(93, 13, 2, 'Si', '2025-11-15 22:04:09.817', 1),
(94, 14, 2, 'No', '2025-11-15 22:04:09.817', 1),
(95, 15, 2, 'Si', '2025-11-15 22:04:09.817', 1),
(96, 16, 2, 'No', '2025-11-16 16:21:30.232', 1),
(97, 16, 1, 'Si', '2025-11-16 16:21:34.420', 1),
(98, 17, 2, 'Si', '2025-11-26 20:48:24.252', 1),
(99, 18, 2, 'Si', '2025-11-26 21:33:07.304', 1),
(100, 17, 1, 'No', '2025-11-26 22:07:51.205', 1),
(101, 18, 1, 'No', '2025-11-26 22:07:51.205', 1),
(103, 19, 2, 'No', '2025-11-27 02:18:24.167', 1),
(104, 19, 1, 'Si', '2025-11-27 02:18:31.520', 1),
(105, 20, 1, 'Si', '2025-11-27 03:33:50.007', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_menu`
--

CREATE TABLE `tm_menu` (
  `MEN_ID` int(11) NOT NULL,
  `MEN_NOM` varchar(150) DEFAULT NULL,
  `MEN_RUTA` varchar(250) DEFAULT NULL,
  `MEN_IDENTI` varchar(150) DEFAULT NULL,
  `MEN_GRUPO` varchar(150) DEFAULT NULL,
  `MEN_ORDEN` int(11) DEFAULT NULL,
  `FECH_CREA` datetime(3) DEFAULT NULL,
  `EST` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tm_menu`
--

INSERT INTO `tm_menu` (`MEN_ID`, `MEN_NOM`, `MEN_RUTA`, `MEN_IDENTI`, `MEN_GRUPO`, `MEN_ORDEN`, `FECH_CREA`, `EST`) VALUES
(1, 'Dashboard', '../home', 'dashboard', 'Principal', 1, '2025-10-24 15:40:00.000', 1),
(2, 'Mantenimiento  Rol', '../MntRol', 'mnt-rol', 'Usuarios', 2, '2025-10-24 15:40:00.000', 1),
(3, 'Mantenimiento Categoria', '../MntCategoria', 'mnt-categoria', 'Mantenimiento', 3, '2025-10-24 15:40:00.000', 1),
(4, 'Mantenimiento Piso', '../MntPiso', 'mnt-piso', 'Mantenimiento', 4, '2025-10-24 15:40:00.000', 1),
(5, 'Mantenimiento Habitacion', '../MntHabitacion', 'mnt-habitacion', 'Mantenimiento', 5, '2025-10-24 15:40:00.000', 1),
(10, 'Mantenimiento  Usuarios', '../MntUsuario', 'mnt-usuario', 'Usuarios', 10, '2025-10-24 15:40:00.000', 1),
(11, 'Clientes', '	\n../Cliente', 'cliente', 'Clientes', 11, '2025-10-24 15:40:00.000', 1),
(12, 'Estado Habitacion', '	\n../MntEstadoHabitacion', 'mtn-estadohabitacion', 'Mantenimiento', 12, '2025-10-24 15:40:00.000', 1),
(13, 'Recepcion', '../ListRecepcion', 'gst-recepcion', 'Gestion', 3, '2025-11-05 20:25:45.000', 1),
(14, 'Mantenimiento  Productos', '../MntProducto', 'mtn-producto', 'Tienda', 1, '2025-11-12 19:09:24.000', 1),
(15, 'Vender', '../ListVender', 'gst-venta', 'Tienda', 1, '2025-11-12 20:26:53.000', 1),
(16, 'Mantenimiento  Tarifas', '../MntTarifa', 'mnt-tarifa', 'Mantenimiento', 1, '2025-11-16 16:20:18.000', 1),
(17, 'Habitaciones', '../Habitaciones', 'habitaciones', 'Habitaciones', 1, '2025-11-26 20:41:18.000', 1),
(18, 'Productos', '../Productos', 'productos', 'Tienda', 3, '2025-11-26 21:31:17.000', 1),
(19, 'Reporte de Ventas', '../ReporteVentas', 'rpt-ventas', 'Reportes', 1, '2025-11-27 02:18:06.000', 1),
(20, 'Historial Comprobantes', '../HistorialComprobantes', 'rpt-comprobantes', 'Reportes', 3, '2025-11-27 16:20:18.000', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `IdUsuario` int(11) NOT NULL,
  `Nombre` varchar(50) NOT NULL,
  `Apellido` varchar(50) NOT NULL,
  `DNI` varchar(8) NOT NULL,
  `Correo` varchar(100) NOT NULL,
  `Pass` varchar(255) NOT NULL,
  `Estado` tinyint(1) NOT NULL DEFAULT 1,
  `FechaCreacion` datetime NOT NULL DEFAULT current_timestamp(),
  `IdRol` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`IdUsuario`, `Nombre`, `Apellido`, `DNI`, `Correo`, `Pass`, `Estado`, `FechaCreacion`, `IdRol`) VALUES
(1, 'Jean', 'Cordova', 'd', 'pierrecodex18@gmail.com', '$2y$10$0bPMytnjSTOwvZI3jrG/.Oggo23UKQPp5nsDGaiusYf5vnbczPrCO', 1, '2025-10-14 10:08:33', 1),
(21, 'Jose Mario', 'Perez', '87764534', 'jose@gmail.com', '$2y$10$vEIObKl4fShZHJsEveGaDuZa.HTD7RINRCBcimRX.by4vurGgwlZy', 1, '2025-10-15 16:22:49', 2),
(54, 'FSDF', 'cordova', '87764532', 'joshhhe@gmail.com', '$2y$10$GCRgT66M4EaquO44H7jW1eNFJnY1ZQx8PD2sQ18SSiP5XBCd93ctq', 1, '2025-10-29 14:06:33', 2),
(55, 'Carlos', 'Mendoza', '98765432', 'carlos.mendoza@hotel.com', '$2y$10$uybuEZusY2SUDSM/OdKmdezT9mRy1Pr7iGdYYBbfJoCwr5dLNr5sa', 1, '2025-11-21 21:37:12', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `venta`
--

CREATE TABLE `venta` (
  `IdVenta` int(11) NOT NULL,
  `IdRecepcion` int(11) NOT NULL,
  `Total` decimal(10,2) NOT NULL,
  `Estado` varchar(50) NOT NULL,
  `FechaCreacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `venta`
--

INSERT INTO `venta` (`IdVenta`, `IdRecepcion`, `Total`, `Estado`, `FechaCreacion`) VALUES
(76, 79, 75.52, 'PAGADO', '2025-12-06 17:54:03');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `boleta`
--
ALTER TABLE `boleta`
  ADD PRIMARY KEY (`bol_id`),
  ADD UNIQUE KEY `uk_serie_correlativo` (`bol_serie`,`bol_correlativo`),
  ADD KEY `idx_recepcion` (`rec_id`),
  ADD KEY `idx_fecha_emision` (`bol_fecha_emision`),
  ADD KEY `idx_estado` (`bol_estado`);

--
-- Indices de la tabla `boleta_detalle`
--
ALTER TABLE `boleta_detalle`
  ADD PRIMARY KEY (`bol_det_id`),
  ADD KEY `idx_boleta` (`bol_id`);

--
-- Indices de la tabla `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`IdCategoria`),
  ADD UNIQUE KEY `uq_categoria_descripcion` (`Descripcion`);

--
-- Indices de la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`IdCliente`),
  ADD UNIQUE KEY `uq_cliente_documento` (`Documento`);

--
-- Indices de la tabla `detalle_venta`
--
ALTER TABLE `detalle_venta`
  ADD PRIMARY KEY (`IdDetalleVenta`),
  ADD KEY `fk_detalle_venta_venta` (`IdVenta`),
  ADD KEY `fk_detalle_venta_producto` (`IdProducto`);

--
-- Indices de la tabla `estado_habitacion`
--
ALTER TABLE `estado_habitacion`
  ADD PRIMARY KEY (`IdEstadoHabitacion`);

--
-- Indices de la tabla `factura`
--
ALTER TABLE `factura`
  ADD PRIMARY KEY (`fac_id`),
  ADD UNIQUE KEY `uk_fac_serie_correlativo` (`fac_serie`,`fac_correlativo`),
  ADD KEY `idx_fac_rec_id` (`rec_id`),
  ADD KEY `idx_fac_serie_correlativo` (`fac_serie`,`fac_correlativo`),
  ADD KEY `idx_fac_cliente_ruc` (`fac_cliente_ruc`),
  ADD KEY `idx_fac_fecha_emision` (`fac_fecha_emision`),
  ADD KEY `idx_fac_estado` (`fac_estado`),
  ADD KEY `fk_fac_usuario` (`fac_usuario_registro`);

--
-- Indices de la tabla `factura_cuotas`
--
ALTER TABLE `factura_cuotas`
  ADD PRIMARY KEY (`fac_cuo_id`),
  ADD KEY `idx_fac_cuo_fac_id` (`fac_id`);

--
-- Indices de la tabla `factura_detalle`
--
ALTER TABLE `factura_detalle`
  ADD PRIMARY KEY (`fac_det_id`),
  ADD KEY `idx_fac_det_fac_id` (`fac_id`);

--
-- Indices de la tabla `habitacion`
--
ALTER TABLE `habitacion`
  ADD PRIMARY KEY (`IdHabitacion`),
  ADD UNIQUE KEY `uq_habitacion_numero` (`Numero`),
  ADD KEY `fk_habitacion_estado` (`IdEstadoHabitacion`),
  ADD KEY `fk_habitacion_piso` (`IdPiso`),
  ADD KEY `fk_habitacion_categoria` (`IdCategoria`);

--
-- Indices de la tabla `habitacion_tarifa`
--
ALTER TABLE `habitacion_tarifa`
  ADD PRIMARY KEY (`id_habitacion_tarifa`),
  ADD KEY `ix_habitacion_tarifa_habitacion` (`id_habitacion`),
  ADD KEY `ix_habitacion_tarifa_tarifa` (`id_tarifa`),
  ADD KEY `ix_habitacion_tarifa_periodo` (`id_habitacion`,`fecha_inicio`,`fecha_fin`);

--
-- Indices de la tabla `piso`
--
ALTER TABLE `piso`
  ADD PRIMARY KEY (`IdPiso`),
  ADD UNIQUE KEY `uq_piso_descripcion` (`Descripcion`);

--
-- Indices de la tabla `producto`
--
ALTER TABLE `producto`
  ADD PRIMARY KEY (`IdProducto`),
  ADD UNIQUE KEY `uq_producto_nombre` (`Nombre`);

--
-- Indices de la tabla `recepcion`
--
ALTER TABLE `recepcion`
  ADD PRIMARY KEY (`IdRecepcion`),
  ADD KEY `fk_recepcion_cliente` (`IdCliente`),
  ADD KEY `fk_recepcion_habitacion` (`IdHabitacion`),
  ADD KEY `fk_recepcion_tarifa` (`IdTarifa`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`IdRol`),
  ADD UNIQUE KEY `uq_rol_descripcion` (`Descripcion`);

--
-- Indices de la tabla `tarifa`
--
ALTER TABLE `tarifa`
  ADD PRIMARY KEY (`IdTarifa`);

--
-- Indices de la tabla `td_menu`
--
ALTER TABLE `td_menu`
  ADD PRIMARY KEY (`MEND_ID`),
  ADD KEY `FK_td_menu_tm_menu` (`MEN_ID`),
  ADD KEY `FK_td_menu_rol` (`IdRol`);

--
-- Indices de la tabla `tm_menu`
--
ALTER TABLE `tm_menu`
  ADD PRIMARY KEY (`MEN_ID`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`IdUsuario`),
  ADD KEY `ix_usuario_dni` (`DNI`),
  ADD KEY `ix_usuario_correo` (`Correo`),
  ADD KEY `ix_usuario_rol` (`IdRol`);

--
-- Indices de la tabla `venta`
--
ALTER TABLE `venta`
  ADD PRIMARY KEY (`IdVenta`),
  ADD KEY `fk_venta_recepcion` (`IdRecepcion`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `boleta`
--
ALTER TABLE `boleta`
  MODIFY `bol_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT de la tabla `boleta_detalle`
--
ALTER TABLE `boleta_detalle`
  MODIFY `bol_det_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT de la tabla `categoria`
--
ALTER TABLE `categoria`
  MODIFY `IdCategoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT de la tabla `cliente`
--
ALTER TABLE `cliente`
  MODIFY `IdCliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT de la tabla `detalle_venta`
--
ALTER TABLE `detalle_venta`
  MODIFY `IdDetalleVenta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

--
-- AUTO_INCREMENT de la tabla `estado_habitacion`
--
ALTER TABLE `estado_habitacion`
  MODIFY `IdEstadoHabitacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `factura`
--
ALTER TABLE `factura`
  MODIFY `fac_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `factura_cuotas`
--
ALTER TABLE `factura_cuotas`
  MODIFY `fac_cuo_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `factura_detalle`
--
ALTER TABLE `factura_detalle`
  MODIFY `fac_det_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `habitacion`
--
ALTER TABLE `habitacion`
  MODIFY `IdHabitacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `habitacion_tarifa`
--
ALTER TABLE `habitacion_tarifa`
  MODIFY `id_habitacion_tarifa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `piso`
--
ALTER TABLE `piso`
  MODIFY `IdPiso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `producto`
--
ALTER TABLE `producto`
  MODIFY `IdProducto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT de la tabla `recepcion`
--
ALTER TABLE `recepcion`
  MODIFY `IdRecepcion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `IdRol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `tarifa`
--
ALTER TABLE `tarifa`
  MODIFY `IdTarifa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `td_menu`
--
ALTER TABLE `td_menu`
  MODIFY `MEND_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

--
-- AUTO_INCREMENT de la tabla `tm_menu`
--
ALTER TABLE `tm_menu`
  MODIFY `MEN_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `IdUsuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT de la tabla `venta`
--
ALTER TABLE `venta`
  MODIFY `IdVenta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `boleta_detalle`
--
ALTER TABLE `boleta_detalle`
  ADD CONSTRAINT `fk_boleta_detalle` FOREIGN KEY (`bol_id`) REFERENCES `boleta` (`bol_id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `detalle_venta`
--
ALTER TABLE `detalle_venta`
  ADD CONSTRAINT `fk_detalle_venta_producto` FOREIGN KEY (`IdProducto`) REFERENCES `producto` (`IdProducto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detalle_venta_venta` FOREIGN KEY (`IdVenta`) REFERENCES `venta` (`IdVenta`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `factura`
--
ALTER TABLE `factura`
  ADD CONSTRAINT `fk_fac_recepcion` FOREIGN KEY (`rec_id`) REFERENCES `recepcion` (`IdRecepcion`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_fac_usuario` FOREIGN KEY (`fac_usuario_registro`) REFERENCES `usuario` (`IdUsuario`) ON DELETE SET NULL;

--
-- Filtros para la tabla `factura_cuotas`
--
ALTER TABLE `factura_cuotas`
  ADD CONSTRAINT `fk_fac_cuo_factura` FOREIGN KEY (`fac_id`) REFERENCES `factura` (`fac_id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `factura_detalle`
--
ALTER TABLE `factura_detalle`
  ADD CONSTRAINT `fk_fac_det_factura` FOREIGN KEY (`fac_id`) REFERENCES `factura` (`fac_id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `habitacion`
--
ALTER TABLE `habitacion`
  ADD CONSTRAINT `fk_habitacion_categoria` FOREIGN KEY (`IdCategoria`) REFERENCES `categoria` (`IdCategoria`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_habitacion_estado` FOREIGN KEY (`IdEstadoHabitacion`) REFERENCES `estado_habitacion` (`IdEstadoHabitacion`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_habitacion_piso` FOREIGN KEY (`IdPiso`) REFERENCES `piso` (`IdPiso`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `habitacion_tarifa`
--
ALTER TABLE `habitacion_tarifa`
  ADD CONSTRAINT `fk_habitacion_tarifa_habitacion` FOREIGN KEY (`id_habitacion`) REFERENCES `habitacion` (`IdHabitacion`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_habitacion_tarifa_tarifa` FOREIGN KEY (`id_tarifa`) REFERENCES `tarifa` (`IdTarifa`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `recepcion`
--
ALTER TABLE `recepcion`
  ADD CONSTRAINT `fk_recepcion_cliente` FOREIGN KEY (`IdCliente`) REFERENCES `cliente` (`IdCliente`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_recepcion_habitacion` FOREIGN KEY (`IdHabitacion`) REFERENCES `habitacion` (`IdHabitacion`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_recepcion_tarifa` FOREIGN KEY (`IdTarifa`) REFERENCES `tarifa` (`IdTarifa`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `td_menu`
--
ALTER TABLE `td_menu`
  ADD CONSTRAINT `FK_td_menu_rol` FOREIGN KEY (`IdRol`) REFERENCES `rol` (`IdRol`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_td_menu_tm_menu` FOREIGN KEY (`MEN_ID`) REFERENCES `tm_menu` (`MEN_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `fk_usuario_rol` FOREIGN KEY (`IdRol`) REFERENCES `rol` (`IdRol`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `venta`
--
ALTER TABLE `venta`
  ADD CONSTRAINT `fk_venta_recepcion` FOREIGN KEY (`IdRecepcion`) REFERENCES `recepcion` (`IdRecepcion`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
