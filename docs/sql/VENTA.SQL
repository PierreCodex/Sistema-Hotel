-- Adaptado a MySQL y al esquema actual (venta, detalle_venta, producto)
-- Procedimientos para gestión de ventas

DELIMITER $$

-- 1) Insertar cabecera de venta y retornar Id generado
DROP PROCEDURE IF EXISTS SP_I_VENTA_01 $$
CREATE PROCEDURE SP_I_VENTA_01 (IN p_IdRecepcion INT)
BEGIN
  INSERT INTO venta (IdRecepcion, Total, Estado)
  VALUES (p_IdRecepcion, 0, 'BORRADOR');
  SELECT LAST_INSERT_ID() AS VENT_ID;
END $$

-- 2) Insertar detalle de venta calculando el subtotal
DROP PROCEDURE IF EXISTS SP_I_VENTA_02 $$
CREATE PROCEDURE SP_I_VENTA_02 (
  IN p_VENT_ID INT,
  IN p_PROD_ID INT,
  IN p_PROD_PVENTA DECIMAL(10,2),
  IN p_DETV_CANT INT
)
BEGIN
  INSERT INTO detalle_venta (IdVenta, IdProducto, Cantidad, SubTotal)
  VALUES (p_VENT_ID, p_PROD_ID, p_DETV_CANT, p_PROD_PVENTA * p_DETV_CANT);
  SELECT LAST_INSERT_ID() AS DETV_ID;
END $$

-- 3) Listar detalles de venta por IdVenta
DROP PROCEDURE IF EXISTS SP_L_VENTA_01 $$
CREATE PROCEDURE SP_L_VENTA_01 (IN p_VENT_ID INT)
BEGIN
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
END $$

-- 4) Eliminar detalle de venta por IdDetalleVenta
DROP PROCEDURE IF EXISTS SP_D_VENTA_01 $$
CREATE PROCEDURE SP_D_VENTA_01 (IN p_DETV_ID INT)
BEGIN
  DELETE FROM detalle_venta WHERE IdDetalleVenta = p_DETV_ID;
END $$

-- 5) Calcular subtotal, IGV (18%) y total; actualizar venta.Total; retornar totales
DROP PROCEDURE IF EXISTS SP_U_VENTA_01 $$
CREATE PROCEDURE SP_U_VENTA_01 (IN p_VENT_ID INT)
BEGIN
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
END $$

-- 6) Finalizar venta (Estado = ACTIVO) y descontar stock de producto
DROP PROCEDURE IF EXISTS SP_U_VENTA_03 $$
CREATE PROCEDURE SP_U_VENTA_03 (IN p_VENT_ID INT)
BEGIN
  -- Marcar venta activa
  UPDATE venta SET Estado = 'ACTIVO' WHERE IdVenta = p_VENT_ID;

  -- Descontar stock por cada detalle (no permitir negativos)
  UPDATE producto p
  INNER JOIN detalle_venta dv ON p.IdProducto = dv.IdProducto
  SET p.Cantidad = GREATEST(p.Cantidad - dv.Cantidad, 0)
  WHERE dv.IdVenta = p_VENT_ID;

  SELECT 'OK' AS status;
END $$

DELIMITER ;